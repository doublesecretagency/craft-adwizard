(function($) {
    /** global: Craft */
    /** global: Garnish */
    Craft.AdGroupSwitcher = Garnish.Base.extend(
        {
            $groupSelect: null,
            $spinner: null,

            init: function() {
                this.$redirect = $('input[name="redirect"]');
                this.$redirect.val(groupRedirects[0]);

                this.$groupSelect = $('#adGroup');
                this.$spinner = $('<div class="spinner hidden"/>').insertAfter(this.$groupSelect.parent());

                this.addListener(this.$groupSelect, 'change', 'onGroupChange');
            },

            onGroupChange: function(ev) {
                this.$spinner.removeClass('hidden');

                Craft.postActionRequest('ad-wizard/ads/switch-ad-group', Craft.cp.$primaryForm.serialize(), $.proxy(function(response, textStatus) {
                    this.$spinner.addClass('hidden');

                    if (textStatus === 'success') {
                        this.trigger('beforeGroupChange');

                        var groupId = this.$groupSelect.val();

                        var r = groupRedirects;
                        var uri = (groupId in r ? r[groupId] : r[0]);

                        this.$redirect.val(uri);

                        var $fields = $('#fields');

                        $fields.html(response.fieldsHtml);
                        Craft.initUiElements($fields);
                        Craft.appendHeadHtml(response.headHtml);
                        Craft.appendFootHtml(response.bodyHtml);

                        this.trigger('groupChange');
                    }
                }, this));
            }

        });
})(jQuery);
