// Initialize Tracker once per page visit
window.adWizardTracker = window.adWizardTracker || { viewed: {} };

// Ad Wizard JS
var adWizard = {
    click: function (id, url) {

        // Set data
        var data = {'id':id};
        data[window.csrfTokenName] = window.csrfTokenValue; // Append CSRF Token

        // Open link in new window
        window.open(url);

        // Tally click
        window.superagent
            .post('/actions/ad-wizard/tracking/click')
            .send(data)
            .type('form')
            .set('X-Requested-With','XMLHttpRequest')
            .end(function (response) {
                var message = JSON.parse(response.text);
                console.log(message);
            })
        ;
    },

    view: function(id) {

        // Set data
        var data = { 'id': id };
        data[window.csrfTokenName] = window.csrfTokenValue; // Append CSRF Token

        // Check if the ad has been tracked as viewed in this visit
        if (id in window.adWizardTracker.viewed === false) {

            // Track in page
            window.adWizardTracker.viewed[id] = 1;

            // Tally view
            window.superagent
                .post('/actions/ad-wizard/tracking/view')
                .send(data)
                .type('form')
                .set('X-Requested-With', 'XMLHttpRequest')
                .end(function(response) {
                    var message = JSON.parse(response.text);
                    console.log(message);
                })
            ;
        } else {
            // Track in page but don't send to server
            // Could be useful to track ads that enter the viewport
            windov.adWizardTracker.viewed[id] += 1;
            console.log(`[AdWizard] Already viewed ${window.adWizardTracker.viewed[id]}.`)
        }
    }
};
