(function ($, wp, _, settings) {

    'use strict';

    var api = wp.customize,
        sections = {};

    api.Builder = api.Builder || {};

    api.Builder.WidgetSection = api.Section.extend({
        ready: function () {
            api.bind('ready', _.bind(this._ready, this));

            return this;
        },
        _ready: function () {
            _.each(this.controls(), _.bind(function (control) {

                if (control.params.type === settings.panel + '_button' && this.id + '[remove]' === control.id) {
                    control.container
                        .find('.button').on('click', _.bind(this.onClick, this));
                }
            }, this));

            return this;
        },
        onClick: function (event) {
            event.preventDefault();
            var switcherId = this.id.replace(/\[\d+]$/, ''),
                switcher = api.section(switcherId),
                duration;

            api(switcherId + '[active_widget]').set('');
            this.active(false);
            duration = this.defaultExpandedArguments.duration;
            setTimeout(function () {
                switcher.active(true);
                switcher.expanded(true);
            }, typeof duration === 'string' ? $.fx.speeds[duration] : duration);

            return this;
        }
    });

    api.Builder.WidgetSwitcherSection = api.Section.extend({
        $body: $('body'),
        $button: $(),
        $list: $(),
        ready: function () {
            this.$button = this.container.find('.add-new-' + settings.panel + '-widget');
            this.$list = $('#available-' + this.$button.data('widget-id') + '-widgets');
            this.$body.on('click', _.bind(this.onClick, this));
            this.$list.find('button').on('click', _.bind(function (event) {
                event.preventDefault();
                var number = parseInt($(event.target).data('number'), 10),
                    section,
                    duration;

                if (!isNaN(number)) {
                    api(this.id + '[active_widget]').set(number);
                    this.active(false);
                    section = api.section(this.id + '[' + number + ']');
                    section.active(true);
                    duration = section.defaultExpandedArguments.duration;
                    setTimeout(function () {
                        section.expanded(true);
                    }, typeof duration === 'string' ? $.fx.speeds[duration] : duration);
                }
            }, this));

            return this;
        },
        onClick: function (event) {

            if (this.$button[0] === event.target) {
                event.preventDefault();
                this.toggle();
            } else if (!$(event.target).hasClass('add-new-' + settings.panel + '-widget')) {
                this.expanded(false);
            }

            return this;
        },
        toggle: function () {
            this.expanded(!this.expanded());

            return this;
        },
        open: function () {
            this.$button.addClass('active');
            this.$body.addClass('adding-' + settings.panel + '-widget');
            this.$list.addClass('active');

            return this;
        },
        closeAll: function () {
            this.$button.removeClass('active');
            this.$body.removeClass('adding-' + settings.panel + '-widget');
            this.$list.removeClass('active');

            return this;
        },
        onChangeExpanded: function (expanded, args) {

            if (expanded) {
                this.open();
                api.panel(this.panel()).expanded(true);
            } else {
                this.closeAll();
            }
            expanded = false;
            api.Section.prototype.onChangeExpanded.call(this, expanded, args);

            return this;
        }
    });

    sections[settings.panel + '_widget'] = api.Builder.WidgetSection;
    sections[settings.panel + '_widget_switcher'] = api.Builder.WidgetSwitcherSection;
    $.extend(api.sectionConstructor, sections);

})(jQuery, wp, _, window.customizeBuilder || {});