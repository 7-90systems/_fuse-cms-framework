/**
 *  @package fuse-cms-framework
 *
 *  @version 1.0
 *
 *  This is the JavaScript functionality for our forms.
 */

jQuery (document).ready (function () {
    
    fuseFormsSetup ();
    fuseFormRequired ();
    fuseCheckFormSubmission ();

});




/**
 *  Set up our forms.
 */
function fuseFormsSetup () {
    // Set up our container display
    jQuery ('.fuse-forms-container').each (function () {
        let container = jQuery (this);
        let tab_buttons = container.find ('.fuse-form-panel-tabs li a');
        let panels = container.find ('.fuse-forms-panel');
        
        tab_buttons.removeClass ('active').first ().addClass ('active');
        panels.hide ().first ().show ();
    });
    
    jQuery ('.fuse-forms-container').on ('click', '.fuse-form-panel-tabs li a', function (e) {
        e.preventDefault ();
        
        let btn = jQuery (this);
        let container = btn.closest ('.fuse-forms-container');
        
        container.find ('.fuse-forms-panel').hide ();
        container.find ('.fuse-form-panel-tabs li a').removeClass ('active');
        
        jQuery (btn.attr ('href')).show ();
        btn.addClass ('active');
    });
    
    // Set up our display conditions
    jQuery ('.fuse-forms-container').on ('input', 'input[type=text]', _checkFuseFormFieldConditions);
    jQuery ('.fuse-forms-container').on ('input', 'input[type=number]', _checkFuseFormFieldConditions);
    jQuery ('.fuse-forms-container').on ('input', 'input[type=url]', _checkFuseFormFieldConditions);
    jQuery ('.fuse-forms-container').on ('input', 'input[type=email]', _checkFuseFormFieldConditions);
    jQuery ('.fuse-forms-container').on ('input', 'input[type=hidden]', _checkFuseFormFieldConditions);
    jQuery ('.fuse-forms-container').on ('input', 'textarea', _checkFuseFormFieldConditions);
    
    jQuery ('.fuse-forms-container').on ('change', 'input[type=checkbox]', _checkFuseFormFieldConditions);
    jQuery ('.fuse-forms-container').on ('change', 'input[type=radio]', _checkFuseFormFieldConditions);
    jQuery ('.fuse-forms-container').on ('change', 'select', _checkFuseFormFieldConditions);
    
    _checkFuseFormFieldConditions ();
    
    // Set up our various fields
    _fuseFormsSetupToggleFields ();
} // fuseFormsSetup ()




/**
 *  Check fields for conditions.
 */
function _checkFuseFormFieldConditions () {
    /**
     *  Check field visibility.
     */
    jQuery ('.fuse-forms-container').find ('input, select, textarea, .fuse-field-group').each (function () {
        let element = jQuery (this);
        let container = element.closest ('.fuse-forms-panel-field-container, .fuse-field-group-column');
        let conditions = element.data ('conditions');
// console.log ("Checking field conditions '" + conditions + "'...");
        
        if (typeof conditions !== 'undefined' && conditions.length > 0) {
// console.log (" - Field has conditions!");
            if (_checkFieldConditions (conditions) !== true) {
                container.hide ();
            } // if ()
            else {
                container.show ();
            } // else
        } // if ()
    });
    
    
    /**
     *  Check panel visibility.
     */
    jQuery ('.fuse-forms-panel').each (function () {
        let panel = jQuery (this);
        
        let conditions = panel.data ('conditions');
// console.log ("Checking panel conditions...");
        
        if (typeof conditions !== 'undefined' && conditions.length > 0) {
// console.log (" - Panel has conditions!");
            let panel_link = jQuery ('.fuse-form-panel-tabs').find ('a[href$=#' + panel.attr ('id') + ']').closest ('li');
            
            if (_checkFieldConditions (conditions) !== true) {
                panel.hide ();
                panel_link.hide ();
            } // if ()
            else {
                panel_link.show ();
            } // else
        } // if ()
    });
} // _checkFuseFormFieldConditions ()




/**
 *  Set up our togle fields.
 */
function _fuseFormsSetupToggleFields () {
    // Set up listeners
    jQuery ('.fuse-forms-container').on ('click', '.fuse-forms-field-toggle li', function (e) {
        e.preventDefault ();
// confirm ("Clicked on option '" + jQuery (this).data ('value') + "'");
        let btn = jQuery (this);
        
        if (btn.hasClass ('selected') === false) {
            let container = btn.closest ('.fuse-forms-field-toggle');
            let fields = container.find ('li');
            let value = btn.data ('value');
            
            fields.removeClass ('selected');
            btn.addClass ('selected');
            
            container.data ('value', value);
            container.find ('input').val (value).trigger ('input');
        } // if ()
    });
    
    // Check for a default value
    jQuery ('.fuse-forms-field-toggle').each (function () {
        let set_value = jQuery (this).data ('value');
        
        if (set_value.length == 0) {
            jQuery (this).find ('li').first ().trigger ('click');
        } // if ()
    });
} // _fuseFormsSetupToggleFields ()




/**
 *  Check a field value for a comparison
 */
