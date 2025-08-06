<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expediente extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_expediente',
        'tipo_documento',
        'fecha_creacion',
        'descripcion',
        'archivo_pdf',
        'estado'
    ];

    protected $casts = [
        'fecha_creacion' => 'date'
    ];

    // Relación con archivos adjuntos (si en el futuro quieres expandir)
    public function archivos()
    {
        return $this->hasMany(ArchivoExpediente::class);
    }

    // Accessor para formatear la fecha
    public function getFechaCreacionFormattedAttribute()
    {
        return $this->fecha_creacion->format('d/m/Y');
    }

    // Scope para expedientes activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    // Scope para buscar por número de expediente
    public function scopeBuscarPorNumero($query, $numero)
    {
        return $query->where('numero_expediente', 'like', '%' . $numero . '%');
    }
}
