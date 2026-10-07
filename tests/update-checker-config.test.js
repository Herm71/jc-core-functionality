/**
 * Consistency checks for the plugin-update-checker wiring.
 *
 * The updater slug appears in plugin.php, uninstall.php, the release asset
 * name and the package name. Each mismatch fails silently on a live site
 * (no update offered, or leftover options on uninstall), so pin them here.
 *
 * Run with `npm run test:unit`.
 */

const { test } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const path = require( 'node:path' );

const root = path.join( __dirname, '..' );
const read = ( file ) => fs.readFileSync( path.join( root, file ), 'utf8' );

const pkg = JSON.parse( read( 'package.json' ) );
const plugin = read( 'plugin.php' );
const uninstall = read( 'uninstall.php' );
const workflow = read( '.github/workflows/release.yml' );

const slugMatch = plugin.match(
	/buildUpdateChecker\(\s*'[^']+',\s*__FILE__,\s*'([^']+)'/
);

test( 'plugin.php registers the update checker with a slug', () => {
	assert.ok( slugMatch, 'buildUpdateChecker() call not found' );
} );

const slug = slugMatch && slugMatch[ 1 ];

test( 'slug matches the package name, which names the release zip', () => {
	assert.equal( slug, pkg.name );
} );

test( 'release asset regex matches the zip the workflow uploads', () => {
	const assetPattern = plugin.match( /enableReleaseAssets\(\s*'\/(.+?)\/'/ );
	assert.ok( assetPattern, 'enableReleaseAssets() call not found' );
	assert.match( `${ pkg.name }.zip`, new RegExp( assetPattern[ 1 ] ) );
	assert.match( workflow, new RegExp( `files:\\s*${ pkg.name }\\.zip` ) );
} );

test( 'release assets are required, not preferred', () => {
	assert.match( plugin, /REQUIRE_RELEASE_ASSETS/ );
} );

test( 'uninstall.php clears PUC state under the same slug', () => {
	for ( const name of [
		`external_updates-${ slug }`,
		`puc_manual_check_errors-${ slug }`,
		`puc_cron_check_updates-${ slug }`,
	] ) {
		assert.ok( uninstall.includes( `'${ name }'` ), `missing ${ name }` );
	}
} );

test( 'release zip ships the update checker and ACF definitions', () => {
	for ( const entry of [ 'vendor', 'acf-json', '*.php' ] ) {
		assert.ok( pkg.files.includes( entry ), `files is missing ${ entry }` );
	}
} );

test( 'CI installs runtime PHP dependencies before packaging', () => {
	const install = workflow.indexOf( 'composer install --no-dev' );
	const pack = workflow.indexOf( 'wp-scripts plugin-zip' );
	assert.ok( install !== -1, 'composer install --no-dev step missing' );
	assert.ok( install < pack, 'composer install must run before plugin-zip' );
} );

test( 'RC tags are published as prereleases, never as Latest', () => {
	// Regression for #21: PUC reads /releases/latest, which skips only
	// releases flagged prerelease, so an unflagged RC reaches every site.
	const release = workflow.slice( workflow.indexOf( '- name: Release' ) );
	const match = release.match(
		/prerelease:\s*\$\{\{\s*contains\(\s*github\.ref_name,\s*'([^']+)'\s*\)\s*\}\}/
	);
	assert.ok( match, 'Release step does not set prerelease from the tag' );

	// GitHub's contains() is a case-insensitive substring test.
	const isPrerelease = ( tag ) =>
		tag.toLowerCase().includes( match[ 1 ].toLowerCase() );

	assert.equal( isPrerelease( 'v1.2.0-rc.0' ), true );
	assert.equal( isPrerelease( 'v1.2.0-rc.12' ), true );
	assert.equal( isPrerelease( 'v1.2.0' ), false );
	assert.equal( isPrerelease( 'v10.0.0' ), false );

	// Every tag pattern that triggers the workflow and looks like an RC must
	// be caught by the expression.
	const triggers = [ ...workflow.matchAll( /^\s+- '(v[^']+)'$/gm ) ].map(
		( m ) => m[ 1 ]
	);
	assert.ok( triggers.length > 0, 'no tag triggers found' );
	for ( const pattern of triggers.filter( ( p ) => /rc/i.test( p ) ) ) {
		assert.ok(
			isPrerelease( pattern ),
			`${ pattern } would publish as Latest`
		);
	}
} );

test( 'no competing GitHub Updater header', () => {
	assert.doesNotMatch( plugin, /GitHub Plugin URI:/ );
} );

test( 'no PHP file rewrites update-check requests', () => {
	// PUC is the only update path. A request filter here would compete with
	// it, and the previous one (#5) was dead code that would fatal if it ran.
	const phpFiles = [
		'plugin.php',
		'uninstall.php',
		...fs
			.readdirSync( path.join( root, 'lib/functions' ) )
			.filter( ( f ) => f.endsWith( '.php' ) )
			.map( ( f ) => `lib/functions/${ f }` ),
	];

	for ( const file of phpFiles ) {
		assert.doesNotMatch(
			read( file ),
			/['"]http_request_args['"]/,
			`${ file } filters http_request_args`
		);
	}
} );
