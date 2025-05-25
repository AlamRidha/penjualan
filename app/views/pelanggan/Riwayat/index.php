<div class="container mt-4">
    <h2 class="mb-4 text-primary fw-bold">
        <i class="fas fa-history me-2"></i>Riwayat Pembelian Saya
    </h2>

    <?php
    $db = new Database();
    $conn = $db->getConnection();

    // Validasi session user
    if (!isset($_SESSION['pelanggan']['id_pelanggan'])) {
        echo '<div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>Anda harus login untuk melihat riwayat pembelian
              </div>';
        return;
    }
    $userId = $_SESSION['pelanggan']['id_pelanggan'];

    // Query untuk mendapatkan semua pembelian user
    $query = "SELECT p.id_pembelian, p.tanggal_pembelian, p.total_pembelian, 
                     p.status_pembelian, p.resi_pengiriman, p.alamat_pengiriman,
                     o.nama_kota, o.tarif,
                     py.bukti_pembayaran, py.bank, py.tanggal_pembayaran, py.nama as nama_pemilik_rekening
              FROM pembelian p
              LEFT JOIN ongkir o ON p.id_ongkir = o.id_ongkir
              LEFT JOIN pembayaran py ON p.id_pembelian = py.id_pembelian
              WHERE p.id_pelanggan = ?
              ORDER BY p.tanggal_pembelian DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo '<div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Anda belum memiliki riwayat pembelian. 
                <a href="index.php?page=pelanggan/data_product" class="alert-link">Mulai berbelanja</a>
              </div>';
        return;
    }
    ?>

    <div class="row">
        <?php while ($pembelian = $result->fetch_assoc()): ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            Pesanan <?php echo date('d M Y', strtotime($pembelian['tanggal_pembelian'])); ?>
                            <!-- #<?= $pembelian['id_pembelian'] ?> -->
                        </h5>
                        <span class="badge rounded-pill bg-<?=
                                                            $pembelian['status_pembelian'] == 'pending' ? 'warning' : ($pembelian['status_pembelian'] == 'dibayar' ? 'info' : ($pembelian['status_pembelian'] == 'dikirim' ? 'primary' : ($pembelian['status_pembelian'] == 'selesai' ? 'success' : 'danger')))
                                                            ?>">
                            <?= ucfirst($pembelian['status_pembelian']) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <i class="far fa-calendar-alt text-secondary me-1"></i>
                                <small><?= date('d M Y H:i', strtotime($pembelian['tanggal_pembelian'])) ?></small>
                            </div>
                            <div>
                                <span class="fw-bold text-dark">Rp<?= number_format($pembelian['total_pembelian'], 0, ',', '.') ?></span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#detailPembelian<?= $pembelian['id_pembelian'] ?>">
                                <i class="fas fa-eye me-1"></i> Detail
                            </button>


                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Detail Pembelian -->
            <div class="modal fade" id="detailPembelian<?= $pembelian['id_pembelian'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                Detail Pembelian #<?= $pembelian['id_pembelian'] ?>
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-truck me-2"></i>Informasi Pengiriman</h6>
                                        </div>
                                        <div class="card-body">
                                            <p>
                                                <strong>Alamat:</strong><br>
                                                <?= nl2br($pembelian['alamat_pengiriman']) ?>
                                            </p>
                                            <p>
                                                <strong>Kota Tujuan:</strong><br>
                                                <?= $pembelian['nama_kota'] ?>
                                            </p>
                                            <?php if ($pembelian['status_pembelian'] != 'pending' && !empty($pembelian['resi_pengiriman'])): ?>
                                                <p>
                                                    <strong>Nomor Resi:</strong><br>
                                                    <?= $pembelian['resi_pengiriman'] ?>
                                                    <?php if ($pembelian['status_pembelian'] == 'dikirim'): ?>
                                                        <br>
                                                        <small class="text-success">
                                                            <i class="fas fa-truck me-1"></i> Sedang dikirim
                                                        </small>
                                                    <?php elseif ($pembelian['status_pembelian'] == 'selesai'): ?>
                                                        <br>
                                                        <small class="text-success">
                                                            <i class="fas fa-check-circle me-1"></i> Telah diterima
                                                        </small>
                                                    <?php endif; ?>
                                                </p>
                                            <?php elseif ($pembelian['status_pembelian'] != 'pending'): ?>
                                                <p class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i> Nomor resi belum tersedia
                                                </p>
                                            <?php else: ?>
                                                <p class="text-muted">
                                                    <i class="fas fa-clock me-1"></i> Menunggu konfirmasi pembayaran
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Informasi Pembayaran</h6>
                                        </div>
                                        <div class="card-body">
                                            <p>
                                                <strong>Bank:</strong> <?= $pembelian['bank'] ?><br>
                                                <strong>Nama Pemilik Rekening:</strong> <?= $pembelian['nama_pemilik_rekening'] ?><br>
                                                <strong>Tanggal Transfer:</strong> <?= date('d M Y H:i', strtotime($pembelian['tanggal_pembayaran'])) ?>
                                            </p>
                                            <?php if ($pembelian['bukti_pembayaran']): ?>
                                                <div class="text-center mt-3">
                                                    <img src="./uploads/bukti/<?= $pembelian['bukti_pembayaran'] ?>"
                                                        class="img-fluid rounded border" style="max-height: 150px;">
                                                    <p class="small text-muted mt-2">Klik gambar untuk memperbesar</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h5 class="mb-3"><i class="fas fa-boxes me-2"></i>Produk Dibeli</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Produk</th>
                                            <th>Harga</th>
                                            <th>Jumlah</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmtProduk = $conn->prepare("SELECT pp.*, pr.foto_produk
                                                                     FROM pembelian_produk pp
                                                                     LEFT JOIN produk pr ON pp.id_produk = pr.id_produk
                                                                     WHERE pp.id_pembelian = ?");
                                        $stmtProduk->bind_param("i", $pembelian['id_pembelian']);
                                        $stmtProduk->execute();
                                        $produkResult = $stmtProduk->get_result();

                                        while ($produk = $produkResult->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($produk['foto_produk']): ?>
                                                            <img src="<?= $produk['foto_produk'] ?>"
                                                                alt="<?= $produk['nama_produk'] ?>"
                                                                class="img-thumbnail me-3" width="60">
                                                        <?php endif; ?>
                                                        <?= $produk['nama_produk'] ?>
                                                    </div>
                                                </td>
                                                <td>Rp<?= number_format($produk['harga'], 0, ',', '.') ?></td>
                                                <td><?= $produk['jumlah'] ?></td>
                                                <td>Rp<?= number_format($produk['sub_harga'], 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                    <tfoot class="fw-bold">
                                        <tr>
                                            <td colspan="3">Total Produk</td>
                                            <td>Rp<?= number_format($pembelian['total_pembelian'] - $pembelian['tarif'], 0, ',', '.') ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3">Ongkos Kirim</td>
                                            <td>Rp<?= number_format($pembelian['tarif'], 0, ',', '.') ?></td>
                                        </tr>
                                        <tr class="table-active">
                                            <td colspan="3">Total Pembayaran</td>
                                            <td>Rp<?= number_format($pembelian['total_pembelian'], 0, ',', '.') ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>

                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<style>
    .card {
        border-radius: 10px;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .badge {
        font-size: 0.85em;
        padding: 0.5em 0.75em;
    }

    .img-thumbnail {
        max-height: 60px;
        object-fit: cover;
    }

    .modal-content {
        border-radius: 10px;
        overflow: hidden;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalImages = document.querySelectorAll('.modal-body img');

        modalImages.forEach(img => {
            img.style.cursor = 'zoom-in';
            img.addEventListener('click', function() {
                if (this.style.maxHeight === '150px') {
                    this.style.maxHeight = 'none';
                    this.style.cursor = 'zoom-out';
                } else {
                    this.style.maxHeight = '150px';
                    this.style.cursor = 'zoom-in';
                }
            });
        });
    });
</script>