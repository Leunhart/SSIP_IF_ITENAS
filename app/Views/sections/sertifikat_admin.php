<section id="sertifikat-admin-section" class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- KOTAK PRATINJAU (Muncul otomatis jika konfigurasi gambar sudah ada) -->
                <?php if(!empty($config['template_gambar'])): ?>
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-success"><i class="fas fa-eye me-2"></i> Pratinjau Sertifikat (Contoh)</h5>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-primary px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#visualEditorModal">
                                <i class="fas fa-palette me-1"></i> Atur Tata Letak Visual
                            </button>
                            <span class="badge bg-secondary">Live Render</span>
                        </div>
                    </div>
                    <div class="card-body p-4 text-center bg-light">
                        <!-- Memanggil fungsi preview dari Controller -->
                        <img src="<?= site_url('sertifikat/preview') ?>?t=<?= time() ?>" class="img-fluid border shadow-sm rounded" alt="Preview Sertifikat" style="max-height: 500px; object-fit: contain;">
                        <p class="mt-3 text-muted small mb-0"><i class="fas fa-info-circle me-1"></i> Ini adalah simulasi tata letak dengan data dummy "NAMA ASISTEN CONTOH" dan "152022032".</p>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
                        <h4 class="mb-0 text-primary"><i class="fas fa-cog me-2"></i> Konfigurasi Data Sertifikat</h4>
                        <p class="text-muted small mb-0 mt-1">Atur templat gambar polos, tanda tangan, dan teks yang akan dicetak secara dinamis ke dalam sertifikat asisten.</p>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Notifikasi -->
                        <?php if (session()->getFlashdata('success')) : ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')) : ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Form Konfigurasi -->
                        <form action="<?= site_url('sertifikat/config') ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            
                            <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2">Pengaturan Teks Utama</h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">Judul Sertifikat</label>
                                    <input type="text" name="judul" class="form-control" value="<?= esc($config['judul'] ?? 'SERTIFIKAT APRESIASI') ?>" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Deskripsi Dedikasi</label>
                                <textarea name="deskripsi_template" class="form-control" rows="3" required placeholder="Contoh: Telah Berdedikasi sebagai Asisten Praktikum Pemrograman IOT selama periode Oktober 2025 - Januari 2026"><?= esc($config['deskripsi_template'] ?? '') ?></textarea>
                            </div>

                            <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2">Pengaturan Pejabat Penandatangan</h6>
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Nama Kepala Laboratorium</label>
                                    <input type="text" name="nama_kepala_lab" class="form-control" value="<?= esc($config['nama_kepala_lab'] ?? 'Galih Ashari R., S.Si., MT.') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Nama Ketua Prodi</label>
                                    <input type="text" name="nama_ketua_prodi" class="form-control" value="<?= esc($config['nama_ketua_prodi'] ?? 'Dr. sc. Lisa Kristiana, ST., MT.') ?>" required>
                                </div>
                            </div>

                            <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2">Aset Gambar <span class="text-muted fw-normal fs-6">(Abaikan jika tidak ingin mengubah)</span></h6>
                            
                            <div class="mb-3 bg-light p-3 rounded border">
                                <label class="form-label fw-semibold">Template Background (Gambar Polos Lanskap)</label>
                                <input type="file" name="template_gambar" class="form-control" accept="image/jpeg, image/png">
                                <?php if(!empty($config['template_gambar'])): ?>
                                    <div class="mt-2 text-success small"><i class="fas fa-check-circle"></i> Template background saat ini sudah terpasang.</div>
                                <?php else: ?>
                                    <div class="mt-2 text-danger small"><i class="fas fa-times-circle"></i> Belum ada template terpasang!</div>
                                <?php endif; ?>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="bg-light p-3 rounded border h-100">
                                        <label class="form-label fw-semibold">Tanda Tangan Kepala Lab (PNG Transparan)</label>
                                        <input type="file" name="ttd_kepala_lab" class="form-control" accept="image/png">
                                        <?php if(!empty($config['ttd_kepala_lab'])): ?>
                                            <div class="mt-2 text-success small"><i class="fas fa-check-circle"></i> TTD Kepala Lab terpasang.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="bg-light p-3 rounded border h-100">
                                        <label class="form-label fw-semibold">Tanda Tangan Ketua Prodi (PNG Transparan)</label>
                                        <input type="file" name="ttd_ketua_prodi" class="form-control" accept="image/png">
                                        <?php if(!empty($config['ttd_ketua_prodi'])): ?>
                                            <div class="mt-2 text-success small"><i class="fas fa-check-circle"></i> TTD Ketua Prodi terpasang.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 bg-light p-3 rounded border">
                                <label class="form-label fw-semibold">Logo Tambahan / Instansi (PNG Transparan / JPG)</label>
                                <input type="file" name="logo_tambahan" class="form-control" accept="image/png, image/jpeg">
                                <?php if(!empty($config['logo_tambahan'])): ?>
                                    <div class="mt-2 text-success small"><i class="fas fa-check-circle"></i> Logo tambahan terpasang.</div>
                                <?php else: ?>
                                    <div class="mt-2 text-muted small"><i class="fas fa-info-circle"></i> Opsional: Dapat diatur posisi dan ukurannya di Visual Editor.</div>
                                <?php endif; ?>
                            </div>

                            <!-- Area Tombol Aksi -->
                            <div class="mt-4 d-flex justify-content-end gap-2">
                                <?php if(!empty($config)): ?>
                                    <button type="button" class="btn btn-danger px-4" data-bs-toggle="modal" data-bs-target="#deleteConfigModal">
                                        <i class="fas fa-trash me-1"></i> Hapus Konfigurasi
                                    </button>
                                <?php endif; ?>
                                
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-1"></i> Simpan Konfigurasi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Hapus Konfigurasi -->
<?php if(!empty($config)): ?>
<div class="modal fade" id="deleteConfigModal" tabindex="-1" aria-labelledby="deleteConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= site_url('sertifikat/delete-config') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteConfigModalLabel"><i class="fas fa-exclamation-triangle me-2"></i> Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-0">Apakah Anda yakin ingin menghapus seluruh konfigurasi sertifikat ini? <strong>Teks dan semua aset gambar (Template & Tanda Tangan) akan dihapus secara permanen dari server.</strong></p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Google Fonts untuk Pratinjau Tipografi Sertifikat yang Presisi -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;800&family=Great+Vibes&family=Montserrat:wght@400;600;700;800&family=Open+Sans:ital,wght@0,400;0,600;0,700;0,800;1,400&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,400&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">

