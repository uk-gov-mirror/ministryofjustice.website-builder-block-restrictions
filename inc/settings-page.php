<?php
/**
 * Website Builder Block Restrictions — settings page.
 *
 * A single per-site screen under Settings → Block Restrictions with one
 * control: turn the restriction on or off. The approved list of blocks
 * itself is code-defined (see inc/allowed-blocks.php) and cannot be changed
 * from here.
 *
 * Styled to match the Hale Components cache dashboard — a status panel on the
 * left, controls on the right (dist/css/wbbr-settings.css).
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'wbbr_add_settings_page' );

/**
 * Register the settings page under the Settings menu.
 */
function wbbr_add_settings_page() {
    add_options_page(
        __( 'Block Restrictions', 'wb-block-restrictions' ),
        __( 'Block Restrictions', 'wb-block-restrictions' ),
        'manage_options',
        WBBR_SETTINGS_PAGE,
        'wbbr_render_settings_page'
    );
}

add_action( 'admin_enqueue_scripts', 'wbbr_settings_enqueue' );

/**
 * Load the settings stylesheet on this screen only.
 *
 * @param string $hook_suffix Current admin page.
 */
function wbbr_settings_enqueue( $hook_suffix ) {
    if ( 'settings_page_' . WBBR_SETTINGS_PAGE !== $hook_suffix ) {
        return;
    }

    $rel  = '../dist/css/wbbr-settings.css';
    $path = plugin_dir_path( __FILE__ ) . $rel;

    wp_enqueue_style(
        'wbbr-settings',
        plugins_url( $rel, __FILE__ ),
        array(),
        file_exists( $path ) ? filemtime( $path ) : false
    );
}

/**
 * Render (and handle submission of) the settings page.
 */
