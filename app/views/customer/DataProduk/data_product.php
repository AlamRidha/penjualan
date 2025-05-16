<style>
    h2 {
        font-weight: bold;
        color: #333;
    }

    .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
    }

    .card-text {
        font-size: 0.9rem;
        color: #666;
    }

    .card .btn-detail {
        transition: all 0.2s ease;
    }

    .card .btn-detail:hover {
        background-color: #0d6efd;
        color: white;
    }

    #product-list .card {
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    #product-list .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }

    #no-data {
        font-size: 1.2rem;
        text-align: center;
        color: #888;
        margin-top: 2rem;
    }

    /* Pagination style */
    .pagination .page-item .page-link {
        color: #0d6efd;
        border-radius: 8px;
        margin: 0 3px;
        transition: background-color 0.2s;
    }

    .pagination .page-item.active .page-link,
    .pagination .page-item .page-link:hover {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }

    .pagination .page-item.disabled .page-link {
        pointer-events: none;
        opacity: 0.6;
    }
</style>


<!-- AJAX -->
<div class="container py-4 rounded-3" style="background: linear-gradient(to right,rgb(23, 107, 232),rgb(218, 15, 15));">
    <h2 class="mb-4">Daftar Produk</h2>

    <!-- Search -->
    <div class="row mb-3">
        <div class="col-md-6">
            <input type="text" id="search-input" class="form-control" placeholder="Cari produk...">
        </div>
    </div>

    <!-- Produk Grid -->
    <div id="product-list" class="row g-4">
        <!-- AJAX akan isi produk di sini -->
    </div>

    <!-- Pagination -->
    <div class="mt-4 d-flex justify-content-center">
        <nav>
            <ul class="pagination" id="pagination">
                <!-- AJAX akan isi pagination di sini -->
            </ul>
        </nav>
    </div>
</div>

<!-- Modal Detail Produk -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detail Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-modal-body">
                <!-- Isi detail produk dengan AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let currentPage = 1;
        const searchInput = document.getElementById("search-input");

        function fetchProducts(page = 1, keyword = '') {
            console.log(`Fetching products: page=${page}, keyword=${keyword}`);
            fetch(`app/controllers/ProductAjaxController.php?action=list&page=${page}&keyword=${keyword}`)
                // fetch(`app/controllers/ProductAjaxController.php?page=${page}&keyword=${keyword}`)

                .then(res => res.text())
                .then(html => {
                    console.log("Memuat pagination untuk halaman:", page);

                    // console.log(html)
                    document.getElementById('product-list').innerHTML = html;

                    // Fetch pagination nav
                    fetch(`app/controllers/ProductAjaxController.php?action=pagination&page=${page}&keyword=${keyword}`)
                        // fetch(`app/controllers/ProductAjaxController.php?action=pagination&page=${page}&keyword=${keyword}`)
                        .then(res => res.text())
                        .then(nav => {
                            console.log("Ini nav : ", nav)
                            document.getElementById('pagination').innerHTML = nav;
                        })
                        .catch(err => {
                            console.err("Pagination error", err)
                        });
                })
                .catch(err => {
                    console.error("Product list error", err)
                });
        }

        // Search handler
        searchInput.addEventListener("input", () => {
            currentPage = 1;
            fetchProducts(currentPage, searchInput.value);
        });

        // Pagination click
        document.getElementById("pagination").addEventListener("click", (e) => {
            if (e.target.classList.contains("page-link")) {
                e.preventDefault();
                currentPage = parseInt(e.target.dataset.page);
                fetchProducts(currentPage, searchInput.value);
            }
        });

        // Modal detail click
        document.getElementById("product-list").addEventListener("click", (e) => {
            if (e.target.classList.contains("btn-detail")) {
                const id = e.target.dataset.id;
                fetch(`app/controllers/ProductAjaxController.php?action=detail&id=${id}`)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById("detail-modal-body").innerHTML = html;
                        new bootstrap.Modal(document.getElementById('detailModal')).show();
                    });
            }
        });

        // Load awal
        fetchProducts();
    });

    // Tambah ke keranjang
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("btn-add-to-cart")) {
            const id = e.target.dataset.id;
            const nama = e.target.dataset.nama;
            const harga = e.target.dataset.harga;
            const qty = document.getElementById("qty").value;

            fetch("app/controllers/CartController.php", {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=add&id=${id}&nama=${encodeURIComponent(nama)}&harga=${harga}&qty=${qty}`
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Produk berhasil ditambahkan ke keranjang!',
                            timer: 1000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        })
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Produk gagal ditambahkan ke keranjang!',
                            timer: 3000,
                            timerProgressBar: true
                        })
                    }
                })
                .catch(err => console.error("Cart error", err));
        }
    });
</script>