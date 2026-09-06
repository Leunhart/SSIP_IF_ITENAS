<?php

namespace App\Models;

use CodeIgniter\Model;

class ConfigSertifikatModel extends Model
{
    protected $table          = 'config_sertifikat';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = false; // Hanya updated_at yang dikelola manual
    protected $allowedFields  = [
        'template_gambar',
        'judul',
        'deskripsi_template',
        'nama_kepala_lab',
        'ttd_kepala_lab',
        'nama_ketua_prodi',
        'ttd_ketua_prodi',
        'logo_tambahan',
        'layout_config',
        'updated_at',
    ];

    /**
     * Ambil konfigurasi aktif (baris pertama / satu-satunya).
     * Seluruh sistem hanya memiliki SATU baris konfigurasi sertifikat.
     *
     * @return array|null
     */
    public function getConfig(): ?array
    {
        return $this->first();
    }
}
