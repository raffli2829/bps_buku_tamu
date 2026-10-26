<!-- Main Sidebar -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">

    <!-- Logo -->
    <a href="dashboard.php" class="brand-link">

        <img src="assets/img/logo_bps.png"
             alt="Logo BPS"
             class="brand-image img-circle elevation-3"
             style="opacity:.8">

        <span class="brand-text font-weight-bold">
            SIRITA
        </span>

    </a>

    <!-- Sidebar -->
    <div class="sidebar">

        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">

    <div class="image">

        <a href="dashboard.php?page=akun">

            <img src="dist/img/user2-160x160.jpg"
                 class="img-circle elevation-2"
                 alt="User">

        </a>

    </div>

    <div class="info">

        <a href="dashboard.php?page=akun" class="d-block text-white">

            <?= $_SESSION['nama']; ?>

        </a>

    </div>

</div>

        <!-- Menu -->
        <nav class="mt-2">

            <ul class="nav nav-pills nav-sidebar flex-column"
                data-widget="treeview"
                role="menu"
                data-accordion="false">

                <!-- Dashboard -->
                <li class="nav-item">

                    <a href="dashboard.php"
                       class="nav-link <?= !isset($_GET['page']) ? 'active' : ''; ?>">

                        <i class="nav-icon fas fa-home"></i>

                        <p>Dashboard</p>

                    </a>

                </li>

                <!-- Pengunjung -->
                <li class="nav-item">

                    <a href="dashboard.php?page=pengunjung"
                       class="nav-link <?= (isset($_GET['page']) && $_GET['page']=='pengunjung') ? 'active' : ''; ?>">

                        <i class="nav-icon fas fa-users"></i>

                        <p>Data Pengunjung</p>

                    </a>

                </li>

                <!-- Pelayanan -->
                <li class="nav-item">

                    <a href="dashboard.php?page=pelayanan"
                       class="nav-link <?= (isset($_GET['page']) && $_GET['page']=='pelayanan') ? 'active' : ''; ?>">

                        <i class="nav-icon fas fa-building"></i>

                        <p>Pelayanan</p>

                    </a>

                </li>

                <!-- Antrian -->
                <li class="nav-item">

                    <a href="dashboard.php?page=antrian"
                       class="nav-link <?= (isset($_GET['page']) && $_GET['page']=='antrian') ? 'active' : ''; ?>">

                        <i class="nav-icon fas fa-ticket-alt"></i>

                        <p>Antrian</p>

                    </a>

                </li>

                <!-- Display TV -->
                <li class="nav-item">

                    <a href="display.php"
                       target="_blank"
                       class="nav-link">

                        <i class="nav-icon fas fa-tv"></i>

                        <p>Display TV</p>

                    </a>

                </li>

                <!-- Laporan -->
                <li class="nav-item">

                    <a href="dashboard.php?page=laporan"
                       class="nav-link <?= (isset($_GET['page']) && $_GET['page']=='laporan') ? 'active' : ''; ?>">

                        <i class="nav-icon fas fa-file-alt"></i>

                        <p>Laporan</p>

                    </a>

                </li>
                <li class="nav-item">

    <a href="dashboard.php?page=akun" class="nav-link">

        <i class="nav-icon fas fa-user-cog"></i>

        <p>Pengaturan Akun</p>

    </a>

</li>
                <!-- Logout -->
                <li class="nav-item">

                  <a href="logout.php" class="nav-link btnLogout">

    <i class="nav-icon fas fa-sign-out-alt"></i>

    <p>Logout</p>

</a>

                </li>

            </ul>

        </nav>

    </div>

</aside>