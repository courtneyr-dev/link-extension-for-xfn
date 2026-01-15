/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import {
	PanelBody,
	CheckboxControl,
	RadioControl,
	TextControl,
	ToggleControl,
	Popover,
	Button,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';
import {
	InspectorControls,
	RichTextToolbarButton,
	BlockControls,
} from '@wordpress/block-editor';
import { createHigherOrderComponent } from '@wordpress/compose';
import { useState, useEffect } from '@wordpress/element';
import { useSelect, dispatch } from '@wordpress/data';
import { link, linkOff, chevronDown, chevronUp } from '@wordpress/icons';
import { registerFormatType, applyFormat, removeFormat, getActiveFormat } from '@wordpress/rich-text';

/**
 * Internal dependencies
 */
import './editor.scss';

/**
 * Register XFN format extension for links
 * This extends the core link format to support rel attributes
 */
registerFormatType( 'xfn-link-extension/xfn-rel', {
	title: __( 'XFN Relationship', 'link-extension-for-xfn' ),
	tagName: 'a',
	className: null,
	attributes: {
		rel: 'rel',
		href: 'href',
	},
	edit() {
		// Return null - we'll handle the UI through filters
		return null;
	},
} );

console.log( '[XFN] XFN format type registered' );

/**
 * XFN Relationship definitions
 */
const XFN_RELATIONSHIPS = {
	friendship: {
		type: 'radio',
		label: __( 'Friendship', 'link-extension-for-xfn' ),
		description: __(
			'Your friendship level (choose one)',
			'link-extension-for-xfn'
		),
		options: [
			{
				label: __( 'Contact', 'link-extension-for-xfn' ),
				value: 'contact',
			},
			{
				label: __( 'Acquaintance', 'link-extension-for-xfn' ),
				value: 'acquaintance',
			},
			{
				label: __( 'Friend', 'link-extension-for-xfn' ),
				value: 'friend',
			},
		],
	},
	physical: {
		type: 'checkbox',
		label: __( 'Physical', 'link-extension-for-xfn' ),
		description: __(
			'Have you met this person?',
			'link-extension-for-xfn'
		),
		options: [
			{
				label: __( 'Met', 'link-extension-for-xfn' ),
				value: 'met',
			},
		],
	},
	professional: {
		type: 'checkbox',
		label: __( 'Professional', 'link-extension-for-xfn' ),
		description: __(
			'Professional relationships',
			'link-extension-for-xfn'
		),
		options: [
			{
				label: __( 'Co-worker', 'link-extension-for-xfn' ),
				value: 'co-worker',
			},
			{
				label: __( 'Colleague', 'link-extension-for-xfn' ),
				value: 'colleague',
			},
		],
	},
	geographical: {
		type: 'radio',
		label: __( 'Geographical', 'link-extension-for-xfn' ),
		description: __(
			'Geographical relationship',
			'link-extension-for-xfn'
		),
		options: [
			{
				label: __( 'Co-resident', 'link-extension-for-xfn' ),
				value: 'co-resident',
			},
			{
				label: __( 'Neighbor', 'link-extension-for-xfn' ),
				value: 'neighbor',
			},
		],
	},
	family: {
		type: 'radio',
		label: __( 'Family', 'link-extension-for-xfn' ),
		description: __(
			'Family relationship',
			'link-extension-for-xfn'
		),
		options: [
			{
				label: __( 'Child', 'link-extension-for-xfn' ),
				value: 'child',
			},
			{
				label: __( 'Parent', 'link-extension-for-xfn' ),
				value: 'parent',
			},
			{
				label: __( 'Sibling', 'link-extension-for-xfn' ),
				value: 'sibling',
			},
			{
				label: __( 'Spouse', 'link-extension-for-xfn' ),
				value: 'spouse',
			},
			{
				label: __( 'Kin', 'link-extension-for-xfn' ),
				value: 'kin',
			},
		],
	},
	romantic: {
		type: 'checkbox',
		label: __( 'Romantic', 'link-extension-for-xfn' ),
		description: __(
			'Romantic relationships',
			'link-extension-for-xfn'
		),
		options: [
			{
				label: __( 'Muse', 'link-extension-for-xfn' ),
				value: 'muse',
			},
			{
				label: __( 'Crush', 'link-extension-for-xfn' ),
				value: 'crush',
			},
			{
				label: __( 'Date', 'link-extension-for-xfn' ),
				value: 'date',
			},
			{
				label: __( 'Sweetheart', 'link-extension-for-xfn' ),
				value: 'sweetheart',
			},
		],
	},
	identity: {
		type: 'checkbox',
		label: __( 'Identity', 'link-extension-for-xfn' ),
		description: __(
			'Is this your own content?',
			'link-extension-for-xfn'
		),
		options: [
			{
				label: __( 'Me', 'link-extension-for-xfn' ),
				value: 'me',
			},
		],
	},
};

/**
 * Parse rel attribute to separate XFN and non-XFN values
 */
function parseRelAttribute( relString ) {
	if ( ! relString ) {
		return { xfn: [], other: [] };
	}

	const xfnValues = [
		'contact',
		'acquaintance',
		'friend',
		'met',
		'co-worker',
		'colleague',
		'co-resident',
		'neighbor',
		'child',
		'parent',
		'sibling',
		'spouse',
		'kin',
		'muse',
		'crush',
		'date',
		'sweetheart',
		'me',
	];

	const relParts = relString.split( /\s+/ ).filter( Boolean );
	const xfn = [];
	const other = [];

	relParts.forEach( ( part ) => {
		if ( xfnValues.includes( part ) ) {
			xfn.push( part );
		} else {
			other.push( part );
		}
	} );

	return { xfn, other };
}

/**
 * Combine XFN and other rel values
 */
function combineRelValues( xfnValues, otherValues ) {
	const allValues = [ ...otherValues, ...xfnValues ].filter( Boolean );
	return [ ...new Set( allValues ) ].join( ' ' );
}

/**
 * Get rel attribute from various block types
 */
function getRelFromBlock( attributes, blockName ) {
	if ( attributes.rel ) {
		return attributes.rel;
	}

	if ( attributes.metadata?.rel ) {
		return attributes.metadata.rel;
	}

	return '';
}

/**
 * Set rel attribute for various block types
 */
function setRelForBlock( attributes, setAttributes, newRel, blockName ) {
	if ( attributes.hasOwnProperty( 'rel' ) ) {
		setAttributes( { rel: newRel || undefined } );
		return;
	}

	const metadata = attributes.metadata || {};
	setAttributes( {
		metadata: {
			...metadata,
			rel: newRel || undefined,
		},
	} );
}

/**
 * XFN Collapsible Section Component
 */
const XFNCollapsibleSection = ( {
	currentRel,
	onUpdateRel,
	isExpanded,
	onToggle,
} ) => {
	const { xfn: xfnValues, other: otherValues } =
		parseRelAttribute( currentRel );

	const updateRelationship = ( category, value, isChecked ) => {
		let newXfnValues = [ ...xfnValues ];

		if ( XFN_RELATIONSHIPS[ category ].type === 'radio' ) {
			const categoryValues = XFN_RELATIONSHIPS[ category ].options.map(
				( opt ) => opt.value
			);
			newXfnValues = newXfnValues.filter(
				( val ) => ! categoryValues.includes( val )
			);

			if ( isChecked ) {
				newXfnValues.push( value );
			}
		} else {
			if ( isChecked ) {
				if ( ! newXfnValues.includes( value ) ) {
					newXfnValues.push( value );
				}
			} else {
				newXfnValues = newXfnValues.filter( ( val ) => val !== value );
			}
		}

		const newRel = combineRelValues( newXfnValues, otherValues );
		onUpdateRel( newRel );
	};

	return (
		<div className="xfn-collapsible-section">
			<Button
				className="xfn-section-toggle"
				onClick={ onToggle }
				aria-expanded={ isExpanded }
				icon={ isExpanded ? chevronUp : chevronDown }
				iconPosition="right"
				variant="tertiary"
			>
				{ __( 'XFN', 'link-extension-for-xfn' ) }
				{ xfnValues.length > 0 && (
					<span className="xfn-count-badge">
						{ xfnValues.length }
					</span>
				) }
			</Button>

			{ isExpanded && (
				<div className="xfn-section-content">
					<p className="xfn-section-description">
						{ __(
							'Describe your relationship to this person or organization',
							'link-extension-for-xfn'
						) }
					</p>

					<div className="xfn-relationships">
						{ Object.entries( XFN_RELATIONSHIPS ).map(
							( [ categoryKey, category ] ) => {
								if ( category.type === 'radio' ) {
									const selectedValue =
										category.options.find( ( opt ) =>
											xfnValues.includes( opt.value )
										)?.value || '';

									return (
										<div
											key={ categoryKey }
											className="xfn-category"
										>
											<ToggleGroupControl
												label={ category.label }
												value={ selectedValue }
												isBlock
												onChange={ ( value ) => {
													updateRelationship(
														categoryKey,
														value || '',
														true
													);
												} }
											>
												<ToggleGroupControlOption
													key="none"
													value=""
													label={ __(
														'None',
														'link-extension-for-xfn'
													) }
												/>
												{ category.options.map(
													( option ) => (
														<ToggleGroupControlOption
															key={ option.value }
															value={ option.value }
															label={ option.label }
														/>
													)
												) }
											</ToggleGroupControl>
										</div>
									);
								} else {
									return (
										<div
											key={ categoryKey }
											className="xfn-category xfn-checkbox-group"
										>
											<h4>{ category.label }</h4>
											<div className="xfn-checkbox-controls">
												{ category.options.map(
													( option ) => (
														<CheckboxControl
															key={ option.value }
															label={ option.label }
															checked={ xfnValues.includes(
																option.value
															) }
															onChange={ ( isChecked ) =>
																updateRelationship(
																	categoryKey,
																	option.value,
																	isChecked
																)
															}
														/>
													)
												) }
											</div>
										</div>
									);
								}
							}
						) }
					</div>

					{ xfnValues.length > 0 && (
						<div className="xfn-summary">
							<h4>
								{ __(
									'Active Relationships:',
									'link-extension-for-xfn'
								) }
							</h4>
							<div className="xfn-pills">
								{ xfnValues.map( ( rel ) => (
									<span
										key={ rel }
										className={ `xfn-pill xfn-pill-${ rel }` }
									>
										{ rel }
									</span>
								) ) }
							</div>
						</div>
					) }
				</div>
			) }
		</div>
	);
};

/**
 * XFN Inspector Controls Component (for blocks that are entirely links)
 */
const XFNInspectorControls = ( { attributes, setAttributes, name } ) => {
	const currentRel = getRelFromBlock( attributes, name );
	const { xfn: xfnValues, other: otherValues } =
		parseRelAttribute( currentRel );

	const updateXFNValues = ( newXfnValues ) => {
		const newRel = combineRelValues( newXfnValues, otherValues );
		setRelForBlock( attributes, setAttributes, newRel, name );
	};

	const updateRelationship = ( category, value, isChecked ) => {
		let newXfnValues = [ ...xfnValues ];

		if ( XFN_RELATIONSHIPS[ category ].type === 'radio' ) {
			const categoryValues = XFN_RELATIONSHIPS[ category ].options.map(
				( opt ) => opt.value
			);
			newXfnValues = newXfnValues.filter(
				( val ) => ! categoryValues.includes( val )
			);

			if ( isChecked ) {
				newXfnValues.push( value );
			}
		} else {
			if ( isChecked ) {
				if ( ! newXfnValues.includes( value ) ) {
					newXfnValues.push( value );
				}
			} else {
				newXfnValues = newXfnValues.filter( ( val ) => val !== value );
			}
		}

		updateXFNValues( newXfnValues );
	};

	// Open by default for Button and other block-level links
	const blockLevelLinks = [
		'core/button',
		'core/image',
		'core/navigation-link',
		'core/site-logo',
		'core/post-title',
		'core/query-title',
		'core/embed',
	];
	// Also open by default for Post Kinds blocks with eventUrl
	const shouldBeOpenByDefault =
		blockLevelLinks.includes( name ) ||
		attributes.hasOwnProperty( 'eventUrl' );

	return (
		<InspectorControls>
			<PanelBody
				title={ __(
					'XFN Relationships',
					'link-extension-for-xfn'
				) }
				initialOpen={ shouldBeOpenByDefault }
				className="xfn-inspector-panel"
			>
				<p className="xfn-panel-description">
					{ __(
						'Describe your relationship to the people or organizations you link to using XFN (XHTML Friends Network) markup.',
						'link-extension-for-xfn'
					) }
				</p>

				{ Object.entries( XFN_RELATIONSHIPS ).map(
					( [ categoryKey, category ] ) => {
						if ( category.type === 'radio' ) {
							const selectedValue =
								category.options.find( ( opt ) =>
									xfnValues.includes( opt.value )
								)?.value || '';
							const radioOptions = [
								{
									label: __(
										'None',
										'link-extension-for-xfn'
									),
									value: '',
								},
								...category.options,
							];

							return (
								<div
									key={ categoryKey }
									className="xfn-category-section"
								>
									<RadioControl
										label={ category.label }
										help={ category.description }
										selected={ selectedValue }
										options={ radioOptions }
										onChange={ ( value ) => {
											updateRelationship(
												categoryKey,
												value,
												value !== ''
											);
										} }
									/>
								</div>
							);
						} else {
							return (
								<div
									key={ categoryKey }
									className="xfn-category-section"
								>
									<h4 className="xfn-category-title">
										{ category.label }
									</h4>
									<p className="xfn-category-help">
										{ category.description }
									</p>
									{ category.options.map( ( option ) => (
										<CheckboxControl
											key={ option.value }
											label={ option.label }
											checked={ xfnValues.includes(
												option.value
											) }
											onChange={ ( isChecked ) => {
												updateRelationship(
													categoryKey,
													option.value,
													isChecked
												);
											} }
										/>
									) ) }
								</div>
							);
						}
					}
				) }

				{ xfnValues.length > 0 && (
					<div className="xfn-selected-summary">
						<h4>
							{ __(
								'Selected Relationships:',
								'link-extension-for-xfn'
							) }
						</h4>
						<div className="xfn-pills">
							{ xfnValues.map( ( rel ) => (
								<span
									key={ rel }
									className={ `xfn-pill xfn-pill-${ rel }` }
								>
									{ rel }
								</span>
							) ) }
						</div>
					</div>
				) }
			</PanelBody>
		</InspectorControls>
	);
};

/**
 * Global state to track XFN editing mode
 */
let isXFNEditingActive = false;
let currentXFNValues = [];
let currentOtherValues = [];

/**
 * Store reference to link value onChange callback
 */
let currentLinkOnChange = null;
let currentLinkValue = null;

/**
 * Inject XFN controls into LinkControl Advanced panel
 */
function injectXFNControls() {
	// Wait for link controls to be available
	setTimeout( () => {
		const linkControls = document.querySelector(
			'.block-editor-link-control'
		);
		if ( ! linkControls ) {
			return;
		}

		// Look for the Advanced panel
		const advancedToggle = linkControls.querySelector(
			'.block-editor-link-control__tools .components-button'
		);
		if ( ! advancedToggle ) {
			return;
		}

		// Check if Advanced panel is expanded
		const isAdvancedOpen =
			advancedToggle.getAttribute( 'aria-expanded' ) === 'true';
		if ( ! isAdvancedOpen ) {
			return;
		}

		// Look for the settings panel container
		const settingsPanel = linkControls.querySelector(
			'.block-editor-link-control__settings'
		);
		if ( ! settingsPanel ) {
			return;
		}

		// Check if XFN controls are already injected
		if ( settingsPanel.querySelector( '.xfn-collapsible-section' ) ) {
			return;
		}

		// Try to get the current link value from React internals
		console.log( '[XFN] Attempting to find link value from React...' );
		const linkControlElement = linkControls;
		const reactKey = Object.keys( linkControlElement ).find( ( key ) =>
			key.startsWith( '__react' )
		);
		if ( reactKey ) {
			const reactInstance = linkControlElement[ reactKey ];
			console.log( '[XFN] React instance found:', reactInstance );

			// Try to traverse to find the link value
			let current = reactInstance;
			let depth = 0;
			while ( current && depth < 10 ) {
				if ( current.memoizedProps?.value ) {
					currentLinkValue = current.memoizedProps.value;
					currentLinkOnChange = current.memoizedProps.onChange;

					// Store globally so block-level links can access it
					window.currentLinkValue = currentLinkValue;
					window.currentLinkOnChange = currentLinkOnChange;

					console.log( '[XFN] Found link value:', currentLinkValue );
					console.log( '[XFN] Link value keys:', Object.keys( currentLinkValue ) );
					console.log( '[XFN] Link value rel:', currentLinkValue.rel );
					console.log( '[XFN] Found onChange:', currentLinkOnChange );
					break;
				}
				current = current.return || current._owner;
				depth++;
			}
		}

		// Get current rel value from the link control
		// Try multiple selectors to find the rel input field
		console.log( '[XFN] Looking for rel input in link control...' );
		const allInputs = settingsPanel.querySelectorAll( 'input' );
		console.log( '[XFN] All inputs in settings panel:', allInputs );

		// Log details about each input
		allInputs.forEach( ( input, index ) => {
			console.log( `[XFN] Input ${index}:`, {
				type: input.type,
				id: input.id,
				name: input.name,
				placeholder: input.placeholder,
				value: input.value,
				element: input,
			} );
		} );

		let relInput = linkControls.querySelector(
			'input[type="text"][placeholder*="rel" i]'
		);
		console.log( '[XFN] Try 1 (placeholder*=rel):', relInput );

		if ( ! relInput ) {
			relInput = linkControls.querySelector( 'input[id*="rel" i]' );
			console.log( '[XFN] Try 2 (id*=rel):', relInput );
		}
		if ( ! relInput ) {
			// Try to find any text-like input (text, search, url, etc.)
			const textLikeInputs = settingsPanel.querySelectorAll(
				'input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"])'
			);
			console.log(
				'[XFN] Try 3 - text-like inputs in settings:',
				textLikeInputs
			);
			// The rel field is usually after "Open in new tab" and "nofollow" checkboxes
			// So it's likely the last text-like input
			if ( textLikeInputs.length > 0 ) {
				relInput = textLikeInputs[ textLikeInputs.length - 1 ];
			}
		}

		console.log( '[XFN] Final rel input found:', !! relInput );
		if ( relInput ) {
			console.log( '[XFN] Rel input element:', relInput );
			console.log( '[XFN] Rel input current value:', relInput.value );
		}

		// Get current rel value
		let currentRel = '';

		// Check if this is a block-level link
		const selectedBlock = wp.data.select( 'core/block-editor' ).getSelectedBlock();
		const blockLevelLinks = [
			'core/button',
			'core/image',
			'core/navigation-link',
			'core/site-logo',
			'core/post-title',
			'core/query-title',
			'core/embed',
		];

		// Also treat blocks with eventUrl (Post Kinds for IndieWeb) as block-level links
		const isBlockLevelLink = selectedBlock && (
			blockLevelLinks.includes( selectedBlock.name ) ||
			selectedBlock.attributes?.hasOwnProperty( 'eventUrl' )
		);

		if ( isBlockLevelLink ) {
			// For block-level links, read directly from block attributes
			currentRel = getRelFromBlock( selectedBlock.attributes, selectedBlock.name );
			console.log( '[XFN] Got rel from block-level link attributes:', currentRel );
		} else if ( currentLinkValue?.url && selectedBlock?.attributes?.content ) {
			// For inline links (like in paragraphs), parse the content
			let content = selectedBlock.attributes.content;

			// Handle RichTextData
			if ( typeof content === 'object' && content.toHTMLString ) {
				content = content.toHTMLString();
			}

			// Parse content to find the link's rel
			if ( typeof content === 'string' && content.includes( currentLinkValue.url ) ) {
				const parser = new DOMParser();
				const doc = parser.parseFromString( content, 'text/html' );
				const link = Array.from( doc.querySelectorAll( 'a' ) ).find(
					( a ) => a.getAttribute( 'href' ) === currentLinkValue.url
				);
				if ( link ) {
					currentRel = link.getAttribute( 'rel' ) || '';
					console.log( '[XFN] Got rel from existing link in content:', currentRel );
				}
			}
		}

		// Fallback to linkValue or input
		if ( ! currentRel && currentLinkValue && currentLinkValue.rel ) {
			currentRel = currentLinkValue.rel;
			console.log( '[XFN] Got rel from link value:', currentRel );
		} else if ( ! currentRel && relInput ) {
			currentRel = relInput.value;
			console.log( '[XFN] Got rel from input:', currentRel );
		}

		const { xfn: xfnValues, other: otherValues } =
			parseRelAttribute( currentRel );

		currentXFNValues = [ ...xfnValues ];
		currentOtherValues = [ ...otherValues ];

		console.log( '[XFN] Initial XFN values:', currentXFNValues );
		console.log( '[XFN] Initial other values:', currentOtherValues );

		// Create XFN collapsible section
		const xfnContainer = document.createElement( 'div' );
		xfnContainer.className = 'xfn-collapsible-section';
		xfnContainer.innerHTML = createXFNCollapsibleHTML( currentXFNValues );

		// Insert XFN controls after existing controls
		settingsPanel.appendChild( xfnContainer );

		// Add event listeners
		addXFNEventListeners( xfnContainer, relInput );

		// Find and intercept the Apply button
		console.log( '[XFN] Looking for Apply button...' );
		const possibleButtons = linkControls.querySelectorAll( 'button' );
		console.log( '[XFN] All buttons in link control:', possibleButtons );

		const applyButton = linkControls.querySelector(
			'.block-editor-link-control__search-submit, button[type="submit"]'
		);

		if ( applyButton ) {
			console.log( '[XFN] Found Apply button (will save XFN on click)' );

			// Add a click listener that runs BEFORE the button's normal handler
			applyButton.addEventListener(
				'click',
				( e ) => {
					console.log( '[XFN] ===== Apply button clicked! =====' );
					console.log( '[XFN] Current XFN values:', currentXFNValues );
					console.log( '[XFN] Current link value:', currentLinkValue );

					// Store XFN values globally so they can be applied after the link is created
					if ( currentXFNValues.length > 0 ) {
						const newRel = combineRelValues(
							currentXFNValues,
							currentOtherValues
						);

						console.log( '[XFN] Storing XFN rel for post-link-creation:', newRel );

						// Store globally
						window.pendingXFNRel = newRel;
						window.pendingXFNUrl = currentLinkValue?.url;

						// Try multiple times with increasing delays to catch the link after creation
						console.log( '[XFN] Scheduling XFN application attempts...' );
						setTimeout( () => {
							console.log( '[XFN] Attempt 1 (100ms)...' );
							applyXFNToCreatedLink();
						}, 100 );
						setTimeout( () => {
							console.log( '[XFN] Attempt 2 (300ms)...' );
							applyXFNToCreatedLink();
						}, 300 );
						setTimeout( () => {
							console.log( '[XFN] Attempt 3 (500ms)...' );
							applyXFNToCreatedLink();
						}, 500 );
						setTimeout( () => {
							console.log( '[XFN] Attempt 4 (1000ms)...' );
							applyXFNToCreatedLink();
						}, 1000 );
					} else {
						console.log( '[XFN] No XFN values selected, skipping' );
					}

					// Auto-collapse Advanced panel after apply
					if (
						advancedToggle &&
						advancedToggle.getAttribute( 'aria-expanded' ) === 'true'
					) {
						setTimeout( () => advancedToggle.click(), 150 );
					}
				},
				true
			); // Use capture phase to run before other handlers

			console.log( '[XFN] Apply button interceptor attached' );
		} else {
			console.warn( '[XFN] Apply button not found!' );
		}
	}, 100 );
}

/**
 * Create HTML for XFN collapsible section
 */
function createXFNCollapsibleHTML( xfnValues ) {
	const countBadge =
		xfnValues.length > 0
			? `<span class="xfn-count-badge">${ xfnValues.length }</span>`
			: '';

	let html = `
		<button 
			class="xfn-section-toggle components-button is-tertiary" 
			aria-expanded="false" 
			type="button"
		>
			XFN ${ countBadge }
			<svg class="xfn-chevron" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
				<path d="M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"/>
			</svg>
		</button>
		<div class="xfn-section-content" style="display: none;">
			<p class="xfn-section-description">
				${ __(
					'Describe your relationship to this person or organization',
					'link-extension-for-xfn'
				) }
			</p>
	`;

	Object.entries( XFN_RELATIONSHIPS ).forEach(
		( [ categoryKey, category ] ) => {
			html += `<div class="xfn-category" data-category="${ categoryKey }">`;
			html += `<h4>${ category.label }</h4>`;

			if ( category.type === 'radio' ) {
				const selectedValue =
					category.options.find( ( opt ) =>
						xfnValues.includes( opt.value )
					)?.value || '';

				html += `<div class="xfn-button-group">`;
				html += `<button class="components-button is-compact ${
					selectedValue === '' ? 'is-pressed' : ''
				}" data-value="">${ __(
					'None',
					'link-extension-for-xfn'
				) }</button>`;

				category.options.forEach( ( option ) => {
					html += `<button class="components-button is-compact ${
						selectedValue === option.value ? 'is-pressed' : ''
					}" data-value="${ option.value }">${
						option.label
					}</button>`;
				} );
				html += `</div>`;
			} else {
				html += `<div class="xfn-button-group">`;
				category.options.forEach( ( option ) => {
					const isPressed = xfnValues.includes( option.value );
					html += `<button class="components-button is-compact ${
						isPressed ? 'is-pressed' : ''
					}" data-value="${ option.value }">${
						option.label
					}</button>`;
				} );
				html += `</div>`;
			}

			html += `</div>`;
		}
	);

	// Add summary if there are selected relationships
	if ( xfnValues.length > 0 ) {
		html += `<div class="xfn-summary">`;
		html += `<h4>${ __(
			'Active Relationships:',
			'link-extension-for-xfn'
		) }</h4>`;
		html += `<div class="xfn-pills">`;
		xfnValues.forEach( ( rel ) => {
			html += `<span class="xfn-pill xfn-pill-${ rel }">${ rel }</span> `;
		} );
		html += `</div></div>`;
	}

	html += `</div>`;

	return html;
}

/**
 * Add event listeners to XFN collapsible section
 */
function addXFNEventListeners( container, relInput ) {
	const toggle = container.querySelector( '.xfn-section-toggle' );
	const content = container.querySelector( '.xfn-section-content' );
	const chevron = toggle.querySelector( '.xfn-chevron' );
	const linkControlRoot =
		container.closest( '.block-editor-link-control' ) || document;

	const updateRelAttribute = () => {
		const newRel = combineRelValues( currentXFNValues, currentOtherValues );
		console.log( '[XFN] Updating XFN values:', {
			xfnValues: currentXFNValues,
			otherValues: currentOtherValues,
			newRel,
		} );

		// Update the rel input field directly if it exists
		if ( relInput ) {
			relInput.value = newRel;
			console.log( '[XFN] Updated rel input field to:', newRel );

			// Trigger input event so React picks up the change
			const inputEvent = new Event( 'input', { bubbles: true } );
			relInput.dispatchEvent( inputEvent );

			const changeEvent = new Event( 'change', { bubbles: true } );
			relInput.dispatchEvent( changeEvent );
		}

		// Also store globally as a backup
		window.pendingXFNRel = newRel;
		window.pendingXFNUrl = currentLinkValue?.url;

		console.log( '[XFN] Stored pending XFN rel:', window.pendingXFNRel );
		console.log( '[XFN] Stored pending XFN url:', window.pendingXFNUrl );

		// Don't call onChange during selection - only store values
		// Calling onChange triggers WordPress to close the Advanced panel
		// We'll apply the values when user clicks Apply button instead
		currentLinkValue = {
			...( currentLinkValue || {} ),
			rel: newRel || '',
		};
		console.log( '[XFN] Stored rel value (will apply on Submit):', currentLinkValue );

		// Visually enable Apply button when XFN has changed
		const applyButton = linkControlRoot.querySelector(
			'.block-editor-link-control__search-submit, button[type="submit"]'
		);

		if ( applyButton ) {
			applyButton.removeAttribute( 'aria-disabled' );
			applyButton.removeAttribute( 'disabled' );
			applyButton.classList.add( 'xfn-apply-ready' );
		}

		// Update the visual summary and count badge
		updateXFNSummary( container );
		updateCountBadge( toggle );
	};

	// Toggle functionality
	toggle.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		e.stopPropagation();
		const isExpanded = toggle.getAttribute( 'aria-expanded' ) === 'true';
		const newState = ! isExpanded;

		toggle.setAttribute( 'aria-expanded', newState );
		content.style.display = newState ? 'block' : 'none';
		chevron.style.transform = newState ? 'rotate(180deg)' : 'rotate(0deg)';
	} );

	// Handle button clicks
	container
		.querySelectorAll( '.xfn-button-group .components-button' )
		.forEach( ( button ) => {
			button.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				e.stopPropagation(); // Prevent event bubbling that might collapse the section

				const category =
					e.target.closest( '.xfn-category' ).dataset.category;
				const value = e.target.dataset.value;
				const buttonGroup = e.target.closest( '.xfn-button-group' );

				if ( XFN_RELATIONSHIPS[ category ].type === 'radio' ) {
					// Remove all values from this category
					const categoryValues = XFN_RELATIONSHIPS[
						category
					].options.map( ( opt ) => opt.value );
					currentXFNValues = currentXFNValues.filter(
						( val ) => ! categoryValues.includes( val )
					);

					// Add new value if not empty
					if ( value ) {
						currentXFNValues.push( value );
					}

					// Update button states in group
					buttonGroup
						.querySelectorAll( '.components-button' )
						.forEach( ( btn ) => {
							btn.classList.remove( 'is-pressed' );
						} );
					e.target.classList.add( 'is-pressed' );
				} else {
					// Toggle checkbox behavior
					const isPressed =
						e.target.classList.contains( 'is-pressed' );

					if ( isPressed ) {
						currentXFNValues = currentXFNValues.filter(
							( val ) => val !== value
						);
						e.target.classList.remove( 'is-pressed' );
					} else {
						if ( ! currentXFNValues.includes( value ) ) {
							currentXFNValues.push( value );
						}
						e.target.classList.add( 'is-pressed' );
					}
				}

				updateRelAttribute();
			} );
		} );
}

