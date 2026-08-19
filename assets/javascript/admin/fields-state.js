/**
 *  Switching form fields on and off from JavaScript.
 *
 *  A field can be rendered disabled from PHP with 'disabled' => true. This is
 *  the same thing at run time, so a panel can respond to what is chosen
 *  elsewhere on it -- greying out the header settings until the server rules
 *  are switched on, say.
 *
 *  Every field carries data-fuse-field with its own name, so nothing here needs
 *  to know how an id was put together.
 *
 *      fuseForms.disableField ('security_header_hsts');
 *      fuseForms.enableField ('security_header_hsts');
 *      fuseForms.setFieldDisabled ('security_header_hsts', on === false);
 *      fuseForms.isFieldDisabled ('security_header_hsts');
 *
 *  A disabled control is not submitted with the form, and the settings form
 *  saves an empty value for anything missing from the request -- so switching a
 *  field off would wipe the setting behind it. Disabling therefore leaves a
 *  hidden copy of the value behind, and enabling takes it away again. That
 *  matches what the PHP does when it renders a disabled field.
 */
(function ($) {
    'use strict';

    var DISABLED = 'fuse-forms-field-disabled';
    var KEEPER = 'data-fuse-kept-value';

    /**
     *  Every element belonging to a named field.
     */
    function field(name) {
        return $('[data-fuse-field="' + name + '"]');
    }

    /**
     *  Keep a disabled control's value posting, or stop once it is enabled.
     */
    function keepValue($el, disabled) {
        var name = $el.attr('name');

        if (!name) {
            return;
        }

        var existing = $el.siblings('[' + KEEPER + ']');

        if (!disabled) {
            existing.remove();

            return;
        }

        if (existing.length) {
            existing.val($el.val());

            return;
        }

        $('<input type="hidden" />')
            .attr('name', name)
            .attr(KEEPER, '')
            .val($el.val())
            .insertAfter($el);
    }

    var fuseForms = window.fuseForms || {};

    /**
     *  Switch a field on or off.
     *
     *  @param string name     The field name.
     *  @param bool   disabled True to disable it, false to enable it.
     *
     *  @return jQuery The field's elements.
     */
    fuseForms.setFieldDisabled = function (name, disabled) {
        disabled = (disabled !== false);

        return field(name).each(function () {
            var $el = $(this);

            $el.toggleClass(DISABLED, disabled);

            /**
             *  A toggle is a list the user clicks, not a control. Its hidden
             *  input carries the value and is left alone so it keeps posting;
             *  only the clicking is switched off, which the click handler
             *  decides from the class.
             */
            if ($el.hasClass('fuse-forms-field-toggle')) {
                if (disabled) {
                    $el.attr('aria-disabled', 'true');
                } else {
                    $el.removeAttr('aria-disabled');
                }

                return;
            }

            $el.prop('disabled', disabled);
            keepValue($el, disabled);
        });
    };

    fuseForms.disableField = function (name) {
        return fuseForms.setFieldDisabled(name, true);
    };

    fuseForms.enableField = function (name) {
        return fuseForms.setFieldDisabled(name, false);
    };

    /**
     *  Is a field currently switched off?
     *
     *  @param string name The field name.
     *
     *  @return bool True when it is.
     */
    fuseForms.isFieldDisabled = function (name) {
        var $field = field(name);

        return $field.length > 0 && $field.first().hasClass(DISABLED);
    };

    /**
     *  The value a field is holding, whether or not it is switched off.
     *
     *  @param string name The field name.
     *
     *  @return string The value.
     */
    fuseForms.fieldValue = function (name) {
        var $field = field(name);

        if (!$field.length) {
            return '';
        }

        if ($field.first().hasClass('fuse-forms-field-toggle')) {
            return $field.first().find('input').val();
        }

        return $field.first().val();
    };

    window.fuseForms = fuseForms;
})(jQuery);
