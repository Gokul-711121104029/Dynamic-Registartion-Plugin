<?php
if (!defined('ABSPATH')) {
    exit;
}

$type_id = isset($_GET['type_id']) ? intval($_GET['type_id']) : 0;
$member_type = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_types WHERE id = %d", $type_id));

if (!$member_type) {
    echo '<p>Member Type not found. <a href="' . esc_url(home_url('/dashboard/?view=config_forms')) . '">Go back to Member Type Forms</a></p>';
    return;
}

// Raw fields or sections data
$raw_data = !empty($member_type->form_fields) ? json_decode($member_type->form_fields, true) : array();

$sections = array();
if (!empty($raw_data)) {
    if (isset($raw_data[0]['section_title']) || isset($raw_data['sections'])) {
        $sections = isset($raw_data['sections']) ? $raw_data['sections'] : $raw_data;
    } else {
        $sections = array(
            array(
                'section_title' => 'Basic Information',
                'fields' => $raw_data
            )
        );
    }
}
?>

<div class="dmr-builder-wrapper">
    <!-- Header -->
    <div style="background:#fff; border:1px solid #E2E8F0; border-radius:12px; padding:20px 24px; margin-bottom:24px;">
        <a href="<?php echo esc_url(home_url('/dashboard/?view=config_forms')); ?>" style="color:#64748B; text-decoration:none; font-size:13px; font-weight:600; display:inline-flex; align-items:center; gap:6px; margin-bottom:6px;">
            &larr; Back to Member Type Forms
        </a>
        <h2 style="margin:0; font-size:24px; font-weight:700; color:#0F172A;">
            Form Builder: <span style="color:#D32F2F;"><?php echo esc_html($member_type->type_name); ?></span>
            <span class="dmr-badge-pill" style="margin-left:8px; font-size:13px;"><?php echo esc_html($member_type->type_prefix); ?></span>
        </h2>
    </div>

    <!-- Sections Workspace -->
    <div id="dmr-sections-container">
        <!-- Rendered via JS -->
    </div>

    <!-- Single Bottom Action Bar -->
    <div style="margin-top:28px; padding-top:20px; display:flex; justify-content:space-between; align-items:center;">
        <button type="button" class="dmr-btn dmr-btn-outline" onclick="addNewSectionBlock()">+ Add New Section</button>
        <div style="display:flex; gap:12px;">
            <button type="button" class="dmr-btn dmr-btn-outline" onclick="openLivePreviewModal()">👁️ Live Preview</button>
            <button type="button" class="dmr-btn dmr-btn-primary" style="padding:10px 26px;" onclick="saveSectionsFormLayout(<?php echo esc_attr($member_type->id); ?>)">💾 Save Form Layout</button>
        </div>
    </div>
</div>

<!-- Modal: Live Preview -->
<div id="dmrLivePreviewModal" class="dmr-modal-backdrop">
    <div class="dmr-modal-box" style="width: 780px; max-width: 95%;">
        <div class="dmr-modal-header">
            <h3>Live Form Preview: <?php echo esc_html($member_type->type_name); ?></h3>
            <span class="dmr-modal-close" onclick="closeLivePreviewModal()">&times;</span>
        </div>
        <div id="dmrLivePreviewContainer" style="max-height: 520px; overflow-y: auto; padding: 10px 4px;"></div>
        <div class="dmr-modal-footer">
            <button type="button" class="dmr-btn dmr-btn-primary" onclick="closeLivePreviewModal()">Close Preview</button>
        </div>
    </div>
</div>

<!-- Modal: Save Success (Replaces native browser popup) -->
<div id="dmrSaveSuccessModal" class="dmr-modal-backdrop">
    <div class="dmr-modal-box" style="max-width: 440px; text-align: center; padding: 32px 28px;">
        <div style="width: 60px; height: 60px; border-radius: 50%; background: #DEF7EC; color: #059669; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px auto; font-size: 28px;">
            ✓
        </div>
        <h3 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #111827;">Form Layout Saved!</h3>
        <p style="color: #6B7280; font-size: 14px; margin: 0 0 24px 0; line-height: 1.5;">
            The registration form fields and section layout for <strong><?php echo esc_html($member_type->type_name); ?></strong> have been saved successfully.
        </p>
        <button type="button" class="dmr-btn dmr-btn-primary" style="width: 100%; padding: 12px; font-size: 15px;" onclick="window.location.href = '<?php echo esc_url(home_url('/dashboard/?view=config_forms')); ?>'">
            Return to Member Type Forms &rarr;
        </button>
    </div>