function wbbr_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wb-block-restrictions' ) );
    }

    $updated = false;

    if ( isset( $_POST['wbbr_submit'] ) ) {
        check_admin_referer( 'wbbr_save_settings' );

        $enabled = ! empty( $_POST['wbbr_enabled'] ) ? '1' : '';
        update_option( WBBR_OPTION, $enabled );

        $updated = true;
    }

    $enabled   = wbbr_is_enabled();
    $blocks    = wbbr_allowed_blocks();
    $core_only = (bool) apply_filters( 'wbbr_restrict_core_only', true );

    // What the restriction does to everything registered right now.
    $split         = wbbr_split_registered_blocks();
    $non_core_test = static function ( $name ) {
        return 0 !== strpos( $name, 'core/' );
    };
    $non_core_allowed = array_values( array_filter( $split['allowed'], $non_core_test ) );
    $blocked          = $split['blocked'];
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Block Restrictions', 'wb-block-restrictions' ); ?></h1>

        <?php if ( $updated ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e( 'Settings saved.', 'wb-block-restrictions' ); ?></p>
            </div>
        <?php endif; ?>

        <p>
            <?php esc_html_e( 'When enabled, the block editor only offers the approved list of core blocks. Blocks from the theme and other plugins are unaffected. Existing content that uses other blocks keeps working.', 'wb-block-restrictions' ); ?>
        </p>
        <p>
            <?php esc_html_e( 'Administrators and super admins are never restricted — including you on this screen.', 'wb-block-restrictions' ); ?>
        </p>

        <div class="wbbr-dashboard-grid">
            <div class="wbbr-dashboard-item">
                <div class="wbbr-dashboard-left">
                    <h4><?php esc_html_e( 'Status', 'wb-block-restrictions' ); ?></h4>

                    <?php if ( $enabled ) : ?>
                        <p>
                            <span class="wbbr-status-on"><?php esc_html_e( 'ON', 'wb-block-restrictions' ); ?></span>
                            <?php esc_html_e( 'the editor is limited to the approved core blocks.', 'wb-block-restrictions' ); ?>
                        </p>
                    <?php else : ?>
                        <p>
                            <span class="wbbr-status-off"><?php esc_html_e( 'OFF', 'wb-block-restrictions' ); ?></span>
                            <?php esc_html_e( 'every registered block is available.', 'wb-block-restrictions' ); ?>
                        </p>
                    <?php endif; ?>

                    <p>
                        <?php
                        echo esc_html(
                            sprintf(
                                /* translators: %d: number of approved core blocks. */
                                _n( '%d approved core block.', '%d approved core blocks.', count( $blocks ), 'wb-block-restrictions' ),
                                count( $blocks )
                            )
                        );
                        ?>
                    </p>

                    <p>
                        <?php
                        echo esc_html(
                            $core_only
                                ? __( 'Theme and plugin blocks (mojblocks, govwind, ACF…): available.', 'wb-block-restrictions' )
                                : __( 'Theme and plugin blocks: also restricted to the approved list.', 'wb-block-restrictions' )
                        );
                        ?>
                    </p>
                </div>

                <div class="wbbr-dashboard-right">
                    <h4><?php esc_html_e( 'Manage', 'wb-block-restrictions' ); ?></h4>

                    <form class="wbbr-dashboard-form" method="post" action="">
                        <?php wp_nonce_field( 'wbbr_save_settings' ); ?>

                        <p>
                            <label for="wbbr_enabled">
                                <input type="checkbox" name="wbbr_enabled" id="wbbr_enabled" value="1" <?php checked( $enabled ); ?> />
                                <?php esc_html_e( 'Limit the editor to the approved core blocks', 'wb-block-restrictions' ); ?>
                            </label>
                        </p>

                        <p class="description">
                            <?php esc_html_e( 'Applies to this site only. Takes effect the next time an editor screen is loaded.', 'wb-block-restrictions' ); ?>
                        </p>

                        <?php submit_button( __( 'Save Changes', 'wb-block-restrictions' ), 'primary', 'wbbr_submit' ); ?>
                    </form>
                </div>
            </div>
        </div>

        <h2><?php esc_html_e( 'Approved core blocks', 'wb-block-restrictions' ); ?></h2>
        <p class="description">
            <?php esc_html_e( 'Edit inc/allowed-blocks.php to change this list.', 'wb-block-restrictions' ); ?>
        </p>
        <ul class="wbbr-block-list">
            <?php foreach ( $blocks as $block ) : ?>
                <li><code><?php echo esc_html( $block ); ?></code></li>
            <?php endforeach; ?>
        </ul>

        <h2><?php esc_html_e( 'Non-core blocks currently allowed', 'wb-block-restrictions' ); ?></h2>
        <p class="description">
            <?php
            echo esc_html(
                sprintf(
                    /* translators: %d: number of non-core blocks. */
                    _n( '%d block from the theme or another plugin.', '%d blocks from the theme and other plugins.', count( $non_core_allowed ), 'wb-block-restrictions' ),
                    count( $non_core_allowed )
                )
            );
            echo ' ';
            esc_html_e( 'Registered server-side; blocks added only in JavaScript are not listed.', 'wb-block-restrictions' );
            ?>
        </p>
        <?php if ( $non_core_allowed ) : ?>
            <ul class="wbbr-block-list">
                <?php foreach ( $non_core_allowed as $block ) : ?>
                    <li><code><?php echo esc_html( $block ); ?></code></li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p><?php esc_html_e( 'None — the strict allowlist is in force.', 'wb-block-restrictions' ); ?></p>
        <?php endif; ?>

        <h2><?php esc_html_e( 'Blocks currently blocked', 'wb-block-restrictions' ); ?></h2>
        <p class="description">
            <?php
            echo esc_html(
                sprintf(
                    /* translators: %d: number of blocked blocks. */
                    _n( '%d registered block would be hidden.', '%d registered blocks would be hidden.', count( $blocked ), 'wb-block-restrictions' ),
                    count( $blocked )
                )
            );
            ?>
        </p>
        <?php if ( $blocked ) : ?>
            <ul class="wbbr-block-list">
                <?php foreach ( $blocked as $block ) : ?>
                    <li><code><?php echo esc_html( $block ); ?></code></li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p><?php esc_html_e( 'None — every registered block is on an allowed list.', 'wb-block-restrictions' ); ?></p>
        <?php endif; ?>
    </div>
    <?php
}
