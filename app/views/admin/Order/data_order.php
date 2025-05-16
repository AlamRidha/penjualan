<?php
require_once base_path('app/config/Auth.php');
require_once base_path('app/controllers/OrderController.php');

if (!isLoggedIn() || !isAdmin()) {
    header("Location: " . base_url('index.php?page=login'));
    exit;
}

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;

// Handle order detail display
$showDetailModal = false;
$orderDetail = null;
$orderItems = null;

if (isset($_GET['action']) && $_GET['action'] === 'view_detail' && isset($_GET['id_order'])) {
    $id_order = $_GET['id_order'];
    $orderDetail = getOrderById($id_order);
    $orderItems = getOrderDetails($id_order);
    $showDetailModal = true;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_order']) && isset($_POST['status'])) {
    $id_order = $_POST['id_order'];
    $status = $_POST['status'];

    $result = updateOrderStatus($id_order, $status);

    if ($result) {
        header("Location: " . base_url('index.php?page=data_order&success=Status pesanan berhasil diperbarui'));
    } else {
        header("Location: " . base_url('index.php?page=data_order&error=Gagal memperbarui status pesanan'));
    }
    exit;
}

// Store success/error messages from redirects
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

$keyword = $_GET['search'] ?? null;
$orders = getAllOrders($keyword);
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
        <h2><i class="fas fa-shopping-cart"></i> Data Pesanan</h2>
    </div>

    <div class="table-responsive">
        <table id="orderTable" class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th width="5%">No</th>
                    <th width="25%">Customer</th>
                    <th width="20%">Tanggal</th>
                    <th width="20%">Status</th>
                    <th width="30%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): $no = 1; ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($order['username']) ?></td>
                            <td class="text-center"><?= date('d-m-Y', strtotime($order['tanggal_order'])) ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $order['status'] === 'selesai' ? 'success' : ($order['status'] === 'diproses' ? 'warning' : 'secondary') ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('index.php?page=data_order&action=view_detail&id_order=' . $order['id_order']) ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            <i class="fas fa-info-circle"></i> Tidak ada data pesanan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detail Pesanan -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= base_url('index.php?page=data_order') ?>">
                <input type="hidden" name="id_order" value="<?= $orderDetail ? $orderDetail['id_order'] : '' ?>">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="orderDetailModalLabel"><i class="fas fa-info-circle"></i> Detail Pesanan</h5>
                    <a href="<?= base_url('index.php?page=data_order') ?>" class="btn-close btn-close-white" aria-label="Close"></a>
                </div>
                <div class="modal-body">
                    <?php if ($orderDetail): ?>
                        <p><strong>Customer:</strong> <?= htmlspecialchars($orderDetail['username']) ?></p>
                        <p><strong>Tanggal:</strong> <?= date('d-m-Y', strtotime($orderDetail['tanggal_order'])) ?></p>

                        <!-- Bukti transfer (jika ada) -->
                        <?php if (!empty($orderDetail['bukti_transfer'])): ?>
                            <div class="mb-3">
                                <label class="form-label d-block"><strong>Bukti Pembayaran:</strong></label>
                                <a href="<?= base_url('uploads/bukti/' . $orderDetail['bukti_transfer']) ?>" target="_blank">
                                    <img src="<?= base_url('uploads/bukti/' . $orderDetail['bukti_transfer']) ?>"
                                        alt="Bukti Pembayaran"
                                        class="img-thumbnail"
                                        style="max-width:220px">
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning py-1">Belum ada bukti pembayaran.</div>
                        <?php endif; ?>


                        <?php if ($orderDetail['status'] !== 'selesai'): ?>
                            <div class="mb-3">
                                <label for="modalStatus" class="form-label">Status Pesanan</label>
                                <select name="status" id="modalStatus" class="form-select w-auto">
                                    <option value="pending" <?= $orderDetail['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="diproses" <?= $orderDetail['status'] === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                    <option value="selesai" <?= $orderDetail['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                </select>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <label class="form-label">Status Pesanan</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-success">Selesai</span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Produk</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 0;
                                    foreach ($orderItems as $item):
                                        $total += floatval($item['subtotal']);
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                                            <td>Rp<?= number_format(intval($item['harga']), 0, ',', '.') ?></td>
                                            <td><?= $item['jumlah'] ?></td>
                                            <td>Rp<?= number_format(intval($item['subtotal']), 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-secondary fw-bold">
                                        <td colspan="3" class="text-end">Total</td>
                                        <td>Rp<?= number_format($total, 0, ',', '.') ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Status</button>
                    <a href="<?= base_url('index.php?page=data_order') ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Tutup</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($showDetailModal): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var orderDetailModal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
            orderDetailModal.show();

            // Menangani redirect saat modal ditutup
            document.querySelectorAll('.btn-close, .btn-secondary').forEach(function(element) {
                element.addEventListener('click', function(e) {
                    e.preventDefault();
                    orderDetailModal.hide();
                    setTimeout(function() {
                        window.location.href = '<?= base_url('index.php?page=data_order') ?>';
                    }, 100);
                });
            });
        });
    </script>
<?php endif; ?>
<!-- <?php if ($showDetailModal): ?>
    <div class="modal-backdrop fade show"></div>
    <script>
        document.body.classList.add('modal-open');
    </script>
<?php endif; ?> -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable
        $('#orderTable').DataTable({
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
                "targets": [4] // Disable sorting for action column
            }],
            "order": [
                [2, 'desc'] // Sort by tanggal_order secara descending by default
            ]
        });

        // Show success/error messages
        <?php if ($success): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= $success ?>',
                timer: 3000,
                timerProgressBar: true
            });
        <?php endif; ?>

        <?php if ($error): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '<?= $error ?>',
                timer: 3000,
                timerProgressBar: true
            });
        <?php endif; ?>

        // Hapus parameter sukses dan error dari URL setelah 3 detik
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success') || urlParams.has('error')) {
            setTimeout(() => {
                if (urlParams.has('success')) urlParams.delete('success');
                if (urlParams.has('error')) urlParams.delete('error');

                // Preserve other parameters like page
                const newUrl = window.location.pathname +
                    (urlParams.toString() ? '?' + urlParams.toString() : '');
                window.history.replaceState({}, document.title, newUrl);
            }, 1000);
        }
    });
</script>