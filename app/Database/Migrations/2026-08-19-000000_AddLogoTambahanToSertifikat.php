<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLogoTambahanToSertifikat extends Migration
{
    public function up()
    {
        $fields = [
            'logo_tambahan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Path file logo tambahan/mitra',
                'after'      => 'ttd_ketua_prodi',
            ],
        ];
        $this->forge->addColumn('config_sertifikat', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('config_sertifikat', 'logo_tambahan');
    }
}