/**
 * Update XFN visual summary
 */
function updateXFNSummary( container ) {
	let summaryContainer = container.querySelector( '.xfn-summary' );

	if ( currentXFNValues.length === 0 ) {
		if ( summaryContainer ) {
			summaryContainer.remove();
		}
		return;
	}

	if ( ! summaryContainer ) {
		summaryContainer = document.createElement( 'div' );
		summaryContainer.className = 'xfn-summary';
		container
			.querySelector( '.xfn-section-content' )
			.appendChild( summaryContainer );
	}

	let html = `<h4>${ __(
		'Active Relationships:',
		'link-extension-for-xfn'
	) }</h4>`;
	html += `<div class="xfn-pills">`;
	currentXFNValues.forEach( ( rel ) => {
		html += `<span class="xfn-pill xfn-pill-${ rel }">${ rel }</span> `;
	} );
	html += `</div>`;

	summaryContainer.innerHTML = html;
}

/**
 * Update count badge in toggle button
 */
function updateCountBadge( toggle ) {
	let badge = toggle.querySelector( '.xfn-count-badge' );

	if ( currentXFNValues.length === 0 ) {
		if ( badge ) {
			badge.remove();
		}
		return;
	}

	if ( ! badge ) {
		badge = document.createElement( 'span' );
		badge.className = 'xfn-count-badge';
		toggle.insertBefore( badge, toggle.querySelector( '.xfn-chevron' ) );
	}

	badge.textContent = currentXFNValues.length;
}