<!-- =========================================================================
     MODAL & SCRIPT VISUAL LAYOUT EDITOR (DRAG & DROP)
     ========================================================================= -->
<?php if(!empty($config['template_gambar'])): ?>
<div class="modal fade" id="visualEditorModal" tabindex="-1" aria-labelledby="visualEditorModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white px-4 py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-magic me-1"></i> Editor Presisi</span>
                    <h5 class="modal-title mb-0" id="visualEditorModalLabel">
                        Visual Layout Editor Sertifikat
                    </h5>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-light btn-sm" onclick="resetToDefault()">
                        <i class="fas fa-undo me-1"></i> Reset ke Bawaan
                    </button>
                    <button type="button" class="btn btn-warning btn-sm text-dark px-3 fw-semibold shadow-sm" onclick="saveLayoutCoords()">
                        <i class="fas fa-save me-1"></i> Simpan Tata Letak
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 d-flex flex-column flex-lg-row bg-secondary bg-opacity-10" style="height: calc(100vh - 56px); overflow: hidden;">
                <!-- Main Workspace (Editor Canvas) -->
                <div class="flex-grow-1 p-4 d-flex align-items-center justify-content-center" style="overflow: auto; min-height: 0;">
                    <div id="editor-workspace" class="position-relative shadow-lg border rounded-3 bg-white" style="user-select: none;">
                        <!-- Background Template Image -->
                        <img id="editor-bg-image" src="<?= site_url('sertifikat/raw-template') ?>" class="w-100 h-100 rounded-3 d-block" alt="Template" style="pointer-events: none; object-fit: contain;">
                        
                        <!-- Draggable Elements -->
                        <div id="drag-judul" class="draggable-element" data-element="judul">
                            <div class="element-label">Judul</div>
                            <div class="element-content font-judul text-uppercase text-center"><?= esc($config['judul'] ?? 'SERTIFIKAT APRESIASI') ?></div>
                        </div>

                        <div id="drag-preamble" class="draggable-element" data-element="preamble">
                            <div class="element-label">Preamble</div>
                            <div class="element-content font-preamble text-center">Dengan bangga dipersembahkan kepada:</div>
                        </div>

                        <div id="drag-nama" class="draggable-element" data-element="nama">
                            <div class="element-label">Nama Asisten</div>
                            <div class="element-content font-nama text-center">NAMA ASISTEN CONTOH</div>
                        </div>

                        <div id="drag-garis" class="draggable-element draggable-line-box" data-element="garis">
                            <div class="element-label">Garis</div>
                            <div class="element-content editor-line"></div>
                        </div>

                        <div id="drag-nrp" class="draggable-element" data-element="nrp">
                            <div class="element-label">NRP Asisten</div>
                            <div class="element-content font-nrp text-center">152022032</div>
                        </div>

                        <div id="drag-deskripsi" class="draggable-element draggable-desc-box" data-element="deskripsi">
                            <div class="element-label">Deskripsi</div>
                            <div class="element-content font-desc text-center"><?= esc($config['deskripsi_template'] ?? 'Telah berdedikasi sebagai asisten laboratorium...') ?></div>
                        </div>

                        <div id="drag-ttd_kiri" class="draggable-element draggable-image" data-element="ttd_kiri">
                            <div class="element-label">TTD Kiri</div>
                            <div class="element-content text-center">
                                <?php if(!empty($config['ttd_kepala_lab'])): ?>
                                    <img src="<?= site_url('sertifikat/raw-ttd/kepala') ?>" style="height: 60px; pointer-events: none;" alt="TTD Kiri">
                                <?php else: ?>
                                    <div class="ttd-placeholder">Belum Ada TTD</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div id="drag-nama_kiri" class="draggable-element" data-element="nama_kiri">
                            <div class="element-label">Nama Kiri</div>
                            <div class="element-content font-sig-name text-center"><?= esc($config['nama_kepala_lab'] ?? 'Galih Ashari R., S.Si., MT.') ?></div>
                        </div>

                        <div id="drag-role_kiri" class="draggable-element" data-element="role_kiri">
                            <div class="element-label">Jabatan Kiri</div>
                            <div class="element-content font-sig-role text-center">Kepala Laboratorium</div>
                        </div>

                        <div id="drag-ttd_kanan" class="draggable-element draggable-image" data-element="ttd_kanan">
                            <div class="element-label">TTD Kanan</div>
                            <div class="element-content text-center">
                                <?php if(!empty($config['ttd_ketua_prodi'])): ?>
                                    <img src="<?= site_url('sertifikat/raw-ttd/prodi') ?>" style="height: 60px; pointer-events: none;" alt="TTD Kanan">
                                <?php else: ?>
                                    <div class="ttd-placeholder">Belum Ada TTD</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div id="drag-nama_kanan" class="draggable-element" data-element="nama_kanan">
                            <div class="element-label">Nama Kanan</div>
                            <div class="element-content font-sig-name text-center"><?= esc($config['nama_ketua_prodi'] ?? 'Dr. sc. Lisa Kristiana, ST., MT.') ?></div>
                        </div>

                        <div id="drag-role_kanan" class="draggable-element" data-element="role_kanan">
                            <div class="element-label">Jabatan Kanan</div>
                            <div class="element-content font-sig-role text-center">Ketua Prodi</div>
                        </div>

                        <div id="drag-logo" class="draggable-element draggable-image" data-element="logo">
                            <div class="element-label">Logo Tambahan</div>
                            <div class="element-content text-center">
                                <?php if(!empty($config['logo_tambahan'])): ?>
                                    <img src="<?= site_url('sertifikat/raw-logo') ?>" style="height: 60px; pointer-events: none;" alt="Logo Tambahan">
                                <?php else: ?>
                                    <div class="ttd-placeholder">Logo Tambahan</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Inspector -->
                <div class="bg-white border-start p-4 d-flex flex-column shadow-sm" style="width: 340px; overflow-y: auto;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="fas fa-sliders-h me-2 text-primary"></i> Inspector</h5>
                        <span class="badge bg-light text-muted border small">Tersinkron 1:1</span>
                    </div>
                    <p class="text-muted small mb-3">Klik atau geser elemen di kanvas untuk mengedit posisi, font, dan ukurannya.</p>
                    
                    <div id="inspector-selected" class="d-none mb-4">
                        <div class="card border border-primary bg-primary bg-opacity-10 rounded-3 mb-3">
                            <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold text-primary mb-1" id="inspector-element-title">Nama Elemen</h6>
                                    <span class="badge bg-primary text-white small" id="inspector-element-type">Tipe: Teks</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" title="Ratakan Tengah Horizontal (X: 50%)" onclick="centerSelectedHorizontal()">
                                    <i class="fas fa-align-center"></i> 50%
                                </button>
                            </div>
                        </div>
                        
                        <!-- Pilihan Font (Hanya untuk Teks) -->
                        <div class="mb-3 d-none" id="inspector-fontfamily-container">
                            <label class="form-label fw-semibold text-secondary small mb-1"><i class="fas fa-font me-1"></i> Jenis Font (Typography)</label>
                            <select id="inspector-fontfamily" class="form-select form-select-sm" onchange="updateSelectedFontFamily()">
                                <option value="PlayfairDisplay-Bold">Playfair Display (Luxury Serif - Bold)</option>
                                <option value="Cinzel-Bold">Cinzel (Classic Formal Serif - Bold)</option>
                                <option value="GreatVibes-Regular">Great Vibes (Calligraphy Script)</option>
                                <option value="Montserrat-Bold">Montserrat (Modern Clean - Bold)</option>
                                <option value="OpenSans-Bold">Open Sans (Sans-Serif - Bold)</option>
                                <option value="OpenSans-Regular">Open Sans (Sans-Serif - Regular)</option>
                                <option value="Poppins-Bold">Poppins (Geometric Modern - Bold)</option>
                                <option value="Poppins-Regular">Poppins (Geometric Modern - Regular)</option>
                            </select>
                            <div class="form-text small text-muted">Font yang dipilih akan dirender persis pada file sertifikat.</div>
                        </div>

                        <!-- Ukuran Font Control (hanya untuk teks) -->
                        <div class="mb-3 d-none" id="inspector-fontsize-container">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold text-secondary small mb-0"><i class="fas fa-text-height me-1"></i> Ukuran Font</label>
                                <span class="badge bg-light text-dark border px-2 py-1" id="inspector-fontsize-badge">24pt</span>
                            </div>
                            <input type="range" id="inspector-fontsize" class="form-range" min="10" max="140" step="1" oninput="updateSelectedSize()">
                        </div>

                        <!-- Lebar Area Deskripsi (hanya untuk Deskripsi) -->
                        <div class="mb-3 d-none" id="inspector-descwidth-container">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold text-secondary small mb-0"><i class="fas fa-arrows-alt-h me-1"></i> Lebar Area Teks</label>
                                <span class="badge bg-light text-dark border px-2 py-1" id="inspector-descwidth-badge">65%</span>
                            </div>
                            <input type="range" id="inspector-descwidth" class="form-range" min="30" max="90" step="1" oninput="updateSelectedSize()">
                            <div class="form-text small text-muted">Batas lebar pembungkus baris teks deskripsi.</div>
                        </div>

                        <!-- Ukuran Gambar Control (hanya untuk TTD) -->
                        <div class="mb-3 d-none" id="inspector-imgheight-container">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold text-secondary small mb-0"><i class="fas fa-image me-1"></i> Tinggi Tanda Tangan</label>
                                <span class="badge bg-light text-dark border px-2 py-1" id="inspector-imgheight-badge">130px</span>
                            </div>
                            <input type="range" id="inspector-imgheight" class="form-range" min="30" max="300" step="5" oninput="updateSelectedSize()">
                        </div>

                        <!-- Lebar Garis Control (hanya untuk Garis) -->
                        <div class="mb-3 d-none" id="inspector-linewidth-container">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold text-secondary small mb-0"><i class="fas fa-ruler-horizontal me-1"></i> Lebar Garis</label>
                                <span class="badge bg-light text-dark border px-2 py-1" id="inspector-linewidth-badge">45%</span>
                            </div>
                            <input type="range" id="inspector-linewidth" class="form-range" min="10" max="90" step="1" oninput="updateSelectedSize()">
                        </div>

                        <hr class="my-3 text-muted opacity-25">

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small mb-1">Posisi Horizontal (X %)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" id="inspector-x" class="form-control" min="0" max="100" step="0.1" oninput="updateSelectedPosition()">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text small text-muted">Pusat horizontal elemen terhadap kanvas.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small mb-1">Posisi Vertikal (Y %)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" id="inspector-y" class="form-control" min="0" max="100" step="0.1" oninput="updateSelectedPosition()">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text small text-muted">Sisi atas elemen terhadap kanvas.</div>
                        </div>
                    </div>

                    <div id="inspector-empty" class="text-center text-muted my-auto py-5">
                        <i class="fas fa-mouse-pointer fa-2x mb-3 text-secondary bg-light p-3 rounded-circle shadow-sm"></i>
                        <p class="mb-0 small">Belum ada elemen yang dipilih.<br>Klik atau geser salah satu elemen di kanvas untuk mulai mengedit.</p>
                    </div>

                    <div class="mt-auto border-top pt-3">
                        <button type="button" class="btn btn-warning w-100 fw-bold text-dark mb-2 shadow-sm" onclick="saveLayoutCoords()">
                            <i class="fas fa-save me-1"></i> Simpan Tata Letak
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-100 btn-sm" data-bs-dismiss="modal">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS khusus untuk visual layout editor presisi */
.draggable-element {
    position: absolute;
    transform: translateX(-50%);
    cursor: move;
    border: 1px dashed transparent;
    padding: 0;
    margin: 0;
    line-height: 1;
    border-radius: 4px;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    user-select: none;
    z-index: 10;
    box-sizing: border-box;
    white-space: nowrap;
}
.draggable-element:hover {
    border-color: rgba(13, 110, 253, 0.6);
    background-color: rgba(13, 110, 253, 0.05);
}
.draggable-element.active {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.1);
    box-shadow: 0 0 10px rgba(13, 110, 253, 0.35);
    z-index: 20;
}
.draggable-element .element-label {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background-color: #0d6efd;
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 3px;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.15s ease;
    pointer-events: none;
    font-weight: 600;
    line-height: 1.2;
    margin-bottom: 2px;
}
.draggable-element:hover .element-label,
.draggable-element.active .element-label {
    opacity: 1;
}

