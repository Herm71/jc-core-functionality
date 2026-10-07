/**
 * Consistency checks for plugin metadata and bootstrap includes.
 *
 * Run with `npm run test:unit`.
 */

const { test } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const path = require( 'node:path' );

const root = path.join( __dirname, '..' );
const read = ( file ) => fs.readFileSync( path.join( root, file ), 'utf8' );

const plugin = read( 'plugin.php' );
const pkg = JSON.parse( read( 'package.json' ) );
const composer = JSON.parse( read( 'composer.json' ) );
const lock = JSON.parse( read( 'package-lock.json' ) );

const header = ( name ) => {
	const match = plugin.match( new RegExp( `^ \\* ${ name }:\\s*(.+)$`, 'm' ) );
	return match && match[ 1 ].trim();
};

test( 'license is GPL-2.0-or-later everywhere it is declared', () => {
	const spdx = 'GPL-2.0-or-later';
	assert.equal( header( 'License' ), spdx );
	assert.equal( pkg.license, spdx );
	assert.equal( lock.packages[ '' ].license, spdx );
	assert.equal( composer.license, spdx );
} );

test( 'License URI points at the GPL-2.0 text', () => {
	assert.equal(
		header( 'License URI' ),
		'https://www.gnu.org/licenses/gpl-2.0.html'
	);
} );

test( 'LICENSE exists, is GPL-2.0, and ships in the zip', () => {
	assert.ok( pkg.files.includes( 'LICENSE' ) );
	const license = read( 'LICENSE' );
	assert.match( license, /GNU GENERAL PUBLIC LICENSE\s+Version 2, June 1991/ );
} );

test( 'every feature file is loaded exactly once, behind file_exists()', () => {
	// Regression for #8: security-headers.php was required bare, then
	// included again inside its file_exists() guard.
	const files = fs
		.readdirSync( path.join( root, 'lib/functions' ) )
		.filter( ( f ) => f.endsWith( '.php' ) );

	for ( const file of files ) {
		const target = `JC_DIR . '/lib/functions/${ file }'`;
		const loads = plugin.split( `include_once ${ target }` ).length - 1;
		const bare =
			plugin.split( `require_once ${ target }` ).length -
			1 +
			plugin.split( `require ${ target }` ).length -
			1;

		assert.equal( bare, 0, `${ file } is loaded without a guard` );
		assert.ok( loads <= 1, `${ file } is included ${ loads } times` );
		if ( loads === 1 ) {
			assert.ok(
				plugin.includes( `file_exists(${ target })` ) ||
					plugin.includes( `file_exists( ${ target } )` ),
				`${ file } is included without a file_exists() guard`
			);
		}
	}
} );
