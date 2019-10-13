<?php

namespace App\Model\Import;

class ImporterFactory {
    const TYPE_FIREFLY = "firefly";

    public static function build(string $type): ?Importer {
        switch($type) {
            case self::TYPE_FIREFLY:
                $importer = new FireflyImporter();
                $importer->init();
                return $importer;
        }

        return null;
    }
}
