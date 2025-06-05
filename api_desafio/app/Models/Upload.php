<?php
declare(strict_types= 1);

namespace App\Models;

use ErrorException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Upload extends Model
{
    /** @use HasFactory<\Database\Factories\UploadFactory> */
    use HasFactory;

    protected $table = 'uploads';
    protected $primaryKey = 'id';

    protected $fillable = [
        'file_path',
        'name',
        'date_upload',
        'hash_upload',
    ];
    public static function upload_file(mixed $file) : void 
    {

        $path = Storage::disk(name: 'local')->put('uploads', $file);

        // self::create([
        //     'file_path' => $path,
        //     'name' => $file->getClientOriginalName(),
        //     'date_upload'=> now()->toDateString(),
        //     'hash_upload' => hash_file("sha256", $file, true)
        // ]);
    


    }
}
