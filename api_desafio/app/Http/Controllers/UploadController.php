<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreUploadRequest;
use App\Models\Upload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;


class UploadController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
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
     * Remove the specified resource from storage.
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
