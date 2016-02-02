(function ($, wp, cCBP) {

    'use strict';

    var api = wp.customize;

    api.Builder = api.Builder || {};

    api.Builder.PostControl = api.Control.extend({
        ready: function () {
            api.bind('ready', _.bind(function () {
                this.busy = this.hold = false;
                this.controls = {
                    category: $('[data-customize-setting-link="' + this.id + '[category]"]'),
                    post_id: $('[data-customize-setting-link="' + this.id + '[post_id]"]')
                };
                this.controls
                    .post_id.select2({
                        allowClear: true,
                        ajax: {
                            url: api.settings.url.ajax,
                            quietMillis: 250,
                            type: 'GET',
                            data: _.bind(function (term, page) {
                                return {
                                    action: cCBP.actions.search,
                                    s: term,
                                    page: page,
                                    category: this.controls.category.find('option:selected').val(),
                                    post_type: this.controls.post_id.data('post_type'),
                                    _wpnonce: cCBP.nonces.search
                                };
                            }, this),
                            results: function (response) {
                                var results = {
                                    results: []
                                };

                                if (response.success) {
                                    _.each(response.data, function (title, id) {
                                        results.results.push({
                                            id: id,
                                            text: title
                                        });
                                    });
                                }

                                return results;
                            }
                        },
                        initSelection: function (element, callback) {
                            var $element = $(element),
                                value = $(element).val();

                            if (value) {
                                callback({
                                    id: value,
                                    text: $element.data('value')
                                });
                            }
                        },
                        dropdownCssClass: 'bigdrop',
                        minimumInputLength: 2
                    });

                api.section(this.section()).expanded.bind('expand', _.bind(function (expanded) {

                    if (!expanded) {
                        this.controls.post_id.select2('close');
                    }
                }, this));
            }, this));

            return this;
        }
    });

    $.extend(api.controlConstructor, {
        builder_post: api.Builder.PostControl
    });

})(jQuery, wp, window.customizeControlsBuilderPost || {});