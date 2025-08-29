
# Laravel 12 + Local LLM Transaction Categorization Implementation Guide

## 1. RECOMMENDED ARCHITECTURE: Hybrid Local Approach

Based on your requirements for privacy and learning capabilities, here's the optimal solution:

**Primary**: Sentence Transformers (for speed and privacy)
**Fallback**: Ollama + Llama 3.2 (for complex cases)
**Framework**: Laravel 12 with Python microservice

## 2. SETUP COMPONENTS

### A. Laravel 12 Side (Main Application)

#### Transaction Model and Migration
```php
// database/migrations/create_transactions_table.php
Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->decimal('amount', 10, 2);
    $table->string('description');
    $table->string('merchant')->nullable();
    $table->date('date');
    $table->unsignedBigInteger('category_id')->nullable();
    $table->decimal('confidence_score', 3, 2)->default(0.00);
    $table->boolean('manual_override')->default(false);
    $table->timestamps();
});
```

#### CSV Import Job
```php
// app/Jobs/ProcessTransactionCsv.php
class ProcessTransactionCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private string $csvPath, private int $userId) {}

    public function handle()
    {
        $transactions = $this->parseCsv($this->csvPath);

        foreach (array_chunk($transactions, 50) as $chunk) {
            CategorizeTransactionsBatch::dispatch($chunk, $this->userId);
        }
    }

    private function parseCsv(string $path): array
    {
        $transactions = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle); // Skip header
            while (($data = fgetcsv($handle)) !== false) {
                $transactions[] = [
                    'date' => $data[0],
                    'description' => $data[1], 
                    'amount' => $data[2],
                    'merchant' => $data[3] ?? null
                ];
            }
            fclose($handle);
        }
        return $transactions;
    }
}
```

#### Categorization Service
```php
// app/Services/TransactionCategorizationService.php
class TransactionCategorizationService
{
    private string $pythonServiceUrl;

    public function __construct()
    {
        $this->pythonServiceUrl = config('services.ml_service.url', 'http://localhost:8001');
    }

    public function categorizeTransaction(array $transaction): array
    {
        $response = Http::timeout(30)
            ->post($this->pythonServiceUrl . '/categorize', [
                'description' => $transaction['description'],
                'amount' => $transaction['amount'],
                'merchant' => $transaction['merchant'] ?? null
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('Categorization service unavailable');
    }

    public function batchCategorize(array $transactions): array
    {
        $response = Http::timeout(120)
            ->post($this->pythonServiceUrl . '/categorize-batch', [
                'transactions' => $transactions
            ]);

        return $response->successful() ? $response->json() : [];
    }
}
```

### B. Python FastAPI Microservice

#### Requirements (requirements.txt)
```text
fastapi==0.104.1
uvicorn==0.24.0
sentence-transformers==2.2.2
pandas==2.1.3
numpy==1.24.3
scikit-learn==1.3.2
requests==2.31.0
python-multipart==0.0.6
```

#### Main FastAPI Application
```python
# main.py
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Optional
import numpy as np
from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity
import json
import os
import requests

app = FastAPI(title="Transaction Categorization API")

# Load sentence transformer model
model = SentenceTransformer('all-MiniLM-L6-v2')

class Transaction(BaseModel):
    description: str
    amount: float
    merchant: Optional[str] = None

class TransactionBatch(BaseModel):
    transactions: List[Transaction]

class CategorizationService:
    def __init__(self):
        self.categories = self.load_categories()
        self.category_embeddings = self.compute_category_embeddings()
        self.ollama_url = "http://localhost:11434"

    def load_categories(self) -> dict:
        # Default categories with descriptions for better embedding
        return {
            "groceries": "food shopping, supermarket, grocery store, fresh produce",
            "dining": "restaurants, fast food, coffee shops, takeout, delivery",
            "transportation": "gas stations, public transit, uber, taxi, parking",
            "utilities": "electricity, water, gas bills, internet, phone service",
            "entertainment": "movies, streaming, games, concerts, sports events",
            "shopping": "retail stores, clothing, electronics, online shopping",
            "health": "medical expenses, pharmacy, doctor visits, insurance",
            "education": "tuition, books, courses, training, certifications",
            "travel": "flights, hotels, car rental, vacation expenses",
            "income": "salary, freelance payment, investment returns, refunds"
        }

    def compute_category_embeddings(self) -> dict:
        embeddings = {}
        for category, description in self.categories.items():
            embeddings[category] = model.encode([description])
        return embeddings

    def categorize_with_transformers(self, transaction: Transaction) -> dict:
        # Create enhanced description
        enhanced_desc = f"{transaction.description}"
        if transaction.merchant:
            enhanced_desc += f" {transaction.merchant}"

        # Get embedding for transaction
        transaction_embedding = model.encode([enhanced_desc])

        # Find best match
        best_category = None
        best_score = 0.0

        for category, embedding in self.category_embeddings.items():
            similarity = cosine_similarity(transaction_embedding, embedding)[0][0]
            if similarity > best_score:
                best_score = similarity
                best_category = category

        return {
            "category": best_category,
            "confidence": float(best_score),
            "method": "sentence_transformers"
        }

    def categorize_with_ollama(self, transaction: Transaction) -> dict:
        # Fallback to Ollama for low-confidence predictions
        prompt = f'''
        Categorize this transaction into one of these categories:
        {", ".join(self.categories.keys())}

        Transaction: {transaction.description}
        Amount: ${transaction.amount}
        Merchant: {transaction.merchant or "Unknown"}

        Respond with just the category name and confidence (0-1):
        Category: <category>
        Confidence: <confidence>
        '''

        try:
            response = requests.post(f"{self.ollama_url}/api/generate", 
                json={
                    "model": "llama3.2",
                    "prompt": prompt,
                    "stream": False
                }, timeout=30)

            if response.status_code == 200:
                result = response.json()['response']
                # Parse the response (simplified - would need more robust parsing)
                lines = result.strip().split('
')
                category = lines[0].replace('Category:', '').strip()
                confidence = float(lines[1].replace('Confidence:', '').strip())

                return {
                    "category": category,
                    "confidence": confidence,
                    "method": "ollama_llm"
                }
        except:
            pass

        return {"category": "uncategorized", "confidence": 0.0, "method": "fallback"}

# Initialize service
categorization_service = CategorizationService()

@app.post("/categorize")
async def categorize_transaction(transaction: Transaction):
    try:
        # Primary: Use sentence transformers
        result = categorization_service.categorize_with_transformers(transaction)

        # Fallback: Use Ollama for low confidence
        if result["confidence"] < 0.7:
            ollama_result = categorization_service.categorize_with_ollama(transaction)
            if ollama_result["confidence"] > result["confidence"]:
                result = ollama_result

        return result

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/categorize-batch")  
async def categorize_batch(batch: TransactionBatch):
    results = []
    for transaction in batch.transactions:
        result = categorization_service.categorize_with_transformers(transaction)
        results.append({
            "transaction": transaction.dict(),
            "categorization": result
        })
    return {"results": results}

@app.get("/health")
async def health_check():
    return {"status": "healthy", "model_loaded": True}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)
```

