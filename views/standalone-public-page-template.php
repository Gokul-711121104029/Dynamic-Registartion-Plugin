<?php
/**
 * Standalone Master Layout for all Public Pages (Home, About Us, Category, Contact Us, Registration, Login)
 * Eliminates WordPress theme bloat completely and provides 100% unified Red & White UI.
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
$login_url = home_url('/member-login/');

// Current page slug
$current_page = 'home';
if (is_page('about')) $current_page = 'about';
elseif (is_page('category')) $current_page = 'category';
elseif (is_page('contact-us')) $current_page = 'contact';
elseif (is_page('membership-registration')) $current_page = 'register';
elseif (is_page('member-login')) $current_page = 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html(get_the_title()); ?> &mdash; Dynamic Registration</title>
    <link rel="stylesheet" href="<?php echo esc_url(DMR_PLUGIN_URL . 'assets/css/frontend.css?v=2.2'); ?>">
    <script src="<?php echo esc_url(includes_url('/js/jquery/jquery.min.js')); ?>"></script>
    <script>
        var dmrFront = {
            ajax_url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
            nonce: "<?php echo wp_create_nonce('dmr_front_nonce'); ?>",
            home_url: "<?php echo esc_url(home_url('/')); ?>"
        };
    </script>
    <script src="<?php echo esc_url(DMR_PLUGIN_URL . 'assets/js/frontend.js?v=2.2'); ?>"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #F8F9FA;
            color: #1F2937;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Unified Header */
        .dmr-header {
            background: #FFFFFF;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            border-bottom: 2px solid #D32F2F;
            position: sticky;
            top: 0;
            z-index: 99999;
        }

        .dmr-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #1F2937;
        }

        .dmr-logo-box {
            width: 40px;
            height: 40px;
            background: #D32F2F;
            color: #FFFFFF;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
        }

        .dmr-logo-name {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .dmr-logo-name strong { color: #D32F2F; }

        .dmr-nav {
            display: flex;
            align-items: center;
            list-style: none;
            gap: 32px;
        }

        .dmr-nav-item { position: relative; }

        .dmr-nav-link {
            text-decoration: none;
            color: #4B5563;
            font-size: 15px;
            font-weight: 600;
            padding: 24px 0;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.15s ease;
        }

        .dmr-nav-link:hover, .dmr-nav-item.active .dmr-nav-link {
            color: #D32F2F;
        }

        /* Hover Dropdown */
        .dmr-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 240px;
            background: #FFFFFF;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
            border: 1px solid #E5E7EB;
            border-top: 3px solid #D32F2F;
            list-style: none;
            padding: 8px 0;
            z-index: 10000;
        }

        .dmr-nav-item:hover .dmr-dropdown { display: block; }

        .dmr-dropdown li a {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 18px;
            font-size: 14px;
            color: #374151;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .dmr-dropdown li a:hover {
            background: #FFF5F5;
            color: #D32F2F;
            padding-left: 22px;
        }

        .dmr-drop-pill {
            background: #FFEBEE;
            color: #D32F2F;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .dmr-btn-login-main {
            background: #D32F2F;
            color: #FFFFFF !important;
            text-decoration: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 6px rgba(211, 47, 47, 0.25);
            transition: background 0.2s ease;
        }

        .dmr-btn-login-main:hover {
            background: #B71C1C;
        }

        /* Main Content Container */
        .dmr-page-wrap {
            flex: 1;
            max-width: 1140px;
            width: 100%;
            margin: 40px auto 60px auto;
            padding: 0 20px;
        }

        .dmr-card {
            background: #FFFFFF;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .dmr-title-center {
            text-align: center;
            margin-bottom: 30px;
        }

        .dmr-title-center h1 {
            font-size: 32px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 10px;
        }

        .dmr-title-center p {
            font-size: 16px;
            color: #6B7280;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Buttons */
        .dmr-btn-solid {
            background: #D32F2F;
            color: #FFFFFF !important;
            border: none;
            border-radius: 6px;
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .dmr-btn-solid:hover { background: #B71C1C; }
    </style>
</head>
<body>

<!-- Unified Top Navigation Bar with Persistent Login Button -->
<header class="dmr-header">
    <a href="<?php echo esc_url($home_url); ?>" class="dmr-logo">
        <span class="dmr-logo-box">DR</span>
        <span class="dmr-logo-name">Dynamic<strong>Reg</strong></span>
    </a>

    <nav>
        <ul class="dmr-nav">
            <li class="dmr-nav-item <?php echo ($current_page === 'home') ? 'active' : ''; ?>">
                <a href="<?php echo esc_url($home_url); ?>" class="dmr-nav-link">Home</a>
            </li>
            <li class="dmr-nav-item <?php echo ($current_page === 'about') ? 'active' : ''; ?>">
                <a href="<?php echo esc_url($about_url); ?>" class="dmr-nav-link">About Us</a>
            </li>
            
            <li class="dmr-nav-item <?php echo ($current_page === 'category') ? 'active' : ''; ?>">
                <a href="<?php echo esc_url($category_url); ?>" class="dmr-nav-link">
                    Category <span style="font-size:11px;">&#9662;</span>
                </a>
                <?php if (!empty($categories)): ?>
                    <ul class="dmr-dropdown">
                        <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="<?php echo esc_url(add_query_arg('category_id', $cat->id, home_url('/membership-registration/'))); ?>">
                                    <span><?php echo esc_html($cat->type_name); ?></span>
                                    <span class="dmr-drop-pill"><?php echo esc_html($cat->type_prefix); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li style="border-top:1px solid #F3F4F6; margin-top:4px;">
                            <a href="<?php echo esc_url($category_url); ?>" style="color:#D32F2F; font-weight:700;">
                                View All Categories &rarr;
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </li>

            <li class="dmr-nav-item <?php echo ($current_page === 'contact') ? 'active' : ''; ?>">
                <a href="<?php echo esc_url($contact_url); ?>" class="dmr-nav-link">Contact Us</a>
            </li>
        </ul>
    </nav>

    <!-- Always displays "Login" button (never displays "Dashboard" or user status here) -->
    <div>
        <a href="<?php echo esc_url($login_url); ?>" class="dmr-btn-login-main">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                <polyline points="10 17 15 12 10 7"></polyline>
                <line x1="15" y1="12" x2="3" y2="12"></line>
            </svg>
            <span>Login</span>
        </a>
    </div>
</header>

<!-- Dynamic Page Content -->
<?php if ($current_page === 'home'): ?>
    <!-- 1. Home Page -->
    <div style="background:#FFFFFF; border-bottom:1px solid #E5E7EB; padding:80px 20px; text-align:center;">
        <div style="max-width:860px; margin:0 auto;">
            <span style="display:inline-block; background:#FFF1F2; color:#E11D48; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; padding:6px 16px; border-radius:9999px; margin-bottom:24px; border:1px solid #FFE4E6;">
                Official Membership Platform
            </span>
            <h1 style="font-size:48px; font-weight:800; line-height:1.2; margin-bottom:24px; color:#0F172A;">
                Preserving Dignity, Respecting Values, <span style="color:#D32F2F;">Securing Your Future.</span>
            </h1>
            <p style="font-size:18px; line-height:1.7; color:#475569; max-width:680px; margin:0 auto 36px auto;">
                The leading digital platform for dynamic membership registrations and seamless onboarding. We provide a secure, transparent gateway connecting members, tailored categories, and custom registration workflows.
            </p>
            <a href="<?php echo esc_url($category_url); ?>" class="dmr-btn-solid" style="padding:14px 36px; font-size:16px;">
                Explore Categories &rarr;
            </a>
        </div>
    </div>

<?php elseif ($current_page === 'about'): ?>
    <!-- 2. About Us Page -->
    <div class="dmr-page-wrap">
        <div class="dmr-card">
            <div class="dmr-title-center">
                <span style="color:#D32F2F; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">About Platform</span>
                <h1>About Our Membership System</h1>
                <p>Learn how our dynamic registration and membership lifecycle works.</p>
            </div>
            <div style="font-size:16px; line-height:1.8; color:#374151; max-width:800px; margin:0 auto; text-align:center;">
                <p style="margin-bottom:20px;">
                    Our platform provides streamlined membership management. Whether you're registering as a Student, Life Member, or Corporate partner, our system offers dynamic form workflows and instant sequential identity generation.
                </p>
                <div style="margin-top:30px;">
                    <a href="<?php echo esc_url($category_url); ?>" class="dmr-btn-solid">
                        View Available Categories &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($current_page === 'category'): ?>
    <!-- 3. Category Page -->
    <div class="dmr-page-wrap">
        <div class="dmr-title-center">
            <span style="color:#D32F2F; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Categories</span>
            <h1>Select Your Membership Category</h1>
            <p>Choose the membership category that best matches your profile to proceed with registration.</p>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px;">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): ?>
                    <div style="background:#FFFFFF; border:1px solid #E5E7EB; border-radius:12px; padding:28px; display:flex; flex-direction:column; justify-content:space-between; box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                        <div>
                            <span style="display:inline-block; background:#FFEBEE; color:#D32F2F; font-size:12px; font-weight:700; padding:4px 10px; border-radius:6px; margin-bottom:12px;">
                                Prefix: <?php echo esc_html($cat->type_prefix); ?>
                            </span>
                            <h3 style="font-size:22px; font-weight:700; margin-bottom:10px; color:#111827;"><?php echo esc_html($cat->type_name); ?></h3>
                            <p style="font-size:14px; color:#6B7280; line-height:1.6; margin-bottom:24px;">
                                <?php echo esc_html($cat->description ? $cat->description : 'Access tailored membership benefits and registration.'); ?>
                            </p>
                        </div>
                        <a href="<?php echo esc_url(add_query_arg('category_id', $cat->id, home_url('/membership-registration/'))); ?>" class="dmr-btn-solid" style="width:100%; text-align:center;">
                            Register as <?php echo esc_html($cat->type_name); ?> &rarr;
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center; color:#6B7280; grid-column:span 2;">No categories found.</p>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($current_page === 'contact'): ?>
    <!-- 4. Contact Us Page -->
    <div class="dmr-page-wrap">
        <div class="dmr-card" style="max-width:640px; margin:0 auto;">
            <div class="dmr-title-center">
                <span style="color:#D32F2F; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Get In Touch</span>
                <h1>Contact Us</h1>
                <p>Have questions regarding membership? Get in touch with our administrative desk.</p>
            </div>
            <div id="dmr-contact-alert" style="display:none; margin-bottom:18px;"></div>
            <form id="dmr-contact-form">
                <div style="margin-bottom:18px;">
                    <label style="display:block; font-size:13px; font-weight:700; color:#374151; margin-bottom:6px;">Your Name *</label>
                    <input type="text" name="name" class="dmr-input" required placeholder="Enter your full name" style="width:100%; height:44px; padding:0 14px; border:1px solid #D1D5DB; border-radius:6px;">
                </div>
                <div style="margin-bottom:18px;">
                    <label style="display:block; font-size:13px; font-weight:700; color:#374151; margin-bottom:6px;">Email Address *</label>
                    <input type="email" name="email" class="dmr-input" required placeholder="Enter your email" style="width:100%; height:44px; padding:0 14px; border:1px solid #D1D5DB; border-radius:6px;">
                </div>
                <div style="margin-bottom:24px;">
                    <label style="display:block; font-size:13px; font-weight:700; color:#374151; margin-bottom:6px;">Message *</label>
                    <textarea name="message" class="dmr-textarea" rows="4" required placeholder="How can we help you?" style="width:100%; padding:12px 14px; border:1px solid #D1D5DB; border-radius:6px;"></textarea>
                </div>
                <button type="submit" class="dmr-btn-solid" style="width:100%;" id="dmr-contact-btn">Send Message</button>
            </form>
        </div>
    </div>

<?php elseif ($current_page === 'login'): ?>
    <!-- 5. Member / Admin Login Page -->
    <div class="dmr-page-wrap">
        <div class="dmr-card" style="max-width:440px; margin:0 auto;">
            <div class="dmr-title-center" style="margin-bottom:24px;">
                <span style="display:inline-block; background:#FFEBEE; color:#D32F2F; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; padding:4px 12px; border-radius:9999px; margin-bottom:12px;">
                    Account Portal
                </span>
                <h1 style="font-size:26px;">Sign In</h1>
                <p style="font-size:14px;">Login to your Admin Dashboard or Member Profile</p>
            </div>

            <div id="dmr-login-alert" style="display:none;"></div>

            <form id="dmr-login-form">
                <div style="margin-bottom:18px;">
                    <label style="display:block; font-size:13px; font-weight:700; color:#374151; margin-bottom:6px;">Username or Email Address</label>
                    <input type="text" name="log" class="dmr-input" required placeholder="Enter username or email" style="width:100%; height:44px; padding:0 14px; border:1px solid #D1D5DB; border-radius:6px;">
                </div>
                <div style="margin-bottom:24px;">
                    <label style="display:block; font-size:13px; font-weight:700; color:#374151; margin-bottom:6px;">Password</label>
                    <input type="password" name="pwd" class="dmr-input" required placeholder="••••••••" style="width:100%; height:44px; padding:0 14px; border:1px solid #D1D5DB; border-radius:6px;">
                </div>
                <button type="submit" class="dmr-btn-solid" style="width:100%;" id="dmr-login-btn">
                    Sign In
                </button>
            </form>
        </div>
    </div>

<?php elseif ($current_page === 'register'): ?>
    <!-- 6. Registration Page with Sections & Form Fields -->
    <div class="dmr-page-wrap">
        <?php echo DMR()->render_register_shortcode(); ?>
    </div>
<?php endif; ?>

</body>
</html>
<?php
exit;

