<?php
    /**
     *  @package fusecms
     *
     *  This class works out what the server we are running on will actually let
     *  us do. The security panel asks it before offering an option, so a rule
     *  that could not work is disabled with a reason shown, rather than being
     *  written out to sit there doing nothing.
     */

    namespace Fuse\Setup\Security;

    use Fuse\Traits\Singleton;


    class Environment {

        use Singleton;

        /**
         *  @var array Cached check results, so each one is only worked out once.
         */
        protected $_checks = array ();




        /**
         *  Which web server we are running on.
         *
         *  @return string One of 'apache', 'litespeed', 'nginx', 'iis', 'unknown'.
         */
        public function serverType () {
            if (array_key_exists ('server_type', $this->_checks) === false) {
                $software = '';

                if (array_key_exists ('SERVER_SOFTWARE', $_SERVER) === true) {
                    $software = strtolower (strval ($_SERVER ['SERVER_SOFTWARE']));
                } // if ()

                $type = 'unknown';

                /**
                 *  LiteSpeed is checked before Apache because it identifies
                 *  itself as both, and we want the more specific name.
                 */
                if (strpos ($software, 'litespeed') !== false) {
                    $type = 'litespeed';
                } // if ()
                elseif (strpos ($software, 'apache') !== false) {
                    $type = 'apache';
                } // elseif ()
                elseif (strpos ($software, 'nginx') !== false) {
                    $type = 'nginx';
                } // elseif ()
                elseif (strpos ($software, 'microsoft-iis') !== false) {
                    $type = 'iis';
                } // elseif ()

                $this->_checks ['server_type'] = $type;
            } // if ()

            return $this->_checks ['server_type'];
        } // serverType ()

        /**
         *  The server name to show in the admin.
         *
         *  @return string The readable server name.
         */
        public function serverName () {
            $names = array (
                'apache' => __ ('Apache', 'fuse'),
                'litespeed' => __ ('LiteSpeed', 'fuse'),
                'nginx' => __ ('nginx', 'fuse'),
                'iis' => __ ('Microsoft IIS', 'fuse'),
                'unknown' => __ ('an unrecognised server', 'fuse')
            );

            return $names [$this->serverType ()];
        } // serverName ()

        /**
         *  Whether this server reads .htaccess files at all.
         *
         *  nginx and IIS never do, so the server-level rules cannot be applied
         *  from here on those and have to go in the server configuration.
         *
         *  @return bool TRUE when .htaccess is read.
         */
        public function readsHtaccess () {
            $type = $this->serverType ();

            return ($type == 'apache' || $type == 'litespeed');
        } // readsHtaccess ()




        /**
         *  The full path to the site's .htaccess file.
         *
         *  @return string The path, or '' when it cannot be worked out.
         */
        public function htaccessPath () {
            if (array_key_exists ('htaccess_path', $this->_checks) === false) {
                if (function_exists ('get_home_path') === false) {
                    require_once (ABSPATH.'wp-admin'.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'file.php');
                } // if ()

                $home = get_home_path ();
                $path = '';

                if (strlen ($home) > 0 && $home != '/') {
                    $path = $home.'.htaccess';
                } // if ()

                $this->_checks ['htaccess_path'] = $path;
            } // if ()

            return $this->_checks ['htaccess_path'];
        } // htaccessPath ()

        /**
         *  Whether we could actually write the .htaccess file.
         *
         *  A missing file is writable when its folder is, since we would be
         *  creating it.
         *
         *  @return bool TRUE when the file can be written.
         */
        public function htaccessWritable () {
            $path = $this->htaccessPath ();

            if (strlen ($path) == 0) {
                return false;
            } // if ()

            if (file_exists ($path) === true) {
                return is_writable ($path);
            } // if ()

            return is_writable (dirname ($path));
        } // htaccessWritable ()

        /**
         *  Whether an Apache module is loaded.
         *
         *  apache_get_modules () only exists when PHP is running as an Apache
         *  module, so a NULL answer means "we cannot tell" - not "missing".
         *  Every directive we write is wrapped in <IfModule> for exactly that
         *  reason, so an unknown answer is safe.
         *
         *  @param string $module The module name, e.g. 'mod_headers'.
         *
         *  @return bool|null TRUE, FALSE, or NULL when it cannot be determined.
         */
        public function hasModule ($module) {
            if (function_exists ('apache_get_modules') === false) {
                return NULL;
            } // if ()

            return in_array ($module, apache_get_modules (), true);
        } // hasModule ()

        /**
         *  Whether mod_rewrite is available, as WordPress sees it.
         *
         *  @return bool TRUE when rewrites are available.
         */
        public function hasRewrite () {
            if (function_exists ('got_mod_rewrite') === false) {
                require_once (ABSPATH.'wp-admin'.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'misc.php');
            } // if ()

            return (bool) got_mod_rewrite ();
        } // hasRewrite ()




        /**
         *  The headers the site actually sends, fetched from the site itself.
         *
         *  Whether a directive works cannot be told by reading the server's
         *  configuration from PHP - under FastCGI we cannot even see which
         *  modules are loaded. Asking the site for a page and looking at what
         *  comes back is the only answer that is not a guess, so that is what
         *  the panel does after it writes the rules.
         *
         *  @return array|null The header names in lower case, or NULL when the
         *                     site could not be reached to check.
         */
        public function liveHeaders () {
            $response = wp_remote_get (home_url ('/'), array (
                'timeout' => 10,
                'sslverify' => false,
                'redirection' => 2,
                'headers' => array ('Cache-Control' => 'no-cache')
            ));

            if (is_wp_error ($response) === true) {
                return NULL;
            } // if ()

            $headers = wp_remote_retrieve_headers ($response);

            if (is_object ($headers) === true && method_exists ($headers, 'getAll') === true) {
                $headers = $headers->getAll ();
            } // if ()

            if (is_array ($headers) === false) {
                return NULL;
            } // if ()

            return array_map ('strtolower', array_keys ($headers));
        } // liveHeaders ()

        /**
         *  Which of the headers we asked for are not actually being sent.
         *
         *  @param array $expected The header names that should be present.
         *
         *  @return array|null The missing names, or NULL when it could not be
         *                     checked.
         */
        public function missingHeaders ($expected) {
            if (count ($expected) == 0) {
                return array ();
            } // if ()

            $live = $this->liveHeaders ();

            if ($live === NULL) {
                return NULL;
            } // if ()

            $missing = array ();

            foreach ($expected as $name) {
                if (in_array (strtolower ($name), $live, true) === false) {
                    $missing [] = $name;
                } // if ()
            } // foreach ()

            return $missing;
        } // missingHeaders ()

        /**
         *  Whether the site is being served over HTTPS.
         *
         *  Both the current request and the saved home address are checked: a
         *  site can be reached over HTTPS while still being configured as
         *  plain HTTP, and turning HSTS on in that state locks visitors out.
         *
         *  @return bool TRUE when the site is fully on HTTPS.
         */
        public function isSecure () {
            return (is_ssl () === true && strpos (strtolower (get_option ('home', '')), 'https://') === 0);
        } // isSecure ()

        /**
         *  The name of any full page caching in use.
         *
         *  Cached pages are served by the web server without PHP running, so
         *  headers sent from PHP never reach them - which is the argument for
         *  writing the headers into .htaccess instead.
         *
         *  @return string The cache plugin name, or '' when none was found.
         */
        public function pageCache () {
            if (defined ('WP_ROCKET_VERSION') === true) {
                return __ ('WP Rocket', 'fuse');
            } // if ()

            if (defined ('LSCWP_V') === true) {
                return __ ('LiteSpeed Cache', 'fuse');
            } // if ()

            if (defined ('W3TC_VERSION') === true) {
                return __ ('W3 Total Cache', 'fuse');
            } // if ()

            if (defined ('WPCACHEHOME') === true) {
                return __ ('WP Super Cache', 'fuse');
            } // if ()

            return '';
        } // pageCache ()

        /**
         *  The name of anything installed that needs XML-RPC to work.
         *
         *  @return string The plugin name, or '' when none was found.
         */
        public function needsXmlrpc () {
            if (defined ('JETPACK__VERSION') === true) {
                return __ ('Jetpack', 'fuse');
            } // if ()

            return '';
        } // needsXmlrpc ()

        /**
         *  Whether this is a multisite network.
         *
         *  The panel is single-site only: a network shares one .htaccess and
         *  its rewrite rules differ, so the server rules are not offered.
         *
         *  @return bool TRUE on a network install.
         */
        public function isNetwork () {
            return is_multisite ();
        } // isNetwork ()




        /**
         *  Whether the server rules can be written at all on this site.
         *
         *  @return bool TRUE when the .htaccess options may be switched on.
         */
        public function canWriteRules () {
            return ($this->readsHtaccess () === true && $this->isNetwork () === false && $this->htaccessWritable () === true);
        } // canWriteRules ()

        /**
         *  Why the server rules cannot be written, for showing in the panel.
         *
         *  @return string The reason, or '' when they can be written.
         */
        public function blockedReason () {
            if ($this->isNetwork () === true) {
                return __ ('This is a multisite network. The server rules are for single sites only and must be added to the server configuration by hand.', 'fuse');
            } // if ()

            if ($this->readsHtaccess () === false) {
                return sprintf (
                    /* translators: %s: the web server name, e.g. nginx. */
                    __ ('This site runs on %s, which does not read .htaccess files. The rules below have to be added to the server configuration instead - the block underneath can be copied straight across.', 'fuse'),
                    $this->serverName ()
                );
            } // if ()

            if ($this->htaccessWritable () === false) {
                return __ ('The .htaccess file cannot be written. Correct its permissions, or copy the block underneath into it by hand.', 'fuse');
            } // if ()

            return '';
        } // blockedReason ()

    } // class Environment