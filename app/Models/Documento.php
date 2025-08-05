<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'tipo_documento',
        'fecha_recepcion',
        'estado',
        // Agrega aquí los otros campos si los incluyes en la migración
    ];
}
