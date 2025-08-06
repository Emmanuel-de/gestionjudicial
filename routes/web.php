<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PendienteController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome'); 
});

// Rutas para el sistema de documentos
Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [DocumentController::class, 'index'])->name('index');
    Route::post('/', [DocumentController::class, 'store'])->name('store');
    Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
    Route::put('/{document}', [DocumentController::class, 'update'])->name('update');
    Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
    Route::get('/search/ajax', [DocumentController::class, 'search'])->name('search');
    
});

// Ruta alternativa si prefieres que sea la ruta principal
// Route::get('/', [DocumentController::class, 'index'])->name('home');

// pendiente
Route::get('/pendientes', [PendienteController::class, 'index'])->name('pendientes');
Route::put('/documents/{id}/status', [DocumentController::class, 'updateStatus']);