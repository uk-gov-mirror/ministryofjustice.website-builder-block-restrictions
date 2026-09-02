<?php
/**
 * Plugin Name: Website Builder Block Restrictions
 * Plugin URI:  https://github.com/ministryofjustice/website-builder-block-restrictions
 * Description: Restricts the block editor to an approved list of core blocks. A per-site settings page turns the restriction on or off.
 * Version:     1.0.0
 * Author:      Ministry of Justice
 * Author URI:  https://github.com/ministryofjustice
 * Text Domain: wb-block-restrictions
 * License:     MIT
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'WBBR_OPTION' ) ) {
    // Per-site option: '1' when the restriction is enabled, '' otherwise.
    define( 'WBBR_OPTION', 'wbbr_restrict_blocks_enabled' );
}

if ( ! defined( 'WBBR_SETTINGS_PAGE' ) ) {
    define( 'WBBR_SETTINGS_PAGE', 'wb-block-restrictions' );
}

include 'inc/allowed-blocks.php';
include 'inc/restrict-blocks.php';
include 'inc/settings-page.php';
