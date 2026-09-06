<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =========================================================================
// 🌐 PUBLIC ROUTES (With Page Caching)
// =========================================================================
$routes->get('/', 'Home::index', ['filter' => 'pagecache']);
$routes->get('/berita', 'BeritaController::index', ['filter' => 'pagecache']);
$routes->get('/asisten', 'UserController::index', ['filter' => 'pagecache']);
$routes->get('/jadwal', 'JadwalController::index');
$routes->get('/jadwal-praktikum', 'JadwalController::praktikum');
$routes->get('/penelitian-proyek', 'ProyekRisetController::index');
$routes->get('/publikasi-ilmiah', 'PublikasiController::index');
$routes->get('/galeri', 'GaleriUmumController::index');
$routes->get('/repositori', 'Home::repositori');
$routes->get('/rekrutmen', 'RekrutController::index');
$routes->get('/modul_praktikum', 'ModulPraktikumController::index');
$routes->get('/organisasi', 'OrganizationController::index', ['filter' => 'pagecache']);

// Modul Praktikum - File Handling
$routes->get('modul-praktikum/preview/(:any)', 'ModulPraktikumController::preview/$1');
$routes->get('modul-praktikum/download/(:any)', 'ModulPraktikumController::download/$1');

// Detail & Profil
$routes->get('/topic_detail/(:segment)', 'TopicController::detail/$1');
$routes->get('/penelitian/(:segment)', 'TopicController::detail/$1');
$routes->get('/asisten/(:num)', 'UserController::profil/$1');

// Contact Form
$routes->get('contact', 'ContactController::index');
$routes->post('contact/send', 'ContactController::send');

// Project Lab Public
$routes->get('/project-lab', 'ProjectLabController::index');
$routes->get('/project-lab/(:num)', 'ProjectLabController::detail/$1');


// =========================================================================
// 🔑 AUTHENTICATION & API ROUTES
// =========================================================================
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    $routes->get('asisten-jadwal', 'AdminApi::getAsistenJadwal');
    $routes->post('asisten-jadwal/sync', 'AdminApi::syncAsistenJadwal');
    $routes->post('asisten-jadwal/create', 'AdminApi::createAsistenJadwal');
    $routes->post('asisten-jadwal/update/(:num)', 'AdminApi::updateAsistenJadwal/$1');
    $routes->post('asisten-jadwal/delete/(:num)', 'AdminApi::deleteAsistenJadwal/$1');
});

$routes->group('api/auth', ['namespace' => 'App\Controllers\Api'], function($routes) {
    $routes->post('login', 'Auth::login', ['filter' => 'throttle']);
    $routes->get('profile', 'Auth::profile');
});

// UI Login & Profile
$routes->get('login', 'AuthUi::login');
$routes->get('profile', 'AuthUi::profile', ['filter' => 'auth']);
$routes->post('profile/update', 'AuthUi::updateProfile', ['filter' => 'auth']);
//di nonaktifkan jika untuk pengujian automation xss testting 
$routes->post('logout', 'AuthUi::logout');


// =========================================================================
// 🛡️ ADMIN CORE & RBAC
// =========================================================================
$routes->group('', ['filter' => ['admin', 'session_security']], function($routes) {
    // Hak Akses RBAC (Only Admin)
    $routes->get('/hak_akses_admin', 'HakAksesController::index');
    $routes->post('/hak_akses_admin/update', 'HakAksesController::update');
});

// =========================================================================
// 🛡️ DYNAMIC RBAC ROUTES (Protected by PermissionFilter)
// =========================================================================

// --- KELOLA USERS ---
$routes->group('', ['filter' => ['session_security', 'permission:asisten_admin']], function($routes) {
    $routes->get('/asisten_admin', 'Admin\Users::asistenAdmin');
    $routes->post('/admin/users/create', 'Admin\Users::create');
    $routes->post('/admin/users/update/(:num)', 'Admin\Users::update/$1');
    $routes->post('/admin/users/delete/(:num)', 'Admin\Users::delete/$1');
});

