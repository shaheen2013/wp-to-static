(function($) {
    "use strict";

    function initializeDataTable() {
        const table = $('#datatable');
        const th = table.find('thead tr th');

        if (table.find('tbody tr').length > 0) {
            table.DataTable({
                "columnDefs": [
                    {
                        "targets": 0,
                        "orderable": false
                    }
                ]
            });
        } else {
            table.find('tbody').append('<tr><td colspan="' + th.length + '" class="text-center">No data available</td></tr>');
        }
    }

    function initializeFormValidation() {
        $('input[required]').on('blur input', function () {
            const inputField = $(this);

            if (inputField.val().trim() === '') {
                if (inputField.siblings('.error-message').length === 0) {
                    inputField.after('<div class="error-message" style="color: red;">This field is required</div>');
                }
            } else {
                inputField.siblings('.error-message').remove();
            }
        });
    }

    function initializeExportSingle() {
        $(document).on('click', '.export-view-link', function (e) {
            e.preventDefault();

            const postId = $(this).data('post-id');
            if (!postId) {
                console.error('Post ID is missing');
                return;
            }

            const ids = [postId];
            const btnText = $(this).text();
            $(this).text('Exporting...');

            showProgressIndicator("progressModal");
            processIdsSequentially(ids, 'mw_export_single', 0);
            $(this).text(btnText);
        });
    }

    function initializeExportAll() {
        $('#export-all').on('click', function (e) {
            e.preventDefault();

            const btnText = $(this).text();
            $(this).text('Exporting...');

            const ids = $('input[name="ids[]"]:checked').map(function () {
                return $(this).val();
            }).get();

            if (ids.length === 0) {
                alert('Please select at least one post to export.');
                $(this).text(btnText);
                return;
            }

            showProgressIndicator("progressModal");
            processIdsSequentially(ids, "mw_export_multiple", 0);
            $(this).text(btnText);
        });
    }

    function addProgressModal() {
        const progressModalHTML = `
            <div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="progressModalLabel">Progress</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="progress">
                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <p id="progressText" class="text-center mt-2">Starting...</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(progressModalHTML);
    }

    function updateProgress(currentValue, maxValue) {
        const percentage = (currentValue / maxValue) * 100;
        $('#progressBar').css('width', percentage + '%').attr('aria-valuenow', percentage);
        $('#progressText').text('Progress: ' + currentValue + ' out of ' + maxValue);
    }

    function showProgressIndicator(modalId) {
        const progressModal = new bootstrap.Modal($(`#${modalId}`)[0], {
            backdrop: 'static',
            keyboard: false
        });

        $('#progressBar').css('width', 0 + '%').attr('aria-valuenow', 0);
        $('#progressText').text('Starting...');

        progressModal.show();
    }

    async function processIdsSequentially(ids, action, current = 0) {
        let currentProgress = current;
        const totalProgress = ids.length;
        let message = "Processing...";
        let progressBarColor = 'progress-bar-striped progress-bar-animated';
        addProgressModal();
    
        for (const id of ids) {
            try {
                message = "Download started!";
                const res = await exportData(id, ids, action, currentProgress + 1, totalProgress);
                if (res.success) {
                    if (res.data.data.download_url.length > 0) {
                        window.location.href = res.data.data.download_url;
                    }
                    message = "Download Completed!";
                } else {
                    message = res.data.message;
                    progressBarColor = 'bg-danger';  // Red progress bar on failure
                }
    
                updateProgress(currentProgress + 1, totalProgress, progressBarColor);
            } catch (error) {
                console.error('Error processing ID:', id, error);
                message = `Failed to process ID ${id}. Error: ${error.message}`;
                progressBarColor = 'bg-danger';
                updateProgress(currentProgress + 1, totalProgress, progressBarColor);
            }
    
            currentProgress++;
        }
    
        $('#progressText').text(message);
        $('#progressBar').removeClass('progress-bar-striped progress-bar-animated').addClass(progressBarColor);
    }

    async function exportData(postId, postIds, action, current = 1, total = 1) {
        if (!postId) {
            console.error('Post ID is missing');
            return;
        }

        try {
            const response = await $.ajax({
                url: mwStatic.ajax_url,
                type: 'POST',
                data: {
                    action: action,
                    post_id: postId,
                    post_ids: postIds,
                    total: total,
                    current: current,
                    nonce: mwStatic.nonce
                }
            });

            if (response.success) {
                return response;
            } else {
                return response;
            }
        } catch (error) {
            console.error('AJAX error', error);
            return false;
        }
    }

    function idSelection() {
        const table = $('#datatable').DataTable();
        const headerSelectAllCheckbox = $('thead .select-all');
        const footerSelectAllCheckbox = $('tfoot .select-all');
        const totalExportable = $('.total_exportable');
    
        headerSelectAllCheckbox.on('change', function () {
            const isChecked = $(this).prop('checked');
            footerSelectAllCheckbox.prop({ checked: isChecked, indeterminate: false });
            toggleRowCheckboxes(isChecked);
            updateTotalSelections();
        });
    
        footerSelectAllCheckbox.on('change', function () {
            const isChecked = $(this).prop('checked');
            headerSelectAllCheckbox.prop({ checked: isChecked, indeterminate: false });
            toggleRowCheckboxes(isChecked);
        });
    
        $('#datatable').on('change', '.row-checkbox', function () {
            updateMasterCheckboxes();
            updateTotalSelections();
        });
    
        function toggleRowCheckboxes(isChecked) {
            $('.row-checkbox', table.rows({ search: 'applied' }).nodes()).prop('checked', isChecked);
        }
    
        function updateMasterCheckboxes() {
            const rowCheckboxes = $('.row-checkbox', table.rows({ search: 'applied' }).nodes());
            const allChecked = rowCheckboxes.length === rowCheckboxes.filter(':checked').length;
            const noneChecked = rowCheckboxes.filter(':checked').length === 0;
    
            headerSelectAllCheckbox.prop({
                indeterminate: !allChecked && !noneChecked,
                checked: allChecked,
            });
    
            footerSelectAllCheckbox.prop({
                indeterminate: !allChecked && !noneChecked,
                checked: allChecked,
            });
        }

        function updateTotalSelections() {
            const selectedCount = $('.row-checkbox:checked', table.rows({ search: 'applied' }).nodes()).length;
            totalExportable.text(selectedCount);
        }

        table.on('draw', function () {
            updateMasterCheckboxes();
            updateTotalSelections()
        });
    }

    function activeLicense(){
        $("#mw-active-license").on("submit", function(e){
            e.preventDefault();
            var form = $(this);
            var formData = new FormData(form[0]);
            formData.append('action', 'active_license');
            formData.append('nonce', mwStatic.nonce);
    
            $.ajax({
                url: mwStatic.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload()
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function (xhr, status, error) {
                    alert('Error: ' + error);
                }
            });
    
            return false;
        });
    }

    function deactiveLicense(){
        $("#mw-deactive-license").on("submit", function(e){
            e.preventDefault();
            if (!confirm('Are you sure you want to deactivate the license?')) {
                return false;
            }
            var form = $(this);
            var formData = new FormData(form[0]);
            formData.append('action', 'deactive_license');
            formData.append('nonce', mwStatic.nonce);
    
            $.ajax({
                url: mwStatic.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload()
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function (xhr, status, error) {
                    alert('Error: ' + error);
                }
            });
    
            return false;
        });
    }


    function hide_category(){
        $('#postType').on('change', function() {
            if ($(this).val() === 'page') {
                $('#category-selector').hide();
            } else {
                $('#category-selector').show();
            }
        });
        $('#postType').trigger('change');
    }
    
    

    $(document).ready(function () {
        addProgressModal();
        initializeDataTable();
        initializeFormValidation();
        initializeExportSingle();
        initializeExportAll();
        idSelection()
        activeLicense()
        deactiveLicense()
        hide_category()
    });

})(jQuery);