.draggable-element.draggable-line-box {
    transform: translate(-50%, -50%);
    white-space: normal;
}
.draggable-element.draggable-desc-box {
    white-space: normal;
}

/* Tipografi dasar kanvas editor */
#editor-workspace .font-judul {
    color: #1e1e1e;
    letter-spacing: 1px;
}
#editor-workspace .font-preamble {
    color: #505050;
}
#editor-workspace .font-nama {
    color: #1e1e1e;
}
#editor-workspace .font-nrp {
    color: #1e1e1e;
}
#editor-workspace .font-desc {
    color: #1e1e1e;
    line-height: 1.45;
    word-break: normal;
    overflow-wrap: break-word;
}
#editor-workspace .editor-line {
    height: 3px;
    background-color: #B49650;
    margin: 0 auto;
}
#editor-workspace .font-sig-name {
    color: #1e1e1e;
}
#editor-workspace .font-sig-role {
    color: #505050;
}
#editor-workspace .ttd-placeholder {
    width: clamp(60px, 8cqw, 140px);
    height: clamp(30px, 4cqw, 70px);
    border: 1px dashed #999;
    background-color: #f8f9fa;
    font-size: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    border-radius: 4px;
}
#editor-workspace img {
    display: block;
}
#editor-workspace {
    max-width: 100%;
    position: relative;
    box-sizing: border-box;
    container-type: inline-size;
}
</style>

