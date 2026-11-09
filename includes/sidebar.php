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
        <?php
        $foto_sidebar = 'assets/img/default-avatar.jpg';
        if (!empty($_SESSION['foto']) && file_exists('assets/img/uploads/' . $_SESSION['foto'])) {
            $foto_sidebar = 'assets/img/uploads/' . $_SESSION['foto'];
        } elseif (isset($_SESSION['id_user'])) {
            if (!isset($conn)) {
                include_once __DIR__ . '/../config/koneksi.php';
            }
            if (isset($conn) && $conn instanceof mysqli) {
                $stmt_side = $conn->prepare("SELECT foto FROM users WHERE id_user = ? LIMIT 1");
                if ($stmt_side) {
                    $stmt_side->bind_param("i", $_SESSION['id_user']);
                    $stmt_side->execute();
                    $res_side = $stmt_side->get_result();
                    if ($res_side && $row_side = $res_side->fetch_assoc()) {
                        if (!empty($row_side['foto']) && file_exists('assets/img/uploads/' . $row_side['foto'])) {
                            $foto_sidebar = 'assets/img/uploads/' . $row_side['foto'];
                            $_SESSION['foto'] = $row_side['foto'];
                        }
                    }
                    $stmt_side->close();
                }
            }
        }
        ?>
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">

    <div class="image">

        <a href="dashboard.php?page=akun">

            <img src="<?= htmlspecialchars($foto_sidebar ?? 'assets/img/default-avatar.jpg'); ?>" style="width: 34px; height: 34px; object-fit: cover; background-color: #fff;"
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
                       class="nav-link <?= (isset($_GET['page']) && in_array($_GET['page'], ['pengunjung', 'pengunjung_tambah', 'pengunjung_detail', 'pengunjung_edit'])) ? 'active' : ''; ?>">

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

                <!-- Survei Kepuasan -->
                <li class="nav-item">

                    <a href="dashboard.php?page=survei"
                       class="nav-link <?= (isset($_GET['page']) && $_GET['page']=='survei') ? 'active' : ''; ?>">

                        <i class="nav-icon fas fa-star"></i>

                        <p>Survei Kepuasan</p>

                    </a>

                </li>

                <!-- Pengumuman Display TV -->
                <li class="nav-item">

                    <a href="dashboard.php?page=pengumuman"
                       class="nav-link <?= (isset($_GET['page']) && $_GET['page']=='pengumuman') ? 'active' : ''; ?>">

                        <i class="nav-icon fas fa-bullhorn"></i>

                        <p>Pengumuman Display</p>

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

    <a href="dashboard.php?page=akun" class="nav-link <?= (isset($_GET['page']) && $_GET['page']=='akun') ? 'active' : ''; ?>">

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