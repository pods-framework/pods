jQuery(document).ready(function () {

    jQuery('#filter-right-tables, #filter-left-tables').focus(function() {
       if (jQuery(this).val() == 'Filter Tables') {
           jQuery(this).val('');
       }
    });

    jQuery('#filter-right-tables, #filter-left-tables').blur(function() {
       if (jQuery(this).val() == '') {
           jQuery(this).val('Filter Tables');
       }
    });

    jQuery('#filter-right-tables').keyup(function() {
        var query = jQuery(this).val();

        if (query == '') {
            jQuery('ul.list-tables-right li.right-table').removeClass('invisible');
        } else {
            jQuery("ul.list-tables-right li.right-table").addClass('invisible');
            jQuery('ul.list-tables-right li.right-table:contains("'+query+'")').each(function() {
                jQuery(this).removeClass('invisible');
            });
        }
    });

    jQuery('#filter-left-tables').keyup(function() {
        var query = jQuery(this).val();

        if (query == '') {
            jQuery('ul.list-tables-left li.left-table').removeClass('invisible');
        } else {
            jQuery("ul.list-tables-left li.left-table").addClass('invisible');
            jQuery('ul.list-tables-left li.left-table:contains("'+query+'")').each(function() {
                jQuery(this).removeClass('invisible');
            });
        }
    });

    jQuery.fn.animateHighlight = function(highlightColor, duration) {
        var highlightBg = highlightColor || "#FFFF9C";
        var animateMs = duration || 1500;
        if ( window.console ) console.log(this)
        var originalBg = '#F5F5F5';

        this.stop().css("background-color", highlightBg).css("padding", "4px").css("border-radius", "6px").animate({backgroundColor: originalBg}, animateMs);
    };

    /**
     * Once a table is selected, it updates the manage div to show the table name,
     * and disables all other checkboxes since only one table can be selected
     * per import.
     */
    jQuery('input[type="checkbox"].pods-importable-table').click(function () {
        var checkedTable = jQuery(this).attr('name');
        var checkedValue = jQuery(this).val();
        var checked = jQuery(this).is(':checked');

        if (checked) {
            jQuery('#import-table-progress span').html('<strong>Selected: </strong>' + checkedValue);
            jQuery('#import-table-progress span').animateHighlight();

            jQuery('#continue-to-field-selection').attr('disabled', false);
        } else {
            jQuery('#import-table-progress span').html('Select a Table.');
            jQuery('#continue-to-field-selection').attr('disabled', 'disabled');
        }

        jQuery('input[type="checkbox"].pods-importable-table').each(function () {
            if (jQuery(this).attr('name') !== checkedTable) {
                jQuery(this).attr('disabled', (checked) ? true : false);
            }
        });
    });

    // Step 1 submit
    jQuery('button#continue-to-field-selection').click(function () {
        jQuery('form#pods-import-table-selection').submit();
    });

    /**
     * On click of either the red x, or green check. Dims the opacity of the
     * closest parent tr, and finds all input/select elements within it and disables
     * or enables them.
     */
    jQuery('.enabled-status.status-switcher').click(function () {
        var enabled = jQuery(this).hasClass('enabled');

        if (enabled) {
            jQuery(this).removeClass('enabled').addClass('disabled');
            jQuery(this).closest('tr.pod-column-row').removeClass('enabled').addClass('disabled');

            jQuery(this).parent('tr.pod-column-row').find('input, select').each(function () {
                jQuery(this).attr('disabled', true);
            });
        } else {
            jQuery(this).removeClass('disabled').addClass('enabled');
            jQuery(this).closest('tr.pod-column-row').removeClass('disabled').addClass('enabled');

            jQuery(this).parent('tr.pod-column-row').find('input, select').each(function () {
                jQuery(this).attr('disabled', false);
            });
        }
    });

    /**
     * Ensures at least one column is enabled for converting to a pod,
     * and that at a minimum the pod name is entered.
     */
    jQuery('a#pods-import-create-pod').click(function () {
        if (jQuery('tr.pod-column-row.enabled').length == 0) {
            alert('At least one column must be selected to convert.');
        } else if (jQuery('input[name="new_pod_data[pod_name]"]').val() == '') {
            alert('The Pod Name field is required.');
        } else {
            jQuery('form#pods-import-create-pod').submit();
        }
    });




});