</div>

<!-- Modal: Section Delete Confirmation (Replaces native browser confirm) -->
<div id="dmrSectionDeleteModal" class="dmr-modal-backdrop">
    <div class="dmr-modal-box" style="max-width: 420px; text-align: center; padding: 28px;">
        <div style="width: 52px; height: 52px; border-radius: 50%; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto; font-size: 24px;">
            🗑️
        </div>
        <h3 style="margin: 0 0 8px 0; font-size: 18px; color: #1F2937;">Delete Section?</h3>
        <p style="color: #6B7280; font-size: 14px; margin: 0 0 22px 0;">
            Are you sure you want to delete this whole section and all fields inside it?
        </p>
        <div style="display: flex; justify-content: center; gap: 12px;">
            <button type="button" class="dmr-btn dmr-btn-outline" onclick="closeSectionDeleteModal()">Cancel</button>
            <button type="button" class="dmr-btn dmr-btn-danger" id="confirmSectionDeleteBtn">Yes, Delete Section</button>
        </div>
    </div>
</div>

<script>
var initialSectionsData = <?php echo json_encode($sections); ?>;

jQuery(document).ready(function($) {
    if (!initialSectionsData || initialSectionsData.length === 0) {
        addNewSectionBlock('Basic Information');
    } else {
        initialSectionsData.forEach(function(sec) {
            renderSectionDOM(sec.section_title || 'Basic Information', sec.fields || []);
        });
    }

    initDragAndDrop();
});

function initDragAndDrop() {
    var $ = jQuery;

    // 1. Sections Sorting: Up & Down
    if ($('#dmr-sections-container').data('ui-sortable')) {
        $('#dmr-sections-container').sortable('destroy');
    }

    $('#dmr-sections-container').sortable({
        handle: '.dmr-section-drag-handle',
        items: '> .dmr-section-block',
        placeholder: 'dmr-sortable-placeholder-section',
        tolerance: 'pointer',
        axis: 'y',
        cursor: 'grabbing',
        opacity: 0.95,
        start: function(e, ui) {
            ui.placeholder.height(ui.item.outerHeight());
        }
    });

    // 2. Fields Sorting: Left, Right, Up, Down (2D Multi-direction across and within sections)
    $('.dmr-section-fields-list').each(function() {
        if ($(this).data('ui-sortable')) {
            $(this).sortable('destroy');
        }
    });

    $('.dmr-section-fields-list').sortable({
        handle: '.dmr-field-drag-handle',
        items: '> .dmr-field-card',
        connectWith: '.dmr-section-fields-list',
        placeholder: 'dmr-sortable-placeholder-field',
        tolerance: 'pointer',
        cursor: 'grabbing',
        opacity: 0.92,
        distance: 4,
        start: function(e, ui) {
            ui.placeholder.css({
                'width': ui.item.hasClass('dmr-field-full-width') ? '100%' : 'calc(50% - 8px)',
                'height': ui.item.outerHeight()
            });
        }
    });
}

