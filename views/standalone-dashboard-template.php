<?php
/**
 * Standalone Custom Admin Dashboard Template
 * Completely independent of wp-admin shell
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure user is logged in and authorized
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_redirect(home_url('/member-login/'));
    exit;
}

global $wpdb;
$table_types = $wpdb->prefix . 'dmr_member_types';
$table_members = $wpdb->prefix . 'dmr_members';

// Current active view
$current_view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'dashboard';

// Statistics
$total_members = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table_members WHERE status = 'approved'");
$pending_reg = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table_members WHERE status = 'pending'");
$total_types = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table_types");
$all_submissions = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table_members");

$current_user = wp_get_current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard &mdash; Dynamic Registration</title>
    <link rel="stylesheet" href="<?php echo esc_url(DMR_PLUGIN_URL . 'assets/css/standalone-dashboard.css?v=4.1'); ?>">
    <link rel="stylesheet" href="<?php echo esc_url(DMR_PLUGIN_URL . 'assets/css/admin.css?v=4.1'); ?>">
    <link rel="stylesheet" href="<?php echo esc_url(DMR_PLUGIN_URL . 'assets/css/builder.css?v=4.1'); ?>">
    <script src="<?php echo esc_url(includes_url('/js/jquery/jquery.min.js')); ?>"></script>
    <script src="<?php echo esc_url(includes_url('/js/jquery/ui/core.min.js')); ?>"></script>
    <script src="<?php echo esc_url(includes_url('/js/jquery/ui/mouse.min.js')); ?>"></script>
    <script src="<?php echo esc_url(includes_url('/js/jquery/ui/sortable.min.js')); ?>"></script>
    <script src="<?php echo esc_url(includes_url('/js/jquery/ui/draggable.min.js')); ?>"></script>
    <script src="<?php echo esc_url(includes_url('/js/jquery/ui/droppable.min.js')); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        var dmrAdmin = {
            ajax_url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
            nonce: "<?php echo wp_create_nonce('dmr_admin_nonce'); ?>"
        };
    </script>
    <script src="<?php echo esc_url(DMR_PLUGIN_URL . 'assets/js/admin.js?v=4.1'); ?>"></script>
    <script>
        function toggleConfigSubmenu(el) {
            var item = jQuery(el).closest('.dmr-menu-item');
            item.toggleClass('open');
        }
    </script>
</head>
<body class="dmr-dashboard-body">

<div class="dmr-app-container">
    <!-- Top Bar Header (Matches reference image) -->
    <header class="dmr-topbar">
        <div class="dmr-topbar-left">
            <button type="button" class="dmr-mobile-nav-toggle" onclick="toggleMobileSidebar()" aria-label="Toggle Navigation">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="dmr-topbar-logo">
                <span class="dmr-topbar-logo-icon">DR</span>
                <span class="dmr-topbar-title">Dashboard</span>
            </a>
        </div>
        <div class="dmr-topbar-right">
            <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="dmr-btn-logout">Logout</a>
        </div>
    </header>

    <div class="dmr-sidebar-overlay" onclick="closeMobileSidebar()"></div>

    <div class="dmr-app-body">
        <!-- Sidebar Navigation (Matches reference image) -->
        <aside class="dmr-sidebar">
            <div class="dmr-sidebar-user">
                <div class="dmr-user-avatar">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="dmr-user-meta">
                    <h4><?php echo esc_html($current_user->display_name ? $current_user->display_name : 'Super Admin'); ?></h4>
                    <span>Super Admin</span>
                </div>
            </div>

            <ul class="dmr-sidebar-menu">
                <li class="dmr-menu-item <?php echo ($current_view === 'dashboard') ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url(home_url('/dashboard/?view=dashboard')); ?>">
                        <span class="dmr-menu-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="dmr-menu-item <?php echo ($current_view === 'registrations') ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url(home_url('/dashboard/?view=registrations')); ?>">
                        <span class="dmr-menu-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                                <line x1="20" y1="8" x2="20" y2="14"></line>
                                <line x1="23" y1="11" x2="17" y2="11"></line>
                            </svg>
                        </span>
                        <span>Registrations</span>
                    </a>
                </li>
                <li class="dmr-menu-item <?php echo ($current_view === 'members') ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url(home_url('/dashboard/?view=members')); ?>">
                        <span class="dmr-menu-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </span>
                        <span>Members</span>
                    </a>
                </li>
                <?php 
                    $is_config_active = in_array($current_view, array('config_master', 'config_forms'));
                ?>
                <li class="dmr-menu-item has-submenu <?php echo $is_config_active ? 'open active' : ''; ?>">
                    <a href="javascript:void(0);" onclick="toggleConfigSubmenu(this)">
                        <span style="display:flex; align-items:center; gap:12px;">
                            <span class="dmr-menu-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                </svg>
                            </span>
                            <span>Config</span>
                        </span>
                        <span class="dmr-submenu-arrow">▶</span>
                    </a>
                    <ul class="dmr-submenu">
                        <li class="dmr-submenu-item <?php echo ($current_view === 'config_master') ? 'active' : ''; ?>">
                            <a href="<?php echo esc_url(home_url('/dashboard/?view=config_master')); ?>" onclick="closeMobileSidebar()">
                                &bull; Master
                            </a>
                        </li>
                        <li class="dmr-submenu-item <?php echo ($current_view === 'config_forms') ? 'active' : ''; ?>">
                            <a href="<?php echo esc_url(home_url('/dashboard/?view=config_forms')); ?>" onclick="closeMobileSidebar()">
                                &bull; Member Type Form
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="dmr-menu-item">
                    <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" style="color:#DC2626;">
                        <span class="dmr-menu-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                        </span>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Workspace Stage -->
        <main class="dmr-main-stage">
            <?php if ($current_view === 'dashboard'): ?>
                <!-- Dashboard Overview Module -->
                <div class="dmr-stage-header">
                    <h2>Dashboard overview</h2>
                </div>

                <div class="dmr-stats-deck">
                    <div class="dmr-metric-box">
                        <div class="dmr-metric-circle">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="dmr-metric-details">
                            <h5>TOTAL MEMBERS</h5>
                            <div class="dmr-number"><?php echo esc_html($total_members); ?></div>
                        </div>
                    </div>
                    <div class="dmr-metric-box">
                        <div class="dmr-metric-circle">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="dmr-metric-details">
                            <h5>PENDING REGISTRATIONS</h5>
                            <div class="dmr-number"><?php echo esc_html($pending_reg); ?></div>
                        </div>
                    </div>
                    <div class="dmr-metric-box">
                        <div class="dmr-metric-circle">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                <line x1="7" y1="7" x2="7.01" y2="7"></line>
                            </svg>
                        </div>
                        <div class="dmr-metric-details">
                            <h5>CONFIGURED CATEGORIES</h5>
                            <div class="dmr-number"><?php echo esc_html($total_types); ?></div>
                        </div>
                    </div>
                    <div class="dmr-metric-box">
                        <div class="dmr-metric-circle">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </div>
                        <div class="dmr-metric-details">
                            <h5>TOTAL SUBMISSIONS</h5>
                            <div class="dmr-number"><?php echo esc_html($all_submissions); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Interactive Graphs: Line Chart, Pie Chart & Bar Chart (Full Width Layout) -->
                <!-- Graph 1: Member Growth (last 12 months) -->
                <div class="dmr-content-card" style="margin-bottom: 24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
                        <h3 style="margin:0; font-size:18px; font-weight:700; color:#1F2937;">Member growth (last 12 months)</h3>
                        <span id="dmr-chart-loading" style="font-size:12px; color:#9CA3AF; display:none;"><span class="dmr-table-spinner" style="width:14px; height:14px; margin-right:4px;"></span> Loading live data...</span>
                    </div>
                    <div style="position:relative; width:100%; height:280px;">
                        <canvas id="dmrGrowthChart"></canvas>
                    </div>
                </div>

                <!-- Graph 2: Pie Chart (Member Status Distribution - Full Width) -->
                <div class="dmr-content-card" style="margin-bottom: 24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                        <h3 style="margin:0; font-size:18px; font-weight:700; color:#1F2937;">Member Status Distribution</h3>
                        <span id="dmr-pie-loading" style="font-size:12px; color:#9CA3AF; display:none;"><span class="dmr-table-spinner" style="width:14px; height:14px; margin-right:4px;"></span> Loading...</span>
                    </div>
                    <div style="position:relative; width:100%; height:320px; display:flex; justify-content:center; align-items:center;">
                        <canvas id="dmrStatusPieChart"></canvas>
                    </div>
                </div>

                <!-- Graph 3: Bar Chart (Members by Category / Master Type - Full Width) -->
                <div class="dmr-content-card" style="margin-bottom: 24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                        <h3 style="margin:0; font-size:18px; font-weight:700; color:#1F2937;">Members by Category (Master Type)</h3>
                        <span id="dmr-bar-loading" style="font-size:12px; color:#9CA3AF; display:none;"><span class="dmr-table-spinner" style="width:14px; height:14px; margin-right:4px;"></span> Loading...</span>
                    </div>
                    <div style="position:relative; width:100%; height:320px;">
                        <canvas id="dmrTypeBarChart"></canvas>
                    </div>
                </div>

            <?php elseif ($current_view === 'registrations'): ?>
                <!-- Registrations Module (Only Pending Applications) -->
                <?php 
                    $registrations = $wpdb->get_results("SELECT * FROM $table_members WHERE status = 'pending' ORDER BY id DESC");
                    include DMR_PLUGIN_DIR . 'views/admin-registrations.php';
                ?>

            <?php elseif ($current_view === 'members'): ?>
                <!-- Members Module -->
                <?php 
                    $members = $wpdb->get_results("SELECT * FROM $table_members WHERE status = 'approved' ORDER BY id DESC");
                    include DMR_PLUGIN_DIR . 'views/admin-members.php';
                ?>

            <?php elseif ($current_view === 'config_master'): ?>
                <!-- Config > Master Page -->
                <?php 
                    $types = $wpdb->get_results("SELECT * FROM $table_types ORDER BY id ASC");
                    include DMR_PLUGIN_DIR . 'views/admin-config-master.php';
                ?>

            <?php elseif ($current_view === 'config_forms'): ?>
                <!-- Config > Member Type Form Page -->
                <?php 
                    $types = $wpdb->get_results("SELECT * FROM $table_types ORDER BY id ASC");
                    include DMR_PLUGIN_DIR . 'views/admin-config-forms.php';
                ?>

            <?php elseif ($current_view === 'form_builder'): ?>
                <!-- Full Page Section & Field Builder -->
                <?php 
                    include DMR_PLUGIN_DIR . 'views/admin-config-form-builder-page.php';
                ?>

            <?php endif; ?>
        </main>
    </div>
</div>

</body>
</html>
<?php
exit;

