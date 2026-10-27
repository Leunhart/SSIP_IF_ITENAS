<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\PeriodeModel;
use App\Models\AsistenPeriodeModel;

class Users extends BaseController
{
    protected UserModel $userModel;
    protected RoleModel $roleModel;
    protected PeriodeModel $periodeModel;
    protected AsistenPeriodeModel $asistenPeriodeModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->periodeModel = new PeriodeModel();
        $this->asistenPeriodeModel = new AsistenPeriodeModel();
    }

    // List semua users
    public function index()
    {
        $users = $this->userModel->select('users.*, roles.role_name')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->orderBy('users.created_at', 'DESC')
            ->findAll();

        $processedUsers = [];
        foreach ($users as $user) {
            $roleName = $user['role_name'] ?? 'user';
            $user['role'] = strtolower($roleName); 
            $processedUsers[] = $user;
        }

        return view('asisten_admin_list_view', [
            'title'   => 'Kelola Anggota Laboratorium',
            'asisten' => $processedUsers
        ]);
    }

    // form untuk add new user
    public function new()
    {
        return redirect()->to('/asisten_admin');
    }

    // Create user baru
    public function create()
    {
        $isAjax = $this->request->isAJAX();
        
        try {
            $nomor      = $this->request->getPost('nomor');
            $nama       = $this->request->getPost('nama');
            $password   = $this->request->getPost('password');
            $jurusan    = $this->request->getPost('jurusan');
            $role_id    = $this->request->getPost('role_id');
            $id_periode = $this->request->getPost('id_periode');

            // Fetch research links
            $google_scholar = $this->request->getPost('google_scholar');
            $sinta          = $this->request->getPost('sinta');
            $orcid          = $this->request->getPost('orcid');
            $scopus         = $this->request->getPost('scopus');

            // Check for duplicate nomor
            $existingUser = $this->userModel->where('nomor', $nomor)->first();
            if ($existingUser) {
                if ($isAjax) {
                    return $this->response->setContentType('application/json')->setJSON(['success' => false, 'message' => 'Nomor sudah digunakan']);
                }
                return redirect()->back()->with('error', 'Nomor sudah digunakan')->withInput();
            }

            $this->userModel->db->transStart();

            $data = [
                'nomor'    => $nomor,
                'nama'     => $nama,
                'password' => $password,
                'jurusan'  => $jurusan,
                'role_id'  => $role_id,
            ];

            if ($role_id == 1 || $role_id == 3) {
                $data['google_scholar'] = $google_scholar;
                $data['sinta']          = $sinta;
                $data['orcid']          = $orcid;
                $data['scopus']         = $scopus;
            }

            $userId = $this->userModel->insert($data);
            
            if (!$userId) {
                $this->userModel->db->transRollback();
                if ($isAjax) {
                    return $this->response->setContentType('application/json')->setJSON(['success' => false, 'message' => 'Failed to create user']);
                }
                return redirect()->back()->with('error', 'Failed to create user')->withInput();
            }

            if ($role_id == 2 && !empty($id_periode)) {
                $this->asistenPeriodeModel->insert([
                    'id_user'    => $userId,
                    'id_periode' => $id_periode,
                    'jabatan'    => 'Asisten Praktikum',
                ]);
            }

            // Handle file upload
            $foto = $this->request->getFile('foto');
            if ($foto && $foto->isValid() && !$foto->hasMoved()) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize = 2 * 1024 * 1024;

                if (!in_array($foto->getMimeType(), $allowedTypes) || $foto->getSize() > $maxSize) {
                    $this->userModel->db->transRollback();
                    return redirect()->back()->with('error', 'File tidak valid atau terlalu besar.');
                }

                $extension = strtolower($foto->getExtension());
                $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($extension, $validExtensions)) {
                    $this->userModel->db->transRollback();
                    return redirect()->back()->with('error', 'Ekstensi file tidak valid.');
                }

                $originalName = $foto->getName();
                if (preg_match('/[<>:"\/\\|?*\x00-\x1f]/', $originalName) || strpos($originalName, '..') !== false) {
                    $this->userModel->db->transRollback();
                    return redirect()->back()->with('error', 'Nama file tidak valid.');
                }

                $uploadPath = FCPATH . 'uploads/photos/' . $userId . '/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $newName = $foto->getRandomName();
                $foto->move($uploadPath, $newName);
                $fotoPath = 'uploads/photos/' . $userId . '/' . $newName;

                $this->userModel->update($userId, ['foto' => $fotoPath]);
            }

            $this->userModel->db->transComplete();

            if ($isAjax) {
                return $this->response->setContentType('application/json')->setJSON(['success' => true, 'message' => 'User created successfully']);
            }
            return redirect()->to('/asisten_admin')->with('success', 'User created successfully');
            
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->response->setContentType('application/json')->setJSON(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage())->withInput();
        }
    }

    // 🛡️ Dihilangkan 'int' pada $id agar tidak memicu Fatal Error dari SQLMap
    public function edit($id)
    {
        if (!is_numeric($id)) return redirect()->to('/asisten_admin')->with('error', 'ID tidak valid');
        return redirect()->to('/asisten_admin');
    }

    // 🛡️ Dihilangkan 'int' pada $id agar bisa divalidasi dengan aman
    public function update($id)
    {
        $isAjax = $this->request->isAJAX();

        // 🛡️ 1. VALIDASI NUMERIC MANUAL
        if (!is_numeric($id)) {
            if ($isAjax) {
                return $this->response->setContentType('application/json')->setJSON(['success' => false, 'message' => 'ID User tidak valid']);
            }
            return redirect()->to('/asisten_admin')->with('error', 'ID User tidak valid');
        }

        try {
            $user = $this->userModel->find($id);
            if (!$user) {
                if ($isAjax) {
                    return $this->response->setContentType('application/json')->setJSON(['success' => false, 'message' => 'User not found']);
                }
                return redirect()->to('/asisten_admin')->with('error', 'User not found');
            }

            $nomor      = $this->request->getPost('nomor');
            $nama       = $this->request->getPost('nama');
            $jurusan    = $this->request->getPost('jurusan');
            $role_id    = $this->request->getPost('role_id');
            $id_periode = $this->request->getPost('id_periode');
            $status_tugas = $this->request->getPost('status_tugas');

            $data = [];

            if ($nomor !== null && $nomor !== '' && $nomor != $user['nomor']) {
                $existingUser = $this->userModel->where('nomor', $nomor)->where('id !=', $id)->first();
                if ($existingUser) {
                    if ($isAjax) {
                        return $this->response->setContentType('application/json')->setJSON(['success' => false, 'message' => 'Nomor sudah digunakan oleh user lain']);
                    }
                    return redirect()->back()->with('error', 'Nomor sudah digunakan oleh user lain')->withInput();
                }
                $data['nomor'] = $nomor;
            }

            if ($nama !== null && $nama != $user['nama']) $data['nama'] = $nama;
            if ($jurusan !== null && $jurusan != $user['jurusan']) $data['jurusan'] = $jurusan;
            
            if ($role_id !== null && $role_id !== '') {
                $data['role_id'] = (int)$role_id;
            }

            $google_scholar = $this->request->getPost('google_scholar');
            $sinta          = $this->request->getPost('sinta');
            $orcid          = $this->request->getPost('orcid');
            $scopus         = $this->request->getPost('scopus');

            $currentRoleId = isset($data['role_id']) ? $data['role_id'] : $user['role_id'];
            if ($currentRoleId == 1 || $currentRoleId == 3) {
                if ($google_scholar !== null) $data['google_scholar'] = $google_scholar;
                if ($sinta !== null) $data['sinta'] = $sinta;
                if ($orcid !== null) $data['orcid'] = $orcid;
                if ($scopus !== null) $data['scopus'] = $scopus;
            } else {
                $data['google_scholar'] = null;
                $data['sinta']          = null;
                $data['orcid']          = null;
                $data['scopus']         = null;
            }

            $password = $this->request->getPost('password');
            if (!empty($password)) {
                $data['password'] = $password;
            }



            // Handle file upload
            $foto = $this->request->getFile('foto');
            if ($foto && $foto->isValid() && !$foto->hasMoved()) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize = 2 * 1024 * 1024;

                if (!in_array($foto->getMimeType(), $allowedTypes) || $foto->getSize() > $maxSize) {
                    return redirect()->back()->with('error', 'File tidak valid atau terlalu besar.');
                }

                $extension = strtolower($foto->getExtension());
                $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($extension, $validExtensions)) {
                    return redirect()->back()->with('error', 'Ekstensi file tidak valid.');
                }

                $originalName = $foto->getName();
                if (preg_match('/[<>:"\/\\|?*\x00-\x1f]/', $originalName) || strpos($originalName, '..') !== false) {
                    return redirect()->back()->with('error', 'Nama file tidak valid.');
                }

                $uploadPath = FCPATH . 'uploads/photos/' . $id . '/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $newName = $foto->getRandomName();
                $foto->move($uploadPath, $newName);
                $data['foto'] = 'uploads/photos/' . $id . '/' . $newName;
            }

            $this->userModel->db->transStart();

            $updateSuccess = empty($data) ? true : $this->userModel->update($id, $data);

            if ($updateSuccess) {
                if ($role_id == 2) {
                    if (!empty($id_periode)) {
                        $existingRelasi = $this->asistenPeriodeModel->where('id_user', $id)->first();
                        if ($existingRelasi) {
                            $this->asistenPeriodeModel->update($existingRelasi['id'], [
                                'id_periode' => $id_periode,
                                'status_tugas' => $status_tugas ?? 'belum selesai',
                            ]);
                        } else {
                            $this->asistenPeriodeModel->insert([
                                'id_user'    => $id,
                                'id_periode' => $id_periode,
                                'jabatan'    => 'Asisten Praktikum',
                                'status_tugas' => $status_tugas ?? 'belum selesai',
                            ]);
                        }
                    } else {
                        $this->asistenPeriodeModel->where('id_user', $id)->delete();
                    }
                } else {
                    $this->asistenPeriodeModel->where('id_user', $id)->delete();
                }

                $this->userModel->db->transComplete();

                if ($isAjax) {
                    return $this->response->setContentType('application/json')->setJSON(['success' => true, 'message' => 'User updated successfully']);
                }
                return redirect()->to('/asisten_admin')->with('success', 'User updated successfully');
            } else {
                $this->userModel->db->transRollback();
                if ($isAjax) {
                    return $this->response->setContentType('application/json')->setJSON(['success' => false, 'message' => 'Failed to update user']);
                }
                return redirect()->back()->with('error', 'Failed to update user')->withInput();
            }
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->response->setContentType('application/json')->setJSON(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage())->withInput();
        }
    }

    // 🔴 DELETE USER (Aman dari SQLMap & Constraint Error)
    // 🛡️ Dihilangkan 'int' pada $id
    public function delete($id)
    {
        $isAjax = $this->request->isAJAX();

        // 🛡️ 1. VALIDASI NUMERIC MANUAL
        if (!is_numeric($id)) {
            if ($isAjax) {
                return $this->response->setContentType('application/json')->setJSON(['success' => false, 'message' => 'ID User tidak valid']);
            }
            return redirect()->back()->with('error', 'ID User tidak valid');
        }

        // 🛡️ 2. TRY-CATCH UNTUK MENANGKAP CONSTRAINT ERROR
        try {
            if ($this->userModel->delete($id)) {
                if ($isAjax) {
                    return $this->response->setContentType('application/json')->setJSON(['success' => true, 'message' => 'User deleted successfully']);
                }
                return redirect()->to('/asisten_admin')->with('success', 'User deleted successfully');
            } else {
                if ($isAjax) {
                    return $this->response->setContentType('application/json')->setJSON(['success' => false, 'message' => 'Failed to delete user']);
                }
                return redirect()->back()->with('error', 'Failed to delete user');
            }
        } catch (\Exception $e) {
            // Tangkap exception (seperti Foreign Key Constraint jika user sedang dipakai di proyek)
            if ($isAjax) {
                return $this->response->setContentType('application/json')->setJSON(['success' => false, 'message' => 'Gagal menghapus user. Data masih terikat dengan tabel lain.']);
            }
            return redirect()->back()->with('error', 'Gagal menghapus user. Pastikan data pengguna ini tidak terikat pada publikasi atau proyek.');
        }
    }

    // Buat asisten_lab_admin view
    public function asistenAdmin()
    {
        // 🔹 DIUBAH: Menambahkan asisten_periode.id dan asisten_periode.status_tugas
        $users = $this->userModel->select('
                users.*, 
                roles.role_name as role, 
                periode.nama_periode,
                asisten_periode.id as id_asisten_periode,
                asisten_periode.status_tugas
            ')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->join('asisten_periode', 'asisten_periode.id_user = users.id', 'left')
            ->join('periode', 'periode.id_periode = asisten_periode.id_periode', 'left')
            ->findAll();

        $processedUsers = [];
        foreach ($users as $user) {
            $user['nama_periode'] = $user['nama_periode'] ?? '-';
            // Default nilai status jika null agar aman dioper ke View
            $user['status_tugas'] = $user['status_tugas'] ?? 'belum selesai'; 
            $processedUsers[] = $user;
        }

        $data = [
            'title'       => 'Kelola Anggota Laboratorium',
            'asisten'     => $processedUsers,
            'listPeriode' => $this->periodeModel->findAll()
        ];

        return view('asisten_admin_list_view', $data);
    }

    /**
     * Mengubah seluruh status tugas asisten mahasiswa menjadi 'selesai' atau 'belum selesai' secara massal
     */
    public function selesaikanSemuaStatus()
    {
        $isAjax = $this->request->isAJAX();
        $idPeriode = $this->request->getPost('id_periode');
        $status = $this->request->getPost('status_tugas') ?? 'selesai';

        if (!in_array($status, ['selesai', 'belum selesai'])) {
            $status = 'selesai';
        }

        try {
            $this->userModel->db->transStart();

            // 1. Ambil seluruh user asisten (role_id = 2)
            $asistenUsers = $this->userModel->where('role_id', 2)->findAll();
            if (empty($asistenUsers)) {
                $this->userModel->db->transRollback();
                $msg = 'Tidak ada user asisten mahasiswa yang terdaftar.';
                return $isAjax 
                    ? $this->response->setJSON(['success' => false, 'message' => $msg]) 
                    : redirect()->to('/asisten_admin')->with('error', $msg);
            }

            $asistenUserIds = array_column($asistenUsers, 'id');

            // 2. Query ke asisten_periode untuk asisten-asisten tersebut
            $builder = $this->asistenPeriodeModel->builder();
            $builder->whereIn('id_user', $asistenUserIds);

            if (!empty($idPeriode) && $idPeriode !== 'all') {
                $builder->where('id_periode', (int)$idPeriode);
            }

            // Update record asisten_periode yang sudah ada
            $builder->update([
                'status_tugas' => $status,
                'updated_at'   => date('Y-m-d H:i:s')
            ]);

            // 3. Untuk mahasiswa asisten yang belum memiliki relasi di asisten_periode sama sekali (jika target 'all'):
            if (empty($idPeriode) || $idPeriode === 'all') {
                $existingRelasi = $this->asistenPeriodeModel
                    ->whereIn('id_user', $asistenUserIds)
                    ->findAll();
                $existingUserIds = array_column($existingRelasi, 'id_user');

                $missingUserIds = array_diff($asistenUserIds, $existingUserIds);
                if (!empty($missingUserIds)) {
                    $defaultPeriode = $this->periodeModel->orderBy('id_periode', 'DESC')->first();
                    $defaultPeriodeId = $defaultPeriode['id_periode'] ?? 1;

                    $newRecords = [];
                    foreach ($missingUserIds as $mUserId) {
                        $newRecords[] = [
                            'id_user'      => $mUserId,
                            'id_periode'   => $defaultPeriodeId,
                            'jabatan'      => 'Asisten Praktikum',
                            'status_tugas' => $status,
                            'created_at'   => date('Y-m-d H:i:s'),
                            'updated_at'   => date('Y-m-d H:i:s'),
                        ];
                    }
                    if (!empty($newRecords)) {
                        $this->asistenPeriodeModel->insertBatch($newRecords);
                    }
                }
            }

            $this->userModel->db->transComplete();

            $statusLabel = ($status === 'selesai') ? 'Selesai' : 'Masih Berjalan';
            $msg = "Seluruh masa jabatan asisten mahasiswa berhasil diubah menjadi '{$statusLabel}'.";

            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => true, 
                    'message' => $msg,
                    'csrf_token' => csrf_hash()
                ]);
            }

            return redirect()->to('/asisten_admin')->with('success', $msg);

        } catch (\Exception $e) {
            $this->userModel->db->transRollback();
            $errorMsg = 'Terjadi kesalahan sistem: ' . $e->getMessage();
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $errorMsg]);
            }
            return redirect()->to('/asisten_admin')->with('error', $errorMsg);
        }
    }
}