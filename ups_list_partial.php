<div class="filter-bar">
    <form action="list" method="GET" class="filter-form">
        <div class="filter-group">
            <label for="tipe">Tipe</label>
            <div class="custom-select-wrapper">
                <div class="custom-select-trigger">
                    <span><?php echo $tipe_filter !== 'all' ? htmlspecialchars($tipe_filter) : 'Semua Tipe'; ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="custom-options">
                    <input type="text" class="custom-select-search" placeholder="Cari Tipe...">
                    <div class="options-list"></div>
                </div>
                <select name="tipe" id="tipe" onchange="this.form.submit()" style="display: none;">
                    <option value="all">Semua Tipe</option>
                    <?php if ($tipes) mysqli_data_seek($tipes, 0);
                    while ($tipe = mysqli_fetch_assoc($tipes)) : ?>
                        <option value="<?php echo htmlspecialchars($tipe['tipe_ups']); ?>" <?php echo ($tipe_filter == $tipe['tipe_ups']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($tipe['tipe_ups']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="filter-group">
            <label for="capacity">Kapasitas</label>
            <div class="custom-select-wrapper">
                <div class="custom-select-trigger">
                    <span><?php echo $capacity_filter !== 'all' ? htmlspecialchars($capacity_filter) : 'Semua Kapasitas'; ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="custom-options">
                    <input type="text" class="custom-select-search" placeholder="Cari Kapasitas...">
                    <div class="options-list"></div>
                </div>
                <select name="capacity" id="capacity" onchange="this.form.submit()" style="display: none;">
                    <option value="all">Semua Kapasitas</option>
                    <?php if ($capacities) mysqli_data_seek($capacities, 0);
                    while ($cap = mysqli_fetch_assoc($capacities)) : ?>
                        <option value="<?php echo htmlspecialchars($cap['ukuran_kapasitas']); ?>" <?php echo ($capacity_filter == $cap['ukuran_kapasitas']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cap['ukuran_kapasitas']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="filter-group">
            <label for="company">Perusahaan Maintenance</label>
            <div class="custom-select-wrapper">
                <div class="custom-select-trigger">
                    <span><?php echo $company_filter !== 'all' ? htmlspecialchars($company_filter) : 'Semua Perusahaan Maintenance'; ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="custom-options">
                    <input type="text" class="custom-select-search" placeholder="Cari Perusahaan Maintenance...">
                    <div class="options-list"></div>
                </div>
                <select name="company" id="company" onchange="this.form.submit()" style="display: none;">
                    <option value="all">Semua</option>
                    <?php if ($companies) mysqli_data_seek($companies, 0);
                    while ($comp = mysqli_fetch_assoc($companies)) : ?>
                        <option value="<?php echo htmlspecialchars($comp['perusahaan_maintenance']); ?>" <?php echo ($company_filter == $comp['perusahaan_maintenance']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($comp['perusahaan_maintenance']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <a href="list" class="btn-secondary" style="align-self: flex-end; text-decoration: none;">Reset</a>
    </form>
</div>

<div class="table-container">
    <div class="table-header">
        <h2>Semua Unit UPS</h2>
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search UPS...">
        </div>
    </div>
    <table id="upsTable">
        <thead>
            <tr>
                <th>NAMA UPS</th>
                <th>MERK</th>
                <th>TIPE</th>
                <th>Kapasitas</th>
                <th>LOKASI</th>
                <th>ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($dataUPS && mysqli_num_rows($dataUPS) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($dataUPS)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nama_ups']); ?></td>
                        <td><?php echo htmlspecialchars($row['merk']); ?></td>
                        <td><?php echo htmlspecialchars($row['tipe_ups']); ?></td>
                        <td><?php echo htmlspecialchars($row['ukuran_kapasitas']); ?> KVA</td>
                        <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                        <td class="table-actions">
                            <button class="btn-details open-ups-details-btn" title="View Details"
                                data-ups_id="<?php echo htmlspecialchars($row['ups_id']); ?>"
                                data-nama_ups="<?php echo htmlspecialchars($row['nama_ups']); ?>"
                                data-lokasi="<?php echo htmlspecialchars($row['lokasi']); ?>"
                                data-merk="<?php echo htmlspecialchars($row['merk']); ?>"
                                data-tipe_ups="<?php echo htmlspecialchars($row['tipe_ups']); ?>"
                                data-ip_address="<?php echo htmlspecialchars($row['ip_address']); ?>"
                                data-ukuran_kapasitas="<?php echo htmlspecialchars($row['ukuran_kapasitas']); ?>"
                                data-jumlah_baterai="<?php echo htmlspecialchars($row['jumlah_baterai']); ?>"
                                data-perusahaan_maintenance="<?php echo htmlspecialchars($row['perusahaan_maintenance']); ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="edit-btn" title="Edit"
                                data-ups_id="<?php echo htmlspecialchars($row['ups_id']); ?>"
                                data-nama_ups="<?php echo htmlspecialchars($row['nama_ups']); ?>"
                                data-lokasi="<?php echo htmlspecialchars($row['lokasi']); ?>"
                                data-merk="<?php echo htmlspecialchars($row['merk']); ?>"
                                data-tipe_ups="<?php echo htmlspecialchars($row['tipe_ups']); ?>"
                                data-ip_address="<?php echo htmlspecialchars($row['ip_address']); ?>"
                                data-ukuran_kapasitas="<?php echo htmlspecialchars($row['ukuran_kapasitas']); ?>"
                                data-jumlah_baterai="<?php echo htmlspecialchars($row['jumlah_baterai']); ?>"
                                data-perusahaan_maintenance="<?php echo htmlspecialchars($row['perusahaan_maintenance']); ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="delete-btn" title="Delete"
                                data-detail="<?php echo htmlspecialchars($row['nama_ups'] . ' (' . $row['lokasi'] . ')'); ?>"
                                data-href="delete?id=<?php echo htmlspecialchars($row['ups_id']); ?>">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">Tidak ada Unit UPS yang sesuai kriteria anda.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="pagination-footer">
        <p>Menampilkan <?php echo $dataUPS ? mysqli_num_rows($dataUPS) : 0; ?> dari <?php echo htmlspecialchars($total_rows); ?> Hasil</p>
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php $query_params = "tipe=$tipe_filter&capacity=$capacity_filter&company=$company_filter"; ?>
                <a href="?page=<?php echo max(1, $page - 1); ?>&<?php echo htmlspecialchars($query_params, ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo ($page <= 1) ? 'disabled' : ''; ?>">Sebelumnya</a>
                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                    <a href="?page=<?php echo $i; ?>&<?php echo htmlspecialchars($query_params, ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <a href="?page=<?php echo min($total_pages, $page + 1); ?>&<?php echo htmlspecialchars($query_params, ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">Selanjutnya</a>
            </div>
        <?php endif; ?>
    </div>
</div>