function _checkFieldConditions (conditions) {
    let condition_met = true;
    
    let field_id;
    let comparison_value;
    let comparison;
    let field_value;
            
    let display = true;
            
    for (let i in conditions) {
        field_id = conditions [i].field;
        comparison_value = conditions [i].value;
        comparison = conditions [i].comparison;
                
        let field = jQuery ('#fuse-form-field-' + field_id);
                
        if (typeof field !== 'undefined') {
            field_value = field.val ();
                    
            if (comparison == '=' || comparison == 'equals') {
                if (field_value != comparison_value) {
                    condition_met = false;
                } // if ()
            } // if ()
            else if (comparison == '!' || comparison == '!=' || comparison == 'not') {
                if (field_value == comparison_value) {
                    condition_met = false;
                } // if ()
            } // else if()
            else if (comparison == 'in') {
                if (_isValueInList (field_value, comparison_value) === false) {
                    condition_met = false;
                } // if ()
            } // else if ()
            else if (comparison == 'not in') {
                if (_isValueInList (field_value, comparison_value) === true) {
                    condition_met = false;
                } // if ()
            } // else if ()
            else if (comparison == 'any') {
// console.log ("Checking ANY comparison: '" + field_value + "'");
                if (typeof field_value === 'undefined' || field_value.length == 0) {
                    condition_met = false;
                } // if ()
            } // else if ()
            else if (comparison == 'empty') {
                if (typeof field_value !== 'undefined' && field_value.length > 0) {
                    condition_met = false;
                } // if ()
            } // else if ()
        } // if ()
    } // for ()
    
    return condition_met;
} // _checkFieldConditions ()

/**
 *  Check to see if a value is in an array of values.
 *
 *  @param mixed value The value to check.
 *  @param array value_list The list of values.
 *
 *  @return bool True if the value exists or false if the value does not exist.
 *  Note that it the value is an array we will search for all array values.
 */
function _isValueInList (value, value_list) {
    let in_list = false;
    let i;
    
    if (typeof (value == 'array')) {
        let in_list_count = 0;
        
        for (i in value) {
            for (let j in value_list) {
                if (values_list [j] == value [i]) {
                    in_list_count++;
                } // if ()
            } // for ()
        } // for ()
        
        if (in_list_count == values.length) {
            in_list = true;
        } // if ()
    } // if ()
    else {
        for (i in value_list) {
            if (value_list [i] == value) {
                in_list = true;
            } // if ()
        } // for ()
    } // else
    
    return in_list;
} // _isValueInList ()




/**
 *  Check our forms on submission.
 */
function fuseCheckFormSubmission () {
    jQuery ('.fuse-form-container input[type="submit"]').on ('click', function (e) {
        let form = jQuery (this).closest ('form');
        
        form.find ('input, select, textarea').trigger ('keyup').trigger ('change');
        
        let invalid_fields = form.find ('label.invalid');
        
        if (invalid_fields.length > 0) {
            e.preventDefault ();
            
            let field = jQuery (invalid_fields [0]);
            let panel_id = field.closest ('.fuse-forms-panel').attr ('id');
            
            let btn = jQuery ('a[href="#' + panel_id + '"]');
            btn.trigger ('click');
        } // if ()

    });
}// fuseCheckFormSubmission ()




/**
 *  Set up the functionality to show required fields.
 */
function fuseFormRequired () {
    jQuery ('.fuse-forms-container input, .fuse-forms-container select, .fuse-forms-container textarea').on ('input', _fuseFormCheckRequired);
    jQuery ('.fuse-forms-container select').change (_fuseFormCheckRequired);
    
    jQuery ('.fuse-forms-container input, .fuse-forms-container textarea').on ('keyup', _fuseFormCheckValid);
    jQuery ('.fuse-forms-container input').on ('change', _fuseFormCheckValid);
} // fuseFormRequired ()

function _fuseFormCheckRequired () {
    let el = jQuery (this);
    
    if (el.prop ('required')) {
        let label = el.parent ().siblings ('label');
        
        if (el.val ().length > 0) {
            label.removeClass (['invalid', 'required-empty', 'admin-bold', 'admin-red']);
        } // if ()
        else {
            label.addClass (['invalid', 'required-empty', 'admin-bold', 'admin-red']);
        } // else
    } // if ()
} // _fuseFormCheckRequired ()

function _fuseFormCheckValid () {
    let field = jQuery (this);
    let label = field.parent ().siblings ('label');
    let is_valid = false;
    
    switch (field.attr ('type')) {
        case 'number':
            is_valid = _fuseCheckNumberFieldValue (field);
            break;
        case 'url':
            is_valid = _fuseCheckUrlFieldValue (field.val ());
            break;
        case 'email':
            is_valid = _fuseCheckEmailFieldValue (field.val ());
            break;
        default:
            if (field.prop ('required') === false || field.val ().length > 0) {
                is_valid = true;
            } // if ()
    } // switch ()
console.log (" - Result: '" + is_valid + "'");

        
    if (is_valid === true) {
        label.removeClass (['invalid', 'admin-bold', 'admin-red']);
    } // if ()
    else {
        label.addClass (['invalid', 'admin-bold', 'admin-red']);
    } // else
} // _fuseFormCheckValid ()

function _fuseCheckEmailFieldValue (value) {
    let checker = new RegExp ('^.+@.+$');
    return checker.test (value);
} // _fuseCheckEmailFieldValue ()

function _fuseCheckUrlFieldValue (value) {
    let is_valid = false;
    let url;
    
    try {
        url = new URL (value);
        is_valid = true;
    } // try
    catch (_) {
        is_valid = false;
    } // catch ()
    
    return is_valid;
} // _fuseCheckUrlFieldValue ()

function _fuseCheckNumberFieldValue (field) {
    let is_valid = true;
    let value = Number (field.val ());
    
    // Check if required
    if (field.prop ('required') === true && String (value).length == 0) {
        is_valid = false;
    } // if ()
    
    // check min value
    if (field.attr ('min') !== false && field.attr ('min') > value) {
        is_valid = false;
    } // if ()
    
    // check max value
    if (field.attr ('max') !== false && field.attr ('max') < value) {
        is_valid = false;
    } // if ()
    
    return is_valid;
} // _fuseCheckNumberFieldValue ();