<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsolidateFileController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->middleware('auth:sanctum')->group(function () :void {
    Route::group(['prefix' => 'uploads'], function (): void  {
        Route::get('/', [UploadController::class, 'index']);
        Route::post('/', [UploadController::class, 'store']);
        Route::delete('/{upload}', [UploadController::class, 'destroy']);
    });

    Route::group(['prefix'=> 'consolidate'], function () : void {
        Route::get('/', [ConsolidateFileController::class,'index']);
        Route::delete('/{consolidateFile}' , [ConsolidateFileController::class,'destroy']);
    });

});

Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
});
