<?php
require_once base_path('app/config/Database.php');
require_once base_path('app/helpers/functions.php');
$conn = (new Database())->getConnection();
$id_user = $_SESSION['user']['id'];

$query = "
    SELECT 
        o.id_order,
        o.tanggal_order,
        o.status,
        p.nama_produk,
        od.jumlah,
        od.subtotal
    FROM orders o
    JOIN order_details od ON o.id_order = od.id_order
    JOIN products p ON od.id_product = p.id_product
    WHERE o.id_user = '$id_user'
    ORDER BY o.tanggal_order DESC
";

$result = mysqli_query($conn, $query);
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
?>

<h3>Daftar Pesanan Saya</h3>
<?php if (empty($orders)): ?>
    <div class="alert alert-info">Belum ada pesanan.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark text-center">
                <tr>
                    <th>ID Order</th>
                    <th>Tanggal & Waktu Pemesanan</th>
                    <th>Status</th>
                    <th>Produk</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php $no = 1;
                foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <!-- <td><?= $order['id_order'] ?></td> -->
                        <!-- <td><?= date('d-m-Y H:i', strtotime($order['tanggal_order'])) ?></td> -->
                        <td><?= formatTanggalIndo($order['tanggal_order']) ?></td>
                        <td>
                            <?php
                            $badge = [
                                'pending' => 'warning',
                                'diproses' => 'primary',
                                'selesai' => 'success'
                            ];
                            ?>
                            <span class="badge bg-<?= $badge[$order['status']] ?? 'secondary' ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td>
                            <button
                                class="btn btn-sm btn-info view-detail"
                                data-id="<?= $order['id_order'] ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#detailModal">
                                Lihat Detail
                            </button>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>

<!-- Modal Detail Produk -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetailBody">
            </div>
        </div>
    </div>
</div>


<script>
    document.querySelectorAll('.view-detail').forEach(btn => {
        btn.addEventListener('click', function() {
            const idOrder = this.dataset.id;
            fetch(`app/controllers/OrderDetailController.php?id_order=${idOrder}`)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('modalDetailBody').innerHTML = html;
                });
        });
    });
</script>