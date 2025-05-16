<?php
require_once base_path('app/config/Auth.php');
require_once base_path('app/controllers/CategoryController.php');

if (!isLoggedIn() || !isAdmin()) {
    header("Location: " . base_url('index.php?page=login'));
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Add category
        if ($_POST['action'] === 'add') {
            if (createCategory($_POST)) {
                $success = "Kategori berhasil ditambahkan!";
                header("Location: " . base_url('index.php?page=data_category&success=' . urlencode($success)));
                exit;
            } else {
                $error = "Gagal menambahkan kategori.";
            }
        }

        // Update category
        else if ($_POST['action'] === 'edit' && isset($_POST['id_category'])) {
            if (updateCategory($_POST['id_category'], $_POST)) {
                $success = "Kategori berhasil diperbarui!";
                header("Location: " . base_url('index.php?page=data_category&success=' . urlencode($success)));
                exit;
            } else {
                $error = "Gagal memperbarui kategori.";
            }
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle delete action
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id_category'])) {
        if (deleteCategory($_GET['id_category'])) {
            $success = "Kategori berhasil dihapus!";
            header("Location: " . base_url('index.php?page=data_category&success=' . urlencode($success)));
            exit;
        } else {
            $error = "Gagal menghapus kategori. Pastikan kategori tidak digunakan oleh produk manapun.";
        }
    }

    // Store success/error messages from redirects
    if (isset($_GET['success'])) {
        $success = $_GET['success'];
    }

    if (isset($_GET['error'])) {
        $error = $_GET['error'];
    }
}

// Ambil data dari controller
$keyword = $_GET['search'] ?? null;
$categories = getAllCategories();
?>

<style>
    /* Custom styling for DataTables */
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length {
        margin-bottom: 15px;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 15px;
    }
</style>

<div class="container mt-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tags"></i> Data Kategori</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
            <i class="fas fa-plus"></i> Tambah Kategori
        </button>
    </div>

    <!-- Table Categories -->
    <div class="table-responsive">
        <table id="categoryTable" class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th width="5%">No</th>
                    <th width="65%">Nama Kategori</th>
                    <th width="30%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($categories)): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($category['nama_category']) ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-warning btnEditKategori"
                                    data-id="<?= $category['id_category'] ?>"
                                    data-nama="<?= htmlspecialchars($category['nama_category']) ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btnHapusKategori"
                                    data-id="<?= $category['id_category'] ?>"
                                    data-nama="<?= htmlspecialchars($category['nama_category']) ?>">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted">
                            <i class="fas fa-info-circle"></i> Tidak ada data kategori.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('index.php?page=data_category') ?>" method="POST">
                <input type="hidden" name="action" value="add">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahLabel">
                        <i class="fas fa-plus-circle"></i> Tambah Kategori Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_category" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" name="nama_category" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kategori -->
<div class="modal fade" id="modalEditKategori" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('index.php?page=data_category') ?>" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_category" id="edit_id_category">

                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalEditLabel">
                        <i class="fas fa-edit"></i> Edit Kategori
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nama_category" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" name="nama_category" id="edit_nama_category" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable
        $('#categoryTable').DataTable({
            "language": {
                "lengthMenu": "Tampilkan _MENU_ Data",
                "zeroRecords": "Tidak ada data yang cocok",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(difilter dari _MAX_ total data)",
                "search": "Cari:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            },
            "responsive": true,
            "columnDefs": [{
                "orderable": false,
                "targets": [2]
            }], // Disable sorting for action column
            "order": [
                [0, 'asc']
            ] // Sort by first column (No) by default
        });

        // Show success message with SweetAlert
        <?php if ($success): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= $success ?>',
                timer: 3000,
                timerProgressBar: true
            });
        <?php endif; ?>

        // Show error message with SweetAlert
        <?php if ($error): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '<?= $error ?>',
                timer: 3000,
                timerProgressBar: true
            });
        <?php endif; ?>

        // Hapus parameter sukses dari URL setelah 3 detik
        <?php if ($success): ?>
            setTimeout(function() {
                const url = new URL(window.location.href);
                url.searchParams.delete('success');
                window.history.replaceState({}, document.title, url);
            }, 1000);
        <?php endif; ?>

        // Hapus parameter error dari URL setelah 3 detik (jika ada)
        <?php if ($error): ?>
            setTimeout(function() {
                const url = new URL(window.location.href);
                url.searchParams.delete('error');
                window.history.replaceState({}, document.title, url);
            }, 1000);
        <?php endif; ?>

        // Handle edit category button click
        const editButtons = document.querySelectorAll('.btnEditKategori');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nama = this.getAttribute('data-nama');

                // Set values in edit modal
                document.getElementById('edit_id_category').value = id;
                document.getElementById('edit_nama_category').value = nama;

                // Show the edit modal
                const editModal = new bootstrap.Modal(document.getElementById('modalEditKategori'));
                editModal.show();
            });
        });

        // Handle delete confirmation with SweetAlert
        const deleteButtons = document.querySelectorAll('.btnHapusKategori');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-id');
                const categoryName = this.getAttribute('data-nama');

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Yakin ingin menghapus kategori "${categoryName}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `<?= base_url('index.php?page=data_category&action=delete&id_category=') ?>${categoryId}`;
                    }
                });
            });
        });
    });
</script>