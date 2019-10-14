<?php

namespace App\Model\Import;

use Symfony\Component\HttpFoundation\File\File;

interface TransactionFileParser {
    function parse(File $file): array;
}
