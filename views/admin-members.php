<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_types = $wpdb->prefix . 'dmr_member_types';
$all_member_types = $wpdb->get_results("SELECT * FROM $table_types ORDER BY id ASC");
?>
<div class="dmr-stage-header">
    <h2>Members</h2>
</div>

<div class="dmr-content-card">
    <div class="dmr-table-toolbar" style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px; margin-bottom:20px;">
        <!-- Left Filter Controls: Stacked Cards Matching Reference Image -->
        <div class="dmr-left-filters" style="display:flex; align-items:flex-end; gap:16px; flex-wrap:wrap;">
            <!-- Filter 1: Status Filter -->
            <div class="dmr-filter-card-field" style="display:flex; flex-direction:column; gap:6px;">
                <label for="dmr-member-status-filter" style="font-size:14px; font-weight:600; color:#334155; font-family:'Times New Roman', serif;">Filter by status</label>
                <div style="position:relative; display:inline-block;">
                    <select id="dmr-member-status-filter" class="dmr-filter-select" onchange="filterMembersTable()" style="appearance:none; -webkit-appearance:none; border:1px solid #CBD5E1; border-radius:6px; padding:8px 36px 8px 14px; font-size:14px; background:#FFFFFF; color:#1E293B; outline:none; font-family:'Times New Roman', serif; min-width:140px; cursor:pointer; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
                        <option value="">All statuses</option>
                        <option value="approved" selected>Active</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); pointer-events:none; color:#64748B; font-size:11px;">▼</span>
                </div>
            </div>

            <!-- Filter 2: Member Type Filter -->
            <div class="dmr-filter-card-field" style="display:flex; flex-direction:column; gap:6px;">
                <label for="dmr-member-type-filter" style="font-size:14px; font-weight:600; color:#334155; font-family:'Times New Roman', serif;">Filter by member type</label>
                <div style="position:relative; display:inline-block;">
                    <select id="dmr-member-type-filter" class="dmr-filter-select" onchange="filterMembersTable()" style="appearance:none; -webkit-appearance:none; border:1px solid #CBD5E1; border-radius:6px; padding:8px 36px 8px 14px; font-size:14px; background:#FFFFFF; color:#1E293B; outline:none; font-family:'Times New Roman', serif; min-width:140px; cursor:pointer; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
                        <option value="">All types</option>
                        <?php if (!empty($all_member_types)): ?>
                            <?php foreach ($all_member_types as $mt): ?>
                                <option value="<?php echo esc_attr($mt->type_name); ?>">
                                    <?php echo esc_html($mt->type_name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); pointer-events:none; color:#64748B; font-size:11px;">▼</span>
                </div>
            </div>
        </div>

        <!-- Right Search Box -->
        <div class="dmr-search-box" style="margin-top:auto;">
            <div class="dmr-search-input-wrap">
                <span class="dmr-search-icon-inside" style="display:inline-flex; align-items:center; color:#6B7280;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <input type="text" id="dmr-member-search-input" class="dmr-search-input" placeholder="Search members..." onkeydown="if(event.key==='Enter'||event.keyCode===13){event.preventDefault(); filterMembersTable();}">
            </div>
            <button type="button" class="dmr-btn-search" onclick="filterMembersTable()">SEARCH</button>
        </div>
    </div>

    <div class="dmr-table-responsive">
        <table class="dmr-data-table" id="dmr-members-table">
            <thead>
                <tr>
                    <th style="width:60px;">SI No</th>
                    <th>Member ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Membership Type</th>
                    <th>Status</th>
                    <th style="width:140px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($members)): ?>
                    <?php $si = 1; foreach ($members as $m): ?>
                        <tr id="member-row-<?php echo esc_attr($m->id); ?>">
                            <td><?php echo $si++; ?></td>
                            <td><strong style="color:#D32F2F; font-size:15px;"><?php echo esc_html($m->member_id_code); ?></strong></td>
                            <td><strong><?php echo esc_html($m->first_name); ?></strong></td>
                            <td><strong><?php echo esc_html($m->last_name ? $m->last_name : '-'); ?></strong></td>
                            <td><?php echo esc_html($m->email); ?></td>
                            <td><?php echo esc_html($m->phone ? $m->phone : '-'); ?></td>
                            <td><span class="dmr-badge-pill"><?php echo esc_html($m->type_name); ?></span></td>
                            <td><span class="dmr-status-badge dmr-status-approved">Active</span></td>
                            <td>
                                <?php $fullName = trim($m->first_name . ' ' . $m->last_name); ?>
                                <div class="dmr-action-icons">
                                    <!-- Eye Icon (View Details via AJAX) -->
                                    <button type="button" class="dmr-icon-btn dmr-icon-view" title="View Details" onclick="viewMemberDetails(<?php echo esc_attr($m->id); ?>)">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>

                                    <!-- Edit Icon (Edit Member via AJAX) -->
                                    <button type="button" class="dmr-icon-btn dmr-icon-edit" title="Edit Member" onclick="openEditMemberModal(<?php echo esc_attr($m->id); ?>)">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>

                                    <!-- Delete Icon (Delete Member) -->
                                    <button type="button" class="dmr-icon-btn dmr-icon-delete" title="Delete Member" onclick="askDeleteMember(<?php echo esc_attr($m->id); ?>, '<?php echo esc_js($fullName); ?>')">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding:30px; color:#6B7280;">No members found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Table Footer with Pagination info -->
    <div class="dmr-table-footer">
        <div>
            Showing 1 to <?php echo !empty($members) ? count($members) : 0; ?> of <?php echo !empty($members) ? count($members) : 0; ?> entries 
            &nbsp;&nbsp; Show: 
            <select style="border:1px solid #D1D5DB; border-radius:4px; padding:2px 6px;">
                <option>10</option>
                <option>25</option>
                <option>50</option>
            </select>
        </div>
        <div class="dmr-pagination">
            <button class="dmr-page-btn" disabled>Previous</button>
            <button class="dmr-page-btn active">1</button>
            <button class="dmr-page-btn" disabled>Next</button>
        </div>
    </div>
