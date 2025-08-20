<?php

Log::info('API routes file loaded');

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

Route::prefix('job-requisitions')->group(function () {
    Route::get('/', [JobRequisitionController::class, 'index']);
    Route::get('/departments', [JobRequisitionController::class, 'departments']);
    Route::get('/statistics', [JobRequisitionController::class, 'statistics']);
    Route::get('/{slugUuid}', [JobRequisitionController::class, 'show']);
});
Route::get('/test', function() { return 'Test'; });