function renderSectionDOM(title, fields) {
    var secId = 'sec_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
    var html = `
        <div class="dmr-section-block" id="${secId}">
            <div class="dmr-section-header">
                <div style="display:flex; align-items:center; gap:12px; flex:1;">
                    <div class="dmr-section-drag-handle" title="Drag to move section Up / Down">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="9" cy="5" r="2"></circle>
                            <circle cx="9" cy="12" r="2"></circle>
                            <circle cx="9" cy="19" r="2"></circle>
                            <circle cx="15" cy="5" r="2"></circle>
                            <circle cx="15" cy="12" r="2"></circle>
                            <circle cx="15" cy="19" r="2"></circle>
                        </svg>
                    </div>
                    <span style="font-size:18px;">📑</span>
                    <input type="text" class="dmr-form-input sec-title" value="${escapeHtml(title)}" placeholder="Enter Section Name" style="font-size:15px; font-weight:700; width:300px;">
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <button type="button" class="dmr-btn dmr-btn-outline dmr-btn-sm" onclick="addFieldToSection('${secId}')">+ Add Field</button>
                    <button type="button" class="dmr-btn dmr-btn-danger dmr-btn-sm" onclick="removeSectionBlock('${secId}')">🗑️ Delete Section</button>
                </div>
            </div>
            <div class="dmr-section-body">
                <div class="dmr-section-fields-list"></div>
                <div style="text-align:center; margin-top:20px; padding-top:14px; border-top:1px dashed #E2E8F0;">
                    <button type="button" class="dmr-btn dmr-btn-outline dmr-btn-sm" onclick="addFieldToSection('${secId}')">+ Add Another Field to this Section</button>
                </div>
            </div>
        </div>
    `;

    var secElem = jQuery(html);
    var listContainer = secElem.find('.dmr-section-fields-list');

    if (fields && fields.length > 0) {
        fields.forEach(function(f) {
            listContainer.append(buildFieldCardDOM(f));
        });
    } else {
        listContainer.append(buildFieldCardDOM({ label: '', type: 'text', placeholder: '', required: true, width: 'half' }));
    }

    jQuery('#dmr-sections-container').append(secElem);
    initDragAndDrop();
}

function toggleFieldCardWidth(btn) {
    var card = jQuery(btn).closest('.dmr-field-card');
    if (card.hasClass('dmr-field-full-width')) {
        card.removeClass('dmr-field-full-width');
        jQuery(btn).html('&#9638; Half Width');
        card.data('width', 'half');
    } else {
        card.addClass('dmr-field-full-width');
        jQuery(btn).html('&#9635; Full Width');
        card.data('width', 'full');
    }
}

function buildFieldCardDOM(f) {
    f = f || {};
    var label = f.label || '';
    var type = f.type || 'text';
    var placeholder = f.placeholder || '';
    var req = (f.required === undefined || f.required === true || f.required === 'true');
    var isFull = (f.width === 'full' || type === 'address' || type === 'textarea');
    var cfg = f.type_config || {};

    var fullClass = isFull ? 'dmr-field-full-width' : '';
    var widthBtnText = isFull ? '&#9635; Full Width' : '&#9638; Half Width';

    var card = jQuery(`
        <div class="dmr-field-card ${fullClass}" data-width="${isFull ? 'full' : 'half'}">
            <!-- Top Toolbar with Drag Handle & Actions -->
            <div class="dmr-field-card-bar">
                <div class="dmr-field-drag-handle" title="Drag Left, Right, Up, or Down to rearrange">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="9" cy="5" r="2"></circle>
                        <circle cx="9" cy="12" r="2"></circle>
                        <circle cx="9" cy="19" r="2"></circle>
                        <circle cx="15" cy="5" r="2"></circle>
                        <circle cx="15" cy="12" r="2"></circle>
                        <circle cx="15" cy="19" r="2"></circle>
                    </svg>
                    <span>Drag Field</span>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <button type="button" class="dmr-width-toggle-btn" title="Toggle 1/2 column or full width" onclick="toggleFieldCardWidth(this)">
                        ${widthBtnText}
                    </button>
                    <button type="button" class="dmr-btn-del-field" title="Delete Field" onclick="jQuery(this).closest('.dmr-field-card').fadeOut(150, function(){ jQuery(this).remove(); })">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- 4-Column Clean Grid -->
            <div class="dmr-field-main-grid">
                <div class="dmr-col-item">
                    <label>LABEL NAME *</label>
                    <input type="text" class="dmr-form-input f-label" value="${escapeHtml(label)}" placeholder="Enter the Label Name">
                </div>

                <div class="dmr-col-item">
                    <label>INPUT FIELD TYPE *</label>
                    <select class="dmr-form-input f-type" onchange="renderDynamicTypeOptions(this)">
                        <option value="text" ${type === 'text' ? 'selected' : ''}>Text</option>
                        <option value="email" ${type === 'email' ? 'selected' : ''}>Email</option>
                        <option value="tel" ${type === 'tel' ? 'selected' : ''}>Phone Number</option>
                        <option value="number" ${type === 'number' ? 'selected' : ''}>Number</option>
                        <option value="date" ${type === 'date' ? 'selected' : ''}>Date</option>
                        <option value="select" ${type === 'select' ? 'selected' : ''}>Dropdown (Select)</option>
                        <option value="radio" ${type === 'radio' ? 'selected' : ''}>Radio Button</option>
                        <option value="checkbox" ${type === 'checkbox' ? 'selected' : ''}>Checkbox</option>
                        <option value="textarea" ${type === 'textarea' ? 'selected' : ''}>Textarea</option>
                        <option value="file" ${type === 'file' ? 'selected' : ''}>File Upload</option>
                        <option value="image" ${type === 'image' ? 'selected' : ''}>Image Upload</option>
                        <option value="address" ${type === 'address' ? 'selected' : ''}>Address</option>
                        <option value="password" ${type === 'password' ? 'selected' : ''}>Password</option>
                        <option value="hidden" ${type === 'hidden' ? 'selected' : ''}>Hidden Field</option>
                    </select>
                </div>

                <div class="dmr-col-item">
                    <label class="col-placeholder-label">PLACEHOLDER / DEFAULT</label>
                    <input type="text" class="dmr-form-input f-placeholder" value="${escapeHtml(placeholder)}" placeholder="Enter placeholder text">
                </div>

                <div class="dmr-col-item" style="align-items:center; text-align:center;">
                    <label>REQUIRED</label>
                    <label class="dmr-switch">
                        <input type="checkbox" class="f-required" ${req ? 'checked' : ''}>
                        <span class="dmr-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Dynamic Options Row (Sub-options render cleanly underneath without breaking column layout) -->
            <div class="dmr-dynamic-type-box"></div>
        </div>
    `);

    renderDynamicTypeOptionsDOM(card.find('.f-type')[0], cfg);
    return card;
}

