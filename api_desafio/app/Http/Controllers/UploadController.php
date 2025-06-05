<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreUploadRequest;
use App\Http\Requests\UpdateUploadRequest;
use App\Models\Upload;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;


class UploadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() : JsonResponse
    {
        $uploads = Upload::paginate(50, ['*'], 'page', null);

        return response()->json($uploads);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUploadRequest $request): JsonResponse
    {

        // Log::debug(ini_get('upload_max_filesize'), []);
        // Log::debug(ini_get('post_max_size'), []);

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
     * Display the specified resource.
     */
    public function show(Upload $upload): JsonResponse
    {
        $upload = Upload::find($upload->id);

        return response()->json($upload);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUploadRequest $request, Upload $upload): JsonResponse    
    {
        $upload = Upload::find($upload->id);
        $upload->update($request->all());

        return response()->json($upload);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Upload $upload) : JsonResponse
    {
        $upload = Upload::find($upload->id);
        $upload->delete();

        return response()->json(null, 204);
    }
}
