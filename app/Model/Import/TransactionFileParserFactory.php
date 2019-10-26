<?php

namespace App\Model\Import;

class TransactionFileParserFactory {
    const TYPE_FIREFLY = "firefly";
    const TYPE_ABN = "abn";

    public static function build(string $type): ?TransactionFileParser {
        switch($type) {
            case self::TYPE_FIREFLY:
                $parser = new FireflyTransactionsParser();
                $parser->init();
                return $parser;
            case self::TYPE_ABN:
                $parser = new AbnAmroTransactionsParser();
                $parser->init();
                return $parser;
        }

        return null;
    }
}
