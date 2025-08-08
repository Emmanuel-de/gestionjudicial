<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Poder Judicial - Tamaulipas</title>
    <!-- CSRF Token (¡IMPORTANTE para Laravel y peticiones AJAX!) -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Tailwind CSS CDN para el estilo -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN para los íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" xintegrity="sha512-Fo3rlrZj/k7ujZpL02d4A/1rK9n0nFp7fS7K/0tXl+bQ92P+7w7yVqCjX3y+4uUf7p1+2u/zB2X8J0z8lA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Estilos personalizados para la tipografía */
        body {
            font-family: 'Inter', sans-serif;
        }
        .message-box {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            z-index: 1000;
            display: none;
            text-align: center;
        }
        .message-box.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    
    <!-- Mensaje de confirmación personalizado -->
    <div id="message-box" class="message-box">
        <p id="message-content" class="text-gray-700 text-lg mb-4"></p>
        <button id="close-message-btn" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Aceptar</button>
    </div>

    <!-- Encabezado con franja gris -->
    <header class="bg-white shadow py-4">
        <div class="bg-gray-400 w-full h-8"></div>
        <div class="container mx-auto flex flex-wrap items-center justify-between px-4">
            <!-- Logo -->
            <div class="flex items-center">
    <a href="{{ url('/') }}" class="flex items-center">
        <img src="{{ asset('img/logo.png') }}" alt="Logo" class="mr-4">
        <div class="text-gray-600 text-sm font-semibold">
            PODER JUDICIAL<br>
            <span class="font-normal text-xs">-- TAMAULIPAS --</span>
        </div>
    </a>
</div>
            <!-- Botones de redes sociales -->
            <div class="flex space-x-2 mt-4 md:mt-0">
                <a href="https://www.facebook.com/PJTamaulipasMx/" target="_blank" class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700"><i class="fab fa-facebook-f"></i></a>
                <a href="https://x.com/PJTamaulipasMx" target="_blank" class="w-10 h-10 bg-blue-400 text-white rounded-full flex items-center justify-center hover:bg-blue-500"><i class="fab fa-twitter"></i></a>
                <a href="https://www.youtube.com/@pjtamaulipasmx" target="_blank" class="w-10 h-10 bg-red-600 text-white rounded-full flex items-center justify-center hover:bg-red-700"><i class="fab fa-youtube"></i></a>
                <a href="https://www.instagram.com/pjtamaulipasmx/" target="_blank" class="w-10 h-10 bg-pink-600 text-white rounded-full flex items-center justify-center hover:bg-pink-700"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </header>

    <!-- Contenido dinámico -->
    <main>
        @yield('content')
    </main>

    <!-- Scripts -->
    <script>
        // Función para mostrar el mensaje personalizado
        function showMessage(message) {
            const messageBox = document.getElementById('message-box');
            const messageContent = document.getElementById('message-content');
            messageContent.textContent = message;
            messageBox.classList.add('active');
        }
        
        // Función para ocultar el mensaje personalizado
        document.getElementById('close-message-btn').onclick = function() {
            document.getElementById('message-box').classList.remove('active');
        };
    </script>
    @stack('scripts') {{-- Este es crucial para los scripts de las vistas extendidas --}}
</body>
</html>