<script>
let currentLayout = {};
let selectedElementId = null;

// Font CSS mapping
const fontCssMap = {
    'PlayfairDisplay-Bold': "'Playfair Display', serif",
    'Cinzel-Bold': "'Cinzel', serif",
    'GreatVibes-Regular': "'Great Vibes', cursive",
    'Montserrat-Bold': "'Montserrat', sans-serif",
    'OpenSans-Bold': "'Open Sans', sans-serif",
    'OpenSans-Regular': "'Open Sans', sans-serif",
    'Poppins-Bold': "'Poppins', sans-serif",
    'Poppins-Regular': "'Poppins', sans-serif"
};

const fontWeightMap = {
    'PlayfairDisplay-Bold': '700',
    'Cinzel-Bold': '700',
    'GreatVibes-Regular': '400',
    'Montserrat-Bold': '700',
    'OpenSans-Bold': '700',
    'OpenSans-Regular': '400',
    'Poppins-Bold': '700',
    'Poppins-Regular': '400'
};

// Koordinat & tipografi default sistem yang presisi
const defaultLayout = {
    judul: { x_pct: 50, y_pct: 22, font_size: 65, font_family: 'PlayfairDisplay-Bold' },
    preamble: { x_pct: 50, y_pct: 32, font_size: 26, font_family: 'OpenSans-Regular' },
    nama: { x_pct: 50, y_pct: 42, font_size: 85, font_family: 'PlayfairDisplay-Bold' },
    garis: { x_pct: 50, y_pct: 49, width_pct: 45 },
    nrp: { x_pct: 50, y_pct: 53, font_size: 26, font_family: 'Montserrat-Bold' },
    deskripsi: { x_pct: 50, y_pct: 60, font_size: 24, font_family: 'OpenSans-Regular', width_pct: 65 },
    logo: { x_pct: 15, y_pct: 12, height: 120 },
    ttd_kiri: { x_pct: 30, y_pct: 70, height: 130 },
    nama_kiri: { x_pct: 30, y_pct: 85, font_size: 24, font_family: 'Montserrat-Bold' },
    role_kiri: { x_pct: 30, y_pct: 88.5, font_size: 19, font_family: 'OpenSans-Regular' },
    ttd_kanan: { x_pct: 70, y_pct: 70, height: 130 },
    nama_kanan: { x_pct: 70, y_pct: 85, font_size: 24, font_family: 'Montserrat-Bold' },
    role_kanan: { x_pct: 70, y_pct: 88.5, font_size: 19, font_family: 'OpenSans-Regular' }
};

