<?php
require_once base_path('app/config/Auth.php');
require_once base_path('app/controllers/UserController.php');

if (!isLoggedIn() || !isAdmin()) {
    header("Location: " . base_url('index.php?page=login'));
    exit;
}

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Add user
        if ($_POST['action'] === 'add') {
            if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['Role'])) {
                $error = "Semua field harus diisi!";
            } else {
                if (createUser($_POST)) {
                    $success = "Pengguna berhasil ditambahkan!";
                } else {
                    $error = "Gagal menambahkan pengguna.";
                }
            }
        }

        // Update user
        if ($_POST['action'] === 'edit') {
            $id = $_POST['id_user'];
            if (empty($id) || empty($_POST['username']) || empty($_POST['Role'])) {
                $error = "Username dan role harus diisi!";
            } else {
                if (updateUser($id, $_POST)) {
                    $success = "Pengguna berhasil diperbarui!";
                } else {
                    $error = "Gagal memperbarui pengguna.";
                }
            }
        }
    }
}

// Handle delete action via GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id_user'])) {
    $id = $_GET['id_user'];
    if (deleteUser($id)) {
        $success = "Pengguna berhasil dihapus!";
    } else {
        $error = "Gagal menghapus pengguna. Pastikan pengguna tidak digunakan oleh transaksi manapun.";
    }
    // Redirect untuk menghindari reload delete
    header("Location: " . base_url('index.php?page=data_user'));
    exit;
}

// Get all users (tanpa keyword karena akan menggunakan search dari DataTable)
$users = getAllUsers();
?>

<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users"></i> Data Pengguna</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            <i class="fas fa-plus"></i> Tambah Pengguna
        </button>
    </div>

    <div class="table-responsive">
        <table id="userTable" class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td class="text-center">
                                <span class="badge <?= $user['Role'] === 'admin' ? 'bg-primary' : 'bg-secondary' ?>">
                                    <?= htmlspecialchars($user['Role']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-warning btnEditUser"
                                    data-id="<?= $user['id_user'] ?>"
                                    data-username="<?= htmlspecialchars($user['username']) ?>"
                                    data-role="<?= $user['Role'] ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btnDeleteUser"
                                    data-id="<?= $user['id_user'] ?>"
                                    data-username="<?= htmlspecialchars($user['username']) ?>">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">Tidak ada data pengguna.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-labelledby="modalTambahUserLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="<?= base_url('index.php?page=data_user') ?>" id="formTambahUser">
            <input type="hidden" name="action" value="add">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahUserLabel"><i class="fas fa-user-plus"></i> Tambah Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="Role" class="form-select" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin">Admin</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-labelledby="modalEditUserLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="<?= base_url('index.php?page=data_user') ?>" id="formEditUser">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id_user" id="edit_id_user">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditUserLabel"><i class="fas fa-edit"></i> Edit Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" id="edit_username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password (Kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="Role" id="edit_role" class="form-select" required>
                        <option value="admin">Admin</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- SweetAlert2 dan jQuery (jika belum ada di layout) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Inisialisasi DataTables dengan fitur pencarian
        if (typeof $.fn.DataTable !== 'undefined') {
            $('#userTable').DataTable({
                language: {
                    "emptyTable": "Tidak ada data yang tersedia pada tabel ini",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                    "infoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                    "lengthMenu": "Tampilkan _MENU_ entri",
                    "loadingRecords": "Sedang memuat...",
                    "processing": "Sedang memproses...",
                    "search": "Cari:",
                    "zeroRecords": "Tidak ditemukan data yang sesuai",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    },
                    "aria": {
                        "sortAscending": ": aktifkan untuk mengurutkan kolom ke atas",
                        "sortDescending": ": aktifkan untuk mengurutkan kolom ke bawah"
                    }
                },
                responsive: true
            });
        }

        // Tampilkan SweetAlert jika ada pesan sukses atau error
        <?php if ($success): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= addslashes($success) ?>',
                timer: 3000,
                timerProgressBar: true
            });
            setTimeout(function() {
                const url = new URL(window.location.href);
                url.searchParams.delete('success');
                window.history.replaceState({}, document.title, url);

            }, 1000);
        <?php elseif ($error): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '<?= addslashes($error) ?>',
                timer: 3000,
                timerProgressBar: true
            });
            setTimeout(function() {
                const url = new URL(window.location.href);
                url.searchParams.delete('error');
                window.history.replaceState({}, document.title, url);
            }, 1000);
        <?php endif; ?>

        // Isi modal edit user
        document.querySelectorAll('.btnEditUser').forEach(button => {
            button.addEventListener('click', () => {
                const modal = new bootstrap.Modal(document.getElementById('modalEditUser'));
                document.getElementById('edit_id_user').value = button.dataset.id;
                document.getElementById('edit_username').value = button.dataset.username;
                document.getElementById('edit_role').value = button.dataset.role;
                modal.show();
            });
        });

        // Konfirmasi hapus dengan SweetAlert
        document.querySelectorAll('.btnDeleteUser').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                const username = button.dataset.username;

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Yakin ingin menghapus pengguna "${username}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {

                        window.location.href = `<?= base_url('index.php?page=data_user&action=delete&id_user=') ?>${id}`;
                    }
                });
            });
        });

        // Form Submit dengan SweetAlert
        document.getElementById('formTambahUser').addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                // Formulir tidak valid, biarkan validasi HTML5 berjalan
                return;
            }

            e.preventDefault();

            Swal.fire({
                title: 'Simpan Data?',
                text: "Apakah data pengguna baru sudah benar?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        document.getElementById('formEditUser').addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                // Formulir tidak valid, biarkan validasi HTML5 berjalan
                return;
            }

            e.preventDefault();

            Swal.fire({
                title: 'Perbarui Data?',
                text: "Simpan perubahan data pengguna?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Perbarui!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
</script>