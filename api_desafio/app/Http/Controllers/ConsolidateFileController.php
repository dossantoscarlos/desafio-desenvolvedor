<?php

namespace App\Http\Controllers;

use App\Models\ConsolidateFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *     name="Consolidate",
 *     description="Endpoints para dados consolidados"
 * )
 */
class ConsolidateFileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/consolidate",
     *     summary="Lista dados consolidados",
     *     tags={"Consolidate"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de dados consolidados",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="RptDt", type="string", format="date"),
     *                     @OA\Property(property="TckrSymb", type="string"),
     *                     @OA\Property(property="MktNm", type="string"),
     *                     @OA\Property(property="SctyCtgyNm", type="string"),
     *                     @OA\Property(property="ISIN", type="string"),
     *                     @OA\Property(property="CrpnNm", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request):JsonResponse
    {
        $validated = $request->validate([
            'RptDt'     => 'nullable|date_format:Y-m-d',
            'TckrSymb'  => 'nullable|string',
        ]);
    
        
        if (empty($validated['RptDt']) && empty($validated['TckrSymb'])) {
            $result = ConsolidateFile::paginate(50, ['*'], 'page', null);
            return response()->json($result, Response::HTTP_OK);
        }

        $query = ConsolidateFile::query();
    
        if (!empty($validated['RptDt'])) {
            $query->where('RptDt', $validated['RptDt']);
        }
    
        if (!empty($validated['TckrSymb'])) {
            $query->where('TckrSymb', $validated['TckrSymb']);
        }
    
        $results = $query->get();
    
        if ($results->isEmpty()) {
            return response()->json(['message' => 'Not Found'], Response::HTTP_NOT_FOUND);
        }
    
        return response()->json($results, Response::HTTP_OK);
    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConsolidateFile $consolidateFile)
    {

        $consolidateFile->deleteOrFail();

        return response()->json([
            'message'=> 'Excluido com sucesso.'
        ],
        Response::HTTP_NO_CONTENT);
    }
}
