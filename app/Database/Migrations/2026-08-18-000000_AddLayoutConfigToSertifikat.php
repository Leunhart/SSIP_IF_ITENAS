<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLayoutConfigToSertifikat extends Migration
{
    public function up()
    {
        $fields = [
            'layout_config' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'JSON string of elements coordinates (X, Y percentages)',
                'after'   => 'ttd_ketua_prodi',
            ],
        ];
        $this->forge->addColumn('config_sertifikat', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('config_sertifikat', 'layout_config');
    }
}