function renderDynamicTypeOptions(selectElem) {
    renderDynamicTypeOptionsDOM(selectElem, {});
}

function renderDynamicTypeOptionsDOM(selectElem, cfg) {
    var type = jQuery(selectElem).val();
    var card = jQuery(selectElem).closest('.dmr-field-card');
    var box = card.find('.dmr-dynamic-type-box');
    var placeholderInput = card.find('.f-placeholder');
    var placeholderLabel = card.find('.col-placeholder-label');

    box.empty();
    cfg = cfg || {};

    // 1. DROPDOWN (SELECT) OR RADIO BUTTON
    if (type === 'select' || type === 'radio') {
        placeholderLabel.text('DROPDOWN DEFAULT VALUE');
        placeholderInput.attr('placeholder', 'e.g. Select an option');

        var optionsList = cfg.options && cfg.options.length > 0 ? cfg.options : ['Option 1', 'Option 2'];
        var optionsRows = optionsList.map(function(opt) {
            return `
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                    <input type="text" class="dmr-form-input cfg-opt-val" value="${escapeHtml(opt)}" placeholder="Enter Option value" style="width:260px; height:36px;">
                    <button type="button" onclick="jQuery(this).parent().remove()" title="Delete option" style="color:#DC2626; background:none; border:none; cursor:pointer; font-size:20px; line-height:1;">&times;</button>
                </div>
            `;
        }).join('');

        box.html(`
            <div class="dmr-drawer-box" style="background:#FFF5F5 !important; border-color:#FFE0E0 !important;">
                <label style="display:block; font-size:11px; font-weight:700; color:#D32F2F; margin-bottom:8px; text-transform:uppercase;">
                    📋 Configure Choices:
                </label>
                <div class="options-container">${optionsRows}</div>
                <button type="button" class="dmr-btn dmr-btn-outline dmr-btn-sm" style="margin-top:6px;" onclick="addOptionRow(this)">+ Add Option Value</button>
            </div>
        `);
    }

    // 2. PHONE NUMBER
    else if (type === 'tel') {
        placeholderLabel.text('PLACEHOLDER');
        placeholderInput.attr('placeholder', 'Enter the Phone Number');

        box.html(`
            <div class="dmr-drawer-box" style="display:flex; gap:20px; align-items:center;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; color:#475569; text-transform:uppercase; margin-bottom:4px;">Default Country Code</label>
                    <input type="text" class="dmr-form-input cfg-country-code" value="${cfg.country_code || '+91'}" style="width:110px; height:38px;">
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; color:#475569; text-transform:uppercase; margin-bottom:4px;">Min / Max Digits</label>
                    <div style="display:flex; gap:10px;">
                        <input type="number" class="dmr-form-input cfg-min-digits" value="${cfg.min_digits || '10'}" style="width:90px; height:38px;" placeholder="10">
                        <input type="number" class="dmr-form-input cfg-max-digits" value="${cfg.max_digits || '15'}" style="width:90px; height:38px;" placeholder="15">
                    </div>
                </div>
            </div>
        `);
    }

    // 3. FILE UPLOAD
    else if (type === 'file') {
        placeholderLabel.text('HELPER TEXT / NOTE');
        placeholderInput.attr('placeholder', 'e.g. Upload PDF or Word document');

        box.html(`
            <div class="dmr-drawer-box" style="display:grid; grid-template-columns: 2fr 1fr; gap:16px;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; color:#475569; text-transform:uppercase; margin-bottom:4px;">Allowed File Extensions</label>
                    <input type="text" class="dmr-form-input cfg-file-types" value="${cfg.file_types || '.pdf, .doc, .docx'}" placeholder=".pdf, .doc, .docx" style="height:38px;">
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; color:#475569; text-transform:uppercase; margin-bottom:4px;">Maximum File Size (MB)</label>
                    <input type="number" class="dmr-form-input cfg-max-size" value="${cfg.max_size || '5'}" min="1" max="50" style="height:38px;">
                </div>
            </div>
        `);
    }

    // 4. IMAGE UPLOAD
    else if (type === 'image') {
        placeholderLabel.text('HELPER TEXT / NOTE');
        placeholderInput.attr('placeholder', 'e.g. Upload passport size photograph');

        box.html(`
            <div class="dmr-drawer-box" style="display:grid; grid-template-columns: 2fr 1fr; gap:16px;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; color:#475569; text-transform:uppercase; margin-bottom:4px;">Allowed Image Formats</label>
                    <input type="text" class="dmr-form-input cfg-image-formats" value="${cfg.image_formats || 'JPG, JPEG, PNG'}" placeholder="JPG, JPEG, PNG" style="height:38px;">
                </div>
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; color:#475569; text-transform:uppercase; margin-bottom:4px;">Maximum Image Size (MB)</label>
                    <input type="number" class="dmr-form-input cfg-max-size" value="${cfg.max_size || '3'}" min="1" max="20" style="height:38px;">
                </div>
            </div>
        `);
    }

    // 5. CHECKBOX
    else if (type === 'checkbox') {
        placeholderLabel.text('CHECKBOX TEXT');
        placeholderInput.attr('placeholder', 'e.g. I agree to the terms and conditions');

        box.html(`
            <div class="dmr-drawer-box">
                <label style="display:inline-flex; align-items:center; gap:8px; font-size:13px; font-weight:600; cursor:pointer;">
                    <input type="checkbox" class="cfg-multi-select" ${cfg.multi_select ? 'checked' : ''} style="width:16px; height:16px;">
                    <span>Allow multiple selections if multiple choices provided</span>
                </label>
            </div>
        `);
    }

    // 6. ADDRESS
    else if (type === 'address') {
        placeholderLabel.text('HELPER TEXT');
        placeholderInput.attr('placeholder', 'e.g. Enter permanent address');

        var addr = cfg.subfields || { line: true, city: true, state: true, country: true, pincode: true };
        box.html(`
            <div class="dmr-drawer-box">
                <label style="display:block; font-size:11px; font-weight:700; color:#475569; text-transform:uppercase; margin-bottom:8px;">Active Address Components:</label>
                <div style="display:flex; gap:18px; flex-wrap:wrap;">
                    <label style="font-size:13px; display:inline-flex; align-items:center; gap:6px;"><input type="checkbox" class="cfg-addr-line" ${addr.line !== false ? 'checked' : ''}> Address Line</label>
                    <label style="font-size:13px; display:inline-flex; align-items:center; gap:6px;"><input type="checkbox" class="cfg-addr-city" ${addr.city !== false ? 'checked' : ''}> City</label>
                    <label style="font-size:13px; display:inline-flex; align-items:center; gap:6px;"><input type="checkbox" class="cfg-addr-state" ${addr.state !== false ? 'checked' : ''}> State</label>
                    <label style="font-size:13px; display:inline-flex; align-items:center; gap:6px;"><input type="checkbox" class="cfg-addr-country" ${addr.country !== false ? 'checked' : ''}> Country</label>
                    <label style="font-size:13px; display:inline-flex; align-items:center; gap:6px;"><input type="checkbox" class="cfg-addr-pincode" ${addr.pincode !== false ? 'checked' : ''}> PIN Code</label>
                </div>
            </div>
        `);
    }

    // Standard text/email/number/date
    else {
        placeholderLabel.text('PLACEHOLDER / DEFAULT');
        if (type === 'email') placeholderInput.attr('placeholder', 'Enter the Email');
        else if (type === 'number') placeholderInput.attr('placeholder', 'Enter numeric value');
        else if (type === 'text') placeholderInput.attr('placeholder', 'Enter ' + (card.find('.f-label').val() || 'the value'));
    }
}

