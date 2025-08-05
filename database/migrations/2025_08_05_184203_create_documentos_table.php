<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id(); // Columna de ID auto-incremental
            $table->string('codigo')->unique(); // Código único del documento
            $table->string('tipo_documento'); // Tipo de documento (Oficio, Sentencia, Radicacion)
            $table->timestamp('fecha_recepcion')->nullable(); // Fecha y hora de recepción
            $table->enum('estado', ['Recibido', 'Pendiente', 'Actualizar'])->default('Recibido'); // Estado del documento
            // Puedes añadir más campos aquí según tus necesidades, por ejemplo:
            // $table->text('descripcion')->nullable();
            // $table->string('ruta_archivo_pdf')->nullable(); // Si vas a almacenar la ruta a un PDF
            // $table->text('ocr_texto')->nullable(); // Para el texto extraído por OCR

            $table->timestamps(); // Columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documentos');
    }
};

