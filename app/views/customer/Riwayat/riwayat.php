<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once base_path('app/config/Database.php');
$conn     = (new Database())->getConnection();
$id_user  = $_SESSION['user']['id'];

/* ---------- helper tanggal lokal ---------- */
if (!function_exists('formatTanggalIndo')) {
    function formatTanggalIndo($datetimeStr)
    {
        $fmt = new IntlDateFormatter(
            'id_ID',               // locale
            IntlDateFormatter::LONG,
            IntlDateFormatter::SHORT,
            'Asia/Jakarta',
            IntlDateFormatter::GREGORIAN,
            "EEEE, d MMMM yyyy '•' HH:mm"
        );
        return $fmt->format(strtotime($datetimeStr));
    }
}

/* ---------- ambil pesanan BERSTATUS selesai ---------- */
$sql = "
    SELECT  o.id_order,
            o.tanggal_order,
            o.status,
            SUM(od.subtotal)  AS total_bayar
    FROM    orders o
    JOIN    order_details od ON o.id_order = od.id_order
    WHERE   o.id_user = ?
      AND   o.status   = 'selesai'
    GROUP BY o.id_order
    ORDER BY o.tanggal_order DESC
";

$stmt   = $conn->prepare($sql);
$stmt->bind_param('i', $id_user);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<h3 class="mb-4">Riwayat Pesanan</h3>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">Belum ada pesanan yang selesai.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Total Bayar</th>
                    <th>Status</th>
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach ($orders as $row): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= formatTanggalIndo($row['tanggal_order']) ?></td>
                        <td>Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?></td>
                        <td class="text-center">
                            <span class="badge bg-success">Selesai</span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info view-detail"
                                data-id="<?= $row['id_order'] ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#detailModal">
                                Lihat&nbsp;Detail
                            </button>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>


<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detail Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetailBody">
                <div class="text-center text-muted">Memuat&nbsp;...</div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.view-detail').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            fetch(`app/controllers/OrderDetailController.php?id_order=${id}`)
                .then(r => r.text())
                .then(html => {
                    document.getElementById('modalDetailBody').innerHTML = html;
                });
        });
    });
</script>