<?php

namespace App\Jobs;

use App\Entities\CsvFile;
use App\Entities\XlsxFile;
use App\Models\ConsolidateFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;

class ProcessDataConsolidateFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // public $timeout = 60 * 10;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $path , public string $mimeType)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $consolidateFile = new ConsolidateFile;
        
        $option = null;
        $reader = null;

        if (Str::contains($this->mimeType, "csv")) {
            $option = new CSVOptions;
            $reader = new CsvReader($option);
            $consolidateFile->process(new CsvFile, $this->path, $option, $reader );
        } elseif (Str::contains($this->mimeType, "xlsx") || Str::contains($this->mimeType,"xls")) {
            $option = new Options;
            $reader = new Reader($option); 
            $consolidateFile->process(new XlsxFile, $this->path, $option, $reader );
        }

    }
}
