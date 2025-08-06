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
                <h2 id="leftPageTitle" class="text-lg md:text-xl font-semibold text-gray-700 mb-4"></h2>

                <!-- Contenido de la primera página (Expediente Electrónico - Formulario de Carga) -->
                <div id="pageContentExpediente" class="w-full flex flex-col items-center">
                    <!-- Campo de entrada para búsqueda de documentos o número de documento -->
                    <input type="text" id="searchInputExpediente" class="w-2/3 md:w-1/2 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mb-6 md:mb-8" placeholder="Buscar documento...">
                    <!-- Área de texto grande para descripción o vista previa del documento -->
                    <textarea id="textareaExpediente" class="w-full h-48 md:h-64 p-3 border border-gray-300 rounded-md resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 mb-6" placeholder="Descripción detallada del documento..."></textarea>

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
                    <img src="https://placehold.co/100x100/A0A0A0/FFFFFF?text=LOGO" alt="Logo Institucional" class="mb-4 md:mb-6">
                    <h1 class="text-xl md:text-2xl font-semibold text-gray-700 text-center mb-6 md:mb-8">
                        EXPEDIENTES ELECTRÓNICOS
                        <br>
                        <span class="text-lg md:text-xl font-normal text-gray-500">— GESTIÓN DOCUMENTAL —</span>
                    </h1>

                    <!-- Formulario para crear expediente -->
                    <form id="expedienteForm" method="POST" action="{{ route('expedientes.store') }}" enctype="multipart/form-data" class="w-full">
                        @csrf
                        <input type="text" name="numero_expediente" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4" placeholder="Número de Expediente" required>
                        <input type="text" name="tipo_documento" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4" placeholder="Tipo de Documento" required>
                        <input type="date" name="fecha_creacion" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mb-6 md:mb-8" required>

                        <!-- Contenedor para los botones -->
                        <div class="flex space-x-4">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md shadow-md transition duration-300 ease-in-out">
                                REGISTRAR
                            </button>
                            <button type="button" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-md shadow-md transition duration-300 ease-in-out">
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
            'files' => [
                [
                    'name' => 'Documento 1',
                    'url' => asset('ruta/a/documento1.pdf'), // Ajusta esta ruta a tu lógica real
                ],
                [
                    'name' => 'Documento 2',
                    'url' => asset('ruta/a/documento2.pdf'),
                ]
            ],
        ];
    });
@endphp

<script>
    const expedientesData = @json($expedientesData);

    const treeContainer = document.getElementById("tree-container");
    const previewIframe = document.getElementById("preview-iframe");
    const fileName = document.getElementById("file-name");
    const fileDate = document.getElementById("file-date");
    const fileType = document.getElementById("file-type");
    const fileDescription = document.getElementById("file-description");

    function createTree(data) {
        treeContainer.innerHTML = ""; // Limpiar árbol existente

        data.forEach((expediente) => {
            const expedienteNode = document.createElement("div");
            expedienteNode.className = "mb-2";

            const expedienteTitle = document.createElement("button");
            expedienteTitle.className =
                "w-full text-left px-4 py-2 bg-white border border-gray-200 rounded hover:bg-gray-100 font-semibold";
            expedienteTitle.textContent = "Expediente: " + expediente.id;
            expedienteTitle.onclick = () => toggleVisibility(expedienteNode);

            expedienteNode.appendChild(expedienteTitle);

            const filesList = document.createElement("ul");
            filesList.className = "pl-4 mt-2 hidden";

            expediente.files.forEach((file) => {
                const fileItem = document.createElement("li");
                const fileButton = document.createElement("button");
                fileButton.className =
                    "text-blue-500 hover:underline text-sm";
                fileButton.textContent = file.name;
                fileButton.onclick = () => showFileDetails(file, expediente);

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

    function showFileDetails(file, expediente) {
        previewIframe.src = file.url;
        fileName.textContent = file.name;
        fileDate.textContent = expediente.date;
        fileType.textContent = expediente.type;
        fileDescription.textContent = expediente.description;
    }

    createTree(expedientesData);
</script>

@endsection