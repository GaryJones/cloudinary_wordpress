/* global window wp cloudinaryApi */

const Deactivate = {
	// The link that triggers the ThickBox
	tbLink: document.getElementById( 'cld-deactivation-link' ),
	// The different links to deactivate the plugin.
	pluginListLinks: document.querySelectorAll( '.cld-deactivate-link, .cld-deactivate' ),
	// The reasons.
	options: document.querySelectorAll( '.cloudinary-deactivation input[type="radio"]' ),
	// The feedback submit button.
	submitButton: document.querySelector( '.cloudinary-deactivation .button-primary' ),
	// The skip button.
	skipButton: document.querySelector( '.cloudinary-deactivation .button-link' ),
	// Selected reason.
	reason: '',
	// The more details .
	more: null,
	// The deactivation link for the plugin.
	deactivationUrl: '',

	addEvents: function() {
		const context = this;

		// Add event listener to deactivation links to add the pop up.
		[ ...context.pluginListLinks ].forEach( ( link ) => {
			link.addEventListener( 'click', function( ev ) {
				ev.preventDefault();
				context.deactivationUrl = ev.target.getAttribute( 'href' );
				context.tbLink.click();
				document.getElementById( 'TB_window' ).setAttribute(
					'style',
					'bottom: 0;' +
					'height: 450px;' +
					'left: 0;' +
					'margin: auto;' +
					'right: 0;' +
					'top: 0;' +
					'visibility: visible;' +
					'width: 550px;'
				);
			});
		});

		// Add event listener to skip feedback.
		context.skipButton.addEventListener( 'click', function() {
			window.location.href = context.deactivationUrl;
		});

		// Add event listener to update reason and more container.
		[ ...context.options ].forEach( ( option ) => {
			option.addEventListener( 'change', function( ev ) {
				context.submitButton.removeAttribute( 'disabled' );
				context.reason = ev.target.value;
				context.more = ev.target.parentNode.querySelector( 'textarea' );
			});
		});

		// Add event listener to submit the feedback.
		context.submitButton.addEventListener( 'click', function() {
			wp.ajax.send( {
				url: cloudinaryApi.endpoint,
				data: {
					reason: context.reason,
					more: context.more?.value,
				},
				beforeSend: function( request ) {
					request.setRequestHeader( 'X-WP-Nonce', cloudinaryApi.nonce );
				},
			} ).always( function() {
				window.location.href = context.deactivationUrl;
			} );
		})
	},

	/**
	 * Init method.
	 */
	init: function() {
		this.addEvents();
	}
}

Deactivate.init();

export default Deactivate;
