<?php
    /**
     *  @package fusecms
     *  @version 2.0
     *
     *  Plugin Name: Fuse CMS Framework for WordPress
     *  Plugin URI: https://fusecms.org
     *  Description: This is the Fuse CMS Framework
     *  Author: 7-90 Systems
     *  Author URI: https://7-90.com.au
     *  Version: 2.0
     *  Requires at least: 6.0
     *  Requires PHP: 7.4
     *  Text Domain: fuse
     *  Fuse Update Server: http://fusecms.org
     */
    
    namespace Fuse;
    
    define ('FUSE_BASE_URI', __DIR__);
    define ('FUSE_BASE_URL', plugins_url ('', __FILE__));
    
    /**
     *  Start up our class auto-loader.
     */
    require_once (FUSE_BASE_URI.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'Traits'.DIRECTORY_SEPARATOR.'Singleton.php');
    require_once (FUSE_BASE_URI.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'Loader.php');
    
    $fuse_loader = Loader::getInstance ();
    
    $fuse_setup = Setup::getInstance ();
    
    
    
    
    /**
     *  Set up our installation functions
     */
    register_activation_hook (__FILE__, '\Fuse\fuse_cms_framework_install');
    
    /**
     *  Set up installation.
     */
    function fuse_cms_framework_install () {
        $install = new Install ();
    } // fuse_cms_framework_install ()