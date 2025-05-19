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

        // Proses checkout (tidak berubah)
        document.getElementById("checkoutForm").addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...';

            const formData = new FormData(this);
            const totalPembayaran = document.getElementById('summary-total').textContent.replace('Rp', '').replace(/\./g, '');
            formData.append('total_pembelian', totalPembayaran);

            fetch('index.php?page=cart_action&aksi=checkout', {
                    method: 'POST',
                    body: formData
                })
                .then(async response => {
                    try {
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Terjadi kesalahan');
                        return data;
                    } catch (error) {
                        const text = await response.text();
                        console.error("Response:", text);
                        throw new Error('Respons tidak valid: ' + text.substring(0, 100));
                    }
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Sukses!',
                            text: 'Pesanan Berhasil Dibuat',
                            icon: 'success'
                        }).then(() => {
                            window.location.href = 'index.php?page=pelanggan/data_product';
                        });
                    } else {
                        throw new Error(data.message || 'Gagal memproses pesanan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', error.message, 'error');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Proses Pesanan';
                });
        });

        // Event listeners untuk tombol +/-
        document.querySelectorAll('.btn-plus').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const productId = row.querySelector('.remove-item').dataset.id;
                const quantityDisplay = row.querySelector('.quantity-display');
                const currentQty = parseInt(quantityDisplay.textContent);
                const maxStock = parseInt(row.querySelector('.text-muted').textContent.replace('Stok: ', ''));

                if (currentQty < maxStock) {
                    updateCartItem(productId, currentQty + 1);
                } else {
                    Swal.fire('Info', 'Jumlah tidak boleh melebihi stok', 'info');
                }
            });
        });

        document.querySelectorAll('.btn-minus').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const productId = row.querySelector('.remove-item').dataset.id;
                const quantityDisplay = row.querySelector('.quantity-display');
                const currentQty = parseInt(quantityDisplay.textContent);

                if (currentQty > 1) {
                    updateCartItem(productId, currentQty - 1);
                }
            });
        });

        // Hapus item (tidak berubah)
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.dataset.id;
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
                        removeCartItem(productId);
                    }
                });
            });
        });

        // Fungsi untuk update item
        async function updateCartItem(productId, quantity) {
            try {
                const response = await fetch('index.php?page=cart_action&aksi=update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_produk=${productId}&qty=${quantity}`
                });

                const data = await parseResponse(response);

                if (data.success) {
                    // Perbarui tampilan tanpa reload
                    // updateCartUI(data.cart);
                    updateCartUI(data.cart, productId);
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', error.message, 'error');
                location.reload(); // Fallback jika update UI gagal
            }
        }

        // Fungsi untuk hapus item
        async function removeCartItem(productId) {
            try {
                const response = await fetch(`index.php?page=cart_action&aksi=hapus&id=${productId}`);
                const data = await parseResponse(response);

                if (data.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: data.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', error.message, 'error');
            }
        }

        // Helper function untuk parse response
        async function parseResponse(response) {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            }
            const text = await response.text();
            console.error("Non-JSON response:", text);
            throw new Error('Respons server tidak valid');
        }


        function updateCartUI(cartData, updatedProductId) {
            // Update quantity display
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const rowProductId = row.querySelector('.remove-item').dataset.id;
                const item = cartData.items.find(i => i.id == rowProductId);

                if (item) {
                    // Update quantity display
                    const quantityDisplay = row.querySelector('.quantity-display');
                    quantityDisplay.textContent = item.quantity;

                    // Update subtotal
                    const subtotalCell = row.querySelector('td:nth-child(4)');
                    subtotalCell.textContent = 'Rp' + numberFormat(item.subtotal);

                    // Update status tombol plus
                    const plusBtn = row.querySelector('.btn-plus');
                    plusBtn.disabled = item.quantity >= item.stok;
                }
            });

            // Update total
            const totalElement = document.querySelector('.card-body strong');
            totalElement.textContent = 'Rp' + numberFormat(cartData.total);

            // Update summary jika ada di modal checkout
            const summaryProducts = document.getElementById('summary-products');
            if (summaryProducts) {
                summaryProducts.textContent = 'Rp' + numberFormat(cartData.total);
                const shipping = document.getElementById('summary-shipping').textContent.replace('Rp', '').replace(/\./g, '') || 0;
                const totalPayment = cartData.total + parseFloat(shipping);
                document.getElementById('summary-total').textContent = 'Rp' + numberFormat(totalPayment);
            }
        }

        function numberFormat(number) {
            return parseFloat(number).toLocaleString('id-ID');
        }
        // Fungsi untuk update tampilan keranjang
        // function updateCartUI(cartData) {
        //     // Update quantity dan subtotal di setiap row
        //     document.querySelectorAll('tr').forEach(row => {
        //         const productId = row.querySelector('.remove-item')?.dataset.id;
        //         if (!productId) return;

        //         // Cari item berdasarkan ID
        //         const item = cartData.items.find(i => i.id == productId);
        //         if (item) {
        //             row.querySelector('.quantity-display').textContent = item.quantity;
        //             row.querySelector('td:nth-child(4)').textContent =
        //                 'Rp' + item.subtotal.toLocaleString('id-ID');
        //         }
        //     });

        //     // Update total harga di ringkasan
        //     document.querySelector('.card-body strong').textContent =
        //         'Rp' + cartData.total.toLocaleString('id-ID');

        //     // Update juga summary modal checkout jika modal sudah terbuka
        //     const summaryProducts = document.getElementById('summary-products');
        //     const summaryTotal = document.getElementById('summary-total');
        //     const summaryShipping = document.getElementById('summary-shipping');

        //     if (summaryProducts && summaryTotal && summaryShipping) {
        //         summaryProducts.textContent = 'Rp' + cartData.total.toLocaleString('id-ID');

        //         const tarifText = summaryShipping.textContent.replace('Rp', '').replace(/\./g, '') || '0';
        //         const tarif = parseInt(tarifText);
        //         const newTotal = cartData.total + tarif;

        //         summaryTotal.textContent = 'Rp' + newTotal.toLocaleString('id-ID');
        //     }
        // }

    });
</script>