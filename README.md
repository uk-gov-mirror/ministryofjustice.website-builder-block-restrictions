# Website Builder Block Restrictions

A WordPress plugin that limits the block editor to an
approved list of core blocks. Each site has its own on/off switch.

## What it does

- Adds **Settings → Block Restrictions** (capability `manage_options`), a
  per-site page with a single toggle.
- When the toggle is **on**, the block inserter only offers the core blocks
  on the approved list. When **off**, every block is available (default).
- **Administrators and super admins are never restricted.** Change the exempt capability with the
  `wbbr_bypass_capability` filter.
- Blocks from the theme and other plugins (`mojblocks/*`, `govwind/*`,
  `acf/*`, …) are **not** affected — the restriction is about core blocks
  only. Set the `wbbr_restrict_core_only` filter to `false` for a strict
  allowlist where nothing outside the approved list is offered.
- Existing content that uses a now-disallowed block keeps rendering and
  stays editable. This filters the editor UI (`allowed_block_types_all`); it
  does not unregister blocks or rewrite content on save.
- The settings page lists, for the current state, the approved core blocks,
  the non-core blocks still allowed, and every block that would be blocked —
  read from the server-side block registry (JS-only blocks are not shown).

## Configuring the approved list

The list is **code-defined** in [`inc/allowed-blocks.php`](inc/allowed-blocks.php)
— edit the array in `wbbr_default_allowed_blocks()`. It is deliberately not
editable from the admin so it stays in version control and consistent across
sites.

To adjust it for a single site without editing the file, hook the filter:

```php
add_filter( 'wbbr_allowed_blocks', function ( $blocks ) {
    $blocks[] = 'core/verse';
    return array_values( array_diff( $blocks, array( 'core/code' ) ) );
} );
```

## Filters

| Filter | Default | Purpose |
| ------ | ------- | ------- |
| `wbbr_allowed_blocks` | starter set in `inc/allowed-blocks.php` | The approved core block names. |
| `wbbr_restrict_core_only` | `true` | Keep non-core blocks available. `false` = strict allowlist. |
| `wbbr_bypass_capability` | `'manage_options'` | Capability that exempts a user from the restriction. Super admins are always exempt. |

## Install

Place the `wb-block-restrictions` folder in `wp-content/mu-plugins/` (or
`plugins/` and activate).

## Uninstall

`uninstall.php` deletes the `wbbr_restrict_blocks_enabled` option from every
site.