// --- KELOLA GALERI ---
$routes->group('', ['filter' => ['session_security', 'permission:galeri_admin']], function($routes) {
    $routes->get('/galeri_admin', 'GaleriUmumController::admin');
    $routes->post('galeri_admin/store', 'GaleriUmumController::create');
    $routes->post('galeri_admin/update/(:num)', 'GaleriUmumController::update/$1');
    $routes->post('galeri_admin/delete/(:num)', 'GaleriUmumController::delete/$1');
});

// --- KELOLA REPOSITORI --- (Assuming general admin view)
$routes->get('/repositori_admin','Home::repositori_admin', ['filter' => ['session_security', 'admin']]);

// --- KELOLA REKRUTMEN ---
$routes->group('', ['filter' => ['session_security', 'permission:rekrutmen_admin']], function($routes) {
    $routes->get('/rekrutmen_admin','RekrutController::admin');
    $routes->get('rekrutmen/create', 'RekrutController::create');     
    $routes->post('rekrutmen/store', 'RekrutController::store');      
    $routes->get('rekrutmen/edit/(:num)', 'RekrutController::edit/$1');   
    $routes->post('rekrutmen/update/(:num)', 'RekrutController::update/$1'); 
    $routes->post('rekrutmen/delete/(:num)', 'RekrutController::delete/$1'); 
});

// --- KELOLA BERITA ---
$routes->group('', ['filter' => ['session_security', 'permission:berita_admin']], function($routes) {
    $routes->get('/berita_admin','BeritaController::admin');
    $routes->post('berita/store', 'BeritaController::store');   
    $routes->post('berita/update/(:num)', 'BeritaController::update/$1'); 
    $routes->post('berita/delete/(:num)', 'BeritaController::delete/$1'); 
});

// --- KELOLA EVENTS ---
$routes->group('', ['filter' => ['session_security', 'permission:events_admin']], function($routes) {
    $routes->get('/events_admin','EventsController::admin');
    $routes->get('/events', 'EventsController::index');      
    $routes->post('/events/store', 'EventsController::store');   
    $routes->post('/events/update/(:num)', 'EventsController::update/$1'); 
    $routes->post('/events/delete/(:num)', 'EventsController::delete/$1');  
});

// --- KELOLA MODUL PRAKTIKUM ---
$routes->group('', ['filter' => ['session_security', 'permission:modul_praktikum_admin']], function($routes) {
    $routes->get('/modul_praktikum_admin','ModulPraktikumController::admin');
    $routes->post('modul-praktikum/create', 'ModulPraktikumController::create');
    $routes->post('modul-praktikum/update/(:num)', 'ModulPraktikumController::update/$1');
    $routes->post('modul-praktikum/delete/(:num)', 'ModulPraktikumController::delete/$1');
});

// --- KELOLA VISI MISI ---
$routes->group('', ['filter' => ['session_security', 'permission:visi_misi_admin']], function($routes) {
    $routes->get('visi-misi_admin','VisiMisiController::index');
    $routes->post('visi-misi_admin/store', 'VisiMisiController::create');   
    $routes->post('visi-misi_admin/update/(:num)', 'VisiMisiController::update/$1'); 
    $routes->post('visi-misi_admin/delete/(:num)', 'VisiMisiController::delete/$1'); 
});

// --- KELOLA PERIODE ASISTEN ---
$routes->group('', ['filter' => ['session_security', 'permission:periode_admin']], function($routes) {
    $routes->get('/periode_admin', 'PeriodeController::index');
    $routes->post('/periode_admin/store', 'PeriodeController::store');
    $routes->post('/periode_admin/update/(:num)', 'PeriodeController::update/$1');
    $routes->post('/periode_admin/delete/(:num)', 'PeriodeController::delete/$1');
});

// --- KELOLA RUANGAN ---
$routes->group('', ['filter' => ['session_security', 'permission:ruangan_admin']], function($routes) {
    $routes->get('/ruangan_admin', 'Admin\Ruangan::index');
    $routes->post('/admin/ruangan/create', 'Admin\Ruangan::create');
    $routes->post('/admin/ruangan/update/(:num)', 'Admin\Ruangan::update/$1');
    $routes->post('/admin/ruangan/delete/(:num)', 'Admin\Ruangan::delete/$1');
});

