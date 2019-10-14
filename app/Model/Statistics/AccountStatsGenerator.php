<?php

namespace App\Model\Statistics;

use App\Model\Account;
use App\Model\AccountStats;
use App\Model\Category;
use App\Model\Transaction;
use DateTime;
use Illuminate\Support\Facades\Log;
use LimitIterator;
use SplFileObject;
use Symfony\Component\HttpFoundation\File\File;

class AccountStatsGenerator {
    public function run() {
        $this->clear();

        $stats = $this->generate();

        $this->store($stats);

        return $stats;
    }

    public function generate() {
        Log::info("Generating summary statistics for accounts");

        $transactions = Transaction::query()
            ->orderBy('account_id', 'asc')
            ->orderBy('date', 'asc')
            ->get();

        Log::debug("Retrieved " . count($transactions) . " transactions for summary statistics");

        // Group transaction by account, year and month
        $grouped = $transactions->groupBy([
            'account_id',
            function($transaction) { return intval(substr($transaction->date, 0, 4)); },
            function($transaction) { return intval(substr($transaction->date, 5, 2)); },
        ]);

        // Compute total for each month
        $stats = [];

        foreach($grouped as $account_id => $perAccount) {
            $balance = 0;

            foreach($perAccount as $year => $perYear) {
                foreach($perYear as $month => $perMonth) {
                    $balance += $perMonth->sum('amount');
                    $stats[] = [
                        'account_id' => $account_id,
                        'year' => $year,
                        'month' => $month,
                        'balance' => $balance
                    ];
                }
            }

            Log::debug("Current balance for account #" . $account_id . " is " . $balance);
        }

        return $stats;
    }

    public function store(array $stats) {
        return AccountStats::insert($stats);
    }

    public function clear() {
        AccountStats::truncate();
    }
}
