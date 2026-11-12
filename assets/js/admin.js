/* DMR Admin JS Scripts */
jQuery(document).ready(function($) {
    // -------------------------------------------------------------
    // MOBILE SIDEBAR NAVIGATION TOGGLE
    // -------------------------------------------------------------
    window.toggleMobileSidebar = function() {
        $('.dmr-sidebar').toggleClass('mobile-open');
        $('.dmr-sidebar-overlay').toggleClass('mobile-open');
    };

    window.closeMobileSidebar = function() {
        $('.dmr-sidebar').removeClass('mobile-open');
        $('.dmr-sidebar-overlay').removeClass('mobile-open');
    };

    // -------------------------------------------------------------
    // DASHBOARD OVERVIEW: LIVE AJAX GROWTH CHART (Chart.js)
    // -------------------------------------------------------------
    function initDashboardGrowthChart() {
        var canvas = document.getElementById('dmrGrowthChart');
        if (!canvas) return;

        $('#dmr-chart-loading').show();

        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_get_growth_chart_data',
            nonce: dmrAdmin.nonce
        }, function(res) {
            $('#dmr-chart-loading').hide();
            if (res.success && res.data) {
                var ctx = canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: res.data.labels,
                        datasets: [{
                            label: 'New members',
                            data: res.data.data,
                            borderColor: '#2563EB',
                            backgroundColor: 'rgba(37, 99, 235, 0.08)',
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: '#2563EB',
                            pointBorderColor: '#FFFFFF',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    boxWidth: 16,
                                    font: { size: 12, weight: '600' },
                                    color: '#4B5563'
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1E293B',
                                titleFont: { size: 13, weight: '700' },
                                bodyFont: { size: 12 },
                                padding: 10,
                                cornerRadius: 8
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: '#F3F4F6' },
                                ticks: { color: '#6B7280', font: { size: 12 } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: '#F3F4F6' },
                                ticks: {
                                    stepSize: 1,
                                    precision: 0,
                                    color: '#6B7280',
                                    font: { size: 12 }
                                }
                            }
                        }
                    }
                });
            }
        }).fail(function() {
            $('#dmr-chart-loading').hide();
        });
    }

    initDashboardGrowthChart();

    // -------------------------------------------------------------
    // DASHBOARD OVERVIEW: PIE CHART & BAR CHART (Chart.js via AJAX)
    // -------------------------------------------------------------
    function initDashboardOverviewCharts() {
        var pieCanvas = document.getElementById('dmrStatusPieChart');
        var barCanvas = document.getElementById('dmrTypeBarChart');
        if (!pieCanvas && !barCanvas) return;

        $('#dmr-pie-loading, #dmr-bar-loading').show();

        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_get_dashboard_charts_data',
            nonce: dmrAdmin.nonce
        }, function(res) {
            $('#dmr-pie-loading, #dmr-bar-loading').hide();
            if (res.success && res.data) {
                // 1. Status Pie Chart
                if (pieCanvas) {
                    var pieCtx = pieCanvas.getContext('2d');
                    new Chart(pieCtx, {
                        type: 'pie',
                        data: {
                            labels: res.data.pie.labels,
                            datasets: [{
                                data: res.data.pie.counts,
                                backgroundColor: [
                                    '#059669', // Active - Green
                                    '#F59E0B', // Pending - Amber/Yellow
                                    '#DC2626'  // Rejected - Red
                                ],
                                borderWidth: 2,
                                borderColor: '#FFFFFF',
                                hoverOffset: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 14,
                                        padding: 14,
                                        font: { size: 12, family: "'Times New Roman', serif", weight: '600' },
                                        color: '#334155'
                                    }
                                },
                                tooltip: {
                                    backgroundColor: '#1E293B',
                                    titleFont: { size: 13, weight: '700' },
                                    bodyFont: { size: 12 },
                                    padding: 10,
                                    cornerRadius: 6
                                }
                            }
                        }
                    });
                }

                // 2. Member Type Bar Chart (X: Master Type, Y: Number of members)
                if (barCanvas) {
                    var barCtx = barCanvas.getContext('2d');
                    new Chart(barCtx, {
                        type: 'bar',
                        data: {
                            labels: res.data.bar.labels,
                            datasets: [{
                                label: 'Number of Members',
                                data: res.data.bar.counts,
                                backgroundColor: 'rgba(211, 47, 47, 0.85)', // DMR Primary Red
                                hoverBackgroundColor: '#B71C1C',
                                borderColor: '#D32F2F',
                                borderWidth: 1,
                                borderRadius: 6,
                                maxBarThickness: 45
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        boxWidth: 14,
                                        font: { size: 12, family: "'Times New Roman', serif", weight: '600' },
                                        color: '#334155'
                                    }
                                },
                                tooltip: {
                                    backgroundColor: '#1E293B',
                                    titleFont: { size: 13, weight: '700' },
                                    bodyFont: { size: 12 },
                                    padding: 10,
                                    cornerRadius: 6
                                }
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Member Type (Configured in Master)',
                                        font: { size: 12, weight: '700', family: "'Times New Roman', serif" },
                                        color: '#475569'
                                    },
                                    grid: { display: false },
                                    ticks: {
                                        color: '#475569',
                                        font: { size: 12, family: "'Times New Roman', serif" }
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Number of Members',
                                        font: { size: 12, weight: '700', family: "'Times New Roman', serif" },
                                        color: '#475569'
                                    },
                                    beginAtZero: true,
                                    grid: { color: '#F1F5F9' },
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0,
                                        color: '#475569',
                                        font: { size: 12 }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        }).fail(function() {
            $('#dmr-pie-loading, #dmr-bar-loading').hide();
        });
    }

    initDashboardOverviewCharts();

    // -------------------------------------------------------------
    // MEMBER TYPE (MASTER PAGE) LOGIC (ALL VIA AJAX)
    // -------------------------------------------------------------
    window.openMemberTypeModal = function() {
        $('#type_id').val(0);
        $('#dmrTypeForm')[0].reset();
        $('#modalTitle').text('Add New Member Type');
        $('#dmrTypeModal').css('display', 'flex');
    };

    window.closeMemberTypeModal = function() {
        $('#dmrTypeModal').hide();
    };

    window.viewMemberType = function(target) {
        if (typeof target === 'object' && target !== null) {
            populateMemberTypeViewModal(target);
            return;
        }

        // Fetch freshly via AJAX
        var id = parseInt(target, 10);
        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_get_member_type_details',
            id: id,
            nonce: dmrAdmin.nonce
        }, function(res) {
            if (res.success && res.data.type) {
                populateMemberTypeViewModal(res.data.type);
            } else {
                alert(res.data ? res.data.message : 'Error fetching member type details.');
            }
        });
    };

    function populateMemberTypeViewModal(t) {
        var start_raw = String(t.start_number || '001').trim();
        var pad_len = start_raw.length;
        var start_int = parseInt(start_raw, 10) || 1;
        var next_num = start_int + parseInt(t.current_counter || 0, 10);
        var padded = String(next_num).padStart(pad_len, '0');
        var sample_id = (t.type_prefix || '') + '-' + padded;

        $('#view-type-name').text(t.type_name);
        $('#view-type-prefix').text(t.type_prefix);
        $('#view-start-number').text(t.start_number);
        $('#view-sample-id').text(sample_id);
        $('#view-registered-count').text((t.current_counter || 0) + ' members');
        $('#view-description').text(t.description || 'No description provided.');

        $('#dmrViewModal').css('display', 'flex');
    }

    window.closeViewModal = function() {
        $('#dmrViewModal').hide();
    };

    window.editMemberType = function(target) {
        if (typeof target === 'object' && target !== null) {
            populateMemberTypeEditModal(target);
            return;
        }

        // Fetch freshly via AJAX
        var id = parseInt(target, 10);
        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_get_member_type_details',
            id: id,
            nonce: dmrAdmin.nonce
        }, function(res) {
            if (res.success && res.data.type) {
                populateMemberTypeEditModal(res.data.type);
            } else {
                alert(res.data ? res.data.message : 'Error fetching member type details.');
            }
        });
    };

    function populateMemberTypeEditModal(typeData) {
        $('#type_id').val(typeData.id);
        $('#type_name').val(typeData.type_name);
        $('#type_prefix').val(typeData.type_prefix);
        $('#start_number').val(typeData.start_number);
        $('#type_description').val(typeData.description || '');
        $('#modalTitle').text('Edit Member Type: ' + typeData.type_name);
        $('#dmrTypeModal').css('display', 'flex');
    }

    $('#dmrTypeForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=dmr_save_member_type&nonce=' + dmrAdmin.nonce;

        $('#btnSaveType').prop('disabled', true).text('Saving...');
        $.post(dmrAdmin.ajax_url, formData, function(res) {
            $('#btnSaveType').prop('disabled', false).text('Save Member Type');
            if (res.success) {
                closeMemberTypeModal();
                location.reload();
            } else {
                alert('Error: ' + res.data.message);
            }
        });
    });

    // Custom In-Page Delete Modal
    var targetDeleteId = null;

    window.askDeleteMemberType = function(id, name) {
        targetDeleteId = id;
        $('#delete-target-name').text(name);
        $('#dmrDeleteModal').css('display', 'flex');
    };

    window.closeDeleteModal = function() {
        $('#dmrDeleteModal').hide();
        targetDeleteId = null;
    };

    $('#confirmDeleteBtn').on('click', function() {
        if (!targetDeleteId) return;

        var btn = $(this);
        var id = targetDeleteId;
        btn.prop('disabled', true).text('Deleting...');

        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_delete_member_type',
            id: id,
            nonce: dmrAdmin.nonce
        }, function(res) {
            btn.prop('disabled', false).text('Yes, Delete');
            closeDeleteModal();
            if (res.success) {
                $('#dmr-type-row-' + id).fadeOut(200, function() {
                    $(this).remove();
                    location.reload();
                });
            } else {
                alert('Error: ' + res.data.message);
            }
        }).fail(function() {
            btn.prop('disabled', false).text('Yes, Delete');
            closeDeleteModal();
            alert('A network error occurred. Please try again.');
        });
    });

    // -------------------------------------------------------------
    // AJAX SEARCH: MEMBER TYPES (Config > Master)
    // -------------------------------------------------------------
    window.filterMemberTypes = function() {
        var query = $('#dmr-type-search-input').val().trim();
        executeAjaxMemberTypeSearch(query);
    };

    function executeAjaxMemberTypeSearch(query) {
        var tbody = $('#dmr-types-table tbody');
        tbody.html('<tr><td colspan="6" style="text-align:center; padding:35px 20px; color:#D32F2F; font-weight:600;"><span class="dmr-table-spinner"></span> Searching backend...</td></tr>');

        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_search_member_types',
            query: query,
            nonce: dmrAdmin.nonce
        }, function(res) {
            if (res.success) {
                renderMemberTypesRows(res.data.types);
            } else {
                tbody.html('<tr><td colspan="6" style="text-align:center; padding:30px; color:#6B7280;">No member types found matching search.</td></tr>');
            }
        }).fail(function() {
            tbody.html('<tr><td colspan="6" style="text-align:center; padding:30px; color:#DC2626;">Error retrieving member types from server.</td></tr>');
        });
    }

    function renderMemberTypesRows(types) {
        var tbody = $('#dmr-types-table tbody');
        tbody.empty();

        if (!types || types.length === 0) {
            tbody.html('<tr><td colspan="6" style="text-align:center; padding:30px; color:#6B7280;">No member types found matching search.</td></tr>');
            return;
        }

        types.forEach(function(t, idx) {
            var row = `
                <tr id="dmr-type-row-${t.id}" class="dmr-type-data-row">
                    <td>${idx + 1}</td>
                    <td><strong class="col-type-name">${escapeHtml(t.type_name)}</strong></td>
                    <td><span class="col-type-code" style="color:#6B7280; font-weight:600;">${escapeHtml(t.type_prefix)}</span></td>
                    <td>${escapeHtml(t.current_counter || 0)}</td>
                    <td><span style="color:#059669; font-weight:600;">Active</span></td>
                    <td>
                        <div class="dmr-action-icons">
                            <button type="button" class="dmr-icon-btn dmr-icon-view" title="View Details" onclick="viewMemberType(${t.id})">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                            <button type="button" class="dmr-icon-btn dmr-icon-edit" title="Edit Member Type" onclick="editMemberType(${t.id})">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </button>
                            <button type="button" class="dmr-icon-btn dmr-icon-delete" title="Delete Member Type" onclick="askDeleteMemberType(${t.id}, '${escapeHtml(t.type_name)}')">
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
            `;
            tbody.append(row);
        });
    }

    // -------------------------------------------------------------
    // MEMBER TYPE FORMS PAGE LOGIC
    // -------------------------------------------------------------
    window.openCreateFormSelectorModal = function() {
        $('#dmrCreateFormSelectModal').css('display', 'flex');
    };

    window.closeCreateFormSelectorModal = function() {
        $('#dmrCreateFormSelectModal').hide();
    };

    window.proceedToBuildSelectedForm = function() {
        var opt = $('#select_unconfigured_type option:selected');
        if (!opt.length) return;
        var t = JSON.parse(opt.attr('data-obj'));
        closeCreateFormSelectorModal();
        window.location.href = window.location.pathname + "?view=form_builder&type_id=" + t.id;
    };

    // -------------------------------------------------------------
    // AJAX SEARCH: MEMBER TYPE FORMS (Config > Member Type Form)
    // -------------------------------------------------------------
    window.filterFormsTable = function() {
        var query = $('#dmr-form-search-input').val().trim();
        executeAjaxFormsSearch(query);
    };

    function executeAjaxFormsSearch(query) {
        var tbody = $('#dmr-forms-table tbody');
        tbody.html('<tr><td colspan="5" style="text-align:center; padding:35px 20px; color:#D32F2F; font-weight:600;"><span class="dmr-table-spinner"></span> Searching backend...</td></tr>');

        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_search_forms',
            query: query,
            nonce: dmrAdmin.nonce
        }, function(res) {
            if (res.success) {
                renderFormsRows(res.data.forms);
            } else {
                tbody.html('<tr><td colspan="5" style="text-align:center; padding:30px; color:#6B7280;">No member type forms found matching search.</td></tr>');
            }
        }).fail(function() {
            tbody.html('<tr><td colspan="5" style="text-align:center; padding:30px; color:#DC2626;">Error retrieving forms from server.</td></tr>');
        });
    }

    function renderFormsRows(forms) {
        var tbody = $('#dmr-forms-table tbody');
        tbody.empty();

        if (!forms || forms.length === 0) {
            tbody.html('<tr><td colspan="5" style="text-align:center; padding:30px; color:#6B7280;">No member type forms found matching search.</td></tr>');
            return;
        }

        forms.forEach(function(t, idx) {
            var raw = [];
            try {
                raw = JSON.parse(t.form_fields) || [];
            } catch(e) {
                raw = [];
            }

            var fieldCount = 0;
            if (raw && raw.length > 0) {
                if (raw[0] && raw[0].fields) {
                    raw.forEach(function(s) {
                        fieldCount += (s.fields ? s.fields.length : 0);
                    });
                } else {
                    fieldCount = raw.length;
                }
            }

            var countBadge = fieldCount > 0 
                ? `<span style="font-weight:700; color:#1F2937;">${fieldCount}</span>` 
                : `<span style="color:#DC2626; font-size:12px; font-weight:600; background:#FEE2E2; padding:3px 8px; border-radius:4px;">0 (Needs Form)</span>`;

            var actionsHtml = '';
            if (fieldCount > 0) {
                actionsHtml = `
                    <button type="button" class="dmr-icon-btn dmr-icon-view" title="View Form Details & Preview" onclick="previewFormFields(${t.id})">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                    <a href="${window.location.pathname}?view=form_builder&type_id=${t.id}" class="dmr-icon-btn dmr-icon-edit" title="Edit Form Sections & Fields">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </a>
                    <button type="button" class="dmr-icon-btn dmr-icon-delete" title="Delete / Clear Form" onclick="askResetForm(${t.id}, '${escapeHtml(t.type_name)}')">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                    </button>
                `;
            } else {
                actionsHtml = `
                    <a href="${window.location.pathname}?view=form_builder&type_id=${t.id}" class="dmr-btn dmr-btn-primary dmr-btn-sm" title="Create fields for this member type">
                        + Create Fields
                    </a>
                `;
            }

            var row = `
                <tr id="dmr-form-row-${t.id}" class="dmr-form-data-row">
                    <td>${idx + 1}</td>
                    <td><strong class="col-form-name">${escapeHtml(t.type_name)} (${escapeHtml(t.type_prefix)})</strong></td>
                    <td>${countBadge}</td>
                    <td><span style="color:#6B7280; font-size:13px;">${escapeHtml(t.created_at || '')} - Super Admin</span></td>
                    <td><div class="dmr-action-icons">${actionsHtml}</div></td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    window.previewFormFields = function(target) {
        if (typeof target === 'object' && target !== null) {
            populateFormPreviewModal(target);
            return;
        }

        var id = parseInt(target, 10);
        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_get_form_preview_details',
            id: id,
            nonce: dmrAdmin.nonce
        }, function(res) {
            if (res.success && res.data.type) {
                populateFormPreviewModal(res.data.type);
            } else {
                alert(res.data ? res.data.message : 'Error fetching form configuration.');
            }
        });
    };

    function populateFormPreviewModal(t) {
        $('#previewFormTitle').text(t.type_name + ' (' + t.type_prefix + ')');
        var container = $('#previewFormFieldsContainer');
        container.empty();

        var raw = [];
        try {
            raw = JSON.parse(t.form_fields) || [];
        } catch(e) {
            raw = [];
        }

        var sections = [];
        if (raw && raw.length > 0) {
            if (raw[0].section_title) {
                sections = raw;
            } else {
                sections = [{ section_title: 'General Information', fields: raw }];
            }
        }

        if (sections.length === 0) {
            container.html('<p style="color:#6B7280; text-align:center;">No custom fields configured for this form.</p>');
        } else {
            sections.forEach(function(sec) {
                var secBlock = $(`
                    <div style="margin-bottom:20px; border-bottom:1px solid #E5E7EB; padding-bottom:14px;">
                        <h4 style="margin:0 0 12px 0; color:#1F2937; font-size:15px; border-left:3px solid #D32F2F; padding-left:8px;">
                            ${escapeHtml(sec.section_title)}
                        </h4>
                        <div class="sec-fields-box"></div>
                    </div>
                `);

                var box = secBlock.find('.sec-fields-box');
                (sec.fields || []).forEach(function(f) {
                    var reqStar = f.required ? '<span style="color:#D32F2F;">*</span>' : '';
                    var placeholder = f.placeholder ? ` (Placeholder: "${escapeHtml(f.placeholder)}")` : '';
                    var html = `
                        <div style="margin-bottom:12px;">
                            <label style="display:block; font-size:13px; font-weight:600; margin-bottom:4px; color:#374151;">
                                ${escapeHtml(f.label)} ${reqStar}
                            </label>
                            <input type="text" class="dmr-input" disabled placeholder="[${escapeHtml(f.type)}]${placeholder}" style="background:#F9FAFB; padding:8px 12px;">
                        </div>
                    `;
                    box.append(html);
                });

                container.append(secBlock);
            });
        }

        $('#dmrFormPreviewModal').css('display', 'flex');
    }

    window.closePreviewModal = function() {
        $('#dmrFormPreviewModal').hide();
    };

    window.openFormBuilderModal = function(t) {
        $('#builder_type_id').val(t.id);
        $('#formBuilderModalTitle').text('Configure Form: ' + t.type_name);
        $('#formBuilderSubtitle').text('Category: ' + t.type_prefix);

        var fields = [];
        try {
            fields = JSON.parse(t.form_fields) || [];
        } catch(e) {
            fields = [];
        }

        renderBuilderTableRows(fields);
        $('#dmrFormBuilderModal').css('display', 'flex');
    };

    window.closeFormBuilderModal = function() {
        $('#dmrFormBuilderModal').hide();
    };

    function renderBuilderTableRows(fields) {
        var container = $('#dmr-form-builder-tbody');
        container.empty();

        if (fields.length === 0) {
            container.append('<tr><td colspan="6" style="text-align:center; padding:20px; color:#6B7280;">No custom fields. Click "+ Add Field Row" above to add fields.</td></tr>');
            return;
        }

        fields.forEach(function(f, idx) {
            var row = $('<tr class="dmr-builder-row"></tr>');
            row.html(`
                <td style="text-align:center; font-weight:700; color:#9CA3AF;">${idx + 1}</td>
                <td>
                    <input type="text" class="dmr-input f-label" value="${escapeHtml(f.label)}" placeholder="e.g. Full Name" style="padding:6px 10px;">
                </td>
                <td>
                    <select class="dmr-select f-type" style="padding:6px 10px;">
                        <option value="text" ${f.type === 'text' ? 'selected' : ''}>Text Input</option>
                        <option value="email" ${f.type === 'email' ? 'selected' : ''}>Email</option>
                        <option value="tel" ${f.type === 'tel' ? 'selected' : ''}>Phone Number</option>
                        <option value="date" ${f.type === 'date' ? 'selected' : ''}>Date</option>
                        <option value="textarea" ${f.type === 'textarea' ? 'selected' : ''}>Text Area</option>
                        <option value="select" ${f.type === 'select' ? 'selected' : ''}>Dropdown Select</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="dmr-input f-name" value="${escapeHtml(f.name)}" placeholder="e.g. full_name" style="padding:6px 10px;">
                </td>
                <td style="text-align:center;">
                    <input type="checkbox" class="f-req" ${f.required ? 'checked' : ''} style="transform:scale(1.2);">
                </td>
                <td style="text-align:center;">
                    <button type="button" class="dmr-icon-btn dmr-icon-delete" title="Remove Field" onclick="$(this).closest('.dmr-builder-row').remove()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </td>
            `);
            container.append(row);
        });
    }

    window.addNewFieldRow = function() {
        var container = $('#dmr-form-builder-tbody');
        var count = container.find('.dmr-builder-row').length + 1;

        var row = $(`
            <tr class="dmr-builder-row">
                <td style="text-align:center; font-weight:700; color:#9CA3AF;">${count}</td>
                <td>
                    <input type="text" class="dmr-input f-label" placeholder="e.g. Identity Proof No" style="padding:6px 10px;">
                </td>
                <td>
                    <select class="dmr-select f-type" style="padding:6px 10px;">
                        <option value="text">Text Input</option>
                        <option value="email">Email</option>
                        <option value="tel">Phone Number</option>
                        <option value="date">Date</option>
                        <option value="textarea">Text Area</option>
                        <option value="select">Dropdown Select</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="dmr-input f-name" placeholder="e.g. id_proof" style="padding:6px 10px;">
                </td>
                <td style="text-align:center;">
                    <input type="checkbox" class="f-req" checked style="transform:scale(1.2);">
                </td>
                <td style="text-align:center;">
                    <button type="button" class="dmr-icon-btn dmr-icon-delete" title="Remove Field" onclick="$(this).closest('.dmr-builder-row').remove()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </td>
            </tr>
        `);
        container.append(row);
    };

    window.saveFormFieldsFromModal = function() {
        var typeId = $('#builder_type_id').val();
        var fields = [];

        $('#dmr-form-builder-tbody .dmr-builder-row').each(function() {
            var label = $(this).find('.f-label').val().trim();
            var type = $(this).find('.f-type').val();
            var name = $(this).find('.f-name').val().trim();
            var req = $(this).find('.f-req').is(':checked');

            if (!name && label) {
                name = label.toLowerCase().replace(/[^a-z0-9]/g, '_');
            }

            if (label && name) {
                fields.push({
                    label: label,
                    type: type,
                    name: name,
                    required: req
                });
            }
        });

        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_save_form_fields',
            type_id: typeId,
            fields_json: JSON.stringify(fields),
            nonce: dmrAdmin.nonce
        }, function(res) {
            if (res.success) {
                alert('Form fields saved successfully!');
                closeFormBuilderModal();
                location.reload();
            } else {
                alert(res.data.message);
            }
        });
    };

    // Reset Form Fields Modal
    var targetResetId = null;
    window.askResetForm = function(id, name) {
        targetResetId = id;
        $('#reset-target-name').text(name);
        $('#dmrFormResetModal').css('display', 'flex');
    };

    window.closeResetModal = function() {
        $('#dmrFormResetModal').hide();
        targetResetId = null;
    };

    $('#confirmResetBtn').on('click', function() {
        if (!targetResetId) return;

        var btn = $(this);
        btn.prop('disabled', true).text('Resetting...');

        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_save_form_fields',
            type_id: targetResetId,
            fields_json: JSON.stringify([]),
            nonce: dmrAdmin.nonce
        }, function(res) {
            btn.prop('disabled', false).text('Yes, Reset');
            closeResetModal();
            if (res.success) {
                location.reload();
            } else {
                alert(res.data.message);
            }
        });
    });

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#039;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // -------------------------------------------------------------
    // REGISTRATIONS PAGE: IN-APP MODAL WORKFLOW (No native popups)
    // -------------------------------------------------------------
    var targetRejectRegId = null;
    var targetApproveRegId = null;

    window.askRejectRegistration = function(id, name) {
        targetRejectRegId = id;
        $('#reject-applicant-name').text(name);
        $('#dmrRejectModal').css('display', 'flex');
    };

    window.closeRejectModal = function() {
        $('#dmrRejectModal').hide();
        targetRejectRegId = null;
    };

    $('#confirmRejectBtn').on('click', function() {
        if (!targetRejectRegId) return;

        var id = targetRejectRegId;
        var btn = $(this);
        btn.prop('disabled', true).text('Deleting...');

        var row = $('#reg-row-' + id);
        row.css('opacity', '0.5');

        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_update_member_status',
            id: id,
            status: 'rejected',
            nonce: dmrAdmin.nonce
        }, function(res) {
            btn.prop('disabled', false).text('Yes, Reject & Delete');
            closeRejectModal();

            if (res.success) {
                row.fadeOut(300, function() {
                    $(this).remove();
                    if ($('.dmr-data-table tbody tr').length === 0) {
                        $('.dmr-data-table tbody').html('<tr><td colspan="8" style="text-align:center; padding:30px; color:#6B7280;">No registrations submitted yet.</td></tr>');
                    }
                });
            } else {
                row.css('opacity', '1');
                alert(res.data.message);
            }
        }).fail(function() {
            btn.prop('disabled', false).text('Yes, Reject & Delete');
            row.css('opacity', '1');
            closeRejectModal();
            alert('A network error occurred. Please try again.');
        });
    });

    window.askApproveRegistration = function(id, name) {
        targetApproveRegId = id;
        $('#approve-applicant-name').text(name);
        $('#dmrApproveModal').css('display', 'flex');
    };

    window.closeApproveModal = function() {
        $('#dmrApproveModal').hide();
        targetApproveRegId = null;
    };

    $('#confirmApproveBtn').on('click', function() {
        if (!targetApproveRegId) return;

        var id = targetApproveRegId;
        var btn = $(this);
        btn.prop('disabled', true).text('Approving...');

        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_update_member_status',
            id: id,
            status: 'approved',
            nonce: dmrAdmin.nonce
        }, function(res) {
            btn.prop('disabled', false).text('Yes, Approve Member');
            closeApproveModal();

            if (res.success) {
                location.reload();
            } else {
                alert(res.data.message);
            }
        }).fail(function() {
            btn.prop('disabled', false).text('Yes, Approve Member');
            closeApproveModal();
            alert('A network error occurred. Please try again.');
        });
    });

    window.updateStatus = function(id, newStatus, applicantName) {
        if (newStatus === 'rejected') {
            askRejectRegistration(id, applicantName);
        } else if (newStatus === 'approved') {
            askApproveRegistration(id, applicantName);
        }
    };

    // View Details Modal for Registrations (via AJAX)
    window.viewRegistrationDetails = function(target) {
        if (typeof target === 'object' && target !== null) {
            populateRegistrationDetailsModal(target);
            return;
        }

        var id = parseInt(target, 10);
        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_get_registration_details',
            id: id,
            nonce: dmrAdmin.nonce
        }, function(res) {
            if (res.success && res.data.registration) {
                populateRegistrationDetailsModal(res.data.registration);
            } else {
                alert(res.data ? res.data.message : 'Error fetching registration details.');
            }
        });
    };

    function populateRegistrationDetailsModal(r) {
        if (typeof r === 'string') {
            try { r = JSON.parse(r); } catch(e) {}
        }
        $('#v-reg-member-id').text(r.member_id_code || '-');
        $('#v-reg-first-name').text(r.first_name || '-');
        $('#v-reg-last-name').text(r.last_name || '-');
        $('#v-reg-email').text(r.email || '-');
        $('#v-reg-phone').text(r.phone || '-');
        $('#v-reg-type').text(r.type_name || '-');
        
        var status = r.status || 'pending';
        var statusCap = status.charAt(0).toUpperCase() + status.slice(1);
        $('#v-reg-status').html('<span class="dmr-status-badge dmr-status-' + escapeHtml(status) + '">' + escapeHtml(statusCap) + '</span>');
        
        var container = $('#v-reg-custom-fields');
        container.empty();
        
        var formData = {};
        if (r.form_data) {
            if (typeof r.form_data === 'string') {
                try { formData = JSON.parse(r.form_data); } catch(e) {}
            } else {
                formData = r.form_data;
            }
        }
        
        var keys = Object.keys(formData);
        if (keys.length === 0) {
            container.html('<em style="color:#94A3B8; font-size:13px;">No additional fields submitted.</em>');
        } else {
            var html = '<table style="width:100%; border-collapse:collapse; font-size:13px;">';
            keys.forEach(function(k) {
                var label = k.replace(/_/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
                var val = formData[k];
                html += '<tr style="border-bottom:1px solid #F1F5F9;"><td style="padding:8px 10px; color:#64748B; width:40%; font-weight:600;">' + escapeHtml(label) + '</td><td style="padding:8px 10px; color:#0F172A; font-weight:500;">' + escapeHtml(val) + '</td></tr>';
            });
            html += '</table>';
            container.html(html);
        }
        
        $('#dmrViewRegistrationModal').css('display', 'flex').hide().fadeIn(150);
    }

    window.closeViewRegistrationModal = function() {
        $('#dmrViewRegistrationModal').fadeOut(150);
    };

    // -------------------------------------------------------------
    // AJAX SEARCH & FILTERS: REGISTRATIONS
    // -------------------------------------------------------------
    window.filterRegistrationsTable = function() {
        var query = $('#dmr-reg-search-input').val().trim();
        var typeFilter = $('#dmr-reg-type-filter').val() || '';
        var statusFilter = $('#dmr-reg-status-filter').val() || '';
        executeAjaxRegistrationsSearch(query, typeFilter, statusFilter);
    };

    function executeAjaxRegistrationsSearch(query, typeFilter, statusFilter) {
        var tbody = $('#dmr-registrations-table tbody');
        tbody.html('<tr><td colspan="9" style="text-align:center; padding:35px 20px; color:#D32F2F; font-weight:600;"><span class="dmr-table-spinner"></span> Searching backend...</td></tr>');

        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_search_registrations',
            query: query,
            type: typeFilter,
            status: statusFilter,
            nonce: dmrAdmin.nonce
        }, function(res) {
            if (res.success) {
                renderRegistrationsRows(res.data.registrations);
            } else {
                tbody.html('<tr><td colspan="9" style="text-align:center; padding:30px; color:#6B7280;">No registrations found matching search.</td></tr>');
            }
        }).fail(function() {
            tbody.html('<tr><td colspan="9" style="text-align:center; padding:30px; color:#DC2626;">Error retrieving registrations from server.</td></tr>');
        });
    }

    function renderRegistrationsRows(regs) {
        var tbody = $('#dmr-registrations-table tbody');
        tbody.empty();

        if (!regs || regs.length === 0) {
            tbody.html('<tr><td colspan="9" style="text-align:center; padding:30px; color:#6B7280;">No registrations found matching search.</td></tr>');
            return;
        }

        regs.forEach(function(r, idx) {
            var fullName = escapeHtml(r.first_name + ' ' + (r.last_name || '')).trim();
            var actionsHtml = `
                <div class="dmr-action-icons">
                    <button type="button" class="dmr-icon-btn dmr-icon-view" title="View Details" onclick="viewRegistrationDetails(${r.id})">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
            `;
            if (r.status === 'pending') {
                actionsHtml += `
                    <button type="button" class="dmr-icon-btn dmr-icon-approve" style="color:#059669;" title="Approve Registration" onclick="askApproveRegistration(${r.id}, '${fullName}')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </button>
                    <button type="button" class="dmr-icon-btn dmr-icon-reject" style="color:#DC2626;" title="Reject Registration" onclick="askRejectRegistration(${r.id}, '${fullName}')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                `;
            } else if (r.status === 'approved') {
                actionsHtml += '<span style="color:#059669; font-size:12px; font-weight:700;">✓ Approved</span>';
            } else {
                actionsHtml += `
                    <button type="button" class="dmr-icon-btn dmr-icon-reject" style="color:#DC2626;" title="Delete Record" onclick="askRejectRegistration(${r.id}, '${fullName}')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                `;
            }
            actionsHtml += '</div>';

            var row = `
                <tr id="reg-row-${r.id}">
                    <td>${idx + 1}</td>
                    <td><strong style="color:#D32F2F; font-size:15px;">${escapeHtml(r.member_id_code)}</strong></td>
                    <td><strong>${escapeHtml(r.first_name)}</strong></td>
                    <td><strong>${escapeHtml(r.last_name || '-')}</strong></td>
                    <td>${escapeHtml(r.email)}</td>
                    <td>${escapeHtml(r.phone || '-')}</td>
                    <td><span class="dmr-badge-pill">${escapeHtml(r.type_name)}</span></td>
                    <td><span class="dmr-status-badge dmr-status-${escapeHtml(r.status)}">${escapeHtml(r.status.charAt(0).toUpperCase() + r.status.slice(1))}</span></td>
                    <td>${actionsHtml}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // -------------------------------------------------------------
    // AJAX SEARCH & FILTERS: MEMBERS
    // -------------------------------------------------------------
    window.filterMembersTable = function() {
        var query = $('#dmr-member-search-input').val().trim();
        var typeFilter = $('#dmr-member-type-filter').val() || '';
        var statusFilter = $('#dmr-member-status-filter').val() || '';
        executeAjaxMembersSearch(query, typeFilter, statusFilter);
    };

    function executeAjaxMembersSearch(query, typeFilter, statusFilter) {
        var tbody = $('#dmr-members-table tbody');
        tbody.html('<tr><td colspan="9" style="text-align:center; padding:35px 20px; color:#D32F2F; font-weight:600;"><span class="dmr-table-spinner"></span> Searching backend...</td></tr>');

        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_search_members',
            query: query,
            type: typeFilter,
            status: statusFilter,
            nonce: dmrAdmin.nonce
        }, function(res) {
            if (res.success) {
                renderMembersRows(res.data.members);
            } else {
                tbody.html('<tr><td colspan="9" style="text-align:center; padding:30px; color:#6B7280;">No members found matching search.</td></tr>');
            }
        }).fail(function() {
            tbody.html('<tr><td colspan="9" style="text-align:center; padding:30px; color:#DC2626;">Error retrieving members from server.</td></tr>');
        });
    }

    // -------------------------------------------------------------
    // MEMBER ACTIONS: VIEW, EDIT, DELETE (ALL VIA AJAX)
    // -------------------------------------------------------------
    window.viewMemberDetails = function(target) {
        if (typeof target === 'object' && target !== null) {
            populateMemberDetailsModal(target);
            return;
        }

        var id = parseInt(target, 10);
        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_get_member_details',
            id: id,
            nonce: dmrAdmin.nonce
        }, function(res) {
            if (res.success && res.data.member) {
                populateMemberDetailsModal(res.data.member);
            } else {
                alert(res.data ? res.data.message : 'Error fetching member details.');
            }
        });
    };

    function populateMemberDetailsModal(m) {
        if (typeof m === 'string') {
            try { m = JSON.parse(m); } catch(e) {}
        }
        $('#v-mem-code').text(m.member_id_code || '-');
        $('#v-mem-first-name').text(m.first_name || '-');
        $('#v-mem-last-name').text(m.last_name || '-');
        $('#v-mem-email').text(m.email || '-');
        $('#v-mem-phone').text(m.phone || '-');
        $('#v-mem-type').text(m.type_name || '-');

        var container = $('#v-mem-custom-fields');
        container.empty();

        var formData = {};
        if (m.form_data) {
            if (typeof m.form_data === 'string') {
                try { formData = JSON.parse(m.form_data); } catch(e) {}
            } else {
                formData = m.form_data;
            }
        }

        var keys = Object.keys(formData);
        if (keys.length === 0) {
            container.html('<em style="color:#94A3B8; font-size:13px;">No additional fields submitted.</em>');
        } else {
            var html = '<table style="width:100%; border-collapse:collapse; font-size:13px;">';
            keys.forEach(function(k) {
                var label = k.replace(/_/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
                var val = formData[k];
                html += '<tr style="border-bottom:1px solid #F1F5F9;"><td style="padding:8px 10px; color:#64748B; width:40%; font-weight:600;">' + escapeHtml(label) + '</td><td style="padding:8px 10px; color:#0F172A; font-weight:500;">' + escapeHtml(val) + '</td></tr>';
            });
            html += '</table>';
            container.html(html);
        }

        $('#dmrViewMemberModal').css('display', 'flex').hide().fadeIn(150);
    }

    window.closeViewMemberModal = function() {
        $('#dmrViewMemberModal').fadeOut(150);
    };

    window.openEditMemberModal = function(target) {
        if (typeof target === 'object' && target !== null) {
            populateMemberEditModal(target);
            return;
        }

        var id = parseInt(target, 10);
        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_get_member_details',
            id: id,
            nonce: dmrAdmin.nonce
        }, function(res) {
            if (res.success && res.data.member) {
                populateMemberEditModal(res.data.member);
            } else {
                alert(res.data ? res.data.message : 'Error fetching member details.');
            }
        });
    };

    function populateMemberEditModal(m) {
        if (typeof m === 'string') {
            try { m = JSON.parse(m); } catch(e) {}
        }
        $('#edit-mem-id').val(m.id);
        $('#edit-mem-first-name').val(m.first_name || '');
        $('#edit-mem-last-name').val(m.last_name || '');
        $('#edit-mem-email').val(m.email || '');
        $('#edit-mem-phone').val(m.phone || '');

        $('#dmrEditMemberModal').css('display', 'flex').hide().fadeIn(150);
    }

    window.closeEditMemberModal = function() {
        $('#dmrEditMemberModal').fadeOut(150);
    };

    $('#dmr-edit-member-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#saveMemberBtn');
        btn.prop('disabled', true).text('Saving...');

        $.post(dmrAdmin.ajax_url, form.serialize() + '&action=dmr_save_member&nonce=' + dmrAdmin.nonce, function(res) {
            btn.prop('disabled', false).text('Save Changes');
            if (res.success) {
                closeEditMemberModal();
                filterMembersTable();
            } else {
                alert(res.data.message || 'Error updating member.');
            }
        }).fail(function() {
            btn.prop('disabled', false).text('Save Changes');
            alert('A network error occurred. Please try again.');
        });
    });

    var targetDeleteMemberId = null;
    window.askDeleteMember = function(id, name) {
        targetDeleteMemberId = id;
        $('#delete-member-name').text(name);
        $('#dmrDeleteMemberModal').css('display', 'flex').hide().fadeIn(150);
    };

    window.closeDeleteMemberModal = function() {
        $('#dmrDeleteMemberModal').fadeOut(150);
        targetDeleteMemberId = null;
    };

    $('#confirmDeleteMemberBtn').on('click', function() {
        if (!targetDeleteMemberId) return;
        var btn = $(this);
        var id = targetDeleteMemberId;
        btn.prop('disabled', true).text('Deleting...');

        $.post(dmrAdmin.ajax_url, {
            action: 'dmr_delete_member',
            id: id,
            nonce: dmrAdmin.nonce
        }, function(res) {
            btn.prop('disabled', false).text('Yes, Delete Permanently');
            closeDeleteMemberModal();
            if (res.success) {
                $('#member-row-' + id).fadeOut(300, function() {
                    $(this).remove();
                    if ($('#dmr-members-table tbody tr').length === 0) {
                        $('#dmr-members-table tbody').html('<tr><td colspan="9" style="text-align:center; padding:30px; color:#6B7280;">No members found.</td></tr>');
                    }
                });
            } else {
                alert(res.data.message || 'Error deleting member.');
            }
        }).fail(function() {
            btn.prop('disabled', false).text('Yes, Delete Permanently');
            closeDeleteMemberModal();
            alert('A network error occurred. Please try again.');
        });
    });

    function renderMembersRows(members) {
        var tbody = $('#dmr-members-table tbody');
        tbody.empty();

        if (!members || members.length === 0) {
            tbody.html('<tr><td colspan="9" style="text-align:center; padding:30px; color:#6B7280;">No members found matching search.</td></tr>');
            return;
        }

        members.forEach(function(m, idx) {
            var fullName = escapeHtml(m.first_name + ' ' + (m.last_name || '')).trim();
            var status = m.status || 'approved';
            var statusCap = status === 'approved' ? 'Active' : (status.charAt(0).toUpperCase() + status.slice(1));
            var row = `
                <tr id="member-row-${m.id}">
                    <td>${idx + 1}</td>
                    <td><strong style="color:#D32F2F; font-size:15px;">${escapeHtml(m.member_id_code)}</strong></td>
                    <td><strong>${escapeHtml(m.first_name)}</strong></td>
                    <td><strong>${escapeHtml(m.last_name || '-')}</strong></td>
                    <td>${escapeHtml(m.email)}</td>
                    <td>${escapeHtml(m.phone || '-')}</td>
                    <td><span class="dmr-badge-pill">${escapeHtml(m.type_name)}</span></td>
                    <td><span class="dmr-status-badge dmr-status-${escapeHtml(status)}">${escapeHtml(statusCap)}</span></td>
                    <td>
                        <div class="dmr-action-icons">
                            <button type="button" class="dmr-icon-btn dmr-icon-view" title="View Details" onclick="viewMemberDetails(${m.id})">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                            <button type="button" class="dmr-icon-btn dmr-icon-edit" title="Edit Member" onclick="openEditMemberModal(${m.id})">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </button>
                            <button type="button" class="dmr-icon-btn dmr-icon-delete" title="Delete Member" onclick="askDeleteMember(${m.id}, '${fullName}')">
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
            `;
            tbody.append(row);
        });
    }

    // Explicit keyboard Enter triggers for search inputs across all views
    $(document).on('keydown', '#dmr-type-search-input', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            filterMemberTypes();
        }
    });

    $(document).on('keydown', '#dmr-form-search-input', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            filterFormsTable();
        }
    });

    $(document).on('keydown', '#dmr-reg-search-input', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            filterRegistrationsTable();
        }
    });

    $(document).on('keydown', '#dmr-member-search-input', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            filterMembersTable();
        }
    });
});