</div>

<!-- Modal 1: View Member Details -->
<div id="dmrViewMemberModal" class="dmr-modal-backdrop" style="display:none;">
    <div class="dmr-modal-box" style="max-width: 600px; width: 95%; max-height: 90vh; overflow-y: auto; padding: 28px 32px; text-align: left; position: relative;">
        <button type="button" onclick="closeViewMemberModal()" style="position: absolute; top: 20px; right: 24px; background: none; border: none; font-size: 24px; line-height: 1; cursor: pointer; color: #94A3B8;">&times;</button>
        
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #E2E8F0;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 19px; font-weight: 700; color: #1E293B;">Member Profile</h3>
                <div style="font-size: 13px; color: #64748B;">Membership ID: <strong id="v-mem-code" style="color: #D32F2F; font-size:15px;"></strong></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px 20px; margin-bottom: 22px;">
            <div style="background: #F8FAFC; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <small style="color: #64748B; display: block; font-size: 11px; text-transform: uppercase; font-weight: 700;">First Name</small>
                <strong id="v-mem-first-name" style="color: #1E293B; font-size: 14px;"></strong>
            </div>
            <div style="background: #F8FAFC; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <small style="color: #64748B; display: block; font-size: 11px; text-transform: uppercase; font-weight: 700;">Last Name</small>
                <strong id="v-mem-last-name" style="color: #1E293B; font-size: 14px;"></strong>
            </div>
            <div style="background: #F8FAFC; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <small style="color: #64748B; display: block; font-size: 11px; text-transform: uppercase; font-weight: 700;">Email Address</small>
                <span id="v-mem-email" style="color: #1E293B; font-size: 14px; word-break: break-all;"></span>
            </div>
            <div style="background: #F8FAFC; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <small style="color: #64748B; display: block; font-size: 11px; text-transform: uppercase; font-weight: 700;">Phone Number</small>
                <span id="v-mem-phone" style="color: #1E293B; font-size: 14px;"></span>
            </div>
            <div style="background: #F8FAFC; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <small style="color: #64748B; display: block; font-size: 11px; text-transform: uppercase; font-weight: 700;">Membership Type</small>
                <span id="v-mem-type" class="dmr-badge-pill" style="margin-top: 4px; display: inline-block;"></span>
            </div>
            <div style="background: #F8FAFC; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <small style="color: #64748B; display: block; font-size: 11px; text-transform: uppercase; font-weight: 700;">Status</small>
                <span class="dmr-status-badge dmr-status-approved" style="margin-top: 4px; display: inline-block;">Active</span>
            </div>
        </div>

        <h4 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Registered Form Details</h4>
        <div id="v-mem-custom-fields" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 14px; margin-bottom: 24px; min-height: 50px;">
        </div>

        <div style="text-align: right;">
            <button type="button" class="dmr-btn dmr-btn-outline" onclick="closeViewMemberModal()">Close</button>
        </div>
    </div>
