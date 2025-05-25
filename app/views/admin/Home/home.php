<div class="welcome-admin-container">
    <div class="welcome-admin-card">
        <h1 class="welcome-admin-title">
            <i class="fas fa-user-shield welcome-icon"></i>
            Selamat Datang, <?= htmlspecialchars($_SESSION['admin']['username']) ?> 👋
        </h1>
        <p class="welcome-admin-subtitle">Ini adalah halaman dashboard admin.</p>
    </div>
</div>

<style>
    .welcome-admin-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 70vh;
        padding: 20px;
    }

    .welcome-admin-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        padding: 40px;
        text-align: center;
        max-width: 600px;
        width: 100%;
        border-left: 5px solid #4f46e5;
    }

    .welcome-admin-title {
        font-size: 2.2rem;
        color: #4f46e5;
        margin-bottom: 15px;
        font-weight: 700;
    }

    .welcome-icon {
        color: #f59e0b;
        margin-right: 10px;
    }

    .welcome-admin-subtitle {
        font-size: 1.1rem;
        color: #64748b;
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        .welcome-admin-title {
            font-size: 1.8rem;
        }

        .welcome-admin-card {
            padding: 30px 20px;
        }
    }
</style>
<!-- <pre><?php print_r($_SESSION); ?></pre> -->