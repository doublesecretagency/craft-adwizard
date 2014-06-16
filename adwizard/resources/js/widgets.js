
var adWizard = {
    widgets: {
        // Variables
        positions       : {},
        $positionSelect : $('select#types-AdWizard_AdTimeline-positionId'),
        $adSelect       : $('select#types-AdWizard_AdTimeline-adId'),
        // Initialize JS object
        init : function (positionIds) {
            var i, id;
            for (i in positionIds) {
                id = positionIds[i];
                adWizard.widgets.positions[id] = adWizard.widgets.$adSelect.find('option.position-'+id);
            }
            adWizard.widgets.$positionSelect.on('change', function () {
                adWizard.widgets.changeOptions();
            })
        },
        // Change select menu options
        changeOptions : function () {
            var selectedId = adWizard.widgets.$positionSelect.find('option:selected').val();
            adWizard.widgets.$adSelect.find('option').remove();
            adWizard.widgets.positions[selectedId].appendTo(adWizard.widgets.$adSelect);
        },
    }
}