/**
 * Apply XFN rel attribute to a newly created link using WordPress APIs
 */
function applyXFNToCreatedLink() {
	console.log( '[XFN] ===== Applying XFN to created link =====' );
	console.log( '[XFN] Pending XFN rel:', window.pendingXFNRel );
	console.log( '[XFN] Pending XFN url:', window.pendingXFNUrl );

	if ( ! window.pendingXFNRel || ! window.pendingXFNUrl ) {
		console.log( '[XFN] No pending XFN data to apply' );
		return false;
	}

	// Find the selected block
	const selectedBlock = wp.data.select( 'core/block-editor' ).getSelectedBlock();
	if ( ! selectedBlock ) {
		console.log( '[XFN] No selected block found' );
		return false;
	}

	console.log( '[XFN] Selected block:', selectedBlock.name );
	console.log( '[XFN] Selected block clientId:', selectedBlock.clientId );
	console.log( '[XFN] Selected block attributes:', selectedBlock.attributes );
	console.log( '[XFN] Block innerBlocks:', selectedBlock.innerBlocks );

	// Check if this is a block-level link (like Button, Image, etc.)
	const blockLevelLinks = [
		'core/button',
		'core/image',
		'core/navigation-link',
		'core/site-logo',
		'core/post-title',
		'core/query-title',
		'core/embed',
	];

	// Also treat blocks with eventUrl (Post Kinds for IndieWeb) as block-level links
	const isBlockLevelLink =
		blockLevelLinks.includes( selectedBlock.name ) ||
		selectedBlock.attributes?.hasOwnProperty( 'eventUrl' );

	if ( isBlockLevelLink ) {
		console.log( '[XFN] This is a block-level link, updating rel attribute directly...' );

		// Get existing rel value
		const existingRel = getRelFromBlock( selectedBlock.attributes, selectedBlock.name );
		console.log( '[XFN] Existing rel:', existingRel );

		// Parse and combine with pending XFN values
		const { other: existingOther } = parseRelAttribute( existingRel );
		console.log( '[XFN] Existing other values:', existingOther );

		const { xfn: pendingXFN } = parseRelAttribute( window.pendingXFNRel );
		console.log( '[XFN] Pending XFN values:', pendingXFN );

		const newRel = combineRelValues( pendingXFN, existingOther );
		console.log( '[XFN] New combined rel:', newRel );

		// Update the block using setRelForBlock pattern
		if ( selectedBlock.attributes.hasOwnProperty( 'rel' ) ) {
			wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes(
				selectedBlock.clientId,
				{ rel: newRel || undefined }
			);
			console.log( '[XFN] ✓ Updated rel attribute on block' );
		} else {
			const metadata = selectedBlock.attributes.metadata || {};
			wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes(
				selectedBlock.clientId,
				{
					metadata: {
						...metadata,
						rel: newRel || undefined,
					},
				}
			);
			console.log( '[XFN] ✓ Updated metadata.rel attribute on block' );
		}

		// Also try to update via onChange if available
		if ( window.currentLinkOnChange && typeof window.currentLinkOnChange === 'function' ) {
			try {
				const updatedValue = {
					...( window.currentLinkValue || {} ),
					rel: newRel || '',
				};
				window.currentLinkOnChange( updatedValue );
				console.log( '[XFN] ✓ Also called onChange with updated rel' );
			} catch ( error ) {
				console.warn( '[XFN] Could not call onChange:', error );
			}
		}

		// Clear pending XFN data
		window.pendingXFNRel = null;
		window.pendingXFNUrl = null;

		console.log( '[XFN] ✓✓✓ Block-level link updated successfully! ✓✓✓' );
		return true;
	}

	// Get the block's content (for RichText blocks like paragraph)
	let blockContent = selectedBlock.attributes.content;
	console.log( '[XFN] Block content:', blockContent );
	console.log( '[XFN] Block content type:', typeof blockContent );

	// WordPress RichText can be either a string or RichTextData object
	// If it's a RichTextData object, convert it to HTML string
	if ( blockContent && typeof blockContent === 'object' && blockContent.toHTMLString ) {
		console.log( '[XFN] Converting RichTextData to HTML string...' );
		blockContent = blockContent.toHTMLString();
		console.log( '[XFN] Converted content:', blockContent );
	}

	// Check if content exists and is now a string
	if ( ! blockContent || typeof blockContent !== 'string' ) {
		console.warn( '[XFN] Block has no string content or unable to convert to string' );
		return false;
	}

	// Check if URL is in the content
	const urlInContent = blockContent.includes( window.pendingXFNUrl );
	console.log( '[XFN] URL in content?', urlInContent );

	if ( ! urlInContent ) {
		console.warn( '[XFN] Pending URL not found in block content' );
		console.log( '[XFN] Looking for:', window.pendingXFNUrl );
		console.log( '[XFN] In content:', blockContent );
		return false;
	}

	console.log( '[XFN] Found URL in block content, attempting to update using proper WordPress approach...' );

	// Use a more reliable approach: inject via LinkControl value directly
	// This ensures WordPress core link system properly handles the rel attribute
	try {
		// Parse existing rel and combine with XFN values
		const { xfn: pendingXFN, other: pendingOther } = parseRelAttribute( window.pendingXFNRel );

		// Try to find and update link using the block editor's internal methods
		// This is more reliable than DOM manipulation
		const parser = new DOMParser();
		const doc = parser.parseFromString( blockContent, 'text/html' );
		const links = doc.querySelectorAll( 'a' );

		let linkUpdated = false;
		links.forEach( ( link ) => {
			if ( link.getAttribute( 'href' ) === window.pendingXFNUrl ) {
				const existingRel = link.getAttribute( 'rel' ) || '';
				const { other: existingOther } = parseRelAttribute( existingRel );
				const newRel = combineRelValues( pendingXFN, existingOther.length ? existingOther : pendingOther );

				console.log( '[XFN] Setting rel attribute:', newRel );
				link.setAttribute( 'rel', newRel );
				linkUpdated = true;
			}
		} );

		if ( linkUpdated ) {
			const newContent = doc.body.innerHTML;
			console.log( '[XFN] Updating block with new content...' );

			// Use updateBlockAttributes to set the content
			wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes(
				selectedBlock.clientId,
				{ content: newContent }
			);

			console.log( '[XFN] ✓ Block content updated' );

			// Clear pending data
			window.pendingXFNRel = null;
			window.pendingXFNUrl = null;

			// Force a re-render to ensure the change persists
			setTimeout( () => {
				const currentBlock = wp.data.select( 'core/block-editor' ).getBlock( selectedBlock.clientId );
				console.log( '[XFN] Verification - Current content:', currentBlock?.attributes?.content );
			}, 100 );

			return true;
		}
	} catch ( error ) {
		console.error( '[XFN] Error updating link:', error );
	}

	console.warn( '[XFN] Could not update link' );
	return false;
}

