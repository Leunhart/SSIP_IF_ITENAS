# Dokumentasi Use Case Diagram - Website SSIP IF ITENAS

Dokumen ini berisi use case diagram untuk aplikasi utama **SSIP (Sistem Informasi dan Pengelolaan Laboratorium Informatika Itenas)** serta detail use case diagram untuk masing-masing fitur/modul yang ada. Seluruh diagram dibuat menggunakan kode **Mermaid** dengan orientasi **vertikal (Top-Down)** agar lebih mudah dibaca dan digabungkan ke laporan.

---

## 👥 Aktor Sistem
Sistem SSIP memiliki 4 aktor utama dengan peran sebagai berikut:
1. **Pengunjung Umum / Mahasiswa (Public Guest)**: Pengguna tanpa login yang dapat mengakses informasi publik seperti profil lab, berita, jadwal praktikum publik, penelitian, publikasi, modul praktikum, dan formulir rekrutmen/kontak.
2. **Asisten (Asisten Laboratorium)**: Anggota lab terdaftar yang memiliki hak akses untuk login, melihat jadwal mengajar pribadi (*Jadwal Saya*), dan melakukan klaim serta mengunduh *Sertifikat Apresiasi* jika masa tugasnya telah diselesaikan.
3. **Dosen**: Staff pengajar/dosen yang memiliki hak akses login dan melihat jadwal mengajar pribadi.
4. **Admin (Super Admin)**: Pengelola sistem dengan hak akses penuh untuk melakukan operasi CRUD (Create, Read, Update, Delete) pada seluruh data master, mengelola konfigurasi sertifikat, mengubah status tugas asisten, serta mengatur hak akses dinamis (RBAC) untuk Asisten dan Dosen.

---

## 🗺️ 1. Main App Use Case Diagram (Overview)
Diagram ini menggambarkan gambaran umum hubungan seluruh aktor dengan modul-modul utama di dalam sistem SSIP.

```mermaid
graph TD
    %% Actors Definition
    Public((👤 Mahasiswa / Umum))
    Asisten((👤 Asisten Lab))
    Dosen((👤 Dosen))
    Admin((👑 Admin))

    %% System Boundary
    subgraph "Sistem Informasi & Pengelolaan Laboratorium (SSIP)"
        UC_Home([Lihat Profil Lab, Visi Misi & Organisasi])
        UC_News([Lihat Berita & Kegiatan])
        UC_Schedule_Pub([Lihat Jadwal Praktikum])
        UC_Proj_Pub([Lihat Riset & Project Lab])
        UC_Modul_Pub([Lihat & Unduh Modul])
        UC_Recruit_Pub([Lihat Lowongan Rekrutmen])
        UC_Contact([Kirim Pesan Kontak])
        
        UC_Auth([Login, Logout & Kelola Profil])
        
        UC_Sched_Aslab([Lihat Jadwal Saya])
        UC_Claim_Cert([Klaim & Unduh Sertifikat])
        
        UC_M_Users([Kelola Anggota & Periode Asisten])
        UC_M_Sched([Kelola Jadwal Praktikum])
        UC_M_Modul([Kelola Modul Praktikum])
        UC_M_Riset([Kelola Riset & Publikasi])
        UC_M_Proj([Kelola Project Lab & Member])
        UC_M_Cert([Kelola Template & Status Sertifikat])
        UC_M_Recruit([Kelola lowongan Rekrutmen])
        UC_M_RBAC([Kelola Hak Akses RBAC])
        UC_M_General([Kelola Berita, Event & Ruangan])
    end

    %% Relations for Public Guest
    Public --> UC_Home
    Public --> UC_News
    Public --> UC_Schedule_Pub
    Public --> UC_Proj_Pub
    Public --> UC_Modul_Pub
    Public --> UC_Recruit_Pub
    Public --> UC_Contact
    Public --> UC_Auth

    %% Relations for Asisten Lab
    Asisten --> UC_Auth
    Asisten --> UC_Sched_Aslab
    Asisten --> UC_Claim_Cert
    
    %% Relations for Dosen
    Dosen --> UC_Auth
    Dosen --> UC_Sched_Aslab
    
    %% Relations for Admin
    Admin --> UC_Auth
    Admin --> UC_M_Users
    Admin --> UC_M_Sched
    Admin --> UC_M_Modul
    Admin --> UC_M_Riset
    Admin --> UC_M_Proj
    Admin --> UC_M_Cert
    Admin --> UC_M_Recruit
    Admin --> UC_M_RBAC
    Admin --> UC_M_General
```

