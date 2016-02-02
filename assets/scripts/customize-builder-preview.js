(function ($, _, wp, settings, parent) {

    'use strict';

    var Preview = (function () {
        var DELAY = 2000,
            ESC_KEY_CODE = 27,
            api = parent.wp.customize,
            $body = $('body'),
            $page = $body.add('html'),
            $collapseSidebar = parent.jQuery('.collapse-sidebar'),
            $widgetOverlayClicked = $('#' + settings.panel + '-widget-overlay-clicked'),
            $widgetOverlayHovered = $('#' + settings.panel + '-widget-overlay-hovered'),
            $document = $(document).add(parent.document),
            $window = $(window);

        function Preview() {
            this.$active = null;
            this.busy = false;
            this.widgets = [];

            this.addEventListeners()
                .bindAll();
        }

        Preview.prototype = {
            /**
             * Add all event handlers
             * @returns {Preview}
             */
            addEventListeners: function () {
                var type = settings.panel + '_widget',
                    typeLength = type.length;

                $body.on('click', _.bind(this.onClick, this))
                    .find('a').on('click', function () {
                        /**
                         * preventDefault does not work in customizer
                         */
                        return false;
                    })
                    .end()
                    .on('mouseenter', '[data-widget]', function () {
                        var $this = $(this),
                            offset = $this.offset();

                        if (offset) {
                            $widgetOverlayHovered.css({
                                top: offset.top,
                                left: offset.left,
                                height: $this.outerHeight(),
                                width: $this.outerWidth(),
                                display: 'block'
                            });
                            $widgetOverlayHovered.text($this.attr('title'));
                            $widgetOverlayHovered.data('rel-widget', $(this).data('widget'));
                        }
                    });
                $widgetOverlayHovered.on('mouseleave', function () {
                    $widgetOverlayHovered.css({
                        display: ''
                    });
                }).on('click', function (event) {
                    event.preventDefault();
                    $widgetOverlayHovered.css({
                        display: ''
                    });
                    $('[data-widget="' + $widgetOverlayHovered.data('rel-widget') + '"]').click();
                });
                $page.on('scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove', function () {
                    $page.stop();
                });
                $collapseSidebar.on('click', _.bind(this.onSidebarCollapsed, this));
                api.section.each(_.bind(function (section) {

                    if (section.params.type.substring(0, typeLength) === type) {
                        this.widgets[this.widgets.length] = section;
                        section.expanded.bind('expanded', _.bind(this.onExpanded, this, section));
                    }
                }, this));
                $document.on('keyup', _.bind(this.onKeyUp, this));

                return this;
            },
            /**
             * KeyUp event handler (for "esc" button handling)
             * @param event
             * @returns {Preview}
             */
            onKeyUp: function (event) {
                var code = event.keyCode || event.which;

                if (code === ESC_KEY_CODE) {
                    this.close();
                }

                return this;
            },
            /**
             * Close active widget when closing sidebar
             * @returns {Preview}
             */
            onSidebarCollapsed: function () {

                if ($collapseSidebar.attr('aria-expanded') === 'false') {
                    this.close();
                }

                return this;
            },
            /**
             * Handle widget expanding/collapsing
             * @param widget
             * @param expanded
             * @returns {Preview}
             */
            onExpanded: function (widget, expanded) {

                if (expanded) {
                    this.open(this.getWidget(widget.id));
                } else {
                    this.close(false);
                }

                return this;
            },
            /**
             * Return jQuery widget object
             * @param id
             * @returns {*|HTMLElement}
             */
            getWidget: function (id) {
                var $widget = $('[data-widget="' + id + '"]');

                if (!$widget.length) {
                    $widget = $('[data-widget="' + id.replace(/\[\d+]$/, '') + '"]');
                }

                return $widget;
            },
            /**
             * Click on widget event handler
             * @param event
             * @returns bool
             */
            onClick: function (event) {
                var $widget,
                    widgetId;

                if (event.target.id === $widgetOverlayClicked.attr('id') || event.target.id === $widgetOverlayHovered.attr('id')) {
                    return false;
                }
                $widget = $(event.target).closest('[data-widget]');

                if (!$widget.length) {
                    $widget = $(event.target);
                }
                widgetId = $widget.data('widget');

                if (widgetId) {

                    if ($collapseSidebar.attr('aria-expanded') === 'false') {
                        $collapseSidebar.trigger('click');
                    }

                    if ($widget !== this.$active) {
                        this.close()
                            .open($widget)
                            .toggleExpanded(widgetId, true);
                    }
                } else {
                    this.close();
                }

                return false;
            },
            /**
             * Open widget
             * @param $widget
             * @returns {Preview}
             */
            open: function ($widget) {
                var widgetH,
                    windowH,
                    margin,
                    offset;

                if ($widget.length && $widget !== this.$active) {
                    this.$active = $widget;
                    widgetH = this.$active.outerHeight();
                    windowH = $window.height();
                    margin = widgetH > windowH ? 0 : (windowH - widgetH) / 2;
                    this.$active.addClass('active');
                    $body.addClass('widget-opened');
                    offset = this.$active.offset();

                    if (offset) {
                        $widgetOverlayClicked.css({
                            top: offset.top,
                            left: offset.left,
                            height: this.$active.outerHeight(),
                            width: this.$active.outerWidth()
                        });
                    }
                    $page.animate({
                        scrollTop: this.$active.offset().top - margin
                    }, 'fast', function () {
                        $page.off('scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove');
                    });
                }

                return this;
            },
            /**
             * Close widget
             * @returns {Preview}
             */
            close: function (collapse) {

                if (this.$active) {
                    this.$active.removeClass('active');
                    $body.removeClass('widget-opened');

                    if (typeof collapse === 'undefined' || collapse) {
                        this.toggleExpanded(this.$active.data('widget'), false);
                    }
                    this.$active = null;
                }

                return this;
            },
            /**
             * Expand widget settings
             * @param id
             * @param expanded
             * @returns {Preview}
             */
            toggleExpanded: function (id, expanded) {
                var widget = api.section(id),
                    activeNumber;

                if (widget.params.type === settings.panel + '_widget_switcher') {
                    if (!widget.active()) {
                        activeNumber = parseInt(api(id + '[active_widget]').get(), 10);

                        if (!isNaN(activeNumber)) {
                            widget = api.section(id + '[' + activeNumber + ']');
                        }
                    }
                }

                return widget.expanded(expanded);
            },
            /**
             * Bind all widgets controls for changing
             * @returns {Preview}
             */
            bindAll: function () {
                _.each(this.widgets, _.bind(function (widget) {
                    _.each(widget.controls(), _.bind(function (control) {
                        api(control.id, _.bind(function (value) {
                            /**
                             * Exclude group controls
                             */
                            if (!control.hasOwnProperty('controls')) {
                                this.bind(value, widget);
                            }
                        }, this));
                    }, this));
                }, this));

                return this;
            },
            /**
             * Handle widget control change
             * @param value
             * @param widget
             * @returns {Preview}
             */
            bind: function (value, widget) {
                var timer;

                value.bind(_.bind(function (to, from) {
                    var request,
                        _widget;

                    if (to !== from) {
                        if (timer) {
                            clearTimeout(timer);
                        }

                        if (widget.params.type === settings.panel + '_widget_switcher' && _.isNumber(to)) {
                            _widget = widget;
                            widget = api.section(widget.id + '[' + to + ']');
                        }
                        request = {
                            _id: widget.id,
                            _widget: widget.params.widgetType,
                            _wpnonce: settings.nonce,
                            data: {}
                        };
                        _.each(widget.controls(), function (control) {
                            /**
                             * Exclude group controls
                             */
                            if (!control.hasOwnProperty('controls')) {
                                request[control.id.replace(widget.id, 'data')] = api(control.id).get();
                            }
                        });

                        if (_widget) {
                            widget = _widget;
                        }
                        timer = setTimeout(_.bind(this.onChange, this, request), DELAY);
                    }
                }, this));

                return this;
            },
            /**
             * Change event handler
             * @param data
             * @returns {Preview}
             */
            onChange: function (data) {
                var timer;

                if (!this.busy) {
                    this.load(data);
                } else {
                    if (timer) {
                        clearInterval(timer);
                    }
                    timer = setInterval(_.bind(function () {

                        if (!this.busy) {
                            clearInterval(timer);
                            this.load(data);
                        }
                    }, this), DELAY);
                }

                return this;
            },
            /**
             * Load widget html
             * @param data
             * @returns {Preview}
             */
            load: function (data) {
                this.busy = true;
                wp.ajax.send(settings.action, {
                    data: data,
                    method: 'GET',
                    success: _.bind(function (response) {
                        var $widget = this.getWidget(data._id),
                            widget;

                        if ($widget.length) {
                            widget = api.section(data._id);
                            $widget
                                .html($.trim(response))
                                .attr('title', widget.params.title); // For dynamic widgets
                            this.open($widget)
                                .toggleExpanded(this.$active.data('widget'), true);
                            $widget.find('a').on('click', function () {
                                /**
                                 * Need to add it here because of customizer specific
                                 * preventDefault does not work in customizer
                                 */
                                return false;
                            });
                        }
                    }, this)
                }).always(_.bind(function () {
                    this.busy = false;
                }, this));

                return this;
            }
        };

        return Preview;
    })();

    $(function () {
        new Preview();
    });

})(jQuery, _, wp, window.customizeBuilderPreview || {}, window.parent);