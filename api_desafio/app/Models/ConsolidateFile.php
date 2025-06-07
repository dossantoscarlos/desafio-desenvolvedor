<?php

namespace App\Models;

use App\Adapters\ProcessDataFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ConsolidateFile extends Model
{
    /** @use HasFactory<\Database\Factories\ConsolidateFileFactory> */
    use HasFactory;

    protected $fillable = [
        'RptDt',
        'TckrSymb',
        'MktNm',
        'SctyCtgyNm',
        'ISIN',
        'CrpnNm'
    ];

    public function process(ProcessDataFile $process, string $file, mixed $readerOptions, mixed $reader): void
    {
        foreach ($process->processFile($file, $readerOptions, $reader ) as $bloco) {
            foreach($bloco as $line ) {
                self::create($line);
            }
        }
    }
}
