<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $userData = [
            ['nomor' => '152022001', 'nama' => 'Jasman Pardede', 'no_telp' => '081234567890', 'jurusan' => 'Informatika', 'role_id' => 1], // Admin/Kepala Lab
            ['nomor' => '152022101', 'nama' => 'Admin Test', 'no_telp' => '081234567895', 'jurusan' => 'Informatika', 'role_id' => 1], // Admin Baru
            ['nomor' => '152022002', 'nama' => 'Prof. Siti Nurhaliza, Ph.D', 'no_telp' => '081234567891', 'jurusan' => 'Informatika', 'role_id' => 3],
            ['nomor' => '152022003', 'nama' => 'Dr. Budi Santoso, M.T', 'no_telp' => '081234567892', 'jurusan' => 'Informatika', 'role_id' => 3],
            ['nomor' => '152022004', 'nama' => 'Dr. Rina Sari, M.Kom', 'no_telp' => '081234567893', 'jurusan' => 'Informatika', 'role_id' => 3],
            ['nomor' => '152022005', 'nama' => 'Dr. Eko Prasetyo, Ph.D', 'no_telp' => '081234567894', 'jurusan' => 'Informatika', 'role_id' => 3],
        ];

        foreach ($userData as $user) {
            // Check if user already exists by nomor
            $existing = $this->db->table('users')->where('nomor', $user['nomor'])->get()->getRow();
            if (!$existing) {
                // Different password for admin vs dosen
                $password = ($user['role_id'] == 1) ? 'admin123' : 'dosen123';
                $user['password'] = password_hash($password, PASSWORD_DEFAULT);
                $user['created_at'] = date('Y-m-d H:i:s');
                $user['updated_at'] = date('Y-m-d H:i:s');
                $this->db->table('users')->insert($user);
            } else {
                // Update existing user data (important for admin data changes)
                $updateData = [
                    'nama' => $user['nama'],
                    'no_telp' => $user['no_telp'],
                    'jurusan' => $user['jurusan'],
                    'role_id' => $user['role_id'],
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                // Only update password if role changed or it's admin
                if ($existing->role_id != $user['role_id'] || $user['role_id'] == 1) {
                    $password = ($user['role_id'] == 1) ? 'admin123' : 'dosen123';
                    $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
                }

                $this->db->table('users')->where('nomor', $user['nomor'])->update($updateData);
            }
        }

        // Pastikan semua password ter-hash setelah seeding
        $this->ensureAllPasswordsHashed();
    }

    /**
     * Pastikan semua password di database sudah ter-hash
     */
    private function ensureAllPasswordsHashed()
    {
        $users = $this->db->table('users')->get()->getResultArray();

        foreach ($users as $user) {
            $passwordInfo = password_get_info($user['password']);

            // Jika password belum di-hash, hash sekarang
            if ($passwordInfo['algo'] === 0) {
                $hashed = password_hash($user['password'], PASSWORD_DEFAULT);
                $this->db->table('users')
                         ->where('id', $user['id'])
                         ->update(['password' => $hashed]);
            }
        }
    }
}