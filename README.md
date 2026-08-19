# Fuse CMS Framework for WordPress

A framework plugin that gives a WordPress site a shared foundation: a class
autoloader, a page layout system, a form and settings builder, an optional set of
post types and editor blocks, and an update channel for Fuse plugins and themes.

This is not a standalone feature plugin. It is the base a Fuse theme and any Fuse
companion plugin are built on, and most of what it provides is only reachable from
theme or plugin code.

- **Author:** 7-90 Systems — <https://7-90.com.au>
- **Plugin URI:** <https://fusecms.org>
- **Version:** 2.0
- **Requires:** WordPress 6.0+, PHP 7.4+
- **Text domain:** `fuse`

## Installation

Activate the plugin. On activation it creates a `fuse_layouts` post called
"Global Default Layout" and sets it as the site-wide default, but only if no layout
already exists.

Most features are off until switched on under **Fuse CMS → Site Settings**.

## Settings

A top-level **Fuse CMS** menu is added, gated on `manage_options`. Its settings are
stored as options prefixed `fuse_setting_`, read and written with
`get_fuse_option ()` and `update_fuse_option ()`.

| Panel | Setting | Effect |
| --- | --- | --- |
| Email Sender | `fuse_email_from_name`, `fuse_email_from_email` | Sets the site's outgoing mail sender |
| Theme CSS Styles | `theme_css_layout` | Enables the bundled layout stylesheet |
| | `theme_css_buttons` | Enables the bundled button stylesheet |
| | `theme_css_block` | Disables the Gutenberg block stylesheets |
| | `theme_css_woo` | Disables the WooCommerce stylesheets (only shown when WooCommerce is active) |
| Theme Features | `faq_posttype` | Enables the FAQ post type and its block |
| | `sliders_posttype` | Enables the Slider and Slide post types and the slider block |
| | `tabs_block` | Enables the tabs editor block |
| | `html_fragments` | Enables the AJAX HTML fragments system |
| | `web_fonts` | Auto-loads web fonts found in the theme |
| | `fallback_image` | Attachment used when an image is missing |
| Development Features | `pagetype_builder` | Enables the Post Type Builder |
| Header & Footer Scripts | `header_scripts`, `body_scripts`, `footer_scripts` | Markup printed verbatim in `wp_head`, `wp_body_open` and `wp_footer` |
| Google API | `google_api_key` | Key used for Maps and geocoding |
| Contact Details | `contact_<location>_<field>` | Phone, email, street, town, state and postcode per location |

The three script fields are printed unescaped by design, which is why the settings
screen and its save handler both require `manage_options`.

Contact locations default to a single `default` location. Add more with the
`fuse_settings_contact_locations` filter, and change the per-location fields with
`fuse_settings_contact_fields`.

## Layouts

Layouts are a `fuse_layouts` post type. Each layout decides which of six regions are
shown — header, two left columns, two right columns and footer — which sidebar goes
in each column, and any extra body classes. A layout can be set as the default for
the site, for a post type, for a post type archive, for a taxonomy, or for the 404
page; individual posts can override it from a **Layout** meta box.

The resulting body classes (`fuse-layout-three-col`, `fuse-layout-single-left-col`,
`with-header` and so on) are added through the `body_class` filter.

## Post types

| Slug | Purpose | Enabled by |
| --- | --- | --- |
| `fuse_layouts` | Page layouts | Always |
| `fuse_faq` | FAQs, with a `fuse_faq_section` taxonomy | `faq_posttype` |
| `fuse_slider` | Sliders | `sliders_posttype` |
| `fuse_slide` | Slides within a slider, with optional start and end dates | `sliders_posttype` |
| `fuse_posttype` | Post Type Builder — defines further post types and their meta boxes from the admin | `pagetype_builder` |

## Blocks

| Block | Enabled by |
| --- | --- |
| `fuse/tabs` and `fuse/tab` | `tabs_block` |
| `fuse-slider/main` | `sliders_posttype` |
| `dynamic-select-block/main` — the FAQs block | `faq_posttype` |

The FAQs block name is a leftover from the block it was originally derived from.
Renaming it would invalidate existing content, so it has been left as it is.

## Shortcodes

- `[content_block]`
- `[content_column]`
- `[contact_field]`

Each renders a template, resolved from the theme first and then from the plugin's
`templates/shortcodes/` directory. Add more with `fuse_register_shortcodes`.

## Template functions

Available to themes once the plugin is active. Loaded from `functions/`.

**Options and meta**

- `get_fuse_option ($name, $default)` / `update_fuse_option ($name, $value)`
- `get_fuse_post_meta ($post_id, $name, $single)` / `update_fuse_post_meta ($post_id, $name, $value, $prev)`

**Contact details**

- `fuse_get_contact_field ($field, $location)`
- `fuse_get_contact_phone ($field, $location, $link, $link_text)`
- `fuse_get_contact_email ($field, $location, $link, $link_text)`

