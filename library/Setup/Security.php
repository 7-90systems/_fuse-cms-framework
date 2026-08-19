<?php
    /**
     *  @package fusecms
     *
     *  @filter fuse_settings_form_panels
     *  @filter fuse_security_defaults
     *
     *  This class is our security baseline. It holds the settings, applies the
     *  protections that run in PHP, and adds its own panel to the settings form
     *  through the panels filter, so nothing in the admin class has to know it
     *  is here.
     *
     *  The protections are split in two. The ones applied here work on any
     *  server. The rest are server directives, which only Apache and LiteSpeed
     *  read from .htaccess - those are built by the Rules class and are off
     *  until they are deliberately switched on.
     *
     *  The panel is a set of Fuse form fields rather than markup of its own, so
     *  the settings form renders and saves it the way it handles every other
     *  panel and there is no separate render, nonce or save path to keep right.
     */

    namespace Fuse\Setup;

    use Fuse\Traits\Singleton;
    use Fuse\Forms\Component;


    class Security {

        use Singleton;

        /**
         *  @var string The prefix every one of our settings is stored under.
         *
         *  Fuse options are already prefixed 'fuse_setting_', so this only has
         *  to separate ours from the rest of the settings form.
         */
        const PREFIX = 'security_';




        /**
         *  @var bool Whether a rules rewrite is already queued this request.
         */
        protected $_rules_queued = false;

        /**
         *  @var bool Whether DISALLOW_FILE_EDIT was already defined before we
         *  looked -- by wp-config.php, or by something loading ahead of us.
         *
         *  Recorded because by the time the settings screen renders, this class
         *  may have defined it itself, and the panel has to be able to tell the
         *  difference to say anything useful about it.
         */
        protected $_file_edit_external = false;




        /**
         *  Set up our object.
         */
        protected function _init () {
            /**
             *  The protections read the settings, and get_fuse_option () does
             *  not exist yet -- the function files load on after_setup_theme at
             *  priority 1, while this class is built as the plugin file loads.
             *  Wait until they are there. Priority 2 matches the point the
             *  framework reads its own settings from.
             *
             *  This is still early enough for everything applied here:
             *  xmlrpc_enabled, rest_endpoints, parse_request and wp_head are
             *  all read after after_setup_theme has run.
             */
            add_action ('after_setup_theme', array ($this, 'protect'), 2);

            if (is_admin () === true) {
                add_filter ('fuse_settings_form_panels', array ($this, 'settingsPanel'));

                /**
                 *  The settings form does not announce that it has saved, so
                 *  watch for one of our own options changing instead. The
                 *  rewrite is queued for the end of the request so that saving
                 *  a dozen settings still writes the file once.
                 */
                add_action ('updated_option', array ($this, 'queueRules'), 10, 1);
                add_action ('added_option', array ($this, 'queueRules'), 10, 1);
            } // if ()
        } // _init ()

        /**
         *  Note that one of our settings changed, and rewrite the rules once
         *  the request is finished.
         *
         *  @param string $option The option that was saved.
         */
        public function queueRules ($option) {
            if ($this->_rules_queued === true) {
                return;
            } // if ()

            if (strpos ($option, 'fuse_setting_'.self::PREFIX) !== 0) {
                return;
            } // if ()

            $this->_rules_queued = true;

            add_action ('shutdown', array ($this, 'writeRules'));
        } // queueRules ()




        /**
         *  Add our panel to the settings form.
         *
         *  The two halves are separate groups: the protections that work
         *  everywhere, and the ones that need the server's co-operation. They
         *  behave very differently, and reading them as one list of twenty-odd
         *  switches hides that.
         *
         *  @param array $panels The panels already on the form.
         *
         *  @return array The panels, with ours added.
         */
        public function settingsPanel ($panels) {
            if (is_array ($panels) === false) {
                $panels = array ();
            } // if ()

            $panels [] = new Component\Panel ('security', __ ('Security', 'fuse'), array (
                new Component\Field\Group (
                    'security_application',
                    __ ('Applied by WordPress', 'fuse'),
                    $this->_applicationFields ()
                ),
                new Component\Field\Group (
                    'security_server',
                    __ ('Server rules', 'fuse'),
                    $this->_serverFields ()
                )
            ));

            return $panels;
        } // settingsPanel ()

        /**
         *  The protections that work on any server.
         *
         *  @return array The fields.
         */
        protected function _applicationFields () {
            return array (
                new Component\Field\Toggle (self::PREFIX.'xmlrpc', __ ('Disable XML-RPC', 'fuse'), $this->option ('xmlrpc'), array (
                    'description' => $this->_xmlrpcDescription ()
                )),
                new Component\Field\Toggle (self::PREFIX.'rest_users', __ ('Hide the user list from visitors', 'fuse'), $this->option ('rest_users'), array (
                    'description' => __ ('Stops the REST API listing your user names to anyone not logged in. Logged-in users still see it, because the editor needs it.', 'fuse')
                )),
                new Component\Field\Toggle (self::PREFIX.'author_enum', __ ('Block author look-ups', 'fuse'), $this->option ('author_enum'), array (
                    'description' => __ ('Turns ?author=1 into a 404, and leaves the users out of the sitemap so the same list is not simply published elsewhere.', 'fuse')
                )),
                new Component\Field\Toggle (self::PREFIX.'version', __ ('Remove the WordPress version', 'fuse'), $this->option ('version'), array (
                    'description' => __ ('Takes the version out of the page head and the feeds.', 'fuse')
                )),
                new Component\Field\Toggle (self::PREFIX.'version_assets', __ ('Also strip it from asset URLs', 'fuse'), $this->option ('version_assets'), array (
                    'description' => __ ('Only removes the version WordPress adds to its own files. A plugin that sets its own keeps it, because that is its cache busting.', 'fuse')
                )),
                new Component\Field\Toggle (self::PREFIX.'file_edit', __ ('Disable the file editor', 'fuse'), $this->option ('file_edit'), array (
                    'description' => $this->_fileEditDescription ()
                ))
            );
        } // _applicationFields ()

        /**
         *  The protections written to .htaccess.
         *
         *  @return array The fields.
         */
        protected function _serverFields () {
            return array (
                new Component\Field\Toggle (self::PREFIX.'htaccess', __ ('Write server rules to .htaccess', 'fuse'), $this->option ('htaccess'), array (
                    'description' => $this->_serverDescription ()
                )),
                new Component\Field\Toggle (self::PREFIX.'headers', __ ('Security headers', 'fuse'), $this->option ('headers'), array (
                    'description' => __ ('The switches below only take effect while this and the server rules above are on.', 'fuse')
                )),
                new Component\Field\Toggle (self::PREFIX.'header_xfo', __ ('X-Frame-Options', 'fuse'), $this->option ('header_xfo'), array (
                    'description' => __ ('Controls who may put this site inside a frame, which is what clickjacking relies on.', 'fuse')
                )),
                new Component\Field\Select (self::PREFIX.'header_xfo_value', __ ('Frames allowed from', 'fuse'), array (
                    'SAMEORIGIN' => __ ('This site only (SAMEORIGIN)', 'fuse'),
                    'DENY' => __ ('Nowhere at all (DENY)', 'fuse')
                ), $this->option ('header_xfo_value'), array (
                    'description' => __ ('Choose nowhere at all unless something on this site frames its own pages.', 'fuse')
                )),
                new Component\Field\Toggle (self::PREFIX.'header_xcto', __ ('X-Content-Type-Options', 'fuse'), $this->option ('header_xcto'), array (
                    'description' => __ ('Stops a browser guessing a file is something other than what it was served as.', 'fuse')
                )),
                new Component\Field\Toggle (self::PREFIX.'header_referrer', __ ('Referrer-Policy', 'fuse'), $this->option ('header_referrer'), array (
                    'description' => __ ('Controls how much of the current address is passed on when a visitor follows a link away from this site.', 'fuse')
                )),
                new Component\Field\Select (self::PREFIX.'header_referrer_value', __ ('Referrer sent', 'fuse'), array (
                    'strict-origin-when-cross-origin' => __ ('Full address here, domain only elsewhere', 'fuse'),
                    'same-origin' => __ ('Only within this site', 'fuse'),
                    'strict-origin' => __ ('Domain only, always', 'fuse'),
                    'no-referrer' => __ ('Never send one', 'fuse')
                ), $this->option ('header_referrer_value'), array (
                    'description' => __ ('The default keeps the page address inside this site and sends only the domain elsewhere, which is what analytics needs without leaking the path.', 'fuse')
                )),
                new Component\Field\Toggle (self::PREFIX.'header_permissions', __ ('Permissions-Policy', 'fuse'), $this->option ('header_permissions'), array (
                    'description' => __ ('States which browser features this site will never ask for, so a script that has got in cannot ask either.', 'fuse')
                )),
                new Component\Field\TextArea (self::PREFIX.'header_permissions_value', __ ('Features refused', 'fuse'), $this->option ('header_permissions_value'), array (
                    'description' => __ ('The browser features this site will not ask for. The default refuses the lot.', 'fuse')
                )),
                new Component\Field\Toggle (self::PREFIX.'header_hsts', __ ('HSTS', 'fuse'), $this->option ('header_hsts'), array (
                    'description' => $this->_hstsDescription ()
                )),
                new Component\Field\Number (self::PREFIX.'header_hsts_maxage', __ ('Remember for (seconds)', 'fuse'), $this->option ('header_hsts_maxage'), array (
                    'min' => 0,
                    'step' => 1,
                    'description' => __ ('How long a browser refuses plain HTTP for. The default is a year. Start smaller while testing, because this cannot be called back once a browser has been told.', 'fuse')
                )),
                new Component\Field\Toggle (self::PREFIX.'header_hsts_subdomains', __ ('Include sub-domains', 'fuse'), $this->option ('header_hsts_subdomains'), array (
                    'description' => __ ('Only switch this on once every sub-domain is on HTTPS as well.', 'fuse')
                )),
                new Component\Field\Toggle (self::PREFIX.'header_hsts_preload', __ ('Ask to be preloaded', 'fuse'), $this->option ('header_hsts_preload'), array (
                    'description' => __ ('Asks for this domain to be built into the browsers themselves. Getting back off that list takes months, so only ask once HTTPS is settled everywhere.', 'fuse')
                )),
                new Component\Field\Toggle (self::PREFIX.'header_csp', __ ('Content-Security-Policy', 'fuse'), $this->option ('header_csp'), array (
                    'description' => __ ('States where scripts, styles and images may be loaded from. The most effective of these headers, and the easiest to get wrong.', 'fuse')
                )),
                new Component\Field\TextArea (self::PREFIX.'header_csp_value', __ ('Policy', 'fuse'), $this->option ('header_csp_value'), array (
                    'description' => __ ('Leave this reporting until the browser console is clear. An enforced policy that is wrong will stop scripts, styles and images loading.', 'fuse')
                )),
                new Component\Field\Select (self::PREFIX.'header_csp_mode', __ ('Policy mode', 'fuse'), array (
                    'report' => __ ('Report only, nothing blocked', 'fuse'),
                    'enforce' => __ ('Enforced', 'fuse')
                ), $this->option ('header_csp_mode'), array (
                    'description' => __ ('Reporting writes what would have been blocked to the browser console and blocks nothing. Only move to enforced once that console is clear.', 'fuse')
                )),
                new Component\Field\Toggle (self::PREFIX.'files', __ ('Block sensitive files', 'fuse'), $this->option ('files'), array (
                    'description' => $this->_filesDescription ()
                )),
                new Component\Field\Toggle (self::PREFIX.'indexes', __ ('Disable directory browsing', 'fuse'), $this->option ('indexes'), array (
                    'description' => __ ('Stops a folder with no index file listing what is inside it.', 'fuse')
                )),
                new Component\Field\Toggle (self::PREFIX.'uploads_php', __ ('Block PHP in the uploads folder', 'fuse'), $this->option ('uploads_php'), array (
                    'description' => $this->_uploadsDescription ()
                ))
            );
        } // _serverFields ()




        /**
         *  What to say about the server rules, given this server.
         *
         *  A field cannot be greyed out from the settings themselves, so what
         *  a badge beside the control would have said is said in the field's
         *  description instead.
         *
         *  @return string The description.
         */
        protected function _serverDescription () {
            $environment = Security\Environment::getInstance ();
            $reason = $environment->blockedReason ();

            /**
             *  The opening line only holds where a block is actually written.
             *  Where it is not, the reason already names the server, so
             *  leading with it said the same thing twice and promised a file
             *  that is never touched.
             */
            if ($reason !== '') {
                $description = $reason;
                $config = $this->serverConfig ();

                if ($config !== '') {
                    $description .= '<br /><br /><code class="fuse-security-config">'.
                        nl2br (esc_html ($config)).'</code>';
                } // if ()

                return $description;
            } // if ()

            $description = sprintf (
                /* translators: %s: the web server name. */
                __ ('Adds a block to the .htaccess file. This site runs on %s.', 'fuse'),
                $environment->serverName ()
            );

            return $description.' '.__ ('The rules are rewritten whenever these settings are saved, and taken out again when this is switched off.', 'fuse');
        } // _serverDescription ()

        /**
         *  The server configuration to hand over, for a server that cannot be
         *  written to from here.
         *
         *  Only nginx, because that is the only other syntax generated. The
         *  settings still record what is wanted, so the block reflects the
         *  switches above it and changes as they do.
         *
         *  @return string The configuration, or '' when there is none to give.
         */
        protected function serverConfig () {
            if (Security\Environment::getInstance ()->serverType () != 'nginx') {
                return '';
            } // if ()

            return Security\Rules::getInstance ()->nginx ($this->settings ());
        } // serverConfig ()

        /**
         *  What to say about XML-RPC, given what is installed.
         *
         *  @return string The description.
         */
        protected function _xmlrpcDescription () {
            $description = __ ('Closes the endpoint used for remote publishing and pingbacks, which is a common target for password guessing.', 'fuse');
            $needs = Security\Environment::getInstance ()->needsXmlrpc ();

            if ($needs !== '') {
                return $description.' '.sprintf (
                    /* translators: %s: the name of a plugin that needs XML-RPC. */
                    __ ('%s is active on this site and uses XML-RPC, so switching this on may stop it working.', 'fuse'),
                    $needs
                );
            } // if ()

            return $description;
        } // _xmlrpcDescription ()

        /**
         *  What to say about HSTS, given whether the site is on HTTPS.
         *
         *  @return string The description.
         */
        protected function _hstsDescription () {
            $description = __ ('Tells browsers to only ever reach this site over HTTPS.', 'fuse');

            if (Security\Environment::getInstance ()->isSecure () === false) {
                return $description.' '.__ ('This site is not being served over HTTPS. Do not switch this on until it is, or browsers will refuse to load it.', 'fuse');
            } // if ()

            return $description;
        } // _hstsDescription ()

        /**
         *  What to say about the blocked files.
         *
         *  @return string The description.
         */
        protected function _filesDescription () {
            return __ ('Refuses to serve the files that give away what a site is running or hold its credentials, such as readme.html, .env, debug.log and any copy of wp-config.', 'fuse');
        } // _filesDescription ()

        /**
         *  What to say about blocking PHP in uploads.
         *
         *  @return string The description.
         */
        protected function _uploadsDescription () {
            $description = __ ('Refuses to run PHP found under the uploads folder. An upload that slips past validation is only dangerous if the server will execute it, and nothing legitimate in uploads is ever meant to run.', 'fuse');

            $path = Security\Rules::getInstance ()->uploadsPath ();

            if ($path === '') {
                return $description.' '.__ ('The uploads folder on this site is not inside the WordPress directory, so it cannot be covered by a rule in the root .htaccess. It needs a rule where it actually lives.', 'fuse');
            } // if ()

            return $description.' '.sprintf (
                /* translators: %s: the uploads path, relative to the site root. */
                __ ('Covers %s and everything under it.', 'fuse'),
                $path
            );
        } // _uploadsDescription ()




        /**
         *  Every setting, with the value it starts life as.
         *
         *  Everything is off to begin with. This framework updates itself
         *  across live sites, and a release that quietly started blocking
         *  requests would be found out by a broken site rather than by being
         *  read about here. The sub-settings carry sensible values so that
         *  switching a group on gives a working set straight away.
         *
         *  @return array The setting names and their defaults.
         */
        public function defaults () {
            return apply_filters ('fuse_security_defaults', array (
                // Applied in PHP; these work on any server.
                'xmlrpc' => 'no',
                'rest_users' => 'no',
                'author_enum' => 'no',
                'version' => 'no',
                'version_assets' => 'no',
                'file_edit' => 'no',

                // Written to .htaccess; Apache and LiteSpeed only.
                'htaccess' => 'no',
                'headers' => 'no',
                'header_xfo' => 'yes',
                'header_xfo_value' => 'SAMEORIGIN',
                'header_xcto' => 'yes',
                'header_referrer' => 'yes',
                'header_referrer_value' => 'strict-origin-when-cross-origin',
                'header_permissions' => 'yes',
                'header_permissions_value' => 'geolocation=(), camera=(), microphone=(), payment=(), usb=(), interest-cohort=()',
                'header_hsts' => 'no',
                'header_hsts_maxage' => '31536000',
                'header_hsts_subdomains' => 'no',
                'header_hsts_preload' => 'no',
                'header_csp' => 'no',
                'header_csp_mode' => 'report',
                'header_csp_value' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https://fonts.gstatic.com; connect-src 'self'; frame-ancestors 'self';",
                'files' => 'no',
                'indexes' => 'no',
                'uploads_php' => 'no'
            ));
        } // defaults ()

        /**
         *  Read one of our settings.
         *
         *  @param string $name The setting name, without the prefix.
         *
         *  @return string The stored value, or its default.
         */
        public function option ($name) {
            $defaults = $this->defaults ();
            $default = array_key_exists ($name, $defaults) ? $defaults [$name] : '';

            /**
             *  Normally get_fuse_option (), but not always: on activation the
             *  plugin is included after after_setup_theme has already run, so
             *  the function files were never loaded and the helper does not
             *  exist. It is only a wrapper around get_option () with the
             *  framework's prefix, so read the option directly when it is not
             *  there rather than fataling.
             */
            if (function_exists ('get_fuse_option') === true) {
                return get_fuse_option (self::PREFIX.$name, $default);
            } // if ()

            return get_option ('fuse_setting_'.self::PREFIX.$name, $default);
        } // option ()

        /**
         *  Read every setting at once, for the rule builder.
         *
         *  @return array The setting names and their current values.
         */
        public function settings () {
            $settings = array ();

            foreach (array_keys ($this->defaults ()) as $name) {
                $settings [$name] = $this->option ($name);
            } // foreach ()

            return $settings;
        } // settings ()

        /**
         *  Is a setting switched on?
         *
         *  @param string $name The setting name, without the prefix.
         *
         *  @return bool True when it is on.
         */
        public function isOn ($name) {
            return $this->option ($name) == 'yes';
        } // isOn ()




        /**
         *  Apply the protections that run in PHP.
         *
         *  These need no help from the server, so they work everywhere.
         */
        public function protect () {
            /**
             *  Read before anything below defines it, so the panel can tell
             *  wp-config.php having set it from this setting having set it.
             */
            $this->_file_edit_external = defined ('DISALLOW_FILE_EDIT');

            if ($this->isOn ('file_edit') === true) {
                $this->stopFileEditor ();
            } // if ()

            if ($this->isOn ('xmlrpc') === true) {
                $this->stopXmlrpc ();
            } // if ()

            if ($this->isOn ('rest_users') === true) {
                add_filter ('rest_endpoints', array ($this, 'hideUserEndpoints'));
            } // if ()

            if ($this->isOn ('author_enum') === true) {
                add_action ('parse_request', array ($this, 'stopAuthorEnumeration'));
                add_filter ('wp_sitemaps_add_provider', array ($this, 'hideUserSitemap'), 10, 2);
            } // if ()

            if ($this->isOn ('version') === true) {
                remove_action ('wp_head', 'wp_generator');
                add_filter ('the_generator', '__return_empty_string');

                if ($this->isOn ('version_assets') === true) {
                    add_filter ('style_loader_src', array ($this, 'removeAssetVersion'), 9999);
                    add_filter ('script_loader_src', array ($this, 'removeAssetVersion'), 9999);
                } // if ()
            } // if ()
        } // protect ()

        /**
         *  Turn the built-in plugin and theme file editor off.
         *
         *  The editor writes straight to disk with no revision and no backup,
         *  so one stolen administrator password becomes arbitrary PHP on the
         *  server without needing a file transfer of any kind. Almost nobody
         *  uses it deliberately; leaving it on is a foothold kept for nothing.
         *
         *  after_setup_theme is early enough. WordPress reads the constant when
         *  it maps the edit_plugins and edit_themes capabilities and when the
         *  editor screens load, both of which happen later.
         *
         *  A definition already in place wins: wp-config.php is the documented
         *  home for this, and a site that has deliberately set it false there
         *  is not overridden from a settings screen.
         */
        protected function stopFileEditor () {
            if (defined ('DISALLOW_FILE_EDIT') === true) {
                return;
            } // if ()

            define ('DISALLOW_FILE_EDIT', true);
        } // stopFileEditor ()

        /**
         *  What to say about the file editor, given how it is already set.
         *
         *  @return string The description.
         */
        protected function _fileEditDescription () {
            $description = __ ('Removes Appearance > Theme File Editor and Plugins > Plugin File Editor. The editor writes PHP straight to the server, so a stolen administrator password becomes running code without needing file access.', 'fuse');

            if ($this->_file_edit_external === true) {
                if (constant ('DISALLOW_FILE_EDIT') == true) {
                    return $description.' '.__ ('Already switched off in wp-config.php, so this setting changes nothing.', 'fuse');
                } // if ()

                return $description.' '.__ ('wp-config.php sets DISALLOW_FILE_EDIT to false, which wins. Remove it there before this setting can do anything.', 'fuse');
            } // if ()

            return $description;
        } // _fileEditDescription ()

        /**
         *  Turn XML-RPC off.
         *
         *  The endpoint is switched off and its advertisements removed, so
         *  nothing keeps pointing at a door that no longer opens.
         */
        protected function stopXmlrpc () {
            add_filter ('xmlrpc_enabled', '__return_false');
            add_filter ('xmlrpc_methods', '__return_empty_array');
            add_filter ('wp_headers', array ($this, 'removePingbackHeader'));

            remove_action ('wp_head', 'rsd_link');
        } // stopXmlrpc ()

        /**
         *  Drop the pingback header that advertises XML-RPC.
         *
         *  @param array $headers The headers being sent.
         *
         *  @return array The headers without the pingback entry.
         */
        public function removePingbackHeader ($headers) {
            if (is_array ($headers) && array_key_exists ('X-Pingback', $headers)) {
                unset ($headers ['X-Pingback']);
            } // if ()

            return $headers;
        } // removePingbackHeader ()

        /**
         *  Hide the REST user endpoints from anyone not logged in.
         *
         *  The endpoints stay for logged-in users, because the editor uses them
         *  to fill in author lists.
         *
         *  @param array $endpoints The registered REST endpoints.
         *
         *  @return array The endpoints, with the user routes removed.
         */
        public function hideUserEndpoints ($endpoints) {
            if (is_user_logged_in () === true) {
                return $endpoints;
            } // if ()

            foreach (array ('/wp/v2/users', '/wp/v2/users/(?P<id>[\d]+)') as $route) {
                if (array_key_exists ($route, $endpoints)) {
                    unset ($endpoints [$route]);
                } // if ()
            } // foreach ()

            return $endpoints;
        } // hideUserEndpoints ()

        /**
         *  Stop ?author=1 being used to list the user names.
         *
         *  @param \WP $wp The request being parsed.
         */
        public function stopAuthorEnumeration ($wp) {
            if (is_admin () === true || is_user_logged_in () === true) {
                return;
            } // if ()

            if (array_key_exists ('author', $_GET) === false) {
                return;
            } // if ()

            $wp->query_vars = array ('error' => '404');
        } // stopAuthorEnumeration ()

        /**
         *  Leave the users out of the sitemap.
         *
         *  Blocking ?author= while still listing every author in the sitemap
         *  would only move the list somewhere else.
         *
         *  @param mixed $provider The sitemap provider.
         *  @param string $name The provider name.
         *
         *  @return mixed The provider, or false to drop it.
         */
        public function hideUserSitemap ($provider, $name) {
            if ($name === 'users') {
                return false;
            } // if ()

            return $provider;
        } // hideUserSitemap ()

        /**
         *  Strip the version from an asset URL.
         *
         *  Only ours and WordPress's own version is removed. A plugin that
         *  passes its own version keeps it, because that is its cache busting
         *  and removing it would leave visitors on stale files.
         *
         *  @param string $src The asset URL.
         *
         *  @return string The URL without the WordPress version.
         */
        public function removeAssetVersion ($src) {
            if (is_string ($src) === false || strpos ($src, 'ver=') === false) {
                return $src;
            } // if ()

            global $wp_version;

            if (strpos ($src, 'ver='.$wp_version) === false) {
                return $src;
            } // if ()

            return remove_query_arg ('ver', $src);
        } // removeAssetVersion ()




        /**
         *  Write the server rules, or take them away again.
         *
         *  Called when the settings are saved and when the plugin is activated
         *  or deactivated.
         */
        public function writeRules () {
            $rules = Security\Rules::getInstance ();

            if ($this->isOn ('htaccess') === false) {
                $rules->remove ();

                return;
            } // if ()

            $rules->write ($rules->build ($this->settings ()));
        } // writeRules ()

        /**
         *  Take the server rules out.
         *
         *  Used on deactivation, so a site is never left with a block from a
         *  plugin that is no longer running.
         */
        public function removeRules () {
            Security\Rules::getInstance ()->remove ();
        } // removeRules ()

    } // class Security
