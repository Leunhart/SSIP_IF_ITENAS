<?php

namespace App\Controllers;

use App\Models\ConfigSertifikatModel;
use App\Models\AsistenPeriodeModel;
use App\Models\UserModel;
use App\Libraries\JwtHelper;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SertifikatController extends BaseController
{
    protected ConfigSertifikatModel $configModel;
    protected AsistenPeriodeModel   $asistenPeriodeModel;

    private const UPLOAD_PATH = WRITEPATH . 'uploads/sertifikat/';

    public function __construct()
    {
        $this->configModel         = new ConfigSertifikatModel();
        $this->asistenPeriodeModel = new AsistenPeriodeModel();
    }

    // =========================================================================
    // BAGIAN ADMIN: Manajemen Konfigurasi Sertifikat
    // =========================================================================
    
    public function admin()
    {
        $data = [
            'title'  => 'Konfigurasi Sertifikat',
            'config' => $this->configModel->getConfig()
        ];
        return view('sertifikat_admin_view', $data); 
    }

    public function updateConfig()
    {
        $data = [
            'judul'               => $this->request->getPost('judul'),
            'deskripsi_template'  => $this->request->getPost('deskripsi_template'),
            'nama_kepala_lab'     => $this->request->getPost('nama_kepala_lab'),
            'nama_ketua_prodi'    => $this->request->getPost('nama_ketua_prodi'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        // Proses Background
        $templateFile = $this->request->getFile('template_gambar');
        if ($templateFile && $templateFile->isValid() && ! $templateFile->hasMoved()) {
            if (! in_array($templateFile->getMimeType(), ['image/jpeg', 'image/png'])) {
                return redirect()->back()->with('error', 'File template hanya boleh berformat JPG atau PNG.');
            }
            $newName = 'template_' . time() . '.' . $templateFile->getExtension();
            $templateFile->move(self::UPLOAD_PATH, $newName);
            $data['template_gambar'] = 'sertifikat/' . $newName;
        }

        // Proses TTD Kepala Lab
        $ttdKepala = $this->request->getFile('ttd_kepala_lab');
        if ($ttdKepala && $ttdKepala->isValid() && ! $ttdKepala->hasMoved()) {
            if ($ttdKepala->getMimeType() !== 'image/png') {
                return redirect()->back()->with('error', 'File TTD Kepala Lab harus berformat PNG (transparan).');
            }
            $newName = 'ttd_kepala_' . time() . '.png';
            $ttdKepala->move(self::UPLOAD_PATH, $newName);
            $data['ttd_kepala_lab'] = 'sertifikat/' . $newName;
        }

        // Proses TTD Ketua Prodi
        $ttdProdi = $this->request->getFile('ttd_ketua_prodi');
        if ($ttdProdi && $ttdProdi->isValid() && ! $ttdProdi->hasMoved()) {
            if ($ttdProdi->getMimeType() !== 'image/png') {
                return redirect()->back()->with('error', 'File TTD Ketua Prodi harus berformat PNG (transparan).');
            }
            $newName = 'ttd_prodi_' . time() . '.png';
            $ttdProdi->move(self::UPLOAD_PATH, $newName);
            $data['ttd_ketua_prodi'] = 'sertifikat/' . $newName;
        }

        // Proses Logo 1, 2, 3
        foreach ([1, 2, 3] as $i) {
            $logoFile = $this->request->getFile('logo_' . $i);
            if ($logoFile && $logoFile->isValid() && ! $logoFile->hasMoved()) {
                if (! in_array($logoFile->getMimeType(), ['image/jpeg', 'image/png'])) {
                    return redirect()->back()->with('error', "File Logo $i harus berformat PNG atau JPG.");
                }
                $newName = 'logo_' . $i . '_' . time() . '.' . $logoFile->getExtension();
                $logoFile->move(self::UPLOAD_PATH, $newName);
                $data['logo_' . $i] = 'sertifikat/' . $newName;
                if ($i === 1) {
                    $data['logo_tambahan'] = 'sertifikat/' . $newName;
                }
            }
        }

        $existing = $this->configModel->getConfig();
        if ($existing) {
            $this->configModel->update($existing['id'], $data);
        } else {
            $this->configModel->insert($data);
        }

        return redirect()->to('/sertifikat_admin')->with('success', 'Konfigurasi sertifikat berhasil diperbarui.');
    }

    public function deleteConfig()
    {
        $existing = $this->configModel->getConfig();

        if ($existing) {
            $filesToDelete = [
                $existing['template_gambar'],
                $existing['ttd_kepala_lab'],
                $existing['ttd_ketua_prodi'],
                $existing['logo_tambahan'] ?? null,
                $existing['logo_1'] ?? null,
                $existing['logo_2'] ?? null,
                $existing['logo_3'] ?? null,
            ];

            foreach ($filesToDelete as $file) {
                if (!empty($file)) {
                    $filePath = WRITEPATH . 'uploads/' . $file;
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }

            $this->configModel->delete($existing['id']);
            return redirect()->to('/sertifikat_admin')->with('success', 'Konfigurasi sertifikat beserta aset gambar berhasil dihapus.');
        }

        return redirect()->to('/sertifikat_admin')->with('error', 'Tidak ada konfigurasi yang bisa dihapus.');
    }

    public function updateStatusTugas(int $id_asisten_periode)
    {
        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }
        if (empty($input)) {
            $input = $this->request->getPost();
        }
        $statusBaru = $input['status_tugas'] ?? null;
        $allowed    = ['selesai', 'belum selesai'];

        if (! in_array($statusBaru, $allowed)) {
            return $this->response->setJSON(['status' => 'error', 'message' => "Nilai status tidak valid."])->setStatusCode(422);
        }

        $record = $this->asistenPeriodeModel->find($id_asisten_periode);
        if (! $record) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan.'])->setStatusCode(404);
        }

        $this->asistenPeriodeModel->update($id_asisten_periode, ['status_tugas' => $statusBaru]);
        return $this->response->setJSON(['status' => 'success', 'message' => "Status tugas asisten berhasil diubah."]);
    }

    /**
     * Endpoint untuk merender Pratinjau Sertifikat (Inline Image) di halaman Admin
     */
    public function preview()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $config = $this->configModel->getConfig();
        if (! $config || empty($config['template_gambar'])) {
            header('Content-Type: text/plain');
            die('ERROR 1: Template gambar belum diatur di database.');
        }

        $templatePath = WRITEPATH . 'uploads/' . $config['template_gambar'];
        if (!file_exists($templatePath)) {
            header('Content-Type: text/plain');
            die('ERROR 2: File fisik template tidak ditemukan di path: ' . $templatePath);
        }

        $canvas = $this->buildCertificateCanvas('NAMA ASISTEN CONTOH', '152022032', $config);
        if (!$canvas) {
            header('Content-Type: text/plain');
            die('ERROR 3: Fungsi buildCertificateCanvas() gagal. Kemungkinan file gambar corrupt atau format tidak didukung.');
        }

        ob_start();
        imagejpeg($canvas, null, 85); 
        $imageData = ob_get_clean();
        imagedestroy($canvas);

        header('Content-Type: image/jpeg');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo $imageData;
        exit(); 
    }

    /**
     * Endpoint untuk menyajikan gambar background template mentah
     */
    public function rawTemplate()
    {
        $config = $this->configModel->getConfig();
        if (!$config || empty($config['template_gambar'])) {
            return $this->response->setStatusCode(404, 'Template not set');
        }

        $path = WRITEPATH . 'uploads/' . $config['template_gambar'];
        if (!file_exists($path)) {
            return $this->response->setStatusCode(404, 'File not found');
        }

        $mime = mime_content_type($path) ?: 'image/jpeg';
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setBody(file_get_contents($path));
    }

    /**
     * Endpoint untuk menyajikan gambar tanda tangan mentah (PNG transparan)
     */
    public function rawTtd(string $type)
    {
        $config = $this->configModel->getConfig();
        if (!$config) {
            return $this->response->setStatusCode(404, 'Config not found');
        }

        $field = ($type === 'kepala') ? 'ttd_kepala_lab' : 'ttd_ketua_prodi';
        $file = $config[$field] ?? null;

        if (empty($file)) {
            return $this->response->setStatusCode(404, 'Signature image not set');
        }

        $path = WRITEPATH . 'uploads/' . $file;
        if (!file_exists($path)) {
            return $this->response->setStatusCode(404, 'File not found');
        }

        return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setBody(file_get_contents($path));
    }

    /**
     * Endpoint untuk menyajikan gambar logo tambahan mentah (PNG/JPG)
     */
    public function rawLogo(string $index = '1')
    {
        $config = $this->configModel->getConfig();
        if (!$config) {
            return $this->response->setStatusCode(404, 'Config not found');
        }

        $field = 'logo_' . $index;
        $file = $config[$field] ?? null;
        if (empty($file) && ($index === '1' || $index === 'logo')) {
            $file = $config['logo_tambahan'] ?? ($config['logo_1'] ?? null);
        }

        if (empty($file)) {
            return $this->response->setStatusCode(404, "Logo $index not set");
        }

        $path = WRITEPATH . 'uploads/' . $file;
        if (!file_exists($path)) {
            return $this->response->setStatusCode(404, 'File not found');
        }

        $mime = mime_content_type($path) ?: 'image/png';
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setBody(file_get_contents($path));
    }

    /**
     * Endpoint untuk menyimpan payload JSON koordinat tata letak baru
     */
    public function saveLayout()
    {
        $config = $this->configModel->getConfig();
        if (!$config) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Konfigurasi sertifikat belum dibuat. Buat konfigurasi terlebih dahulu.'
            ])->setStatusCode(404);
        }

        try {
            $layout = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $layout = null;
        }

        if (empty($layout)) {
            $layout = json_decode($this->request->getPost('layout'), true);
        }

        if (empty($layout)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data koordinat tidak valid.'
            ])->setStatusCode(400);
        }

        $updatedData = [
            'layout_config' => json_encode($layout),
            'updated_at'    => date('Y-m-d H:i:s')
        ];

        $this->configModel->update($config['id'], $updatedData);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Tata letak sertifikat berhasil disimpan.'
        ]);
    }


    // =========================================================================
    // HELPER: Penangkap ID User Multi-Jalur (Mendukung uid & id)
    // =========================================================================
    private function getCurrentUserId()
    {
        $session = session();
        
        // 1. Jalur Utama: Coba dari Token JWT (Mendukung 'id' atau 'uid')
        $token = $session->get('token');
        if ($token) {
            try {
                $decoded = JWT::decode($token, new Key(JwtHelper::getSecretKey(), 'HS256'));
                if (isset($decoded->id)) return (int) $decoded->id;
                if (isset($decoded->uid)) return (int) $decoded->uid;
            } catch (\Exception $e) {}
        }

        // 2. Jalur Alternatif: Session Key Standar CI4 ['user']['id']
        $user = $session->get('user');
        if (is_array($user) && isset($user['id'])) return (int) $user['id'];
        if (is_object($user) && isset($user->id)) return (int) $user->id;

        // 3. Jalur Cadangan Lainnya
        if ($session->has('id')) return (int) $session->get('id');
        if ($session->has('id_user')) return (int) $session->get('id_user');
        if ($session->has('user_id')) return (int) $session->get('user_id');

        return null;
    }

    // =========================================================================
    // BAGIAN ASISTEN: Halaman & Download Sertifikat
    // =========================================================================

    public function index()
    {
        $status = 'belum selesai';
        $userId = $this->getCurrentUserId();

        

        // ---------------------------------------------------------

        if ($userId) {
            $record = $this->asistenPeriodeModel
                           ->where('id_user', $userId)
                           ->orderBy('id', 'DESC')
                           ->first();
                           
            if ($record) {
                $status = $record['status_tugas'];
            }
        }

        $data = [
            'title'        => 'Sertifikat Apresiasi',
            'status_tugas' => $status
        ];
        
        return view('sertifikat_view', $data);
    }

    public function generate()
    {
        // 1. SAPU BERSIH SEMUA BUFFER AGAR TIDAK ADA ERROR/SPASI TERSELIP
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $userId = $this->getCurrentUserId();
        
        if (!$userId) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Sesi login tidak valid atau sudah berakhir. Silakan login ulang.'
            ])->setStatusCode(401);
        }

        $userModel = new UserModel();
        $userData  = $userModel->find($userId);

        if (!$userData) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Data pengguna tidak ditemukan di sistem.'
            ])->setStatusCode(404);
        }
        
        // Role authorization sudah ditangani oleh Route Filter (role:1,2) di Routes.php

        $namaUser = $userData['nama'];
        $nrpUser  = $userData['nomor'];

        $rekorAsisten = $this->asistenPeriodeModel
                             ->where('id_user', $userId)
                             ->where('status_tugas', 'selesai')
                             ->first();
                             
        if (! $rekorAsisten) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Sertifikat belum tersedia. Masa tugas Anda belum ditandai selesai oleh Admin.'
            ])->setStatusCode(403);
        }

        $config = $this->configModel->getConfig();
        if (! $config || empty($config['template_gambar'])) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Template sertifikat belum diatur oleh Kepala Lab.'
            ])->setStatusCode(503);
        }

        $canvas = $this->buildCertificateCanvas($namaUser, $nrpUser, $config);

        if (! $canvas) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Gagal memproses gambar sertifikat. Hubungi teknisi.'
            ])->setStatusCode(500);
        }

        $namaFile = 'Sertifikat_' . preg_replace('/\s+/', '_', $namaUser) . '_' . $nrpUser . '.jpg';

        ob_start();
        imagejpeg($canvas, null, 95); 
        $imageData = ob_get_clean();
        imagedestroy($canvas);

        return $this->response
            ->setHeader('Content-Type', 'image/jpeg')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $namaFile . '"')
            ->setHeader('Content-Length', (string) strlen($imageData))
            ->setBody($imageData);
    }
    /**
     * Endpoint pratinjau sertifikat khusus untuk asisten yang sedang login (Real Data)
     */
    public function previewAsisten()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->response->setStatusCode(401, 'Unauthorized');
        }

        $userModel = new UserModel();
        $userData  = $userModel->find($userId);
        if (!$userData) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        // Role authorization sudah ditangani oleh Route Filter (role:1,2) di Routes.php


        // Pastikan statusnya sudah selesai
        $rekorAsisten = $this->asistenPeriodeModel
                             ->where('id_user', $userId)
                             ->where('status_tugas', 'selesai')
                             ->first();
                             
        if (! $rekorAsisten) {
            return $this->response->setStatusCode(403, 'Certificate not available');
        }

        $config = $this->configModel->getConfig();
        if (! $config || empty($config['template_gambar'])) {
            return $this->response->setStatusCode(503, 'Template not configured');
        }

        // Render menggunakan data asli asisten (Nama & NRP)
        $canvas = $this->buildCertificateCanvas($userData['nama'], $userData['nomor'], $config);

        if (!$canvas) {
            return $this->response->setStatusCode(500, 'Canvas generation failed');
        }

        ob_start();
        imagejpeg($canvas, null, 85); 
        $imageData = ob_get_clean();
        imagedestroy($canvas);

        header('Content-Type: image/jpeg');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo $imageData;
        exit();
    }

    // =========================================================================
    // FUNGSI INTI: Image Processing GD Library
    // =========================================================================

    private function buildCertificateCanvas(string $namaUser, string $nrpUser, array $config)
    {
        $templatePath = WRITEPATH . 'uploads/' . $config['template_gambar'];
        if (! file_exists($templatePath)) return false;

        $imgInfo = getimagesize($templatePath);
        if ($imgInfo === false) return false;
        
        $mime = $imgInfo['mime'];
        $canvas = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($templatePath),
            'image/png'  => imagecreatefrompng($templatePath),
            default      => false,
        };

        if (! $canvas) return false;

        $imgWidth  = imagesx($canvas);
        $imgHeight = imagesy($canvas);
        
        // Robust Font File Resolver
        $locateFontFile = function(string $fontName) {
            $cleanName = str_replace('.ttf', '', $fontName);
            $candidates = [
                FCPATH . 'assets/fonts/' . $cleanName . '.ttf',
                ROOTPATH . 'public/assets/fonts/' . $cleanName . '.ttf',
                __DIR__ . '/../../public/assets/fonts/' . $cleanName . '.ttf',
                realpath(__DIR__ . '/../../public/assets/fonts') . '/' . $cleanName . '.ttf',
            ];
            foreach ($candidates as $path) {
                if ($path && file_exists($path)) return $path;
            }
            // Fallback jika font spesifik belum ada
            foreach ($candidates as $path) {
                if (!$path) continue;
                $fallback = dirname($path) . '/OpenSans-Bold.ttf';
                if (file_exists($fallback)) return $fallback;
            }
            return null;
        };

        // Decode layout_config dari database
        $layout = [];
        if (!empty($config['layout_config'])) {
            $layout = json_decode($config['layout_config'], true) ?: [];
        }

        // Helper untuk mendapatkan path font yang valid
        $getFontPath = function(string $key, string $defaultFontKey = 'OpenSans-Bold') use ($layout, $locateFontFile) {
            $fontKey = $layout[$key]['font_family'] ?? $defaultFontKey;
            $found = $locateFontFile($fontKey);
            if ($found) return $found;
            $foundDefault = $locateFontFile($defaultFontKey);
            if ($foundDefault) return $foundDefault;
            return $locateFontFile('OpenSans-Bold');
        };
        
        $colorBlack = imagecolorallocate($canvas, 30, 30, 30);
        $colorGray  = imagecolorallocate($canvas, 80, 80, 80);
        $colorLine  = imagecolorallocate($canvas, 180, 150, 80);

        $scale = $imgWidth / 2000;

        // Helper untuk mendapatkan koordinat piksel berdasarkan persentase
        $getCoords = function(string $key, float $defaultX, float $defaultY) use ($layout, $imgWidth, $imgHeight) {
            $xPct = isset($layout[$key]['x_pct']) ? (float)$layout[$key]['x_pct'] : $defaultX * 100;
            $yPct = isset($layout[$key]['y_pct']) ? (float)$layout[$key]['y_pct'] : $defaultY * 100;
            return [
                (int) (($xPct / 100) * $imgWidth),
                (int) (($yPct / 100) * $imgHeight)
            ];
        };

        // Helper untuk mendapatkan ukuran font dinamis
        $getFontSize = function(string $key, float $defaultVal) use ($layout, $scale) {
            $baseVal = isset($layout[$key]['font_size']) ? (float)$layout[$key]['font_size'] : $defaultVal;
            return $baseVal * $scale;
        };

        // Helper fungsi mencetak teks center-aligned dengan baseline matching yang presisi
        $printCenteredText = function($text, $fontSize, $fontFile, $centerX, $y, $color) use ($canvas) {
            if (empty($text)) return;
            if (file_exists($fontFile)) {
                $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
                $txtWidth = abs($bbox[2] - $bbox[0]);
                $x = (int) ($centerX - ($txtWidth / 2));
                // $bbox[7] adalah offset Y sudut kiri atas dari baseline (nilai negatif)
                $yDraw = (int) ($y - $bbox[7]);
                imagettftext($canvas, $fontSize, 0, $x, $yDraw, $color, $fontFile, $text);
            } else {
                $charWidth = imagefontwidth(5) * strlen($text);
                $x = (int) ($centerX - ($charWidth / 2));
                imagestring($canvas, 5, $x, $y, $text, $color);
            }
        };

        // Helper pemotong baris teks (word wrapping) berdasarkan batas lebar piksel
        $wrapText = function(string $text, float $fontSize, string $fontFile, int $maxWidthPx) {
            if (!file_exists($fontFile)) {
                return explode("\n", wordwrap($text, 75, "\n"));
            }
            $words = preg_split('/\s+/', trim($text));
            $lines = [];
            $currentLine = '';
            foreach ($words as $word) {
                $testLine = ($currentLine === '') ? $word : $currentLine . ' ' . $word;
                $box = imagettfbbox($fontSize, 0, $fontFile, $testLine);
                $testWidth = abs($box[2] - $box[0]);
                if ($testWidth > $maxWidthPx && $currentLine !== '') {
                    $lines[] = $currentLine;
                    $currentLine = $word;
                } else {
                    $currentLine = $testLine;
                }
            }
            if ($currentLine !== '') {
                $lines[] = $currentLine;
            }
            return $lines;
        };

        // Font Files
        $fontJudul     = $getFontPath('judul', 'PlayfairDisplay-Bold');
        $fontPreamble  = $getFontPath('preamble', 'OpenSans-Regular');
        $fontNama      = $getFontPath('nama', 'PlayfairDisplay-Bold');
        $fontNrp       = $getFontPath('nrp', 'Montserrat-Bold');
        $fontDesc      = $getFontPath('deskripsi', 'OpenSans-Regular');
        $fontNamaKiri  = $getFontPath('nama_kiri', 'Montserrat-Bold');
        $fontRoleKiri  = $getFontPath('role_kiri', 'OpenSans-Regular');
        $fontNamaKanan = $getFontPath('nama_kanan', 'Montserrat-Bold');
        $fontRoleKanan = $getFontPath('role_kanan', 'OpenSans-Regular');

        // Font Sizes Dinamis
        $fsJudul     = $getFontSize('judul', 65);  
        $fsPreamble  = $getFontSize('preamble', 26);  
        $fsNama      = $getFontSize('nama', 85); 
        $fsNrp       = $getFontSize('nrp', 26);  
        $fsDesc      = $getFontSize('deskripsi', 24);  
        $fsNamaKiri  = $getFontSize('nama_kiri', 24);  
        $fsRoleKiri  = $getFontSize('role_kiri', 19);  
        $fsNamaKanan = $getFontSize('nama_kanan', 24);  
        $fsRoleKanan = $getFontSize('role_kanan', 19);  

        // Koordinat Elemen
        list($xJudul, $yJudul)       = $getCoords('judul', 0.50, 0.22);
        list($xPreamble, $yPreamble) = $getCoords('preamble', 0.50, 0.32);
        list($xNama, $yNama)         = $getCoords('nama', 0.50, 0.42);
        list($xLine, $yLine)         = $getCoords('garis', 0.50, 0.49);
        list($xNrp, $yNrp)           = $getCoords('nrp', 0.50, 0.53);
        list($xDesc, $yDesc)         = $getCoords('deskripsi', 0.50, 0.60);

        list($xTtdKiri, $yTtdKiri)   = $getCoords('ttd_kiri', 0.30, 0.70);
        list($xNamaKiri, $yNamaKiri) = $getCoords('nama_kiri', 0.30, 0.85);
        list($xRoleKiri, $yRoleKiri) = $getCoords('role_kiri', 0.30, 0.885);

        list($xTtdKanan, $yTtdKanan)   = $getCoords('ttd_kanan', 0.70, 0.70);
        list($xNamaKanan, $yNamaKanan) = $getCoords('nama_kanan', 0.70, 0.85);
        list($xRoleKanan, $yRoleKanan) = $getCoords('role_kanan', 0.70, 0.885);

        // 1. Render Judul
        $judul = strtoupper($config['judul'] ?? 'SERTIFIKAT APRESIASI');
        $printCenteredText($judul, $fsJudul, $fontJudul, $xJudul, $yJudul, $colorBlack);

        // 2. Render Preamble
        $preamble = "Dengan bangga dipersembahkan kepada:";
        $printCenteredText($preamble, $fsPreamble, $fontPreamble, $xPreamble, $yPreamble, $colorGray);

        // 3. Render Nama
        $printCenteredText($namaUser, $fsNama, $fontNama, $xNama, $yNama, $colorBlack);

        // 4. Render Garis Pembatas (Dengan lebar dinamis persis visual editor)
        $lineLimitPct = isset($layout['garis']['width_pct']) ? (float)$layout['garis']['width_pct'] : 45;
        $lineWidthHalf = (int)($imgWidth * ($lineLimitPct / 2) / 100);
        $lineStartX = $xLine - $lineWidthHalf;
        $lineEndX = $xLine + $lineWidthHalf;
        imagesetthickness($canvas, max(2, (int)(3 * $scale)));
        imageline($canvas, $lineStartX, $yLine, $lineEndX, $yLine, $colorLine);

        // 5. Render NRP
        $printCenteredText($nrpUser, $fsNrp, $fontNrp, $xNrp, $yNrp, $colorBlack);

        // 6. Render Deskripsi (Word wrapping presisi dengan lebar dinamis)
        $deskripsi = $config['deskripsi_template'] ?? '';
        $descWidthPct = isset($layout['deskripsi']['width_pct']) ? (float)$layout['deskripsi']['width_pct'] : 65;
        $descMaxWidth = (int)($imgWidth * ($descWidthPct / 100));
        $descLines = $wrapText($deskripsi, $fsDesc, $fontDesc, $descMaxWidth);
        $lineHeightDesc = (int)($fsDesc * 1.45);
        $currentYDesc = $yDesc;
        
        foreach ($descLines as $line) {
            $printCenteredText(trim($line), $fsDesc, $fontDesc, $xDesc, $currentYDesc, $colorBlack);
            $currentYDesc += $lineHeightDesc;
        }

        // 7. Render Gambar Universal (TTD & Logo dengan Alpha Transparency)
        $renderImage = function($imgPath, $centerX, $yPos, $targetHBase = 130) use ($canvas, $scale) {
            if (!empty($imgPath) && file_exists(WRITEPATH . 'uploads/' . $imgPath)) {
                $fullPath = WRITEPATH . 'uploads/' . $imgPath;
                $imgInfo = @getimagesize($fullPath);
                if (!$imgInfo) return;

                $srcImg = match ($imgInfo['mime']) {
                    'image/jpeg' => @imagecreatefromjpeg($fullPath),
                    'image/png'  => @imagecreatefrompng($fullPath),
                    default      => false,
                };

                if ($srcImg) {
                    $srcW = imagesx($srcImg);
                    $srcH = imagesy($srcImg);
                    
                    $targetH = (int)($targetHBase * $scale);
                    $targetW = (int)($srcW * ($targetH / $srcH));
                    
                    $resized = imagecreatetruecolor($targetW, $targetH);
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                    imagefilledrectangle($resized, 0, 0, $targetW, $targetH, $transparent);
                    
                    imagecopyresampled($resized, $srcImg, 0, 0, 0, 0, $targetW, $targetH, $srcW, $srcH);
                    
                    $x = (int)($centerX - ($targetW / 2));
                    imagecopy($canvas, $resized, $x, $yPos, 0, 0, $targetW, $targetH);
                    
                    imagedestroy($resized);
                    imagedestroy($srcImg);
                }
            }
        };

        // Render Logo 1, 2, 3 (Jika Ada)
        $logoDefaults = [
            1 => ['x' => 0.12, 'y' => 0.10],
            2 => ['x' => 0.50, 'y' => 0.10],
            3 => ['x' => 0.88, 'y' => 0.10],
        ];

        foreach ([1, 2, 3] as $i) {
            $logoFile = $config['logo_' . $i] ?? ($i === 1 ? ($config['logo_tambahan'] ?? null) : null);
            if (!empty($logoFile)) {
                $key = 'logo_' . $i;
                list($xLogo, $yLogo) = $getCoords($key, $logoDefaults[$i]['x'], $logoDefaults[$i]['y']);
                $hLogo = isset($layout[$key]['height']) ? (float)$layout[$key]['height'] : (isset($layout['logo']['height']) && $i === 1 ? (float)$layout['logo']['height'] : 110);
                $renderImage($logoFile, $xLogo, $yLogo, $hLogo);
            }
        }

        // Render TTD Kiri (Kepala Lab)
        $hTtdKiri = isset($layout['ttd_kiri']['height']) ? (float)$layout['ttd_kiri']['height'] : 130;
        $renderImage($config['ttd_kepala_lab'] ?? '', $xTtdKiri, $yTtdKiri, $hTtdKiri);
        $printCenteredText($config['nama_kepala_lab'] ?? '', $fsNamaKiri, $fontNamaKiri, $xNamaKiri, $yNamaKiri, $colorBlack);
        $printCenteredText("Kepala Laboratorium", $fsRoleKiri, $fontRoleKiri, $xRoleKiri, $yRoleKiri, $colorGray);

        // Render TTD Kanan (Ketua Prodi)
        $hTtdKanan = isset($layout['ttd_kanan']['height']) ? (float)$layout['ttd_kanan']['height'] : 130;
        $renderImage($config['ttd_ketua_prodi'] ?? '', $xTtdKanan, $yTtdKanan, $hTtdKanan);
        $printCenteredText($config['nama_ketua_prodi'] ?? '', $fsNamaKanan, $fontNamaKanan, $xNamaKanan, $yNamaKanan, $colorBlack);
        $printCenteredText("Ketua Prodi", $fsRoleKanan, $fontRoleKanan, $xRoleKanan, $yRoleKanan, $colorGray);

        return $canvas;
    }
}