function addOptionRow(btn) {
    var container = jQuery(btn).siblings('.options-container');
    var count = container.children().length + 1;
    var row = `
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
            <input type="text" class="dmr-form-input cfg-opt-val" value="Option ${count}" placeholder="Enter Option value" style="width:260px; height:36px;">
            <button type="button" onclick="jQuery(this).parent().remove()" title="Delete option" style="color:#DC2626; background:none; border:none; cursor:pointer; font-size:20px; line-height:1;">&times;</button>
        </div>
    `;
    container.append(row);
}

function addNewSectionBlock(title) {
    renderSectionDOM(title || 'New Section', []);
}

var targetDeleteSecId = null;

function removeSectionBlock(secId) {
    targetDeleteSecId = secId;
    jQuery('#dmrSectionDeleteModal').css('display', 'flex');
}

function closeSectionDeleteModal() {
    jQuery('#dmrSectionDeleteModal').hide();
    targetDeleteSecId = null;
}

jQuery(document).on('click', '#confirmSectionDeleteBtn', function() {
    if (targetDeleteSecId) {
        jQuery('#' + targetDeleteSecId).fadeOut(200, function() {
            jQuery(this).remove();
        });
        closeSectionDeleteModal();
    }
});

function addFieldToSection(secId) {
    var list = jQuery('#' + secId).find('.dmr-section-fields-list');
    list.append(buildFieldCardDOM({ label: '', type: 'text', placeholder: '', required: true, width: 'half' }));
    initDragAndDrop();
}

