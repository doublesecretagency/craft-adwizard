
$(function () {
	var selectedGroup, $widget, $adSelect, $options;
	$('body').on('change', '.adwizard_adtimeline select#widget8-settings-groupId', function () {
		selectedGroup = $(this).val();
		$widget = $(this).parents('.adwizard_adtimeline');
		$adSelect = $widget.find('select#widget8-settings-adId');
		$adSelect.find('option').each(function () {
			if ($(this).hasClass('group-'+selectedGroup)) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});

		$options = $adSelect.find('option[style!="display: none;"]');
		$options.first().prop('selected', true);

	});
});



/*
var adWizard = {
	widgets: {
		// Variables
		groups       : {},
		$groupSelect : $('select#types-AdWizard_AdTimeline-groupId'),
		$adSelect    : $('select#types-AdWizard_AdTimeline-adId'),
		// Initialize JS object
		init : function (groupIds) {
			var i, id;
			for (i in groupIds) {
				id = groupIds[i];
				adWizard.widgets.groups[id] = adWizard.widgets.$adSelect.find('option.group-'+id);
			}
			adWizard.widgets.$groupSelect.on('change', function () {
				adWizard.widgets.changeOptions();
			})
		},
		// Change select menu options
		changeOptions : function () {
			var selectedId = adWizard.widgets.$groupSelect.find('option:selected').val();
			adWizard.widgets.$adSelect.find('option').remove();
			adWizard.widgets.groups[selectedId].appendTo(adWizard.widgets.$adSelect);
		},
	}
}
*/