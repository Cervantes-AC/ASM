<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine login state and user info
$isLoggedIn = isset($_SESSION['user_id']);
$userRole   = $_SESSION['role']      ?? 'guest';
$username   = $_SESSION['full_name'] ?? 'Guest';

// Include header after session started
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Management System - CMU SSC</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        main {
            max-width: 900px;
            margin: 40px auto;
            padding: 0;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
        }

        .hero-section {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(-10px, -10px) rotate(1deg); }
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #fff, #e8f4fd);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .hero-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .welcome-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .content-section {
            padding: 50px 40px;
        }

        .section-title {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(45deg, #3498db, #2980b9);
            border-radius: 2px;
        }

        .description {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
            margin-bottom: 40px;
            text-align: justify;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }

        .feature-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 30px;
            border-radius: 15px;
            border-left: 5px solid #3498db;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, transparent 0%, rgba(52, 152, 219, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-left-color: #2980b9;
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, #3498db, #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: white;
            font-size: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }

        .feature-description {
            color: #666;
            line-height: 1.6;
            position: relative;
            z-index: 2;
        }

        .mission-section {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .mission-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            animation: float 15s ease-in-out infinite reverse;
        }

        .mission-content {
            position: relative;
            z-index: 2;
        }

        .contact-section {
            background: #f8f9fa;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            border: 2px solid #e9ecef;
        }

        .contact-title {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .contact-info {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
        }

        .contact-link {
            color: #2e7d32;
            font-weight: 600;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 25px;
            background: rgba(46, 125, 50, 0.1);
            transition: all 0.3s ease;
            display: inline-block;
            margin-top: 10px;
        }

        .contact-link:hover {
            background: #2e7d32;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 125, 50, 0.3);
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            text-align: center;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-top: 3px solid #3498db;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @media (max-width: 768px) {
            main {
                margin: 20px;
            }
            
            .hero-section {
                padding: 40px 20px;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .content-section {
                padding: 30px 20px;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<main>
    <div class="hero-section">
        <div class="hero-content">
            <?php if ($isLoggedIn): ?>
                <div class="welcome-badge">
                    👋 Welcome back, <?= htmlspecialchars($username) ?>!
                </div>
            <?php endif; ?>
            
            <h1 class="hero-title">
                Asset Management System
            </h1>
            <p class="hero-subtitle">
                Central Mindanao University Supreme Student Council<br>
            </p>
        </div>
    </div>

    <div class="content-section">
        <p class="description">
            Welcome to the comprehensive Asset Management System designed specifically for the Central Mindanao University Supreme Student Council. Our platform revolutionizes how university-owned assets are tracked, borrowed, and managed across all departments, ensuring complete transparency and operational efficiency.
        </p>


        <div class="mission-section">
            <div class="mission-content">
                <h2 class="section-title" style="color: white; margin-bottom: 20px;">🎯 Our Mission</h2>
                <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 0;">
                    To revolutionize asset management within CMU by providing a transparent, accountable, and user-friendly platform that supports the university's digital transformation goals. We're committed to enhancing operational efficiency while maintaining the highest standards of security and reliability.
                </p>
            </div>
        </div>

        <div class="contact-section">
            <h2 class="contact-title">📞 Get in Touch</h2>
            <p class="contact-info">
                Need assistance or have technical questions? Our dedicated support team is here to help you make the most of our asset management system.
                <br><br>
                <strong>SSC Technical Committee</strong><br>
                <a href="mailto:ssc-tech@cmu.edu.ph" class="contact-link">
                    📧 menguez@cmu.edu.ph
                </a>
            </p>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>