---

## 🛠️ 2. Detail Use Case Diagram per Fitur

Berikut adalah rincian use case diagram untuk setiap fitur dengan format visual vertikal:

### 2.1. Fitur Autentikasi & Manajemen Profil
Fitur ini menangani proses masuk ke sistem, pengamanan sesi (session security), pembatasan percobaan login (throttling), serta pengelolaan data profil pengguna.

```mermaid
graph TD
    User((👤 Pengguna Terautentikasi))
    Guest((👤 Guest / Pengunjung))

    subgraph "Sistem Autentikasi & Profil"
        UC_Login([Melakukan Login])
        UC_Logout([Melakukan Logout])
        UC_Profile([Melihat Profil Pribadi])
        UC_Update_Profile([Mengubah Data Profil])
        UC_Throttle([Throttle Login / Batasi Percobaan])
        UC_Secure_Sess([Proteksi Sesi Keamanan])
    end

    Guest --> UC_Login
    UC_Login -.->|include| UC_Throttle
    
    User --> UC_Profile
    User --> UC_Update_Profile
    User --> UC_Logout
    UC_Profile -.->|include| UC_Secure_Sess
```
* **Deskripsi**: Guest memasukkan NRP/NIDN dan password. Sistem membatasi percobaan login (throttle) untuk keamanan dari serangan brute-force. Setelah masuk, Pengguna Terautentikasi (Admin/Asisten/Dosen) dapat mengelola profil pribadi, mengubah password, dan melakukan logout dengan sesi yang terproteksi.

---

### 2.2. Fitur Kelola Anggota Laboratorium (Users & Periode)
Modul ini digunakan oleh Admin untuk mengelola akun anggota laboratorium, riwayat jabatan, serta periode aktif asisten.

```mermaid
graph TD
    Admin((👑 Admin))

    subgraph "Manajemen Anggota Lab (Users)"
        UC_List_User([Melihat Daftar Anggota])
        UC_Create_User([Menambah Anggota Baru])
        UC_Update_User([Mengubah Detail Anggota])
        UC_Delete_User([Menghapus Anggota])
        UC_Periode([Kelola Jabatan & Periode Asisten])
    end

    Admin --> UC_List_User
    Admin --> UC_Create_User
    Admin --> UC_Update_User
    Admin --> UC_Delete_User
    Admin --> UC_Periode
```
* **Deskripsi**: Admin dapat melakukan manajemen data pengguna laboratorium, menetapkan peran (Role), serta mengatur periode kerja asisten dan jabatan mereka pada periode aktif tertentu.

---

### 2.3. Fitur Kelola Jadwal Praktikum & Jadwal Saya
Fitur ini mencakup penjadwalan sesi praktikum secara keseluruhan dan fitur jadwal pribadi untuk asisten dan dosen.

```mermaid
graph TD
    Public((👤 Mahasiswa / Umum))
    Aslab((👤 Asisten / Dosen))
    Admin((👑 Admin))

    subgraph "Manajemen Jadwal Praktikum"
        UC_View_Pub([Melihat Jadwal Praktikum Publik])
        UC_View_Saya([Melihat Jadwal Saya])
        UC_Create_Sched([Membuat Jadwal Baru])
        UC_Update_Sched([Mengubah Jadwal])
        UC_Delete_Sched([Menghapus Jadwal])
        UC_Sync_Aslab([Sinkronisasi Asisten Jadwal])
    end

    Public --> UC_View_Pub
    Aslab --> UC_View_Saya
    
    Admin --> UC_Create_Sched
    Admin --> UC_Update_Sched
    Admin --> UC_Delete_Sched
    Admin --> UC_Sync_Aslab
```
* **Deskripsi**: Mahasiswa dapat melihat jadwal praktikum umum di halaman publik. Asisten atau Dosen yang login dapat melihat agenda mengajar spesifik mereka di menu *Jadwal Saya*. Admin berhak mengelola seluruh jadwal praktikum, menetapkan hari/jam, serta mensinkronisasikan asisten yang bertugas pada jadwal tersebut.

