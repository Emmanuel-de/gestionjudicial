@extends('layouts.app')

@section('content')
<div class="font-sans bg-gray-100 min-h-screen">
    <!-- Configuración de Tailwind para usar colores personalizados -->
    <style>
        :root {
            --grid-bg: #f8f8f8;
            --grid-line: #e0e0e0;
            --footer-bg: #d4c4a8;
            --footer-dark-bg: #c0b094;
            --icon-color: #4a5568;
        }
        
        .selected-row {
            background-color: #dbeafe !important;
            border-left: 4px solid #3b82f6;
        }
        
        .approved-row {
            background-color: #dcfce7;
            border-left: 4px solid #16a34a;
        }
        
        .rejected-row {
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
        }
        
        .table-row:hover {
            background-color: #f3f4f6;
        }
    </style>

    <!-- Contenedor principal del diseño -->
    <div class="flex flex-col max-w-7xl mx-auto w-full bg-white shadow-lg rounded-lg overflow-hidden my-4">

        <!-- Área superior con tabla de documentos -->
        <div class="flex-grow p-4" style="background-color: var(--grid-bg);">
            <!-- Encabezado -->
            <div class="mb-4">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Documentos</h2>
                <p class="text-gray-600">Selecciona un documento para marcarlo como aprobado o rechazado</p>
            </div>

            <!-- Tabla de documentos -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Codigo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($documents as $document)
                            <tr class="table-row cursor-pointer transition-colors duration-200
                                @if($document->status === 'approved') approved-row
                                @elseif($document->status === 'rejected') rejected-row
                                @endif" 
                                data-document-id="{{ $document->id }}"
                                onclick="selectRow(this, {{ $document->id }})">
                                
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $document->id }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $document->code ?? 'Sin codigo' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $document->type ?? 'Sin tipo' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $document->created_at ? $document->created_at->format('d/m/Y') : 'N/A' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($document->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Aprobado
                                        </span>
                                    @elseif($document->status === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                            Rechazado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    {{ $document->description ?? 'Sin descripción' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    No hay documentos disponibles
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación si es necesaria -->
            @if(method_exists($documents, 'links'))
                <div class="mt-4">
                    {{ $documents->links() }}
                </div>
            @endif
        </div>

        <!-- Área del pie de página -->
        <div class="p-3 flex items-center justify-between space-x-4 rounded-b-lg" style="background-color: var(--footer-bg);">
            <!-- Sección izquierda: Búsqueda -->
            <div class="flex items-center space-x-2 flex-grow">
                <!-- Icono de búsqueda -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-600">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <!-- Campo de entrada de búsqueda -->
                <input type="text" id="searchInput" placeholder="Buscar documentos..." 
                       class="flex-grow p-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white shadow-sm"
                       onkeyup="filterDocuments()">
            </div>

            <!-- Información del documento seleccionado -->
            <div id="selectedInfo" class="hidden text-sm text-gray-700 px-3 py-1 bg-blue-100 rounded-md">
                Documento seleccionado: <span id="selectedDocumentId">-</span>
            </div>

            <!-- Sección derecha: Iconos de acción -->
            <div class="flex items-center space-x-3">
                <!-- Icono de Información -->
                <button onclick="showInfo()" class="p-2 rounded-full hover:bg-yellow-200 transition-colors duration-200 shadow-sm" style="background-color: var(--footer-dark-bg);" title="Información">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--icon-color);">
                        <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                    </svg>
                </button>
                
                <!-- Icono de Marca de Verificación (Aprobar) -->
                <button id="approveBtn" onclick="updateDocumentStatus('approved')" disabled class="p-2 rounded-full hover:bg-green-200 transition-colors duration-200 shadow-sm opacity-50" style="background-color: var(--footer-dark-bg);" title="Aprobar documento">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                </button>
                
                <!-- Icono de Actualizar -->
                <button onclick="refreshPage()" class="p-2 rounded-full hover:bg-blue-200 transition-colors duration-200 shadow-sm" style="background-color: var(--footer-dark-bg);" title="Actualizar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600">
                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M3 21v-5h5"/>
                    </svg>
                </button>
                
                <!-- Icono de X (Rechazar) -->
                <button id="rejectBtn" onclick="updateDocumentStatus('rejected')" disabled class="p-2 rounded-full hover:bg-red-200 transition-colors duration-200 shadow-sm opacity-50" style="background-color: var(--footer-dark-bg);" title="Rechazar documento">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-600">
                        <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                    </svg>
                </button>
                
                <!-- Icono de Limpiar selección -->
                <button id="clearBtn" onclick="clearSelection()" disabled class="p-2 rounded-full hover:bg-red-200 transition-colors duration-200 shadow-sm opacity-50" style="background-color: var(--footer-dark-bg);" title="Limpiar selección">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-600">
                        <circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para mensajes -->
    <div id="messageModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle">Mensaje</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500" id="modalMessage"></p>
                </div>
                <div class="items-center px-4 py-3">
                    <button onclick="closeModal()" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedDocumentId = null;

// Función para seleccionar una fila
function selectRow(row, documentId) {
    // Limpiar selección anterior
    document.querySelectorAll('.table-row').forEach(r => {
        r.classList.remove('selected-row');
    });
    
    // Seleccionar la nueva fila
    row.classList.add('selected-row');
    selectedDocumentId = documentId;
    
    // Actualizar UI
    document.getElementById('selectedDocumentId').textContent = documentId;
    document.getElementById('selectedInfo').classList.remove('hidden');
    
    // Habilitar botones
    document.getElementById('approveBtn').disabled = false;
    document.getElementById('rejectBtn').disabled = false;
    document.getElementById('clearBtn').disabled = false;
    document.getElementById('approveBtn').classList.remove('opacity-50');
    document.getElementById('rejectBtn').classList.remove('opacity-50');
    document.getElementById('clearBtn').classList.remove('opacity-50');
}

// Función para limpiar selección
function clearSelection() {
    document.querySelectorAll('.table-row').forEach(r => {
        r.classList.remove('selected-row');
    });
    
    selectedDocumentId = null;
    document.getElementById('selectedInfo').classList.add('hidden');
    
    // Deshabilitar botones
    document.getElementById('approveBtn').disabled = true;
    document.getElementById('rejectBtn').disabled = true;
    document.getElementById('clearBtn').disabled = true;
    document.getElementById('approveBtn').classList.add('opacity-50');
    document.getElementById('rejectBtn').classList.add('opacity-50');
    document.getElementById('clearBtn').classList.add('opacity-50');
}

// Función para actualizar el estado del documento
async function updateDocumentStatus(status) {
    if (!selectedDocumentId) {
        showMessage('Error', 'No hay ningún documento seleccionado');
        return;
    }
    
    try {
        const response = await fetch(`/documents/${selectedDocumentId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        });
        
        if (response.ok) {
            const result = await response.json();
            showMessage('Éxito', `Documento ${status === 'approved' ? 'aprobado' : 'rechazado'} correctamente`);
            
            // Actualizar la fila visualmente
            const row = document.querySelector(`[data-document-id="${selectedDocumentId}"]`);
            row.classList.remove('approved-row', 'rejected-row');
            if (status === 'approved') {
                row.classList.add('approved-row');
            } else if (status === 'rejected') {
                row.classList.add('rejected-row');
            }
            
            // Actualizar el badge de estado
            const statusCell = row.querySelector('td:nth-child(5)');
            if (status === 'approved') {
                statusCell.innerHTML = `
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Aprobado
                    </span>
                `;
            } else {
                statusCell.innerHTML = `
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        Rechazado
                    </span>
                `;
            }
            
            clearSelection();
        } else {
            throw new Error('Error en la respuesta del servidor');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error', 'No se pudo actualizar el documento. Inténtalo de nuevo.');
    }
}

// Función para mostrar información
function showInfo() {
    const totalDocs = document.querySelectorAll('.table-row').length;
    const approvedDocs = document.querySelectorAll('.approved-row').length;
    const rejectedDocs = document.querySelectorAll('.rejected-row').length;
    const pendingDocs = totalDocs - approvedDocs - rejectedDocs;
    
    showMessage('Información', 
        `Total de documentos: ${totalDocs}\n` +
        `Aprobados: ${approvedDocs}\n` +
        `Rechazados: ${rejectedDocs}\n` +
        `Pendientes: ${pendingDocs}`
    );
}

// Función para refrescar la página
function refreshPage() {
    window.location.reload();
}

// Función para filtrar documentos
function filterDocuments() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('.table-row');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Función para mostrar mensajes
function showMessage(title, message) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = message;
    document.getElementById('messageModal').classList.remove('hidden');
}

// Función para cerrar modal
function closeModal() {
    document.getElementById('messageModal').classList.add('hidden');
}

// Cerrar modal al hacer clic fuera de él
window.onclick = function(event) {
    const modal = document.getElementById('messageModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>

@endsection