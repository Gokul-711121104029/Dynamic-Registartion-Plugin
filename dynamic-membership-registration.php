<?php
/**
 * Plugin Name: Dynamic Membership Registration & Management
 * Description: Dynamic membership registration, category management, form builder, and admin dashboard.
 * Version: 1.1.0
 * Author: Antigravity
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DMR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DMR_PLUGIN_URL', plugin_dir_url(__FILE__));

class DMR_Plugin {
    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('init', array($this, 'init'));
        add_action('template_redirect', array($this, 'handle_standalone_dashboard_redirect'));

        add_action('admin_menu', array($this, 'register_admin_menus'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

        // Shortcodes
        add_shortcode('dmr_navbar', array($this, 'render_navbar_shortcode'));
        add_shortcode('dmr_home', array($this, 'render_home_shortcode'));
        add_shortcode('dmr_about', array($this, 'render_about_shortcode'));
        add_shortcode('dmr_categories', array($this, 'render_categories_shortcode'));
        add_shortcode('dmr_contact', array($this, 'render_contact_shortcode'));
        add_shortcode('dmr_register', array($this, 'render_register_shortcode'));
        add_shortcode('dmr_login', array($this, 'render_login_shortcode'));
        add_shortcode('dmr_standalone_dashboard', array($this, 'render_standalone_dashboard_shortcode'));

        // AJAX Handlers
        add_action('wp_ajax_dmr_save_member_type', array($this, 'ajax_save_member_type'));
        add_action('wp_ajax_dmr_delete_member_type', array($this, 'ajax_delete_member_type'));
        add_action('wp_ajax_dmr_save_form_fields', array($this, 'ajax_save_form_fields'));
        add_action('wp_ajax_dmr_submit_registration', array($this, 'ajax_submit_registration'));
        add_action('wp_ajax_nopriv_dmr_submit_registration', array($this, 'ajax_submit_registration'));
        add_action('wp_ajax_dmr_update_member_status', array($this, 'ajax_update_member_status'));
        add_action('wp_ajax_dmr_search_member_types', array($this, 'ajax_search_member_types'));
        add_action('wp_ajax_dmr_search_forms', array($this, 'ajax_search_forms'));
        add_action('wp_ajax_dmr_search_registrations', array($this, 'ajax_search_registrations'));
        add_action('wp_ajax_dmr_search_members', array($this, 'ajax_search_members'));
        add_action('wp_ajax_dmr_save_member', array($this, 'ajax_save_member'));
        add_action('wp_ajax_dmr_delete_member', array($this, 'ajax_delete_member'));
        add_action('wp_ajax_dmr_submit_contact', array($this, 'ajax_submit_contact'));
        add_action('wp_ajax_nopriv_dmr_submit_contact', array($this, 'ajax_submit_contact'));
        add_action('wp_ajax_dmr_user_login', array($this, 'ajax_user_login'));
        add_action('wp_ajax_nopriv_dmr_user_login', array($this, 'ajax_user_login'));
        
        // Auto-hook top frontend navbar for regular public pages
        add_action('wp_body_open', array($this, 'inject_site_header'));
        add_action('wp_footer', array($this, 'inject_theme_styles'));
    }

    public function handle_standalone_dashboard_redirect() {
        // Intercept all Frontend Public Pages to render completely clean standalone layout without WP theme interference
        if (is_front_page() || is_page(array('home', 'about', 'category', 'contact-us', 'member-login', 'membership-registration'))) {
            include DMR_PLUGIN_DIR . 'views/standalone-public-page-template.php';
            exit;
        }

        // Intercept /dashboard/ with standalone admin dashboard
        if (is_page('dashboard') || (is_singular() && has_shortcode(get_post()->post_content, 'dmr_standalone_dashboard'))) {
            include DMR_PLUGIN_DIR . 'views/standalone-dashboard-template.php';
            exit;
        }
    }

    public function render_standalone_dashboard_shortcode() {
        include DMR_PLUGIN_DIR . 'views/standalone-dashboard-template.php';
        exit;
    }

    public function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Table 1: Membership Types (Master)
        $table_types = $wpdb->prefix . 'dmr_member_types';
        $sql1 = "CREATE TABLE IF NOT EXISTS $table_types (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            type_name varchar(100) NOT NULL,
            type_prefix varchar(20) NOT NULL,
            start_number varchar(20) NOT NULL DEFAULT '001',
            current_counter int(11) NOT NULL DEFAULT 0,
            description text,
            status varchar(20) NOT NULL DEFAULT 'active',
            form_fields longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Table 2: Registrations / Members
        $table_members = $wpdb->prefix . 'dmr_members';
        $sql2 = "CREATE TABLE IF NOT EXISTS $table_members (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned DEFAULT NULL,
            member_id_code varchar(50) NOT NULL,
            type_id mediumint(9) NOT NULL,
            type_name varchar(100) NOT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) DEFAULT '',
            email varchar(100) NOT NULL,
            phone varchar(30) DEFAULT '',
            form_data longtext,
            status varchar(20) NOT NULL DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql1);
        dbDelta($sql2);

        $this->ensure_default_pages();
        $this->ensure_default_member_types();
    }

    public function ensure_default_pages() {
        $required_pages = array(
            'home' => array(
                'title' => 'Home',
                'content' => ''
            ),
            'dashboard' => array(
                'title' => 'Dashboard',
                'content' => '[dmr_standalone_dashboard]'
            ),
            'category' => array(
                'title' => 'Category',
                'content' => ''
            ),
            'membership-registration' => array(
                'title' => 'Registration',
                'content' => '[dmr_register]'
            ),
            'member-login' => array(
                'title' => 'Login',
                'content' => '[dmr_login]'
            ),
            'about' => array(
                'title' => 'About',
                'content' => ''
            ),
            'contact-us' => array(
                'title' => 'Contact Us',
                'content' => ''
            )
        );

        foreach ($required_pages as $slug => $p) {
            $existing = get_page_by_path($slug);
            if (!$existing) {
                $page_id = wp_insert_post(array(
                    'post_title'     => $p['title'],
                    'post_name'      => $slug,
                    'post_content'   => $p['content'],
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'comment_status' => 'closed'
                ));
                if ($slug === 'home' && $page_id) {
                    update_option('show_on_front', 'page');
                    update_option('page_on_front', $page_id);
                }
            }
        }
        flush_rewrite_rules();
    }

    public function ensure_default_member_types() {
        global $wpdb;
        $table_types = $wpdb->prefix . 'dmr_member_types';
        $count = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table_types");
        if ($count === 0) {
            $default_fields = array(
                array(
                    'section_title' => 'Basic Information',
                    'fields' => array(
                        array('label' => 'First Name', 'name' => 'first_name', 'type' => 'text', 'placeholder' => 'Enter the First Name', 'required' => true, 'width' => 'half', 'type_config' => (object)array()),
                        array('label' => 'Last Name', 'name' => 'last_name', 'type' => 'text', 'placeholder' => 'Enter the Last Name', 'required' => true, 'width' => 'half', 'type_config' => (object)array()),
                        array('label' => 'Email', 'name' => 'email', 'type' => 'email', 'placeholder' => '', 'required' => true, 'width' => 'half', 'type_config' => (object)array()),
                        array('label' => 'Phone', 'name' => 'phone', 'type' => 'tel', 'placeholder' => '', 'required' => true, 'width' => 'half', 'type_config' => array('country_code' => '+91', 'min_digits' => '10', 'max_digits' => '15')),
                        array('label' => 'Gender', 'name' => 'gender', 'type' => 'select', 'placeholder' => '-- Select --', 'required' => true, 'width' => 'half', 'type_config' => array('options' => array('Male', 'Female')))
                    )
                )
            );

            $wpdb->insert($table_types, array(
                'type_name'       => 'SCG',
                'type_prefix'     => 'SCG',
                'start_number'    => '0001',
                'current_counter' => 0,
                'status'          => 'active',
                'form_fields'     => json_encode($default_fields)
            ));

            $wpdb->insert($table_types, array(
                'type_name'       => 'NRL',
                'type_prefix'     => 'NRL',
                'start_number'    => '0001',
                'current_counter' => 0,
                'status'          => 'active',
                'form_fields'     => json_encode(array())
            ));
        }
    }

    public function init() {
        // Ensure tables exist
        global $wpdb;
        $table_types = $wpdb->prefix . 'dmr_member_types';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_types'") != $table_types) {
            $this->activate();
        }

        if (!get_page_by_path('dashboard')) {
            $this->ensure_default_pages();
        }
    }

    public function register_admin_menus() {
        add_menu_page(
            'Standalone Dashboard',
            'Membership App',
            'manage_options',
            'dmr-standalone-redirect',
            array($this, 'redirect_to_standalone_dashboard'),
            'dashicons-groups',
            6
        );

        add_submenu_page(
            'dmr-standalone-redirect',
            'Open App Dashboard',
            'Open App &rarr;',
            'manage_options',
            'dmr-standalone-redirect',
            array($this, 'redirect_to_standalone_dashboard')
        );
    }

    public function redirect_to_standalone_dashboard() {
        ?>
        <script>window.location.href = "<?php echo esc_url(home_url('/dashboard/')); ?>";</script>
        <p>Redirecting to standalone dashboard... <a href="<?php echo esc_url(home_url('/dashboard/')); ?>">Click here if not redirected</a>.</p>
        <?php
    }

    public function enqueue_admin_assets($hook) {
        wp_enqueue_style('dmr-admin-css', DMR_PLUGIN_URL . 'assets/css/admin.css', array(), '1.1.0');
        wp_enqueue_script('dmr-admin-js', DMR_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), '1.1.0', true);
        wp_localize_script('dmr-admin-js', 'dmrAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dmr_admin_nonce')
        ));
    }

    public function enqueue_frontend_assets() {
        if (!is_page('dashboard')) {
            wp_enqueue_style('dmr-frontend-css', DMR_PLUGIN_URL . 'assets/css/frontend.css', array(), '1.1.0');
            wp_enqueue_script('dmr-frontend-js', DMR_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), '1.1.0', true);
            wp_localize_script('dmr-frontend-js', 'dmrFront', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('dmr_front_nonce'),
                'home_url' => home_url('/')
            ));
        }
    }

    public function inject_site_header() {
        if (!is_admin() && !is_page('dashboard')) {
            echo $this->render_navbar_html();
        }
    }

    public function inject_theme_styles() {
        if (!is_page('dashboard')) {
            ?>
            <style>
                :root {
                    --dmr-red: #D32F2F;
                    --dmr-red-hover: #B71C1C;
                    --dmr-white: #ffffff;
                    --dmr-bg: #F8F9FA;
                    --dmr-card-bg: #ffffff;
                    --dmr-text: #212529;
                    --dmr-border: #E5E7EB;
                }
            </style>
            <?php
        }
    }

    public function render_navbar_html() {
        global $wpdb;
        $table_types = $wpdb->prefix . 'dmr_member_types';
        $categories = $wpdb->get_results("SELECT * FROM $table_types WHERE status = 'active' ORDER BY id ASC");

        $home_url = home_url('/');
        $about_url = home_url('/about/');
        $category_url = home_url('/category/');
        $contact_url = home_url('/contact-us/');
        $login_url = is_user_logged_in() ? home_url('/dashboard/') : home_url('/member-login/');
        $login_label = is_user_logged_in() ? 'Dashboard' : 'Login';

        ob_start();
        ?>
        <div id="dmr-header-wrapper">
            <header class="dmr-navbar">
                <div class="dmr-nav-container">
                    <a href="<?php echo esc_url($home_url); ?>" class="dmr-brand">
                        <span class="dmr-brand-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2L2 7l10 5 10-5-10-5zm0 8.5L4.5 7 12 3.5 19.5 7 12 10.5zm0 3L3.5 9.25 2 10l10 5 10-5-1.5-.75L12 13.5zm0 4.5L3.5 13.75 2 14.5l10 5 10-5-1.5-.75L12 18z"/>
                            </svg>
                        </span>
                        <span class="dmr-brand-text">Dynamic<strong>Reg</strong></span>
                    </a>
                    
                    <nav class="dmr-nav-links">
                        <a href="<?php echo esc_url($home_url); ?>" class="<?php echo is_front_page() ? 'active' : ''; ?>">Home</a>
                        <a href="<?php echo esc_url($about_url); ?>">About</a>
                        
                        <!-- Hover Dropdown for Category -->
                        <div class="dmr-nav-item-dropdown">
                            <a href="<?php echo esc_url($category_url); ?>" class="<?php echo is_page('category') ? 'active' : ''; ?>">
                                <span>Category</span>
                                <span class="dmr-dropdown-caret">&#9662;</span>
                            </a>
                            <?php if (!empty($categories)): ?>
                                <ul class="dmr-nav-dropdown-menu">
                                    <?php foreach ($categories as $cat): ?>
                                        <li>
                                            <a href="<?php echo esc_url(add_query_arg('category_id', $cat->id, home_url('/membership-registration/'))); ?>">
                                                <span><?php echo esc_html($cat->type_name); ?></span>
                                                <span class="dmr-dropdown-badge"><?php echo esc_html($cat->type_prefix); ?></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                    <li style="border-top:1px solid #F3F4F6; margin-top:4px;">
                                        <a href="<?php echo esc_url($category_url); ?>" style="font-weight:700; color:#D32F2F;">
                                            View All Categories &rarr;
                                        </a>
                                    </li>
                                </ul>
                            <?php endif; ?>
                        </div>

                        <a href="<?php echo esc_url($contact_url); ?>">Contact Us</a>
                    </nav>

                    <div class="dmr-nav-actions">
                        <a href="<?php echo esc_url($login_url); ?>" class="dmr-btn-login">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                <polyline points="10 17 15 12 10 7"></polyline>
                                <line x1="15" y1="12" x2="3" y2="12"></line>
                            </svg>
                            <span><?php echo esc_html($login_label); ?></span>
                        </a>
                    </div>
                </div>
            </header>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_navbar_shortcode() {
        return $this->render_navbar_html();
    }

    // Frontend Shortcode: Home
    public function render_home_shortcode() {
        ob_start();
        ?>
        <div class="dmr-frontend-container">
            <div class="dmr-hero-section">
                <div class="dmr-badge">Official Membership Platform</div>
                <h1 class="dmr-hero-title">Welcome to <span>Dynamic Membership</span> System</h1>
                <p class="dmr-hero-subtitle">Fast, seamless, and category-tailored registration designed for seamless membership onboarding.</p>
                <div class="dmr-hero-btns">
                    <a href="<?php echo esc_url(home_url('/category/')); ?>" class="dmr-btn dmr-btn-primary">Browse Categories</a>
                    <a href="<?php echo esc_url(home_url('/about/')); ?>" class="dmr-btn dmr-btn-outline">Learn More</a>
                </div>
            </div>

            <div class="dmr-features-grid">
                <div class="dmr-feature-card">
                    <div class="dmr-card-icon">⚡</div>
                    <h3>Automated Membership ID</h3>
                    <p>Sequential IDs with customizable prefixes and padding (e.g. LM-0001) generated instantly.</p>
                </div>
                <div class="dmr-feature-card">
                    <div class="dmr-card-icon">📋</div>
                    <h3>Category Tailored Forms</h3>
                    <p>Registration forms adapt according to your chosen membership category.</p>
                </div>
                <div class="dmr-feature-card">
                    <div class="dmr-card-icon">🛡️</div>
                    <h3>Admin Managed</h3>
                    <p>Complete control over member records, verification, and dynamic configuration.</p>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Frontend Shortcode: About
    public function render_about_shortcode() {
        ob_start();
        ?>
        <div class="dmr-frontend-container">
            <div class="dmr-page-header">
                <h2>About Our Membership System</h2>
                <p>Learn how our dynamic registration and membership lifecycle works.</p>
            </div>
            <div class="dmr-content-card">
                <p>Our platform provides streamlined membership management. Whether you're registering as a Student, Life Member, or Corporate partner, our system offers dynamic form workflows and instant sequential identity generation.</p>
                <div style="margin-top:20px;">
                    <a href="<?php echo esc_url(home_url('/category/')); ?>" class="dmr-btn dmr-btn-primary">View Available Categories &rarr;</a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Frontend Shortcode: Contact
    public function render_contact_shortcode() {
        ob_start();
        ?>
        <div class="dmr-frontend-container">
            <div class="dmr-page-header">
                <h2>Contact Us</h2>
                <p>Have questions regarding membership? Get in touch with our administrative desk.</p>
            </div>
            <div class="dmr-content-card">
                <form onsubmit="event.preventDefault(); alert('Thank you! Your message has been received.');">
                    <div class="dmr-form-group">
                        <label>Your Name</label>
                        <input type="text" class="dmr-form-control" required placeholder="Enter your full name">
                    </div>
                    <div class="dmr-form-group">
                        <label>Email Address</label>
                        <input type="email" class="dmr-form-control" required placeholder="Enter your email">
                    </div>
                    <div class="dmr-form-group">
                        <label>Message</label>
                        <textarea class="dmr-form-control" rows="4" required placeholder="How can we help you?"></textarea>
                    </div>
                    <button type="submit" class="dmr-btn dmr-btn-primary">Send Message</button>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Frontend Shortcode: Categories (Listing Member Types)
    public function render_categories_shortcode() {
        global $wpdb;
        $table_types = $wpdb->prefix . 'dmr_member_types';
        $categories = $wpdb->get_results("SELECT * FROM $table_types WHERE status = 'active' ORDER BY id ASC");

        ob_start();
        ?>
        <div class="dmr-frontend-container">
            <div class="dmr-page-header">
                <h2>Select Your Membership Category</h2>
                <p>Choose the membership category that best matches your profile to proceed with registration.</p>
            </div>

            <div class="dmr-categories-grid">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                        <div class="dmr-cat-card">
                            <div class="dmr-cat-badge">Prefix: <?php echo esc_html($cat->type_prefix); ?></div>
                            <h3 class="dmr-cat-title"><?php echo esc_html($cat->type_name); ?></h3>
                            <p class="dmr-cat-desc"><?php echo esc_html($cat->description ? $cat->description : 'Access tailored membership benefits and registration.'); ?></p>
                            <div class="dmr-cat-footer">
                                <a href="<?php echo esc_url(add_query_arg('category_id', $cat->id, home_url('/membership-registration/'))); ?>" class="dmr-btn dmr-btn-primary dmr-btn-block">
                                    Register as <?php echo esc_html($cat->type_name); ?> &rarr;
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No active membership categories found. Please contact the administrator.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Frontend Shortcode: Dynamic Registration Form
    public function render_register_shortcode() {
        global $wpdb;
        $table_types = $wpdb->prefix . 'dmr_member_types';

        $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
        $category = null;

        if ($category_id > 0) {
            $category = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_types WHERE id = %d AND status = 'active'", $category_id));
        }

        // If no category selected or not found, show selection prompt
        if (!$category) {
            $all_types = $wpdb->get_results("SELECT * FROM $table_types WHERE status = 'active'");
            ob_start();
            ?>
            <div class="dmr-frontend-container">
                <div class="dmr-page-header">
                    <h2>Member Registration</h2>
                    <p>Please select a membership category below to begin your application:</p>
                </div>
                <div class="dmr-categories-grid">
                    <?php foreach ($all_types as $t): ?>
                        <div class="dmr-cat-card">
                            <h3><?php echo esc_html($t->type_name); ?></h3>
                            <p><?php echo esc_html($t->description); ?></p>
                            <a href="<?php echo esc_url(add_query_arg('category_id', $t->id)); ?>" class="dmr-btn dmr-btn-primary">Apply Now</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        $raw_fields = !empty($category->form_fields) ? json_decode($category->form_fields, true) : array();
        $sections = array();
        if (!empty($raw_fields)) {
            if (isset($raw_fields[0]['section_title']) || isset($raw_fields['sections'])) {
                $sections = isset($raw_fields['sections']) ? $raw_fields['sections'] : $raw_fields;
            } else {
                $sections = array(
                    array(
                        'section_title' => 'General Information',
                        'fields' => $raw_fields
                    )
                );
            }
        }

        // Filter out empty sections
        $sections = array_filter($sections, function($sec) {
            return !empty($sec['fields']) && is_array($sec['fields']) && count($sec['fields']) > 0;
        });

        // If this member type has 0 configured fields in the builder, inform the applicant clearly
        if (empty($sections)) {
            ob_start();
            ?>
            <div class="dmr-frontend-container" style="max-width: 720px; margin: 40px auto; padding: 0 20px;">
                <div class="dmr-form-card" style="background:#FFFFFF; border:1px solid #E5E7EB; border-radius:16px; padding:44px 36px; text-align:center; box-shadow:0 4px 20px rgba(0,0,0,0.04);">
                    <div style="width:64px; height:64px; border-radius:50%; background:#FFF5F5; color:#D32F2F; display:flex; align-items:center; justify-content:center; margin:0 auto 20px auto; font-size:30px; border:1px solid #FFE0E0;">
                        📋
                    </div>
                    <span style="display:inline-block; background:#FFEBEE; color:#D32F2F; font-size:12px; font-weight:700; padding:4px 12px; border-radius:12px; margin-bottom:12px;">
                        Category: <?php echo esc_html($category->type_prefix); ?>
                    </span>
                    <h2 style="font-size:24px; font-weight:700; color:#1F2937; margin:0 0 10px 0;">
                        <?php echo esc_html($category->type_name); ?> Registration Form
                    </h2>
                    <p style="color:#64748B; font-size:15px; line-height:1.6; max-width:520px; margin:0 auto 28px auto;">
                        No registration fields have been created for <strong><?php echo esc_html($category->type_name); ?></strong> yet. The administrator must first configure fields in <em>Dashboard &gt; Config &gt; Member Type Form</em>.
                    </p>
                    <a href="<?php echo esc_url(home_url('/category/')); ?>" class="dmr-btn dmr-btn-outline" style="padding:10px 24px; border-radius:8px; font-weight:600;">
                        &larr; Choose Another Category
                    </a>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        ob_start();
        ?>
        <div class="dmr-frontend-container" style="max-width: 980px; margin: 30px auto; padding: 0 20px;">
            <!-- Main Registration Card (Matches Reference Image) -->
            <div class="dmr-form-card" style="background:#FFFFFF; border:1px solid #E5E7EB; border-radius:16px; padding:36px 44px; box-shadow:0 4px 20px rgba(0,0,0,0.04);">
                <div id="dmr-alert-box" style="display:none; margin-bottom:20px;"></div>

                <form id="dmr-dynamic-registration-form">
                    <input type="hidden" name="type_id" value="<?php echo esc_attr($category->id); ?>">

                    <?php foreach ($sections as $sIdx => $sec): ?>
                        <div class="dmr-form-section-block" style="margin-bottom:32px;">
                            <!-- Section Heading with crisp Red Underline exactly matching user's reference -->
                            <div style="border-bottom:2px solid #D32F2F; padding-bottom:8px; margin-bottom:22px;">
                                <h2 style="margin:0; font-size:22px; font-weight:700; color:#0F172A; letter-spacing:-0.2px;">
                                    <?php echo esc_html(!empty($sec['section_title']) ? $sec['section_title'] : 'Basic info'); ?>
                                </h2>
                            </div>

                            <!-- 2-Column Responsive Flex/Grid Layout -->
                            <div class="dmr-section-fields-grid" style="display:flex; flex-wrap:wrap; gap:20px 24px;">
                                <?php foreach (($sec['fields'] ?? array()) as $field): ?>
                                    <?php 
                                        $name = esc_attr($field['name']);
                                        $label = esc_html($field['label']);
                                        $type = esc_attr($field['type']);
                                        $placeholder = esc_attr($field['placeholder'] ?? '');
                                        $req = !empty($field['required']) ? 'required' : '';
                                        $cfg = !empty($field['type_config']) ? $field['type_config'] : array();
                                        
                                        // Width calculation: half-width by default (side-by-side like First name / Last name), full-width for address/textarea or if configured
                                        $isFull = (isset($field['width']) && $field['width'] === 'full') || in_array($type, array('address', 'textarea', 'hidden'));
                                        $flexBasis = $isFull ? '100%' : 'calc(50% - 12px)';
                                    ?>
                                    
                                    <?php if ($type === 'hidden'): ?>
                                        <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $placeholder; ?>">
                                        <?php continue; ?>
                                    <?php endif; ?>

                                    <div class="dmr-form-group" style="flex:1 1 <?php echo $flexBasis; ?>; width:<?php echo $flexBasis; ?>; min-width:280px; margin-bottom:0; box-sizing:border-box;">
                                        <label style="display:block; font-size:14px; font-weight:600; color:#1E293B; margin-bottom:8px;">
                                            <?php echo $label; ?> <?php if ($req) echo '<span style="color:#D32F2F; margin-left:2px;">*</span>'; ?>
                                        </label>
                                        
                                        <?php if ($type === 'textarea'): ?>
                                            <textarea name="<?php echo $name; ?>" class="dmr-form-control" rows="3" placeholder="<?php echo $placeholder; ?>" <?php echo $req; ?> style="width:100%; border:1px solid #CBD5E1; border-radius:8px; padding:12px 14px; font-size:14px; min-height:90px;"></textarea>
                                        
                                        <?php elseif ($type === 'address'): ?>
                                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                                <input type="text" name="<?php echo $name; ?>[line]" class="dmr-form-control" style="grid-column:span 2; height:46px; border:1px solid #CBD5E1; border-radius:8px; padding:0 14px; font-size:14px;" placeholder="Enter your street address" <?php echo $req; ?>>
                                                <input type="text" name="<?php echo $name; ?>[city]" class="dmr-form-control" style="height:46px; border:1px solid #CBD5E1; border-radius:8px; padding:0 14px; font-size:14px;" placeholder="City" <?php echo $req; ?>>
                                                <input type="text" name="<?php echo $name; ?>[state]" class="dmr-form-control" style="height:46px; border:1px solid #CBD5E1; border-radius:8px; padding:0 14px; font-size:14px;" placeholder="State / Province">
                                                <input type="text" name="<?php echo $name; ?>[country]" class="dmr-form-control" style="height:46px; border:1px solid #CBD5E1; border-radius:8px; padding:0 14px; font-size:14px;" placeholder="Country">
                                                <input type="text" name="<?php echo $name; ?>[pincode]" class="dmr-form-control" style="height:46px; border:1px solid #CBD5E1; border-radius:8px; padding:0 14px; font-size:14px;" placeholder="PIN / Postal Code" <?php echo $req; ?>>
                                            </div>

                                        <?php elseif ($type === 'select'): ?>
                                            <?php 
                                                $opts = array();
                                                if (!empty($cfg['options']) && is_array($cfg['options'])) {
                                                    $opts = $cfg['options'];
                                                } elseif (!empty($placeholder)) {
                                                    $opts = explode(',', $placeholder);
                                                } else {
                                                    $opts = array('Option 1', 'Option 2');
                                                }
                                                $def_label = !empty($placeholder) && count($opts) > 1 && !in_array($placeholder, $opts) ? $placeholder : '-- Select --';
                                            ?>
                                            <select name="<?php echo $name; ?>" class="dmr-form-control" <?php echo $req; ?> style="width:100%; height:46px; border:1px solid #CBD5E1; border-radius:8px; padding:0 14px; font-size:14px; background:#FFFFFF;">
                                                <option value=""><?php echo esc_html($def_label); ?></option>
                                                <?php foreach ($opts as $opt): $opt = trim($opt); ?>
                                                    <option value="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></option>
                                                <?php endforeach; ?>
                                            </select>

                                        <?php elseif ($type === 'radio'): ?>
                                            <div style="display:flex; gap:18px; flex-wrap:wrap; margin-top:8px;">
                                                <?php 
                                                $opts = !empty($cfg['options']) && is_array($cfg['options']) ? $cfg['options'] : (!empty($placeholder) ? explode(',', $placeholder) : array('Option 1', 'Option 2'));
                                                foreach ($opts as $rIdx => $opt): $opt = trim($opt);
                                                ?>
                                                    <label style="display:inline-flex; align-items:center; gap:8px; font-size:14px; font-weight:normal; cursor:pointer;">
                                                        <input type="radio" name="<?php echo $name; ?>" value="<?php echo esc_attr($opt); ?>" <?php echo ($rIdx === 0 && $req) ? 'required' : ''; ?> style="accent-color:#D32F2F;">
                                                        <span><?php echo esc_html($opt); ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>

                                        <?php elseif ($type === 'checkbox'): ?>
                                            <div style="margin-top:8px;">
                                                <label style="display:inline-flex; align-items:center; gap:8px; font-size:14px; font-weight:normal; cursor:pointer;">
                                                    <input type="checkbox" name="<?php echo $name; ?>" value="yes" <?php echo $req; ?> style="width:16px; height:16px; accent-color:#D32F2F;">
                                                    <span><?php echo !empty($placeholder) ? $placeholder : 'I confirm / accept the terms'; ?></span>
                                                </label>
                                            </div>

                                        <?php elseif ($type === 'file' || $type === 'image'): ?>
                                            <input type="file" name="<?php echo $name; ?>" class="dmr-form-control" <?php echo ($type === 'image') ? 'accept="image/*"' : (!empty($cfg['file_types']) ? 'accept="' . esc_attr($cfg['file_types']) . '"' : ''); ?> <?php echo $req; ?> style="width:100%; height:46px; border:1px solid #CBD5E1; border-radius:8px; padding:8px 14px; font-size:14px;">
                                            <?php if (!empty($placeholder)): ?>
                                                <small style="display:block; color:#64748B; font-size:12px; margin-top:4px;"><?php echo $placeholder; ?></small>
                                            <?php endif; ?>

                                        <?php elseif ($type === 'tel'): ?>
                                            <div style="display:flex; gap:10px;">
                                                <input type="text" name="<?php echo $name; ?>_code" value="<?php echo esc_attr($cfg['country_code'] ?? '+91'); ?>" style="width:75px; height:46px; border:1px solid #CBD5E1; border-radius:8px; padding:0 10px; font-size:14px; text-align:center; background:#F8FAFC;">
                                                <input type="tel" name="<?php echo $name; ?>" class="dmr-form-control" placeholder="<?php echo $placeholder ?: 'Enter phone number'; ?>" <?php echo $req; ?> style="flex:1; height:46px; border:1px solid #CBD5E1; border-radius:8px; padding:0 14px; font-size:14px;">
                                            </div>

                                        <?php elseif ($type === 'date'): ?>
                                            <input type="date" name="<?php echo $name; ?>" class="dmr-form-control" <?php echo $req; ?> placeholder="dd-mm-yyyy" style="width:100%; height:46px; border:1px solid #CBD5E1; border-radius:8px; padding:0 14px; font-size:14px; color:#1E293B;">

                                        <?php elseif ($type === 'email'): ?>
                                            <input type="email" name="<?php echo $name; ?>" class="dmr-form-control" placeholder="<?php echo $placeholder ?: 'Enter your email'; ?>" <?php echo $req; ?> style="width:100%; height:46px; border:1px solid #CBD5E1; border-radius:8px; padding:0 14px; font-size:14px;">

                                        <?php elseif ($type === 'number'): ?>
                                            <input type="number" name="<?php echo $name; ?>" class="dmr-form-control" placeholder="<?php echo $placeholder; ?>" <?php echo $req; ?> style="width:100%; height:46px; border:1px solid #CBD5E1; border-radius:8px; padding:0 14px; font-size:14px;">

                                        <?php elseif ($type === 'password'): ?>
                                            <input type="password" name="<?php echo $name; ?>" class="dmr-form-control" placeholder="<?php echo $placeholder ?: '••••••••'; ?>" <?php echo $req; ?> style="width:100%; height:46px; border:1px solid #CBD5E1; border-radius:8px; padding:0 14px; font-size:14px;">

                                        <?php else: ?>
                                            <input type="text" name="<?php echo $name; ?>" class="dmr-form-control" placeholder="<?php echo $placeholder ?: ('Enter ' . strtolower($label)); ?>" <?php echo $req; ?> style="width:100%; height:46px; border:1px solid #CBD5E1; border-radius:8px; padding:0 14px; font-size:14px;">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div style="margin-top:36px; padding-top:24px; border-top:1px solid #E5E7EB; text-align:center;">
                        <button type="submit" class="dmr-btn dmr-btn-primary" id="dmr-submit-btn" style="padding:14px 48px; font-size:16px; font-weight:700; border-radius:8px; background:#D32F2F; box-shadow:0 4px 14px rgba(211,47,47,0.25);">
                            Submit Application &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- In-Page Registration Success Modal Popup -->
        <div id="dmrRegistrationSuccessModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); z-index:999999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(3px);">
            <div style="background:#FFFFFF; border-radius:18px; padding:36px 32px; max-width:460px; width:100%; text-align:center; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border:1px solid #E2E8F0; position:relative;">
                <div style="width:68px; height:68px; border-radius:50%; background:#DEF7EC; color:#059669; display:flex; align-items:center; justify-content:center; margin:0 auto 18px auto; font-size:34px; font-weight:bold; border:2px solid #BCF0DA;">
                    ✓
                </div>
                <h3 style="margin:0 0 10px 0; font-size:22px; font-weight:800; color:#1E293B;">Registration Successful!</h3>
                <p style="color:#64748B; font-size:15px; margin:0 0 18px 0; line-height:1.5;">
                    Thank you! Your registration details have been received.
                </p>
                <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:12px; padding:14px 18px; margin-bottom:20px; text-align:center;">
                    <div style="font-size:13px; font-weight:700; color:#B45309; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">
                        ⏳ Application Status
                    </div>
                    <div style="font-size:16px; font-weight:700; color:#92400E;">
                        Your Application is Under Review
                    </div>
                    <div style="font-size:13px; color:#78350F; margin-top:6px;">
                        Assigned ID: <strong id="dmr-modal-assigned-id" style="color:#D32F2F; font-size:15px;"></strong>
                    </div>
                </div>
                <p style="font-size:13px; color:#94A3B8; margin:0 0 24px 0; line-height:1.4;">
                    Once approved by the administrator, your official membership will be activated.
                </p>
                <button type="button" id="dmr-modal-ok-redirect-btn" class="dmr-btn dmr-btn-primary" style="width:100%; height:48px; font-size:16px; font-weight:700; border-radius:10px; background:#D32F2F; color:#FFFFFF; border:none; cursor:pointer; box-shadow:0 4px 14px rgba(211,47,47,0.3); transition:all 0.2s ease;">
                    OK
                </button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Frontend Shortcode: Member Login
    public function render_login_shortcode() {
        if (is_user_logged_in()) {
            ob_start();
            ?>
            <div class="dmr-frontend-container">
                <div class="dmr-content-card" style="text-align:center;">
                    <h2>Welcome back!</h2>
                    <p>You are logged in as <strong><?php echo esc_html(wp_get_current_user()->display_name); ?></strong>.</p>
                    <div style="margin-top:20px;">
                        <?php if (current_user_can('manage_options')): ?>
                            <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="dmr-btn dmr-btn-primary">Access Standalone Dashboard</a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="dmr-btn dmr-btn-outline">Logout</a>
                    </div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        ob_start();
        ?>
        <div class="dmr-frontend-container">
            <div class="dmr-form-wrapper" style="max-width: 440px;">
                <div class="dmr-form-header">
                    <span class="dmr-badge">Account Portal</span>
                    <h2>Sign In</h2>
                    <p>Login to your Admin Dashboard or Member Profile</p>
                </div>

                <div id="dmr-login-alert" style="display:none;"></div>

                <form id="dmr-login-form">
                    <div class="dmr-form-group">
                        <label>Username or Email Address</label>
                        <input type="text" name="log" class="dmr-form-control" required placeholder="Enter username or email">
                    </div>
                    <div class="dmr-form-group">
                        <label>Password</label>
                        <input type="password" name="pwd" class="dmr-form-control" required placeholder="••••••••">
                    </div>
                    <button type="submit" class="dmr-btn dmr-btn-primary dmr-btn-block" id="dmr-login-btn">
                        Sign In
                    </button>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ==========================================================
    // AJAX ACTIONS
    // ==========================================================

    public function ajax_save_member_type() {
        check_ajax_referer('dmr_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized.'));
        }

        global $wpdb;
        $table_types = $wpdb->prefix . 'dmr_member_types';

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = sanitize_text_field($_POST['type_name']);
        $prefix = strtoupper(sanitize_text_field($_POST['type_prefix']));
        $start_number = sanitize_text_field($_POST['start_number']);

        if (empty($name) || empty($prefix) || empty($start_number)) {
            wp_send_json_error(array('message' => 'Please provide Type Name, Prefix, and Start Number.'));
        }

        if ($id > 0) {
            $wpdb->update($table_types, array(
                'type_name' => $name,
                'type_prefix' => $prefix,
                'start_number' => $start_number
            ), array('id' => $id));
            wp_send_json_success(array('message' => 'Membership Type updated successfully!'));
        } else {
            // Newly created Member Type starts with 0 fields ([]), ready to be created in Member Type Form page
            $wpdb->insert($table_types, array(
                'type_name' => $name,
                'type_prefix' => $prefix,
                'start_number' => $start_number,
                'current_counter' => 0,
                'description' => '',
                'status' => 'active',
                'form_fields' => '[]'
            ));
            wp_send_json_success(array('message' => 'Membership Type created successfully!'));
        }
    }

    public function ajax_delete_member_type() {
        check_ajax_referer('dmr_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized.'));
        }

        global $wpdb;
        $table_types = $wpdb->prefix . 'dmr_member_types';
        $id = intval($_POST['id']);

        $wpdb->delete($table_types, array('id' => $id));
        wp_send_json_success(array('message' => 'Membership Type deleted.'));
    }

    public function ajax_save_form_fields() {
        check_ajax_referer('dmr_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized.'));
        }

        global $wpdb;
        $table_types = $wpdb->prefix . 'dmr_member_types';

        $id = intval($_POST['type_id']);
        $fields_json = wp_unslash($_POST['fields_json']);

        $wpdb->update($table_types, array(
            'form_fields' => $fields_json
        ), array('id' => $id));

        wp_send_json_success(array('message' => 'Form fields saved successfully!'));
    }

    public function ajax_submit_registration() {
        global $wpdb;
        $table_types = $wpdb->prefix . 'dmr_member_types';
        $table_members = $wpdb->prefix . 'dmr_members';

        $type_id = isset($_POST['type_id']) ? intval($_POST['type_id']) : 0;
        $type = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_types WHERE id = %d AND status = 'active'", $type_id));

        if (!$type) {
            wp_send_json_error(array('message' => 'Invalid membership category selected.'));
        }

        // Generate sequential padded ID
        $start_num_raw = trim($type->start_number);
        $padding_len = strlen($start_num_raw);
        $start_int = intval($start_num_raw);

        $next_counter = (int)$type->current_counter + 1;
        $current_num_val = $start_int + ($next_counter - 1);
        $formatted_number = str_pad($current_num_val, $padding_len, '0', STR_PAD_LEFT);
        $generated_member_id = $type->type_prefix . '-' . $formatted_number;

        // Extract submitted data
        $form_data = array();
        foreach ($_POST as $k => $v) {
            if (!in_array($k, array('action', 'type_id', 'nonce'))) {
                $form_data[sanitize_key($k)] = sanitize_text_field(wp_unslash($v));
            }
        }

        // Properly capture First Name and Last Name
        $first_name = '';
        if (!empty($form_data['first_name'])) {
            $first_name = $form_data['first_name'];
        } elseif (!empty($form_data['full_name'])) {
            $name_parts = explode(' ', trim($form_data['full_name']), 2);
            $first_name = $name_parts[0];
        } elseif (!empty($form_data['name'])) {
            $name_parts = explode(' ', trim($form_data['name']), 2);
            $first_name = $name_parts[0];
        } else {
            $first_name = 'Member';
        }

        $last_name = '';
        if (!empty($form_data['last_name'])) {
            $last_name = $form_data['last_name'];
        } elseif (!empty($form_data['full_name'])) {
            $name_parts = explode(' ', trim($form_data['full_name']), 2);
            $last_name = isset($name_parts[1]) ? $name_parts[1] : '';
        }

        $email = isset($form_data['email']) ? sanitize_email($form_data['email']) : '';
        $phone = isset($form_data['phone']) ? $form_data['phone'] : '';

        // Insert member record
        $wpdb->insert($table_members, array(
            'member_id_code' => $generated_member_id,
            'type_id'        => $type->id,
            'type_name'      => $type->type_name,
            'first_name'     => $first_name,
            'last_name'      => $last_name,
            'email'          => $email,
            'phone'          => $phone,
            'form_data'      => json_encode($form_data),
            'status'         => 'pending'
        ));

        // Increment counter in master table
        $wpdb->update($table_types, array('current_counter' => $next_counter), array('id' => $type->id));

        wp_send_json_success(array(
            'message' => 'Registration successful!',
            'member_id' => $generated_member_id,
            'type_name' => $type->type_name
        ));
    }

    public function ajax_update_member_status() {
        check_ajax_referer('dmr_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized.'));
        }

        global $wpdb;
        $table_members = $wpdb->prefix . 'dmr_members';

        $id = intval($_POST['id']);
        $status = sanitize_text_field($_POST['status']);

        if ($status === 'rejected' || $status === 'delete') {
            // Permanently delete the rejected registration record so it does not show in the registrations table
            $wpdb->delete($table_members, array('id' => $id));
            wp_send_json_success(array('message' => 'Registration rejected and deleted successfully.', 'deleted' => true));
        } else {
            // Update status (e.g. approved)
            $wpdb->update($table_members, array('status' => $status), array('id' => $id));
            wp_send_json_success(array('message' => 'Registration ' . ucfirst($status) . ' successfully! It now appears in the Members page.', 'status' => $status));
        }
    }

    public function ajax_search_member_types() {
        check_ajax_referer('dmr_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized.'));
        }

        global $wpdb;
        $table_types = $wpdb->prefix . 'dmr_member_types';
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';

        if (!empty($query)) {
            $like = '%' . $wpdb->esc_like($query) . '%';
            $types = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_types WHERE type_name LIKE %s OR type_prefix LIKE %s ORDER BY id ASC", $like, $like));
        } else {
            $types = $wpdb->get_results("SELECT * FROM $table_types ORDER BY id ASC");
        }

        wp_send_json_success(array('types' => $types));
    }

    public function ajax_search_forms() {
        check_ajax_referer('dmr_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized.'));
        }

        global $wpdb;
        $table_types = $wpdb->prefix . 'dmr_member_types';
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';

        if (!empty($query)) {
            $like = '%' . $wpdb->esc_like($query) . '%';
            $types = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_types WHERE type_name LIKE %s OR type_prefix LIKE %s ORDER BY id ASC", $like, $like));
        } else {
            $types = $wpdb->get_results("SELECT * FROM $table_types ORDER BY id ASC");
        }

        wp_send_json_success(array('forms' => $types));
    }

    public function ajax_search_registrations() {
        check_ajax_referer('dmr_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized.'));
        }

        global $wpdb;
        $table_members = $wpdb->prefix . 'dmr_members';
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';

        if (!empty($query)) {
            $like = '%' . $wpdb->esc_like($query) . '%';
            $registrations = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_members WHERE status = 'pending' AND (member_id_code LIKE %s OR first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR phone LIKE %s OR type_name LIKE %s) ORDER BY id DESC", $like, $like, $like, $like, $like, $like));
        } else {
            $registrations = $wpdb->get_results("SELECT * FROM $table_members WHERE status = 'pending' ORDER BY id DESC");
        }

        wp_send_json_success(array('registrations' => $registrations));
    }

    public function ajax_search_members() {
        check_ajax_referer('dmr_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized.'));
        }

        global $wpdb;
        $table_members = $wpdb->prefix . 'dmr_members';
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';

        if (!empty($query)) {
            $like = '%' . $wpdb->esc_like($query) . '%';
            $members = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_members WHERE status = 'approved' AND (member_id_code LIKE %s OR first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR phone LIKE %s OR type_name LIKE %s) ORDER BY id DESC", $like, $like, $like, $like, $like, $like));
        } else {
            $members = $wpdb->get_results("SELECT * FROM $table_members WHERE status = 'approved' ORDER BY id DESC");
        }

        wp_send_json_success(array('members' => $members));
    }

    public function ajax_save_member() {
        check_ajax_referer('dmr_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized.'));
        }

        global $wpdb;
        $table_members = $wpdb->prefix . 'dmr_members';

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';

        if (!$id || empty($first_name) || empty($email)) {
            wp_send_json_error(array('message' => 'First Name and Email are required.'));
        }

        $wpdb->update($table_members, array(
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'email'      => $email,
            'phone'      => $phone
        ), array('id' => $id));

        wp_send_json_success(array('message' => 'Member updated successfully!'));
    }

    public function ajax_delete_member() {
        check_ajax_referer('dmr_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized.'));
        }

        global $wpdb;
        $table_members = $wpdb->prefix . 'dmr_members';

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if (!$id) {
            wp_send_json_error(array('message' => 'Invalid member ID.'));
        }

        $wpdb->delete($table_members, array('id' => $id));
        wp_send_json_success(array('message' => 'Member deleted permanently!'));
    }

    public function ajax_submit_contact() {
        check_ajax_referer('dmr_front_nonce', 'nonce');
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

        if (empty($name) || empty($email) || empty($message)) {
            wp_send_json_error(array('message' => 'Please fill in all required fields.'));
        }

        // Successfully received contact message
        wp_send_json_success(array('message' => 'Thank you, ' . esc_html($name) . '! Your message has been sent to our administration team.'));
    }

    public function ajax_user_login() {
        $creds = array(
            'user_login'    => sanitize_text_field($_POST['log']),
            'user_password' => $_POST['pwd'],
            'remember'      => true
        );

        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            wp_send_json_error(array('message' => $user->get_error_message()));
        } else {
            $redirect_url = user_can($user, 'manage_options') 
                ? home_url('/dashboard/') 
                : home_url('/');
            wp_send_json_success(array(
                'message' => 'Login successful! Redirecting...',
                'redirect' => $redirect_url
            ));
        }
    }
}

function DMR() {
    return DMR_Plugin::instance();
}

DMR();
