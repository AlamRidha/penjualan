<?php
require_once '../config/Database.php';
require_once '../helpers/functions.php';

$db = new Database();
$conn = $db->getConnection();

$perPage = 8;

$action = $_GET['action'] ?? '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$keyword = $_GET['keyword'] ?? '';
$offset = ($page - 1) * $perPage;

function escape($str)
{
    global $conn;
    return mysqli_real_escape_string($conn, $str);
}

// Fetch list produk
// if ($action === '') {
if ($action === 'list') {
    $where = $keyword ? "WHERE p.nama_produk LIKE '%" . escape($keyword) . "%'" : '';
    $query = "SELECT p.*, c.nama_category 
              FROM products p
              LEFT JOIN categories c ON p.id_category = c.id_category
              $where
              LIMIT $perPage OFFSET $offset";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) === 0) {
        echo '<div id="no-data" class="col-12">Data tidak ditemukan</div>';
    } else {
        while ($row = mysqli_fetch_assoc($result)) {
?>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm">
                    <img src="<?= base_url($row['gambar']) ?>" class="card-img-top" style="height: 180px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($row['nama_produk']) ?></h5>
                        <p class="card-text text-muted"><?= htmlspecialchars($row['nama_category']) ?></p>
                        <p class="fw-bold text-primary">Rp <?= number_format($row['harga'], 0, ',', '.') ?></p>
                        <button class="btn btn-sm btn-outline-primary btn-detail" data-id="<?= $row['id_product'] ?>">Lihat Detail</button>
                    </div>
                </div>
            </div>
        <?php
        }
    }
    exit;
}

// At the top of your file
error_log("ProductAjaxController - Action: " . ($_GET['action'] ?? 'none') . ", Page: " . ($_GET['page'] ?? 'none'));


// Pagination
if ($action === 'pagination') {
    $where = $keyword ? "WHERE nama_produk LIKE '%" . escape($keyword) . "%'" : '';
    $total = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM products $where"))[0];
    $totalPages = ceil($total / $perPage);

    // Tetap tampilkan pagination meskipun tidak ada produk
    if ($totalPages == 0) $totalPages = 1; // untuk tetap render page 1

    // Tombol Previous
    $prevDisabled = ($page <= 1) ? 'disabled' : '';
    $prevPage = ($page > 1) ? ($page - 1) : 1;
    echo '<li class="page-item ' . $prevDisabled . '">
    <a class="page-link" href="#" data-page="' . $prevPage . '">Previous</a>
</li>';

    // Nomor halaman
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = ($i == $page) ? 'active' : '';
        echo '<li class="page-item ' . $active . '">
        <a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>
    </li>';
    }

    // Tombol Next
    $nextDisabled = ($page >= $totalPages) ? 'disabled' : '';
    $nextPage = ($page < $totalPages) ? ($page + 1) : $totalPages;
    echo '<li class="page-item ' . $nextDisabled . '">
    <a class="page-link" href="#" data-page="' . $nextPage . '">Next</a>
</li>';

    // if ($totalPages > 0) {
    //     // Tombol Previous
    //     if ($page > 1) {
    //         echo '<li class="page-item">
    //             <a class="page-link" href="#" data-page="' . ($page - 1) . '">Previous</a>
    //         </li>';
    //     }

    //     // Nomor halaman
    //     for ($i = 1; $i <= $totalPages; $i++) {
    //         $active = ($i == $page) ? 'active' : '';
    //         echo '<li class="page-item ' . $active . '">
    //             <a class="page-link" href="#" data-page="' . $i . '">'  . $i . '</a>
    //         </li>';
    //     }

    //     // Tombol Next
    //     if ($page < $totalPages) {
    //         echo '<li class="page-item">
    //             <a class="page-link" href="#" data-page="' . ($page + 1) . '">Next</a>
    //         </li>';
    //     }
    // } else {
    //     // Tidak ada data sama sekali (mungkin karena keyword tidak cocok)
    //     echo '<li class="page-item disabled"><span class="page-link">Tidak ada halaman</span></li>';
    // }

    exit;
}



// Detail modal
if ($action === 'detail' && isset($_GET['id'])) {
    $id = escape($_GET['id']);
    $query = "SELECT p.*, c.nama_category 
              FROM products p
              LEFT JOIN categories c ON p.id_category = c.id_category
              WHERE p.id_product = '$id' LIMIT 1";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    if ($row) {
        ?>
        <div class="row">
            <div class="col-md-6">
                <img src="<?= base_url($row['gambar']) ?>" class="img-fluid rounded shadow">
            </div>
            <div class="col-md-6">
                <h4><?= htmlspecialchars($row['nama_produk']) ?></h4>
                <p class="text-muted"><?= htmlspecialchars($row['nama_category']) ?></p>
                <p><?= htmlspecialchars($row['deskripsi']) ?></p>
                <p class="fw-bold text-primary fs-5">Rp <?= number_format($row['harga'], 0, ',', '.') ?></p>
                <p>Stok: <?= $row['stok'] ?></p>

                <?php if ($row['stok'] > 0) : ?>
                    <div class="mb-2">
                        <label for="qty">Jumlah:</label>
                        <input type="number" class="form-control" id="qty" value="1" min="1" max="<?= $row['stok'] ?>">
                    </div>
                    <button class="btn btn-success w-100 btn-add-to-cart"
                        data-id="<?= $row['id_product'] ?>"
                        data-nama="<?= htmlspecialchars($row['nama_produk']) ?>"
                        data-harga="<?= $row['harga'] ?>">
                        + Tambah ke Keranjang
                    </button>
                <?php else : ?>
                    <div class="alert alert-danger">Stok Habis</div>
                <?php endif; ?>
            </div>
        </div>
<?php
    }
    exit;
}
