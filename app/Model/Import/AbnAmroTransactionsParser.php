<?php

namespace App\Model\Import;

use App\Model\Account;
use App\Model\Category;
use DateTime;
use Illuminate\Support\Facades\Log;
use SplFileObject;
use Symfony\Component\HttpFoundation\File\File;

class AbnAmroTransactionsParser implements TransactionFileParser {
    const COLUMN_ACCOUNT = 0;
    const COLUMN_DATE = 2;
    const COLUMN_AMOUNT = 6;
    const COLUMN_DESCRIPTION = 7;

    const MAX_DESCRIPTION_LENGTH = 2000;
    const MAX_ACCOUNT_NAME_LENGTH = 255;
    const CATEGORY_KEY_TRANSFERS = 'overboekingen';
    const OPPOSING_ACCOUNT_NAME = 'opposing_account_name';
    const OPPOSING_ACCOUNT_IBAN = 'opposing_account_iban';
    const DESCRIPTION = 'description';

    private $accountsByAccountNo;
    private $transferCategory;

    public function init() {
        $this->transferCategory = Category::where('key', '=', self::CATEGORY_KEY_TRANSFERS)->first();
        $this->accountsByAccountNo = Account::get()->keyBy(function(Account $account) {
            return $this->getAccountNoFromIban($account->iban);
        });
    }

    public function parseFile(File $file): array {
        Log::info("Start importing ABN AMRO file", ["filename" => $file->getFilename(), "time" => new DateTime()]);

        // Read file as CSV file
        $splFile = $file->openFile();
        $splFile->setFlags(SplFileObject::READ_CSV);
        $splFile->setCsvControl("\t");

        return $this->parse($splFile);
    }

    public function parse(\Iterator $iterator): array {
        $transactions = [];
        $numRows = 0;

        if($this->accountsByAccountNo == null) {
            throw new \Exception("Cannot import with uninitialized importer. Call init() first");
        }

        foreach ($iterator as $idx => $row) {
            if (count($row) < 2) {
                Log::debug("Skipping line #" . $idx . " as it contains only " . count($row) . " fields", $row);
                continue;
            }

            $numRows++;

            $account = $this->getAccount($row[self::COLUMN_ACCOUNT]);

            if (!$account) {
                Log::warning("Skipping import for row #" . $idx . " - account with number " . $row[self::COLUMN_ACCOUNT] . " is unknown", $row);
                continue;
            }

            $rawDescription = $row[self::COLUMN_DESCRIPTION];

            // Parse ABN Amro description field for certain specific formats
            // If none of the formats are found, use a default
            $transactionInfo = $this->parseSepaDescription($rawDescription)
                ?? $this->parseTRTPDescription($rawDescription)
                ?? $this->parseGEABEADescription($rawDescription)
                ?? $this->parseABNAMRODescription($rawDescription);

            if(!$transactionInfo) {
                Log::warning("Description for line #" . $idx . " could not be parsed.", $row);
                $transactionInfo = $this->transactionInfo('', '', $rawDescription);
            }

            if(strlen($transactionInfo[self::DESCRIPTION]) > self::MAX_DESCRIPTION_LENGTH) {
                Log::warning("Truncating description for line #" . $idx . " to " . self::MAX_DESCRIPTION_LENGTH . " characters", $row);
                $transactionInfo[self::DESCRIPTION] = substr($transactionInfo[self::DESCRIPTION], 0, self::MAX_DESCRIPTION_LENGTH);
            }

            if(strlen($transactionInfo[self::OPPOSING_ACCOUNT_NAME]) > self::MAX_ACCOUNT_NAME_LENGTH) {
                Log::warning("Truncating account name for line #" . $idx . " to " . self::MAX_ACCOUNT_NAME_LENGTH . " characters", $row);
                $transactionInfo[self::OPPOSING_ACCOUNT_NAME] = substr($transactionInfo[self::OPPOSING_ACCOUNT_NAME], 0, self::MAX_ACCOUNT_NAME_LENGTH);
            }

            // A transfer is added to the transfer category
            $categoryId = $this->isTransfer($transactionInfo) ? $this->transferCategory->id : null;

            $date = $this->parseDate($row[self::COLUMN_DATE]);
            $amount = $this->parseAmount($row[self::COLUMN_AMOUNT]);

            $transactions[] = array_merge(
                [
                    "amount" => $amount,
                    "account_id" => $account->id,
                    "category_id" => $categoryId,
                    "date" => $date
                ],
                $transactionInfo
            );
        }

        Log::info("Recognized " . count($transactions) . " transactions from " . $numRows . " rows of CSV data");

        return $transactions;

    }

