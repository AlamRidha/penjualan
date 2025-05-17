<button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalTambahPelanggan">
    Tambah Pelanggan
</button>

<h2>Data Pelanggan</h2>
<table id="pelangganTable" class="display" style="width:100%">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Email</th>
            <th>No HP</th>
            <th>Alamat</th>
            <th>Aksi</th>
        </tr>
    </thead>
</table>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambahPelanggan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formTambahPelanggan">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>No HP</label>
                        <input type="text" name="no_hp" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control" required></textarea>
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

<!-- Modal Edit -->
<div class="modal fade" id="modalEditPelanggan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formEditPelanggan">
            <input type="hidden" name="id_pelanggan" id="edit_id_pelanggan">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama</label>
                        <input type="text" name="nama" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small></label>
                        <input type="password" name="password" class="form-control" placeholder="Biarkan kosong untuk mempertahankan password lama">
                    </div>
                    <div class="mb-3">
                        <label>No HP</label>
                        <input type="text" name="no_hp" id="edit_no_hp" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="alamat" id="edit_alamat" class="form-control" required></textarea>
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
        let table = $('#pelangganTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: 'app/controllers/PelangganController.php?aksi=list',
            columns: [{
                    data: 'no'
                },
                {
                    data: 'nama'
                },
                {
                    data: 'email'
                },
                {
                    data: 'no_hp'
                },
                {
                    data: 'alamat'
                },
                {
                    data: 'aksi',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $('#modalTambahPelanggan, #modalEditPelanggan').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
        });

        $('#formTambahPelanggan').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'app/controllers/PelangganController.php?aksi=tambah',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#modalTambahPelanggan').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: response.message || 'Data pelanggan berhasil ditambahkan.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Gagal!', 'Terjadi kesalahan: ' + xhr.statusText, 'error');
                }
            });
        });

        $('#pelangganTable').on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            $.getJSON('app/controllers/PelangganController.php?aksi=detail&id=' + id, function(data) {
                $('#edit_id_pelanggan').val(data.id_pelanggan);
                $('#edit_nama').val(data.nama);
                $('#edit_email').val(data.email);
                $('#edit_no_hp').val(data.no_hp);
                $('#edit_alamat').val(data.alamat);
                $('#modalEditPelanggan').modal('show');
            });
        });

        $('#formEditPelanggan').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'app/controllers/PelangganController.php?aksi=edit',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#modalEditPelanggan').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Data pelanggan berhasil diperbarui.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire('Gagal!', response.message || 'Terjadi kesalahan saat mengedit data.', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Gagal!', 'Terjadi kesalahan: ' + xhr.statusText, 'error');
                }
            });
        });

        $('#pelangganTable').on('click', '.btn-hapus', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: 'Data pelanggan akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'app/controllers/PelangganController.php?aksi=hapus',
                        type: 'POST',
                        data: {
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Terhapus!', response.message || 'Data berhasil dihapus.', 'success');
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire('Gagal!', response.message || 'Gagal menghapus data.', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', 'Terjadi kesalahan: ' + xhr.statusText, 'error');
                        }
                    });
                }
            });
        });
    });
</script>