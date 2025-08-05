@extends('layouts.app')

@section('title', 'Entrega-Recepción')

@section('content')
<div class="bg-gray-100 flex flex-col items-center justify-center min-h-screen">
    <!-- Contenedor principal de la aplicación -->
    <div class="bg-white rounded-xl shadow-2xl overflow-hidden w-full max-w-5xl mx-4 my-8">

        <!-- Header de la aplicación -->
        <header class="bg-gradient-to-r from-red-700 to-red-900 text-white p-4 flex items-center justify-between rounded-t-xl">
            <h1 class="text-3xl font-bold">Entrega-Recepción</h1>
            <div class="flex space-x-3">
                <i class="fa-brands fa-facebook-f text-lg hover:text-gray-300 cursor-pointer transition-colors duration-200"></i>
                <i class="fa-brands fa-twitter text-lg hover:text-gray-300 cursor-pointer transition-colors duration-200"></i>
                <i class="fa-solid fa-envelope text-lg hover:text-gray-300 cursor-pointer transition-colors duration-200"></i>
                <!-- Botones de colores -->
                <button class="w-6 h-6 rounded-full bg-yellow-400 hover:bg-yellow-500 transition-colors duration-200"></button>
                <button class="w-6 h-6 rounded-full bg-red-500 hover:bg-red-600 transition-colors duration-200"></button>
                <button class="w-6 h-6 rounded-full bg-blue-500 hover:bg-blue-600 transition-colors duration-200"></button>
            </div>
        </header>

        <!-- Contenido principal -->
        <main class="p-8">
            <div class="bg-gray-50 rounded-lg p-6 shadow-inner">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Documentos Escaneados</h2>
                
                <!-- Mostrar mensajes de éxito/error -->
                <div id="message-container" class="hidden mb-4">
                    <div id="message" class="p-4 rounded-lg"></div>
                </div>

                <!-- Tabla de documentos -->
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Documento</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Recepción</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody id="document-list" class="bg-white divide-y divide-gray-200">
                            @forelse($documents as $document)
                            <tr class="document-row cursor-pointer hover:bg-gray-50" data-id="{{ $document->id }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $document->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $document->type }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $document->formatted_reception_date }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $document->status_color_class }} font-semibold">{{ $document->status }}</td>
                            </tr>
                            @empty
                            <tr id="no-documents-row">
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No hay documentos registrados</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($documents->hasPages())
                <div class="mt-4">
                    {{ $documents->links() }}
                </div>
                @endif
            </div>
        </main>

        <!-- Sección inferior para la búsqueda y botones -->
        <div class="bg-gray-800 p-6 rounded-b-xl flex flex-col md:flex-row items-center justify-between">
            <!-- Formulario de búsqueda -->
            <div class="flex items-center w-full md:w-auto mb-4 md:mb-0">
                <div class="relative w-full mr-2">
                    <input type="text" id="search-input" placeholder="Buscar documento por código o tipo..." class="w-full pl-10 pr-4 py-2 rounded-lg text-gray-900 border-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-shadow">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex space-x-3">
                <button id="print-button" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                    <i class="fa-solid fa-print"></i>
                </button>
                <button id="edit-button" class="bg-gray-400 cursor-not-allowed text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200" disabled>
                    <i class="fa-solid fa-edit mr-2"></i>Editar
                </button>
                <button id="delete-button" class="bg-gray-400 cursor-not-allowed text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200" disabled>
                    <i class="fa-solid fa-trash mr-2"></i>Eliminar
                </button>
                <button id="register-button" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                    <i class="fa-solid fa-plus mr-2"></i>Registrar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para el formulario de registro y edición -->
    <div id="registration-modal" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modal-title" class="text-2xl font-semibold text-gray-800">Registrar Nuevo Documento</h3>
                <button id="close-modal-button" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </div>
            <form id="registration-form">
                @csrf
                <input type="hidden" id="document-id" name="document_id">
                <input type="hidden" id="form-method" value="POST">
                
                <div class="mb-4">
                    <label for="document-code" class="block text-gray-700 font-medium mb-1">Código del Documento</label>
                    <input type="text" id="document-code" name="code" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ej. EXP-2024-001" required>
                    <div class="text-red-500 text-sm mt-1 hidden" id="code-error"></div>
                </div>
                
                <div class="mb-4">
                    <label for="document-type" class="block text-gray-700 font-medium mb-1">Tipo de Documento</label>
                    <select id="document-type" name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Selecciona un tipo</option>
                        @foreach(App\Models\Document::getAvailableTypes() as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    <div class="text-red-500 text-sm mt-1 hidden" id="type-error"></div>
                </div>
                
                <div class="mb-6">
                    <label for="document-status" class="block text-gray-700 font-medium mb-1">Estado</label>
                    <select id="document-status" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @foreach(App\Models\Document::getAvailableStatuses() as $key => $value)
                            <option value="{{ $key }}" {{ $key === 'Recibido' ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                    <div class="text-red-500 text-sm mt-1 hidden" id="status-error"></div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" id="modal-submit-button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200">
                        <span id="submit-text">Guardar Documento</span>
                        <i class="fa-solid fa-spinner fa-spin ml-2 hidden" id="submit-spinner"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
    }
    .modal-overlay.hidden {
        display: none;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a los elementos del DOM
    const searchInput = document.getElementById('search-input');
    const documentList = document.getElementById('document-list');
    const registerButton = document.getElementById('register-button');
    const editButton = document.getElementById('edit-button');
    const deleteButton = document.getElementById('delete-button');
    const registrationModal = document.getElementById('registration-modal');
    const closeModalButton = document.getElementById('close-modal-button');
    const registrationForm = document.getElementById('registration-form');
    const documentCodeInput = document.getElementById('document-code');
    const documentTypeInput = document.getElementById('document-type');
    const documentStatusInput = document.getElementById('document-status');
    const modalTitle = document.getElementById('modal-title');
    const modalSubmitButton = document.getElementById('modal-submit-button');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');
    const messageContainer = document.getElementById('message-container');
    const message = document.getElementById('message');

    // Variable global para almacenar la fila seleccionada
    let selectedRow = null;
    let searchTimeout = null;

    // Función para mostrar mensajes
    function showMessage(text, type = 'success') {
        message.textContent = text;
        message.className = `p-4 rounded-lg ${type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
        messageContainer.classList.remove('hidden');
        setTimeout(() => {
            messageContainer.classList.add('hidden');
        }, 5000);
    }

    // Función para limpiar errores
    function clearErrors() {
        ['code-error', 'type-error', 'status-error'].forEach(id => {
            const element = document.getElementById(id);
            element.classList.add('hidden');
            element.textContent = '';
        });
    }

    // Función para mostrar errores
    function showErrors(errors) {
        clearErrors();
        Object.keys(errors).forEach(field => {
            const errorElement = document.getElementById(`${field}-error`);
            if (errorElement) {
                errorElement.textContent = errors[field][0];
                errorElement.classList.remove('hidden');
            }
        });
    }

    // Función para actualizar el estado de los botones de acción
    function updateActionButtons() {
        if (selectedRow) {
            editButton.disabled = false;
            deleteButton.disabled = false;
            editButton.classList.remove('bg-gray-400', 'cursor-not-allowed');
            editButton.classList.add('bg-yellow-500', 'hover:bg-yellow-600');
            deleteButton.classList.remove('bg-gray-400', 'cursor-not-allowed');
            deleteButton.classList.add('bg-red-500', 'hover:bg-red-600');
        } else {
            editButton.disabled = true;
            deleteButton.disabled = true;
            editButton.classList.add('bg-gray-400', 'cursor-not-allowed');
            editButton.classList.remove('bg-yellow-500', 'hover:bg-yellow-600');
            deleteButton.classList.add('bg-gray-400', 'cursor-not-allowed');
            deleteButton.classList.remove('bg-red-500', 'hover:bg-red-600');
        }
    }

    // Función para manejar la selección de una fila de la tabla
    function handleRowSelection(e) {
        const row = e.currentTarget;
        if (selectedRow) {
            selectedRow.classList.remove('bg-blue-100');
        }
        if (selectedRow === row) {
            selectedRow = null;
        } else {
            selectedRow = row;
            selectedRow.classList.add('bg-blue-100');
        }
        updateActionButtons();
    }

    // Función para adjuntar el evento de clic a las filas
    function attachRowListeners() {
        const rows = documentList.querySelectorAll('.document-row');
        rows.forEach(row => {
            row.removeEventListener('click', handleRowSelection);
            row.addEventListener('click', handleRowSelection);
        });
    }

    // Función para crear una nueva fila
    function createDocumentRow(document) {
        const row = document.createElement('tr');
        row.classList.add('document-row', 'cursor-pointer', 'hover:bg-gray-50');
        row.setAttribute('data-id', document.id);
        
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${document.code}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${document.type}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${document.reception_date}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm ${document.status_color_class} font-semibold">${document.status}</td>
        `;
        
        return row;
    }

    // Función para buscar documentos
    function searchDocuments(term) {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        searchTimeout = setTimeout(() => {
            fetch(`{{ route('documents.search') }}?term=${encodeURIComponent(term)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Limpiar la tabla
                    documentList.innerHTML = '';
                    
                    if (data.documents.length === 0) {
                        documentList.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No se encontraron documentos</td></tr>';
                    } else {
                        data.documents.forEach(document => {
                            const row = createDocumentRow(document);
                            documentList.appendChild(row);
                        });
                        attachRowListeners();
                    }
                }
            })
            .catch(error => {
                console.error('Error en la búsqueda:', error);
            });
        }, 300);
    }

    // Event listeners
    attachRowListeners();
    updateActionButtons();

    // Búsqueda
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.trim();
        if (term.length >= 2 || term.length === 0) {
            searchDocuments(term);
        }
    });

    // Mostrar modal para registrar
    registerButton.addEventListener('click', () => {
        modalTitle.textContent = 'Registrar Nuevo Documento';
        submitText.textContent = 'Guardar Documento';
        document.getElementById('form-method').value = 'POST';
        document.getElementById('document-id').value = '';
        registrationForm.reset();
        clearErrors();
        registrationModal.classList.remove('hidden');
    });

    // Mostrar modal para editar
    editButton.addEventListener('click', () => {
        if (!selectedRow) return;
        
        modalTitle.textContent = 'Editar Documento';
        submitText.textContent = 'Actualizar Documento';
        document.getElementById('form-method').value = 'PUT';
        document.getElementById('document-id').value = selectedRow.getAttribute('data-id');
        
        documentCodeInput.value = selectedRow.children[0].textContent;
        documentTypeInput.value = selectedRow.children[1].textContent;
        documentStatusInput.value = selectedRow.children[3].textContent;
        
        clearErrors();
        registrationModal.classList.remove('hidden');
    });

    // Eliminar documento
    deleteButton.addEventListener('click', () => {
        if (!selectedRow) return;
        
        if (confirm('¿Estás seguro de que deseas eliminar este documento?')) {
            const documentId = selectedRow.getAttribute('data-id');
            
            fetch(`{{ url('documents') }}/${documentId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    selectedRow.remove();
                    selectedRow = null;
                    updateActionButtons();
                    showMessage(data.message, 'success');
                    
                    // Si no quedan filas, mostrar mensaje
                    if (documentList.querySelectorAll('.document-row').length === 0) {
                        documentList.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No hay documentos registrados</td></tr>';
                    }
                } else {
                    showMessage('Error al eliminar el documento', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Error al eliminar el documento', 'error');
            });
        }
    });

    // Cerrar modal
    closeModalButton.addEventListener('click', () => {
        registrationModal.classList.add('hidden');
        selectedRow = null;
        updateActionButtons();
    });

    // Cerrar modal haciendo clic en el overlay
    registrationModal.addEventListener('click', (e) => {
        if (e.target.id === 'registration-modal') {
            registrationModal.classList.add('hidden');
            selectedRow = null;
            updateActionButtons();
        }
    });

    // Envío del formulario
    registrationForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const formData = new FormData(registrationForm);
        const method = document.getElementById('form-method').value;
        const documentId = document.getElementById('document-id').value;
        
        let url = '{{ route("documents.store") }}';
        let fetchOptions = {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        };

        if (method === 'PUT') {
            url = `{{ url('documents') }}/${documentId}`;
            fetchOptions.method = 'POST';
            formData.append('_method', 'PUT');
        }

        // Mostrar spinner
        submitText.classList.add('hidden');
        submitSpinner.classList.remove('hidden');
        modalSubmitButton.disabled = true;

        fetch(url, fetchOptions)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (method === 'POST') {
                    // Nuevo documento
                    const noDocsRow = document.getElementById('no-documents-row');
                    if (noDocsRow) {
                        noDocsRow.remove();
                    }
                    
                    const newRow = createDocumentRow(data.document);
                    documentList.insertBefore(newRow, documentList.firstChild);
                    newRow.addEventListener('click', handleRowSelection);
                } else {
                    // Actualizar documento existente
                    if (selectedRow) {
                        selectedRow.children[0].textContent = data.document.code;
                        selectedRow.children[1].textContent = data.document.type;
                        selectedRow.children[2].textContent = data.document.reception_date;
                        selectedRow.children[3].textContent = data.document.status;
                        selectedRow.children[3].className = `px-6 py-4 whitespace-nowrap text-sm ${data.document.status_color_class} font-semibold`;
                        selectedRow.setAttribute('data-id', data.document.id);
                        selectedRow.classList.remove('bg-blue-100');
                    }
                }

                registrationModal.classList.add('hidden');
                selectedRow = null;
                updateActionButtons();
                showMessage(data.message, 'success');
                registrationForm.reset();
                clearErrors();
            } else {
                if (data.errors) {
                    showErrors(data.errors);
                } else {
                    showMessage(data.message || 'Error al procesar la solicitud', 'error');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error al procesar la solicitud', 'error');
        })
        .finally(() => {
            // Ocultar spinner
            submitText.classList.remove('hidden');
            submitSpinner.classList.add('hidden');
            modalSubmitButton.disabled = false;
        });
    });

    // Función para imprimir (puedes personalizarla según tus necesidades)
    document.getElementById('print-button').addEventListener('click', () => {
        window.print();
    });
});
</script>
@endpush
@endsection