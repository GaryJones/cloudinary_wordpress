/* global */
import TermsInspector from './components/terms-inspector';

const ClassicEditor = {
	...TermsInspector,
	selection: {},
	tagDelimiter: wp.i18n._x( ',', 'tag delimiter' ) || ',',
	getTaxonomies() {
		return cldData.taxonomies;
	},
	addTaxonomyListener( taxonomy ) {
		const inputs = document.querySelectorAll( `#taxonomy-${ taxonomy.slug } .selectit input[type="checkbox"],textarea[name=tax_input\\[${ taxonomy.slug }\\]]` );

		this.available[ taxonomy.slug ] = Object.values( taxonomy.terms );
		this.selection[ taxonomy.slug ] = [];
		inputs.forEach( input => {
			let value = parseInt( input.value );
			if ( Object.is( value, NaN ) ) {
				value = input.value;
			}
			if ( 'checkbox' === input.type ) {
				if ( input.checked && -1 === this.selection[ taxonomy.slug ].indexOf( value ) ) {
					this.selection[ taxonomy.slug ].push( value );
				}
			} else if ( 'textarea' === input.type ) {
				const keys = value.split( this.tagDelimiter );
				keys.forEach( ( tag ) => {
					const tagValue = tag.trim();
					if ( -1 === this.selection[ taxonomy.slug ].indexOf( tagValue ) ) {
						this.selection[ taxonomy.slug ].push( tagValue );
					}
				} );
			}
			input.addEventListener( 'change', () => {
				const index = this.selection[ taxonomy.slug ].indexOf( value );
				if ( input.checked ) {
					this.selection[ taxonomy.slug ].push( value );
				} else if ( -1 < index ) {
					this.selection[ taxonomy.slug ].splice( index, 1 );
				}

				this.event( taxonomy );
			} );
		} );
		this.event( taxonomy );
	},
	getSelection( taxonomy ) {
		return this.selection[ taxonomy.slug ];
	}
};
if ( typeof window.CLDN !== 'undefined' ) {
	ClassicEditor._init();
}

export default ClassicEditor;
