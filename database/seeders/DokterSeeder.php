<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dokter;

class DokterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dokters = [
            ['nama' => 'dr. Budi Santoso, Sp.PD', 'spesialis' => 'Penyakit Dalam', 'sip' => '446.4/011/SIP-DS/401.103/2022', 'no_telp' => '0812-3344-9872', 'alamat' => 'RSUD Dr. Soedono, Jl. Dr. Sutomo No. 59, Madiun'],
            ['nama' => 'dr. Andi Wijaya, Sp.A', 'spesialis' => 'Anak', 'sip' => '446.4/025/SIP-DS/401.103/2023', 'no_telp' => '0821-4566-3129', 'alamat' => 'RS Santa Clara, Jl. Biliton No. 15, Madiun'],
            ['nama' => 'dr. Siti Aminah, Sp.OG', 'spesialis' => 'Kandungan', 'sip' => '446.4/082/SIP-DS/401.103/2021', 'no_telp' => '0813-8890-5412', 'alamat' => 'RSI Siti Aisyah, Jl. Mayjen Sungkono No. 38, Madiun'],
            ['nama' => 'dr. Rina Marlina, Sp.KK', 'spesialis' => 'Kulit & Kelamin', 'sip' => '446.4/044/SIP-DS/401.103/2022', 'no_telp' => '0857-3321-9988', 'alamat' => 'RS Griya Husada, Jl. D.I. Panjaitan, Madiun'],
            ['nama' => 'dr. Hendra Gunawan, Sp.S', 'spesialis' => 'Saraf', 'sip' => '446.4/057/SIP-DS/401.103/2020', 'no_telp' => '0819-4455-2231', 'alamat' => 'Klinik Saraf Utama, Jl. Pahlawan No. 20, Madiun'],
            ['nama' => 'dr. Ayu Lestari, Sp.THT', 'spesialis' => 'THT', 'sip' => '446.4/102/SIP-DS/401.103/2023', 'no_telp' => '0878-5643-1290', 'alamat' => 'Praktek Pribadi, Jl. Cokroaminoto No. 8, Madiun'],
            ['nama' => 'dr. Joko Susanto, Sp.JP', 'spesialis' => 'Jantung & Pembuluh', 'sip' => '446.4/033/SIP-DS/401.103/2021', 'no_telp' => '0812-9988-7745', 'alamat' => 'RSUD Dr. Soedono, Jl. Dr. Sutomo No. 59, Madiun'],
            ['nama' => 'dr. Maya Sari, Sp.M', 'spesialis' => 'Mata', 'sip' => '446.4/061/SIP-DS/401.103/2022', 'no_telp' => '0896-3421-8876', 'alamat' => 'Klinik Mata Madiun, Jl. Mastrip No. 12, Madiun'],
            ['nama' => 'dr. Doni Pratama', 'spesialis' => 'Umum', 'sip' => '446.4/115/SIP-DU/401.103/2024', 'no_telp' => '0822-5567-9098', 'alamat' => 'Klinik Muhammadiyah, Jl. Panglima Sudirman, Madiun'],
            ['nama' => 'drg. Fitriani, Sp.KG', 'spesialis' => 'Kedokteran Gigi', 'sip' => '446.4/029/SIP-DG/401.103/2021', 'no_telp' => '0813-2234-5543', 'alamat' => 'Praktek Dokter Gigi, Jl. Kapuas No. 45, Madiun']
        ];
        
        foreach($dokters as $d) {
            Dokter::create($d);
        }
    }
}
