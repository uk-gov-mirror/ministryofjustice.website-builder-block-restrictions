<?php
/**
 * Website Builder Block Restrictions — apply the restriction in the block editor.
 *
 * Hooks `allowed_block_types_all` so the inserter only offers the approved
 * core blocks. Third-party and theme blocks (mojblocks/*, govwind/*, acf/*,
 * and so on) are left alone by default — the restriction is about core
 * blocks, not the platform's own design system. Set the
 * `wbbr_restrict_core_only` filter to false for a strict allowlist where
 * nothing outside `wbbr_allowed_blocks()` is available.
 *
 * Administrators and network super admins are never restricted — they keep
 * the full block library.
 *
 * Note: this filters the editor UI only. It does not unregister blocks or
 * strip them on save, so content that already uses a now-disallowed block
 * keeps working and stays editable.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Is the restriction switched on for the current site?
 *
 * @return bool
 */
function wbbr_is_enabled() {
    return '1' === (string) get_option( WBBR_OPTION, '' );
}

/**
 * Should the current user skip the restriction?
 *
 * Network super admins are always exempt. Otherwise the `wbbr_bypass_capability`
 * filter decides which capability grants exemption — `manage_options` by
 * default, i.e. site administrators.
 *
 * @return bool
 */
function wbbr_user_is_exempt() {
    if ( is_super_admin() ) {
        return true;
    }

    $capability = apply_filters( 'wbbr_bypass_capability', 'manage_options' );

    return ! empty( $capability ) && current_user_can( $capability );
}

add_filter( 'allowed_block_types_all', 'wbbr_filter_allowed_block_types', 10, 2 );

/**
 * Restrict the blocks offered by the editor.
 *
 * @param bool|string[] $allowed_block_types Current value: true for "all
 *                      blocks", or an array of allowed block type names if
 *                      something has already narrowed it.
 * @param WP_Block_Editor_Context $context   The editor context (unused).
 * @return bool|string[]
 */
function wbbr_filter_allowed_block_types( $allowed_block_types, $context ) {
    if ( ! wbbr_is_enabled() || wbbr_user_is_exempt() ) {
        return $allowed_block_types;
    }

    $split = wbbr_split_registered_blocks();

    // Merge the approved core list back in: a block on it might not be
    // registered yet on the screen this filter runs for, and it must never be
    // dropped for that reason.
    $allowlist = array_values( array_unique( array_merge( $split['allowed'], wbbr_allowed_blocks() ) ) );

    // If another plugin already restricted the list, respect it: never widen
    // beyond what it allowed.
    if ( is_array( $allowed_block_types ) ) {
        $allowlist = array_values( array_intersect( $allowlist, $allowed_block_types ) );
    }

    return $allowlist;
}

/**
 * Split every block registered on this request into the ones the restriction
 * allows and the ones it blocks.
 *
 * Rules, matching wbbr_filter_allowed_block_types():
 *   - a block on the approved core list is always allowed;
 *   - any other non-core block is allowed unless `wbbr_restrict_core_only` is
 *     set to false (strict allowlist);
 *   - everything else is blocked.
 *
 * This looks only at what is registered server-side. Blocks registered purely
 * in JavaScript will not appear here. It also ignores other plugins that may
 * filter `allowed_block_types_all` as well.
 *
 * @return array{allowed: string[], blocked: string[]} Sorted block names.
 */
function wbbr_split_registered_blocks() {
    $result = array(
        'allowed' => array(),
        'blocked' => array(),
    );

    if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
        return $result;
    }

    $approved  = wbbr_allowed_blocks();
    $core_only = (bool) apply_filters( 'wbbr_restrict_core_only', true );
    $names     = array_keys( WP_Block_Type_Registry::get_instance()->get_all_registered() );

    sort( $names );

    foreach ( $names as $name ) {
        $is_core = 0 === strpos( $name, 'core/' );

        if ( in_array( $name, $approved, true ) || ( ! $is_core && $core_only ) ) {
            $result['allowed'][] = $name;
        } else {
            $result['blocked'][] = $name;
        }
    }

    return $result;
}
