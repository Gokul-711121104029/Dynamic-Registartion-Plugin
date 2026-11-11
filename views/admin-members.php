<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="dmr-stage-header">
    <h2>Members</h2>
</div>

<div class="dmr-content-card">
    <div class="dmr-table-toolbar" style="display:flex; justify-content:flex-end; margin-bottom:16px;">
        <div class="dmr-search-box">
            <div class="dmr-search-input-wrap">
                <span class="dmr-search-icon-inside">🔍</span>
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
                                    <!-- Eye Icon (View Details) -->
                                    <button type="button" class="dmr-icon-btn dmr-icon-view" title="View Details" onclick='viewMemberDetails(<?php echo json_encode($m); ?>)'>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>

                                    <!-- Edit Icon (Edit Member) -->
                                    <button type="button" class="dmr-icon-btn dmr-icon-edit" title="Edit Member" onclick='openEditMemberModal(<?php echo json_encode($m); ?>)'>
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
</div>

<!-- Modal 1: View Member Details -->
<div id="dmrViewMemberModal" class="dmr-modal-backdrop" style="display:none;">
    <div class="dmr-modal-box" style="max-width: 600px; width: 95%; max-height: 90vh; overflow-y: auto; padding: 28px 32px; text-align: left; position: relative;">
        <button type="button" onclick="closeViewMemberModal()" style="position: absolute; top: 20px; right: 24px; background: none; border: none; font-size: 24px; line-height: 1; cursor: pointer; color: #94A3B8;">&times;</button>
        
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #E2E8F0;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                👤
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
        <div style="width: 56px; height: 56px; border-radius: 50%; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px auto; font-size: 26px;">
            🗑️
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