/**
 * Monitor for link control changes and inject XFN controls
 */
function startXFNMonitoring() {
	const observer = new MutationObserver( ( mutations ) => {
		mutations.forEach( ( mutation ) => {
			if ( mutation.type === 'childList' ) {
				const addedNodes = Array.from( mutation.addedNodes );

				// Check if link control was added
				addedNodes.forEach( ( node ) => {
					if ( node.nodeType === Node.ELEMENT_NODE ) {
						if (
							node.classList?.contains(
								'block-editor-link-control'
							) ||
							node.querySelector?.( '.block-editor-link-control' )
						) {
							injectXFNControls();
						}

						// Check for advanced settings expansion
						if (
							node.classList?.contains(
								'block-editor-link-control__settings'
							) ||
							node.querySelector?.(
								'.block-editor-link-control__settings'
							)
						) {
							injectXFNControls();
						}
					}
				} );
			}

			// Also check for attribute changes that might indicate advanced panel expansion
			if (
				mutation.type === 'attributes' &&
				mutation.attributeName === 'aria-expanded'
			) {
				injectXFNControls();
			}
		} );
	} );

	observer.observe( document.body, {
		childList: true,
		subtree: true,
		attributes: true,
		attributeFilter: [ 'aria-expanded' ],
	} );

	// Also monitor clicks on advanced toggle buttons
	document.addEventListener( 'click', ( e ) => {
		if (
			e.target.closest(
				'.block-editor-link-control__tools .components-button'
			)
		) {
			setTimeout( injectXFNControls, 50 );
		}
	} );
}

