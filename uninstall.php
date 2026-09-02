<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Removes the per-site option on every site in the network.
 *
 * @link    https://github.com/ministryofjustice/wb-block-restrictions
 * @package WB Block Restrictions
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$wbbr_option = 'wbbr_restrict_blocks_enabled';

if ( is_multisite() ) {
    $wbbr_site_ids = get_sites(
        array(
            'fields' => 'ids',
            'number' => 0,
        )
    );

    foreach ( $wbbr_site_ids as $wbbr_site_id ) {
        switch_to_blog( (int) $wbbr_site_id );
        delete_option( $wbbr_option );
        restore_current_blog();
    }
} else {
    delete_option( $wbbr_option );
}
