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
                return $domain_path;
            } // if ()

            return trailingslashit ($domain_path).'wp-json/fuseupdateserver/v1/data';
        } // _getServerUrl ()
        
    } // trait Update