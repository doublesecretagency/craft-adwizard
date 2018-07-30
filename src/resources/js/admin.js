// Delete ad
function deleteAd(id, adName) {
    var message = 'Are you sure you want to delete "'+adName+'"?';
    if (confirm(message)) {
        var url = Craft.getActionUrl('ad-wizard/ads/delete-ad');
        $.post(url, {'id':id}, function(response) {
            if ('SUCCESS' === response) {
                window.location.reload();
            } else {
                alert(response);
            }
        });
    }
}
// Delete group
function deleteGroup(id, groupName) {
    var message = 'Are you sure you want to delete "'+groupName+'"?';
    if (confirm(message)) {
        var url = Craft.getActionUrl('adwizard/ad-groups/delete-group');
        $.post(url, {'id':id}, function(response) {
            if ('SUCCESS' === response) {
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
    // Behavior for "New Ad" button
    $('#new-ad').on('click', function () {
        // Get id of current group
        var id = $('#sidebar a.sel').data('key').replace(/group:/, '');
        // If id is not valid
        if (!handles.hasOwnProperty(id)) {
            // Set to default id
            id = Object.keys(handles)[0];
        }
        // Redirect
        window.location = Craft.getUrl('ad-wizard/'+handles[id]+'/new');
    });
});