**Template parts and navigation**

- `fuse_get_header ($location)`, `fuse_get_footer ($location)`, `fuse_get_sidebar ($location)`
- `fuse_paging_nav ($args)`, `fuse_comments_paging_nav ($args)`

**Images**

- `fuse_get_image ($image_id, $size, $fallback)` / `fuse_get_image_url (...)`
- `fuse_get_feature_image ($post, $size, $fallback)` / `fuse_get_feature_image_url (...)`
- `fuse_responsive_image ($args)` — accepts `image`, `size`, `alt`, `class` and `caption`

**FAQs**

- `fuse_faqs_list ($section_id)`

**Markup helpers**

- `fuse_format_attribute ($value, $name, $render)`
- `fuse_format_attributes ($attributes, $hide_empty, $render)`
- `fuse_format_phone_number_link ($phone)`

**Security helpers**

- `fuse_can_save_post_meta ($post_id)` — every `save_post` handler must call this
  before writing anything. It rules out autosaves and revisions, checks `edit_post`
  on that specific post, and verifies WordPress's own post edit nonce.
- `fuse_sanitise_meta ($value)` — walks a string or nested array from `$_POST` and
  runs every scalar through `sanitize_text_field ()`.
- `fuse_sanitise_html ($value)` — `wp_kses_post ()` for values meant to hold markup.
- `fuse_block_direct_access ()` — dies if `ABSPATH` is not defined.

## For developers

**Autoloading.** Classes are resolved from the `Fuse` namespace:

| Namespace | Resolves to |
| --- | --- |
| `Fuse\Foo\Bar` | `library/Foo/Bar.php` in this plugin |
| `Fuse\Theme\<Name>\Foo` | `library/Foo.php` under `FUSE_THEME_<NAME>_BASE_URI` |
| `Fuse\Plugin\<Name>\Foo` | `library/Foo.php` under `FUSE_PLUGIN_<NAME>_BASE_URI` |

A theme or companion plugin defines its own `FUSE_THEME_*_BASE_URI` or
`FUSE_PLUGIN_*_BASE_URI` constant to join the autoloader. If the constant is not
defined the autoloader simply passes, so an unrelated class of the same shape will
not break the request.

**Function files.** Every `.php` file in `functions/` is loaded automatically, except
`index.php`. Add your own directories with the `fuse_load_functions_from` filter.

**Getting in early.** `fuse_init` fires once the framework is set up and is the hook
a companion plugin should use. `fuse_register_posttypes` fires when post types are
being registered.

**Hooks.** The main extension points, all documented in the docblock of the class
that fires them:

- Setup — `fuse_init`, `fuse_before_load_functions`, `fuse_after_load_functions`,
  `fuse_load_functions_from`, `fuse_register_posttypes`
- Theme — `fuse_theme_supports`, `fuse_nav_menus`, `fuse_sidebars`,
  `fuse_image_sizes`, `fuse_register_shortcodes`, `fuse_register_assets`
- Assets — `fuse_css_dependencies`, `fuse_javascript_dependencies`, and the matching
  `*_admin_*` and `*_login_*` variants, plus
  `fuse_before_enqueue_css` / `fuse_after_enqueue_css` and their JavaScript pair
- Layout — `before_fuse_post_layout`, `after_fuse_post_layout`,
  `fuse_layout_sidebar_class`, `fuse_sidebar_classes`
- Settings — `fuse_settings_form_panels`, `fuse_settings_contact_fields`,
  `fuse_settings_contact_locations`, `fuse_admin_menu`
- Fragments — `fuse_theme_fragments`

**HTML fragments.** With `html_fragments` on, the plugin prints a small script that
calls `admin-ajax.php` after load and replaces the inner HTML of elements by ID. Hook
`fuse_theme_fragments` and return an array keyed by element ID. It is intended for
keeping a few dynamic areas live on an aggressively cached site.

## Updates

Plugins and themes carrying a `Fuse Update Server:` header are offered updates from
that server. This plugin performs the check for all of them.

The scheme is forced to HTTPS. The update endpoint decides which package the site
downloads, so a plain HTTP exchange there is remote code execution for anyone able to
intercept it. A server without HTTPS will fail its update check rather than fall back.
Loopback addresses are the only exception, so a local update server can still be used
in development.

## Bundled libraries

`assets/external/` carries bxSlider, Colorbox, mmenu-light, slick, Superfish and the
jQuery UI **stylesheet**. The jQuery UI script is deliberately not bundled — every
jQuery UI script comes from WordPress core's own `jquery-ui-*` handles. Only the
stylesheet is kept, because core registers no full jQuery UI theme stylesheet.

## Licence

See the licence of each bundled library in `assets/external/`. The framework itself is
© 7-90 Systems.
