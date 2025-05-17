<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Laporan Pembelian Selesai</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
        </div>
        <div class="card-body">
            <form id="filterLaporan">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Tanggal Mulai</label>
                            <input type="date" class="form-control" id="startDate" name="start_date" required>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Tanggal Akhir</label>
                            <input type="date" class="form-control" id="endDate" name="end_date" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group" style="margin-top: 30px">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button type="button" id="resetFilter" class="btn btn-secondary">
                                <i class="fas fa-sync-alt"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Data Pembelian Selesai</h6>
            <div>
                <button id="cetakLaporan" class="btn btn-success">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="laporanTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Pembelian</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>No HP</th>
                            <th>Produk</th>
                            <th>Kota Tujuan</th>
                            <th>Ongkos Kirim</th>
                            <th>Total Pembelian</th>
                            <th>Resi</th>
                        </tr>
                    </thead>
                    <tbody id="laporanBody">
                        <!-- Data akan diisi via AJAX -->
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td colspan="7">Total Keuntungan</td>
                            <td colspan="3" class="text-right" id="totalKeuntungan">Rp0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Set tanggal default (1 bulan terakhir)
        const endDate = new Date();
        const startDate = new Date();
        startDate.setMonth(startDate.getMonth() - 1);

        $('#startDate').val(startDate.toISOString().split('T')[0]);
        $('#endDate').val(endDate.toISOString().split('T')[0]);

        // Load data awal
        loadLaporan();

        // Handle submit filter
        $('#filterLaporan').submit(function(e) {
            e.preventDefault();
            loadLaporan();
        });

        // Handle reset filter
        $('#resetFilter').click(function() {
            $('#startDate').val('');
            $('#endDate').val('');
            loadLaporan();
        });

        // Handle cetak laporan
        $('#cetakLaporan').click(function() {
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();

            if (!startDate || !endDate) {
                Swal.fire('Peringatan', 'Harap pilih tanggal mulai dan tanggal akhir', 'warning');
                return;
            }

            window.open(`app/controllers/LaporanController.php?aksi=cetak_laporan&start_date=${startDate}&end_date=${endDate}`, '_blank');
        });

        // Fungsi untuk memuat data laporan
        function loadLaporan() {
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();

            if (!startDate || !endDate) {
                Swal.fire('Peringatan', 'Harap pilih tanggal mulai dan tanggal akhir', 'warning');
                return;
            }

            $.ajax({
                url: 'app/controllers/LaporanController.php?aksi=laporan_pembelian',
                type: 'GET',
                data: {
                    start_date: startDate,
                    end_date: endDate
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#laporanBody').html('<tr><td colspan="10" class="text-center">Memuat data...</td></tr>');
                },
                success: function(response) {
                    if (response.success) {
                        let html = '';
                        let no = 1;

                        if (response.data.length > 0) {
                            response.data.forEach(function(item) {
                                html += `
                                <tr>
                                    <td>${no++}</td>
                                    <td>${item.id_pembelian}</td>
                                    <td>${item.tanggal}</td>
                                    <td>${escapeHtml(item.nama_pelanggan)}</td>
                                    <td>${escapeHtml(item.no_hp || '-')}</td>
                                    <td>${escapeHtml(item.produk)}</td>
                                    <td>${escapeHtml(item.nama_kota)}</td>
                                    <td class="text-right">${item.ongkir}</td>
                                    <td class="text-right">${item.total}</td>
                                    <td>${escapeHtml(item.resi_pengiriman || '-')}</td>
                                </tr>
                            `;
                            });
                        } else {
                            html = '<tr><td colspan="10" class="text-center">Tidak ada data ditemukan</td></tr>';
                        }

                        $('#laporanBody').html(html);
                        $('#totalKeuntungan').text(response.total_keuntungan);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Terjadi kesalahan saat memuat data', 'error');
                }
            });
        }

        // Fungsi untuk escape HTML
        function escapeHtml(text) {
            return text.replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    });
</script>