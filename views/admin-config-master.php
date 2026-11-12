<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="dmr-stage-header">
    <h2>Member Types</h2>
</div>

<div class="dmr-content-card">
    <!-- Top Action Toolbar (Matches Reference Image) -->
    <div class="dmr-table-toolbar">
        <button type="button" class="dmr-btn dmr-btn-primary" onclick="openMemberTypeModal()">
            +Add new member type
        </button>

        <div class="dmr-search-box">
            <div class="dmr-search-input-wrap">
                <span class="dmr-search-icon-inside" style="display:inline-flex; align-items:center; color:#6B7280;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <input type="text" id="dmr-type-search-input" class="dmr-search-input" placeholder="Search..." onkeydown="if(event.key==='Enter'||event.keyCode===13){event.preventDefault(); filterMemberTypes();}">
            </div>
            <button type="button" class="dmr-btn-search" onclick="filterMemberTypes()">SEARCH</button>
        </div>
    </div>

    <!-- Responsive Table -->
    <div class="dmr-table-responsive">
        <table class="dmr-data-table" id="dmr-types-table">
            <thead>
                <tr>
                    <th style="width: 70px;">SI No</th>
                    <th>Member Type</th>
                    <th>Code</th>
                    <th>Number Of Members</th>
                    <th>Status</th>
                    <th style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($types)): ?>
                    <?php $si = 1; foreach ($types as $t): ?>
                        <tr id="dmr-type-row-<?php echo esc_attr($t->id); ?>" class="dmr-type-data-row">
                            <td><?php echo $si++; ?></td>
                            <td><strong class="col-type-name"><?php echo esc_html($t->type_name); ?></strong></td>
                            <td><span class="col-type-code" style="color:#6B7280; font-weight:600;"><?php echo esc_html($t->type_prefix); ?></span></td>
                            <td><?php echo esc_html($t->current_counter); ?></td>
                            <td>
                                <span style="color:#059669; font-weight:600;">Active</span>
                            </td>
                            <td>
                                <div class="dmr-action-icons">
                                    <!-- View Icon -->
                                    <button type="button" class="dmr-icon-btn dmr-icon-view" title="View Details" onclick="viewMemberType(<?php echo esc_attr($t->id); ?>)">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>

                                    <!-- Edit Icon -->
                                    <button type="button" class="dmr-icon-btn dmr-icon-edit" title="Edit Member Type" onclick="editMemberType(<?php echo esc_attr($t->id); ?>)">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>

                                    <!-- Delete Icon -->
                                    <button type="button" class="dmr-icon-btn dmr-icon-delete" title="Delete Member Type" onclick="askDeleteMemberType(<?php echo esc_attr($t->id); ?>, '<?php echo esc_js($t->type_name); ?>')">
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
                        <td colspan="6" style="text-align:center; padding:30px; color:#6B7280;">No membership types found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Table Footer with Pagination info -->
    <div class="dmr-table-footer">
        <div>
            Showing 1 to <?php echo count($types); ?> of <?php echo count($types); ?> entries 
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

<!-- Modal 1: Create / Edit Member Type -->
<div id="dmrTypeModal" class="dmr-modal-backdrop">
    <div class="dmr-modal-box">
        <div class="dmr-modal-header">
            <h3 id="modalTitle">Add New Member Type</h3>
            <span class="dmr-modal-close" onclick="closeMemberTypeModal()">&times;</span>
        </div>
        <form id="dmrTypeForm">
            <input type="hidden" name="id" id="type_id" value="0">
            
            <div class="dmr-field-group" style="margin-bottom:14px;">
                <label>Member Type Name *</label>
                <input type="text" name="type_name" id="type_name" class="dmr-input" required>
            </div>

            <div class="dmr-field-group" style="margin-bottom:14px;">
                <label>Member Type Prefix (Code) *</label>
                <input type="text" name="type_prefix" id="type_prefix" class="dmr-input" required>
            </div>

            <div class="dmr-field-group" style="margin-bottom:14px;">
                <label>Member Type Start Number *</label>
                <input type="text" name="start_number" id="start_number" class="dmr-input" required>
                <small style="color:#6B7280;">Example: 001 generates 001, 002... 0001 generates 0001, 0002...</small>
            </div>

            <div class="dmr-modal-footer">
                <button type="button" class="dmr-btn dmr-btn-outline" onclick="closeMemberTypeModal()">Cancel</button>
                <button type="submit" class="dmr-btn dmr-btn-primary" id="btnSaveType">Save Member Type</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: View Member Type Details -->
<div id="dmrViewModal" class="dmr-modal-backdrop">
    <div class="dmr-modal-box">
        <div class="dmr-modal-header">
            <h3>Member Type Details</h3>
            <span class="dmr-modal-close" onclick="closeViewModal()">&times;</span>
        </div>
        <div style="font-size:14px; line-height:1.8; color:#374151;">
            <p><strong>Member Type Name:</strong> <span id="view-type-name">-</span></p>
            <p><strong>Prefix Code:</strong> <span id="view-type-prefix" class="dmr-badge-pill">-</span></p>
            <p><strong>Start Number Format:</strong> <code id="view-start-number">-</code></p>
            <p><strong>Next Member ID Example:</strong> <span id="view-sample-id" style="color:#D32F2F; font-weight:700;">-</span></p>
            <p><strong>Total Registered:</strong> <span id="view-registered-count">-</span></p>
        </div>
        <div class="dmr-modal-footer">
            <button type="button" class="dmr-btn dmr-btn-primary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<!-- Modal 3: Custom In-Page Delete Confirmation (No browser alert) -->
<div id="dmrDeleteModal" class="dmr-modal-backdrop">
    <div class="dmr-modal-box" style="max-width: 420px; text-align:center;">
        <div style="width:50px; height:50px; border-radius:50%; background:#FEE2E2; color:#DC2626; display:flex; align-items:center; justify-content:center; margin:0 auto 16px auto;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <h3 style="margin:0 0 8px 0; font-size:18px; color:#1F2937;">Confirm Deletion</h3>
        <p style="color:#6B7280; font-size:14px; margin:0 0 20px 0;">
            Are you sure you want to delete <strong id="delete-target-name"></strong>? This action cannot be undone.
        </p>
        <div style="display:flex; justify-content:center; gap:12px;">
            <button type="button" class="dmr-btn dmr-btn-outline" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="dmr-btn dmr-btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
        </div>
    </div>
</div>
