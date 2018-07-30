var $dashboard = $('#dashboard-grid');
var timelineWidgets = '.widget.doublesecretagency\\\\adwizard\\\\widgets\\\\adtimeline';

var adWizard = {
    widgets: {
        // Reset select menu options
        resetAdSelect : function ($groupSelect) {
            var groupId = $groupSelect.val();
            var $widget = $groupSelect.closest('.widget');
            var $adSelect = $widget.find('select.adId');
            $adSelect.find('option').each(function () {
                if ($(this).hasClass('group-'+groupId)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            return $adSelect;
        }
    }
};

// On group change, reset ad select options
$dashboard.on(
    'change',
    timelineWidgets + ' select.groupId',
    function () {
        var $adSelect = adWizard.widgets.resetAdSelect($(this));
        var $options = $adSelect.find('option[style!="display: none;"]');
        $options.first().prop('selected', true);
    }
);
// On widget edit, reset ad select options
$dashboard.on(
    'click',
    timelineWidgets + ' .front > .pane > .icon.settings',
    function () {
        var $adSelect = $(this).closest('.widget').find('select.groupId');
        adWizard.widgets.resetAdSelect($adSelect);
    }
);
