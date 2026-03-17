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
            return trailingslashit ($domain_path).'wp-json/fuseupdateserver/v1/data';
        } // _getServerUrl ()
        
    } // trait Update