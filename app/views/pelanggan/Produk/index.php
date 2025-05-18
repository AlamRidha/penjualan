<style>
    .card {
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .card-img-top {
        height: 180px;
        object-fit: cover;
    }

    .card-body {
        display: flex;
        flex-direction: column;
    }

    .card-text {
        flex-grow: 1;
    }

    .btn-cart {
        margin-top: auto;
    }

    .pagination {
        justify-content: center;
        margin-top: 20px;
    }

    .price {
        font-size: 1.2rem;
        font-weight: bold;
    }

    .stock {
        font-size: 0.9rem;
        color: #6c757d;
    }

    /* Animasi saat card muncul */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .produk-card {
        animation: fadeInUp 0.5s ease-out forwards;
        opacity: 0;
    }

    /* Delay animasi untuk setiap card */
    .col-md-3:nth-child(1) .produk-card {
        animation-delay: 0.1s;
    }

    .col-md-3:nth-child(2) .produk-card {
        animation-delay: 0.2s;
    }

    #price-filter {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    #filter-btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    @media (max-width: 768px) {
        .row.mb-4>div {
            margin-bottom: 10px;
        }

        .row.mb-4>div:last-child {
            margin-bottom: 0;
        }
    }
</style>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-4">
            <h3>Daftar Produk</h3>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <input type="text" id="search-input" class="form-control" placeholder="Cari produk...">
                <button class="btn btn-outline-secondary" id="search-btn">Cari</button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <select class="form-select" id="price-filter">
                    <option value="">Semua Harga</option>
                    <option value="0-50000">Rp0 - Rp50.000</option>
                    <option value="50000-100000">Rp50.000 - Rp100.000</option>
                    <option value="100000-200000">Rp100.000 - Rp200.000</option>
                    <option value="200000-">> Rp200.000</option>
                </select>
                <button class="btn btn-outline-secondary" id="filter-btn">Filter</button>
            </div>
        </div>
    </div>

    <div class="row" id="produk-grid">
        <!-- Loading spinner -->
        <div class="col-12 text-center my-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
        <ul class="pagination" id="pagination">
            <!-- Pagination akan diisi oleh JavaScript -->
        </ul>
    </nav>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let currentPage = 1;
        const itemsPerPage = 8;
        let currentSearch = '';
        let currentPriceFilter = '';

        // Tambahkan event listener untuk filter harga
        document.getElementById("filter-btn").addEventListener('click', function() {
            currentPriceFilter = document.getElementById("price-filter").value;
            currentPage = 1;
            loadProducts(currentPage, currentSearch, currentPriceFilter);
        });

        // Fungsi untuk memuat produk
        // function loadProducts(page = 1, search = '') {
        //     const container = document.getElementById("produk-grid");
        //     container.innerHTML = `
        //     <div class="col-12 text-center my-5">
        //         <div class="spinner-border text-primary" role="status">
        //             <span class="visually-hidden">Loading...</span>
        //         </div>
        //     </div>`;

        //     const url = `app/controllers/ProdukController.php?aksi=list&mode=raw&page=${page}&per_page=${itemsPerPage}&search=${encodeURIComponent(search)}`;

        //     fetch(url)
        //         .then(res => res.json())
        //         .then(data => {
        //             if (data.success) {
        //                 renderProducts(data.data);
        //                 renderPagination(data.total_pages, page);
        //             } else {
        //                 container.innerHTML = `<div class="col-12 text-center text-danger">Gagal memuat produk</div>`;
        //             }
        //         })
        //         .catch(error => {
        //             console.error('Error:', error);
        //             container.innerHTML = `<div class="col-12 text-center text-danger">Terjadi kesalahan saat memuat produk</div>`;
        //         });
        // }
        function loadProducts(page = 1, search = '', priceFilter = '') {
            const container = document.getElementById("produk-grid");
            container.innerHTML = `
            <div class="col-12 text-center my-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>`;

            // Bangun URL dengan parameter tambahan untuk filter harga
            let url = `app/controllers/ProdukController.php?aksi=list&mode=raw&page=${page}&per_page=${itemsPerPage}&search=${encodeURIComponent(search)}`;

            if (priceFilter) {
                url += `&price_filter=${encodeURIComponent(priceFilter)}`;
            }

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderProducts(data.data);
                        renderPagination(data.total_pages, page);
                    } else {
                        container.innerHTML = `<div class="col-12 text-center text-danger">Gagal memuat produk</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = `<div class="col-12 text-center text-danger">Terjadi kesalahan saat memuat produk</div>`;
                });
        }

        // Fungsi untuk menampilkan produk
        function renderProducts(products) {
            const container = document.getElementById("produk-grid");

            if (products.length === 0) {
                container.innerHTML = `
                <div class="col-12 text-center">
                    <div class="alert alert-info">Tidak ada produk yang ditemukan</div>
                </div>`;
                return;
            }

            container.innerHTML = '';

            products.forEach(item => {
                const produkCard = `
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        ${item.foto_produk ? 
                            `<img src="${item.foto_produk}" class="card-img-top" alt="${item.nama_produk}">` : 
                            `<div class="card-img-top bg-light d-flex align-items-center justify-content-center">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>`
                        }
                        <div class="card-body">
                            <h5 class="card-title">${item.nama_produk}</h5>
                            <p class="card-text">${item.deskripsi_singkat}</p>
                            <div class="price text-success">${item.harga_produk}</div>
                            <div class="stock">Stok: ${item.stok_produk}</div>
                            <div class="mt-3 d-flex justify-content-between">
                                <button class="btn btn-sm btn-outline-primary btn-detail" data-id="${item.id_produk}">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                                <button class="btn btn-sm btn-success btn-cart" data-id="${item.id_produk}" ${item.stok_produk <= 0 ? 'disabled' : ''}>
                                    <i class="fas fa-cart-plus"></i> ${item.stok_produk <= 0 ? 'Habis' : 'Beli'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                container.innerHTML += produkCard;
            });
        }

        // Fungsi untuk menampilkan pagination
        function renderPagination(totalPages, currentPage) {
            const pagination = document.getElementById("pagination");
            pagination.innerHTML = '';

            if (totalPages <= 1) return;

            // Previous button
            pagination.innerHTML += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
            </li>
        `;

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                pagination.innerHTML += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
            }

            // Next button
            pagination.innerHTML += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
            </li>
        `;
        }

        // Event listener untuk pagination
        document.getElementById("pagination").addEventListener('click', function(e) {
            e.preventDefault();
            if (e.target.tagName === 'A') {
                currentPage = parseInt(e.target.dataset.page);
                loadProducts(currentPage, currentSearch);
            }
        });

        // Event listener untuk pencarian
        document.getElementById("search-btn").addEventListener('click', function() {
            currentSearch = document.getElementById("search-input").value;
            currentPage = 1;
            loadProducts(currentPage, currentSearch);
        });

        // Event listener untuk enter pada input search
        document.getElementById("search-input").addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                currentSearch = this.value;
                currentPage = 1;
                loadProducts(currentPage, currentSearch);
            }
        });

        // Event listener untuk detail dan keranjang
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-detail') || e.target.closest('.btn-detail')) {
                const id = e.target.dataset.id || e.target.closest('.btn-detail').dataset.id;
                showProductDetail(id);
            }

            if (e.target.classList.contains('btn-cart') || e.target.closest('.btn-cart')) {
                const id = e.target.dataset.id || e.target.closest('.btn-cart').dataset.id;
                addToCart(id);
            }
        });

        // Fungsi untuk menampilkan detail produk
        function showProductDetail(id) {
            fetch(`app/controllers/ProdukController.php?aksi=detail&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        Swal.fire('Error', data.error, 'error');
                    } else {
                        // Tampilkan modal detail produk
                        Swal.fire({
                            title: data.nama_produk,
                            html: `
                            <div class="text-center mb-3">
                                ${data.foto_produk ? `<img src="${data.foto_produk}" class="img-fluid" style="max-height: 200px;">` : ''}
                            </div>
                            <p><strong>Harga:</strong> Rp${number_format(data.harga_produk, 0, ',', '.')}</p>
                            <p><strong>Stok:</strong> ${data.stok_produk}</p>
                            <p><strong>Deskripsi:</strong> ${data.deskripsi_produk}</p>
                        `,
                            showCancelButton: true,
                            confirmButtonText: 'Tambah ke Keranjang',
                            cancelButtonText: 'Tutup',
                            showCloseButton: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                addToCart(id);
                            }
                        });
                    }
                });
        }

        // Fungsi untuk menambahkan ke keranjang
        function addToCart(id) {
            // Contoh implementasi AJAX untuk menambah ke keranjang
            fetch('app/controllers/KeranjangController.php?aksi=tambah', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_produk=${id}&qty=1`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Sukses', 'Produk berhasil ditambahkan ke keranjang', 'success');
                        // Update counter keranjang jika ada
                        if (typeof updateCartCount === 'function') {
                            updateCartCount();
                        }
                    } else {
                        Swal.fire('Error', data.message || 'Gagal menambahkan ke keranjang', 'error');
                    }
                });
        }

        // Helper function untuk format number
        function number_format(number, decimals, decPoint, thousandsSep) {
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            const n = !isFinite(+number) ? 0 : +number;
            const prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
            const sep = typeof thousandsSep === 'undefined' ? ',' : thousandsSep;
            const dec = typeof decPoint === 'undefined' ? '.' : decPoint;

            const toFixedFix = (n, prec) => {
                const k = Math.pow(10, prec);
                return '' + (Math.round(n * k) / k).toFixed(prec);
            };

            const s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }

            return s.join(dec);
        }

        // Muat produk pertama kali
        loadProducts(currentPage);
    });
</script>