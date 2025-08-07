<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivoExpedientesTable extends Migration
{
    public function up()
    {
        Schema::create('archivo_expedientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained()->onDelete('cascade');
            $table->string('nombre_original');
            $table->string('ruta');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('archivo_expedientes');
    }
}

