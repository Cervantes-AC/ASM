<?php
// ASM/index.php - Main entry point for the Asset Management System
require_once 'System/config/db.php';
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect logged-in users to their dashboard
    header('Location: System/dashboard/index.php');
    exit();
}

// Get basic system stats for display (public view)
try {
    $totalAssets = (int) $pdo->query("SELECT COUNT(*) FROM assets")->fetchColumn();
    $totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
} catch (Exception $e) {
    $totalAssets = 0;
    $totalUsers = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Management System - Home</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .hero {
            margin-top: 20px;
            padding: 4rem 0;
            text-align: center;
            color: white;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn-primary {
            background: #fff;
            color: #667eea;
        }
        
        .btn-primary:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-secondary:hover {
            background: white;
            color: #667eea;
        }
        
        .features {
            background: white;
            padding: 4rem 0;
        }
        
        .features h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #333;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .feature-card {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .stats {
            background: #2c3e50;
            color: white;
            padding: 3rem 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            color: #3498db;
        }
        
        .stat-card p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        footer {
            background: #1a1a1a;
            color: white;
            text-align: center;
            padding: 2rem 0;
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <!-- Include your existing navbar -->
    <?php include 'includes/navbar.php'; ?>

    <main>
        <section id="home" class="hero">
            <div class="container">
                <h1>SSC ASSET MANAGEMENT SYSTEM</h1>
                <p>Comprehensive digital solution for organization's asset tracking, borrowing, and management processes.</p>
                
                <div class="cta-buttons">
                    <a href="System/auth/login.php" class="btn btn-primary">Get Started</a>
                    <a href="#features" class="btn btn-secondary">Learn More</a>
                </div>
            </div>
        </section>

        <section id="features" class="features">
            <div class="container">
                <h2>System Features</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">📋</div>
                        <h3>Asset Management</h3>
                        <p>Track and manage all your organization's assets in one centralized location. Add, edit, and monitor asset status effortlessly.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">🔄</div>
                        <h3>Borrowing System</h3>
                        <p>Streamlined asset borrowing and return process with approval workflows and automatic due date tracking.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">👥</div>
                        <h3>User Management</h3>
                        <p>Role-based access control with admin and member privileges. Secure user authentication and profile management.</p>
                    </div>
                    
                    </div>
                </div>
            </div>
        </section>

    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> Asset Management System. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll effect to header
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.color = '#333';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.1)';
                header.style.color = 'white';
            }
        });
    </script>
</body>
</html>