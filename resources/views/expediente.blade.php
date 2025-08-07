@extends('layouts.app')

@section('content')
<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f0f0f0;
    }
    /* Estilos para el iframe de vista previa */
    .pdf-preview-iframe {
        width: 100%;
        height: 300px;
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        background-color: #f8fafc;
    }
    /* Estilo para filas de tabla seleccionables */
    .selectable-row {
        cursor: pointer;
        transition: background-color 0.2s ease-in-out;
    }
    .selectable-row:hover {
        background-color: #e2e8f0;
    }
    .selectable-row.selected {
        background-color: #bfdbfe;
    }
    /* Estilos para el árbol de consulta */
    .tree-node {
        cursor: pointer;
        padding: 4px 0;
        display: flex;
        align-items: center;
        font-size: 0.95rem;
        color: #4a5568;
    }
    .tree-node:hover {
        background-color: #edf2f7;
    }
    .tree-node-icon {
        margin-right: 8px;
        width: 16px;
        height: 16px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        color: #2b6cb0;
    }
    .tree-node-children {
        margin-left: 20px;
        list-style: none;
        padding: 0;
    }
    .tree-node-children.hidden {
        display: none;
    }
    .tree-item-leaf {
        padding-left: 24px;
    }
    /* Estilos para el mini-calendario */
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
        text-align: center;
    }
    .calendar-day {
        padding: 4px;
        font-size: 0.8rem;
        border-radius: 0.25rem;
    }
    .calendar-day.current-month {
        background-color: #f0f4f8;
    }
    .calendar-day.other-month {
        color: #a0aec0;
    }
    .calendar-day.has-alert-in-time {
        background-color: #a7f3d0;
        font-weight: bold;
        color: #10b981;
    }
    .calendar-day.has-alert-late {
        background-color: #fecaca;
        font-weight: bold;
        color: #ef4444;
    }
    .calendar-day.today {
        border: 1px solid #2563eb;
        background-color: #bfdbfe;
    }
</style>

