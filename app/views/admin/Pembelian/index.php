<!-- Tambahkan di bagian HTML -->
<div class="row mb-3">
    <div class="col-md-6">
        <h2>Data Pembelian</h2>
    </div>
    <div class="col-md-6 text-end">
        <div class="btn-group">
            <button class="btn btn-outline-secondary filter-status" data-status="all">Semua</button>
            <button class="btn btn-outline-warning filter-status" data-status="pending">Pending</button>
            <button class="btn btn-outline-info filter-status" data-status="dibayar">Dibayar</button>
            <button class="btn btn-outline-primary filter-status" data-status="dikirim">Dikirim</button>
            <button class="btn btn-outline-success filter-status" data-status="selesai">Selesai</button>
            <button class="btn btn-outline-danger filter-status" data-status="batal">Batal</button>
        </div>
    </div>
</div>

<table id="pembelianTable" class="display" style="width:100%">
    <thead>
        <tr>
            <th>No</th>
            <th>ID Pembelian</th>
            <th>Pelanggan</th>
            <th>Tanggal</th>
            <th>Total</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
</table>

<!-- Modal Detail Pembelian -->
<div class="modal fade" id="modalDetailPembelian" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pembelian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informasi Pembelian</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th>ID Pembelian</th>
                                <td id="detail_id"></td>
                            </tr>
                            <tr>
                                <th>Tanggal</th>
                                <td id="detail_tanggal"></td>
                            </tr>
                            <tr>
                                <th>Total</th>
                                <td id="detail_total"></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td id="detail_status"></td>
                            </tr>
                            <tr>
                                <th>Resi</th>
                                <td id="detail_resi"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Informasi Pengiriman</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th>Pelanggan</th>
                                <td id="detail_pelanggan"></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td id="detail_email"></td>
                            </tr>
                            <tr>
                                <th>No HP</th>
                                <td id="detail_no_hp"></td>
                            </tr>
                            <tr>
                                <th>Kota Tujuan</th>
                                <td id="detail_kota"></td>
                            </tr>
                            <tr>
                                <th>Ongkos Kirim</th>
                                <td id="detail_ongkir"></td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td id="detail_alamat"></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h6 class="mt-3">Produk yang Dibeli</h6>
                <table class="table table-bordered" id="detailProdukTable">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detailProdukBody">
                        <!-- Data produk akan diisi via AJAX -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ubah Status -->
<div class="modal fade" id="modalUbahStatus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formUbahStatus">
            <input type="hidden" name="id_pembelian" id="status_id_pembelian">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Status Pembelian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" id="selectStatus" class="form-select" required>
                            <option value="pending">Pending</option>
                            <option value="dibayar">Dibayar</option>
                            <option value="dikirim">Dikirim</option>
                            <option value="selesai">Selesai</option>
                            <option value="batal">Batal</option>
                        </select>
                    </div>
                    <div class="mb-3" id="resiField">
                        <label>Nomor Resi</label>
                        <input type="text" name="resi" id="inputResi" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Inisialisasi DataTable
        let pembelianTable = $('#pembelianTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: 'app/controllers/PembelianController.php?aksi=list',
                data: function(d) {
                    d.status = $('.filter-status.active').data('status') || 'all';
                }
            },
            columns: [{
                    data: 'no'
                },
                {
                    data: 'id_pembelian'
                },
                {
                    data: 'nama_pelanggan'
                },
                {
                    data: 'tanggal'
                },
                {
                    data: 'total'
                },
                {
                    data: 'status'
                },
                {
                    data: 'aksi',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // Filter berdasarkan status
        $('.filter-status').click(function() {
            $('.filter-status').removeClass('active');
            $(this).addClass('active');
            pembelianTable.ajax.reload();
        });

        // Handle klik tombol detail
        $('#pembelianTable').on('click', '.btn-detail', function() {
            const id = $(this).data('id');

            $.getJSON('app/controllers/PembelianController.php?aksi=detail&id=' + id, function(response) {
                if (response.success) {
                    const data = response.data;

                    // Isi data pembelian
                    $('#detail_id').text(data.id_pembelian);
                    $('#detail_tanggal').text(data.tanggal_pembelian);
                    $('#detail_total').text('Rp' + data.total_pembelian);
                    $('#detail_status').html(getStatusBadge(data.status_pembelian));
                    $('#detail_resi').text(data.resi_pengiriman || '-');

                    // Isi data pelanggan
                    $('#detail_pelanggan').text(data.nama_pelanggan);
                    $('#detail_email').text(data.email);
                    $('#detail_no_hp').text(data.no_hp);
                    $('#detail_kota').text(data.nama_kota);
                    $('#detail_ongkir').text('Rp' + data.tarif);
                    $('#detail_alamat').text(data.alamat_pengiriman);

                    // TODO: Tambahkan AJAX untuk mendapatkan detail produk yang dibeli
                    // $('#detailProdukBody').html(...);

                    $('#modalDetailPembelian').modal('show');
                } else {
                    Swal.fire('Gagal!', response.message, 'error');
                }
            });
        });

        // Handle klik tombol ubah status
        $('#pembelianTable').on('click', '.btn-edit-status', function() {
            const id = $(this).data('id');
            $('#status_id_pembelian').val(id);

            // Dapatkan status saat ini
            $.getJSON('app/controllers/PembelianController.php?aksi=detail&id=' + id, function(response) {
                if (response.success) {
                    $('#selectStatus').val(response.data.status_pembelian);
                    $('#inputResi').val(response.data.resi_pengiriman || '');

                    // Tampilkan field resi hanya jika status dikirim
                    toggleResiField();

                    $('#modalUbahStatus').modal('show');
                } else {
                    Swal.fire('Gagal!', response.message, 'error');
                }
            });
        });

        // Toggle field resi berdasarkan status
        $('#selectStatus').change(function() {
            toggleResiField();
        });

        function toggleResiField() {
            if ($('#selectStatus').val() === 'dikirim') {
                $('#resiField').show();
            } else {
                $('#resiField').hide();
            }
        }

        // Handle form ubah status
        $('#formUbahStatus').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: 'app/controllers/PembelianController.php?aksi=update_status',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#modalUbahStatus').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        pembelianTable.ajax.reload(null, false);
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Gagal!', 'Terjadi kesalahan: ' + xhr.statusText, 'error');
                }
            });
        });

        // Handle klik tombol hapus
        $('#pembelianTable').on('click', '.btn-hapus', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: 'Data pembelian akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'app/controllers/PembelianController.php?aksi=hapus',
                        type: 'POST',
                        data: {
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Terhapus!', response.message, 'success');
                                pembelianTable.ajax.reload(null, false);
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', 'Terjadi kesalahan: ' + xhr.statusText, 'error');
                        }
                    });
                }
            });
        });

        // Fungsi untuk menampilkan badge status
        function getStatusBadge(status) {
            const badges = {
                'pending': '<span class="badge bg-warning">Pending</span>',
                'dibayar': '<span class="badge bg-info">Dibayar</span>',
                'dikirim': '<span class="badge bg-primary">Dikirim</span>',
                'selesai': '<span class="badge bg-success">Selesai</span>',
                'batal': '<span class="badge bg-danger">Batal</span>'
            };
            return badges[status] || status;
        }
    });
</script>