(function (wp) {
    var registerBlockType = wp.blocks.registerBlockType;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var SelectControl = wp.components.SelectControl;
    var Placeholder = wp.components.Placeholder;
    var Spinner = wp.components.Spinner;
    var el = wp.element.createElement;
    var useState = wp.element.useState;
    var useEffect = wp.element.useEffect;
    var __ = wp.i18n.__;
    var apiFetch = wp.apiFetch;
    
    registerBlockType ('dynamic-select-block/main', {
        title: __('Fuse FAQs List'),
        icon: 'editor-help',
        category: 'widgets',
        
        attributes: {
            selectedOption: {
                type: 'string',
                default: '',
            },
            blockId: {
                type: 'string',
                default: '',
            }
        },
        
        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var selectedOption = attributes.selectedOption;
            var blockId = attributes.blockId;
            
            // Generate unique ID for the block if not already set
            useEffect(function() {
                if (!blockId) {
                    setAttributes ({
                      blockId: 'dynamic-select-' + Math.random ().toString (36).substring (2, 15)
                    });
                }
            }, []);
            
            // State for options
            var optionsState = useState ([]);
            var options = optionsState [0];
            var setOptions = optionsState [1];
            
            var loadingState = useState (true);
            var isLoading = loadingState [0];
            var setIsLoading = loadingState [1];
            
            var errorState = useState (null);
            var error = errorState [0];
            var setError = errorState [1];
            
            // Fetch options from the server
            useEffect (function () {
                setIsLoading (true);
                
                apiFetch ({ path: '/dynamic-select-block/v1/options'})
                    .then (function (response) {
                        var fetchedOptions = response.map (function (option) {
                            return {
                                value: option.value.toString (),
                                label: option.label
                            };
                        });
                        
                        // Add empty option at the beginning
                        fetchedOptions.unshift ({
                            value: '',
                            label: '-- Select an option --'
                        });
                        
                        setOptions (fetchedOptions);
                        setIsLoading (false);
                    })
                    .catch(function(err) {
                        console.error ('Error loading options:', err);
                        setError ('Failed to load options. Please reload the editor.');
                        setIsLoading (false);
                    });
            }, []);
            
            // Find the label for the selected option
            var getSelectedLabel = function () {
                if (!selectedOption) return __('No option selected');
                
                var selectedObj = options.find (function (opt) {
                    return opt.value === selectedOption;
                });
                
                return selectedObj ? selectedObj.label : selectedOption;
            };
            
            // Create inspector controls for the sidebar
            var inspectorControls = el (
                InspectorControls,
                null,
                el (
                  PanelBody,
                  {
                      title: __('Block Settings')
                  },
                  isLoading ? 
                    el (
                        'div',
                        {
                            className: 'dynamic-select-loading'
                        },
                        el (Spinner, null),
                        el ('p', null, 'Loading options...')
                    ) : 
                    error ? 
                        el (
                            'div',
                            {
                                className: 'dynamic-select-error'
                            },
                            el ('p', null, error)
                        ) :
                        el(
                            SelectControl,
                            {
                                label: 'Choose an option',
                                value: selectedOption,
                                options: options,
                                onChange: function (value) {
                                    setAttributes ({
                                        selectedOption: value
                                    });
                                }
                            }
                        )
                )
            );
            
            // Create the block preview
            var blockPreview = el(
                'div',
                { className: 'dynamic-select-block' + (!selectedOption ? ' is-empty' : '') },
                isLoading ?
                    el(
                        Placeholder,
                        null,
                        el(Spinner, null),
                        el('p', null, 'Loading dynamic options...')
                    ) :
                error ?
                    el(
                        Placeholder,
                        null,
                        el('p', { className: 'dynamic-select-error' }, error)
                    ) :
                    el(
                        'div',
                        { className: 'dynamic-select-preview' },
                        el(
                            'div',
                            { className: 'dynamic-select-label' },
                            'FAQ section selected:'
                        ),
                        el(
                            'div',
                            { className: 'dynamic-select-value' },
                            getSelectedLabel ()
                        ),
                        el(
                            'div',
                            { className: 'dynamic-select-help' },
                            '(Choose section in the block settings panel)'
                        )
                    )
            );
            
            // Return the combined elements
            return el(
                wp.element.Fragment,
                null,
                inspectorControls,
                blockPreview
            );
        },
        
        // We're using a PHP callback for rendering, so this is just a placeholder
        save: function() {
            return null;
        }
    });
})(window.wp);