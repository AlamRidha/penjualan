<button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalTambahOngkir">
    Tambah Tarif Kota
</button>

<h2>Data Ongkos Kirim</h2>
<table id="ongkirTable" class="display" style="width:100%">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Kota</th>
            <th>Tarif</th>
            <th>Aksi</th>
        </tr>
    </thead>
</table>

<!-- Modal Tambah Ongkir -->
<div class="modal fade" id="modalTambahOngkir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formTambahOngkir">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Ongkos Kirim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Kota</label>
                        <input type="text" name="nama_kota" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Tarif</label>
                        <input type="number" name="tarif" class="form-control" required step="0.01">
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

<!-- Modal Edit Ongkir -->
<div class="modal fade" id="modalEditOngkir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formEditOngkir">
            <input type="hidden" name="id_ongkir" id="edit_id_ongkir">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Ongkos Kirim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Kota</label>
                        <input type="text" name="nama_kota" id="edit_nama_kota" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Tarif</label>
                        <input type="number" name="tarif" id="edit_tarif" class="form-control" required step="0.01">
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
        // Inisialisasi DataTable
        let ongkirTable = $('#ongkirTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: 'app/controllers/OngkirController.php?aksi=list',
            columns: [{
                    data: 'no'
                },
                {
                    data: 'nama_kota'
                },
                {
                    data: 'tarif'
                },
                {
                    data: 'aksi',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // Handle form tambah ongkir
        $('#formTambahOngkir').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'app/controllers/OngkirController.php?aksi=tambah',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#modalTambahOngkir').modal('hide');
                        $('#formTambahOngkir')[0].reset();
                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        ongkirTable.ajax.reload(null, false);
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Gagal!', 'Terjadi kesalahan: ' + xhr.statusText, 'error');
                }
            });
        });


        $('#modalTambahOngkir').on('show.bs.modal', function() {
            $('#formTambahOngkir')[0].reset();
        });

        // Handle klik tombol edit
        $('#ongkirTable').on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            $.getJSON('app/controllers/OngkirController.php?aksi=get&id=' + id, function(data) {
                $('#edit_id_ongkir').val(data.id_ongkir);
                $('#edit_nama_kota').val(data.nama_kota);
                $('#edit_tarif').val(data.tarif);
                $('#modalEditOngkir').modal('show');
            });
        });

        // Handle form edit ongkir
        $('#formEditOngkir').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'app/controllers/OngkirController.php?aksi=edit',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#modalEditOngkir').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        ongkirTable.ajax.reload(null, false);
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
        $('#ongkirTable').on('click', '.btn-hapus', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: 'Data ongkos kirim akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'app/controllers/OngkirController.php?aksi=hapus',
                        type: 'POST',
                        data: {
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Terhapus!', response.message, 'success');
                                ongkirTable.ajax.reload(null, false);
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
    });
</script>