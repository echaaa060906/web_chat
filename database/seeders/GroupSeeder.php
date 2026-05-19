<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data grup lama agar tidak duplikat atau bentrok
        Schema::disableForeignKeyConstraints();
        DB::table('group_user')->truncate();
        Group::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Buat Grup Baru yang Segar
        $group = Group::create([
            'name' => 'Grup Programmer Real-time'
        ]);

        // 2. Ambil semua ID User tanpa terkecuali
        $allUserIds = User::pluck('id')->toArray();

        // 3. Daftarkan semua user menjadi anggota grup ini
        $group->users()->attach($allUserIds);
        
        $this->command->info('Grup berhasil dibuat dan semua user telah dimasukkan!');
    }
}