<div class="container">
    <h2 class="my-4">Keranjang Belanja</h2>

    <?php if (empty($cart['items'])): ?>
        <div class="alert alert-info">
            Keranjang belanja Anda kosong. <a href="index.php?page=pelanggan/data_product">Mulai belanja</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
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
                                    <?php foreach ($cart['items'] as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($item['foto']): ?>
                                                        <img src="<?= $item['foto'] ?>" alt="<?= $item['nama'] ?>" class="img-thumbnail me-3" width="60">
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><?= $item['nama'] ?></h6>
                                                        <small class="text-muted">Stok: <?= $item['stok'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Rp<?= number_format($item['harga'], 0, ',', '.') ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <button class="btn btn-sm btn-outline-secondary btn-minus" data-id="<?= $item['id'] ?>">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <span class="mx-2 quantity-display"><?= $item['quantity'] ?></span>
                                                    <button class="btn btn-sm btn-outline-secondary btn-plus" data-id="<?= $item['id'] ?>"
                                                        <?= $item['quantity'] >= $item['stok'] ? 'disabled' : '' ?>>
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>Rp<?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-danger remove-item" data-id="<?= $item['id'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ringkasan Belanja</h5>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Harga:</span>
                            <strong>Rp<?= number_format($cart['total'], 0, ',', '.') ?></strong>
                        </div>
                        <button class="btn btn-primary w-100 mt-3" id="checkout-btn" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                            Lanjut ke Pembayaran
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Checkout Modal -->
<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkoutModalLabel">Informasi Pengiriman & Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="checkoutForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                            </div>
                            <div class="mb-3">
                                <label for="telepon" class="form-label">Nomor Telepon</label>
                                <input type="tel" class="form-control" id="telepon" name="telepon" required>
                            </div>
                            <div class="mb-3">
                                <label for="kota" class="form-label">Kota Pengiriman</label>
                                <select class="form-select" id="kota" name="id_ongkir" required>
                                    <option value="">Pilih Kota</option>
                                    <?php
                                    $db = new Database();
                                    $conn = $db->getConnection();
                                    $stmt = $conn->query("SELECT * FROM ongkir");
                                    while ($ongkir = $stmt->fetch_assoc()) {
                                        echo '<option value="' . $ongkir['id_ongkir'] . '" data-tarif="' . $ongkir['tarif'] . '">'
                                            . $ongkir['nama_kota'] . ' (Rp' . number_format($ongkir['tarif'], 0, ',', '.') . ')'
                                            . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat Lengkap</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Ringkasan Pembayaran</h5>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total Produk:</span>
                                        <strong id="summary-products">Rp<?= number_format($cart['total'], 0, ',', '.') ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Ongkos Kirim:</span>
                                        <strong id="summary-shipping">Rp0</strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="fw-bold">Total Pembayaran:</span>
                                        <strong class="text-success" id="summary-total">Rp<?= number_format($cart['total'], 0, ',', '.') ?></strong>
                                    </div>
                                    <div class="mb-3">
                                        <label for="bank" class="form-label">Bank Transfer</label>
                                        <select class="form-select" id="bank" name="bank" required>
                                            <option value="">Pilih Bank</option>
                                            <option value="BCA">BCA</option>
                                            <option value="BRI">BRI</option>
                                            <option value="Mandiri">Mandiri</option>
                                            <option value="BNI">BNI</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="bukti" class="form-label">Bukti Pembayaran</label>
                                        <input type="file" class="form-control" id="bukti" name="bukti" accept="image/*" required>
                                        <small class="text-muted">Upload bukti transfer (format: JPG/PNG)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Proses Pesanan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        // Hitung ongkir saat kota berubah
        document.getElementById('kota').addEventListener('change', function() {
            const tarif = this.options[this.selectedIndex].dataset.tarif || 0;
            const totalProduk = <?= $cart['total'] ?>;
            const totalPembayaran = totalProduk + parseFloat(tarif);

            document.getElementById('summary-shipping').textContent = 'Rp' + parseFloat(tarif).toLocaleString('id-ID');
            document.getElementById('summary-total').textContent = 'Rp' + totalPembayaran.toLocaleString('id-ID');
        });

        // Proses checkout
        document.getElementById("checkoutForm").addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('total_pembelian', document.getElementById('summary-total').textContent.replace('Rp', '').replace(/\./g, ''));

            fetch('index.php?page=cart_action&aksi=checkout', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'index.php?page=riwayat&id=' + data.id_pembelian;
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Terjadi kesalahan saat memproses pesanan', 'error');
                });
        });
    });

    // Update quantity
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.id;
            const quantity = this.value;

            fetch('index.php?page=cart_action&aksi=update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_produk=${productId}&qty=${quantity}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
        });
    });

    document.querySelectorAll('.btn-plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.id;
            const quantityDisplay = this.parentElement.querySelector('.quantity-display');
            const currentQty = parseInt(quantityDisplay.textContent);
            const maxStock = parseInt(this.closest('tr').querySelector('.text-muted').textContent.replace('Stok: ', ''));

            if (currentQty < maxStock) {
                updateCartItem(productId, currentQty + 1);
            }
        });
    });

    // Tombol minus
    document.querySelectorAll('.btn-minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.id;
            const quantityDisplay = this.parentElement.querySelector('.quantity-display');
            const currentQty = parseInt(quantityDisplay.textContent);

            if (currentQty > 1) {
                updateCartItem(productId, currentQty - 1);
            }
        });
    });

    // Hapus item
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function() {
            Swal.fire({
                title: 'Hapus Produk?',
                text: "Apakah Anda yakin ingin menghapus produk ini dari keranjang?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const productId = btn.dataset.id;
                    fetch(`index.php?page=cart_action&aksi=hapus&id=${productId}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'Produk telah dihapus dari keranjang.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => location.reload());
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        });
                }
            });

        });
    });

    // Fungsi update item
    function updateCartItem(productId, quantity) {
        fetch('index.php?page=cart_action&aksi=update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id_produk=${productId}&qty=${quantity}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
    }

    // Checkout form
    document.getElementById("checkoutForm").addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('index.php?page=cart_action&aksi=checkout', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Redirect ke halaman konfirmasi
                    window.location.href = `index.php?page=order_confirmation&id=${data.transaksi_id}`;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses pesanan');
            });
    });
</script>