    private function getAccount(string $accountNo): ?Account {
        return $this->accountsByAccountNo->get($accountNo);
    }

    private function parseDate(string $date): DateTime {
        return DateTime::createFromFormat("Ymd", $date);
    }

    private function parseAmount(string $amount): float {
        return floatval(str_replace(',', '.', $amount));
    }

    private function isTransfer(array $transactionInfo): bool {
        // This transaction is a transfer if the opposing iban is known as account
        if($transactionInfo[self::OPPOSING_ACCOUNT_IBAN]) {
            return $this->getAccount($this->getAccountNoFromIban($transactionInfo[self::OPPOSING_ACCOUNT_IBAN])) != null;
        }

        return false;
    }

    /**
     * Parses the current description with costs from ABN AMRO itself.
     *
     * @return array containing information on the description if description is in ABNAMRO format, empty array otherwise
     */
    protected function parseABNAMRODescription(string $description): ?array
    {
        // See if the current description is formatted in ABN AMRO format
        if (preg_match('/ABN AMRO.{24} (.*)/', $description, $matches)) {
            return $this->transactionInfo('ABN AMRO', '', $matches[1]);
        }

        return null;
    }
    /**
     * Parses the current description in GEA/BEA format.
     *
     * @return array containing information on the description if description is in GEA/BEA format, empty array otherwise
     */
    protected function parseGEABEADescription(string $description): ?array
    {
        // See if the current description is formatted in GEA/BEA format
        if (preg_match('/([BG]EA) +(NR:[a-zA-Z:0-9]+) +([0-9.\/]+) +([^,]*)/', $description, $matches)) {
            // description and opposing account will be the same.
            return $this->transactionInfo($matches[4], '', 'GEA' === $matches[1] ? 'GEA ' . $matches[4] : $matches[4]);
        }
        return null;
    }
    /**
     * Parses the current description in SEPA format.
     *
     * @return array containing information on the description if description is in SEPA format, empty array otherwise
     */
    protected function parseSepaDescription(string $description): ?array
    {
        // See if the current description is formatted as a SEPA plain description
        if (preg_match('/^SEPA(.{28})/', $description, $matches)) {
            $type = trim($matches[1]);

            switch(strtolower($type)) {
                case 'ideal':
                    $parsed = $this->parseSepaDescriptionField($description, ["IBAN", "BIC", "Naam", "Omschrijving", "Kenmerk"]);
                    break;
                case 'incasso algemeen doorlopend':
                case 'incasso algemeen eenmalig':
                    $parsed = $this->parseSepaDescriptionField($description, ["Incassant", "Naam", "Machtiging", "Omschrijving", "IBAN", "Kenmerk", "Voor"]);
                    break;
                case 'overboeking':
                case 'periodieke overb.':
                    $parsed = $this->parseSepaDescriptionField($description, ["IBAN", "BIC", "Naam", "Omschrijving"]);
                    break;
                default:
                    Log::warning("Sepa transfer found without known type: " . $type);
                    return null;
            }

            return $this->transactionInfo($parsed["Naam"], $parsed["IBAN"], $type . " " . $parsed['Omschrijving']);
        }
        return null;
    }