document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('visualEditorModal');
    if (!modalEl) return;

    modalEl.addEventListener('shown.bs.modal', () => {
        loadInitialLayout();
        resizeWorkspace();
        initDraggable();
    });
});

function loadInitialLayout() {
    const dbLayoutStr = '<?= !empty($config['layout_config']) ? addslashes($config['layout_config']) : '' ?>';
    if (dbLayoutStr) {
        try {
            currentLayout = JSON.parse(dbLayoutStr);
        } catch (e) {
            console.error("Gagal parse layout_config DB, menggunakan default.", e);
            currentLayout = JSON.parse(JSON.stringify(defaultLayout));
        }
    } else {
        currentLayout = JSON.parse(JSON.stringify(defaultLayout));
    }
    
    // Fallback field-by-field jika ada field yang hilang dari DB
    for (const key in defaultLayout) {
        if (!currentLayout[key]) {
            currentLayout[key] = { ...defaultLayout[key] };
        } else {
            for (const prop in defaultLayout[key]) {
                if (currentLayout[key][prop] === undefined) {
                    currentLayout[key][prop] = defaultLayout[key][prop];
                }
            }
        }
    }
}

function resizeWorkspace() {
    const bgImg = document.getElementById('editor-bg-image');
    const workspace = document.getElementById('editor-workspace');
    if (!bgImg || !workspace) return;

    if (!bgImg.complete) {
        bgImg.onload = resizeWorkspace;
        return;
    }

    const naturalWidth = bgImg.naturalWidth;
    const naturalHeight = bgImg.naturalHeight;
    if (!naturalWidth || !naturalHeight) return;

    const parent = workspace.parentElement;
    const parentWidth = parent.clientWidth - 32; 
    const parentHeight = parent.clientHeight - 32;

    const imgRatio = naturalWidth / naturalHeight;
    const parentRatio = parentWidth / parentHeight;

    let targetWidth, targetHeight;
    if (imgRatio > parentRatio) {
        targetWidth = parentWidth;
        targetHeight = parentWidth / imgRatio;
    } else {
        targetHeight = parentHeight;
        targetWidth = parentHeight * imgRatio;
    }

    workspace.style.width = targetWidth + 'px';
    workspace.style.height = targetHeight + 'px';
    
    updateElementsOnCanvas();
}

