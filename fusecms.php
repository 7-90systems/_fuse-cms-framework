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
     *  License: GPL-3.0-or-later
     *  License URI: https://www.gnu.org/licenses/gpl-3.0.html
     *  Text Domain: fuse
     *  Fuse Update Server: https://fusecms.org
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

        // Write the security rules, if they have been switched on.
        Setup\Security::getInstance ()->writeRules ();
    } // fuse_cms_framework_install ()




    /**
     *  Tidy up on deactivation.
     *
     *  The .htaccess block has to come out. Leaving it behind would keep a site
     *  applying rules from a plugin that is no longer running, and whoever
     *  found it next would have nothing to switch it off with.
     */
    register_deactivation_hook (__FILE__, '\Fuse\fuse_cms_framework_deactivate');

    function fuse_cms_framework_deactivate () {
        Setup\Security::getInstance ()->removeRules ();
    } // fuse_cms_framework_deactivate ()