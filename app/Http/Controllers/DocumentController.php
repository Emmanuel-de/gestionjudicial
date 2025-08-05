<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class DocumentController extends Controller
{
    /**
     * Mostrar la lista de documentos
     */
    public function index(Request $request): View
    {
        $query = Document::query()->orderBy('reception_date', 'desc');

        // Si hay término de búsqueda
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        $documents = $query->paginate(10);

        return view('documents.index', compact('documents'));
    }

    /**
     * Almacenar un nuevo documento
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:documents,code',
            'type' => 'required|string|in:Oficio,Sentencia,Radicacion',
            'status' => 'required|string|in:Recibido,Pendiente,Actualizar'
        ], [
            'code.required' => 'El código del documento es requerido.',
            'code.unique' => 'Este código de documento ya existe.',
            'type.required' => 'El tipo de documento es requerido.',
            'type.in' => 'El tipo de documento seleccionado no es válido.',
            'status.required' => 'El estado del documento es requerido.',
            'status.in' => 'El estado seleccionado no es válido.'
        ]);

        $validated['reception_date'] = Carbon::now();

        $document = Document::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Documento registrado exitosamente.',
            'document' => [
                'id' => $document->id,
                'code' => $document->code,
                'type' => $document->type,
                'status' => $document->status,
                'reception_date' => $document->formatted_reception_date,
                'status_color_class' => $document->status_color_class
            ]
        ]);
    }

    /**
     * Mostrar un documento específico
     */
    public function show(Document $document): JsonResponse
    {
        return response()->json([
            'success' => true,
            'document' => [
                'id' => $document->id,
                'code' => $document->code,
                'type' => $document->type,
                'status' => $document->status,
                'reception_date' => $document->formatted_reception_date,
                'status_color_class' => $document->status_color_class
            ]
        ]);
    }

    /**
     * Actualizar un documento
     */
    public function update(Request $request, Document $document): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:documents,code,' . $document->id,
            'type' => 'required|string|in:Oficio,Sentencia,Radicacion',
            'status' => 'required|string|in:Recibido,Pendiente,Actualizar'
        ], [
            'code.required' => 'El código del documento es requerido.',
            'code.unique' => 'Este código de documento ya existe.',
            'type.required' => 'El tipo de documento es requerido.',
            'type.in' => 'El tipo de documento seleccionado no es válido.',
            'status.required' => 'El estado del documento es requerido.',
            'status.in' => 'El estado seleccionado no es válido.'
        ]);

        // Actualizar la fecha de recepción al editar
        $validated['reception_date'] = Carbon::now();

        $document->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Documento actualizado exitosamente.',
            'document' => [
                'id' => $document->id,
                'code' => $document->code,
                'type' => $document->type,
                'status' => $document->status,
                'reception_date' => $document->formatted_reception_date,
                'status_color_class' => $document->status_color_class
            ]
        ]);
    }

    /**
     * Eliminar un documento
     */
    public function destroy(Document $document): JsonResponse
    {
        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Documento eliminado exitosamente.'
        ]);
    }

    /**
     * Búsqueda AJAX
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->get('term', '');
        
        $documents = Document::search($term)
                            ->orderBy('reception_date', 'desc')
                            ->get()
                            ->map(function ($document) {
                                return [
                                    'id' => $document->id,
                                    'code' => $document->code,
                                    'type' => $document->type,
                                    'status' => $document->status,
                                    'reception_date' => $document->formatted_reception_date,
                                    'status_color_class' => $document->status_color_class
                                ];
                            });

        return response()->json([
            'success' => true,
            'documents' => $documents
        ]);
    }
}