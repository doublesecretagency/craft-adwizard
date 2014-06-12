// Ad Wizard JS
var adWizard = {
    click: function (id, url) {
        window.open(url);
        var url = '/actions/adWizard/click';
        jQuery.post(url, {'id':id});
    }
}