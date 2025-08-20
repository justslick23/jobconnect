<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JobRequisitionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('job-requisitions')->group(function () {
    Route::get('/', [JobRequisitionController::class, 'index']);
    Route::get('/departments', [JobRequisitionController::class, 'departments']);
    Route::get('/statistics', [JobRequisitionController::class, 'statistics']);
    Route::get('/{slugUuid}', [JobRequisitionController::class, 'show']);
});
