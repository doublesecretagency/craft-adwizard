(function($) {
    /** global: Craft */
    /** global: Garnish */
    Craft.GroupTotalsWidget = Garnish.Base.extend(
        {
            chart: null,
            $widget: null,
            $body: null,
            $subheader: null,

            init: function(widgetId, groupId) {

                this.$widget = $('#widget' + widgetId);
                this.$body = this.$widget.find('.body:first');
                this.$subheader = $('<p>Lifetime totals of ads in this group.</p>').appendTo(this.$body);
                this.$chartContainer = $('<div id="chart' + widgetId + '" class="chart hidden"></div>').appendTo(this.$body);
                this.$error = $('<div class="error"/>').appendTo(this.$body);

                // Request orders report
                var requestData = {groupId: groupId};

                // Get data and render chart
                Craft.postActionRequest('ad-wizard/charts/get-group-totals-data', requestData, $.proxy(function(response, textStatus) {
                    if (textStatus === 'success' && typeof(response.error) === 'undefined') {

                        // Shift ad names from response data
                        var adNames = response.data.shift();

                        // Render chart
                        this.chart = bb.generate({
                            bindto: '#chart' + widgetId,
                            data: {
                                type: 'bar',
                                columns: response.data
                            },
                            axis: {
                                rotated: true,
                                x: {
                                    type: 'category',
                                    categories: adNames
                                },
                                y: {}
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

                Craft.GroupTotalsWidget.instances.push(this);
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
                Craft.GroupTotalsWidget.instances.splice($.inArray(this, Craft.GroupTotalsWidget.instances), 1);
                this.base();
            }

        }, {
            instances: []
        });
})(jQuery);