function extractSectionsPayload() {
    var sections = [];

    jQuery('#dmr-sections-container .dmr-section-block').each(function() {
        var secTitle = jQuery(this).find('.sec-title').val().trim() || 'Basic Information';
        var fields = [];

        jQuery(this).find('.dmr-field-card').each(function() {
            var label = jQuery(this).find('.f-label').val().trim();
            var type = jQuery(this).find('.f-type').val();
            var placeholder = jQuery(this).find('.f-placeholder').val().trim();
            var req = jQuery(this).find('.f-required').is(':checked');
            var width = jQuery(this).hasClass('dmr-field-full-width') ? 'full' : 'half';

            if (label) {
                var name = label.toLowerCase().replace(/[^a-z0-9]/g, '_');
                var type_config = {};

                if (type === 'select' || type === 'radio') {
                    var opts = [];
                    jQuery(this).find('.cfg-opt-val').each(function() {
                        var v = jQuery(this).val().trim();
                        if (v) opts.push(v);
                    });
                    type_config.options = opts;
                } else if (type === 'file') {
                    type_config.file_types = jQuery(this).find('.cfg-file-types').val();
                    type_config.max_size = jQuery(this).find('.cfg-max-size').val();
                } else if (type === 'image') {
                    type_config.image_formats = jQuery(this).find('.cfg-image-formats').val();
                    type_config.max_size = jQuery(this).find('.cfg-max-size').val();
                } else if (type === 'checkbox') {
                    type_config.multi_select = jQuery(this).find('.cfg-multi-select').is(':checked');
                } else if (type === 'address') {
                    type_config.subfields = {
                        line: jQuery(this).find('.cfg-addr-line').is(':checked'),
                        city: jQuery(this).find('.cfg-addr-city').is(':checked'),
                        state: jQuery(this).find('.cfg-addr-state').is(':checked'),
                        country: jQuery(this).find('.cfg-addr-country').is(':checked'),
                        pincode: jQuery(this).find('.cfg-addr-pincode').is(':checked')
                    };
                } else if (type === 'tel') {
                    type_config.country_code = jQuery(this).find('.cfg-country-code').val();
                    type_config.min_digits = jQuery(this).find('.cfg-min-digits').val();
                    type_config.max_digits = jQuery(this).find('.cfg-max-digits').val();
                }

                fields.push({
                    label: label,
                    name: name,
                    type: type,
                    placeholder: placeholder,
                    required: req,
                    width: width,
                    type_config: type_config
                });
            }
        });

        if (fields.length > 0) {
            sections.push({
                section_title: secTitle,
                fields: fields
            });
        }
    });

    return sections;
}

