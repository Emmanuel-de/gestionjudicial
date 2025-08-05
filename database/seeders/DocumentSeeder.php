<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Document;
use Carbon\Carbon;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documents = [
            [
                'code' => 'COD-001',
                'type' => 'Oficio',
                'status' => 'Recibido',
                'reception_date' => Carbon::now()->subDays(2)->setTime(10, 0)
            ],
            [
                'code' => 'SEN-002',
                'type' => 'Sentencia',
                'status' => 'Recibido',
                'reception_date' => Carbon::now()->subDays(2)->setTime(11, 30)
            ],
            [
                'code' => 'RAD-003',
                'type' => 'Radicacion',
                'status' => 'Recibido',
                'reception_date' => Carbon::now()->subDays(2)->setTime(14, 0)
            ],
            [
                'code' => 'EXP-004',
                'type' => 'Oficio',
                'status' => 'Pendiente',
                'reception_date' => Carbon::now()->subDays(1)->setTime(9, 15)
            ],
            [
                'code' => 'DOC-005',
                'type' => 'Sentencia',
                'status' => 'Actualizar',
                'reception_date' => Carbon::now()->subDays(1)->setTime(16, 45)
            ],
            [
                'code' => 'REG-006',
                'type' => 'Radicacion',
                'status' => 'Pendiente',
                'reception_date' => Carbon::now()->setTime(8, 30)
            ]
        ];

        foreach ($documents as $document) {
            Document::create($document);
        }
    }
}