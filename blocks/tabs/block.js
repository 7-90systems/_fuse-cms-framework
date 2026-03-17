(function (blocks, element, blockEditor, components, i18n) {
	const {registerBlockType} = blocks;
	const {createElement: el} = element;
	const {RichText, InnerBlocks, InspectorControls} = blockEditor;
	const {TextControl, PanelBody} = components;

	const ALLOWED_BLOCKS = ['core/paragraph', 'core/image', 'core/heading'];
    
	registerBlockType('fuse/tab', {
        title: 'Tab',
        parent: ['fuse/tabs'],
        icon: 'excerpt-view',
        category: 'layout',
        attributes: {
             label: {
				type: 'string',
				default: 'Tab Label'
			 }
        },
        supports: {
			html: false
		},
        edit: function({ attributes, setAttributes }) {
            return el('div', { className: 'fuse-tab-block' }, [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Tab Label' },
                        el(TextControl, {
                            label: 'Label',
                            value: attributes.label,
                            onChange: (val) => setAttributes({ label: val })
                        })
                    )
                ),
                el('h4', {}, attributes.label),
                    el(InnerBlocks)
                ]);
            },
            save: function({ attributes }) {
                return el('div', {
                     className: 'fuse-tab',
                    'data-label': attributes.label
                }, el(InnerBlocks.Content));
            }
        });

            
            registerBlockType('fuse/tabs', {
                title: 'Tabs Container',
                icon: 'index-card',
                category: 'layout',
                supports: { html: false },
                edit: function() {
                    return el('div', { className: 'fuse-tabs-container' },
                        el(InnerBlocks, {
                            allowedBlocks: ['fuse/tab'],
                            orientation: 'horizontal',
                        })
                    );
                },
                save: function() {
                    return el('div', { className: 'fuse-tabs-container' },
                        el(InnerBlocks.Content)
                    );
                }
            });
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n
);