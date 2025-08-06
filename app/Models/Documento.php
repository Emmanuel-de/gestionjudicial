<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'type',
        'status',
        'description', // ¡Este es el campo que hemos añadido!
        'reception_date',
    ];

    /**
     * Obtener el estado del documento formateado.
     *
     * @return string
     */
    public function getFormattedReceptionDateAttribute()
    {
        return $this->reception_date->format('d/m/Y H:i');
    }
}
