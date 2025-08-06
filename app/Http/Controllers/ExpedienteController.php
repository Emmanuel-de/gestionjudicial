<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ExpedienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expedientes = Expediente::activos()->orderBy('fecha_creacion', 'desc')->get();
        return view('expedientes.index', compact('expedientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $expedientes = Expediente::activos()->orderBy('fecha_creacion', 'desc')->get();
        return view('expedientes.expediente', compact('expedientes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero_expediente' => 'required|string|max:255|unique:expedientes,numero_expediente',
            'tipo_documento' => 'required|string|max:255',
            'fecha_creacion' => 'required|date',
            'descripcion' => 'nullable|string|max:1000',
            'archivo_pdf' => 'nullable|file|mimes:pdf|max:10240' // 10MB máximo
        ], [
            'numero_expediente.required' => 'El número de expediente es obligatorio.',
            'numero_expediente.unique' => 'Este número de expediente ya existe.',
            'tipo_documento.required' => 'El tipo de documento es obligatorio.',
            'fecha_creacion.required' => 'La fecha de creación es obligatoria.',
            'fecha_creacion.date' => 'La fecha de creación debe ser una fecha válida.',
            'archivo_pdf.mimes' => 'El archivo debe ser un PDF.',
            'archivo_pdf.max' => 'El archivo no debe superar los 10MB.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Por favor corrige los errores en el formulario.');
        }

        try {
            $expediente = new Expediente();
            $expediente->numero_expediente = $request->numero_expediente;
            $expediente->tipo_documento = $request->tipo_documento;
            $expediente->fecha_creacion = $request->fecha_creacion;
            $expediente->descripcion = $request->descripcion;

            // Manejar la carga del archivo PDF
            if ($request->hasFile('archivo_pdf')) {
                $archivo = $request->file('archivo_pdf');
                $nombreArchivo = time() . '_' . $request->numero_expediente . '.' . $archivo->getClientOriginalExtension();
                $rutaArchivo = $archivo->storeAs('expedientes', $nombreArchivo, 'public');
                $expediente->archivo_pdf = $rutaArchivo;
            }

            $expediente->save();

            return redirect()->route('expedientes.create')
                ->with('success', 'Expediente creado exitosamente.')
                ->with('expediente_creado', $expediente->numero_expediente);

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrió un error al crear el expediente. Por favor intenta de nuevo.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Expediente $expediente)
    {
        return view('expedientes.show', compact('expediente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expediente $expediente)
    {
        return view('expedientes.edit', compact('expediente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expediente $expediente)
    {
        $validator = Validator::make($request->all(), [
            'numero_expediente' => 'required|string|max:255|unique:expedientes,numero_expediente,' . $expediente->id,
            'tipo_documento' => 'required|string|max:255',
            'fecha_creacion' => 'required|date',
            'descripcion' => 'nullable|string|max:1000',
            'archivo_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'estado' => 'required|in:activo,archivado,cancelado'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Por favor corrige los errores en el formulario.');
        }

        try {
            $expediente->numero_expediente = $request->numero_expediente;
            $expediente->tipo_documento = $request->tipo_documento;
            $expediente->fecha_creacion = $request->fecha_creacion;
            $expediente->descripcion = $request->descripcion;
            $expediente->estado = $request->estado;

            // Manejar la actualización del archivo PDF
            if ($request->hasFile('archivo_pdf')) {
                // Eliminar el archivo anterior si existe
                if ($expediente->archivo_pdf && Storage::disk('public')->exists($expediente->archivo_pdf)) {
                    Storage::disk('public')->delete($expediente->archivo_pdf);
                }

                $archivo = $request->file('archivo_pdf');
                $nombreArchivo = time() . '_' . $request->numero_expediente . '.' . $archivo->getClientOriginalExtension();
                $rutaArchivo = $archivo->storeAs('expedientes', $nombreArchivo, 'public');
                $expediente->archivo_pdf = $rutaArchivo;
            }

            $expediente->save();

            return redirect()->route('expedientes.index')
                ->with('success', 'Expediente actualizado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrió un error al actualizar el expediente. Por favor intenta de nuevo.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expediente $expediente)
    {
        try {
            // Eliminar el archivo PDF si existe
            if ($expediente->archivo_pdf && Storage::disk('public')->exists($expediente->archivo_pdf)) {
                Storage::disk('public')->delete($expediente->archivo_pdf);
            }

            $expediente->delete();

            return redirect()->route('expedientes.index')
                ->with('success', 'Expediente eliminado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ocurrió un error al eliminar el expediente. Por favor intenta de nuevo.');
        }
    }

    /**
     * Búsqueda AJAX de expedientes
     */
    public function buscar(Request $request)
    {
        $termino = $request->get('q');
        
        $expedientes = Expediente::activos()
            ->buscarPorNumero($termino)
            ->orWhere('tipo_documento', 'like', '%' . $termino . '%')
            ->orWhere('descripcion', 'like', '%' . $termino . '%')
            ->limit(10)
            ->get();

        return response()->json($expedientes);
    }

    /**
     * Obtener detalles de un expediente específico (AJAX)
     */
    public function obtenerDetalles($id)
    {
        try {
            $expediente = Expediente::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'expediente' => [
                    'id' => $expediente->id,
                    'numero_expediente' => $expediente->numero_expediente,
                    'tipo_documento' => $expediente->tipo_documento,
                    'fecha_creacion' => $expediente->fecha_creacion_formatted,
                    'descripcion' => $expediente->descripcion,
                    'archivo_pdf' => $expediente->archivo_pdf ? Storage::url($expediente->archivo_pdf) : null,
                    'estado' => $expediente->estado
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Expediente no encontrado.'
            ], 404);
        }
    }

    /**
     * Descargar archivo PDF del expediente
     */
    public function descargarPdf(Expediente $expediente)
    {
        if (!$expediente->archivo_pdf || !Storage::disk('public')->exists($expediente->archivo_pdf)) {
            return redirect()->back()->with('error', 'El archivo PDF no existe.');
        }

        return Storage::disk('public')->download($expediente->archivo_pdf, $expediente->numero_expediente . '.pdf');
    }
}