### C. Laravel Integration & Usage

#### Controller for CSV Upload
```php
// app/Http/Controllers/TransactionController.php
class TransactionController extends Controller
{
    public function uploadCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        $path = $request->file('csv_file')->store('transactions', 'local');

        // Dispatch background job
        ProcessTransactionCsv::dispatch($path, auth()->id());

        return response()->json([
            'message' => 'CSV uploaded successfully. Processing in background.',
            'job_id' => 'txn_' . time()
        ]);
    }

    public function getCategorizationStatus()
    {
        $pending = Transaction::where('user_id', auth()->id())
            ->whereNull('category_id')
            ->count();

        $total = Transaction::where('user_id', auth()->id())->count();

        return response()->json([
            'total_transactions' => $total,
            'pending_categorization' => $pending,
            'completion_percentage' => $total > 0 ? (($total - $pending) / $total) * 100 : 0
        ]);
    }
}
```

#### Batch Categorization Job
```php
// app/Jobs/CategorizeTransactionsBatch.php
class CategorizeTransactionsBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(TransactionCategorizationService $categorizationService)
    {
        try {
            $results = $categorizationService->batchCategorize($this->transactions);

            foreach ($results['results'] as $result) {
                Transaction::create([
                    'user_id' => $this->userId,
                    'description' => $result['transaction']['description'],
                    'amount' => $result['transaction']['amount'],
                    'merchant' => $result['transaction']['merchant'],
                    'category_id' => $this->getCategoryId($result['categorization']['category']),
                    'confidence_score' => $result['categorization']['confidence'],
                    'date' => $result['transaction']['date'] ?? now()
                ]);
            }

        } catch (Exception $e) {
            Log::error('Batch categorization failed: ' . $e->getMessage());
            $this->fail($e);
        }
    }

    private function getCategoryId(string $categoryName): ?int
    {
        return Category::firstOrCreate(['name' => $categoryName])->id;
    }
}
```

## 3. DEPLOYMENT & SETUP

### Docker Compose Setup
```yaml
# docker-compose.yml
version: '3.8'
services:
  laravel:
    build: .
    ports:
      - "8000:8000"
    volumes:
      - .:/var/www/html
    depends_on:
      - mysql
      - redis
      - ml-service

  ml-service:
    build: ./ml-service
    ports:
      - "8001:8001"
    volumes:
      - ./ml-service:/app

  ollama:
    image: ollama/ollama
    ports:
      - "11434:11434"
    volumes:
      - ollama-data:/root/.ollama

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: budgeting_app
      MYSQL_ROOT_PASSWORD: secret

  redis:
    image: redis:alpine

volumes:
  ollama-data:
```

## 4. LEARNING & TRAINING CAPABILITIES

### Training Data Collection
```php
// app/Http/Controllers/LearningController.php  
class LearningController extends Controller
{
    public function recordCategorization(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'correct_category_id' => 'required|exists:categories,id',
            'was_correct' => 'required|boolean'
        ]);

        $transaction = Transaction::find($request->transaction_id);

        // Store training example
        TrainingExample::create([
            'description' => $transaction->description,
            'amount' => $transaction->amount, 
            'merchant' => $transaction->merchant,
            'correct_category_id' => $request->correct_category_id,
            'predicted_category_id' => $transaction->category_id,
            'was_correct' => $request->was_correct,
            'user_id' => auth()->id()
        ]);

        // Update transaction if corrected
        if (!$request->was_correct) {
            $transaction->update([
                'category_id' => $request->correct_category_id,
                'manual_override' => true
            ]);
        }

        return response()->json(['message' => 'Learning recorded']);
    }
}
```

This implementation provides:
✅ **Privacy**: All processing stays local
✅ **Learning**: Collects user corrections for model improvement  
✅ **Speed**: Fast sentence transformers + LLM fallback
✅ **Laravel Integration**: Clean API design with queued processing
✅ **Scalability**: Handles large CSV files through background jobs
✅ **Flexibility**: Easy to swap models and add new categories