</div>

<!-- Modal 2: Edit Member -->
<div id="dmrEditMemberModal" class="dmr-modal-backdrop" style="display:none;">
    <div class="dmr-modal-box" style="max-width: 520px; width: 95%; padding: 28px 32px; text-align: left; position: relative;">
        <button type="button" onclick="closeEditMemberModal()" style="position: absolute; top: 20px; right: 24px; background: none; border: none; font-size: 24px; line-height: 1; cursor: pointer; color: #94A3B8;">&times;</button>
        
        <h3 style="margin: 0 0 6px 0; font-size: 20px; font-weight: 700; color: #1E293B;">Edit Member</h3>
        <p style="color: #64748B; font-size: 13px; margin: 0 0 20px 0;">Update member contact information below.</p>
        
        <form id="dmr-edit-member-form">
            <input type="hidden" id="edit-mem-id" name="id">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">First Name *</label>
                    <input type="text" id="edit-mem-first-name" name="first_name" required style="width: 100%; height: 42px; border: 1px solid #CBD5E1; border-radius: 8px; padding: 0 12px; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">Last Name</label>
                    <input type="text" id="edit-mem-last-name" name="last_name" style="width: 100%; height: 42px; border: 1px solid #CBD5E1; border-radius: 8px; padding: 0 12px; font-size: 14px;">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">Email Address *</label>
                <input type="email" id="edit-mem-email" name="email" required style="width: 100%; height: 42px; border: 1px solid #CBD5E1; border-radius: 8px; padding: 0 12px; font-size: 14px;">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">Phone Number</label>
                <input type="text" id="edit-mem-phone" name="phone" style="width: 100%; height: 42px; border: 1px solid #CBD5E1; border-radius: 8px; padding: 0 12px; font-size: 14px;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="dmr-btn dmr-btn-outline" onclick="closeEditMemberModal()">Cancel</button>
                <button type="submit" class="dmr-btn dmr-btn-primary" id="saveMemberBtn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Delete Member Confirmation -->
<div id="dmrDeleteMemberModal" class="dmr-modal-backdrop" style="display:none;">
    <div class="dmr-modal-box" style="max-width: 440px; text-align: center; padding: 30px;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px auto;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </div>
        <h3 style="margin: 0 0 10px 0; font-size: 20px; font-weight: 700; color: #1F2937;">Delete Member Permanently?</h3>
        <p style="color: #6B7280; font-size: 14px; margin: 0 0 24px 0; line-height: 1.5;">
            Are you sure you want to permanently delete member <strong id="delete-member-name" style="color: #111827;"></strong>? This action cannot be undone.
        </p>
        <div style="display: flex; justify-content: center; gap: 12px;">
            <button type="button" class="dmr-btn dmr-btn-outline" onclick="closeDeleteMemberModal()">Cancel</button>
            <button type="button" class="dmr-btn dmr-btn-danger" id="confirmDeleteMemberBtn">Yes, Delete Permanently</button>
        </div>
    </div>
</div>
