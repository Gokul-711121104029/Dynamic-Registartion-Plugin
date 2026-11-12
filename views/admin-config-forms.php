<?php
if (!defined('ABSPATH')) {
    exit;
}

// Find member types with 0 fields (available for "Create new form")
$unconfigured_types = array();
if (!empty($types)) {
    foreach ($types as $t) {
        $f = !empty($t->form_fields) ? json_decode($t->form_fields, true) : array();
        if (empty($f) || count($f) === 0) {
            $unconfigured_types[] = $t;
        }
    }
}
?>
<div class="dmr-stage-header">
    <h2>Member Type Forms</h2>
</div>

<div class="dmr-content-card">
    <!-- Top Action Toolbar (Matches Reference Image) -->
    <div class="dmr-table-toolbar">
        <button type="button" class="dmr-btn dmr-btn-primary" onclick="openCreateFormSelectorModal()">
            +Create new form
        </button>

        <div class="dmr-search-box">
            <div class="dmr-search-input-wrap">
                <span class="dmr-search-icon-inside" style="display:inline-flex; align-items:center; color:#6B7280;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <input type="text" id="dmr-form-search-input" class="dmr-search-input" placeholder="Search..." onkeydown="if(event.key==='Enter'||event.keyCode===13){event.preventDefault(); filterFormsTable();}">
            </div>
            <button type="button" class="dmr-btn-search" onclick="filterFormsTable()">SEARCH</button>
        </div>
    </div>

    <!-- Table of Forms per Member Type -->
    <div class="dmr-table-responsive">
        <table class="dmr-data-table" id="dmr-forms-table">
            <thead>
                <tr>
                    <th style="width: 70px;">SI No</th>
                    <th>Form (Member Type)</th>
                    <th>Fields</th>
                    <th>Last Changed</th>
                    <th style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($types)): ?>
                    <?php $si = 1; foreach ($types as $t): ?>
                        <?php 
                            $raw_fields = !empty($t->form_fields) ? json_decode($t->form_fields, true) : array();
                            $field_count = 0;
                            if (is_array($raw_fields) && !empty($raw_fields)) {
                                if (isset($raw_fields[0]['fields'])) {
                                    foreach ($raw_fields as $sec) {
                                        $field_count += isset($sec['fields']) ? count($sec['fields']) : 0;
                                    }
                                } else {
                                    $field_count = count($raw_fields);
                                }
                            }
                        ?>
                        <tr id="dmr-form-row-<?php echo esc_attr($t->id); ?>" class="dmr-form-data-row">
                            <td><?php echo $si++; ?></td>
                            <td><strong class="col-form-name"><?php echo esc_html($t->type_name); ?> (<?php echo esc_html($t->type_prefix); ?>)</strong></td>
                            <td>
                                <?php if ($field_count > 0): ?>
                                    <span style="font-weight:700; color:#1F2937;"><?php echo esc_html($field_count); ?></span>
                                <?php else: ?>
                                    <span style="color:#DC2626; font-size:12px; font-weight:600; background:#FEE2E2; padding:3px 8px; border-radius:4px;">0 (Needs Form)</span>
                                <?php endif; ?>
                            </td>
                            <td><span style="color:#6B7280; font-size:13px;"><?php echo esc_html(date('d/m/Y H:i', strtotime($t->created_at))); ?> - Super Admin</span></td>
                            <td>
                                <div class="dmr-action-icons">
                                    <?php if ($field_count > 0): ?>
                                        <!-- View Form Preview Icon -->
                                        <button type="button" class="dmr-icon-btn dmr-icon-view" title="View Form Details & Preview" onclick="previewFormFields(<?php echo esc_attr($t->id); ?>)">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </button>

                                        <!-- Edit Form Fields Icon (Opens Full Page Builder) -->
                                        <a href="<?php echo esc_url(home_url('/dashboard/?view=form_builder&type_id=' . $t->id)); ?>" class="dmr-icon-btn dmr-icon-edit" title="Edit Form Sections & Fields">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </a>

                                        <!-- Delete / Reset Form Fields Icon -->
                                        <button type="button" class="dmr-icon-btn dmr-icon-delete" title="Delete / Clear Form" onclick="askResetForm(<?php echo esc_attr($t->id); ?>, '<?php echo esc_js($t->type_name); ?>')">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                            </svg>
                                        </button>
                                    <?php else: ?>
                                        <!-- If 0 fields, direct action to build the form in Full Page -->
                                        <a href="<?php echo esc_url(home_url('/dashboard/?view=form_builder&type_id=' . $t->id)); ?>" class="dmr-btn dmr-btn-primary dmr-btn-sm" title="Create fields for this member type">
                                            + Create Fields
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:30px; color:#6B7280;">No member type forms configured yet.</td>
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

