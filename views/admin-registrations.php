<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_types = $wpdb->prefix . 'dmr_member_types';
$all_member_types = $wpdb->get_results("SELECT * FROM $table_types ORDER BY id ASC");
?>
<div class="dmr-stage-header">
    <h2>Registrations</h2>
</div>

<div class="dmr-content-card">
    <div class="dmr-table-toolbar" style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px; margin-bottom:20px;">
        <!-- Left Filter Controls: Stacked Cards Matching Reference Image -->
        <div class="dmr-left-filters" style="display:flex; align-items:flex-end; gap:16px; flex-wrap:wrap;">
            <!-- Filter 1: Status Filter -->
            <div class="dmr-filter-card-field" style="display:flex; flex-direction:column; gap:6px;">
                <label for="dmr-reg-status-filter" style="font-size:14px; font-weight:600; color:#334155; font-family:'Times New Roman', serif;">Filter by status</label>
                <div style="position:relative; display:inline-block;">
                    <select id="dmr-reg-status-filter" class="dmr-filter-select" onchange="filterRegistrationsTable()" style="appearance:none; -webkit-appearance:none; border:1px solid #CBD5E1; border-radius:6px; padding:8px 36px 8px 14px; font-size:14px; background:#FFFFFF; color:#1E293B; outline:none; font-family:'Times New Roman', serif; min-width:140px; cursor:pointer; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
                        <option value="">All statuses</option>
                        <option value="pending" selected>Pending</option>
                        <option value="approved">Active</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); pointer-events:none; color:#64748B; font-size:11px;">▼</span>
                </div>
            </div>

            <!-- Filter 2: Member Type Filter -->
            <div class="dmr-filter-card-field" style="display:flex; flex-direction:column; gap:6px;">
                <label for="dmr-reg-type-filter" style="font-size:14px; font-weight:600; color:#334155; font-family:'Times New Roman', serif;">Filter by member type</label>
                <div style="position:relative; display:inline-block;">
                    <select id="dmr-reg-type-filter" class="dmr-filter-select" onchange="filterRegistrationsTable()" style="appearance:none; -webkit-appearance:none; border:1px solid #CBD5E1; border-radius:6px; padding:8px 36px 8px 14px; font-size:14px; background:#FFFFFF; color:#1E293B; outline:none; font-family:'Times New Roman', serif; min-width:140px; cursor:pointer; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
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
                <input type="text" id="dmr-reg-search-input" class="dmr-search-input" placeholder="Search registrations..." onkeydown="if(event.key==='Enter'||event.keyCode===13){event.preventDefault(); filterRegistrationsTable();}">
            </div>
            <button type="button" class="dmr-btn-search" onclick="filterRegistrationsTable()">SEARCH</button>
        </div>
    </div>

    <div class="dmr-table-responsive">
        <table class="dmr-data-table" id="dmr-registrations-table">
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
                    <th style="width:160px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($registrations)): ?>
                    <?php $si = 1; foreach ($registrations as $r): ?>
                        <tr id="reg-row-<?php echo esc_attr($r->id); ?>">
                            <td><?php echo $si++; ?></td>
                            <td><strong style="color:#D32F2F; font-size:15px;"><?php echo esc_html($r->member_id_code); ?></strong></td>
                            <td><strong><?php echo esc_html($r->first_name); ?></strong></td>
                            <td><strong><?php echo esc_html($r->last_name ? $r->last_name : '-'); ?></strong></td>
                            <td><?php echo esc_html($r->email); ?></td>
                            <td><?php echo esc_html($r->phone ? $r->phone : '-'); ?></td>
                            <td><span class="dmr-badge-pill"><?php echo esc_html($r->type_name); ?></span></td>
                            <td>
                                <span class="dmr-status-badge dmr-status-<?php echo esc_attr($r->status); ?>">
                                    <?php echo esc_html(ucfirst($r->status)); ?>
                                </span>
                            </td>
                            <td>
                                <?php $fullName = trim($r->first_name . ' ' . $r->last_name); ?>
                                <div class="dmr-action-icons">
                                    <!-- Eye Icon (View Details via AJAX) -->
                                    <button type="button" class="dmr-icon-btn dmr-icon-view" title="View Details" onclick="viewRegistrationDetails(<?php echo esc_attr($r->id); ?>)">
                                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>

                                    <?php if ($r->status === 'pending'): ?>
                                        <!-- Tick Icon (Approve) -->
                                        <button type="button" class="dmr-icon-btn dmr-icon-approve" style="color:#059669;" title="Approve Registration" onclick="askApproveRegistration(<?php echo esc_attr($r->id); ?>, '<?php echo esc_js($fullName); ?>')">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </button>
                                        <!-- Reject Icon (Delete) -->
                                        <button type="button" class="dmr-icon-btn dmr-icon-reject" style="color:#DC2626;" title="Reject Registration" onclick="askRejectRegistration(<?php echo esc_attr($r->id); ?>, '<?php echo esc_js($fullName); ?>')">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                    <?php elseif ($r->status === 'approved'): ?>
                                        <span style="color:#059669; font-size:12px; font-weight:700;">✓ Approved</span>
                                    <?php else: ?>
                                        <button type="button" class="dmr-icon-btn dmr-icon-reject" style="color:#DC2626;" title="Delete Record" onclick="askRejectRegistration(<?php echo esc_attr($r->id); ?>, '<?php echo esc_js($fullName); ?>')">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding:30px; color:#6B7280;">No pending registrations found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Table Footer with Pagination info -->
    <div class="dmr-table-footer">
        <div>
            Showing 1 to <?php echo !empty($registrations) ? count($registrations) : 0; ?> of <?php echo !empty($registrations) ? count($registrations) : 0; ?> entries 
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

