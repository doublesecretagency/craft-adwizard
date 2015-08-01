// Delete ad
function deleteAd(id, adName) {
	var message = 'Are you sure you want to delete "'+adName+'"?';
	if (confirm(message)) {
		var url = Craft.getActionUrl('adWizard/deleteAd');
		$.post(url, {'id':id}, function(response) {
			if ('SUCCESS' == response) {
				window.location.reload();
			} else {
				alert(response);
			}
		});
	}
}
// Delete position
function deletePosition(id, positionName) {
	var message = 'Are you sure you want to delete "'+positionName+'"?';
	if (confirm(message)) {
		var url = Craft.getActionUrl('adWizard/deletePosition');
		$.post(url, {'id':id}, function(response) {
			if ('SUCCESS' == response) {
				window.location.reload();
			} else {
				alert(response);
			}
		});
	}
}

// LOAD EVENTS

$(function () {
	$('input[name="sourceType"]').on('change', function () {
		var sourceType = $(this).val();
		$('.sourceTypeOptions').hide();
		$('#'+sourceType).show();
	});
});