<div class="bg-gray-200 min-h-screen py-4">
    <!-- Contenedor principal del diseño -->
    <div class="relative w-full max-w-5xl mx-auto p-4 md:p-8">
        <!-- Barra superior decorativa -->
        <div class="absolute top-0 left-0 right-0 h-4 md:h-6 bg-gray-500 rounded-t-lg"></div>
        <div class="absolute top-4 md:top-6 left-0 right-0 h-2 md:h-3 bg-gray-400"></div>

        <!-- Contenedor del "libro" o "carpeta" -->
        <div class="flex flex-col md:flex-row bg-white rounded-lg shadow-lg overflow-hidden mt-6 md:mt-8 border border-gray-300">
            <!-- Página izquierda: Contenido dinámico -->
            <div class="flex-1 p-6 md:p-8 bg-white border-r border-gray-200 flex flex-col items-center justify-center">
                <h2 id="leftPageTitle" class="text-lg md:text-xl font-semibold text-gray-700 mb-4">EXPEDIENTES ELECTRÓNICOS</h2>

                <!-- Contenido de la primera página (Expediente Electrónico - Formulario de Carga) -->
                <div id="pageContentExpediente" class="w-full flex flex-col items-center">
                    <!-- Campo de entrada para búsqueda de documentos o número de documento -->
                    <input type="text" id="searchInputExpediente" class="w-2/3 md:w-1/2 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mb-6 md:mb-8" placeholder="Buscar documento...">
                    <div id="search-message" class="mb-4 text-sm hidden"></div>
                    <!-- Área de texto grande para descripción o vista previa del documento -->
                    <textarea id="textareaExpediente" class="w-full h-48 md:h-64 p-3 border border-gray-300 rounded-md resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 mb-6" placeholder="Descripción detallada del documento..." readonly></textarea>

                    <!-- Sección para Cargar PDF y Vista Previa -->
                    <div id="pdfUploadSection" class="w-full flex flex-col items-center mt-4 p-4 border border-dashed border-gray-400 rounded-md bg-gray-50">
                        <h3 class="text-md md:text-lg font-medium text-gray-600 mb-3">ADJUNTAR DOCUMENTO PDF</h3>
                        <input type="file" id="pdfUpload" accept=".pdf" class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100"
                        />
                        <p class="mt-2 text-xs text-gray-500" id="pdfMessage">Solo archivos .pdf son permitidos.</p>

                        <!-- Iframe para la vista previa del PDF -->
                        <iframe id="pdfPreview" class="pdf-preview-iframe mt-4 hidden" title="Vista previa de PDF"></iframe>
                    </div>
                </div>

                <!-- Contenido de la segunda página (Lista de Expedientes) -->
                <div id="pageContentExpedientesList" class="w-full flex flex-col items-center hidden">
                    <h3 class="text-md md:text-lg font-medium text-gray-600 mb-4">LISTA DE EXPEDIENTES CREADOS</h3>
                    <div class="w-full overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-300 rounded-md shadow-sm">
                            <thead>
                                <tr class="bg-gray-100 border-b border-gray-300">
                                    <th class="py-2 px-4 text-left text-sm font-semibold text-gray-600">Número de Expediente</th>
                                    <th class="py-2 px-4 text-left text-sm font-semibold text-gray-600">Fecha de Creación</th>
                                </tr>
                            </thead>
                            <tbody id="expedientesTableBody">
                                @foreach($expedientes as $expediente)
                                <tr class="border-b border-gray-200 selectable-row" data-expediente-id="{{ $expediente->id }}">
                                    <td class="py-2 px-4 text-sm text-gray-700">{{ $expediente->numero_expediente }}</td>
                                    <td class="py-2 px-4 text-sm text-gray-700">{{ $expediente->fecha_creacion }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Contenido de la tercera página (Árbol de Consulta - Izquierda) -->
                <div id="pageContentConsultationTreeLeft" class="w-full flex flex-col items-start hidden">
                    <h3 class="text-md md:text-lg font-medium text-gray-600 mb-4">Navegación del Expediente</h3>
                    <div id="treeContainer" class="w-full bg-white border border-gray-300 rounded-md p-4 overflow-y-auto" style="max-height: 500px;">
                        <!-- El árbol se renderizará aquí por JavaScript -->
                    </div>
                </div>

                <!-- Contenido de la página izquierda de Alerta Calendar -->
                <div id="pageContentAlertCalendarLeft" class="w-full flex flex-col items-center hidden p-4">
                    <h3 class="text-md md:text-lg font-medium text-gray-600 mb-4">CALENDARIO DE ALERTAS</h3>
                    <div class="w-full max-w-sm bg-white p-6 rounded-lg shadow-md space-y-4">
                        <div>
                            <label for="receptionDate" class="block text-sm font-medium text-gray-700">Fecha de Recepción:</label>
                            <input type="date" id="receptionDate" class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="receptionTime" class="block text-sm font-medium text-gray-700">Hora de Recepción:</label>
                            <input type="time" id="receptionTime" class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="alertTypeSelect" class="block text-sm font-medium text-gray-700">Tipo de Alerta:</label>
                            <select id="alertTypeSelect" class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="radicacion">Radicación (24h)</option>
                                <option value="incidente">Incidente</option>
                                <option value="revision">Revisión</option>
                                <option value="sentencia">Sentencia</option>
                                <option value="caducidad">Caducidad</option>
                            </select>
                        </div>
                        <button id="calculateDeadlineBtn" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md shadow-md transition duration-300 ease-in-out">
                            Calcular Fecha Límite
                        </button>
                        <div id="alertResult" class="mt-4 p-3 border rounded-md bg-gray-50 text-center">
                            <p class="text-sm font-medium text-gray-700">Fecha Límite: <span id="deadlineResult" class="font-bold">--/--/---- --:--</span></p>
                            <p class="text-sm font-medium">Estado: <span id="alertStatus" class="font-bold"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Página derecha: Contenido dinámico -->
            <div class="flex-1 p-6 md:p-8 bg-white relative flex flex-col items-center">
                <!-- Pestañas de colores -->
                <div class="absolute top-0 right-0 h-full flex flex-col justify-evenly">
                    <div class="w-4 h-1/4 bg-red-500 rounded-l-md cursor-pointer" id="redTab"></div>
                    <div class="w-4 h-1/4 bg-orange-500 rounded-l-md cursor-pointer" id="orangeTab"></div>
                    <div class="w-4 h-1/4 bg-yellow-500 rounded-l-md cursor-pointer" id="yellowTab"></div>
                    <div class="w-4 h-1/4 bg-green-500 rounded-l-md cursor-pointer" id="greenTab"></div>
                </div>

                <!-- Contenido de la primera página derecha (Formulario de Detalles de Expediente) -->
                <div id="pageContentExpedienteDetailsForm" class="w-full flex flex-col items-center">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo Institucional" class="mb-4 md:mb-6">
                    <h1 class="text-xl md:text-2xl font-semibold text-gray-700 text-center mb-6 md:mb-8">
                        EXPEDIENTES ELECTRÓNICOS
                        <br>
                        <span class="text-lg md:text-xl font-normal text-gray-500">— GESTIÓN DOCUMENTAL —</span>
                    </h1>

                    <!-- Formulario para crear expediente -->
                    <form id="expedienteForm" method="POST" action="{{ route('expedientes.store') }}" enctype="multipart/form-data" class="w-full">
                        @csrf
                        <input type="text" id="numero_expediente" name="numero_expediente" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4" placeholder="Número de Expediente" readonly>
                        <input type="text" id="tipo_documento" name="tipo_documento" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4" placeholder="Tipo de Documento" readonly>
                        <input type="text" id="fecha_creacion" name="fecha_creacion" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mb-6 md:mb-8" placeholder="dd/mm/aaaa" readonly>

                        <!-- Contenedor para los botones -->
                        <div class="flex space-x-4">
                            <button type="submit" id="register-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md shadow-md transition duration-300 ease-in-out" disabled>
                                REGISTRAR
                            </button>
                            <button type="button" id="close-btn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-md shadow-md transition duration-300 ease-in-out">
                                CERRAR
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Resto del contenido de páginas derechas... -->
                <div id="pageContentExpedienteFilesView" class="w-full flex flex-col items-center hidden">
                    <h2 class="text-xl md:text-2xl font-semibold text-gray-700 text-center mb-4">DETALLES DEL EXPEDIENTE</h2>
                    <p class="text-gray-600 mb-4 text-center">Número: <span id="detailExpedienteNumber" class="font-bold"></span></p>
                    <p class="text-gray-600 mb-6 text-center">Fecha: <span id="detailExpedienteDate" class="font-bold"></span></p>

                    <h3 class="text-lg md:text-xl font-semibold text-gray-700 mb-4">ARCHIVOS GUARDADOS</h3>
                    <ul id="filesList" class="w-full bg-white border border-gray-300 rounded-md p-4 space-y-2">
                        <!-- Los archivos se insertarán aquí por JavaScript -->
                    </ul>
                </div>

                <div id="pageContentConsultationTreeRight" class="w-full flex flex-col items-center hidden">
                    <h2 class="text-xl md:text-2xl font-semibold text-gray-700 text-center mb-4" id="consultationDetailTitle"></h2>
                    <div id="consultationDetailContent" class="w-full bg-white border border-gray-300 rounded-md p-6 overflow-y-auto" style="min-height: 200px; max-height: 400px;">
                        <p class="text-gray-600" id="consultationDetailText">Seleccione un elemento del árbol para ver sus detalles.</p>
                    </div>
                </div>

                <div id="pageContentAlertCalendarRight" class="w-full flex flex-col items-center hidden p-4">
                    <h2 class="text-xl md:text-2xl font-semibold text-gray-700 text-center mb-4">PROCESO DE REVISIÓN Y LIBERACIÓN</h2>
                    <p class="text-gray-600 text-center leading-relaxed">
                        Los documentos de la pantalla anterior son revisados y se complementan con información adicional.
                        Después de esta acción, se libera el registro y aparecerá como trabajo pendiente.
                    </p>
                    <div class="mt-8 p-4 bg-yellow-100 border border-yellow-300 rounded-md text-yellow-800">
                        <p class="font-semibold">Estado Actual:</p>
                        <p>Trabajo Pendiente: Esperando Liberación</p>
                    </div>

                    <!-- Mini Calendario de Alertas -->
                    <div class="w-full max-w-xs bg-white p-4 rounded-lg shadow-md mt-6">
                        <div class="flex justify-between items-center mb-4">
                            <button id="prevMonthBtn" class="text-gray-600 hover:text-gray-900 font-bold">&lt;</button>
                            <h4 id="currentMonthYear" class="text-lg font-semibold text-gray-800"></h4>
                            <button id="nextMonthBtn" class="text-gray-600 hover:text-gray-900 font-bold">&gt;</button>
                        </div>
                        <div class="calendar-grid font-bold text-sm text-gray-700 mb-2">
                            <span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
                        </div>
                        <div id="miniCalendarBody" class="calendar-grid">
                            <!-- Los días del calendario se insertarán aquí -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra inferior decorativa -->
        <div class="absolute bottom-0 left-0 right-0 h-4 md:h-6 bg-gray-700 rounded-b-lg"></div>
    </div>
</div>

@php
    $expedientesData = $expedientes->map(function($expediente) {
        return [
            'id' => $expediente->numero_expediente,
            'date' => $expediente->fecha_creacion,
            'type' => $expediente->tipo_documento,
            'description' => $expediente->descripcion ?? '',
            'files' => $expediente->archivo_pdf ? [$expediente->archivo_pdf] : [], // Modificación aquí
        ];
    });
@endphp

<script>
   document.addEventListener('DOMContentLoaded', function() {
    const leftPageTitle = document.getElementById('leftPageTitle');
    const contentSectionsLeft = {
        'red': document.getElementById('pageContentExpediente'),
        'orange': document.getElementById('pageContentExpedientesList'),
        'yellow': document.getElementById('pageContentConsultationTreeLeft'),
        'green': document.getElementById('pageContentAlertCalendarLeft')
    };
    const contentSectionsRight = {
        'red': document.getElementById('pageContentExpedienteDetailsForm'),
        'orange': document.getElementById('pageContentExpedienteFilesView'),
        'yellow': document.getElementById('pageContentConsultationTreeRight'),
        'green': document.getElementById('pageContentAlertCalendarRight')
    };

    const tabTitles = {
        'red': 'EXPEDIENTES ELECTRÓNICOS',
        'orange': 'LISTA DE EXPEDIENTES',
        'yellow': 'ÁRBOL DE CONSULTA',
        'green': 'CALENDARIO DE ALERTAS'
    };

    function showContent(color) {
        // Oculta todos los contenidos
        for (const key in contentSectionsLeft) {
            contentSectionsLeft[key].classList.add('hidden');
            contentSectionsRight[key].classList.add('hidden');
        }
        // Muestra el contenido correspondiente
        if (contentSectionsLeft[color]) {
            contentSectionsLeft[color].classList.remove('hidden');
        }
        if (contentSectionsRight[color]) {
            contentSectionsRight[color].classList.remove('hidden');
        }

        // Actualiza el título de la página izquierda
        leftPageTitle.textContent = tabTitles[color];
    }

    // Manejadores de eventos para cada pestaña
    document.getElementById('redTab').addEventListener('click', () => {
        showContent('red');
    });
    document.getElementById('orangeTab').addEventListener('click', () => {
        showContent('orange');
    });
    document.getElementById('yellowTab').addEventListener('click', () => {
        showContent('yellow');
    });
    document.getElementById('greenTab').addEventListener('click', () => {
        showContent('green');
    });

    // Muestra el contenido de la pestaña roja por defecto al cargar la página
    showContent('red');

    // Document search functionality
    const documentSearchInput = document.getElementById('searchInputExpediente');
    const documentDescription = document.getElementById('textareaExpediente');
    const fileNumberInput = document.getElementById('numero_expediente');
    const documentTypeInput = document.getElementById('tipo_documento');
    const documentDateInput = document.getElementById('fecha_creacion');
    const searchMessage = document.getElementById('search-message');
    const registerBtn = document.getElementById('register-btn');
    const pdfUpload = document.getElementById('pdfUpload');

    let searchTimeout = null;
    let foundDocument = null;

    // Function to show messages
    function showMessage(text, type = 'success') {
        searchMessage.textContent = text;
        searchMessage.className = `mb-4 text-sm ${type === 'success' ? 'text-green-600' : 'text-red-600'}`;
        searchMessage.classList.remove('hidden');

        if (type === 'success') {
            setTimeout(() => {
                searchMessage.classList.add('hidden');
            }, 3000);
        }
    }

    // Function to clear form fields
    function clearForm() {
        documentDescription.value = '';
        fileNumberInput.value = '';
        documentTypeInput.value = '';
        documentDateInput.value = '';
        registerBtn.disabled = true;
        foundDocument = null;
    }

    // Function to fill form with document data
    function fillForm(document) {
        documentDescription.value = document.description || '';
        fileNumberInput.value = document.file_number || '';
        documentTypeInput.value = document.type || '';
        documentDateInput.value = document.reception_date || '';
        registerBtn.disabled = false;
        foundDocument = document;
    }

    // Search for document by code
    function searchDocumentByCode(code) {
        if (!code.trim()) {
            clearForm();
            searchMessage.classList.add('hidden');
            return;
        }

        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        searchTimeout = setTimeout(() => {
            fetch(`{{ url('/documents/search-by-code') }}/${encodeURIComponent(code)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.found) {
                    fillForm(data.document);
                    showMessage(`Documento encontrado: ${data.document.code}`, 'success');
                } else {
                    clearForm();
                    showMessage(data.message || 'Documento no encontrado', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                clearForm();
                showMessage('Error al buscar el documento', 'error');
            });
        }, 500);
    }

    // Event listeners
    documentSearchInput.addEventListener('input', function(e) {
        const code = e.target.value.trim();
        searchDocumentByCode(code);
    });

    // Form submission
    document.getElementById('expedienteForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (!foundDocument) {
            showMessage('Debe buscar y seleccionar un documento primero', 'error');
            return;
        }

        if (!pdfUpload.files[0]) {
            showMessage('Debe adjuntar un archivo PDF', 'error');
            return;
        }

        // Create FormData object to handle file upload
        const formData = new FormData();
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        formData.append('numero_expediente', fileNumberInput.value);
        formData.append('tipo_documento', documentTypeInput.value);
        formData.append('fecha_creacion', documentDateInput.value);
        formData.append('descripcion', documentDescription.value);
        formData.append('archivo_pdf', pdfUpload.files[0]);

        // Submit form via AJAX
        fetch('{{ route("expedientes.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('Expediente registrado exitosamente', 'success');
                setTimeout(() => {
                    documentSearchInput.value = '';
                    clearForm();
                    pdfUpload.value = '';
                    document.getElementById('pdfPreview').classList.add('hidden');
                }, 2000);
            } else {
                showMessage(data.message || 'Error al registrar el expediente', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error al registrar el expediente', 'error');
        });
    });

    // PDF preview functionality
    pdfUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const pdfMessage = document.getElementById('pdfMessage');
        const pdfPreview = document.getElementById('pdfPreview');

        if (file) {
            if (file.type === 'application/pdf') {
                const fileURL = URL.createObjectURL(file);
                pdfPreview.src = fileURL;
                pdfPreview.classList.remove('hidden');
                pdfMessage.textContent = `Archivo seleccionado: ${file.name}`;
                pdfMessage.className = 'mt-2 text-xs text-green-600';
            } else {
                pdfPreview.classList.add('hidden');
                pdfMessage.textContent = 'Solo archivos .pdf son permitidos.';
                pdfMessage.className = 'mt-2 text-xs text-red-500';
                pdfUpload.value = '';
            }
        }
    });

    // Close button
    document.getElementById('close-btn').addEventListener('click', function() {
        // En lugar de `confirm()`, se debe usar un modal personalizado.
        // Aquí se usará una solución simple para este ejemplo.
        if (window.confirm('¿Está seguro de que desea cerrar? Se perderán todos los datos no guardados.')) {
            documentSearchInput.value = '';
            clearForm();
            pdfUpload.value = '';
            document.getElementById('pdfPreview').classList.add('hidden');
            searchMessage.classList.add('hidden');
        }
    });

    // Function to handle selecting an expediente and displaying its files
    function selectExpedienteWithDetails(expediente, row) {
        // Remove any previously selected row class
        document.querySelectorAll('.selectable-row').forEach(r => r.classList.remove('selected'));
        // Add selected class to the current row
        row.classList.add('selected');

        // Update the expediente details view
        document.getElementById('detailExpedienteNumber').textContent = expediente.numero_expediente;
        document.getElementById('detailExpedienteDate').textContent = expediente.fecha_creacion;

        const filesListElement = document.getElementById('filesList');
        filesListElement.innerHTML = ''; // Clear previous list

        if (expediente.archivo_pdf) {
            const listItem = document.createElement('li');
            const fileLink = document.createElement('a');
            const fileName = expediente.archivo_pdf.split('/').pop();
            
            fileLink.href = expediente.archivo_pdf;
            fileLink.textContent = 'Ver PDF';
            fileLink.className = 'text-blue-600 hover:underline';
            fileLink.target = '_blank';

            // Ya no se necesita un event listener para el iframe.
            // La funcionalidad de abrir en una nueva pestaña es la predeterminada del `target="_blank"`.

            listItem.textContent = fileName;
            listItem.appendChild(document.createTextNode(' '));
            listItem.appendChild(fileLink);

            filesListElement.appendChild(listItem);
        } else {
            filesListElement.innerHTML = '<li class="text-gray-500">No hay archivos adjuntos para este expediente.</li>';
        }

        showContent('orange');
    }

    // Code for the consultation tree
    const expedientesData = @json($expedientesData);
    const treeContainer = document.getElementById("treeContainer");
    const previewIframe = document.getElementById("pdfPreview");
    const consultationDetailText = document.getElementById("consultationDetailText");
    const consultationDetailTitle = document.getElementById("consultationDetailTitle");

    function createTree(data) {
        if (!treeContainer) return;

        treeContainer.innerHTML = "";

        data.forEach((expediente) => {
            const expedienteNode = document.createElement("div");
            expedienteNode.className = "mb-2";

            const expedienteTitle = document.createElement("button");
            expedienteTitle.className = "w-full text-left px-4 py-2 bg-white border border-gray-200 rounded hover:bg-gray-100 font-semibold";
            expedienteTitle.textContent = "Expediente: " + expediente.id;
            expedienteTitle.onclick = () => toggleVisibility(expedienteNode);

            expedienteNode.appendChild(expedienteTitle);

            const filesList = document.createElement("ul");
            filesList.className = "pl-4 mt-2 hidden";

            expediente.files.forEach((file) => {
                const fileItem = document.createElement("li");
                const fileButton = document.createElement("button");
                fileButton.className = "text-blue-500 hover:underline text-sm";
                fileButton.textContent = file.name;
                fileButton.onclick = () => {
                    consultationDetailTitle.textContent = file.name;
                    consultationDetailText.textContent = `Detalles para el documento: ${file.name} del expediente ${expediente.id}.`;
                };

                fileItem.appendChild(fileButton);
                filesList.appendChild(fileItem);
            });

            expedienteNode.appendChild(filesList);
            treeContainer.appendChild(expedienteNode);
        });
    }

    function toggleVisibility(node) {
        const list = node.querySelector("ul");
        if (list) {
            list.classList.toggle("hidden");
        }
    }

    createTree(expedientesData);

    // Code for the calendar
    const miniCalendarBody = document.getElementById('miniCalendarBody');
    const currentMonthYear = document.getElementById('currentMonthYear');
    const prevMonthBtn = document.getElementById('prevMonthBtn');
    const nextMonthBtn = document.getElementById('nextMonthBtn');
    let currentDate = new Date();

    function renderCalendar() {
        if (!miniCalendarBody || !currentMonthYear) return;

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);

        currentMonthYear.textContent = new Intl.DateTimeFormat('es-ES', { month: 'long', year: 'numeric' }).format(currentDate);

        miniCalendarBody.innerHTML = '';
        const startDayOfWeek = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;

        for (let i = 0; i < startDayOfWeek; i++) {
            miniCalendarBody.innerHTML += '<div></div>';
        }

        for (let i = 1; i <= lastDay.getDate(); i++) {
            const dayDiv = document.createElement('div');
            dayDiv.className = 'calendar-day current-month';
            dayDiv.textContent = i;
            if (year === new Date().getFullYear() && month === new Date().getMonth() && i === new Date().getDate()) {
                dayDiv.classList.add('today');
            }
            miniCalendarBody.appendChild(dayDiv);
        }
    }

    if (prevMonthBtn && nextMonthBtn) {
        prevMonthBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });

        nextMonthBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });
    }

    renderCalendar();

    // Event listeners para filas de expedientes
    document.addEventListener('click', function(e) {
        if (e.target.closest('.selectable-row')) {
            const row = e.target.closest('.selectable-row');
            const expedienteId = row.dataset.expedienteId;

            // Fetch expediente details via AJAX
            fetch(`{{ url('/api/expedientes') }}/${expedienteId}/detalles`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    selectExpedienteWithDetails(data.expediente, row);
                } else {
                    console.error('Error loading expediente details:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });
});

</script>
@endsection