window.addEventListener('resize', () => {
    const modal = document.getElementById('visualEditorModal');
    if (modal && modal.classList.contains('show')) {
        resizeWorkspace();
    }
});

function updateElementsOnCanvas() {
    for (const key in currentLayout) {
        const el = document.getElementById('drag-' + key);
        if (!el) continue;

        el.style.left = currentLayout[key].x_pct + '%';
        el.style.top = currentLayout[key].y_pct + '%';

        const content = el.querySelector('.element-content');

        // Sizing & Font Family untuk Teks (Skala 1:1 terhadap FreeType GD: 1pt = (4/3)px pada skala 2000px)
        if (currentLayout[key].font_size && content) {
            const fsCqw = (currentLayout[key].font_size * 4 / 3) / 20;
            content.style.fontSize = fsCqw + 'cqw';
        }

        if (currentLayout[key].font_family && content) {
            const ff = currentLayout[key].font_family;
            content.style.fontFamily = fontCssMap[ff] || "'Open Sans', sans-serif";
            content.style.fontWeight = fontWeightMap[ff] || '400';
            content.style.fontStyle = (ff.includes('Italic') || ff.includes('GreatVibes')) ? 'italic' : 'normal';
        }

        // Terapkan ukuran Gambar ke TTD / Logo
        if ((key.startsWith('ttd_') || key === 'logo') && currentLayout[key].height) {
            const img = el.querySelector('img');
            if (img) {
                const hCqw = (currentLayout[key].height / 20);
                img.style.height = hCqw + 'cqw';
            }
        }

        // Terapkan lebar garis ke elemen Garis
        if (key === 'garis' && currentLayout[key].width_pct) {
            const line = el.querySelector('.editor-line');
            if (line) {
                line.style.width = currentLayout[key].width_pct + 'cqw';
            }
        }

        // Terapkan lebar deskripsi
        if (key === 'deskripsi' && currentLayout[key].width_pct) {
            el.style.width = currentLayout[key].width_pct + '%';
        }
    }
}

function initDraggable() {
    const draggables = document.querySelectorAll('.draggable-element');
    const container = document.getElementById('editor-workspace');

    draggables.forEach(el => {
        el.removeEventListener('mousedown', dragStart);
        el.removeEventListener('touchstart', dragStart);
        
        el.addEventListener('mousedown', dragStart);
        el.addEventListener('touchstart', dragStart, { passive: false });
        
        el.addEventListener('click', (e) => {
            e.stopPropagation();
            selectElement(el);
        });
    });

    container.addEventListener('click', () => {
        deselectElement();
    });

    let activeEl = null;
    let startX, startY;
    let startX_pct, startY_pct;

    function dragStart(e) {
        e.preventDefault();
        const el = e.currentTarget;
        activeEl = el;
        selectElement(el);

        const clientX = e.type.startsWith('touch') ? e.touches[0].clientX : e.clientX;
        const clientY = e.type.startsWith('touch') ? e.touches[0].clientY : e.clientY;

        startX = clientX;
        startY = clientY;

        const key = el.getAttribute('data-element');
        startX_pct = currentLayout[key].x_pct;
        startY_pct = currentLayout[key].y_pct;

        document.addEventListener('mousemove', dragMove);
        document.addEventListener('touchmove', dragMove, { passive: false });
        document.addEventListener('mouseup', dragEnd);
        document.addEventListener('touchend', dragEnd);
    }

    function dragMove(e) {
        if (!activeEl) return;
        if (e.cancelable) e.preventDefault();

        const clientX = e.type.startsWith('touch') ? e.touches[0].clientX : e.clientX;
        const clientY = e.type.startsWith('touch') ? e.touches[0].clientY : e.clientY;

        const dx = clientX - startX;
        const dy = clientY - startY;

        const containerWidth = container.clientWidth;
        const containerHeight = container.clientHeight;

        const dxPct = (dx / containerWidth) * 100;
        const dyPct = (dy / containerHeight) * 100;

        let xPct = startX_pct + dxPct;
        let yPct = startY_pct + dyPct;

        xPct = Math.max(0, Math.min(100, xPct));
        yPct = Math.max(0, Math.min(100, yPct));

        xPct = Math.round(xPct * 10) / 10;
        yPct = Math.round(yPct * 10) / 10;

        const key = activeEl.getAttribute('data-element');
        currentLayout[key].x_pct = xPct;
        currentLayout[key].y_pct = yPct;

        activeEl.style.left = xPct + '%';
        activeEl.style.top = yPct + '%';

        if (selectedElementId === key) {
            document.getElementById('inspector-x').value = xPct;
            document.getElementById('inspector-y').value = yPct;
        }
    }

    function dragEnd() {
        activeEl = null;
        document.removeEventListener('mousemove', dragMove);
        document.removeEventListener('touchmove', dragMove);
        document.removeEventListener('mouseup', dragEnd);
        document.removeEventListener('touchend', dragEnd);
    }
}