<!-- Modal: Select Newly Created Member Type (Fields == 0) -->
<div id="dmrCreateFormSelectModal" class="dmr-modal-backdrop">
    <div class="dmr-modal-box" style="max-width: 480px;">
        <div class="dmr-modal-header">
            <h3>Create New Form</h3>
            <span class="dmr-modal-close" onclick="closeCreateFormSelectorModal()">&times;</span>
        </div>
        
        <?php if (!empty($unconfigured_types)): ?>
            <p style="color:#6B7280; font-size:14px; margin-top:0;">
                Select a newly created Member Type from Master to build its registration form:
            </p>
            <div class="dmr-field-group" style="margin-bottom:20px;">
                <label>Select Member Type (0 Fields Configured) *</label>
                <select id="select_unconfigured_type" class="dmr-select" style="padding:10px 14px; border:1px solid #D1D5DB; border-radius:8px; width:100%;">
                    <?php foreach ($unconfigured_types as $ut): ?>
                        <option value="<?php echo esc_attr($ut->id); ?>" data-obj='<?php echo esc_attr(json_encode($ut)); ?>'>
                            <?php echo esc_html($ut->type_name); ?> (<?php echo esc_html($ut->type_prefix); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="dmr-modal-footer">
                <button type="button" class="dmr-btn dmr-btn-outline" onclick="closeCreateFormSelectorModal()">Cancel</button>
                <button type="button" class="dmr-btn dmr-btn-primary" onclick="proceedToBuildSelectedForm()">Continue to Form Builder &rarr;</button>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:15px 0;">
                <div style="width:50px; height:50px; border-radius:50%; background:#DEF7EC; color:#059669; display:flex; align-items:center; justify-content:center; margin:0 auto 12px auto;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <h4 style="margin:0 0 6px 0; font-size:16px; color:#1F2937;">All Member Types Have Forms Configured</h4>
                <p style="color:#6B7280; font-size:13px; line-height:1.5;">
                    Every member type currently has fields configured. You can <strong>Edit</strong> or <strong>View</strong> them directly in the table. If you want to create a brand new form, please first create a new Member Type in the <a href="<?php echo esc_url(home_url('/dashboard/?view=config_master')); ?>" style="color:#D32F2F; font-weight:600;">Config &gt; Master</a> page.
                </p>
            </div>
            <div class="dmr-modal-footer">
                <button type="button" class="dmr-btn dmr-btn-primary" onclick="closeCreateFormSelectorModal()">Okay</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal 1: Form Builder Editor (Edit / Create Form Fields) -->
<div id="dmrFormBuilderModal" class="dmr-modal-backdrop">
    <div class="dmr-modal-box" style="width: 820px; max-width: 95%;">
        <div class="dmr-modal-header">
            <div>
                <h3 id="formBuilderModalTitle" style="margin:0;">Configure Form Fields</h3>
                <small id="formBuilderSubtitle" style="color:#6B7280;">Custom registration fields</small>
            </div>
            <span class="dmr-modal-close" onclick="closeFormBuilderModal()">&times;</span>
        </div>

        <input type="hidden" id="builder_type_id" value="0">

        <div style="margin-bottom: 14px; display:flex; justify-content:flex-end;">
            <button type="button" class="dmr-btn dmr-btn-outline" onclick="addNewFieldRow()">+ Add Field Row</button>
        </div>

        <div class="dmr-table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="dmr-data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">SI</th>
                        <th>Field Label</th>
                        <th>Field Type</th>
                        <th>Field Slug</th>
                        <th style="width: 80px;">Required</th>
                        <th style="width: 60px;">Action</th>
                    </tr>
                </thead>
                <tbody id="dmr-form-builder-tbody">
                    <!-- Rows dynamically generated -->
                </tbody>
            </table>
        </div>

        <div class="dmr-modal-footer">
            <button type="button" class="dmr-btn dmr-btn-outline" onclick="closeFormBuilderModal()">Cancel</button>
            <button type="button" class="dmr-btn dmr-btn-primary" onclick="saveFormFieldsFromModal()">Save Form Layout</button>
        </div>
    </div>
</div>

<!-- Modal 2: Preview Form -->
<div id="dmrFormPreviewModal" class="dmr-modal-backdrop">
    <div class="dmr-modal-box" style="width: 540px; max-width: 90%;">
        <div class="dmr-modal-header">
            <div>
                <h3 id="previewFormTitle">Form Preview</h3>
                <small style="color:#6B7280;">Applicant view preview</small>
            </div>
            <span class="dmr-modal-close" onclick="closePreviewModal()">&times;</span>
        </div>
        
        <div id="previewFormFieldsContainer" style="padding: 10px 0; max-height:420px; overflow-y:auto;">
            <!-- Rendered form fields -->
        </div>

        <div class="dmr-modal-footer">
            <button type="button" class="dmr-btn dmr-btn-primary" onclick="closePreviewModal()">Close Preview</button>
        </div>
    </div>
</div>

<!-- Modal 3: Custom In-Page Reset/Clear Confirmation -->
<div id="dmrFormResetModal" class="dmr-modal-backdrop">
    <div class="dmr-modal-box" style="max-width: 420px; text-align:center;">
        <div style="width:50px; height:50px; border-radius:50%; background:#FEE2E2; color:#DC2626; display:flex; align-items:center; justify-content:center; margin:0 auto 16px auto;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <h3 style="margin:0 0 8px 0; font-size:18px; color:#1F2937;">Delete Form Fields?</h3>
        <p style="color:#6B7280; font-size:14px; margin:0 0 20px 0;">
            Are you sure you want to delete all form fields for <strong id="reset-target-name"></strong>? This will reset the fields count to 0.
        </p>
        <div style="display:flex; justify-content:center; gap:12px;">
            <button type="button" class="dmr-btn dmr-btn-outline" onclick="closeResetModal()">Cancel</button>
            <button type="button" class="dmr-btn dmr-btn-danger" id="confirmResetBtn">Yes, Delete Form</button>
        </div>
    </div>
</div>
