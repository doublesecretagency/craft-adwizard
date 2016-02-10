
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
				// adWizard.widgets.changeOptions();
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