function selectElement(el) {
    document.querySelectorAll('.draggable-element').forEach(item => item.classList.remove('active'));
    el.classList.add('active');
    
    const key = el.getAttribute('data-element');
    selectedElementId = key;

    document.getElementById('inspector-empty').classList.add('d-none');
    document.getElementById('inspector-selected').classList.remove('d-none');

    const title = getElementReadableName(key);
    document.getElementById('inspector-element-title').innerText = title;
    document.getElementById('inspector-element-type').innerText = el.classList.contains('draggable-image') ? 'Tipe: Gambar' : (key === 'garis' ? 'Tipe: Garis' : 'Tipe: Teks');
    document.getElementById('inspector-x').value = currentLayout[key].x_pct;
    document.getElementById('inspector-y').value = currentLayout[key].y_pct;

    // Toggle Sizing & Font Controls berdasarkan Tipe Elemen
    const fontFamContainer = document.getElementById('inspector-fontfamily-container');
    const fsContainer = document.getElementById('inspector-fontsize-container');
    const descWidthContainer = document.getElementById('inspector-descwidth-container');
    const imgHeightContainer = document.getElementById('inspector-imgheight-container');
    const lineWidthContainer = document.getElementById('inspector-linewidth-container');

    fontFamContainer.classList.add('d-none');
    fsContainer.classList.add('d-none');
    descWidthContainer.classList.add('d-none');
    imgHeightContainer.classList.add('d-none');
    lineWidthContainer.classList.add('d-none');

    if (key === 'garis') {
        lineWidthContainer.classList.remove('d-none');
        const wPct = currentLayout[key].width_pct || 45;
        document.getElementById('inspector-linewidth').value = wPct;
        document.getElementById('inspector-linewidth-badge').innerText = wPct + '%';
    } else if (key.startsWith('ttd_') || key === 'logo') {
        imgHeightContainer.classList.remove('d-none');
        const imgH = currentLayout[key].height || 120;
        document.getElementById('inspector-imgheight').value = imgH;
        document.getElementById('inspector-imgheight-badge').innerText = imgH + 'px';
    } else {
        fontFamContainer.classList.remove('d-none');
        fsContainer.classList.remove('d-none');

        const currentFont = currentLayout[key].font_family || defaultLayout[key]?.font_family || 'OpenSans-Bold';
        document.getElementById('inspector-fontfamily').value = currentFont;

        const fs = currentLayout[key].font_size || defaultLayout[key]?.font_size || 24;
        document.getElementById('inspector-fontsize').value = fs;
        document.getElementById('inspector-fontsize-badge').innerText = fs + 'pt';

        if (key === 'deskripsi') {
            descWidthContainer.classList.remove('d-none');
            const dWidth = currentLayout[key].width_pct || 65;
            document.getElementById('inspector-descwidth').value = dWidth;
            document.getElementById('inspector-descwidth-badge').innerText = dWidth + '%';
        }
    }
}

function deselectElement() {
    document.querySelectorAll('.draggable-element').forEach(item => item.classList.remove('active'));
    selectedElementId = null;

    document.getElementById('inspector-empty').classList.remove('d-none');
    document.getElementById('inspector-selected').classList.add('d-none');
}

function getElementReadableName(key) {
    const names = {
        logo: "Logo Tambahan / Mitra",
        judul: "Judul Sertifikat",
        preamble: "Teks Pengantar (Preamble)",
        nama: "Nama Asisten",
        garis: "Garis Pembatas",
        nrp: "NRP Asisten",
        deskripsi: "Deskripsi Dedikasi",
        ttd_kiri: "TTD Kepala Lab",
        nama_kiri: "Nama Kepala Lab",
        role_kiri: "Jabatan Kepala Lab",
        ttd_kanan: "TTD Ketua Prodi",
        nama_kanan: "Nama Ketua Prodi",
        role_kanan: "Jabatan Ketua Prodi"
    };
    return names[key] || key;
}

function centerSelectedHorizontal() {
    if (!selectedElementId) return;
    document.getElementById('inspector-x').value = 50;
    updateSelectedPosition();
}

