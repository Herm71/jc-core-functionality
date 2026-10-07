/**
 * Plugin-wide code rules that are easy to regress file by file.
 *
 * Run with `npm run test:unit`.
 */

const { test } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const path = require( 'node:path' );

const root = path.join( __dirname, '..' );
const read = ( file ) => fs.readFileSync( path.join( root, file ), 'utf8' );

const featureFiles = fs
	.readdirSync( path.join( root, 'lib/functions' ) )
	.filter( ( f ) => f.endsWith( '.php' ) )
	.map( ( f ) => `lib/functions/${ f }` );
const phpFiles = [ 'plugin.php', ...featureFiles ];
const pkg = JSON.parse( read( 'package.json' ) );

// Strip the leading docblock so only executable code is inspected.
const codeOf = ( src ) => src.replace( /^<\?php\s*\/\*\*[\s\S]*?\*\/\s*/, '' );

test( 'no empty PHP files in lib/functions', () => {
	for ( const file of featureFiles ) {
		assert.ok( read( file ).trim().length > 0, `${ file } is empty` );
	}
} );

test( 'every PHP file blocks direct access before any other code', () => {
	// Regression for #10.
	for ( const file of phpFiles ) {
		assert.match(
			codeOf( read( file ) ),
			/^(\/\/[^\n]*\n\s*)?if \( ! defined\( 'ABSPATH' \) \) \{\s*exit;\s*\}/,
			`${ file } does not start with an ABSPATH guard`
		);
	}
	assert.match(
		codeOf( read( 'uninstall.php' ) ),
		/^(\/\/[^\n]*\n\s*)?if \( ! defined\( 'WP_UNINSTALL_PLUGIN' \) \) \{\s*exit;\s*\}/
	);
} );

test( 'one text domain, matching the plugin slug', () => {
	const header = read( 'plugin.php' ).match( /^ \* Text Domain:\s*(\S+)$/m );
	assert.ok( header, 'plugin.php has no Text Domain header' );
	assert.equal( header[ 1 ], pkg.name );

	const i18n =
		/\b(?:__|_e|_x|_ex|_n|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*'(?:[^'\\]|\\.)*'\s*(?:,\s*'([^']*)')?\s*\)/g;
	let calls = 0;
	for ( const file of phpFiles ) {
		for ( const [ call, domain ] of read( file ).matchAll( i18n ) ) {
			calls++;
			assert.equal( domain, pkg.name, `${ file }: ${ call }` );
		}
	}
	assert.ok( calls > 0, 'no translation calls found; the pattern is stale' );
} );

test( 'dates use the site timezone', () => {
	// date() returns PHP's UTC date under WordPress; use wp_date().
	for ( const file of phpFiles ) {
		assert.doesNotMatch(
			read( file ),
			/(?<![\w>:$])date\(/,
			`${ file } calls date()`
		);
	}
} );

test( 'no leftovers from the UCSC/RCID plugin this was copied from', () => {
	// The CSP still lists UCSC hosts until it is rebuilt in #9.
	const files = [
		...phpFiles,
		'uninstall.php',
		'composer.json',
		'package.json',
		'README.md',
	];
	for ( const file of files ) {
		const lines = read( file )
			.split( '\n' )
			.filter( ( line ) => ! line.includes( 'Content-Security-Policy' ) );
		for ( const line of lines ) {
			assert.doesNotMatch(
				line,
				/ucsc|rcid|santa cruz|gmail\.edu/i,
				`${ file }: ${ line.trim() }`
			);
		}
	}
} );