function saveSectionsFormLayout(typeId) {
    var sections = extractSectionsPayload();

    if (sections.length === 0) {
        showInlineToast('Please add at least one field to save.', 'error');
        return;
    }

    var btn = jQuery('button[onclick*="saveSectionsFormLayout"]');
    btn.prop('disabled', true).html('💾 Saving...');

    jQuery.post(dmrAdmin.ajax_url, {
        action: 'dmr_save_form_fields',
        type_id: typeId,
        fields_json: JSON.stringify(sections),
        nonce: dmrAdmin.nonce
    }, function(res) {
        btn.prop('disabled', false).html('💾 Save Form Layout');
        if (res.success) {
            // Display stylish in-page confirmation modal without browser popup
            jQuery('#dmrSaveSuccessModal').css('display', 'flex');
        } else {
            showInlineToast('Error: ' + res.data.message, 'error');
        }
    }).fail(function() {
        btn.prop('disabled', false).html('💾 Save Form Layout');
        showInlineToast('A server error occurred. Please try again.', 'error');
    });
}

function showInlineToast(msg, type) {
    var toast = jQuery(`
        <div style="position:fixed; bottom:24px; right:24px; background:${type === 'error' ? '#DC2626' : '#059669'}; color:#fff; padding:12px 24px; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.2); font-size:14px; font-weight:600; z-index:999999; animation:dmrFadeIn 0.2s ease-out;">
            ${escapeHtml(msg)}
        </div>
    `);
    jQuery('body').append(toast);
    setTimeout(function() {
        toast.fadeOut(300, function() { jQuery(this).remove(); });
    }, 3500);
}

