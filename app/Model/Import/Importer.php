<?php

namespace App\Model\Import;

use Symfony\Component\HttpFoundation\File\File;

interface Importer {
    function import(File $file);
}
