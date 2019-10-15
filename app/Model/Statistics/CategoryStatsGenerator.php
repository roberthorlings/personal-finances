<?php

namespace App\Model\Statistics;

use App\Model\CategoryStats;
use App\Model\Transaction;
use Illuminate\Support\Facades\Log;

class CategoryStatsGenerator {
    public function run() {
        $this->clear();

        $stats = $this->generate();

        $this->store($stats);

        return $stats;
    }

    public function generate() {
        Log::info("Generating summary statistics for categories");

        // List all transactions. Note the 'getQuery()' call here. It retrieves the
        // query itself, which enables us to get the results as stdClass objects, instead of
        // actual Account objects. This is much more performant (more than 40% better) and
        // is fine for these purposes
        $transactions = Transaction::query()
            ->orderBy('category_id', 'asc')
            ->orderBy('date', 'asc')
            ->getQuery()
            ->get();

        Log::debug("Retrieved " . count($transactions) . " transactions for summary statistics");

        // Group transaction by account, year and month
        $grouped = $transactions->groupBy([
            'category_id',
            function($transaction) { return intval(substr($transaction->date, 0, 4)); },
            function($transaction) { return intval(substr($transaction->date, 5, 2)); },
        ]);

        // Compute total for each month
        $stats = [];

        foreach($grouped as $category_id => $perCategory) {
            $count = 0;
            foreach($perCategory as $year => $perYear) {
                foreach($perYear as $month => $perMonth) {
                    $stats[] = [
                        'category_id' => $category_id ? $category_id : null,
                        'year' => $year,
                        'month' => $month,
                        'amount' => $perMonth->sum('amount')
                    ];
                    $count++;
                }
            }

            Log::debug("Stored " . $count . " stats for category #" . $category_id);
        }

        return $stats;
    }

    public function store(array $stats) {
        return CategoryStats::insert($stats);
    }

    public function clear() {
        CategoryStats::truncate();
    }
}
