<!-- navbar.php -->
<nav class="main-nav">
    <div class="nav-container">
        <div class="nav-logo">
            <a href="../index.php">
                <span class="logo-icon"><i class="fas fa-check-circle"></i></span>
                <span class="logo-text">Placify</span>
            </a>
        </div>
        
        <button class="mobile-menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a href="../admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</a></li>
                    <li><a href="../admin/create_test.php"><i class="fas fa-plus-circle"></i> Create Test</a></li>
                    <li><a href="../admin/view_results.php"><i class="fas fa-chart-pie"></i> View Results</a></li>
                <?php elseif ($_SESSION['role'] === 'student'): ?>
                    <li><a href="../student/dashboard.php"><i class="fas fa-tachometer-alt"></i> Student Dashboard</a></li>
                    <li><a href="../student/result.php"><i class="fas fa-poll"></i> View Results</a></li>
                <?php endif; ?>
                <li class="nav-divider"></li>
                <li class="user-menu">
                    <a href="#" class="user-menu-trigger">
                        <i class="fas fa-user-circle"></i>
                        <span class="username"><?= htmlspecialchars($_SESSION['name']); ?></span>
                        <i class="fas fa-angle-down"></i>
                    </a>
                    <ul class="user-dropdown">
                        <li><a href="../profile.php"><i class="fas fa-id-card"></i> My Profile</a></li>
                        <li><a href="../settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                        <li class="dropdown-divider"></li>
                        <li><a href="./auth/logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="./auth/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="./auth/register.php" class="signup-btn"><i class="fas fa-user-plus"></i> Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<style>
    .main-nav {
        background-color: white;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        position: sticky;
        top: 0;
        z-index: 1000;
    }
    
    .nav-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 2rem;
        max-width: 1400px;
        margin: 0 auto;
        height: 70px;
    }
    
    .nav-logo a {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: var(--primary, #4361ee);
        font-weight: 700;
        font-size: 1.5rem;
    }
    
    .logo-icon {
        margin-right: 0.5rem;
        font-size: 1.8rem;
    }
    
    .nav-links {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
        align-items: center;
    }
    
    .nav-links li {
        margin: 0 0.2rem;
    }
    
    .nav-links a {
        text-decoration: none;
        color: var(--dark, #212529);
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
    }
    
    .nav-links a i {
        margin-right: 0.5rem;
    }
    
    .nav-links a:hover {
        background-color: #f5f7fb;
        color: var(--primary, #4361ee);
    }
    
    .signup-btn {
        background-color: var(--primary, #4361ee);
        color: white !important;
        padding: 0.6rem 1.2rem !important;
        border-radius: 20px !important;
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.15);
    }
    
    .signup-btn:hover {
        background-color: var(--secondary, #3f37c9) !important;
        transform: translateY(-2px);
    }
    
    .nav-divider {
        height: 30px;
        width: 1px;
        background-color: #e9ecef;
        margin: 0 0.5rem;
    }
    
    .user-menu {
        position: relative;
    }
    
    .user-menu-trigger {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        background-color: #f5f7fb;
    }
    
    .user-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        width: 220px;
        padding: 0.5rem 0;
        list-style: none;
        display: none;
        z-index: 1001;
    }
    
    .user-menu:hover .user-dropdown {
        display: block;
    }
    
    .user-dropdown a {
        padding: 0.75rem 1.5rem;
        display: flex;
        align-items: center;
    }
    
    .dropdown-divider {
        height: 1px;
        background-color: #e9ecef;
        margin: 0.5rem 0;
    }
    
    .logout-link {
        color: #dc3545 !important;
    }
    
    .logout-link:hover {
        background-color: #fdf2f4 !important;
    }
    
    .mobile-menu-toggle {
        display: none;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--dark, #212529);
        cursor: pointer;
    }
    
    /* Responsive styles */
    @media (max-width: 992px) {
        .mobile-menu-toggle {
            display: block;
        }
        
        .nav-links {
            position: absolute;
            top: 70px;
            left: 0;
            right: 0;
            background-color: white;
            flex-direction: column;
            align-items: stretch;
            padding: 1rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            display: none;
        }
        
        .nav-links.active {
            display: flex;
        }
        
        .nav-links li {
            margin: 0.5rem 0;
        }
        
        .nav-divider {
            height: 1px;
            width: 100%;
            margin: 0.5rem 0;
        }
        
        .user-dropdown {
            position: static;
            box-shadow: none;
            width: 100%;
            padding: 0 0 0 1rem;
            margin-top: 0.5rem;
        }
        
        .user-menu:hover .user-dropdown {
            display: none;
        }
        
        .user-menu.active .user-dropdown {
            display: block;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const navLinks = document.getElementById('navLinks');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                navLinks.classList.toggle('active');
            });
        }
        
        // Handle user menu on mobile
        const userMenus = document.querySelectorAll('.user-menu-trigger');
        userMenus.forEach(menu => {
            menu.addEventListener('click', function(e) {
                if (window.innerWidth <= 992) {
                    e.preventDefault();
                    this.closest('.user-menu').classList.toggle('active');
                }
            });
        });
    });
</script>