// --- KELOLA SERTIFIKAT ---
$routes->group('', ['filter' => ['session_security', 'permission:sertifikat_admin']], function($routes) {
    $routes->get('/sertifikat_admin', 'SertifikatController::admin');
    $routes->group('sertifikat', function ($routes) {
        $routes->get('preview', 'SertifikatController::preview');
        $routes->get('raw-template', 'SertifikatController::rawTemplate');
        $routes->get('raw-ttd/(:segment)', 'SertifikatController::rawTtd/$1');
        $routes->post('save-layout', 'SertifikatController::saveLayout');
        $routes->post('config', 'SertifikatController::updateConfig');
        $routes->post('delete-config', 'SertifikatController::deleteConfig');
        $routes->put('status/(:num)', 'SertifikatController::updateStatusTugas/$1');
        $routes->post('status/(:num)', 'SertifikatController::updateStatusTugas/$1');
    });
});

// --- JADWAL SAYA (Asisten specific usually, but dynamic) ---
$routes->group('', ['filter' => ['session_security', 'permission:jadwal_saya']], function($routes) {
    $routes->get('/jadwal_saya', 'JadwalController::asistenJadwalSaya');
});

// --- KLAIM SERTIFIKAT ---
$routes->group('', ['filter' => ['session_security', 'permission:sertifikat_klaim']], function($routes) {
    $routes->get('/sertifikat', 'SertifikatController::index');
    $routes->get('sertifikat/generate', 'SertifikatController::generate');
    $routes->get('sertifikat/preview-asisten', 'SertifikatController::previewAsisten');
});

// --- KELOLA JADWAL ---
$routes->group('', ['filter' => ['session_security', 'permission:jadwal_admin']], function($routes) {
    $routes->get('/jadwal_admin', 'JadwalController::admin');
    $routes->post('/jadwal/store', 'JadwalController::store');
    $routes->post('/jadwal/update/(:num)', 'JadwalController::update/$1');
    $routes->post('/jadwal/sync_asisten', 'JadwalController::syncAsisten');
    $routes->post('/jadwal/delete/(:num)', 'JadwalController::delete/$1');
});

// --- KELOLA PENELITIAN PROYEK ---
$routes->group('', ['filter' => ['session_security', 'permission:penelitian_proyek_admin']], function($routes) {
    $routes->get('/penelitian-proyek_admin', 'ProyekRisetController::getDataAdmin');
    $routes->post('proyek-riset/store', 'ProyekRisetController::create');
    $routes->post('proyek-riset/update/(:num)', 'ProyekRisetController::update/$1');
    $routes->post('proyek-riset/delete/(:num)', 'ProyekRisetController::delete/$1');
    $routes->get('proyek-riset/(:num)', 'ProyekRisetController::edit/$1');
});

// --- KELOLA PUBLIKASI ILMIAH ---
$routes->group('', ['filter' => ['session_security', 'permission:publikasi_ilmiah_admin']], function($routes) {
    $routes->get('/publikasi-ilmiah_admin', 'PublikasiController::getDataAdmin');
    $routes->post('publikasi-ilmiah/store', 'PublikasiController::store');   
    $routes->post('publikasi-ilmiah/update/(:num)', 'PublikasiController::update/$1'); 
    $routes->post('publikasi-ilmiah/delete/(:num)', 'PublikasiController::delete/$1'); 
});

// --- KELOLA PROJECT LAB ---
$routes->group('', ['filter' => ['session_security', 'permission:project_lab_admin']], function($routes) {
    $routes->get('/project-lab_admin', 'ProjectLabController::getDataAdmin');
    $routes->post('/project-lab/create', 'ProjectLabController::create');
    $routes->post('/project-lab/update/(:num)', 'ProjectLabController::update/$1');
    $routes->post('/project-lab/delete/(:num)', 'ProjectLabController::delete/$1');
    $routes->post('/project-lab/member/add', 'ProjectLabController::addMember');
});