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

        // If not yet tracking views, begin tracking
        if (!(id in window.adWizardTracker.viewed)) {
            window.adWizardTracker.viewed[id] = 0;
        }

        // If already viewed and limited to once per page, bail
        if (window.adWizardTracker.viewed[id] && opts.oncePerPage) {
            return;
        }

        // Increment tracker
        window.adWizardTracker.viewed[id] += 1;

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

        // If debugging, show console message
        if (opts.debug) {
            console.log(`[Ad Wizard] Ad ${id} viewed ${window.adWizardTracker.viewed[id]}.`)
        }

    }
};

