<?php
require_once base_path('app/config/Auth.php');
require_once base_path('app/config/Database.php');

if (!isLoggedIn() || !isAdmin()) {
    header("Location: " . base_url('index.php?page=login'));
    exit;
}

// Default tanggal
$startDate = date('Y-m-01');
$endDate = date('Y-m-d');

?>

<div class="container mt-4">
    <h3 class="mb-3"><i class="fas fa-chart-bar"></i> Laporan Penjualan</h3>

    <div class="row mb-3">
        <div class="col-md-3">
            <label>Dari Tanggal</label>
            <input type="date" id="startDate" class="form-control" value="<?= $startDate ?>">
        </div>
        <div class="col-md-3">
            <label>Sampai Tanggal</label>
            <input type="date" id="endDate" class="form-control" value="<?= $endDate ?>">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button id="btnFilter" class="btn btn-primary">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table id="reportTable" class="table table-bordered table-striped">
            <thead class="table-dark text-center">
                <tr>
                    <th>No</th>
                    <th>Customer</th>
                    <th>Tanggal Order</th>
                    <th>Total Harga</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th id="footerTotal">Rp0</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function logAjaxError(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
            console.log("Status code:", jqXHR.status);
            console.log("Response text:", jqXHR.responseText);
        }

        const table = $('#reportTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '<?= base_url('app/controllers/laporan_handler.php') ?>',
                type: 'GET',
                data: function(d) {
                    return {
                        start: $('#startDate').val(),
                        end: $('#endDate').val()
                    };
                },
                dataSrc: function(json) {
                    let total = 0;
                    console.log("DataTable received data:", json);
                    json.data.forEach(item => {
                        let angka = parseInt(item.total_harga.replace(/\D/g, ''));
                        total += angka;
                    })

                    $('#footerTotal').text('Rp' + total.toLocaleString('id-ID'));

                    return json.data || [];
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    logAjaxError(jqXHR, textStatus, errorThrown);
                }
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'username'
                },
                {
                    data: 'tanggal_order'
                },
                {
                    data: 'total_harga'
                }
            ],
            language: {
                lengthMenu: "Tampilkan _MENU_ Data",
                zeroRecords: "Tidak ada data yang cocok",
                info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                infoEmpty: "Tidak ada data tersedia",
                infoFiltered: "(difilter dari _MAX_ total data)",
                search: "Cari:",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        $('#btnFilter').on('click', function() {
            table.ajax.reload();
        });

        // Optional: Global error handler
        $(document).ajaxError(function(event, jqXHR, settings, errorThrown) {
            logAjaxError(jqXHR, errorThrown);
            Swal.fire({
                icon: 'error',
                title: 'Error Loading Data',
                text: 'There was a problem fetching the report data. Please try again.',
                confirmButtonText: 'OK'
            });
        });
    });
</script>