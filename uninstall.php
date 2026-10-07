<?php
/**
 * Uninstall handler.
 *
 * Runs when the plugin is deleted from the Plugins screen — not on deactivation.
 *
 * POLICY: content outlives the plugin. The `person` and `quote` post types
 * (defined in acf-json/) exist so the site's content survives a theme change,
 * and deleting the plugin must not undo that. Their posts, terms and meta are
 * left completely untouched. The post types simply stop being registered, so
 * the content becomes invisible in the admin until something registers them
 * again; it is not destroyed. Nothing here should ever delete a post, and any
 * future addition that does needs a deliberate decision and an opt-in, not a
 * quiet line in this file.
 *
 * What is removed is the plugin's own bookkeeping. The plugin stores no options
 * of its own; everything below belongs to plugin-update-checker, which caches
 * update metadata and schedules a cron event. PUC clears its cron on
 * deactivation, but deleting a plugin does not always deactivate it first, and
 * uninstall.php runs without the plugin loaded, so the names are hard-coded
 * here. They are derived from the slug passed to buildUpdateChecker() in
 * plugin.php — if that slug changes, these change with it.
 *
 * @package jc_Custom_Functionality
 * @since   1.1.0
 * @link    https://developer.wordpress.org/plugins/plugin-basics/uninstall-methods/
 */

// Block anything that is not WordPress running the uninstall routine.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
 * Cached update state belonging to plugin-update-checker.
 *
 * Written with update_site_option(), so it must be removed with
 * delete_site_option() to be found on both single and multisite installs.
 */
delete_site_option( 'external_updates-jc-core-functionality' );

// Short-lived transient holding the errors from a manual "Check for updates".
delete_site_transient( 'puc_manual_check_errors-jc-core-functionality' );

// The twice-daily update check PUC schedules.
wp_clear_scheduled_hook( 'puc_cron_check_updates-jc-core-functionality' );
