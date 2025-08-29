<?php

declare(strict_types=1);

use App\Livewire\ImportWizard;
use App\Models\Account;
use App\Models\Import;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

covers(ImportWizard::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->for($this->user)->create();
    Storage::fake('local');
});

test('import wizard can be rendered', function () {
    $this->actingAs($this->user)
        ->get('/import')
        ->assertStatus(200)
        ->assertSeeLivewire(ImportWizard::class);
});

test('import wizard shows upload step by default', function () {
    Livewire::actingAs($this->user)
        ->test(ImportWizard::class)
        ->assertSet('currentStep', 'upload')
        ->assertSee('Upload Your CSV File')
        ->assertSee('Select Account');
});

test('import wizard validates required fields on upload', function () {
    Livewire::actingAs($this->user)
        ->test(ImportWizard::class)
        ->call('processUpload')
        ->assertHasErrors(['selectedAccountId', 'csvFile']);
});

test('import wizard processes valid CSV upload', function () {
    // Create a test CSV file with the sample bank statement format
    $csvContent = "Effective Date,Entered Date,Transaction Description,Amount,Balance\n";
    $csvContent .= "01/03/2025,02/03/2025,\"POS - #459939 - PAYPAL *PYPL Payin4\",-22.25,953.72\n";
    $csvContent .= "05/03/2025,05/03/2025,\"Osko Payment From Robert E Wilde\",400.00,1250.97\n";

    $csvFile = UploadedFile::fake()->createWithContent('test_import.csv', $csvContent);

    Livewire::actingAs($this->user)
        ->test(ImportWizard::class)
        ->set('selectedAccountId', $this->account->id)
        ->set('csvFile', $csvFile)
        ->call('processUpload')
        ->assertSet('currentStep', 'mapping')
        ->assertSee('Map CSV Columns');
});

test('import wizard detects CSV columns correctly', function () {
    $csvContent = "Effective Date,Entered Date,Transaction Description,Amount,Balance\n";
    $csvContent .= "01/03/2025,02/03/2025,\"Test Transaction\",-22.25,953.72\n";

    $csvFile = UploadedFile::fake()->createWithContent('test_import.csv', $csvContent);

    Livewire::actingAs($this->user)
        ->test(ImportWizard::class)
        ->set('selectedAccountId', $this->account->id)
        ->set('csvFile', $csvFile)
        ->call('processUpload')
        ->assertSet('currentStep', 'mapping')
        ->assertSet('detectedMapping.date', 'Effective Date')
        ->assertSet('detectedMapping.description', 'Transaction Description')
        ->assertSet('detectedMapping.amount', 'Amount')
        ->assertSet('detectedMapping.balance', 'Balance');
});

test('import wizard validates column mapping', function () {
    $csvContent = "Date,Description,Amount\n01/03/2025,Test,-22.25\n";
    $csvFile = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

    Livewire::actingAs($this->user)
        ->test(ImportWizard::class)
        ->set('selectedAccountId', $this->account->id)
        ->set('csvFile', $csvFile)
        ->call('processUpload')
        ->set('columnMapping.date', '')
        ->call('processMapping')
        ->assertHasErrors(['columnMapping.date']);
});

test('import wizard processes column mapping successfully', function () {
    $csvContent = "Date,Description,Amount\n01/03/2025,Test Transaction,-22.25\n05/03/2025,Another Transaction,400.00\n";
    $csvFile = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

    Livewire::actingAs($this->user)
        ->test(ImportWizard::class)
        ->set('selectedAccountId', $this->account->id)
        ->set('csvFile', $csvFile)
        ->call('processUpload')
        ->set('columnMapping.date', 'Date')
        ->set('columnMapping.description', 'Description')
        ->set('columnMapping.amount', 'Amount')
        ->call('processMapping')
        ->assertSet('currentStep', 'preview')
        ->assertSet('totalRows', 2);
});

