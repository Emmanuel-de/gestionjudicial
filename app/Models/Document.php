<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'status',
        'reception_date'
    ];

    protected $casts = [
        'reception_date' => 'datetime',
    ];

    /**
     * Tipos de documentos disponibles
     */
    public static function getAvailableTypes()
    {
        return [
            'Oficio' => 'Oficio',
            'Sentencia' => 'Sentencia',
            'Radicacion' => 'Radicación'
        ];
    }

    /**
     * Estados disponibles
     */
    public static function getAvailableStatuses()
    {
        return [
            'Recibido' => 'Recibido',
            'Pendiente' => 'Pendiente',
            'Actualizar' => 'Actualizar'
        ];
    }

    /**
     * Obtener la fecha de recepción formateada
     */
    public function getFormattedReceptionDateAttribute()
    {
        return $this->reception_date ? $this->reception_date->format('d/m/Y H:i') : '';
    }

    /**
     * Obtener la clase CSS para el estado
     */
    public function getStatusColorClassAttribute()
    {
        return match($this->status) {
            'Recibido' => 'text-green-600',
            'Pendiente' => 'text-yellow-600',
            'Actualizar' => 'text-blue-600',
            default => 'text-gray-600'
        };
    }

    /**
     * Scope para búsqueda
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('code', 'like', "%{$term}%")
                    ->orWhere('type', 'like', "%{$term}%")
                    ->orWhere('status', 'like', "%{$term}%");
    }
}