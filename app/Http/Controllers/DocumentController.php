<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
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
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('type', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('status', 'LIKE', "%{$searchTerm}%");
            });
        }

        $documents = $query->paginate(10);

        return view('documents.index', compact('documents'));
    }

    /**
     * Almacenar un nuevo documento
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:documents,code',
            'type' => 'required|string|in:Oficio,Sentencia,Radicacion',
            'status' => 'required|string|in:Recibido,Pendiente,Actualizar'
        ], [
            'code.required' => 'El código del documento es requerido.',
            'code.unique' => 'Este código de documento ya existe.',
            'code.max' => 'El código no puede tener más de 255 caracteres.',
            'type.required' => 'El tipo de documento es requerido.',
            'type.in' => 'El tipo de documento seleccionado no es válido.',
            'status.required' => 'El estado del documento es requerido.',
            'status.in' => 'El estado seleccionado no es válido.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Por favor, corrija los errores en el formulario.'
            ], 422);
        }

        try {
            $document = Document::create([
                'code' => $request->code,
                'type' => $request->type,
                'status' => $request->status,
                'reception_date' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documento registrado exitosamente.',
                'document' => [
                    'id' => $document->id,
                    'code' => $document->code,
                    'type' => $document->type,
                    'status' => $document->status,
                    'reception_date' => $document->formatted_reception_date ?? $document->reception_date->format('d/m/Y H:i'),
                    'status_color_class' => $document->status_color_class ?? $this->getStatusColorClass($document->status)
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el documento. Por favor, inténtelo de nuevo.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
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
                'reception_date' => $document->formatted_reception_date ?? $document->reception_date->format('d/m/Y H:i'),
                'status_color_class' => $document->status_color_class ?? $this->getStatusColorClass($document->status)
            ]
        ]);
    }

    /**
     * Actualizar un documento
     */
    public function update(Request $request, Document $document): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:documents,code,' . $document->id,
            'type' => 'required|string|in:Oficio,Sentencia,Radicacion',
            'status' => 'required|string|in:Recibido,Pendiente,Actualizar'
        ], [
            'code.required' => 'El código del documento es requerido.',
            'code.unique' => 'Este código de documento ya existe.',
            'code.max' => 'El código no puede tener más de 255 caracteres.',
            'type.required' => 'El tipo de documento es requerido.',
            'type.in' => 'El tipo de documento seleccionado no es válido.',
            'status.required' => 'El estado del documento es requerido.',
            'status.in' => 'El estado seleccionado no es válido.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Por favor, corrija los errores en el formulario.'
            ], 422);
        }

        try {
            $updateData = [
                'code' => $request->code,
                'type' => $request->type,
                'status' => $request->status,
            ];

            // Solo actualizar reception_date si el status cambió
            if ($document->status !== $request->status) {
                $updateData['reception_date'] = Carbon::now();
            }

            $document->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Documento actualizado exitosamente.',
                'document' => [
                    'id' => $document->id,
                    'code' => $document->code,
                    'type' => $document->type,
                    'status' => $document->status,
                    'reception_date' => $document->formatted_reception_date ?? $document->reception_date->format('d/m/Y H:i'),
                    'status_color_class' => $document->status_color_class ?? $this->getStatusColorClass($document->status)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el documento. Por favor, inténtelo de nuevo.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Eliminar un documento
     */
    public function destroy(Document $document): JsonResponse
    {
        try {
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el documento. Por favor, inténtelo de nuevo.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Búsqueda AJAX
     */
    public function search(Request $request): JsonResponse
    {
        $term = trim($request->get('term', ''));
        
        if (empty($term)) {
            return response()->json([
                'success' => true,
                'documents' => []
            ]);
        }

        try {
            $documents = Document::where('code', 'LIKE', "%{$term}%")
                               ->orWhere('type', 'LIKE', "%{$term}%")
                               ->orWhere('status', 'LIKE', "%{$term}%")
                               ->orderBy('reception_date', 'desc')
                               ->limit(50) // Limitar resultados para mejor rendimiento
                               ->get()
                               ->map(function ($document) {
                                   return [
                                       'id' => $document->id,
                                       'code' => $document->code,
                                       'type' => $document->type,
                                       'status' => $document->status,
                                       'reception_date' => $document->formatted_reception_date ?? $document->reception_date->format('d/m/Y H:i'),
                                       'status_color_class' => $document->status_color_class ?? $this->getStatusColorClass($document->status)
                                   ];
                               });

            return response()->json([
                'success' => true,
                'documents' => $documents,
                'count' => $documents->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar la búsqueda.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener clase CSS para el color del estado
     */
    private function getStatusColorClass(string $status): string
    {
        return match ($status) {
            'Recibido' => 'bg-green-100 text-green-800',
            'Pendiente' => 'bg-yellow-100 text-yellow-800',
            'Actualizar' => 'bg-red-100 text-red-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $document = Document::findOrFail($id);
        
        // Map the frontend status to the backend status
        $statusMap = [
            'approved' => 'Recibido',
            'rejected' => 'Actualizar'
        ];
        
        $document->status = $statusMap[$request->status];
        $document->reception_date = Carbon::now();
        $document->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente.',
            'document' => [
                'id' => $document->id,
                'code' => $document->code,
                'type' => $document->type,
                'status' => $document->status,
                'reception_date' => $document->formatted_reception_date ?? $document->reception_date->format('d/m/Y H:i'),
                'status_color_class' => $document->status_color_class ?? $this->getStatusColorClass($document->status)
            ]
        ]);
    }
}