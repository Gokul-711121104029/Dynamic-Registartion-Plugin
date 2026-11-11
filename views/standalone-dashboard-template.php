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
    <link rel="stylesheet" href="<?php echo esc_url(DMR_PLUGIN_URL . 'assets/css/standalone-dashboard.css?v=2.5'); ?>">
    <link rel="stylesheet" href="<?php echo esc_url(DMR_PLUGIN_URL . 'assets/css/admin.css?v=2.5'); ?>">
    <link rel="stylesheet" href="<?php echo esc_url(DMR_PLUGIN_URL . 'assets/css/builder.css?v=2.5'); ?>">
    <script src="<?php echo esc_url(includes_url('/js/jquery/jquery.min.js')); ?>"></script>
    <script src="<?php echo esc_url(includes_url('/js/jquery/ui/core.min.js')); ?>"></script>
    <script src="<?php echo esc_url(includes_url('/js/jquery/ui/mouse.min.js')); ?>"></script>
    <script src="<?php echo esc_url(includes_url('/js/jquery/ui/sortable.min.js')); ?>"></script>
    <script src="<?php echo esc_url(includes_url('/js/jquery/ui/draggable.min.js')); ?>"></script>
    <script src="<?php echo esc_url(includes_url('/js/jquery/ui/droppable.min.js')); ?>"></script>
    <script>
        var dmrAdmin = {
            ajax_url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
            nonce: "<?php echo wp_create_nonce('dmr_admin_nonce'); ?>"
        };
    </script>
    <script src="<?php echo esc_url(DMR_PLUGIN_URL . 'assets/js/admin.js?v=2.5'); ?>"></script>
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
            <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="dmr-topbar-logo">
                <span class="dmr-topbar-logo-icon">DR</span>
                <span class="dmr-topbar-title">Dashboard</span>
            </a>
        </div>
        <div class="dmr-topbar-right">
            <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="dmr-btn-logout">Logout</a>
        </div>
    </header>

    <div class="dmr-app-body">
        <!-- Sidebar Navigation (Matches reference image) -->
        <aside class="dmr-sidebar">
            <div class="dmr-sidebar-user">
                <div class="dmr-user-avatar">👤</div>
                <div class="dmr-user-meta">
                    <h4><?php echo esc_html($current_user->display_name ? $current_user->display_name : 'Super Admin'); ?></h4>
                    <span>Super Admin</span>
                </div>
            </div>

            <ul class="dmr-sidebar-menu">
                <li class="dmr-menu-item <?php echo ($current_view === 'dashboard') ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url(home_url('/dashboard/?view=dashboard')); ?>">
                        <span class="dmr-menu-icon">⭕</span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="dmr-menu-item <?php echo ($current_view === 'registrations') ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url(home_url('/dashboard/?view=registrations')); ?>">
                        <span class="dmr-menu-icon">👤</span>
                        <span>Registrations &rsaquo;</span>
                    </a>
                </li>
                <li class="dmr-menu-item <?php echo ($current_view === 'members') ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url(home_url('/dashboard/?view=members')); ?>">
                        <span class="dmr-menu-icon">👥</span>
                        <span>Members</span>
                    </a>
                </li>
                <?php 
                    $is_config_active = in_array($current_view, array('config_master', 'config_forms'));
                ?>
                <li class="dmr-menu-item has-submenu <?php echo $is_config_active ? 'open active' : ''; ?>">
                    <a href="javascript:void(0);" onclick="toggleConfigSubmenu(this)">
                        <span style="display:flex; align-items:center; gap:12px;">
                            <span class="dmr-menu-icon">⚙️</span>
                            <span>Config</span>
                        </span>
                        <span class="dmr-submenu-arrow">▶</span>
                    </a>
                    <ul class="dmr-submenu">
                        <li class="dmr-submenu-item <?php echo ($current_view === 'config_master') ? 'active' : ''; ?>">
                            <a href="<?php echo esc_url(home_url('/dashboard/?view=config_master')); ?>">
                                &bull; Master
                            </a>
                        </li>
                        <li class="dmr-submenu-item <?php echo ($current_view === 'config_forms') ? 'active' : ''; ?>">
                            <a href="<?php echo esc_url(home_url('/dashboard/?view=config_forms')); ?>">
                                &bull; Member Type Form
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="dmr-menu-item">
                    <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" style="color:#DC2626;">
                        <span class="dmr-menu-icon">🚪</span>
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
                        <div class="dmr-metric-circle">👥</div>
                        <div class="dmr-metric-details">
                            <h5>TOTAL MEMBERS</h5>
                            <div class="dmr-number"><?php echo esc_html($total_members); ?></div>
                        </div>
                    </div>
                    <div class="dmr-metric-box">
                        <div class="dmr-metric-circle">⏳</div>
                        <div class="dmr-metric-details">
                            <h5>PENDING REGISTRATIONS</h5>
                            <div class="dmr-number"><?php echo esc_html($pending_reg); ?></div>
                        </div>
                    </div>
                    <div class="dmr-metric-box">
                        <div class="dmr-metric-circle">🏷️</div>
                        <div class="dmr-metric-details">
                            <h5>CONFIGURED CATEGORIES</h5>
                            <div class="dmr-number"><?php echo esc_html($total_types); ?></div>
                        </div>
                    </div>
                    <div class="dmr-metric-box">
                        <div class="dmr-metric-circle">📋</div>
                        <div class="dmr-metric-details">
                            <h5>TOTAL SUBMISSIONS</h5>
                            <div class="dmr-number"><?php echo esc_html($all_submissions); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Recent Registrations Card -->
                <div class="dmr-content-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                        <h3 style="margin:0; font-size:18px; font-weight:700;">Recent Registrations</h3>
                        <a href="<?php echo esc_url(home_url('/dashboard/?view=registrations')); ?>" class="dmr-btn dmr-btn-outline dmr-btn-sm">View All &rarr;</a>
                    </div>
                    <?php 
                        $recent_members = $wpdb->get_results("SELECT * FROM $table_members ORDER BY id DESC LIMIT 5");
                    ?>
                    <table class="dmr-data-table">
                        <thead>
                            <tr>
                                <th>Member ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_members)): ?>
                                <?php foreach ($recent_members as $m): ?>
                                    <tr>
                                        <td><strong style="color:#D32F2F;"><?php echo esc_html($m->member_id_code); ?></strong></td>
                                        <td><?php echo esc_html($m->first_name . ' ' . $m->last_name); ?></td>
                                        <td><span class="dmr-badge-pill"><?php echo esc_html($m->type_name); ?></span></td>
                                        <td><?php echo esc_html(date('M d, Y', strtotime($m->created_at))); ?></td>
                                        <td>
                                            <span class="dmr-status-badge dmr-status-<?php echo esc_attr($m->status); ?>">
                                                <?php echo esc_html(ucfirst($m->status)); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:24px; color:#6B7280;">No registrations yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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

