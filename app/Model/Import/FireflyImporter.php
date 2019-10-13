<?php

namespace App\Model\Import;

use App\Model\Account;
use App\Model\Category;
use DateTime;
use Illuminate\Support\Facades\Log;
use LimitIterator;
use SplFileObject;
use Symfony\Component\HttpFoundation\File\File;

class FireflyImporter implements Importer {
    const SKIP_HEADER_LINES = 1;
    const COLUMN_AMOUNT = 0;
    const COLUMN_ACCOUNT_IBAN = 2;
    const COLUMN_ACCOUNT_NAME = 4;
    const COLUMN_CATEGORY = 12;
    const COLUMN_DATE = 14;
    const COLUMN_DESCRIPTION = 15;
    const COLUMN_OPPOSING_ACCOUNT_IBAN = 21;
    const COLUMN_OPPOSING_ACCOUNT_NAME = 23;
    const COLUMN_TRANSACTION_TYPE = 28;
    const TRANSACTION_TYPE_TRANSFER = 'Transfer';

    private $categoriesByKey;
    private $accountsByIban;

    public function init() {
        $this->categoriesByKey = Category::get()->keyBy('key');
        $this->accountsByIban = Account::get()->keyBy('iban');
    }

    public function import(File $file) {
        if($this->categoriesByKey == null || $this->accountsByIban == null) {
            throw new Exception("Cannot import with uninitialized importer. Call init() first");
        }

        Log::info("Start importing", ["filename" => $file->getFilename(), "time" => new DateTime()]);
        $transactions = [];

        // Read file as CSV file
        $splFile = $file->openFile();
        $splFile->setFlags(SplFileObject::READ_CSV);

        // Skip first line of CSV file
        $transactionData = new LimitIterator($splFile, self::SKIP_HEADER_LINES);

        foreach ($transactionData as $idx => $row) {
            if(count($row) < 2) {
                Log::debug("Skipping line #" . $idx . " as it contains only " . count($row) . " fields", $row);
                continue;
            }
            $account = $this->getAccount($row[self::COLUMN_ACCOUNT_IBAN]);

            if(!$account) {
                Log::warning("Skipping import for row #" . $idx . " - account with IBAN " . $row[self::COLUMN_ACCOUNT_IBAN] . " is unknown", $row);
                continue;
            }

            $category = $this->getCategory($row[self::COLUMN_CATEGORY]);
            $categoryId = $category ? $category->id : null;

            if(!$category) {
                Log::warning("Importing row #" . $idx . " without category - category " . $row[self::COLUMN_CATEGORY] . " is unknown", $row);
            }

            $date = $this->parseDate($row[self::COLUMN_DATE]);

            $transactions[] = [
                "amount" => $row[self::COLUMN_AMOUNT],
                "account_id" => $account->id,
                "category_id" => $categoryId,
                "date" => $date,
                "description" => $row[self::COLUMN_DESCRIPTION],
                "opposing_account_iban" => $row[self::COLUMN_OPPOSING_ACCOUNT_IBAN],
                "opposing_account_name" => $row[self::COLUMN_OPPOSING_ACCOUNT_NAME],
            ];

            if($row[self::COLUMN_TRANSACTION_TYPE] == self::TRANSACTION_TYPE_TRANSFER) {
                $opposingAccount = $this->getAccount($row[self::COLUMN_OPPOSING_ACCOUNT_IBAN]);

                if(!$opposingAccount) {
                    Log::warning("Could not correctly store transfer on row #" . $idx . " - opposing account with IBAN " . $row[self::COLUMN_OPPOSING_ACCOUNT_IBAN] . " is unknown", $row);
                    continue;
                }

                // Add opposing transaction as well
                $transactions[] = [
                    "amount" => -$row[self::COLUMN_AMOUNT],
                    "account_id" => $opposingAccount->id,
                    "category_id" => $categoryId,
                    "date" => $date,
                    "description" => $row[self::COLUMN_DESCRIPTION],
                    "opposing_account_iban" => $row[self::COLUMN_ACCOUNT_IBAN],
                    "opposing_account_name" => $row[self::COLUMN_ACCOUNT_NAME]
                ];
            }
        }
    }

    private function getAccount(string $iban): ?Account {
        return $this->accountsByIban->get($iban);
    }

    private function getCategory(string $key): ?Category {
        return $this->categoriesByKey->get($key);
    }

    private function parseDate(string $date): DateTime {
        return DateTime::createFromFormat("Ymd", $date);
    }

}
