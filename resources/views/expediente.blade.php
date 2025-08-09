@extends('layouts.app')

@section('content')
<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f0f0f0;
    }

    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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

    /* Estilos mejorados para el árbol de consulta */
    .tree-node {
        cursor: pointer;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        font-size: 0.95rem;
        color: #4a5568;
        border-radius: 4px;
        margin: 2px 0;
        position: relative;
        transition: background-color 0.2s ease-in-out;
    }

    .tree-node:hover {
        background-color: #edf2f7;
    }

    .tree-node.selected {
        background-color: #bfdbfe;
        color: #1e40af;
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

    .tree-actions {
        margin-left: auto;
        display: none;
        gap: 4px;
    }

    .tree-node:hover .tree-actions {
        display: flex;
    }

    .action-btn {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .add-btn {
        background-color: #10b981;
        color: white;
    }

    .add-btn:hover {
        background-color: #059669;
    }

    .delete-btn {
        background-color: #ef4444;
        color: white;
    }

    .delete-btn:hover {
        background-color: #dc2626;
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
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .calendar-day:hover {
        background-color: #e2e8f0;
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
    .calendar-day.has-event {
        position: relative;
    }
    .calendar-day.has-event::after {
        content: '●';
        position: absolute;
        top: 2px;
        right: 2px;
        font-size: 8px;
        color: #3b82f6;
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

                    <div class="w-4 h-1/4 bg-red-500 rounded-l-md cursor-pointer flex items-center justify-center text-white font-bold"
                     id="redTab"
                     style="writing-mode: vertical-rl; text-orientation: upright; font-size: 10px;">
                      EXPEDIENTE
                    </div>

                    <div class="w-4 h-1/4 bg-orange-500 rounded-l-md cursor-pointer flex items-center justify-center text-white font-bold" id="orangeTab"
                    style="writing-mode: vertical-rl; text-orientation: upright; font-size: 10px;">
                     LISTA EXP
                    </div>
                    <div class="w-4 h-1/4 bg-yellow-500 rounded-l-md cursor-pointer flex items-center justify-center text-white font-bold" id="yellowTab"
                    style="writing-mode: vertical-rl; text-orientation: upright; font-size: 10px;">
                     ARBOL_CON
                    </div>
                    <div class="w-4 h-1/4 bg-green-500 rounded-l-md cursor-pointer flex items-center justify-center text-white font-bold" id="greenTab"
                    style="writing-mode: vertical-rl; text-orientation: upright; font-size: 10px;">
                     CALENDARIO
                    </div>
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
                    <h2 class="text-xl md:text-2xl font-semibold text-gray-700 text-center mb-4" id="consultationDetailTitle">Selecciona un elemento</h2>
                    <div id="consultationDetailContent" class="w-full bg-white border border-gray-300 rounded-md p-6 overflow-y-auto" style="min-height: 200px; max-height: 400px;">
                        <p class="text-gray-600 text-center" id="consultationDetailText">Seleccione un elemento del árbol para ver sus detalles.</p>
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

<!-- Modal para agregar elementos al árbol -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Agregar Nuevo Elemento</h3>
            <button id="closeModal" class="text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
        </div>

        <form id="addElementForm">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Elemento:</label>
                <div class="flex gap-4">
                    <label class="flex items-center">
                        <input type="radio" name="elementType" value="carpeta" class="mr-2" checked>
                        <span>📁 Carpeta</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="elementType" value="hecho" class="mr-2">
                        <span>📄 Hecho</span>
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label for="elementName" class="block text-sm font-medium text-gray-700 mb-2">Nombre:</label>
                <input type="text" id="elementName" name="elementName" 
                       class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Ingrese el nombre del elemento" required>
            </div>

            <div id="hechoDetails" class="mb-4 hidden">
                <label for="elementDescription" class="block text-sm font-medium text-gray-700 mb-2">Descripción del Hecho:</label>
                <textarea id="elementDescription" name="elementDescription" rows="3"
                          class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Describa los detalles del hecho..."></textarea>

                <div class="mt-2">
                    <label for="elementDate" class="block text-sm font-medium text-gray-700 mb-2">Fecha del Hecho:</label>
                    <input type="date" id="elementDate" name="elementDate"
                           class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" id="cancelModal" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Agregar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Confirmar Eliminación</h3>
            <button id="closeDeleteModal" class="text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
        </div>

        <div class="mb-6">
            <p class="text-gray-600">¿Está seguro de que desea eliminar este elemento?</p>
            <p id="deleteElementName" class="font-semibold text-gray-800 mt-2"></p>
            <p class="text-red-600 text-sm mt-2">Esta acción no se puede deshacer.</p>
        </div>

        <div class="flex justify-end gap-2">
            <button type="button" id="cancelDelete" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                Cancelar
            </button>
            <button type="button" id="confirmDelete"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                Eliminar
            </button>
        </div>
    </div>
</div>

<!-- Modal para ver/editar detalles del día del calendario -->
<div id="dayDetailModal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 id="dayDetailModalTitle" class="text-lg font-semibold text-gray-800">Detalles del Día</h3>
            <button id="closeDayDetailModal" class="text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
        </div>

        <div class="mb-4">
            <label for="modalEventName" class="block text-sm font-medium text-gray-700 mb-2">Nombre del Evento/Archivo:</label>
            <input type="text" id="modalEventName" name="modalEventName" 
                   class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Ingrese el nombre del evento o archivo">
        </div>

        <div class="mb-4">
            <label for="modalEventDescription" class="block text-sm font-medium text-gray-700 mb-2">Descripción (Opcional):</label>
            <textarea id="modalEventDescription" name="modalEventDescription" rows="3"
                      class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Describa los detalles del evento..."></textarea>
        </div>

        <div class="flex justify-end gap-2">
            <button type="button" id="cancelDayDetailModal" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                Cancelar
            </button>
            <button type="button" id="saveDayDetail" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Guardar
            </button>
        </div>
    </div>
</div>

@php
    $expedientesData = $expedientes->map(function($expediente) {
        return [
            'id' => $expediente->numero_expediente,
            'date' => $expediente->fecha_creacion,
            'type' => $expediente->tipo_documento,
            'description' => $expediente->descripcion ?? '',
            'files' => $expediente->archivo_pdf ? [$expediente->archivo_pdf] : [],
        ];
    });
@endphp

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales para el árbol
    let treeData = [];
    let selectedElement = null;
    let elementToDelete = null;
    let currentExpedienteId = null;

    // Variables para el calendario
    let calendarEvents = {}; // Stores events: { 'YYYY-MM-DD': [{ name: 'Event Name', description: 'Details' }] }
    let selectedCalendarDate = null; // To store the date selected in the mini-calendar

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

    // Elementos del modal
    const addModal = document.getElementById('addModal');
    const deleteModal = document.getElementById('deleteModal');
    const addElementForm = document.getElementById('addElementForm');
    const elementTypeRadios = document.querySelectorAll('input[name="elementType"]');
    const hechoDetails = document.getElementById('hechoDetails');

    // Elementos del árbol
    const treeContainer = document.getElementById('treeContainer');
    const consultationDetailTitle = document.getElementById('consultationDetailTitle');
    const consultationDetailText = document.getElementById('consultationDetailText');

    // Calendar elements
    const dayDetailModal = document.getElementById('dayDetailModal');
    const dayDetailModalTitle = document.getElementById('dayDetailModalTitle');
    const modalEventNameInput = document.getElementById('modalEventName');
    const modalEventDescriptionInput = document.getElementById('modalEventDescription');
    const closeDayDetailModalBtn = document.getElementById('closeDayDetailModal');
    const cancelDayDetailModalBtn = document.getElementById('cancelDayDetailModal');
    const saveDayDetailBtn = document.getElementById('saveDayDetail');


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

    // Funciones para el árbol mejorado
    function createTreeElement(item, level = 0) {
        const div = document.createElement('div');
        div.className = 'tree-item';
        div.style.marginLeft = `${level * 20}px`;

        const nodeDiv = document.createElement('div');
        nodeDiv.className = 'tree-node';
        nodeDiv.dataset.id = item.id;
        nodeDiv.dataset.type = item.type;

        let icon = '📄';
        if (item.type === 'expediente') icon = '📋';
        else if (item.type === 'carpeta') icon = '📁';

        const iconSpan = document.createElement('span');
        iconSpan.className = 'tree-node-icon';
        iconSpan.innerHTML = item.children && item.children.length > 0 ? '▼' : '▶';

        const nameSpan = document.createElement('span');
        nameSpan.textContent = `${icon} ${item.name}`;

        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'tree-actions';

        const addBtn = document.createElement('button');
        addBtn.className = 'action-btn add-btn';
        addBtn.innerHTML = '+';
        addBtn.title = 'Agregar elemento';
        addBtn.onclick = (e) => {
            e.stopPropagation();
            openAddModal(item);
        };

        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'action-btn delete-btn';
        deleteBtn.innerHTML = '−';
        deleteBtn.title = 'Eliminar elemento';
        deleteBtn.onclick = (e) => {
            e.stopPropagation();
            openDeleteModal(item);
        };

        actionsDiv.appendChild(addBtn);
        actionsDiv.appendChild(deleteBtn);

        nodeDiv.appendChild(iconSpan);
        nodeDiv.appendChild(nameSpan);
        nodeDiv.appendChild(actionsDiv);

        nodeDiv.onclick = (e) => {
            e.stopPropagation();
            selectElement(item, nodeDiv);
            if (item.children && item.children.length > 0) {
                toggleChildren(div);
                iconSpan.innerHTML = childrenDiv.classList.contains('hidden') ? '▶' : '▼';
            }
        };

        div.appendChild(nodeDiv);

        if (item.children && item.children.length > 0) {
            const childrenDiv = document.createElement('div');
            childrenDiv.className = 'tree-node-children';

            item.children.forEach(child => {
                childrenDiv.appendChild(createTreeElement(child, level + 1));
            });

            div.appendChild(childrenDiv);
        }

        return div;
    }

    function toggleChildren(parentDiv) {
        const childrenDiv = parentDiv.querySelector('.tree-node-children');
        if (childrenDiv) {
            childrenDiv.classList.toggle('hidden');
        }
    }

    function selectElement(item, nodeElement) {
        // Remove previous selection
        document.querySelectorAll('.tree-node.selected').forEach(node => {
            node.classList.remove('selected');
        });

        // Add selection to current node
        nodeElement.classList.add('selected');
        selectedElement = item;

        // Update detail panel
        consultationDetailTitle.textContent = item.name;

        let detailContent = '';
        if (item.type === 'hecho') {
            detailContent = `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-semibold text-gray-800">Descripción:</h4>
                        <p class="text-gray-600">${item.description || 'Sin descripción'}</p>
                    </div>
                    ${item.date ? `
                    <div>
                        <h4 class="font-semibold text-gray-800">Fecha:</h4>
                        <p class="text-gray-600">${item.date}</p>
                    </div>
                    ` : ''}
                    ${item.details ? `
                    <div>
                        <h4 class="font-semibold text-gray-800">Detalles Adicionales:</h4>
                        ${item.details.estado ? `<p><strong>Estado:</strong> ${item.details.estado}</p>` : ''}
                        ${item.details.delito ? `<p><strong>Delito:</strong> ${item.details.delito}</p>` : ''}
                        ${item.details.fechaDetencion ? `<p><strong>Fecha de Detención:</strong> ${item.details.fechaDetencion}</p>` : ''}
                        ${item.details.acuerdos ? `
                        <div>
                            <strong>Acuerdos Generados:</strong>
                            <ul class="list-disc list-inside mt-1">
                                ${item.details.acuerdos.map(acuerdo => `<li>${acuerdo}</li>`).join('')}
                            </ul>
                        </div>
                        ` : ''}
                    </div>
                    ` : ''}
                </div>
            `;
        } else if (item.type === 'carpeta') {
            const childCount = item.children ? item.children.length : 0;
            detailContent = `
                <div>
                    <p class="text-gray-600">Carpeta del expediente</p>
                    <p class="text-sm text-gray-500 mt-2">Contiene ${childCount} elemento(s)</p>
                    <p class="text-sm text-gray-400 mt-4">Use los botones (+) y (-) para agregar o eliminar elementos en esta carpeta.</p>
                </div>
            `;
        } else if (item.type === 'expediente') {
            const totalChildren = countAllChildren(item);
            detailContent = `
                <div>
                    <p class="text-gray-600">Expediente principal del sistema</p>
                    <p class="text-sm text-gray-500 mt-2">Total de elementos: ${totalChildren}</p>
                    <p class="text-sm text-gray-400 mt-4">Este es el expediente raíz que contiene toda la información del caso.</p>
                </div>
            `;
        }

        consultationDetailText.innerHTML = detailContent;
    }

    function countAllChildren(item) {
        if (!item.children) return 0;
        let count = item.children.length;
        item.children.forEach(child => {
            count += countAllChildren(child);
        });
        return count;
    }

    function renderTree() {
        if (!treeContainer) return;
        treeContainer.innerHTML = '';
        treeData.forEach(item => {
            treeContainer.appendChild(createTreeElement(item));
        });
    }

    function generateId() {
        return 'item_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    function openAddModal(parentItem) {
        selectedElement = parentItem;
        document.getElementById('elementName').value = '';
        document.getElementById('elementDescription').value = '';
        document.getElementById('elementDate').value = '';
        elementTypeRadios[0].checked = true;
        hechoDetails.classList.add('hidden');
        addModal.classList.add('show');
    }

    function closeAddModal() {
        addModal.classList.remove('show');
        selectedElement = null;
    }

    function openDeleteModal(item) {
        elementToDelete = item;
        document.getElementById('deleteElementName').textContent = item.name;
        deleteModal.classList.add('show');
    }

    function closeDeleteModal() {
        deleteModal.classList.remove('show');
        elementToDelete = null;
    }

    function saveTreeToDatabase() {
        if (!currentExpedienteId) return;

        fetch('{{ route("expedientes.tree.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                expediente_id: currentExpedienteId,
                tree_data: treeData
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Árbol guardado exitosamente');
            }
        })
        .catch(error => {
            console.error('Error al guardar el árbol:', error);
        });
    }

    function loadTreeFromDatabase(expedienteId) {
        fetch(`{{ url('/api/expedientes') }}/${expedienteId}/tree`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.tree_data) {
                treeData = data.tree_data;
            } else {
                // Crear estructura básica si no existe
                treeData = [{
                    id: `exp_${expedienteId}`,
                    name: `Expediente: ${data.expediente?.numero_expediente || expedienteId}`,
                    type: 'expediente',
                    children: [
                        { id: generateId(), name: 'Inculpados', type: 'carpeta', children: [] },
                        { id: generateId(), name: 'Ofendidos', type: 'carpeta', children: [] },
                        { id: generateId(), name: 'Delitos', type: 'carpeta', children: [] },
                        { id: generateId(), name: 'Etapas Procesales', type: 'carpeta', children: [] }
                    ]
                }];
            }
            renderTree();
        })
        .catch(error => {
            console.error('Error al cargar el árbol:', error);
        });
    }

    // Event listeners para pestañas
    document.getElementById('redTab').addEventListener('click', () => showContent('red'));
    document.getElementById('orangeTab').addEventListener('click', () => showContent('orange'));
    document.getElementById('yellowTab').addEventListener('click', () => {
        showContent('yellow');
        if (currentExpedienteId) {
            loadTreeFromDatabase(currentExpedienteId);
        }
    });
    document.getElementById('greenTab').addEventListener('click', () => showContent('green'));

    // Event listeners para modales
    document.getElementById('closeModal').onclick = closeAddModal;
    document.getElementById('cancelModal').onclick = closeAddModal;
    document.getElementById('closeDeleteModal').onclick = closeDeleteModal;
    document.getElementById('cancelDelete').onclick = closeDeleteModal;

    // Close modals when clicking outside
    addModal.onclick = (e) => {
        if (e.target === addModal) closeAddModal();
    };

    deleteModal.onclick = (e) => {
        if (e.target === deleteModal) closeDeleteModal();
    };

    // Toggle hecho details based on element type
    elementTypeRadios.forEach(radio => {
        radio.onchange = () => {
            if (radio.value === 'hecho') {
                hechoDetails.classList.remove('hidden');
            } else {
                hechoDetails.classList.add('hidden');
            }
        };
    });

    // Form submission for adding elements
    addElementForm.onsubmit = (e) => {
        e.preventDefault();

        const formData = new FormData(addElementForm);
        const elementType = formData.get('elementType');
        const elementName = formData.get('elementName');
        const elementDescription = formData.get('elementDescription');
        const elementDate = formData.get('elementDate');

        if (!selectedElement) return;

        const newElement = {
            id: generateId(),
            name: elementName,
            type: elementType,
            children: elementType === 'carpeta' ? [] : undefined
        };

        if (elementType === 'hecho') {
            newElement.description = elementDescription;
            newElement.date = elementDate;
        }

        // Add to parent's children
        if (!selectedElement.children) {
            selectedElement.children = [];
        }
        selectedElement.children.push(newElement);

        // Re-render tree and save
        renderTree();
        saveTreeToDatabase();
        closeAddModal();
    };

    // Delete confirmation
    document.getElementById('confirmDelete').onclick = () => {
        if (!elementToDelete) return;

        // Find parent and remove element
        function removeElementFromTree(data, targetId) {
            for (let i = 0; i < data.length; i++) {
                if (data[i].id === targetId) {
                    data.splice(i, 1);
                    return true;
                }
                if (data[i].children) {
                    if (removeElementFromTree(data[i].children, targetId)) {
                        return true;
                    }
                }
            }
            return false;
        }

        removeElementFromTree(treeData, elementToDelete.id);
        renderTree();
        saveTreeToDatabase();

        // Clear detail panel if deleted element was selected
        if (selectedElement && selectedElement.id === elementToDelete.id) {
            consultationDetailTitle.textContent = 'Selecciona un elemento';
            consultationDetailText.innerHTML = '<p class="text-gray-600 text-center">Seleccione un elemento del árbol para ver sus detalles.</p>';
            selectedElement = null;
        }

        closeDeleteModal();
    };

    // Resto del código original (búsqueda de documentos, formularios, etc.)
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
    if (documentSearchInput) {
        documentSearchInput.addEventListener('input', function(e) {
            const code = e.target.value.trim();
            searchDocumentByCode(code);
        });
    }

    // Form submission
    if (document.getElementById('expedienteForm')) {
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
    }

    // PDF preview functionality
    if (pdfUpload) {
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
    }

    // Close button
    if (document.getElementById('close-btn')) {
        document.getElementById('close-btn').addEventListener('click', function() {
            if (confirm('¿Está seguro de que desea cerrar? Se perderán todos los datos no guardados.')) {
                documentSearchInput.value = '';
                clearForm();
                pdfUpload.value = '';
                document.getElementById('pdfPreview').classList.add('hidden');
                searchMessage.classList.add('hidden');
            }
        });
    }

    // Function to handle selecting an expediente and displaying its files
    function selectExpedienteWithDetails(expediente, row) {
        // Remove any previously selected row class
        document.querySelectorAll('.selectable-row').forEach(r => r.classList.remove('selected'));
        // Add selected class to the current row
        row.classList.add('selected');

        // Set current expediente for tree functionality
        currentExpedienteId = expediente.id;

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

            listItem.textContent = fileName;
            listItem.appendChild(document.createTextNode(' '));
            listItem.appendChild(fileLink);

            filesListElement.appendChild(listItem);
        } else {
            filesListElement.innerHTML = '<li class="text-gray-500">No hay archivos adjuntos para este expediente.</li>';
        }

        showContent('orange');
    }

    // Calendar functionality
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
        const startDayOfWeek = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1; // Monday is 0

        // Fill empty cells before the first day
        for (let i = 0; i < startDayOfWeek; i++) {
            miniCalendarBody.innerHTML += '<div></div>';
        }

        // Fill the days of the month
        for (let i = 1; i <= lastDay.getDate(); i++) {
            const dayDiv = document.createElement('div');
            const dayKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            
            dayDiv.className = 'calendar-day current-month';
            dayDiv.textContent = i;
            dayDiv.dataset.date = dayKey;

            // Add today's class
            if (year === new Date().getFullYear() && month === new Date().getMonth() && i === new Date().getDate()) {
                dayDiv.classList.add('today');
            }

            // Check if there are events for this day
            if (calendarEvents[dayKey] && calendarEvents[dayKey].length > 0) {
                dayDiv.classList.add('has-event');
            }

            // Add click listener for day details
            dayDiv.addEventListener('click', function() {
                openDayDetailModal(dayKey, i);
            });

            miniCalendarBody.appendChild(dayDiv);
        }
    }

    // Function to open the modal for day details
    function openDayDetailModal(dateKey, dayOfMonth) {
        selectedCalendarDate = dateKey;
        dayDetailModalTitle.textContent = `Detalles para ${dayOfMonth} de ${currentMonthYear.textContent}`;
        
        const eventsForDay = calendarEvents[dateKey] || [];
        modalEventNameInput.value = eventsForDay.length > 0 ? eventsForDay[0].name : '';
        modalEventDescriptionInput.value = eventsForDay.length > 0 ? eventsForDay[0].description : '';

        dayDetailModal.classList.add('show');
    }

    // Close modal handlers
    closeDayDetailModalBtn.onclick = () => {
        dayDetailModal.classList.remove('show');
        selectedCalendarDate = null;
    };
    cancelDayDetailModalBtn.onclick = () => {
        dayDetailModal.classList.remove('show');
        selectedCalendarDate = null;
    };

    // Save day detail handler
    saveDayDetailBtn.onclick = () => {
        if (!selectedCalendarDate) return;

        const eventName = modalEventNameInput.value.trim();
        const eventDescription = modalEventDescriptionInput.value.trim();

        if (!eventName) {
            alert('El nombre del evento es obligatorio.');
            return;
        }

        // Update or add the event
        const dayKey = selectedCalendarDate;
        if (!calendarEvents[dayKey]) {
            calendarEvents[dayKey] = [];
        }

        // For simplicity, we'll just store one event per day. If multiple are needed, the logic needs to change.
        calendarEvents[dayKey] = [{ name: eventName, description: eventDescription }];

        // Re-render the calendar to show the event indicator
        renderCalendar();

        // Close the modal
        closeDayDetailModalBtn.onclick();
    };

    // Modal to close on outside click
    dayDetailModal.onclick = (e) => {
        if (e.target === dayDetailModal) {
            closeDayDetailModalBtn.onclick();
        }
    };


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

    // Initial render of the calendar
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

    // --- Calendar Deadline Calculation ---
    const receptionDateInput = document.getElementById('receptionDate');
    const receptionTimeInput = document.getElementById('receptionTime');
    const alertTypeSelect = document.getElementById('alertTypeSelect');
    const calculateDeadlineBtn = document.getElementById('calculateDeadlineBtn');
    const deadlineResultSpan = document.getElementById('deadlineResult');
    const alertStatusSpan = document.getElementById('alertStatus');

    calculateDeadlineBtn.addEventListener('click', () => {
        const receptionDate = receptionDateInput.value;
        const receptionTime = receptionTimeInput.value;
        const alertType = alertTypeSelect.value;

        if (!receptionDate || !receptionTime) {
            alert('Por favor, ingrese la fecha y hora de recepción.');
            return;
        }

        const [hours, minutes] = receptionTime.split(':').map(Number);
        const receptionDateTime = new Date(receptionDate);
        receptionDateTime.setHours(hours);
        receptionDateTime.setMinutes(minutes);

        let deadline = new Date(receptionDateTime);
        let status = '';

        switch (alertType) {
            case 'radicacion': // 24 hours
                deadline.setHours(deadline.getHours() + 24);
                status = 'Radicación';
                break;
            case 'incidente': // Assuming 3 business days (Mon-Fri)
                for (let i = 0; i < 3; i++) {
                    deadline.setDate(deadline.getDate() + 1);
                    // Skip weekends
                    if (deadline.getDay() === 0) deadline.setDate(deadline.getDate() + 1); // Sunday
                    if (deadline.getDay() === 6) deadline.setDate(deadline.getDate() + 2); // Saturday
                }
                status = 'Incidente';
                break;
            case 'revision': // Assuming 5 business days
                for (let i = 0; i < 5; i++) {
                    deadline.setDate(deadline.getDate() + 1);
                    if (deadline.getDay() === 0) deadline.setDate(deadline.getDate() + 1);
                    if (deadline.getDay() === 6) deadline.setDate(deadline.getDate() + 2);
                }
                status = 'Revisión';
                break;
            case 'sentencia': // Assuming 10 business days
                for (let i = 0; i < 10; i++) {
                    deadline.setDate(deadline.getDate() + 1);
                    if (deadline.getDay() === 0) deadline.setDate(deadline.getDate() + 1);
                    if (deadline.getDay() === 6) deadline.setDate(deadline.getDate() + 2);
                }
                status = 'Sentencia';
                break;
            case 'caducidad': // Assuming 30 calendar days
                deadline.setDate(deadline.getDate() + 30);
                status = 'Caducidad';
                break;
            default:
                break;
        }

        // Format the deadline date and time
        const formattedDeadline = `${String(deadline.getDate()).padStart(2, '0')}/${String(deadline.getMonth() + 1).padStart(2, '0')}/${deadline.getFullYear()} ${String(deadline.getHours()).padStart(2, '0')}:${String(deadline.getMinutes()).padStart(2, '0')}`;

        deadlineResultSpan.textContent = formattedDeadline;
        alertStatusSpan.textContent = status;

        // Add the calculated deadline to the calendar as an event
        const deadlineDateKey = `${deadline.getFullYear()}-${String(deadline.getMonth() + 1).padStart(2, '0')}-${String(deadline.getDate()).padStart(2, '0')}`;
        const eventData = {
            event_date: deadlineDateKey,
            event_time: `${String(deadline.getHours()).padStart(2, '0')}:${String(deadline.getMinutes()).padStart(2, '0')}`,
            event_name: `Alerta: ${status}`,
            event_description: `Fecha Límite: ${formattedDeadline}`
        };

        // Save the event to the database
        fetch('{{ route("calendar.events.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(eventData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCalendarEventDates(); // Refresh calendar to show new event
            }
        })
        .catch(error => {
            console.error('Error saving deadline event:', error);
        });
    });
        // Format the deadline date and time
        const formattedDeadline = `${String(deadline.getDate()).padStart(2, '0')}/${String(deadline.getMonth() + 1).padStart(2, '0')}/${deadline.getFullYear()} ${String(deadline.getHours()).padStart(2, '0')}:${String(deadline.getMinutes()).padStart(2, '0')}`;

        deadlineResultSpan.textContent = formattedDeadline;
        alertStatusSpan.textContent = status;

        // Add the calculated deadline to the calendar as an event
        const deadlineDateKey = `${deadline.getFullYear()}-${String(deadline.getMonth() + 1).padStart(2, '0')}-${String(deadline.getDate()).padStart(2, '0')}`;
        if (!calendarEvents[deadlineDateKey]) {
            calendarEvents[deadlineKey] = [];
        }
        // Add event, preventing duplicates for the same type
        const eventExists = calendarEvents[deadlineDateKey].some(event => event.name === `Alerta: ${status}`);
        if (!eventExists) {
            calendarEvents[deadlineDateKey].push({
                name: `Alerta: ${status}`,
                description: `Fecha Límite: ${formattedDeadline}`
            });
        }
        
        renderCalendar(); // Re-render to show the new event indicator
    });

    // Muestra el contenido de la pestaña roja por defecto al cargar la página
    showContent('red');
});

</script>
@endsection