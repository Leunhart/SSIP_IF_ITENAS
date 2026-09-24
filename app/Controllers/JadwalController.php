<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\JadwalModel;
use App\Models\AsistenJadwalModel;
use App\Models\EventsModel;
use App\Models\UserModel;

class JadwalController extends BaseController
{
    protected $jadwalModel;
    protected $asistenJadwalModel;
    protected $eventModel;
    protected $userModel;

    public function __construct()
    {
        $this->jadwalModel        = new JadwalModel();
        $this->asistenJadwalModel = new AsistenJadwalModel();
        $this->eventModel         = new EventsModel();
        $this->userModel          = new UserModel();
    }

    public function index()
    {
        $data = [
            'title'     => 'Daftar Jadwal Lab',
            'schedules' => $this->jadwalModel->getProcessedJadwalData()
        ];

        return view('jadwal_card_view', $data);
    }

    public function praktikum()
    {
        $view = $this->request->getGet('view') ?? 'list';

        $data = [
            'title'        => 'Jadwal Praktikum Laboratorium',
            'schedules'    => $this->jadwalModel->getProcessedJadwalData(),
            'current_view' => $view
        ];

        return view('jadwal_praktikum_view', $data);
    }

    public function admin()
    {
        $jadwals = $this->jadwalModel->getProcessedJadwalData();
        $rows = [];
        
        foreach ($jadwals as $j) {
            $rawJadwal = $this->jadwalModel->find($j['id_jadwal']);

            $rows[] = [
                $j['id_jadwal'],
                $j['title'],
                $rawJadwal['kelas'] ?? '-', 
                $j['date'],
                $j['time'],
                $j['instructor'],
                $j['lab'],
                $j['assistants'] ?? '-', 
                $j['jenis'] ?? '-',
                $rawJadwal['id_event'] ?? '', 
                $rawJadwal['tanggal'] ?? '', 
                $rawJadwal['waktu_mulai'] ?? '', 
                $rawJadwal['waktu_selesai'] ?? '', 
                $rawJadwal['ruangan'] ?? '', 
                $rawJadwal['kelas'] ?? '', 
            ];
        }

        $assistants = $this->userModel->select('users.id, users.nama')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.role_id', 2) 
            ->findAll();
        
        $db = \Config\Database::connect();
        $ruangans = $db->table('ruangan')->orderBy('nama_ruangan', 'ASC')->get()->getResultArray();

        $data = [
            'title'      => 'Daftar Jadwal Lab',
            'schedules'  => ['rows' => $rows],
            'events'     => $this->eventModel->findAll(),
            'assistants' => $assistants,
            'ruangans'   => $ruangans
        ];

        return view('jadwal_admin_view', $data);
    }

    public function asistenJadwalSaya()
    {
        $sessionUser = session()->get('user');
        $id_user = $sessionUser['id'] ?? $sessionUser['id_user'] ?? null;

        if (!$id_user) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $jadwals = $this->jadwalModel->getProcessedJadwalSaya($id_user);

        $data = [
            'title'     => 'Jadwal Asistensi Saya',
            'schedules' => $jadwals
        ];

        return view('jadwal_saya_view', $data);
    }

