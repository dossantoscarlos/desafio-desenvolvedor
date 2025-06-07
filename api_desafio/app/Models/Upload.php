<?php
declare(strict_types= 1);

namespace App\Models;

use App\Jobs\ProcessDataConsolidateFile;
use ErrorException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="Upload",
 *     title="Upload",
 *     description="Modelo para gerenciamento de uploads de arquivos",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID único do upload"),
 *     @OA\Property(property="file_path", type="string", description="Caminho do arquivo no storage"),
 *     @OA\Property(property="name_file", type="string", description="Nome original do arquivo"),
 *     @OA\Property(property="date_upload", type="string", format="date", description="Data do upload"),
 *     @OA\Property(property="hash_file", type="string", description="Hash SHA256 do arquivo para verificação de duplicidade"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Data de criação do registro"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Data da última atualização do registro")
 * )
 */
class Upload extends Model
{
    /** @use HasFactory<\Database\Factories\UploadFactory> */
    use HasFactory;

    protected $table = 'uploads';
    protected $primaryKey = 'id';

    protected $fillable = [
        'file_path',
        'name_file',
        'date_upload',
        'hash_file',
        'extension_file'
    ];

    /**
     * Realiza o upload de um arquivo
     * 
     * @param UploadedFile $file Arquivo a ser enviado
     * @throws ErrorException Quando o arquivo já existe
     * @return void
     */
    public static function upload_file(UploadedFile $file) : void 
    {
        $path = Storage::disk(name: 'local')->put('uploads', $file);
        $hash = hash_file(algo: "sha256", filename: $file->getRealPath());

        $result = self::where('hash_file', $hash)->get();

        if (!$result->isEmpty()) {
            $store = Storage::disk('local')->delete(strval($path));
            Log::debug("deletado arquivo", [$store]);
            throw new ErrorException('Arquivo existente');
        }

        $mimeType = $file->getMimeType();

        $listMimeType= [
            'text/csv' => 'csv',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=> 'xlsx'
        ];

        $name_file = explode(".", $file->getClientOriginalName());

        self::create([
            'file_path' => $path,
            'name_file' => mb_strtolower($name_file[0]),
            'date_upload'=> now()->format('Y-m-d'),
            'hash_file' => $hash,
            'extension_file' => $listMimeType[$mimeType],
        ]);

        ProcessDataConsolidateFile::dispatch($path, $listMimeType[$mimeType]);

        Log::debug('mime Type', [$mimeType]);
    }
}
