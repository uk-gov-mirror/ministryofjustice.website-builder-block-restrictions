<?php
/**
 * Website Builder Block Restrictions — the approved list of core blocks.
 *
 * This is the single place to edit which core blocks editors are allowed to
 * use. Names are full block type names, e.g. "core/paragraph".
 *
 * The list is code-defined on purpose: it lives in version control, is the
 * same on every site, and cannot be widened from the WordPress admin. The
 * only thing the settings page controls is whether the restriction is on.
 *
 * To change the list for one site without editing this file, hook the
 * `wbbr_allowed_blocks` filter from a site-specific plugin or the theme.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The approved core blocks.
 *
 * This is the signed-off list. Edit it here — it is not editable from the
 * admin on purpose.
 *
 * @return string[] Block type names.
 */
function wbbr_default_allowed_blocks() {
    return array(
        // Text blocks
        'core/code',
        'core/footnotes',
        'core/heading',
        'core/list',
        'core/list-item',
        'core/paragraph',
        'core/table',

        // Media blocks
        'core/cover',
        'core/file',
        'core/image',
        'core/media-text',
        'core/video',

        // Design blocks
        'core/buttons',
        'core/button',
        'core/columns',
        'core/column',
        'core/group',
        'core/spacer',

        // Widgets
        'core/legacy-widget',
        'core/social-links',
        'core/social-link',

        // Embeds
        'core/embed',

        // Pattern blocks
        'core/block',
        'core/pattern',
    );
}

/**
 * The final allowed list of core blocks, after filtering.
 *
 * @return string[] Block type names.
 */
function wbbr_allowed_blocks() {
    $blocks = wbbr_default_allowed_blocks();

    /**
     * Filter the approved list of core blocks.
     *
     * @param string[] $blocks Block type names (e.g. "core/paragraph").
     */
    $blocks = apply_filters( 'wbbr_allowed_blocks', $blocks );

    $blocks = array_values( array_unique( array_filter( array_map( 'strval', (array) $blocks ) ) ) );

    return $blocks;
}