function openLivePreviewModal() {
    var sections = extractSectionsPayload();
    var container = jQuery('#dmrLivePreviewContainer');
    container.empty();

    if (sections.length === 0) {
        container.html('<p style="text-align:center; color:#6B7280; padding:20px;">No fields to preview.</p>');
    } else {
        sections.forEach(function(sec) {
            var block = jQuery(`
                <div style="background:#ffffff; border:1px solid #E5E7EB; border-radius:12px; padding:24px 28px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
                    <div style="border-bottom:2px solid #D32F2F; padding-bottom:10px; margin-bottom:20px;">
                        <h3 style="margin:0; font-size:22px; font-weight:700; color:#0F172A;">
                            ${escapeHtml(sec.section_title)}
                        </h3>
                    </div>
                    <div class="fields-area" style="display:flex; flex-wrap:wrap; gap:20px;"></div>
                </div>
            `);

            var fieldsArea = block.find('.fields-area');

            sec.fields.forEach(function(f) {
                var reqStar = f.required ? '<span style="color:#D32F2F; margin-left:3px;">*</span>' : '';
                var isFull = (f.width === 'full' || f.type === 'address' || f.type === 'textarea');
                var flexBasis = isFull ? '100%' : 'calc(50% - 10px)';

                var fieldHtml = `
                    <div style="flex:1 1 ${flexBasis}; width:${flexBasis}; min-width:280px; box-sizing:border-box;">
                        <label style="display:block; font-size:14px; font-weight:600; color:#1F2937; margin-bottom:8px;">
                            ${escapeHtml(f.label)} ${reqStar}
                        </label>
                `;

                if (f.type === 'select') {
                    var opts = (f.type_config && f.type_config.options) ? f.type_config.options : ['Option 1', 'Option 2'];
                    fieldHtml += `<select class="dmr-form-input" style="height:44px; border-radius:8px; border-color:#D1D5DB;"><option value="">-- Select --</option>`;
                    opts.forEach(function(o) {
                        fieldHtml += `<option value="${escapeHtml(o)}">${escapeHtml(o)}</option>`;
                    });
                    fieldHtml += `</select>`;
                } else if (f.type === 'radio') {
                    var opts = (f.type_config && f.type_config.options) ? f.type_config.options : ['Option 1', 'Option 2'];
                    fieldHtml += `<div style="display:flex; gap:16px; margin-top:6px; flex-wrap:wrap;">`;
                    opts.forEach(function(o) {
                        fieldHtml += `<label style="display:inline-flex; align-items:center; gap:6px; font-size:14px; cursor:pointer;"><input type="radio" name="${escapeHtml(f.name)}"> ${escapeHtml(o)}</label>`;
                    });
                    fieldHtml += `</div>`;
                } else if (f.type === 'checkbox') {
                    fieldHtml += `
                        <div style="margin-top:6px;">
                            <label style="display:inline-flex; align-items:center; gap:8px; font-size:14px; cursor:pointer;">
                                <input type="checkbox" style="width:16px; height:16px;">
                                <span>${escapeHtml(f.placeholder || 'I confirm / agree')}</span>
                            </label>
                        </div>
                    `;
                } else if (f.type === 'file') {
                    var fileTypes = (f.type_config && f.type_config.file_types) ? f.type_config.file_types : '*';
                    fieldHtml += `<input type="file" class="dmr-form-input" accept="${escapeHtml(fileTypes)}" style="height:44px; border-radius:8px; border-color:#D1D5DB; padding:8px 12px;">`;
                } else if (f.type === 'image') {
                    fieldHtml += `<input type="file" class="dmr-form-input" accept="image/*" style="height:44px; border-radius:8px; border-color:#D1D5DB; padding:8px 12px;">`;
                } else if (f.type === 'textarea') {
                    fieldHtml += `<textarea class="dmr-form-input" rows="3" placeholder="${escapeHtml(f.placeholder)}" style="height:auto; min-height:90px; border-radius:8px; border-color:#D1D5DB; padding:10px 14px;"></textarea>`;
                } else if (f.type === 'address') {
                    fieldHtml += `
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                            <input type="text" class="dmr-form-input" style="grid-column: span 2; height:44px; border-radius:8px; border-color:#D1D5DB;" placeholder="Address Line">
                            <input type="text" class="dmr-form-input" style="height:44px; border-radius:8px; border-color:#D1D5DB;" placeholder="City">
                            <input type="text" class="dmr-form-input" style="height:44px; border-radius:8px; border-color:#D1D5DB;" placeholder="State">
                            <input type="text" class="dmr-form-input" style="height:44px; border-radius:8px; border-color:#D1D5DB;" placeholder="Country">
                            <input type="text" class="dmr-form-input" style="height:44px; border-radius:8px; border-color:#D1D5DB;" placeholder="PIN Code">
                        </div>
                    `;
                } else {
                    fieldHtml += `<input type="${f.type}" class="dmr-form-input" placeholder="${escapeHtml(f.placeholder)}" style="height:44px; border-radius:8px; border-color:#D1D5DB;">`;
                }

                fieldHtml += `</div>`;
                fieldsArea.append(fieldHtml);
            });

            container.append(block);
        });
    }

    jQuery('#dmrLivePreviewModal').css('display', 'flex');
}

function closeLivePreviewModal() {
    jQuery('#dmrLivePreviewModal').hide();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#039;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
</script>
