<style>
    .app-footer {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        padding: 1.5rem 0;
    }

    .app-footer p {
        margin-bottom: 0;
        opacity: 0.85;
        font-size: 0.9rem;
    }
</style>

<footer class="app-footer mt-auto bg-light text-center py-3 mt-auto">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <p>
                    &copy; <?= date('Y') ?> <strong>TokoKu</strong>. All rights reserved.
                    <span class="d-block d-sm-inline">Versi 1.0.0</span>
                </p>
            </div>
        </div>
    </div>
</footer>
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
</body>

</html>