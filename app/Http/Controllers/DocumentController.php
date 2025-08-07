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
     * Actualizar la descripción de un documento.
     * Este método procesa una solicitud para actualizar solo el campo de descripción.
     * Valida la nueva descripción y la guarda en la base de datos.
     */
    public function updateDescription(Request $request, Document $document): JsonResponse
    {
        // Validar que la descripción es una cadena de texto y que no excede el límite.
        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:1000',
        ], [
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción no puede tener más de 1000 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Por favor, corrija los errores en el formulario.'
            ], 422);
        }

        try {
            // Actualizar solo el campo de descripción.
            $document->update([
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Descripción del documento actualizada exitosamente.',
                'document' => [
                    'id' => $document->id,
                    'description' => $document->description,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la descripción. Por favor, inténtelo de nuevo.',
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

    /**
     * Search for a document by its code and return details for auto-filling form
     */
    public function searchByCode(string $code): JsonResponse
    {
        try {
            $document = Document::where('code', $code)->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado con el código proporcionado.',
                    'found' => false
                ], 404);
            }

            // Generate a unique file number
            $fileNumber = $this->generateUniqueFileNumber();

            return response()->json([
                'success' => true,
                'found' => true,
                'message' => 'Documento encontrado exitosamente.',
                'document' => [
                    'id' => $document->id,
                    'code' => $document->code,
                    'type' => $document->type,
                    'description' => $document->description ?? '',
                    'reception_date' => $document->formatted_reception_date ?? $document->reception_date->format('d/m/Y H:i'),
                    'file_number' => $fileNumber
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar el documento.',
                'found' => false,
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Generate a unique file number
     */
    private function generateUniqueFileNumber(): string
    {
        do {
            // Generate a random file number format: EXP-YYYY-XXXXXX
            $year = date('Y');
            $randomNumber = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $fileNumber = "EXP-{$year}-{$randomNumber}";
            
            // Check if this number already exists in the database
            // Assuming you have a way to track file numbers, you might need to create a table for this
            // For now, we'll just generate a unique number based on timestamp + random
            $fileNumber = "EXP-{$year}-" . str_pad(time() . rand(10, 99), 8, '0', STR_PAD_LEFT);
            
        } while (false); // In a real implementation, you'd check against existing file numbers

        return $fileNumber;
    }
}
