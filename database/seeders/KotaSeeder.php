<?php

namespace Database\Seeders;

use App\Models\Kota;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kota::create([
            'nama' => 'Sibolga',
            'kode' => 'SBG'
        ]);

        Kota::create([
            'nama' => 'Pandan',
            'kode' => 'PND'
        ]);

        Kota::create([
            'nama' => 'Medan',
            'kode' => 'MDN'
        ]);
    }
}
