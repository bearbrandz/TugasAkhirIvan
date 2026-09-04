<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pasien;

class PasienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pasiens = [
            ['nama' => 'Anton Hermawan', 'jenis_kelamin' => 'L', 'tanggal_lahir' => '1981-05-12', 'no_telp' => '0857-8899-2341', 'alamat' => 'Perum Bumi Mas, Mojorejo'],
            ['nama' => 'Dwi Susanti', 'jenis_kelamin' => 'P', 'tanggal_lahir' => '1984-02-18', 'no_telp' => '0812-4456-9087', 'alamat' => 'Jl. H.A. Salim No. 14, Nambangan Lor'],
            ['nama' => 'Bagus Prakoso', 'jenis_kelamin' => 'L', 'tanggal_lahir' => '2014-02-15', 'no_telp' => '0819-3321-7654', 'alamat' => 'Jl. Yos Sudarso No. 9, Madiun Lor'],
            ['nama' => 'Citra Ayu', 'jenis_kelamin' => 'P', 'tanggal_lahir' => '1998-08-20', 'no_telp' => '0821-7788-5432', 'alamat' => 'Jl. Salak No. 22, Taman'],
            ['nama' => 'Eka Wahyuni', 'jenis_kelamin' => 'P', 'tanggal_lahir' => '2000-01-10', 'no_telp' => '0878-9900-1123', 'alamat' => 'Perum Taman Salak Indah'],
            ['nama' => 'Farah Nabila', 'jenis_kelamin' => 'P', 'tanggal_lahir' => '2004-04-04', 'no_telp' => '0896-5544-3322', 'alamat' => 'Jl. Serayu No. 11, Pandean'],
            ['nama' => 'Gunawan Wibisono', 'jenis_kelamin' => 'L', 'tanggal_lahir' => '1976-09-09', 'no_telp' => '0813-2211-9988', 'alamat' => 'Jl. Manggis No. 5, Kejuron'],
            ['nama' => 'Hadi Sucipto', 'jenis_kelamin' => 'L', 'tanggal_lahir' => '1991-07-25', 'no_telp' => '0857-4433-2211', 'alamat' => 'Jl. Kelapa Manis, Manisrejo'],
            ['nama' => 'Ika Puspitasari', 'jenis_kelamin' => 'P', 'tanggal_lahir' => '1969-03-15', 'no_telp' => '0812-6677-8899', 'alamat' => 'Jl. MT Haryono, Mojorejo'],
            ['nama' => 'Joko Susilo', 'jenis_kelamin' => 'L', 'tanggal_lahir' => '1966-12-01', 'no_telp' => '0822-1122-3344', 'alamat' => 'Jl. Wuni No. 3, Kejuron'],
            ['nama' => 'Kurniawan', 'jenis_kelamin' => 'L', 'tanggal_lahir' => '1991-06-18', 'no_telp' => '0819-8877-6655', 'alamat' => 'Jl. Merpati No. 8, Nambangan Kidul'],
            ['nama' => 'Laila Maharani', 'jenis_kelamin' => 'P', 'tanggal_lahir' => '2001-10-10', 'no_telp' => '0878-2233-4455', 'alamat' => 'Jl. Setia Budi No. 17, Kanigoro'],
            ['nama' => 'Muhammad Rizki', 'jenis_kelamin' => 'L', 'tanggal_lahir' => '1996-02-14', 'no_telp' => '0896-1122-8899', 'alamat' => 'Jl. Ciliwung No. 4, Pandean'],
            ['nama' => 'Nita Anggraini', 'jenis_kelamin' => 'P', 'tanggal_lahir' => '1991-08-08', 'no_telp' => '0813-5566-7788', 'alamat' => 'Jl. Trunojoyo, Madiun Lor'],
            ['nama' => 'Oka Pradana', 'jenis_kelamin' => 'L', 'tanggal_lahir' => '1997-09-22', 'no_telp' => '0857-9988-7766', 'alamat' => 'Perum asabri, Klegen']
        ];
        
        foreach($pasiens as $p) {
            Pasien::create($p);
        }
    }
}
