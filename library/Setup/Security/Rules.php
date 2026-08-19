<?php
    /**
     *  @package fusecms
     *
     *  This class builds the server rules for the security panel and writes
     *  them into .htaccess, using WordPress's own marker system so our block
     *  sits alongside the one WordPress manages without either disturbing the
     *  other.
     *
     *  Every directive is wrapped in <IfModule> so that a server without the
     *  module ignores it rather than failing the whole site with a 500.
     */

    namespace Fuse\Setup\Security;

    use Fuse\Traits\Singleton;


    class Rules {

        use Singleton;

        /**
         *  @var string The marker WordPress writes our block between.
         */
        const MARKER = 'Fuse Security';

        /**
         *  @var array Files never served, matched by their exact name.
         */
        const BLOCKED_FILES = array (
            'xmlrpc.php',
            'readme.html',
            'license.txt',
            'debug.log',
            '.env'
        );

        /**
         *  @var array Names never served, along with anything appended to them.
         *
         *  A configuration file copied aside before an edit keeps everything
         *  that was in it and stops being run as PHP, so wp-config.php.bak is
         *  the database password in plain text to anyone who asks for it. What
         *  the copy ends up called is not ours to know - the editor, the host,
         *  the control panel and whoever was last in there all have their own
         *  habits, and .bak, .old, .save, .orig, ~ and .1 are only the ones we
         *  have seen. Listing them one at a time is a list that is wrong the
         *  moment something new turns up, so the name is matched with whatever
         *  follows it instead.
         *
         *  'wp-config' is deliberately shorter than the file name, because the
         *  copy is as likely to be wp-config-backup.php or wp-config.bak.php as
         *  it is to have the suffix on the end. Nothing else in a WordPress
         *  install begins that way apart from wp-config-sample.php, which has
         *  no reason to be served either. A theme's my-wp-config.php is not
         *  caught: the name has to start this way, not merely contain it.
         */
        const BLOCKED_PREFIXES = array (
            'wp-config',
            '.htaccess',
            '.htpasswd',
            '.user.ini'
        );




        /**
         *  Make a value safe to sit inside a quoted directive.
         *
         *  Header values are typed by hand in the panel and end up in a server
         *  configuration file, so anything that could close the quotes or start
         *  a directive of its own is taken out rather than escaped. Line breaks
         *  go first: without that, one field could add any directive it liked.
         *
         *  @param string $value The submitted value.
         *
         *  @return string The value, safe to write.
         */
        public static function safeValue ($value) {
            if (is_scalar ($value) === false) {
                return '';
            } // if ()

            $value = strval ($value);

            // Quotes, backslashes and anything that ends a line are removed.
            $value = str_replace (array ('"', '\\', "\r", "\n"), ' ', $value);

            // Any remaining control characters go too.
            $value = preg_replace ('/[\x00-\x1F\x7F]/', ' ', $value);

            return trim (preg_replace ('/\s+/', ' ', $value));
        } // safeValue ()




        /**
         *  Build the .htaccess rules from the saved settings.
         *
         *  @param array $settings The security settings.
         *
         *  @return array The lines to write, empty when nothing is switched on.
         */
        public function build ($settings) {
            $lines = array_merge (
                $this->headerLines ($settings),
                $this->fileLines ($settings),
                $this->indexLines ($settings)
            );

            return $lines;
        } // build ()

        /**
         *  Work out the headers to send, and what to send in them.
         *
         *  Both the Apache and the nginx rules are built from this, so the two
         *  cannot drift apart.
         *
         *  @param array $settings The security settings.
         *  @param bool  $on_https Whether to hold HSTS back until the site is
         *                         on HTTPS. Left off for the nginx version,
         *                         which is written to be pasted somewhere else.
         *
         *  @return array The header names and their values.
         */
        public function headerSet ($settings, $on_https = true) {
            if ($settings ['headers'] != 'yes') {
                return array ();
            } // if ()

            $headers = array ();

            if ($settings ['header_xfo'] == 'yes') {
                $headers ['X-Frame-Options'] = $settings ['header_xfo_value'];
            } // if ()

            if ($settings ['header_xcto'] == 'yes') {
                $headers ['X-Content-Type-Options'] = 'nosniff';
            } // if ()

            if ($settings ['header_referrer'] == 'yes') {
                $headers ['Referrer-Policy'] = $settings ['header_referrer_value'];
            } // if ()

            if ($settings ['header_permissions'] == 'yes') {
                $headers ['Permissions-Policy'] = $settings ['header_permissions_value'];
            } // if ()

            /**
             *  HSTS is only ever written for a site already on HTTPS. Sending
             *  it from a site that is not tells every browser to refuse the
             *  plain HTTP version for as long as the age says, and that cannot
             *  be called back.
             */
            if ($settings ['header_hsts'] == 'yes' && ($on_https === false || Environment::getInstance ()->isSecure () === true)) {
                $hsts = 'max-age='.absint ($settings ['header_hsts_maxage']);

                if ($settings ['header_hsts_subdomains'] == 'yes') {
                    $hsts .= '; includeSubDomains';
                } // if ()

                if ($settings ['header_hsts_preload'] == 'yes') {
                    $hsts .= '; preload';
                } // if ()

                $headers ['Strict-Transport-Security'] = $hsts;
            } // if ()

            if ($settings ['header_csp'] == 'yes' && strlen (self::safeValue ($settings ['header_csp_value'])) > 0) {
                $name = 'Content-Security-Policy';

                if ($settings ['header_csp_mode'] == 'report') {
                    $name = 'Content-Security-Policy-Report-Only';
                } // if ()

                $headers [$name] = $settings ['header_csp_value'];
            } // if ()

            return $headers;
        } // headerSet ()

        /**
         *  The header directives.
         *
         *  @param array $settings The security settings.
         *
         *  @return array The lines, empty when no headers are switched on.
         */
        protected function headerLines ($settings) {
            $headers = $this->headerSet ($settings);

            if (count ($headers) == 0) {
                return array ();
            } // if ()

            $lines = array (
                '# Security headers.',
                '<IfModule mod_headers.c>'
            );

            foreach ($headers as $name => $value) {
                $lines [] = '    Header always set '.$name.' "'.self::safeValue ($value).'"';
            } // foreach ()

            $lines [] = '</IfModule>';
            $lines [] = '';

            return $lines;
        } // headerLines ()

        /**
         *  The pattern matching every file we refuse to serve.
         *
         *  Both the Apache and the nginx rules are built from this, so the two
         *  cannot come to mean different things. Exact names are matched as
         *  they are written; the prefixes are followed by .*, which also
         *  matches nothing at all, so the name itself is still caught.
         *
         *  Apache tests this against the file name alone, so a file blocked
         *  here is blocked in every folder, not just the site root.
         *
         *  @return string The alternation, to sit inside ^( )$.
         */
        public function filePattern () {
            $names = array ();

            foreach (self::BLOCKED_FILES as $file) {
                $names [] = preg_quote ($file, '"');
            } // foreach ()

            foreach (self::BLOCKED_PREFIXES as $file) {
                $names [] = preg_quote ($file, '"').'.*';
            } // foreach ()

            return implode ('|', $names);
        } // filePattern ()

        /**
         *  The directives blocking files that should never be served.
         *
         *  Both the 2.4 and the 2.2 way of denying access are written, each
         *  behind its own module test, so the block works whichever the server
         *  happens to be running.
         *
         *  @param array $settings The security settings.
         *
         *  @return array The lines, empty when file blocking is switched off.
         */
        protected function fileLines ($settings) {
            if ($settings ['files'] != 'yes') {
                return array ();
            } // if ()

            $lines = array (
                '# Files that should never be served.',
                '<FilesMatch "^('.$this->filePattern ().')$">',
                '    <IfModule mod_authz_core.c>',
                '        Require all denied',
                '    </IfModule>',
                '    <IfModule !mod_authz_core.c>',
                '        Order allow,deny',
                '        Deny from all',
                '    </IfModule>',
                '</FilesMatch>',
                '',
                '# Version control and environment folders.',
                '<IfModule mod_alias.c>',
                '    RedirectMatch 404 (?i)/\.(git|svn|hg|bzr|env)(/|$)',
                '</IfModule>',
                ''
            );

            return $lines;
        } // fileLines ()

        /**
         *  The directive turning directory browsing off.
         *
         *  @param array $settings The security settings.
         *
         *  @return array The lines, empty when it is switched off.
         */
        protected function indexLines ($settings) {
            if ($settings ['indexes'] != 'yes') {
                return array ();
            } // if ()

            return array (
                '# No directory listings.',
                'Options -Indexes',
                ''
            );
        } // indexLines ()




        /**
         *  Write our block into .htaccess.
         *
         *  @param array $lines The lines to write.
         *
         *  @return bool TRUE when the file was written.
         */
        public function write ($lines) {
            $environment = Environment::getInstance ();

            if ($environment->canWriteRules () === false) {
                return false;
            } // if ()

            if (function_exists ('insert_with_markers') === false) {
                require_once (ABSPATH.'wp-admin'.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'misc.php');
            } // if ()

            return (bool) insert_with_markers ($environment->htaccessPath (), self::MARKER, $lines);
        } // write ()

        /**
         *  Take our block back out of .htaccess, markers and all.
         *
         *  Handing insert_with_markers () an empty set of lines leaves the
         *  markers themselves sitting in the file, which is a puzzle for
         *  whoever opens it next and finds a Fuse block in a site that no
         *  longer runs the plugin. The block is taken out whole instead.
         *
         *  @return bool TRUE when there is no longer a block in the file.
         */
        public function remove () {
            $path = Environment::getInstance ()->htaccessPath ();

            if (strlen ($path) == 0 || file_exists ($path) === false) {
                return true;
            } // if ()

            $contents = file_get_contents ($path);

            if ($contents === false) {
                return false;
            } // if ()

            $begin = '# BEGIN '.self::MARKER;
            $end = '# END '.self::MARKER;

            if (strpos ($contents, $begin) === false) {
                return true;
            } // if ()

            if (is_writable ($path) === false) {
                return false;
            } // if ()

            /**
             *  The file keeps the line endings it already had. WordPress writes
             *  this file with newlines whatever the platform, and rewriting
             *  every line of a site's .htaccess to suit us would turn a rule
             *  removal into a change to the whole file.
             */
            $break = (strpos ($contents, "\r\n") !== false) ? "\r\n" : "\n";

            $lines = preg_split ('/\r\n|\r|\n/', $contents);
            $kept = array ();
            $inside = false;

            foreach ($lines as $line) {
                if (trim ($line) === $begin) {
                    $inside = true;

                    continue;
                } // if ()

                if ($inside === true) {
                    if (trim ($line) === $end) {
                        $inside = false;
                    } // if ()

                    continue;
                } // if ()

                $kept [] = $line;
            } // foreach ()

            // Any blank lines our block left behind at the end go with it.
            while (count ($kept) > 0 && trim ($kept [count ($kept) - 1]) === '') {
                array_pop ($kept);
            } // while ()

            return (file_put_contents ($path, implode ($break, $kept), LOCK_EX) !== false);
        } // remove ()

        /**
         *  The block as it would appear in the file, for showing on screen.
         *
         *  @param array $lines The built lines.
         *
         *  @return string The block, or '' when there is nothing to show.
         */
        public function preview ($lines) {
            if (count ($lines) == 0) {
                return '';
            } // if ()

            $block = array_merge (
                array ('# BEGIN '.self::MARKER),
                $lines,
                array ('# END '.self::MARKER)
            );

            return implode (PHP_EOL, $block);
        } // preview ()




        /**
         *  The same rules written for nginx.
         *
         *  nginx never reads .htaccess, so a site on it needs these pasted into
         *  its server block by whoever looks after the server. Giving them the
         *  right syntax is the difference between the rules going on and the
         *  job being put off.
         *
         *  @param array $settings The security settings.
         *
         *  @return string The nginx configuration, or '' when nothing is on.
         */
        public function nginx ($settings) {
            $lines = array ();
            $headers = $this->headerSet ($settings, false);

            foreach ($headers as $name => $value) {
                $lines [] = 'add_header '.$name.' "'.self::safeValue ($value).'" always;';
            } // foreach ()

            if (count ($headers) > 0) {
                $lines [] = '';
            } // if ()

            if ($settings ['files'] == 'yes') {
                /**
                 *  Matched anywhere in the path rather than only at the root,
                 *  since Apache tests the file name on its own and would catch
                 *  wp-content/debug.log where an anchored nginx pattern would
                 *  let it through.
                 */
                $lines [] = 'location ~* /('.$this->filePattern ().')$ { deny all; }';
                $lines [] = 'location ~ /\.(git|svn|hg|bzr|env) { deny all; }';
                $lines [] = '';
            } // if ()

            if ($settings ['indexes'] == 'yes') {
                $lines [] = 'autoindex off;';
            } // if ()

            return trim (implode (PHP_EOL, $lines));
        } // nginx ()

    } // class Rules