<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PendienteController;
use App\Http\Controllers\ExpedienteController;

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

Route::get('/', [DocumentController::class, 'index'])->name('home');

// Rutas para el sistema de documentos
Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [DocumentController::class, 'index'])->name('index');
    Route::post('/', [DocumentController::class, 'store'])->name('store');
    Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
    Route::put('/{document}', [DocumentController::class, 'update'])->name('update');
    Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
    Route::get('/search/ajax', [DocumentController::class, 'search'])->name('search');
    Route::get('/search-by-code/{code}', [DocumentController::class, 'searchByCode'])->name('documents.searchByCode');

});

// Ruta alternativa si prefieres que sea la ruta principal
// Route::get('/', [DocumentController::class, 'index'])->name('home');

// pendiente
Route::get('/pendientes', [PendienteController::class, 'index'])->name('pendientes');
Route::put('/documents/{id}/status', [DocumentController::class, 'updateStatus']);
//-----------------------------------------------------------------------------------------

Route::resource('expedientes', ExpedienteController::class);
Route::get('/expedientes-sistema', [ExpedienteController::class, 'create'])->name('expedientes.sistema');
Route::get('/api/expedientes/buscar', [ExpedienteController::class, 'buscar'])->name('expedientes.buscar');
Route::get('/api/expedientes/{expediente}/detalles', [ExpedienteController::class, 'obtenerDetalles'])->name('expedientes.detalles');
Route::get('/expedientes/{expediente}/descargar-pdf', [ExpedienteController::class, 'descargarPdf'])->name('expedientes.descargar-pdf');
Route::put('/documents/{document}/description', [DocumentController::class, 'updateDescription']);
Route::get('/expediente/archivo/{id}', [ExpedienteController::class, 'obtenerArchivo']);
Route::get('/expedientes/{expediente}/pdf', [ExpedienteController::class, 'mostrarPdf'])->name('expedientes.pdf');
Route::post('/expedientes/tree/store', [ExpedienteController::class, 'storeTree'])->name('expedientes.tree.store');
Route::get('/api/expedientes/{id}/tree', [ExpedienteController::class, 'getTree']);