test('import wizard generates preview data correctly', function () {
    $csvContent = "Date,Description,Amount\n01/03/2025,Test Transaction,-22.25\n05/03/2025,Income Transaction,400.00\n";
    $csvFile = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

    $component = Livewire::actingAs($this->user)
        ->test(ImportWizard::class)
        ->set('selectedAccountId', $this->account->id)
        ->set('csvFile', $csvFile)
        ->call('processUpload')
        ->set('columnMapping.date', 'Date')
        ->set('columnMapping.description', 'Description')
        ->set('columnMapping.amount', 'Amount')
        ->call('processMapping');

    $previewData = $component->get('previewData');

    expect($previewData)->toHaveCount(2);
    expect($previewData[0]['type'])->toBe('expense');
    expect($previewData[0]['amount'])->toBe(22.25);
    expect($previewData[1]['type'])->toBe('income');
    expect($previewData[1]['amount'])->toBe(400.00);
});

test('import wizard can navigate between steps', function () {
    Livewire::actingAs($this->user)
        ->test(ImportWizard::class)
        ->assertSet('currentStep', 'upload')
        ->call('goToStep', 'mapping')
        ->assertSet('currentStep', 'mapping')
        ->call('previousStep')
        ->assertSet('currentStep', 'upload')
        ->call('nextStep')
        ->assertSet('currentStep', 'mapping');
});

test('import wizard can be reset', function () {
    $csvContent = "Date,Description,Amount\n01/03/2025,Test,-22.25\n";
    $csvFile = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

    Livewire::actingAs($this->user)
        ->test(ImportWizard::class)
        ->set('selectedAccountId', $this->account->id)
        ->set('csvFile', $csvFile)
        ->set('currentStep', 'mapping')
        ->call('startOver')
        ->assertSet('currentStep', 'upload')
        ->assertSet('selectedAccountId', 0)
        ->assertSet('csvFile', null);
});

test('import wizard creates import record and transactions', function () {
    $csvContent = "Date,Description,Amount\n01/03/2025,Test Transaction,-22.25\n05/03/2025,Income Transaction,400.00\n";
    $csvFile = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

    $component = Livewire::actingAs($this->user)
        ->test(ImportWizard::class)
        ->set('selectedAccountId', $this->account->id)
        ->set('csvFile', $csvFile)
        ->call('processUpload')
        ->set('columnMapping.date', 'Date')
        ->set('columnMapping.description', 'Description')
        ->set('columnMapping.amount', 'Amount')
        ->call('processMapping')
        ->call('processPreview')
        ->call('processImport');

    // Check import was created
    $import = Import::where('filename', 'test.csv')->first();
    expect($import)->not->toBeNull();
    expect($import->user_id)->toBe($this->user->id);
    expect($import->status)->toBe('completed');

    // Check transactions were created
    expect($this->account->transactions()->count())->toBe(2);

    $transactions = $this->account->transactions()->orderBy('amount')->get();
    expect((float) $transactions->first()->amount)->toBe(22.25);
    expect($transactions->first()->type)->toBe('expense');
    expect((float) $transactions->last()->amount)->toBe(400.00);
    expect($transactions->last()->type)->toBe('income');

    // Check component shows results
    $component->assertSet('currentStep', 'results')
        ->assertSet('importResult.success', true)
        ->assertSet('importResult.created_count', 2);
});

test('import wizard handles CSV parsing errors gracefully', function () {
    $csvContent = 'Invalid CSV content without proper structure';
    $csvFile = UploadedFile::fake()->createWithContent('invalid.csv', $csvContent);

    $component = Livewire::actingAs($this->user)
        ->test(ImportWizard::class)
        ->set('selectedAccountId', $this->account->id)
        ->set('csvFile', $csvFile)
        ->call('processUpload');

    expect($component->get('errorMessage'))->not->toBeNull();
});

test('import wizard shows progress percentage correctly', function () {
    $component = Livewire::actingAs($this->user)->test(ImportWizard::class);

    // Upload step (0% progress)
    expect($component->get('progress_percentage'))->toBe(0.0);

    // Mapping step (25% progress)
    $component->set('currentStep', 'mapping');
    expect($component->get('progress_percentage'))->toBe(25.0);

    // Results step (100% progress)
    $component->set('currentStep', 'results');
    expect($component->get('progress_percentage'))->toBe(100.0);
});

test('import wizard loads user accounts correctly', function () {
    $secondAccount = Account::factory()->for($this->user)->create(['name' => 'Second Account']);

    $component = Livewire::actingAs($this->user)
        ->test(ImportWizard::class);

    $userAccounts = $component->get('user_accounts');

    expect($userAccounts)->toHaveCount(2);
    expect($userAccounts->pluck('name'))->toContain('Second Account');
});
