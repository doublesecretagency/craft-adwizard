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

    view: function(id, options) {
        const defaultOptions = {
            oncePerPage: true,
            debug: false,
        };
        const opts = Object.assign(defaultOptions, options);

        // Set data
        var data = { 'id': id };
        data[window.csrfTokenName] = window.csrfTokenValue; // Append CSRF Token

        // Check if the ad has been tracked in this visit
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
                    if (opts.debug) {
                        var message = JSON.parse(response.text);
                        console.log(message);
                    }
                })
            ;
        } else {

            // If false, allows tracking views multiple times
            if (!opts.oncePerPage) {

                // Tally view
                window.superagent
                    .post('/actions/ad-wizard/tracking/view')
                    .send(data)
                    .type('form')
                    .set('X-Requested-With', 'XMLHttpRequest')
                    .end(function(response) {
                        if (opts.debug) {
                            var message = JSON.parse(response.text);
                            console.log(message);
                        }
                    })
                ;
            }

            // Track in page
            window.adWizardTracker.viewed[id] += 1;
            if (opts.debug) {
                console.log(`[Ad Wizard] Ad ${id} viewed ${window.adWizardTracker.viewed[id]}.`)
            }
        }
    }
};