---

### 2.4. Fitur Kelola Modul Praktikum
Modul ini berfungsi untuk menyimpan, mendistribusikan, dan mempratinjau modul praktikum yang digunakan di lingkungan program studi.

```mermaid
graph TD
    Public((👤 Mahasiswa / Umum))
    Admin((👑 Admin))

    subgraph "Manajemen Modul Praktikum"
        UC_View_Modul([Melihat Daftar Modul])
        UC_Preview_Modul([Pratinjau Modul PDF])
        UC_Download_Modul([Mengunduh Modul])
        UC_Create_Modul([Mengunggah Modul Baru])
        UC_Update_Modul([Mengubah Modul])
        UC_Delete_Modul([Menghapus Modul])
    end

    Public --> UC_View_Modul
    Public --> UC_Preview_Modul
    Public --> UC_Download_Modul

    Admin --> UC_Create_Modul
    Admin --> UC_Update_Modul
    Admin --> UC_Delete_Modul
```
* **Deskripsi**: Mahasiswa secara bebas dapat mencari, melakukan pratinjau (preview), dan mengunduh berkas modul praktikum. Admin memiliki wewenang mengunggah berkas PDF modul baru, mengedit informasi modul, serta menghapusnya dari repositori server.

---

### 2.5. Fitur Kelola Proyek Riset & Penelitian
Menyajikan informasi mengenai penelitian-penelitian aktif yang dilakukan oleh dosen dan asisten di laboratorium.

```mermaid
graph TD
    Public((👤 Mahasiswa / Umum))
    Admin((👑 Admin))

    subgraph "Manajemen Proyek Riset & Penelitian"
        UC_View_Riset([Melihat Daftar Riset])
        UC_Detail_Riset([Melihat Detail Riset])
        UC_Create_Riset([Menambah Riset Baru])
        UC_Update_Riset([Mengubah Data Riset])
        UC_Delete_Riset([Menghapus Data Riset])
    end

    Public --> UC_View_Riset
    Public --> UC_Detail_Riset

    Admin --> UC_Create_Riset
    Admin --> UC_Update_Riset
    Admin --> UC_Delete_Riset
```
* **Deskripsi**: Publik dapat mengeksplorasi proyek riset serta membaca detail penelitian laboratorium. Admin mengelola (tambah, edit, hapus) katalog riset tersebut agar data selalu diperbarui.

---

### 2.6. Fitur Kelola Publikasi Ilmiah
Modul yang mendokumentasikan karya tulis ilmiah, jurnal, atau prosiding yang diterbitkan oleh civitas akademika laboratorium.

```mermaid
graph TD
    Public((👤 Mahasiswa / Umum))
    Admin((👑 Admin))

    subgraph "Manajemen Publikasi Ilmiah"
        UC_View_Publikasi([Melihat Daftar Publikasi])
        UC_Create_Publikasi([Menambah Publikasi Baru])
        UC_Update_Publikasi([Mengubah Publikasi])
        UC_Delete_Publikasi([Menghapus Publikasi])
    end

    Public --> UC_View_Publikasi

    Admin --> UC_Create_Publikasi
    Admin --> UC_Update_Publikasi
    Admin --> UC_Delete_Publikasi
```
* **Deskripsi**: Pengunjung dapat melihat daftar artikel ilmiah yang telah terbit. Admin bertindak sebagai kurator yang mengelola entri publikasi ilmiah tersebut.

---

### 2.7. Fitur Kelola Project Lab
Digunakan untuk mencatat proyek perangkat lunak atau sistem yang dikembangkan di laboratorium (termasuk daftar pengembang/anggota project).

```mermaid
graph TD
    Public((👤 Mahasiswa / Umum))
    Admin((👑 Admin))

    subgraph "Manajemen Project Laboratorium"
        UC_View_Proj([Melihat Daftar Project Lab])
        UC_Detail_Proj([Melihat Detail Project Lab])
        UC_Create_Proj([Menambah Project Lab Baru])
        UC_Update_Proj([Mengubah Project Lab])
        UC_Delete_Proj([Menghapus Project Lab])
        UC_Add_Member([Mengelola Anggota Project])
    end

    Public --> UC_View_Proj
    Public --> UC_Detail_Proj

    Admin --> UC_Create_Proj
    Admin --> UC_Update_Proj
    Admin --> UC_Delete_Proj
    Admin --> UC_Add_Member
```
* **Deskripsi**: Pengunjung umum dapat memantau produk/sistem yang dihasilkan laboratorium. Admin mengelola data project dan memasukkan asisten/dosen ke dalam tim pengembang project tersebut (*Manage Members*).

