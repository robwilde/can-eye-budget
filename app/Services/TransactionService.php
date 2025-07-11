<?php

namespace App\Services;

use App\Data\TransactionData;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function createTransaction(User $user, TransactionData $transactionData): Transaction
    {
        return DB::transaction(function () use ($user, $transactionData) {
            $account = Account::where('user_id', $user->id)
                ->findOrFail($transactionData->account_id);

            $transaction = $account->transactions()->create($transactionData->toCreateArray());

            if ($transaction->isTransfer() && $transaction->transfer_to_account_id) {
                $this->createTransferTransaction($transaction);
            }

            return $transaction;
        });
    }

    public function updateTransaction(Transaction $transaction, TransactionData $transactionData): Transaction
    {
        return DB::transaction(function () use ($transaction, $transactionData) {
            $oldType = $transaction->type;
            $oldTransferAccountId = $transaction->transfer_to_account_id;

            $transaction->update($transactionData->toUpdateArray());

            if ($oldType === 'transfer' && $oldTransferAccountId) {
                $this->removeTransferTransaction($transaction, $oldTransferAccountId);
            }

            if ($transaction->isTransfer() && $transaction->transfer_to_account_id) {
                $this->createTransferTransaction($transaction);
            }

            return $transaction;
        });
    }

    public function deleteTransaction(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            if ($transaction->isTransfer() && $transaction->transfer_to_account_id) {
                $this->removeTransferTransaction($transaction, $transaction->transfer_to_account_id);
            }

            return $transaction->delete();
        });
    }

    public function bulkCreateTransactions(User $user, \Illuminate\Support\Collection $transactionsData): Collection
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
        return Transaction::whereHas('account', function ($query) use ($user) {
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
        $query = $account->transactions()
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
        $transactions = $account->transactions()
            ->where('transaction_date', '<=', $date)
            ->get();

        $transfersIn = Transaction::where('transfer_to_account_id', $account->id)
            ->where('type', 'transfer')
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

    private function createTransferTransaction(Transaction $sourceTransaction): void
    {
        $targetAccount = Account::find($sourceTransaction->transfer_to_account_id);
        
        if (!$targetAccount) {
            return;
        }

        Transaction::create([
            'account_id' => $targetAccount->id,
            'type' => 'income',
            'amount' => $sourceTransaction->amount,
            'description' => "Transfer from {$sourceTransaction->account->name}: {$sourceTransaction->description}",
            'transaction_date' => $sourceTransaction->transaction_date,
            'category_id' => $sourceTransaction->category_id,
            'recurring_pattern_id' => $sourceTransaction->recurring_pattern_id,
            'import_id' => $sourceTransaction->import_id,
        ]);
    }

    private function removeTransferTransaction(Transaction $sourceTransaction, int $transferAccountId): void
    {
        Transaction::where('account_id', $transferAccountId)
            ->where('type', 'income')
            ->where('amount', $sourceTransaction->amount)
            ->where('transaction_date', $sourceTransaction->transaction_date)
            ->where('description', 'like', "Transfer from {$sourceTransaction->account->name}:%")
            ->delete();
    }
}