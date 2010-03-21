<?php

/**
 * MyHammer LargeXMLSitemap
 * 
 * This source file is subject to the GNU General Public License Version 3
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://www.myhammer.de/opensource/license/gpl.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to opensource@myhammer.de so we can send you a copy immediately.
 *
 * @category   MyHammer
 * @package    LargeXMLSitemap
 * @subpackage Config
 * @license    http://www.myhammer.de/opensource/license/gpl.txt GNU General Public License Version 3
 * @version    1.0
 * @author     Jan Christiansen <christiansen@myhammer.de>
 */

/**
 * Class for holding configuration information
 * Thats the place where you can put your own config things.
 * This class is completely optional and not used inside the other classes
 * so there is no need for you to use it.
 * i just wanted to create a single place for config things for all scripts that create sitmaps
 * 
 * @package LargeXMLSitemap
 * @subpackage Config
 */
class cXmlSitemapConfig {

	/**
	 * compress the sitemaps and sitemap index files
	 * should be true on livesystem
	 * to debug its usefull to set it false
	 */
	const bCompress = TRUE;
	
	/**
	 * cache for the sitenames
	 * 
	 * @var array
	 */
	private static $aSitenames = array();
	
	/**
	 * path where the sitemaps are stored
	 */
	const sSitemapDirectory = "./";
	
	/**
	 * Prefix for all content (but not index) sitemap files
	 * 
	 * @var string
	 */
	private static $sSitemapFilePrefix = 'sitemap';
	
	/**
	 * the default filename for index without special filename
	 * 
	 * @var string
	 */
	private static $sSitemapIndexFile = 'index_sitemaps';
	
	/**
	 * current self object
	 * @var cXmlSitemapConfig
	 */
	private static $oCurrentSingletonObject = null;
	
	/**
	 * load some configuration from DB or a File.
	 * protected because this should be a singleton
	 * 
	 * @access protected
	 * @return void
	 */
	protected function __construct() {}
	
	/**
	 * initializes the config, create singleton object
	 * 
	 * @static
	 * @return cXmlSitemapConfig
	 */
	public static function loadConfig() {
		if (self::$oCurrentSingletonObject == null) {
			self::$oCurrentSingletonObject = new self;
		}
		return self::$oCurrentSingletonObject;
	}
	
	/**
	 * returns the path where the sitemaps are generated
	 * 
	 * @static
	 * @return string
	 */
	public static function getSitemapDirectoryPath() {
		return self::sSitemapDirectory;
	}
	
	/**
	 * returns the sitemap index filename. mostly thats the default name.
	 * see cLoggingXmlSitemapsLib for $iSitemapArea values
	 *
	 * @static
	 * @return string 
	 */
	public static function getSitemapIndexFilename() {
		return self::$sSitemapIndexFile.'.xml';
	}
	
	/**
	 * returns the filename for a sitemap, depending on prefix and basefilename
	 * 
	 * @static
	 * @param string $sBaseFilename
	 * @return string
	 */
	public static function getSitemapFilename($sBaseFilename) {
		return self::$sSitemapFilePrefix.'_'.$sBaseFilename;
	}
}

?>