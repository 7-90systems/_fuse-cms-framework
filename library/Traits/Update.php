<?php
    /**
     *@pacakge fuse-cms
     *
     *  This trait ensures that we use the correct enpoints for our update systems.
     */
    
    namespace Fuse\Traits;
    
    
    trait Update {
        
        /**
         *  @var string This is the update server URL
         */
        protected $_update_server_url;
        
        
        
        
        /**
         *  GEt the full update server URL.
         */
        protected function _getServerUrl ($domain_path) {
            // A Fuse Update Server can be a stand-alone endpoint (a full URL,
            // possibly with a query string) or a WordPress site (where we append
            // the REST route). If the configured server already points at a
            // specific endpoint, use it as-is.
            if (substr ($domain_path, -4) === '.php' || strpos ($domain_path, '?') !== false) {
                return $this->_forceSecureScheme ($domain_path);
            } // if ()
            
            return $this->_forceSecureScheme (trailingslashit ($domain_path).'wp-json/fuseupdateserver/v1/data');
        } // _getServerUrl ()
        
        
        
        
        /**
         *  Force an update server URL onto HTTPS.
         *
         *  The update server tells us which package to download and where from,
         *  so anyone able to sit in the middle of a plain HTTP exchange can
         *  hand the site any code they like. Plugin headers still commonly give
         *  the server as http://, so the scheme is upgraded here rather than
         *  trusted as configured. A server with no HTTPS will now fail its
         *  update check, which is the safe way for this to break.
         *
         *  The only exception is a loopback address, which cannot be reached
         *  off the machine and is how a Fuse Update Server is run locally. That
         *  is matched on the parsed host, not on the start of the string -- a
         *  prefix test would also let through a real host such as
         *  localhost.example.com.
         *
         *  @param string $url The update server URL.
         *
         *  @return string The URL on an HTTPS scheme.
         */
        protected function _forceSecureScheme ($url) {
            $host = parse_url ($url, PHP_URL_HOST);
            
            if (is_string ($host) && in_array (strtolower ($host), $this->_getLoopbackHosts (), true)) {
                return $url;
            } // if ()
            
            return set_url_scheme ($url, 'https');
        } // _forceSecureScheme ()
        
        
        
        
        /**
         *  The hosts that are allowed to stay on plain HTTP.
         *
         *  @return array The loopback host names.
         */
        protected function _getLoopbackHosts () {
            return array (
                'localhost',
                '127.0.0.1',
                '::1',
                '[::1]'
            );
        } // _getLoopbackHosts ()
        
    } // trait Update