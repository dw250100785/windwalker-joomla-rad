<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2012 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Generated by AKHelper - http://asikart.com
 */


// No direct access
defined('_JEXEC') or die;

/**
 * elFinder Connector & Displayer.
 *
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 */
class AKHelperElfinder
{
    /**
     * display
     */
    public static function display($option = null)
    {
        // Init some API objects
        // ================================================================================
        $date   = JFactory::getDate( 'now' , JFactory::getConfig()->get('offset') ) ;
        $doc    = JFactory::getDocument() ;
        $uri    = JFactory::getURI() ;
        $user   = JFactory::getUser() ;
        $app    = JFactory::getApplication() ;
        $lang   = JFactory::getLanguage();
        $lang_code = $lang->getTag();
        $lang_code = str_replace('-', '_', $lang_code) ;    
        
        // Include elFinder and JS
        // ================================================================================
        JHtml::_('behavior.framework', true);
        
        if( JVERSION >= 3){
                
            // jQuery
            JHtml::_('jquery.framework', true);
            JHtml::_('bootstrap.framework', true);
        
        }else{
            $doc->addStyleSheet('components/com_remoteimage/includes/bootstrap/css/bootstrap.min.css');
            
            // jQuery
            AKHelper::_('include.addJS', 'jquery/jquery.js', 'ww') ;
            $doc->addScriptDeclaration('jQuery.noConflict();');
        }
        
        $assets_url = AKHelper::_('path.getWWUrl').'/assets' ;
        
        // elFinder includes
        $doc->addStylesheet( $assets_url.'/js/jquery-ui/css/smoothness/jquery-ui-1.8.24.custom.css' );
        $doc->addStylesheet( $assets_url.'/js/elfinder/css/elfinder.min.css' );
        $doc->addStylesheet( $assets_url.'/js/elfinder/css/theme.css' );
        
        $doc->addscript( $assets_url.'/js/jquery-ui/js/jquery-ui.min.js' );
        $doc->addscript( $assets_url.'/js/elfinder/js/elfinder.min.js' );
        JHtml::script( $assets_url.'/js/elfinder/js/i18n/elfinder.'.$lang_code.'.js' );
        AKHelper::_('include.core');
        
        $option = $option ? $option : JRequest::getVar('option') ;
        $finder_id = JRequest::getVar('finder_id') ;
        
		$modal  = ( JRequest::getVar('tmpl') == 'component' ) ? true : false ;
        
        $getFileCallback = !$modal ? '' : "
        ,
        getFileCallback : function(file){
            if (window.parent) window.parent.AKFinderSelect_{$finder_id}(AKFinderSelected, window.elFinder);
        }"; 
        
        $script = <<<SCRIPT
		var AKFinderSelected ;
		
		// Init elFinder
        jQuery(document).ready(function($) {
            elFinder = $('#elfinder').elfinder({
                url : 'index.php?option={$option}&task=elFinderConnector' ,
                width : '100%' ,
                lang : '{$lang_code}',
                handlers : {
                    select : function(event, elfinderInstance) {
                        var selected = event.data.selected;

                        if (selected.length) {
                            AKFinderSelected = [];
                            jQuery.each(selected, function(i, e){
                                    AKFinderSelected[i] = elfinderInstance.file(e);
                            });
                        }

                    }
                }
                
                {$getFileCallback}
                
            }).elfinder('instance');
        }); 
SCRIPT;

        $doc->addScriptDeclaration($script);
        
        echo '<div class="row-fluid">
                <div id="elfinder" class="span12 rm-finder">
                        
                </div>
            </div>' ;
    }
    
    /**
     * connector
     */
    public static function connector($option = null)
    {
        error_reporting(0); // Set E_ALL for debuging
		
		$elfinder_path = AKPATH_ASSETS.'/js/elfinder/php/' ;
		
		include_once $elfinder_path.'elFinderConnector.class.php';
		include_once $elfinder_path.'elFinder.class.php';
		include_once $elfinder_path.'elFinderVolumeDriver.class.php';
		include_once $elfinder_path.'elFinderVolumeLocalFileSystem.class.php';
		// Required for MySQL storage connector
		// include_once $elfinder_path.'elFinderVolumeMySQL.class.php';
		// Required for FTP connector support
		// include_once $elfinder_path.'elFinderVolumeFTP.class.php';
		// Required for Dropbox.com connector support
		// include_once $elfinder_path.'elFinderVolumeDropbox.class.php';
		// # Dropbox volume driver need "dropbox-php's Dropbox" and "PHP OAuth extension" or "PEAR's HTTP_OAUTH package"
		// * dropbox-php: http://www.dropbox-php.com/
		// * PHP OAuth extension: http://pecl.php.net/package/oauth
		// * PEAR’s HTTP_OAUTH package: http://pear.php.net/package/http_oauth
		//  * HTTP_OAUTH package require HTTP_Request2 and Net_URL2
		// Dropbox driver need next two settings. You can get at https://www.dropbox.com/developers
		// define('ELFINDER_DROPBOX_CONSUMERKEY',    '');
		// define('ELFINDER_DROPBOX_CONSUMERSECRET', '');
		
		
		/**
		 * Simple function to demonstrate how to control file access using "accessControl" callback.
		 * This method will disable accessing files/folders starting from '.' (dot)
		 *
		 * @param  string  $attr  attribute name (read|write|locked|hidden)
		 * @param  string  $path  file path relative to volume root directory started with directory separator
		 * @return bool|null
		 **/
		function access($attr, $path, $data, $volume) {
			return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
				? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
				:  null;                                    // else elFinder decide it itself
		}
		
		
		// Documentation for connector options:
		// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
		
		$option = $option ? $option : JRequest::getVar('option') ;
		
		$opts = array(
			// 'debug' => true,
			'roots' => array(
				array(
					'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
					'path'          => JPATH_ROOT,         // path to files (REQUIRED)
					'URL'           =>  JURI::root(), // URL to files (REQUIRED)
					'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
				)
			)
		);
		
		// run elFinder
		$connector = new elFinderConnector(new elFinder($opts));
		$connector->run();
    }
}