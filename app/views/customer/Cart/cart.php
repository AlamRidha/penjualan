<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'app/config/Database.php';
require_once 'app/helpers/functions.php';

$cart = $_SESSION['cart'] ?? [];

// Hapus item dari keranjang
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $id = $_GET['id'];
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
        header('Location:' . base_url('index.php?page=cart'));
        exit;
    }
}

$total = 0;
foreach ($cart as $item) {
    $total += $item['harga'] * $item['jumlah'];
}
?>

<div class="container py-4">
    <h2>Keranjang Belanja</h2>

    <?php if (empty($cart)) : ?>
        <p>Keranjang kosong.</p>
    <?php else : ?>
        <!-- <?php echo json_encode($cart) ?> -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart as $id => $item) : ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                        <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                        <td><?= $item['jumlah'] ?></td>
                        <td>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></td>
                        <td>
                            <a href="index.php?page=cart&action=remove&id=<?= $id ?>"
                                class="btn btn-sm btn-danger btn-remove-cart"
                                data-id="<?= $id ?>">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                    <td colspan="2"><strong>Rp <?= number_format($total, 0, ',', '.') ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <?php if (!empty($cart)) : ?>
            <form action="index.php?page=checkout" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="total" value="<?= $total ?>">
                <div class="mb-3">
                    <label for="metode">Metode Pembayaran:</label>
                    <select name="metode" class="form-select" required>
                        <option value="">-- Pilih Metode --</option>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="QRIS">QRIS</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="bukti">Upload Bukti Transfer:</label>
                    <input type="file" name="bukti" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Checkout</button>
            </form>
        <?php endif; ?>

    <?php endif; ?>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const buttons = document.querySelectorAll('.btn-remove-cart');

        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const href = this.getAttribute('href');

                Swal.fire({
                    title: 'Apakah kamu yakin?',
                    text: "Produk akan dihapus dari keranjang!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Produk telah dihapus dari keranjang.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = href;
                        });
                    }
                });
            });
        });
    });
</script>

<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Checkout berhasil!',
            text: 'Pesanan kamu sedang diproses.',
            showConfirmButton: true
        });
    </script>
<?php endif; ?>