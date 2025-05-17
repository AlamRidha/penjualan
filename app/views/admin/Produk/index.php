<button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalTambahProduk">
    Tambah Produk
</button>

<h2>Data Produk</h2>
<table id="produkTable" class="display" style="width:100%">
    <thead>
        <tr>
            <th>No</th>
            <th>Foto</th>
            <th>Nama Produk</th>
            <th>Deskripsi</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Aksi</th>
        </tr>
    </thead>
</table>

<!-- Modal Tambah Produk -->
<div class="modal fade" id="modalTambahProduk" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="formTambahProduk" enctype="multipart/form-data">
            <!-- <form action="app/controllers/ProdukController.php?aksi=tambah" method="post" enctype="multipart/form-data"> -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Produk</label>
                        <input type="text" name="nama_produk" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Harga Produk</label>
                        <input type="number" name="harga_produk" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Stok Produk</label>
                        <input type="number" name="stok_produk" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Deskripsi Produk</label>
                        <textarea name="deskripsi_produk" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Foto Produk</label>
                        <input type="file" name="foto_produk" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Produk -->
<div class="modal fade" id="modalEditProduk" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="formEditProduk" enctype="multipart/form-data">
            <!-- <form action="app/controllers/ProdukController.php?aksi=edit" method="post" enctype="multipart/form-data"> -->
            <input type="hidden" name="id_produk" id="edit_id_produk">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Produk</label>
                        <input type="text" name="nama_produk" id="edit_nama_produk" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Harga Produk</label>
                        <input type="number" name="harga_produk" id="edit_harga_produk" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Stok Produk</label>
                        <input type="number" name="stok_produk" id="edit_stok_produk" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Deskripsi Produk</label>
                        <textarea name="deskripsi_produk" id="edit_deskripsi_produk" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Foto Produk (opsional)</label>
                        <input type="file" name="foto_produk" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>



<script>
    $(document).ready(function() {
        let table = $('#produkTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "app/controllers/ProdukController.php?aksi=list",
            columns: [{
                    data: "no"
                },
                {
                    data: "foto_produk"
                },
                {
                    data: "nama_produk"
                },
                {
                    data: "deskripsi_produk"
                },
                {
                    data: "harga_produk"
                },
                {
                    data: "stok_produk"
                },
                {
                    data: "aksi",
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // Reset form saat modal ditutup
        $('#modalTambahProduk, #modalEditProduk').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
        });

        // Tambah produk
        $('#formTambahProduk').submit(function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                url: 'app/controllers/ProdukController.php?aksi=tambah',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function() {
                    $('#modalTambahProduk').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Produk berhasil ditambahkan.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function() {
                    alert('Gagal menambah produk.');
                }
            });
        });

        // Edit produk (tampilkan modal + isi data)
        $('#produkTable').on('click', '.btn-edit', function() {
            const id = $(this).data('id');

            $.ajax({
                url: 'app/controllers/ProdukController.php?aksi=detail&id=' + id,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#edit_id_produk').val(data.id_produk);
                    $('#edit_nama_produk').val(data.nama_produk);
                    $('#edit_harga_produk').val(data.harga_produk);
                    $('#edit_stok_produk').val(data.stok_produk);
                    $('#edit_deskripsi_produk').val(data.deskripsi_produk);

                    $('#modalEditProduk').modal('show');
                },
                error: function() {
                    alert('Gagal mengambil data produk.');
                }
            });
        });

        // Submit form edit
        $('#formEditProduk').submit(function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: 'app/controllers/ProdukController.php?aksi=edit',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function() {
                    $('#modalEditProduk').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Produk berhasil diperbarui.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function() {
                    alert('Gagal mengedit produk.');
                }
            });
        });

        // Hapus produk
        $('#produkTable').on('click', '.btn-hapus', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data tidak bisa dikembalikan setelah dihapus.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'app/controllers/ProdukController.php?aksi=hapus&id=' + id,
                        method: 'GET',
                        success: function(res) {
                            const result = JSON.parse(res);
                            if (result.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Produk berhasil dihapus.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: result.message || 'Gagal menghapus produk.'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Terjadi kesalahan saat menghapus produk.'
                            });
                        }
                    });
                }
            });
        });


    });
</script>