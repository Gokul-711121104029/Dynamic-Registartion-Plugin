<div class="wrap dmr-admin-wrap">
    <!-- Top Bar -->
    <div class="dmr-admin-header">
        <div class="dmr-admin-title">
            <span class="dmr-stat-icon" style="width:40px;height:40px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
            </span>
            <div>
                <h1>Dashboard Overview</h1>
                <small style="color:var(--dmr-gray);">Real-time membership metrics and activity</small>
            </div>
        </div>
        <div>
            <span class="dmr-badge-pill">Status: Operational</span>
        </div>
    </div>

    <!-- Metric Cards (Matching reference screenshot styling) -->
    <div class="dmr-stats-grid">
        <div class="dmr-stat-card">
            <div class="dmr-stat-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="dmr-stat-info">
                <h4>Total Active Members</h4>
                <div class="dmr-stat-number"><?php echo esc_html($total_members); ?></div>
            </div>
        </div>

        <div class="dmr-stat-card">
            <div class="dmr-stat-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="dmr-stat-info">
                <h4>Pending Registrations</h4>
                <div class="dmr-stat-number"><?php echo esc_html($pending_reg); ?></div>
            </div>
        </div>

        <div class="dmr-stat-card">
            <div class="dmr-stat-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                </svg>
            </div>
            <div class="dmr-stat-info">
                <h4>Configured Categories</h4>
                <div class="dmr-stat-number"><?php echo esc_html($total_types); ?></div>
            </div>
        </div>

        <div class="dmr-stat-card">
            <div class="dmr-stat-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </div>
            <div class="dmr-stat-info">
                <h4>Total Submissions</h4>
                <div class="dmr-stat-number"><?php echo esc_html($all_submissions); ?></div>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Panels -->
    <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <div class="dmr-panel-card">
            <div class="dmr-panel-header">
                <h2>Recent Registrations</h2>
                <a href="<?php echo admin_url('admin.php?page=dmr-registrations'); ?>" class="dmr-btn dmr-btn-outline dmr-btn-sm">View All &rarr;</a>
            </div>
            <div class="dmr-panel-body" style="padding:0;">
                <table class="dmr-table">
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
                                    <td><strong><?php echo esc_html($m->member_id_code); ?></strong></td>
                                    <td><?php echo esc_html($m->first_name); ?></td>
                                    <td><?php echo esc_html($m->type_name); ?></td>
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
                                <td colspan="5" style="text-align:center; padding: 24px; color: var(--dmr-gray);">
                                    No registrations yet. Open the frontend <a href="<?php echo esc_url(home_url('/category/')); ?>" target="_blank">Categories Page</a> to test registration.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dmr-panel-card">
            <div class="dmr-panel-header">
                <h2>Quick Actions</h2>
            </div>
            <div class="dmr-panel-body" style="display:flex; flex-direction:column; gap:12px;">
                <a href="<?php echo admin_url('admin.php?page=dmr-config-master'); ?>" class="dmr-btn dmr-btn-primary">
                    ➕ Add / Manage Member Types
                </a>
                <a href="<?php echo admin_url('admin.php?page=dmr-config-forms'); ?>" class="dmr-btn dmr-btn-outline">
                    ⚙️ Build Dynamic Form Fields
                </a>
                <a href="<?php echo admin_url('admin.php?page=dmr-registrations'); ?>" class="dmr-btn dmr-btn-outline">
                    📥 Review Pending Submissions
                </a>
                <a href="<?php echo home_url('/category/'); ?>" target="_blank" class="dmr-btn dmr-btn-outline">
                    🌐 Open Public Category Page &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

