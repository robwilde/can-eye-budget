<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\TransactionData;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

final class TransactionService
{
    /**
     * @throws Throwable
     */
    public function createTransaction(User $user, TransactionData|array $transactionData): Transaction
    {
        return DB::transaction(function () use ($user, $transactionData) {
            if (is_array($transactionData)) {
                $account = Account::where('user_id', $user->id)
                                  ->findOrFail($transactionData['account_id']);

                $transaction = $account->transactions()->create($transactionData);
            } else {
                $account = Account::where('user_id', $user->id)
                                  ->findOrFail($transactionData->account_id);

                $transaction = $account->transactions()->create($transactionData->toCreateArray());
            }

            if ($transaction->transfer_to_account_id && $transaction->isTransfer()) {
                $this->createTransferTransaction($transaction);
            }

            return $transaction;
        });
    }

    /**
     * @throws Throwable
     */
    public function updateTransaction(Transaction $transaction, TransactionData $transactionData): Transaction
    {
        return DB::transaction(function () use ($transaction, $transactionData) {
            $oldType = $transaction->type;
            $oldTransferAccountId = $transaction->transfer_to_account_id;

            $transaction->update($transactionData->toUpdateArray());

            if ($oldType === 'transfer' && $oldTransferAccountId) {
                $this->removeTransferTransaction($transaction, $oldTransferAccountId);
            }

            if ($transaction->transfer_to_account_id && $transaction->isTransfer()) {
                $this->createTransferTransaction($transaction);
            }

            return $transaction;
        });
    }

    /**
     * @throws Throwable
     */
    public function deleteTransaction(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            if ($transaction->transfer_to_account_id && $transaction->isTransfer()) {
                $this->removeTransferTransaction($transaction, $transaction->transfer_to_account_id);
            }

            return $transaction->delete();
        });
    }

    /**
     * @throws Throwable
     */
    public function bulkCreateTransactions(User $user, Collection $transactionsData): Collection
    {
        $transactions = collect();

        DB::transaction(function () use ($user, $transactionsData, &$transactions) {
            foreach ($transactionsData as $transactionData) {
                $transactions->push($this->createTransaction($user, $transactionData));
            }
        });

        return $transactions;
    }

    public function getTransactionsForPeriod(User $user, Carbon $startDate, Carbon $endDate): Collection
    {
        return Transaction::whereHas('account', static function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
                          ->with(['account', 'category', 'transferToAccount'])
                          ->forDateRange($startDate, $endDate)
                          ->orderBy('transaction_date', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    public function getTransactionsForAccount(Account $account, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $query = $account
            ->transactions()
            ->with(['category', 'transferToAccount'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($startDate && $endDate) {
            $query->forDateRange($startDate, $endDate);
        }

        return $query->get();
    }

    public function getRunningBalance(Account $account, Carbon $date): float
    {
        $transactions = $account
            ->transactions()
            ->entered() // Only include entered transactions, not planned
            ->where('transaction_date', '<=', $date)
            ->get();

        $transfersIn = Transaction::where('transfer_to_account_id', $account->id)
                                  ->where('type', 'transfer')
                                  ->where('status', 'entered') // Only include entered transfers, not planned
                                  ->where('transaction_date', '<=', $date)
                                  ->get();

        $balance = (float) $account->initial_balance;

        foreach ($transactions as $transaction) {
            $balance += $transaction->signed_amount;
        }

        foreach ($transfersIn as $transfer) {
            $balance += (float) $transfer->amount;
        }

        return $balance;
    }

    public function reconcileTransaction(Transaction $transaction): Transaction
    {
        $transaction->update(['reconciled' => true]);

        return $transaction;
    }

    public function unreconcileTransaction(Transaction $transaction): Transaction
    {
        $transaction->update(['reconciled' => false]);

        return $transaction;
    }

    public function getPeriodTotals(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $transactions = $this->getTransactionsForPeriod($user, $startDate, $endDate);

        $plannedIncome = $transactions->where('status', 'planned')->where('type', 'income')->sum('amount');
        $plannedExpenses = $transactions->where('status', 'planned')->where('type', 'expense')->sum('amount');
        $enteredIncome = $transactions->where('status', 'entered')->where('type', 'income')->sum('amount');
        $enteredExpenses = $transactions->where('status', 'entered')->where('type', 'expense')->sum('amount');

        $plannedNet = $plannedIncome - $plannedExpenses;
        $enteredNet = $enteredIncome - $enteredExpenses;

        $percentageSaved = $plannedNet > 0 ? round(($enteredNet / $plannedNet) * 100) : 0;

        return [
            'planned' => [
                'income'   => $plannedIncome,
                'expenses' => $plannedExpenses,
                'net'      => $plannedNet,
            ],
            'entered' => [
                'income'   => $enteredIncome,
                'expenses' => $enteredExpenses,
                'net'      => $enteredNet,
            ],
            'percentage_saved' => $percentageSaved,
        ];
    }

    private function createTransferTransaction(Transaction $sourceTransaction): void
    {
        $targetAccount = Account::find($sourceTransaction->transfer_to_account_id);

        if (! $targetAccount) {
            return;
        }

        // Generate a unique transfer pair ID to link the two transactions
        $transferPairId = uniqid('transfer_', true);

        // Update the source transaction with transfer pair ID and better description
        $sourceTransaction->update([
            'transfer_pair_id' => $transferPairId,
            'description'      => "Transfer to $targetAccount->name",
        ]);

        // Create the destination transaction
        Transaction::create([
            'account_id'           => $targetAccount->id,
            'type'                 => 'transfer',
            'amount'               => $sourceTransaction->amount,
            'description'          => "Transfer from {$sourceTransaction->account->name}",
            'transaction_date'     => $sourceTransaction->transaction_date,
            'category_id'          => $sourceTransaction->category_id,
            'recurring_pattern_id' => $sourceTransaction->recurring_pattern_id,
            'import_id'            => $sourceTransaction->import_id,
            'status'               => $sourceTransaction->status ?? 'entered',
            'transfer_pair_id'     => $transferPairId,
            // Destination transactions don't need transfer_to_account_id
        ]);
    }

    private function removeTransferTransaction(Transaction $sourceTransaction, int $transferAccountId): void
    {
        // Use transfer_pair_id for more accurate deletion if available
        if ($sourceTransaction->transfer_pair_id) {
            Transaction::where('transfer_pair_id', $sourceTransaction->transfer_pair_id)
                       ->where('account_id', $transferAccountId)
                       ->delete();
        } else {
            // Fallback to legacy deletion method for existing data
            Transaction::where('account_id', $transferAccountId)
                       ->whereIn('type', ['income', 'transfer'])
                       ->where('amount', $sourceTransaction->amount)
                       ->where('transaction_date', $sourceTransaction->transaction_date)
                       ->where(function ($query) use ($sourceTransaction) {
                           $query
                               ->where('description', 'like', "Transfer from {$sourceTransaction->account->name}:%")
                               ->orWhere('description', "Transfer from {$sourceTransaction->account->name}");
                       })
                       ->delete();
        }
    }
}