---

### 2.8. Fitur Kelola Galeri & Repositori
Menyimpan dokumentasi foto kegiatan laboratorium serta file repositori publik lainnya.

```mermaid
graph TD
    Public((👤 Mahasiswa / Umum))
    Admin((👑 Admin))

    subgraph "Manajemen Galeri & Repositori"
        UC_View_Galeri([Melihat Foto Galeri])
        UC_View_Repo([Melihat Repositori Publik])
        UC_Create_Galeri([Menambah Foto Galeri Baru])
        UC_Update_Galeri([Mengubah Foto Galeri])
        UC_Delete_Galeri([Menghapus Foto Galeri])
    end

    Public --> UC_View_Galeri
    Public --> UC_View_Repo

    Admin --> UC_Create_Galeri
    Admin --> UC_Update_Galeri
    Admin --> UC_Delete_Galeri
```
* **Deskripsi**: Publik dapat melihat dokumentasi visual berupa foto kegiatan lab dan file pendukung repositori. Admin mengunggah gambar dokumentasi baru dan menghapus galeri yang sudah usang.

---

### 2.9. Fitur Kelola Rekrutmen Asisten
Fitur pengumuman penerimaan calon asisten baru di laboratorium.

```mermaid
graph TD
    Public((👤 Mahasiswa / Umum))
    Admin((👑 Admin))

    subgraph "Manajemen Rekrutmen Asisten"
        UC_View_Recruit([Melihat Informasi Lowongan])
        UC_Create_Recruit([Membuat Lowongan Baru])
        UC_Update_Recruit([Mengubah Lowongan])
        UC_Delete_Recruit([Menghapus Lowongan])
    end

    Public --> UC_View_Recruit

    Admin --> UC_Create_Recruit
    Admin --> UC_Update_Recruit
    Admin --> UC_Delete_Recruit
```
* **Deskripsi**: Mahasiswa dapat memantau pembukaan rekrutmen asisten beserta kualifikasi dan syarat pendaftaran. Admin membuat, memperbarui status (aktif/tutup), dan mengedit persyaratan rekrutmen.

---

### 2.10. Fitur Kelola Sertifikat & Klaim Sertifikat
Sistem untuk menerbitkan sertifikat apresiasi bagi asisten. Sertifikat digenerate secara otomatis menjadi gambar/PDF dengan menempelkan nama asisten, NRP, dan tanda tangan digital Kepala Lab & Ketua Prodi secara dinamis.

```mermaid
graph TD
    Asisten((👤 Asisten))
    Admin((👑 Admin))

    subgraph "Manajemen & Klaim Sertifikat"
        UC_Claim_Cert([Akses Halaman Klaim])
        UC_Gen_Cert([Generate & Download Sertifikat])
        UC_Prev_Cert_Aslab([Pratinjau Sertifikat Asisten])
        
        UC_M_Config([Mengatur Template Sertifikat])
        UC_Del_Config([Menghapus Konfigurasi])
        UC_Upd_Status([Mengubah Status Tugas Asisten])
        UC_Prev_Template([Pratinjau Template Admin])
    end

    Asisten --> UC_Claim_Cert
    Asisten --> UC_Gen_Cert
    Asisten --> UC_Prev_Cert_Aslab
    
    UC_Gen_Cert -.->|depends on| UC_Upd_Status
    UC_Prev_Cert_Aslab -.->|depends on| UC_Upd_Status

    Admin --> UC_M_Config
    Admin --> UC_Del_Config
    Admin --> UC_Upd_Status
    Admin --> UC_Prev_Template
```
* **Deskripsi**: Asisten dapat mengklaim sertifikat. Namun, proses klaim dan pengunduhan hanya dapat dilakukan jika Admin telah menandai status tugas asisten tersebut sebagai **"selesai"**. Admin juga berhak menyetel gambar background template sertifikat, mengunggah tanda tangan (TTD) Kepala Lab/Ketua Prodi, serta melakukan pratinjau template konfigurasi.

