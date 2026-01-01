<nav class="navbar">
    <div class="navbar-inner">
        <div class="navbar-row">
            <div class="flex-center">
                <a href="/" class="navbar-brand">
                    <i class='bx bx-check-double icon-lg'></i> To-Do
                </a>
            </div>

            <div class="navbar-desktop">
                <?php if (!empty($_SESSION['user'])): ?>
                    <a href="dashboard.php" class="navbar-link">Dashboard</a>
                    <a href="index.php" class="navbar-link">Todo</a>
                    <a href="matrix.php" class="navbar-link">Matrix</a>
                    <a href="tags.php" class="navbar-link">Kategori</a>
                    <a href="logout.php" class="neo-btn neo-btn-primary navbar-button">
                            <i class='bx bx-log-out icon-gap'></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="neo-btn neo-btn-secondary navbar-button">Login</a>
                    <a href="register.php" class="neo-btn neo-btn-primary navbar-button">Register</a>
                <?php endif; ?>
            </div>

            <div class="md:hidden">
                <button id="mobile-menu-button" aria-label="open menu" class="navbar-mobile-btn">
                    <i class='bx bx-menu icon-nav'></i>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="navbar-mobile-menu is-hidden">
            <div class="navbar-mobile-list">
                <?php if (!empty($_SESSION['user'])): ?>
                    <a href="dashboard.php" class="navbar-mobile-link">Dashboard</a>
                    <a href="index.php" class="navbar-mobile-link">Todo</a>
                    <a href="matrix.php" class="navbar-mobile-link">Matrix</a>
                    <a href="tags.php" class="navbar-mobile-link">Kategori</a>
                    <a href="logout.php" class="navbar-mobile-link text-danger">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="navbar-mobile-link">Login</a>
                    <a href="register.php" class="navbar-mobile-link">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<script>
    const btn = document.getElementById('mobile-menu-button');
    const menu = document.getElementById('mobile-menu');

    btn?.addEventListener('click', () => {
        menu?.classList.toggle('is-hidden');
    });
</script>
<div class="navbar-spacer"></div>