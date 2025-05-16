<?php
require_once base_path('app/config/Auth.php');
require_once base_path('app/controllers/ProductController.php');
require_once base_path('app/controllers/CategoryController.php');

if (!isLoggedIn() || !isAdmin()) {
    header("Location: " . base_url('index.php?page=login'));
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Add product
        if ($_POST['action'] === 'add') {
            if (createProduk($_POST, $_FILES)) {
                $success = "Produk berhasil ditambahkan!";
                header("Location: " . base_url('index.php?page=data_product&success=' . urlencode($success)));
                exit;
            } else {
                $error = "Gagal menambahkan produk.";
            }
        }

        // Update product
        else if ($_POST['action'] === 'edit' && isset($_POST['id_product'])) {
            if (updateProduk($_POST['id_product'], $_POST, $_FILES)) {
                $success = "Produk berhasil diperbarui!";
                header("Location: " . base_url('index.php?page=data_product&success=' . urlencode($success)));
                exit;
            } else {
                $error = "Gagal memperbarui produk.";
            }
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle delete action
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id_product'])) {
        if (deleteProduk($_GET['id_product'])) {
            $success = "Produk berhasil dihapus!";
            header("Location: " . base_url('index.php?page=data_product&success=' . urlencode($success)));
            exit;
        } else {
            $error = "Gagal menghapus produk.";
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
$products = getAllProducts($keyword);
$categories = getAllCategories();
// $products = getAllProducts();
?>

<style>
    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
    }

    /* Fix for overlapping modals */
    .modal-backdrop+.modal-backdrop {
        z-index: 1051;
    }

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
        <h2><i class="fas fa-box"></i> Data Produk</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahProduk">
            <i class="fas fa-plus"></i> Tambah Produk
        </button>
    </div>

    <!-- Table Products -->
    <div class="table-responsive">
        <table id="productTable" class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Nama Produk</th>
                    <th width="10%">Kategori</th>
                    <th width="20%">Deskripsi</th>
                    <th width="10%">Harga</th>
                    <th width="5%">Stok</th>
                    <th width="10%">Gambar</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($product['nama_produk']) ?></td>
                            <td><?= htmlspecialchars($product['nama_category'] ?? 'Tidak ada kategori') ?></td>
                            <td><?= htmlspecialchars($product['deskripsi']) ?></td>
                            <td class="text-end">Rp<?= number_format($product['harga'], 0, ',', '.') ?></td>
                            <td class="text-center"><?= $product['stok'] ?></td>
                            <td class="text-center">
                                <?php if (!empty($product['gambar'])): ?>
                                    <img src="<?= base_url($product['gambar']) ?>" alt="Gambar Produk" class="product-image">
                                <?php else: ?>
                                    <span class="text-muted"><i class="fas fa-image"></i> Tidak ada</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-warning btnEditProduk"
                                    data-id="<?= $product['id_product'] ?>"
                                    data-nama="<?= htmlspecialchars($product['nama_produk']) ?>"
                                    data-deskripsi="<?= htmlspecialchars($product['deskripsi']) ?>"
                                    data-harga="<?= $product['harga'] ?>"
                                    data-stok="<?= $product['stok'] ?>"
                                    data-category="<?= $product['id_category'] ?>"
                                    data-gambar="<?= $product['gambar'] ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btnHapusProduk"
                                    data-id="<?= $product['id_product'] ?>"
                                    data-nama="<?= htmlspecialchars($product['nama_produk']) ?>">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            <i class="fas fa-info-circle"></i> Tidak ada data produk.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
<!-- Modal Tambah Produk -->
<div class="modal fade" id="modalTambahProduk" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= base_url('index.php?page=data_product') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahLabel">
                        <i class="fas fa-plus-circle"></i> Tambah Produk Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" name="nama_produk" required>
                    </div>
                    <!-- Category dropdown -->
                    <div class="mb-3">
                        <label for="id_category" class="form-label">Kategori</label>
                        <select class="form-select" name="id_category" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id_category'] ?>"><?= htmlspecialchars($category['nama_category']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="harga" class="form-label">Harga</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" name="harga" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stok" class="form-label">Stok</label>
                            <input type="number" class="form-control" name="stok" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="gambar" class="form-label">Gambar Produk</label>
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                        <div class="form-text">Format: JPG, PNG, GIF. Maks: 2MB</div>
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

<!-- Modal Edit Produk -->
<div class="modal fade" id="modalEditProduk" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= base_url('index.php?page=data_product') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_product" id="edit_id_product">

                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalEditLabel">
                        <i class="fas fa-edit"></i> Edit Produk
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" name="nama_produk" id="edit_nama_produk" required>
                    </div>
                    <!-- Category dropdown -->
                    <div class="mb-3">
                        <label for="edit_id_category" class="form-label">Kategori</label>
                        <select class="form-select" name="id_category" id="edit_id_category" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id_category'] ?>"><?= htmlspecialchars($category['nama_category']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_harga" class="form-label">Harga</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" name="harga" id="edit_harga" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_stok" class="form-label">Stok</label>
                            <input type="number" class="form-control" name="stok" id="edit_stok" min="0" required>
                        </div>
                    </div>

                    <div class="mb-3" id="current_image_container">
                        <label class="form-label">Gambar Saat Ini</label>
                        <div>
                            <img id="current_image" src="" alt="Gambar Produk" style="max-height: 200px;">
                        </div>
                        <div id="no_image_text" class="text-muted mt-2" style="display: none;">
                            <i class="fas fa-image"></i> Tidak ada gambar
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="gambar" class="form-label">Ganti Gambar (opsional)</label>
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                        <div class="form-text">Biarkan kosong jika tidak ingin mengubah gambar.</div>
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
        $('#productTable').DataTable({
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
                    "targets": [6, 7]
                } // Disable sorting for image and action columns
            ],
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

        // Handle edit product button click
        const editButtons = document.querySelectorAll('.btnEditProduk');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nama = this.getAttribute('data-nama');
                const deskripsi = this.getAttribute('data-deskripsi');
                const harga = this.getAttribute('data-harga');
                const stok = this.getAttribute('data-stok');
                const category = this.getAttribute('data-category');
                const gambar = this.getAttribute('data-gambar');

                // Set values in edit modal
                document.getElementById('edit_id_product').value = id;
                document.getElementById('edit_nama_produk').value = nama;
                document.getElementById('edit_deskripsi').value = deskripsi;
                document.getElementById('edit_harga').value = harga;
                document.getElementById('edit_stok').value = stok;

                // Set category dropdown value
                if (category) {
                    document.getElementById('edit_id_category').value = category;
                }

                // Handle image preview
                const currentImage = document.getElementById('current_image');
                const noImageText = document.getElementById('no_image_text');

                if (gambar && gambar !== '') {
                    currentImage.src = '<?= base_url() ?>' + gambar;
                    currentImage.style.display = 'block';
                    noImageText.style.display = 'none';
                } else {
                    currentImage.style.display = 'none';
                    noImageText.style.display = 'block';
                }

                // Show the edit modal
                const editModal = new bootstrap.Modal(document.getElementById('modalEditProduk'));
                editModal.show();
            });
        });

        // Handle delete confirmation with SweetAlert
        const deleteButtons = document.querySelectorAll('.btnHapusProduk');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const productName = this.getAttribute('data-nama');

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Yakin ingin menghapus produk "${productName}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `<?= base_url('index.php?page=data_product&action=delete&id_product=') ?>${productId}`;
                    }
                });
            });
        });
    });
</script>