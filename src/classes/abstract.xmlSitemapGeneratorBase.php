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
 * @subpackage Base
 * @license    http://www.myhammer.de/opensource/license/gpl.txt GNU General Public License Version 3
 * @version    1.0
 * @author     Jan Christiansen <christiansen@myhammer.de>
 */

/**
 * Abstract Generator Base Class
 * 
 * @package LargeXMLSitemap
 * @subpackage Base
 */
abstract class cXmlSitemapGeneratorBase {
	/**
	 * current cXmlSitemapWrite object
	 * 
	 * @var cXmlSitemapWrite
	 */
	protected $oSitemap;
	
	/**
	 * Basefilename without fileending (eg: showauctions)
	 * 
	 * @var string
	 */
	protected $sFilename;
	
	/**
	 * filename ending with point (eg: .xml.gz)
	 * 
	 * @var string
	 */
	protected $sFilenameEnding;
	
	/**
	 * complete filename for current sitemapfile
	 * 
	 * @var string
	 */
	protected $sFilenameComplete;
	
	/**
	 * save sitemaps compressed?
	 * 
	 * @var bool
	 */
	protected $bSaveCompressed;
	
	/**
	 * path to the directory where the sitemaps are stored
	 * 
	 * @var mixed
	 */
	public $sBaseDirectory = '';
	
	/**
	 * directory where the sitemaps are created.
	 * 
	 * @var string
	 */
	public $sTempDirectory;
	
	/**
	 * number of current file
	 * 
	 * @var int
	 */
	protected $iCurrentFileNumber = 0;
	
	/**
	 * the created sitemaps
	 * 
	 * @var array
	 */
	protected $aSitemaps = array();
	
	/**
	 * This Url is added to all sitemap urls in the sitemapindex file
	 * 
	 * @var string
	 */
	public $sSitemapsBaseUrl;
	
	/**
	 * the number of urls added
	 * 
	 * @var int
	 */
	public $iNumberUrls = 0;
	
	/**
	 * the number of urls added in the current active sitemap file
	 * 
	 * @var int
	 */
	protected $iNumberUrlsSitemap = 0;
	
	/**
	 * the number of sitemaps touched
	 * 
	 * @var int
	 */
	public $iNumberSitemaps = 0;
	
	/**
	 * filename for the indexfile
	 * 
	 * @var string
	 */
	public $sSitemapIndexFilename = '';
	
	
	abstract public function open();
	abstract public function save();
	
	/**
	 * adds an url tag to the file. See cXmlSitemap::$aValidChangeFreq for valid $eChangefreq values.
	 * 
	 * @param string $sLocation
	 * @param int $iLastmod Last modification timestamp
	 * @param mixed $eChangefreq Must be a value from cXmlSitemap::$aValidChangeFreq
	 * @param mixed $fPriority
	 */
	abstract public function addUrl($sLocation, $iLastmod = NULL, $eChangefreq = NULL, $fPriority = NULL);
	
	/**
	 * @param string $sFileName Basefilename without fileending
	 * @param bool $bSaveCompressed
	 * @param string $sBaseDirectory
	 * @param string $sSitemapsBaseUrl
	 * @param string $sTempDirectory
	 */
	public function __construct($sFilename, $bSaveCompressed = true, $sBaseDirectory = '', $sSitemapsBaseUrl = '', $sTempDirectory = NULL) {
		$this->sFilename = $sFilename;
		$this->bSaveCompressed = $bSaveCompressed;
		if ($bSaveCompressed == true) {
			$this->sFilenameEnding = '.xml.gz';
		} else {
			$this->sFilenameEnding = '.xml';
		}
		$this->sFilenameComplete = $this->getCompleteFilename();
		$this->sBaseDirectory = $sBaseDirectory;
		$this->sSitemapsBaseUrl = $sSitemapsBaseUrl;
		$this->sTempDirectory = $sTempDirectory;
	}
	
	/**
	 * moves the sitemaps vom basedirectory to $sTargetDirectory
	 * 
	 * @param string $sTargetDirectory
	 * @throws Exception
	 */
	public function moveSitemaps($sTargetDirectory) {
		if (!is_dir($sTargetDirectory)) {
			throw new Exception("Directory not found: ".$sTargetDirectory);
		}
		foreach ($this->aSitemaps as $aSitemap) {
			rename($this->sBaseDirectory.$aSitemap['loc'], $sTargetDirectory.DIRECTORY_SEPARATOR.$aSitemap['loc']);
		}
	}
	
