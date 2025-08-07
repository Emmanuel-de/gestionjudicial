<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArchivoExpediente extends Model
{
    use HasFactory;

    protected $fillable = [
        'expediente_id',
        'nombre_original',
        'ruta',
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class);
    }
}

