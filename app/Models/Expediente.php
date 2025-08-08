<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Expediente extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_expediente',
        'tipo_documento',
        'fecha_creacion',
        'descripcion',
        'archivo_pdf',
        'tree_data',
        'estado'
    ];

    protected $casts = [
        'fecha_creacion' => 'date',
        
    ];

    

    // Scope for active expedientes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    // Scope for searching by number
    public function scopeBuscarPorNumero($query, $numero)
    {
        if ($numero) {
            return $query->where('numero_expediente', 'like', '%' . $numero . '%');
        }
        return $query;
    }

    // Accessor for formatted creation date
    public function getFechaCreacionFormattedAttribute()
    {
        return $this->fecha_creacion ? $this->fecha_creacion->format('d/m/Y') : null;
    }

    // Default estado to 'activo'
    protected $attributes = [
        'estado' => 'activo',
    ];
}