    /**
     * Parses the given list of fields from a SEPA description field. The SEPA description field seems to have
     * a fixes set of field names in a string.
     *
     * @param string $description
     * @param array $array
     */
    private function parseSepaDescriptionField(string $description, array $fields)
    {
        $parsed = [
            "Naam" => "",
            "IBAN" => "",
            "Omschrijving" => ""
        ];
        $start = $this->getValueStart($description, $fields[0]);

        if($start == -1) {
            Log::warning("Trying to parse SEPA description, but first field " . $fields[0] . " was not found", compact('description', 'fields'));
            return [];
        }

        Log::info("Parsing [" . $description . "] with fields", $fields);

        for($i = 0; $i < count($fields); $i++) {
            $field = $fields[$i];

            if($i < count($fields) -1) {
                $next = $fields[$i + 1];
                $nextpos = strpos($description, $next, $start);

                // If the next field is not found, use the rest of the description
                if($nextpos === false) {
                    Log::debug("Expected SEPA field " . $next . " was not found in description.", compact('description', 'next'));
                    $value = substr($description, $start);
                    $parsed[$field] = trim($value);

                    // Stop parsing this description
                    break;
                } else {
                    $value = substr($description, $start, $nextpos - $start - 1);
                    $parsed[$field] = trim($value);
                }

                $start = $nextpos + strlen($next) + 1;
            } else {
                $value = substr($description, $start);
                $parsed[$field] = trim($value);
            }
        }

        Log::info("Parsed " . $description, $parsed);

        return $parsed;
    }

    function getValueStart(string $description, string $field, int $offset = 0) {
        $pos = strpos($description, $field, $offset);

        return $pos >= 0 ? $pos + strlen($field) + 1 : -1;
    }

    /**
     * Parses the current description in TRTP format.
     *
     * @return array containing information on the description if description is in TRTP format, empty array otherwise
     *
     */
    protected function parseTRTPDescription(string $description): ?array
    {
        // See if the current description is formatted in TRTP format
        if (preg_match_all('!\/([A-Z]{3,4})\/([^/]*)!', $description, $matches, PREG_SET_ORDER)) {
            $type = '';
            $name = '';
            $reference = '';
            $newDescription = '';
            $iban = '';
            // Search for properties specified in the TRTP format. If no description
            // is provided, use the type, name and reference as new description
            if (is_array($matches)) {
                foreach ($matches as $match) {
                    $key   = $match[1];
                    $value = trim($match[2]);
                    switch (strtoupper($key)) {
                        case 'NAME':
                            $name = $value;
                            break;
                        case 'REMI':
                            $newDescription = $value;
                            break;
                        case 'IBAN':
                            $iban = $value;
                            break;
                        case 'EREF':
                            $reference = $value;
                            break;
                        case 'TRTP':
                            $type = trim(str_replace("SEPA", "", $value));
                            break;
                        default: // @codeCoverageIgnore
                            // Ignore the rest
                    }
                }

                if(!$newDescription) {
                    $newDescription = sprintf('%s (%s)', $name, $reference);
                }

                return $this->transactionInfo($name, $iban, $type . ' ' . $newDescription);
            }
        }
        return null;
    }

    /**
     * @param $matches
     * @return array
     */
    private function transactionInfo($opposingAccountName, $opposingAccountIban, $description): array
    {
        return [
            self::OPPOSING_ACCOUNT_NAME => $opposingAccountName,
            self::OPPOSING_ACCOUNT_IBAN => $opposingAccountIban,
            self::DESCRIPTION => $description
        ];
    }

    /**
     * @param Account $account
     * @return bool|string
     */
    private function getAccountNoFromIban(string $iban)
    {
        return substr($iban, -9);
    }

    /**
     * @return mixed
     */
    public function getAccountsByAccountNo()
    {
        return $this->accountsByAccountNo;
    }

    /**
     * @param mixed $accountsByAccountNo
     */
    public function setAccountsByAccountNo($accountsByAccountNo): void
    {
        $this->accountsByAccountNo = $accountsByAccountNo;
    }

    /**
     * @return mixed
     */
    public function getTransferCategory()
    {
        return $this->transferCategory;
    }

    /**
     * @param mixed $transferCategory
     */
    public function setTransferCategory($transferCategory): void
    {
        $this->transferCategory = $transferCategory;
    }


}
