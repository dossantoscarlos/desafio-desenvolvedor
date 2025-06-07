<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreUploadRequest;
use App\Http\Requests\UpdateUploadRequest;
use App\Models\Upload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API de Upload de Arquivos",
 *     description="API para upload e processamento de arquivos CSV e XLSX",
 *     @OA\Contact(
 *         email="seu-email@exemplo.com"
 *     )
 * )
 */
class UploadController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/uploads",
     *     summary="Lista todos os uploads",
     *     tags={"Uploads"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de uploads",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="file_path", type="string"),
     *                     @OA\Property(property="name_file", type="string"),
     *                     @OA\Property(property="date_upload", type="string"),
     *                     @OA\Property(property="hash_file", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request) : JsonResponse
    {
        $validated = $request->validate([
            'date_upload'     => 'nullable|date_format:Y-m-d',
            'name_file'       => 'nullable|string',
        ]);
    
        if (empty($validated['date_upload']) && empty($validated['name_file'])) {
            $result = Upload::paginate(50, ['*'], 'page', null);
            return response()->json($result, Response::HTTP_OK);
        }

        $query = Upload::query();
    
        if (!empty($validated['name_file'])) {
            $query->where('name_file', mb_strtolower($validated['name_file']));
        }
    
        if (!empty($validated['date_upload'])) {
            $query->where('date_upload', $validated['date_upload']);
        }
    
        $results = $query->get();
    
        if ($results->isEmpty()) {
            return response()->json(['message' => 'Not Found'], Response::HTTP_NOT_FOUND);
        }
        
        return response()->json($results, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/uploads",
     *     summary="Realiza upload de arquivo",
     *     tags={"Uploads"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="file",
     *                     description="Arquivo CSV ou XLSX (máx. 150MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Upload realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="upload com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro no upload",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Arquivo inválido")
     *         )
     *     )
     * )
     */
    public function store(StoreUploadRequest $request): JsonResponse
    {
        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return response()
                    ->json(['message' => 'Arquivo invalido'], 
                    Response::HTTP_BAD_REQUEST);
        }

        $file = $request->file('file');

        try {
            Upload::upload_file($file);
        } catch (\Exception $e) {
            Log::error("Error hash: ", [ $e->getMessage() ]);

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }

        return response()->json([
            "message" => "upload com sucesso"
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/uploads/{id}",
     *     summary="Remove um upload",
     *     tags={"Uploads"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do upload",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Upload removido com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Upload não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Arquivo inexistente")
     *         )
     *     )
     * )
     */
    public function destroy(Upload $upload) : JsonResponse
    {
        $model = Upload::find($upload->id);

        if (empty($model)) {
            return response()->json([
                    "message"=> "Arquivo inexistente",
                ], 
                Response::HTTP_FOUND
            );
        } 
        
        $model->delete();

        return response()->json(null, 204);
    }
}
