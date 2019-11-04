<?php

namespace App\Model\Statistics;

use App\Model\Category;
use App\Model\CategoryStats;
use App\Model\Transaction;
use Illuminate\Support\Collection;
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

        // Load the tree of categories and
        $categories = Category::get()->toTree();

        return $this->convertToStats($transactions, $categories)->toArray();
    }

    public function store(array $stats) {
        return CategoryStats::insert($stats);
    }

    public function clear() {
        CategoryStats::truncate();
    }

    public function convertToStats(Collection $transactions, Collection $categories): Collection {
        // Group transaction by account, year and month
        $grouped = $transactions->groupBy([
            function($transaction) { return intval(substr($transaction->date, 0, 4)); },
            function($transaction) { return intval(substr($transaction->date, 5, 2)); },
            'category_id',
        ]);

        // Compute total for each month
        $stats = Collection::make();

        $count = 0;
        foreach($grouped as $year => $perYear) {
            foreach($perYear as $month => $perMonth) {
                // Create a map of totals per category
                $categoryTotals = Collection::make($perMonth)->map(function($categoryStats) { return $categoryStats->sum('amount'); });

                // Create stats entries, combining the category total with
                // the totals of all descendants
                $this->generateStatsEntries($stats, $year, $month, $categoryTotals, $categories);

                // Also add an entry for transactions without category
                if($categoryTotals->get("")) {
                    $stats->add([
                        'category_id' => null,
                        'year' => $year,
                        'month' => $month,
                        'amount' => $categoryTotals->get(""),
                        'grand_total' => $categoryTotals->get("")
                    ]);
                }
            }
        }

        Log::debug("Generated " . $count . " stats");

        return $stats;
    }

    private function generateStatsEntries(Collection $stats, int $year, int $month, Collection $categoryTotals, Collection $categories): float
    {
        $total = 0;
        foreach($categories as $category) {
            // First add all entries for the children of this category
            $childTotal = $this->generateStatsEntries($stats, $year, $month,  $categoryTotals, $category->children);
            $thisTotal = $categoryTotals->get($category->id, 0);

            // Add a stats entry
            if($thisTotal != 0 || $childTotal != 0) {
                $stats->add([
                    'category_id' => $category->id,
                    'year' => $year,
                    'month' => $month,
                    'amount' => $thisTotal,
                    'grand_total' => $thisTotal + $childTotal
                ]);
            }

            // Update the grand total for this set of categories
            $total += $childTotal + $thisTotal;
        }

        return $total;
    }
}
