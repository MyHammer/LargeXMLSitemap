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
 * @subpackage Generator
 * @license    http://www.myhammer.de/opensource/license/gpl.txt GNU General Public License Version 3
 * @version    1.0
 * @author     Jan Christiansen <christiansen@myhammer.de>
 */

/**
 * Creator for sitemaps. Handles the maximums and creates additional sitemaps if needed.
 * can also create/update the sitemap index
 * 
 * @package LargeXMLSitemap
 * @subpackage Generator
 */
class cXmlSitemapGenerator extends cXmlSitemapGeneratorBase {
	/**
	 * current cXmlSitemap object
	 * 
	 * @var cXmlSitemap
	 */
	protected $oSitemap;
	
	/**
	 * @param string $sFileName Basefilename without fileending
	 * @param bool $bSaveCompressed
	 * @param string $sBaseDirectory
	 * @param string $sSitemapsBaseUrl
	 * @param string $sTempDirectory
	 * @return cXmlSitemapGenerator
	 */
	public function __construct($sFilename, $bSaveCompressed = true, $sBaseDirectory = '', $sSitemapsBaseUrl = '', $sTempDirectory = NULL) {
		parent::__construct($sFilename, $bSaveCompressed, $sBaseDirectory, $sSitemapsBaseUrl, $sTempDirectory);
	}
	
	/**
	 * opens the first sitemapfile
	 * be sure to call this before first addUrl() call
	 * 
	 * @todo Add check that addUrl() call was issued before
	 */
	public function open() {
		// get the highest sitemap
		$sFilename = $this->getHighestFilename();
		$this->oSitemap = new cXmlSitemap();
		if ($sFilename !== false) {
			// and open it
			$this->oSitemap->open($this->sBaseDirectory.$sFilename);
			$this->sFilenameComplete = $sFilename;
		}
	}
	
	/**
	 * adds an url tag to the XML file
	 * 
	 * @param string $sLocation
	 * @param int $iLastmod Last modification timestamp
	 * @param string $eChangefreq
	 * @param float $fPriority
	 * @return array
	 */
	public function addUrl($sLocation, $iLastmod = NULL, $eChangefreq = NULL, $fPriority = NULL) {
		//$this->aUrls[] = cXmlSitemap::createArray($sLocation, $iLastmod, $eChangefreq, $fPriority);
		try {
			$this->oSitemap->addUrl($sLocation, $iLastmod, $eChangefreq, $fPriority);
			$this->iNumberUrlsSitemap++;
		}
		// catch maximum exceptions
		catch (cXmlSitemapMaximumError $e)
		{
			$sTempFilename = $this->saveSitemap();
			if ($sTempFilename !== false)
			{
				$this->sFilenameComplete = $sTempFilename;
				// add filename and modification time
				$this->aSitemaps[] = array('loc' => $this->sFilenameComplete, 'lastmod' => time());
			}
			
			// increment the filenumber
			$this->iCurrentFileNumber++;
			$this->iNumberUrlsSitemap = 0;
			
			// create new sitemap object
			$this->oSitemap = new cXmlSitemap();
			
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
		$sFilename = $this->saveSitemap();
		if ($sFilename !== false) {
			$this->aSitemaps[] = array('loc' => $sFilename, 'lastmod' => time());
		}
		$this->iNumberSitemaps = count($this->aSitemaps);
		return $this->aSitemaps;
	}
	
	/**
	 * saves the current sitemap and returns the filename
	 * @return string
	 */
	private function saveSitemap() {
		if ($this->iNumberUrlsSitemap == 0) {
			return false;
		}
		
		// save the current sitemap object to file
		$sFilename = $this->getCompleteFilename();
		
		// compress or not compress, thats the question
		if ($this->bSaveCompressed) {
			$this->oSitemap->saveCompressed($this->sBaseDirectory.$sFilename);
		} else {
			$this->oSitemap->save($this->sBaseDirectory.$sFilename);
		}
		return $sFilename;
	}
}

?>