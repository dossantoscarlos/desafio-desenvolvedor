<?php

namespace App\Adapters;

use Generator;

interface ProcessDataFile
{
    public function processFile(string $fileUrl, mixed $option, mixed $reader): Generator;
}
