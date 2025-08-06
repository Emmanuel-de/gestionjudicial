<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Código del documento
            $table->string('type'); // Tipo de documento (Oficio, Sentencia, Radicación)
            $table->enum('status', ['Recibido', 'Pendiente', 'Actualizar'])->default('Pendiente'); // Estado
            $table->text('description')->nullable(); // Campo para la descripción
            $table->timestamp('reception_date'); // Fecha de recepción
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