    /**
     *  CREATE JADWAL 
     */
    public function store()
    {
        // Role authorization sudah ditangani oleh Route Filter (role:1,2) di Routes.php


        // 1. ATURAN VALIDASI PINTU DEPAN
        $rules = [
            'id_event'      => 'required|numeric',
            'tanggal'       => 'required|valid_date',
            'waktu_mulai'   => 'required',
            'waktu_selesai' => 'required',
            'ruangan'       => 'required|max_length[100]',
            'kelas'         => 'permit_empty|max_length[50]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 2. TANGKAP ERROR DATABASE DENGAN TRY-CATCH
        try {
            $data = [
                'id_event'      => $this->request->getPost('id_event'),
                'tanggal'       => $this->request->getPost('tanggal'),
                'waktu_mulai'   => $this->request->getPost('waktu_mulai'),
                'waktu_selesai' => $this->request->getPost('waktu_selesai'),
                'ruangan'       => $this->request->getPost('ruangan'),
                'kelas'         => $this->request->getPost('kelas'),
            ];

            // Cek konflik ruangan (Aman karena data sudah tervalidasi)
            $conflict = $this->jadwalModel->join('ruangan', 'ruangan.id_ruangan = jadwal.id_ruangan')
                ->where('ruangan.nama_ruangan', $data['ruangan'])
                ->where('tanggal', $data['tanggal'])
                ->where('waktu_mulai <', $data['waktu_selesai'])
                ->where('waktu_selesai >', $data['waktu_mulai'])
                ->first();

            if ($conflict) {
                return redirect()->back()->withInput()->with('error', 'Ruangan sudah digunakan pada waktu tersebut untuk praktikum lain.');
            }

            $this->jadwalModel->insert($data);
            return redirect()->to('/jadwal_admin')->with('success', 'Jadwal berhasil ditambahkan');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: Data tidak valid.');
        }
    }

    /**
     * UPDATE JADWAL (Aman dari SQLMap & Crash)
     */
    public function update($id)
    {
        // Role authorization sudah ditangani oleh Route Filter (role:1,2) di Routes.php


        // Pastikan ID berupa angka untuk mencegah error
        if (!is_numeric($id)) {
            return redirect()->to('/jadwal_admin')->with('error', 'ID Jadwal tidak valid.');
        }

        // 1. ATURAN VALIDASI PINTU DEPAN
        $rules = [
            'id_event'      => 'required|numeric',
            'tanggal'       => 'required|valid_date',
            'waktu_mulai'   => 'required',
            'waktu_selesai' => 'required',
            'ruangan'       => 'required|max_length[100]',
            'kelas'         => 'permit_empty|max_length[50]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 2. TANGKAP ERROR DATABASE DENGAN TRY-CATCH
        try {
            $data = [
                'id_event'      => $this->request->getPost('id_event'),
                'tanggal'       => $this->request->getPost('tanggal'),
                'waktu_mulai'   => $this->request->getPost('waktu_mulai'),
                'waktu_selesai' => $this->request->getPost('waktu_selesai'),
                'ruangan'       => $this->request->getPost('ruangan'),
                'kelas'         => $this->request->getPost('kelas'),
            ];

            // Cek konflik ruangan, kecualikan jadwal ini sendiri
            $conflict = $this->jadwalModel->join('ruangan', 'ruangan.id_ruangan = jadwal.id_ruangan')
                ->where('ruangan.nama_ruangan', $data['ruangan'])
                ->where('tanggal', $data['tanggal'])
                ->where('waktu_mulai <', $data['waktu_selesai'])
                ->where('waktu_selesai >', $data['waktu_mulai'])
                ->where('id_jadwal !=', $id)
                ->first();

            if ($conflict) {
                return redirect()->back()->withInput()->with('error', 'Ruangan sudah digunakan pada waktu tersebut untuk praktikum lain.');
            }

            $this->jadwalModel->update($id, $data);
            return redirect()->to('/jadwal_admin')->with('success', 'Data jadwal berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: Data tidak valid.');
        }
    }

    /**
     * SYNC ASISTEN JADWAL
     */
    public function syncAsisten()
    {
        $id_jadwal = $this->request->getPost('id_jadwal');
        $asisten_ids = $this->request->getPost('assigned_asisten'); // Array of user IDs

        if (!$id_jadwal) {
            return redirect()->back()->with('error', 'ID Jadwal tidak valid.');
        }

        // Hapus asisten lama
        $this->asistenJadwalModel->where('id_jadwal', $id_jadwal)->delete();

        // Tambahkan asisten baru
        if (!empty($asisten_ids) && is_array($asisten_ids)) {
            $insertData = [];
            foreach ($asisten_ids as $id_user) {
                $insertData[] = [
                    'id_jadwal' => $id_jadwal,
                    'id_user'   => $id_user
                ];
            }
            $this->asistenJadwalModel->insertBatch($insertData);
        }

        return redirect()->back()->with('success', 'Asisten berhasil ditugaskan ke jadwal.');
    }

    /**
     * 🔴 DELETE JADWAL (Aman dari Crash)
     */
    public function delete($id)
{
    if (!is_numeric($id)) return redirect()->to('/jadwal_admin')->with('error', 'ID tidak valid');
    try {
        $this->jadwalModel->delete($id);
        return redirect()->to('/jadwal_admin')->with('success', 'Jadwal berhasil dihapus');
    } catch (\Throwable $e) {
        return redirect()->to('/jadwal_admin')->with('error', 'Gagal menghapus jadwal. Pastikan jadwal ini tidak sedang digunakan.');
    }
}
}