	/**
	 * deletes current sitemaps (matching the filename, set in constructor)
	 * returns an array with the deleted files
	 * 
	 * @return array
	 */
	public function deleteCurrent() {
		$aReturn = array();
		$aFilenames = glob($this->sBaseDirectory.$this->sFilename.'_*'.$this->sFilenameEnding);
		foreach ($aFilenames as $sFilename) {
			$sFilenameReal = realpath($sFilename);
			if (is_file($sFilenameReal)) {
				unlink($sFilenameReal);
				$aReturn[] = $sFilename;
			}
		}
		return $aReturn;
	}
	
	/**
	 * deletes sitemaps from indexfile matching the basefilename sFilename'_'
	 * 
	 * @param string $sIndexFilename sitemapindex file
	 */
	public function deleteCurrentFromSitemapIndex($sIndexFilename) {
		if ($this->bSaveCompressed == true) {
			$sCompressionEnding = '.gz';
		}
		
		$oSitemapIndex = new cXmlSitemapIndex();
		$oSitemapIndex->open($sIndexFilename.$sCompressionEnding);
		$oSitemapIndex->deleteUrls($this->sSitemapsBaseUrl.$this->sFilename.'_');
		$oSitemapIndex->save($sIndexFilename.$sCompressionEnding);
	}
	
	/**
	 * updates the sitemap indexfile
	 * 
	 * @param string $sIndexFilename
	 * @param bool $bForceCompression
	 */
	public function updateSitemapIndex($sIndexFilename, $bForceCompression=null) {
		if ($bForceCompression === null) {
			$bForceCompression = $this->bSaveCompressed;
		}
		
		$sCompressionEnding = '';
		if ($bForceCompression == true) {
			$sCompressionEnding = '.gz';
		}
		
		if (file_exists($sIndexFilename.$sCompressionEnding)) {
			$oSitemapIndex = new cXmlSitemapIndex();
			$oSitemapIndex->open($sIndexFilename.$sCompressionEnding);
		} else {
			$oSitemapIndex = new cXmlSitemapIndex();
		}

		foreach ($this->aSitemaps as $aSitemap) {
			$oSitemapIndex->addSitemap($this->sSitemapsBaseUrl.$aSitemap['loc'], $aSitemap['lastmod']);
		}
		
		if ($bForceCompression == true) {
			$oSitemapIndex->saveCompressed($sIndexFilename.".gz");
		} else {
			$oSitemapIndex->saveUncompressed($sIndexFilename);
		}
	}
	
	/**
	 * returns the filename from the file with the highest number or false if no matching file is found
	 * 
	 * @return string
	 */
	protected function getHighestFilename() {
		// get all filenames matching the expression
		$aFilenames = glob($this->sBaseDirectory.$this->sFilename.'_*'.$this->sFilenameEnding);
		if (count($aFilenames) > 0) {
			// if there are files, get only the numbers from the filenames
			$aNumbers = array_map(array($this, 'getNumbersFromFilename'), $aFilenames);
			
			// sort the numbers
			rsort($aNumbers, SORT_NUMERIC);
			
			// get the hightes number
			$this->iCurrentFileNumber = (int)$aNumbers[0];
			
			// build filename
			return $this->sFilename.'_'.$aNumbers[0].$this->sFilenameEnding;
		}
		return false;
	}
	
	/**
	 * returns only the number from a filename
	 * 
	 * @param string $sFilename
	 * @return int
	 */
	protected function getNumbersFromFilename($sFilename) {
		$aMatches = array();
		preg_match("#_([0-9]?)".$this->sFilenameEnding."#", $sFilename, $aMatches);
		return $aMatches[1];
	}
	
	/**
	 * returns the complete filename for a sitemap file, depending on current number, area and compression
	 * 
	 * @return string
	 */
	protected function getCompleteFilename() {
		return $this->sFilename.'_'.$this->iCurrentFileNumber.$this->sFilenameEnding;
	}
}

?>