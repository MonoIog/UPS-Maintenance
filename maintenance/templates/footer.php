<div class="footer-wrapper" id="site-footer">
    <button class="footer-toggle-btn" id="footer-toggle-btn" title="Toggle Footer View">
        <i class="fas fa-chevron-down"></i>
    </button>
    <footer class="site-footer-content">
        <div class="footer-container" id="footer-full-content">
            <!-- Column 1: Project Info -->
            <div class="footer-column footer-info">
                <div class="logo-container">
                    <img src="<?php echo $base_path; ?>/assets/images/SIPUT-Header.png" alt="SIPUT Logo" class="logo-icon">
                    <span class="logo-text">SIPUT</span>
                </div>
                <p class="tagline">Sistem Informasi Pemeliharaan UPS</p>
                <p class="description">
                    Aplikasi manajemen terintegrasi untuk pemeliharaan unit UPS,
                    memastikan keandalan operasional dengan penjadwalan, pelaporan, dan inventaris yang efisien.
                </p>
            </div>

            <!-- Column 2: Navigation Links -->
            <div class="footer-column footer-links">
                <h3>Navigasi</h3>
                <ul>
                    <li><a href="<?php echo $base_path; ?>/views/dashboard">Dashboard</a></li>
                    <li><a href="<?php echo $base_path; ?>/views/ups/list">Perangkat UPS</a></li>
                    <li><a href="<?php echo $base_path; ?>/views/hasil/add">Jadwal & Laporan</a></li>
                    <li><a href="<?php echo $base_path; ?>/views/hasil/list">Riwayat Laporan</a></li>
                </ul>
            </div>

            <!-- Column 3: Contact Info -->
            <div class="footer-column footer-contact">
                <h3>Contact</h3>
                <ul>
                    <li><i class="fas fa-map-marker-alt"></i><span>Jl. Parigi No. 1, Tanjung Enim, Sumatera Selatan, Indonesia</span></li>
                    <li><i class="fas fa-phone-alt"></i><span>(0734) 451-096</span></li>
                    <li><i class="fas fa-envelope"></i><span>corsec@ptba.co.id</span></li>
                </ul>
            </div>

            <!-- Column 4: Social & Copyright -->
            <div class="footer-column footer-social-copyright">
                <h3>Terhubung</h3>
                <div class="social-icons">
                    <a href="https://www.instagram.com/akiraadocx/" title="Instagram" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.ptba.co.id/" title="PTBA Website" target="_blank"><i class="fas fa-globe"></i></a>
                </div>
                <p class="copyright">&copy; <?php echo date('Y'); ?> Muhammad Athira Ramadhan. Hak cipta dilindungi.</p>
            </div>
        </div>

        <!-- Collapsed Footer Content -->
        <div id="footer-simple-content">
            <div class="footer-content">
                <span>&copy; <?php echo date('Y'); ?> Muhammad Athira Ramadhan. Hak cipta dilindungi.</span>
                <span class="d-none d-sm-inline"> | Sistem Informasi Pemeliharaan UPS</span>
            </div>
        </div>
    </footer>
</div>
</div> <!-- Close main-content -->
</div> <!-- Close page-wrapper -->
</div> <!-- Close app-container -->


<!-- Universal Notification Container -->
<div id="notification-container"></div>

<!-- Universal Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal">
    <div class="modal-content modal-sm">
        <div class="modal-body">
            <div class="modal-icon-container">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="modal-title">Konfirmasi Penghapusan</h2>
            <p id="deleteModalDetail" class="modal-text">
                Apakah Anda benar-benar yakin ingin menghapus item ini? Proses ini tidak bisa dibatalkan.
            </p>
        </div>
        <div class="modal-footer-confirm">
            <button type="button" id="cancelDeleteBtn" class="btn btn-secondary">Batal</button>
            <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Ya, Hapus</a>
        </div>
    </div>
</div>

<!-- Attachment Viewer Modal -->
<div id="attachmentViewerModal" class="modal">
    <div class="modal-content modal-lg" style="height: 90vh;">
        <div class="modal-header">
            <h2 id="attachment-viewer-title">Penampil Lampiran</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body" style="padding: 0; display: flex; flex-direction: column;">
            <iframe id="attachment-iframe" style="width: 100%; height: 100%; border: none;" src="about:blank"></iframe>
        </div>
    </div>
</div>

<!-- Edit Report Modal -->
<div id="editReportModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2>Ubah Laporan Pemeliharaan <span id="edit-report-id"></span></h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <form id="editReportForm" method="POST" action="<?php echo $base_path; ?>/views/hasil/update_report" class="modal-form" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="maintenance_id" id="edit_maintenance_id">

                <div class="details-section" style="border-bottom:none; padding-bottom:0; margin-bottom: 20px;">
                    <h2 class="section-title">Informasi Tugas</h2>
                    <div class="details-grid">
                        <div><strong>Unit UPS:</strong> <span id="edit_ups_name_display"></span></div>
                        <div><strong>Teknisi:</strong> <span id="edit_teknisi_display"></span></div>
                        <div><strong>Tanggal Jadwal:</strong> <span id="edit_tanggal_jadwal_display"></span></div>
                        <div><strong>Jenis Pemeliharaan:</strong> <span id="edit_jenis_display"></span></div>
                    </div>
                </div>

                <input type="hidden" name="tanggal_jadwal" id="hidden_tanggal_jadwal">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_tanggal_pelaksanaan">Tanggal & Waktu Pelaksanaan</label>
                        <input type="datetime-local" id="edit_tanggal_pelaksanaan" name="tanggal_pelaksanaan">
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <div class="custom-select-wrapper">
                            <div class="custom-select-trigger"><span class="placeholder">-- Pilih Status --</span><i class="fas fa-chevron-down"></i></div>
                            <div class="custom-options">
                                <div class="options-list"></div>
                            </div>
                            <select id="edit_status" name="status" required style="display: none;">
                                <option value="">-- Pilih Status --</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Ditunda">Ditunda</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_hasil_pengecekan">Hasil Pengecekan</label>
                    <textarea id="edit_hasil_pengecekan" name="hasil_pengecekan" rows="4" placeholder="Jelaskan temuan..."></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_pengubahan">Tindakan yang diambil</label>
                    <textarea id="edit_pengubahan" name="pengubahan" rows="4" placeholder="Jelaskan tindakan yang diambil..."></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_catatan">Catatan Tambahan</label>
                    <textarea id="edit_catatan" name="catatan" rows="2" placeholder="Catatan tambahan..."></textarea>
                </div>

                <div class="form-group">
                    <label>Lampiran (Hardcopy)</label>
                    <label for="edit_attachment" class="attachment-box" data-default-text="Unggah file atau seret & jatuhkan">
                        <i class="fas fa-file-upload" style="color: #4299e1;"></i>
                        <p class="attachment-text">Unggah file atau seret & jatuhkan</p>
                        <span>PDF, Gambar hingga 50MB</span>
                    </label>
                    <input type="file" id="edit_attachment" name="attachment" accept=".pdf,.png,.jpg,.jpeg,.gif,.webp,.bmp" style="display: none;">
                    <input type="hidden" name="old_attachment_path" id="edit_old_attachment_path">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary close-modal-footer-btn">Batal</button>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<div id="loading-spinner" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div class="spinner"></div>
</div>

<script src="<?php echo $base_path; ?>/assets/js/script.js?v=1.4"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>

<?php
if (function_exists('display_notification')) {
    display_notification();
}
?>

</body>

</html>