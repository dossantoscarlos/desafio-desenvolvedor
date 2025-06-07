<?php

namespace App\Models;

use App\Adapters\ProcessDataFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Schema(
 *     schema="ConsolidateFile",
 *     title="ConsolidateFile",
 *     description="Modelo para armazenamento de dados consolidados de arquivos",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID único do registro"),
 *     @OA\Property(property="RptDt", type="string", format="date", description="Data do relatório"),
 *     @OA\Property(property="TckrSymb", type="string", description="Símbolo do ticker"),
 *     @OA\Property(property="MktNm", type="string", description="Nome do mercado"),
 *     @OA\Property(property="SctyCtgyNm", type="string", description="Categoria do título"),
 *     @OA\Property(property="ISIN", type="string", description="Código ISIN"),
 *     @OA\Property(property="CrpnNm", type="string", description="Nome da corporação"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Data de criação do registro"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Data da última atualização do registro")
 * )
 */
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

    /**
     * Processa o arquivo e salva os dados consolidados
     * 
     * @param ProcessDataFile $process Adaptador para processamento do arquivo
     * @param string $file Caminho do arquivo
     * @param mixed $readerOptions Opções do leitor de arquivo
     * @param mixed $reader Leitor de arquivo
     * @return void
     */
    public function process(ProcessDataFile $process, string $file, mixed $readerOptions, mixed $reader): void
    {
        foreach ($process->processFile($file, $readerOptions, $reader ) as $bloco) {
            foreach($bloco as $line ) {
                Log::info('linha => ', $line);
                self::create($line);
            }
        }
    }
}
