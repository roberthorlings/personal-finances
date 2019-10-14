<?php

namespace App\Model\Import;

class TransactionFileParserFactory {
    const TYPE_FIREFLY = "firefly";

    public static function build(string $type): ?TransactionFileParser {
        switch($type) {
            case self::TYPE_FIREFLY:
                $parser = new FireflyTransactionsParser();
                $parser->init();
                return $parser;
        }

        return null;
    }
}
