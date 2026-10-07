/**
 * Tests for the commit-and-tag-version plugin header updater.
 *
 * Run with `npm run test:unit`.
 */

const { test } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const path = require( 'node:path' );

const {
	readVersion,
	writeVersion,
} = require( '../wp-plugin-version-updater.js' );

const header = ( version ) =>
	`<?php\n/**\n * Plugin Name: Test\n * Version: ${ version }\n * Author: Test\n */\n`;

test( 'reads the version from the real plugin.php header', () => {
	const contents = fs.readFileSync(
		path.join( __dirname, '..', 'plugin.php' ),
		'utf8'
	);
	assert.match( readVersion( contents ), /^\d+\.\d+\.\d+/ );
} );

test( 'reads multi-digit segments in full', () => {
	assert.equal( readVersion( header( '1.0.10' ) ), '1.0.10' );
	assert.equal( readVersion( header( '12.34.56' ) ), '12.34.56' );
} );

test( 'reads prerelease versions', () => {
	assert.equal( readVersion( header( '1.1.0-rc.1' ) ), '1.1.0-rc.1' );
} );

test( 'writes the whole version, not a prefix of it', () => {
	const out = writeVersion( header( '1.0.10' ), '1.0.11' );
	assert.equal( readVersion( out ), '1.0.11' );
	assert.doesNotMatch( out, /1\.0\.110/ );
} );

test( 'replaces a prerelease with a final version', () => {
	const out = writeVersion( header( '1.1.0-rc.2' ), '1.1.0' );
	assert.match( out, / \* Version: 1\.1\.0\n/ );
} );

test( 'leaves the rest of the file untouched', () => {
	const before = header( '1.0.1' );
	const after = writeVersion( before, '1.0.2' );
	assert.equal( after, before.replace( '1.0.1', '1.0.2' ) );
} );

test( 'throws instead of silently drifting when no header exists', () => {
	assert.throws( () => readVersion( '<?php\n' ), /no "Version:" header/ );
	assert.throws(
		() => writeVersion( '<?php\n', '1.0.0' ),
		/no "Version:" header/
	);
} );