---

### 2.11. Fitur Kelola Berita & Events
Modul publikasi artikel berita internal, pengumuman, serta agenda event/kegiatan mendatang.

```mermaid
graph TD
    Public((👤 Mahasiswa / Umum))
    Admin((👑 Admin))

    subgraph "Manajemen Berita & Kegiatan (Events)"
        UC_View_Berita([Melihat Berita & Pengumuman])
        UC_View_Event([Melihat Detail Event])
        UC_Create_Berita([Menambah Berita Baru])
        UC_Update_Berita([Mengubah Berita])
        UC_Delete_Berita([Menghapus Berita])
        UC_Create_Event([Menambah Event Baru])
        UC_Update_Event([Mengubah Event])
        UC_Delete_Event([Menghapus Event])
    end

    Public --> UC_View_Berita
    Public --> UC_View_Event

    Admin --> UC_Create_Berita
    Admin --> UC_Update_Berita
    Admin --> UC_Delete_Berita
    Admin --> UC_Create_Event
    Admin --> UC_Update_Event
    Admin --> UC_Delete_Event
```
* **Deskripsi**: Publik dapat membaca berita dan event terbaru laboratorium. Admin mengelola seluruh konten berita dan event (seperti Seminar, Workshop, dll.).

---

### 2.12. Fitur Kelola Visi Misi & Ruangan
Pengelolaan konten statis visi-misi laboratorium serta inventaris ruangan praktikum yang tersedia.

```mermaid
graph TD
    Public((👤 Mahasiswa / Umum))
    Admin((👑 Admin))

    subgraph "Manajemen Visi Misi & Ruangan"
        UC_View_Visi([Melihat Visi Misi & Struktur])
        UC_View_Ruang([Melihat Info Ruangan])
        UC_Update_Visi([Mengubah Visi Misi])
        UC_Create_Ruang([Menambah Ruang Baru])
        UC_Update_Ruang([Mengubah Detail Ruang])
        UC_Delete_Ruang([Menghapus Ruang])
    end

    Public --> UC_View_Visi
    Public --> UC_View_Ruang

    Admin --> UC_Update_Visi
    Admin --> UC_Create_Ruang
    Admin --> UC_Update_Ruang
    Admin --> UC_Delete_Ruang
```
* **Deskripsi**: Pengguna dapat melihat sejarah, visi misi, struktur organisasi lab, serta kapasitas ruangan praktikum. Admin dapat memperbarui informasi teks visi misi dan mengelola data fisik ruangan laboratorium.

---

### 2.13. Fitur Kelola Hak Akses / RBAC (Role-Based Access Control)
Fitur inti keamanan sistem untuk membatasi fungsionalitas menu bagi peran Asisten dan Dosen secara dinamis.

```mermaid
graph TD
    Admin((👑 Super Admin))

    subgraph "Sistem RBAC (Role-Based Access Control)"
        UC_View_RBAC([Melihat Tabel Hak Akses])
        UC_Update_RBAC([Memperbarui Hak Akses Role])
        UC_Check_Perm([Validasi Akses Middleware/Filter])
    end

    Admin --> UC_View_RBAC
    Admin --> UC_Update_RBAC
    UC_Check_Perm -.->|include| UC_View_RBAC
```
* **Deskripsi**: Super Admin memiliki otorisasi penuh untuk menyalakan/mematikan menu administratif tertentu bagi asisten maupun dosen (misalnya memberi asisten izin untuk mengelola modul praktikum). Sistem secara otomatis memvalidasi hak akses ini di tingkat server menggunakan filter/middleware sebelum memproses request pengguna.

---

> **Tips untuk Penulisan Laporan Kerja Praktek**:
> Anda bisa menyalin kode Mermaid di atas langsung ke aplikasi Markdown editor Anda. Untuk memasukkan diagram ini ke aplikasi pengolah kata seperti Microsoft Word, disarankan untuk merender diagram ini terlebih dahulu di markdown editor (atau [Mermaid Live Editor](https://mermaid.live/)), lalu mengekspornya ke format gambar PNG atau SVG dengan resolusi tinggi.
