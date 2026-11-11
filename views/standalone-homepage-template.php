<?php
/**
 * Standalone Frontend Public Template
 * Matches reference image (Clean hero with background image overlay, perfect typography, dark dropdown)
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_types = $wpdb->prefix . 'dmr_member_types';
$categories = $wpdb->get_results("SELECT * FROM $table_types WHERE status = 'active' ORDER BY id ASC");

$home_url = home_url('/');
$about_url = home_url('/about/');
$category_url = home_url('/category/');
$contact_url = home_url('/contact-us/');
$login_url = is_user_logged_in() ? home_url('/dashboard/') : home_url('/member-login/');
$login_label = is_user_logged_in() ? 'Dashboard' : 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Registration &mdash; Official Membership Platform</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #FAFAFA;
            color: #1F2937;
            overflow-x: hidden;
        }

        /* Top Clean Navbar (Matches Second Screenshot) */
        .site-header {
            background: #FFFFFF;
            height: 76px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            position: relative;
            z-index: 1000;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #1F2937;
        }

        .header-logo-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: #D32F2F;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
        }

        .header-logo-text {
            font-size: 20px;
            font-weight: 700;
            color: #1F2937;
            letter-spacing: -0.5px;
        }

        .header-logo-text strong {
            color: #D32F2F;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            list-style: none;
            gap: 32px;
            margin: 0;
            padding: 0;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            text-decoration: none;
            color: #374151;
            font-size: 15px;
            font-weight: 500;
            padding: 26px 0;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color 0.15s ease;
        }

        .nav-link:hover, .nav-item.active .nav-link {
            color: #D32F2F;
        }

        /* Dark Dropdown like Reference Image */
        .category-dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            min-width: 220px;
            background: rgba(43, 20, 20, 0.95);
            backdrop-filter: blur(8px);
            border-radius: 4px;
            list-style: none;
            padding: 10px 0;
            margin: 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            z-index: 99999;
        }

        .nav-item:hover .category-dropdown-menu {
            display: block;
        }

        .category-dropdown-menu li a {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            color: #E5E7EB;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .category-dropdown-menu li a:hover {
            background: rgba(211, 47, 47, 0.4);
            color: #FFFFFF;
            padding-left: 24px;
        }

        .category-dropdown-badge {
            background: rgba(255, 255, 255, 0.15);
            color: #FFFFFF;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .btn-header-login {
            background: #D32F2F;
            color: #FFFFFF !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 24px;
            border-radius: 6px;
            transition: background 0.2s ease;
        }

        .btn-header-login:hover {
            background: #B71C1C;
        }

        /* Hero Banner - Clean White Background without Dark Image */
        .hero-banner {
            position: relative;
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #1F2937;
            background: #FFFFFF;
            padding: 80px 24px;
            border-bottom: 1px solid #E5E7EB;
        }

        .hero-inner {
            max-width: 860px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .hero-badge-top {
            display: inline-block;
            background: #FFF1F2;
            color: #E11D48;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 6px 16px;
            border-radius: 9999px;
            margin-bottom: 24px;
            border: 1px solid #FFE4E6;
        }

        .hero-title {
            font-size: 52px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 24px;
            letter-spacing: -0.02em;
            color: #0F172A;
        }

        .hero-title span {
            color: #D32F2F;
        }

        .hero-description {
            font-size: 19px;
            line-height: 1.65;
            color: #475569;
            max-width: 680px;
            margin: 0 auto 36px auto;
            font-weight: 400;
        }

        .btn-hero-action {
            display: inline-block;
            background: #D32F2F;
            color: #FFFFFF !important;
            text-decoration: none;
            padding: 15px 38px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 14px rgba(211, 47, 47, 0.35);
            transition: all 0.2s ease;
        }

        .btn-hero-action:hover {
            background: #B71C1C;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(211, 47, 47, 0.45);
        }

        /* Language badge bottom right like screenshot */
        .bottom-lang-pill {
            position: absolute;
            bottom: 24px;
            right: 40px;
            background: #FFFFFF;
            color: #1F2937;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            z-index: 10;
        }
    </style>
</head>
<body>

<!-- Header (Clean Navbar matching screenshot 2) -->
<header class="site-header">
    <a href="<?php echo esc_url($home_url); ?>" class="header-logo">
        <span class="header-logo-icon">DR</span>
        <span class="header-logo-text">Dynamic<strong>Reg</strong></span>
    </a>

    <nav>
        <ul class="nav-menu">
            <li class="nav-item <?php echo is_front_page() ? 'active' : ''; ?>">
                <a href="<?php echo esc_url($home_url); ?>" class="nav-link">Home</a>
            </li>
            <li class="nav-item">
                <a href="<?php echo esc_url($about_url); ?>" class="nav-link">About Us</a>
            </li>
            
            <!-- Category Dropdown with dark styling like screenshot 2 -->
            <li class="nav-item">
                <a href="<?php echo esc_url($category_url); ?>" class="nav-link">
                    Category <span style="font-size:11px;">&#9662;</span>
                </a>
                <ul class="category-dropdown-menu">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="<?php echo esc_url(add_query_arg('category_id', $cat->id, home_url('/membership-registration/'))); ?>">
                                    <span><?php echo esc_html($cat->type_name); ?></span>
                                    <span class="category-dropdown-badge"><?php echo esc_html($cat->type_prefix); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><a href="<?php echo esc_url($category_url); ?>">All Categories</a></li>
                    <?php endif; ?>
                </ul>
            </li>

            <li class="nav-item">
                <a href="<?php echo esc_url($contact_url); ?>" class="nav-link">Contact Us</a>
            </li>
        </ul>
    </nav>

    <div>
        <a href="<?php echo esc_url($login_url); ?>" class="btn-header-login">
            <?php echo esc_html($login_label); ?>
        </a>
    </div>
</header>

<!-- Hero Section (Clean White Red Theme without background image) -->
<section class="hero-banner">
    <div class="hero-inner">
        <span class="hero-badge-top">Official Membership Platform</span>
        <h1 class="hero-title">
            Preserving Dignity, Respecting Values, <span>Securing Your Future.</span>
        </h1>
        <p class="hero-description">
            The leading digital platform for dynamic membership registrations and seamless onboarding. We provide a secure, transparent gateway connecting members, tailored categories, and custom registration workflows.
        </p>
        <a href="<?php echo esc_url($category_url); ?>" class="btn-hero-action">
            Explore Categories &rarr;
        </a>
    </div>
</section>

</body>
</html>
<?php
exit;

