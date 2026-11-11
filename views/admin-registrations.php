<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="dmr-stage-header">
    <h2>Registrations</h2>
</div>

<div class="dmr-content-card">
    <div class="dmr-table-toolbar" style="display:flex; justify-content:flex-end; margin-bottom:16px;">
        <div class="dmr-search-box">
            <div class="dmr-search-input-wrap">
                <span class="dmr-search-icon-inside">🔍</span>
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
                                    <!-- Eye Icon (View Details) -->
                                    <button type="button" class="dmr-icon-btn dmr-icon-view" title="View Details" onclick='viewRegistrationDetails(<?php echo json_encode($r); ?>)'>
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
</div>

<!-- Modal 1: Custom In-Page Reject & Delete Confirmation -->
<div id="dmrRejectModal" class="dmr-modal-backdrop">
    <div class="dmr-modal-box" style="max-width: 440px; text-align: center; padding: 30px;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px auto; font-size: 26px;">
            🗑️
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
        <div style="width: 56px; height: 56px; border-radius: 50%; background: #DEF7EC; color: #059669; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px auto; font-size: 26px;">
            ✓
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
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #FFF1F2; color: #D32F2F; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                👁️
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