/**
 * Filter to add XFN controls to blocks that are entirely links
 */
const withXFNControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const { attributes, name } = props;

		// Get settings from localized data
		const settings = window.linkexfoData?.settings || {
			enable_inspector_controls: false,
			enable_floating_toolbar: false,
		};

		// List of blocks that support links as the entire block
		const supportedBlocks = [
			'core/button',
			'core/image',
			'core/navigation-link',
			'core/site-logo',
			'core/post-title',
			'core/query-title',
			'core/embed',
		];

		// Check if this block type should have XFN controls in inspector
		// Supports core blocks plus Post Kinds for IndieWeb blocks (eventUrl for RSVP cards)
		const shouldShowXFN =
			supportedBlocks.includes( name ) ||
			attributes.hasOwnProperty( 'url' ) ||
			attributes.hasOwnProperty( 'href' ) ||
			attributes.hasOwnProperty( 'linkDestination' ) ||
			attributes.hasOwnProperty( 'eventUrl' );

		// Only show inspector controls if setting is enabled
		if ( ! shouldShowXFN || ! settings.enable_inspector_controls ) {
			return <BlockEdit { ...props } />;
		}

		const currentRel = getRelFromBlock( attributes, name );

		const handleXFNUpdate = ( newRel ) => {
			setRelForBlock( attributes, props.setAttributes, newRel, name );
		};

		return (
			<>
				<BlockEdit { ...props } />
				<XFNInspectorControls { ...props } />
			</>
		);
	};
}, 'withXFNControls' );

