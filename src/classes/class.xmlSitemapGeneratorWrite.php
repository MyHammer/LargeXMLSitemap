<?php

/**
 * MyHammer LargeXMLSitemap
 * 
 * This source file is subject to the GNU General Public License Version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.myhammer.de/opensource/license/gpl.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to opensource@myhammer.de so we can send you a copy immediately.
 *
 * @category   MyHammer
 * @package    LargeXMLSitemap
 * @subpackage Generator
 * @license    http://www.myhammer.de/opensource/license/gpl.txt GNU General Public License Version 3
 * @version    1.0
 * @author     Jan Christiansen <christiansen@myhammer.de>
 */

/**
 * Creator for sitemaps. Handles the maximums and creates additional sitemaps if needed.
 * Uses the cXmlSitemapWrite class for maximum perfomance, and therefor it can only create new sitemaps
 * 
 * @package LargeXMLSitemap
 * @subpackage Generator
 */
class cXmlSitemapGeneratorWrite extends cXmlSitemapGeneratorBase {
	/**
	 * current cXmlSitemapWrite object
	 * 
	 * @var cXmlSitemapWrite
	 */
	protected $oSitemap;
	
	/**
	 * @param string $sFilename Basefilename without fileending
	 * @param bool $bSaveCompressed
	 * @param string $sBaseDirectory
	 * @param string $sSitemapsBaseUrl
	 * @param string $sTempDirectory
	 * @return cXmlSitemapGeneratorWrite
	 */
	public function __construct($sFilename, $bSaveCompressed = TRUE, $sBaseDirectory = '', $sSitemapsBaseUrl = '', $sTempDirectory = NULL) {
		parent::__construct($sFilename, $bSaveCompressed, $sBaseDirectory, $sSitemapsBaseUrl, $sTempDirectory);
	}
	
	/**
	 * opens the first sitemapfile
	 * be sure to call this before first addUrl() call
	 */
	public function open() {
		$this->oSitemap = new cXmlSitemapWrite($this->sBaseDirectory.$this->sFilenameComplete);		
	}
	
	/**
	 * adds an url tag to the XML file
	 * 
	 * @param string $sLocation
	 * @param int $iLastmod Last modification timestamp
	 * @param mixed $eChangefreq
	 * @param mixed $fPriority
	 */
	public function addUrl($sLocation, $iLastmod = NULL, $eChangefreq = NULL, $fPriority = NULL) {
		try {
			$this->oSitemap->addUrl($sLocation, $iLastmod, $eChangefreq, $fPriority);
			$this->iNumberUrlsSitemap++;
		}
		// catch maximum exceptions
		catch(cXmlSitemapMaximumError $e) {
			if ($this->saveSitemap()) {
				// add filename and modification time
				$this->aSitemaps[] = array('loc' => $this->sFilenameComplete, 'lastmod' => time());
			}
			
			// increment the filenumber
			$this->iCurrentFileNumber++;
			$this->iNumberUrlsSitemap = 0;
			
			// create new sitemap object
			$this->sFilenameComplete = $this->getCompleteFilename();
			$this->oSitemap = new cXmlSitemapWrite($this->sBaseDirectory.$this->sFilenameComplete);
			
			// add url to new sitemap
			$this->oSitemap->addUrl($sLocation, $iLastmod, $eChangefreq, $fPriority);
			$this->iNumberUrlsSitemap++;
		}
		$this->iNumberUrls++;
	}
	
	/**
	 * closes/saves the last sitemap
	 * Returns an array with the sitemaps created
	 * @return array
	 */
	public function save() {
		if ($this->saveSitemap()) {
			$this->aSitemaps[] = array('loc' => $this->sFilenameComplete, 'lastmod' => time());
		}
		$this->iNumberSitemaps = count($this->aSitemaps);
		return $this->aSitemaps;
	}
	
	/**
	 * saves the current sitemapfile
	 */
	private function saveSitemap() {
		if ($this->iNumberUrlsSitemap == 0) {
			// delete the sitemap because 
			$this->oSitemap->delete();
			return false;
		}
		if ($this->bSaveCompressed == true) {
			return $this->oSitemap->saveCompressed();
		} else {
			return $this->oSitemap->save();
		}
	}
}

?>