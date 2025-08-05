<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Para depuración

class EntregaRecepcionController extends Controller
{
    /**
     * Muestra la vista de Entrega-Recepción.
     * En un proyecto real, aquí cargarías los documentos desde la base de datos.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // En un proyecto real, cargarías los documentos de la base de datos:
        // use App\Models\Documento;
        // $documentos = Documento::all(); 
        // return view('entrega-recepcion.index', compact('documentos'));

        // Para este ejemplo, simulamos algunos documentos iniciales
        $documentos = [
            ['codigo' => 'COD-001', 'tipo_documento' => 'Oficio', 'fecha_recepcion' => '01/01/2024, 10:00', 'estado' => 'Recibido'],
            ['codigo' => 'SEN-002', 'tipo_documento' => 'Sentencia', 'fecha_recepcion' => '01/01/2024, 11:30', 'estado' => 'Recibido'],
            ['codigo' => 'RAD-003', 'tipo_documento' => 'Radicacion', 'fecha_recepcion' => '01/01/2024, 14:00', 'estado' => 'Recibido'],
        ];

        return view('entrega-recepcion.index', compact('documentos'));
    }

    /**
     * Maneja la lógica para registrar o actualizar un documento.
     * Este método sería llamado por una petición AJAX desde el frontend.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeOrUpdate(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'codigo' => 'required|string|max:255',
            'tipo_documento' => 'required|in:Oficio,Sentencia,Radicacion',
            'estado' => 'required|in:Recibido,Pendiente,Actualizar',
        ]);

        // Simulación de guardar/actualizar en la base de datos
        // En un proyecto real, usarías Eloquent de la siguiente manera:
        /*
        use App\Models\Documento; // Asegúrate de importar tu modelo Documento

        $documento = Documento::updateOrCreate(
            ['codigo' => $request->codigo], // Busca por código, si existe actualiza, si no, crea
            [
                'tipo_documento' => $request->tipo_documento,
                'estado' => $request->estado,
                'fecha_recepcion' => now(), // Laravel se encarga de las fechas
            ]
        );
        */

        Log::info('Documento procesado:', $request->all());

        return response()->json([
            'message' => 'Documento procesado con éxito.',
            'data' => [
                'codigo' => $request->codigo,
                'tipo_documento' => $request->tipo_documento,
                'estado' => $request->estado,
                'fecha_recepcion' => now()->format('d/m/Y, H:i'), // Formato para la respuesta
            ]
        ]);
    }

    /**
     * Maneja la lógica para eliminar un documento.
     * Este método sería llamado por una petición AJAX desde el frontend.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:255',
        ]);

        // Simulación de eliminación en la base de datos
        // En un proyecto real, usarías Eloquent:
        // use App\Models\Documento; // Asegúrate de importar tu modelo Documento
        // Documento::where('codigo', $request->codigo)->delete();

        Log::info('Documento eliminado:', ['codigo' => $request->codigo]);

        return response()->json(['message' => 'Documento eliminado con éxito.']);
    }
}
