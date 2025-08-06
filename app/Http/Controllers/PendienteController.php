<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;

class PendienteController extends Controller
{
    public function index()
    {
        // Aquí puedes filtrar solo documentos pendientes si deseas
        $documents = Document::all(); // o ->where('status', 'pending')->get();

        return view('pendiente', compact('documents'));
    }
}

