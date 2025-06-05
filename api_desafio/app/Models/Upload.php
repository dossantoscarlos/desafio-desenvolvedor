<?php
declare(strict_types= 1);

namespace App\Models;

use ErrorException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
    ];
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

        self::create([
            'file_path' => $path,
            'name_file' => $file->getClientOriginalName(),
            'date_upload'=> now()->format('Y-m-d'),
            'hash_file' => $hash
        ]);

    }
}
