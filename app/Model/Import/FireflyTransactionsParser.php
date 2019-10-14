<?php

namespace App\Model\Import;

use App\Model\Account;
use App\Model\Category;
use DateTime;
use Illuminate\Support\Facades\Log;
use LimitIterator;
use SplFileObject;
use Symfony\Component\HttpFoundation\File\File;

class FireflyTransactionsParser implements TransactionFileParser {
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
    const MAX_DESCRIPTION_LENGTH = 2000;

    private $categoriesByKey;
    private $accountsByIban;

    public function init() {
        $this->categoriesByKey = Category::get()->keyBy(function($category) { return strtolower($category['key']); });
        $this->accountsByIban = Account::get()->keyBy('iban');
    }

    public function parse(File $file): array {
        if($this->categoriesByKey == null || $this->accountsByIban == null) {
            throw new \Exception("Cannot import with uninitialized importer. Call init() first");
        }

        Log::info("Start importing", ["filename" => $file->getFilename(), "time" => new DateTime()]);
        $transactions = [];
        $numRows = 0;

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

            $numRows++;

            $account = $this->getAccount($row[self::COLUMN_ACCOUNT_IBAN]);

            if(!$account) {
                Log::warning("Skipping import for row #" . $idx . " - account with IBAN " . $row[self::COLUMN_ACCOUNT_IBAN] . " is unknown", $row);
                continue;
            }

            $providedCategory = trim($row[self::COLUMN_CATEGORY]);
            $categoryId = null;

            if($providedCategory) {
                $category = $this->getCategory($row[self::COLUMN_CATEGORY]);

                if ($category) {
                    $categoryId = $category->id;
                } else {
                    Log::warning("Importing row #" . $idx . " without category - category " . $row[self::COLUMN_CATEGORY] . " is unknown", $row);
                }
            }

            $description = $row[self::COLUMN_DESCRIPTION];
            if(strlen($description) > self::MAX_DESCRIPTION_LENGTH) {
                Log::warning("Truncating description for line #" . $idx . " to 255 characters", $row);
                $description = substr($description, 0, self::MAX_DESCRIPTION_LENGTH);
            }


            $date = $this->parseDate($row[self::COLUMN_DATE]);

            $transactions[] = [
                "amount" => $row[self::COLUMN_AMOUNT],
                "account_id" => $account->id,
                "category_id" => $categoryId,
                "date" => $date,
                "description" => $description,
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
                    "description" => $description,
                    "opposing_account_iban" => $row[self::COLUMN_ACCOUNT_IBAN],
                    "opposing_account_name" => $row[self::COLUMN_ACCOUNT_NAME]
                ];
            }
        }

        Log::info("Recognized " . count($transactions) . " transactions from " . $numRows . " rows of CSV data");

        return $transactions;
    }

    private function getAccount(string $iban): ?Account {
        return $this->accountsByIban->get($iban);
    }

    private function getCategory(string $key): ?Category {
        return $this->categoriesByKey->get(strtolower($key));
    }

    private function parseDate(string $date): DateTime {
        return DateTime::createFromFormat("Ymd", $date);
    }

}