<!-- Modal 1: Custom In-Page Reject & Delete Confirmation -->
<div id="dmrRejectModal" class="dmr-modal-backdrop">
    <div class="dmr-modal-box" style="max-width: 440px; text-align: center; padding: 30px;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px auto;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </div>
        <h3 style="margin: 0 0 10px 0; font-size: 20px; font-weight: 700; color: #1F2937;">Reject &amp; Delete Registration?</h3>
        <p style="color: #6B7280; font-size: 14px; margin: 0 0 24px 0; line-height: 1.5;">
            Are you sure you want to reject the application for <strong id="reject-applicant-name" style="color: #111827;"></strong>? This record will be permanently deleted from the database.
        </p>
        <div style="display: flex; justify-content: center; gap: 12px;">
            <button type="button" class="dmr-btn dmr-btn-outline" onclick="closeRejectModal()">Cancel</button>
            <button type="button" class="dmr-btn dmr-btn-danger" id="confirmRejectBtn">Yes, Reject &amp; Delete</button>
        </div>
    </div>
</div>

<!-- Modal 2: Custom In-Page Approve Confirmation -->
<div id="dmrApproveModal" class="dmr-modal-backdrop">
    <div class="dmr-modal-box" style="max-width: 440px; text-align: center; padding: 30px;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: #DEF7EC; color: #059669; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px auto;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h3 style="margin: 0 0 10px 0; font-size: 20px; font-weight: 700; color: #1F2937;">Approve Registration?</h3>
        <p style="color: #6B7280; font-size: 14px; margin: 0 0 24px 0; line-height: 1.5;">
            Approve membership for <strong id="approve-applicant-name" style="color: #111827;"></strong>? They will immediately be added to the official <strong>Members</strong> directory.
        </p>
        <div style="display: flex; justify-content: center; gap: 12px;">
            <button type="button" class="dmr-btn dmr-btn-outline" onclick="closeApproveModal()">Cancel</button>
            <button type="button" class="dmr-btn dmr-btn-primary" id="confirmApproveBtn">Yes, Approve Member</button>
        </div>
    </div>
</div>

<!-- Modal 3: View Full Registration Details Modal -->
<div id="dmrViewRegistrationModal" class="dmr-modal-backdrop" style="display:none;">
    <div class="dmr-modal-box" style="max-width: 600px; width: 95%; max-height: 90vh; overflow-y: auto; padding: 28px 32px; text-align: left; position: relative;">
        <button type="button" onclick="closeViewRegistrationModal()" style="position: absolute; top: 20px; right: 24px; background: none; border: none; font-size: 24px; line-height: 1; cursor: pointer; color: #94A3B8;">&times;</button>
        
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #E2E8F0;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #FFF1F2; color: #D32F2F; display: flex; align-items: center; justify-content: center;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 19px; font-weight: 700; color: #1E293B;">Registration Details</h3>
                <div style="font-size: 13px; color: #64748B;">Member ID: <strong id="v-reg-member-id" style="color: #D32F2F; font-size:15px;"></strong></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px 20px; margin-bottom: 22px;">
            <div style="background: #F8FAFC; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <small style="color: #64748B; display: block; font-size: 11px; text-transform: uppercase; font-weight: 700;">First Name</small>
                <strong id="v-reg-first-name" style="color: #1E293B; font-size: 14px;"></strong>
            </div>
            <div style="background: #F8FAFC; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <small style="color: #64748B; display: block; font-size: 11px; text-transform: uppercase; font-weight: 700;">Last Name</small>
                <strong id="v-reg-last-name" style="color: #1E293B; font-size: 14px;"></strong>
            </div>
            <div style="background: #F8FAFC; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <small style="color: #64748B; display: block; font-size: 11px; text-transform: uppercase; font-weight: 700;">Email Address</small>
                <span id="v-reg-email" style="color: #1E293B; font-size: 14px; word-break: break-all;"></span>
            </div>
            <div style="background: #F8FAFC; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <small style="color: #64748B; display: block; font-size: 11px; text-transform: uppercase; font-weight: 700;">Phone Number</small>
                <span id="v-reg-phone" style="color: #1E293B; font-size: 14px;"></span>
            </div>
            <div style="background: #F8FAFC; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <small style="color: #64748B; display: block; font-size: 11px; text-transform: uppercase; font-weight: 700;">Membership Type</small>
                <span id="v-reg-type" class="dmr-badge-pill" style="margin-top: 4px; display: inline-block;"></span>
            </div>
            <div style="background: #F8FAFC; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <small style="color: #64748B; display: block; font-size: 11px; text-transform: uppercase; font-weight: 700;">Current Status</small>
                <span id="v-reg-status" style="margin-top: 4px; display: inline-block;"></span>
            </div>
        </div>

        <h4 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">All Submitted Information</h4>
        <div id="v-reg-custom-fields" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 14px; margin-bottom: 24px; min-height: 50px;">
        </div>

        <div style="text-align: right;">
            <button type="button" class="dmr-btn dmr-btn-outline" onclick="closeViewRegistrationModal()">Close</button>
        </div>
    </div>
</div>
