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

        // List all transactions. Note the 'getQuery()' call here. It retrieves the
        // query itself, which enables us to get the results as stdClass objects, instead of
        // actual Account objects. This is much more performant (more than 40% better) and
        // is fine for these purposes
        $transactions = Transaction::query()
            ->orderBy('account_id', 'asc')
            ->orderBy('date', 'asc')
            ->getQuery()
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

            Log::debug("Current balance for account #" . $account_id . " is " . $balance . ". Updating accounts table");
            Account::where('id', $account_id)->update(['balance' => $balance]);
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
