<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMultipleLogosToSertifikat extends Migration
{
    public function up()
    {
        $fields = [
            'logo_1' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Path file logo 1 (misal: Logo Universitas)',
                'after'      => 'ttd_ketua_prodi',
            ],
            'logo_2' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Path file logo 2 (misal: Logo Lab SSIP)',
                'after'      => 'logo_1',
            ],
            'logo_3' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Path file logo 3 (misal: Logo Mitra/Sponsor)',
                'after'      => 'logo_2',
            ],
        ];
        $this->forge->addColumn('config_sertifikat', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('config_sertifikat', ['logo_1', 'logo_2', 'logo_3']);
    }
}