// Apply the filter for block-level links only if inspector controls are enabled
if ( window.linkexfoData?.settings?.enable_inspector_controls ) {
	addFilter(
		'editor.BlockEdit',
		'xfn-link-extension/with-xfn-controls',
		withXFNControls
	);
	console.log( '[XFN] Inspector Controls enabled' );
}

/**
 * Filter to add rel attribute to links when blocks are saved
 * This handles inline links in RichText (paragraphs, etc.)
 */
addFilter(
	'blocks.getSaveContent.extraProps',
	'xfn-link-extension/add-rel-to-links',
	( props, blockType, attributes ) => {
		console.log( '[XFN] getSaveContent filter called for:', blockType.name );

		// This filter runs when blocks are being saved
		// We need to inject XFN values that were stored globally
		if ( currentXFNValues.length > 0 ) {
			console.log( '[XFN] Injecting XFN values into saved content:', currentXFNValues );
		}

		return props;
	}
);

// Start monitoring for inline links
if ( typeof document !== 'undefined' ) {
	// Wait for editor to be ready
	console.log( '[XFN] Starting XFN monitoring in 1 second...' );
	setTimeout( () => {
		console.log( '[XFN] XFN monitoring started!' );
		startXFNMonitoring();
	}, 1000 );
}

console.log(
	'%c[XFN] Link Extension loaded successfully!',
	'color: #00a32a; font-weight: bold; font-size: 14px;'
);
console.log( '[XFN] Controls will appear in:' );

const settings = window.linkexfoData?.settings || {
	enable_inspector_controls: false,
	enable_floating_toolbar: false,
};

if ( settings.enable_inspector_controls ) {
	console.log( '[XFN] ✓ Inspector Controls for link blocks (ENABLED)' );
} else {
	console.log( '[XFN] ✗ Inspector Controls for link blocks (DISABLED - enable in Settings > Link Extension for XFN)' );
}

if ( settings.enable_floating_toolbar ) {
	console.log( '[XFN] ✓ Floating toolbar for link blocks (ENABLED)' );
} else {
	console.log( '[XFN] ✗ Floating toolbar for link blocks (DISABLED - enable in Settings > Link Extension for XFN)' );
}

console.log( '[XFN] ✓ Collapsible XFN section in Link Advanced Panel (ALWAYS ENABLED)' );