function updateSelectedPosition() {
    if (!selectedElementId) return;

    const xVal = parseFloat(document.getElementById('inspector-x').value) || 0;
    const yVal = parseFloat(document.getElementById('inspector-y').value) || 0;

    const xPct = Math.max(0, Math.min(100, xVal));
    const yPct = Math.max(0, Math.min(100, yVal));

    currentLayout[selectedElementId].x_pct = xPct;
    currentLayout[selectedElementId].y_pct = yPct;

    const el = document.getElementById('drag-' + selectedElementId);
    if (el) {
        el.style.left = xPct + '%';
        el.style.top = yPct + '%';
    }
}

function updateSelectedFontFamily() {
    if (!selectedElementId) return;
    const select = document.getElementById('inspector-fontfamily');
    const selectedFont = select.value;
    currentLayout[selectedElementId].font_family = selectedFont;

    const el = document.getElementById('drag-' + selectedElementId);
    if (el) {
        const content = el.querySelector('.element-content');
        if (content) {
            content.style.fontFamily = fontCssMap[selectedFont] || "'Open Sans', sans-serif";
            content.style.fontWeight = fontWeightMap[selectedFont] || '400';
            content.style.fontStyle = (selectedFont.includes('Italic') || selectedFont.includes('GreatVibes')) ? 'italic' : 'normal';
        }
    }
}

function updateSelectedSize() {
    if (!selectedElementId) return;

    const el = document.getElementById('drag-' + selectedElementId);
    if (!el) return;

    if (selectedElementId === 'garis') {
        const wPct = parseInt(document.getElementById('inspector-linewidth').value) || 45;
        currentLayout[selectedElementId].width_pct = wPct;
        document.getElementById('inspector-linewidth-badge').innerText = wPct + '%';
        
        const line = el.querySelector('.editor-line');
        if (line) {
            line.style.width = wPct + 'cqw';
        }
    } else if (selectedElementId.startsWith('ttd_') || selectedElementId === 'logo') {
        const imgH = parseInt(document.getElementById('inspector-imgheight').value) || 120;
        currentLayout[selectedElementId].height = imgH;
        document.getElementById('inspector-imgheight-badge').innerText = imgH + 'px';
        
        const img = el.querySelector('img');
        if (img) {
            img.style.height = (imgH / 20) + 'cqw';
        }
    } else {
        const fs = parseFloat(document.getElementById('inspector-fontsize').value) || 24;
        currentLayout[selectedElementId].font_size = fs;
        document.getElementById('inspector-fontsize-badge').innerText = fs + 'pt';
        
        const content = el.querySelector('.element-content');
        if (content) {
            content.style.fontSize = ((fs * 4 / 3) / 20) + 'cqw';
        }

        if (selectedElementId === 'deskripsi') {
            const dWidth = parseInt(document.getElementById('inspector-descwidth').value) || 65;
            currentLayout[selectedElementId].width_pct = dWidth;
            document.getElementById('inspector-descwidth-badge').innerText = dWidth + '%';
            el.style.width = dWidth + '%';
        }
    }
}

function resetToDefault() {
    if (confirm("Apakah Anda yakin ingin mengembalikan seluruh posisi elemen dan font ke bawaan sistem?")) {
        currentLayout = JSON.parse(JSON.stringify(defaultLayout));
        updateElementsOnCanvas();
        if (selectedElementId) {
            selectElement(document.getElementById('drag-' + selectedElementId));
        }
    }
}

function getCsrfTokenValue() {
    const cookieName = '<?= config('Security')->cookieName ?? 'csrf_cookie' ?>';
    const match = document.cookie.match(new RegExp('(^|;\\s*)' + cookieName + '=([^;]+)'));
    if (match && match[2]) return decodeURIComponent(match[2]);
    const input = document.querySelector('input[name="<?= csrf_token() ?>"]');
    if (input && input.value) return input.value;
    return '<?= csrf_hash() ?>';
}

function saveLayoutCoords() {
    const saveBtn = document.querySelector('button[onclick="saveLayoutCoords()"]');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';

    const url = '<?= site_url('sertifikat/save-layout') ?>';
    const csrfToken = getCsrfTokenValue();
    const csrfHeader = '<?= csrf_header() ?>';
    
    const headers = {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };
    if (csrfHeader && csrfToken) {
        headers[csrfHeader] = csrfToken;
    }
    
    fetch(url, {
        method: 'POST',
        headers: headers,
        body: JSON.stringify(currentLayout)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errData => {
                throw new Error(errData.message || 'HTTP Error ' + response.status);
            }).catch(() => {
                throw new Error('HTTP Error ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            alert(data.message || 'Tata letak berhasil disimpan.');
            const modalEl = document.getElementById('visualEditorModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            window.location.reload();
        } else {
            alert('Gagal menyimpan tata letak: ' + (data.message || 'Error tidak diketahui'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal menyimpan tata letak: ' + error.message);
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}
</script>
<?php endif; ?>