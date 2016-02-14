var adWizard = {
	widgets: {
		// Initialize JS object
		init : function () {
			$('body').on('change', '.adwizard_adtimeline select.groupId', function () {
				var $adSelect = adWizard.widgets.resetAdSelect($(this));
				var $options = $adSelect.find('option[style!="display: none;"]');
				$options.first().prop('selected', true);
			});
		},
		// Reset select menu options
		resetAdSelect : function ($groupSelect) {
			var selectedGroup = $groupSelect.val();
			var $widget = $groupSelect.parents('.adwizard_adtimeline');
			var $adSelect = $widget.find('select.adId');
			$adSelect.find('option').each(function () {
				if ($(this).hasClass('group-'+selectedGroup)) {
					$(this).show();
				} else {
					$(this).hide();
				}
			});
			return $adSelect;
		},
	}
}