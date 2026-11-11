<div class="wrap dmr-admin-wrap">
    <!-- Top Bar -->
    <div class="dmr-admin-header">
        <div class="dmr-admin-title">
            <span class="dmr-stat-icon" style="width:40px;height:40px;font-size:18px;">📊</span>
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
            <div class="dmr-stat-icon">👥</div>
            <div class="dmr-stat-info">
                <h4>Total Active Members</h4>
                <div class="dmr-stat-number"><?php echo esc_html($total_members); ?></div>
            </div>
        </div>

        <div class="dmr-stat-card">
            <div class="dmr-stat-icon">⏳</div>
            <div class="dmr-stat-info">
                <h4>Pending Registrations</h4>
                <div class="dmr-stat-number"><?php echo esc_html($pending_reg); ?></div>
            </div>
        </div>

        <div class="dmr-stat-card">
            <div class="dmr-stat-icon">🏷️</div>
            <div class="dmr-stat-info">
                <h4>Configured Categories</h4>
                <div class="dmr-stat-number"><?php echo esc_html($total_types); ?></div>
            </div>
        </div>

        <div class="dmr-stat-card">
            <div class="dmr-stat-icon">📝</div>
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

