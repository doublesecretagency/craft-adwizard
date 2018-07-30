(function($) {
    /** global: Craft */
    /** global: Garnish */
    Craft.AdTimelineWidget = Garnish.Base.extend(
        {
            chart: null,
            $widget: null,
            $body: null,
            $subheader: null,

            init: function(widgetId, adId) {

                this.$widget = $('#widget' + widgetId);
                this.$body = this.$widget.find('.body:first');
                this.$subheader = $('<p>Ad activity from this month.</p>').appendTo(this.$body);
                this.$chartContainer = $('<div id="chart' + widgetId + '" class="chart hidden"></div>').appendTo(this.$body);
                this.$error = $('<div class="error"/>').appendTo(this.$body);

                // Request orders report
                var requestData = {adId: adId};

                // Get data and render chart
                Craft.postActionRequest('ad-wizard/charts/get-ad-timeline-data', requestData, $.proxy(function(response, textStatus) {
                    if (textStatus === 'success' && typeof(response.error) === 'undefined') {

                        // Render chart
                        this.chart = bb.generate({
                            bindto: '#chart' + widgetId,
                            data: {
                                x: 'Day',
                                type: 'line',
                                columns: response.data
                            },
                            axis: {
                                x: {
                                    type: 'timeseries',
                                    tick: {
                                        format: '%B %e'
                                    }
                                },
                                y: {
                                    label: {
                                        text: 'Daily Totals',
                                        position: 'outer-middle'
                                    }
                                }
                            },
                            legend: {
                                position: 'right'
                            }
                        });

                        // Unhide chart
                        this.$chartContainer.removeClass('hidden');

                        // Resize chart when grid is refreshed
                        window.dashboard.grid.on('refreshCols', $.proxy(this, 'handleGridRefresh'));
                    }
                    else {
                        // Error
                        var msg = Craft.t('An unknown error occurred.');

                        // Output error
                        if (typeof(response) !== 'undefined' && response && typeof(response.error) !== 'undefined') {
                            msg = response.error;
                        }

                        // Show error
                        this.$error.html(msg);
                        this.$error.removeClass('hidden');
                    }

                }, this));

                this.$widget.data('widget').on('destroy', $.proxy(this, 'destroy'));

                Craft.AdTimelineWidget.instances.push(this);
            },

            // Dynamically resize chart
            handleGridRefresh: function() {
                this.chart.resize({
                    width:  this.$chartContainer.width(),
                    height: this.$chartContainer.height()
                });
            },

            // Destroy widget with chart
            destroy: function() {
                Craft.AdTimelineWidget.instances.splice($.inArray(this, Craft.AdTimelineWidget.instances), 1);
                this.base();
            }

        }, {
            instances: []
        });
})(jQuery);
