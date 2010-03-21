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
 * @subpackage Sitemap
 * @license    http://www.myhammer.de/opensource/license/gpl.txt GNU General Public License Version 3
 * @version    1.0
 * @author     Jan Christiansen <christiansen@myhammer.de>
 */

/**
 * Sitemap Class
 * 
 * @package LargeXMLSitemap
 * @subpackage Sitemap
 */
class cXmlSitemap extends cXmlSitemapBase {
	/**
	 * XML NS Path
	 * 
	 * @var string
	 */
	protected $sXMLNamespace = 'http://www.google.com/schemas/sitemap/0.84';
	
	/**
	 * Maximum number of sitemaps per file
	 * 
	 * @var int
	 */
	const maxNumberOfUrls = 50000;
	
	/**
	 * valid change frequency values
	 *  
	 * @var array
	 */
	public static $aValidChangeFreq = array(
		self::changeFreqAlways,
		self::changeFreqHourly,
		self::changeFreqDaily,
		self::changeFreqWeekly,
		self::changeFreqMonthly,
		self::changeFreqYearly,
		self::changeFreqNever
	);
	
	const changeFreqAlways	= 'always';
	const changeFreqHourly	= 'hourly';
	const changeFreqDaily	= 'daily';
	const changeFreqWeekly	= 'weekly';
	const changeFreqMonthly	= 'monthly';
	const changeFreqYearly	= 'yearly';
	const changeFreqNever	= 'never';
	
	/**
	 * Name of the rootnode
	 * 
	 * @var string
	 */
	protected $sRootNodeName = 'urlset';
	
	/**
	 * name of the nodes
	 * 
	 * @var string
	 */
	protected $sNodeName = 'url';
	
	
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * adds an url tag to the XML tree
	 * 
	 * @param string $sLocation
	 * @param int $iLastmod Last modification timestamp
	 * @param mixed $eChangefreq
	 * @param mixed $fPriority
	 * @throws cXmlSitemapMaximumError, Exception
	 */
	public function addUrl($sLocation, $iLastmod = NULL, $eChangefreq = NULL, $fPriority = NULL) {
		if ($this->getNumberOfChildNodes() >= self::maxNumberOfUrls) {
			throw new cXmlSitemapMaximumError('Maximum number of URLs reached ('.$this->getNumberOfChildNodes().')', cXmlSitemapMaximumError::maxNumberOfChildNodes);
		}
		
		$oUrl = $this->oDocument->createElement('url');
		$oLoc = $oUrl->appendChild($this->oDocument->createElement('loc'));
		
		// createTextNode() converts special chars to xml entities
		$oLoc->appendChild($this->oDocument->createTextNode(self::parseUrl($sLocation)));
		
		if (!empty($iLastmod)) {
			$oUrl->appendChild($this->oDocument->createElement('lastmod', date(DATE_W3C, $iLastmod)));
		}
		
		if (!empty($eChangefreq)) {
			if(!in_array($eChangefreq, self::$aValidChangeFreq)) {
				throw new Exception('$eChangefreq: '.$eChangefreq.' is not a valid value');
			}
			$oUrl->appendChild($this->oDocument->createElement('changefreq', $eChangefreq));
		}
		
		if (!empty($fPriority)) {
			// format priority because 1.0 gets 1 without formatting
			$oUrl->appendChild($this->oDocument->createElement('priority', number_format($fPriority, 1, '.', '')));
		}
		
		$this->oRootNode->appendChild($oUrl);
	}
	
	/**
	 * adds multiple URLs to XML tree. calls addUrl() for every array entry
	 * 
	 * @param array $aUrls array('loc' => location, 'lastmod' => last modified, 'changefreq' => change frequency, 'priority' => priority) 
	 */
	public function addUrls(array $aUrls) {
		foreach ($aUrls as $aUrl) {
			$this->addUrl($aUrl['loc'], $aUrl['lastmod'], $aUrl['changefreq'], $aUrl['priority']);
		}
	}
	
	/**
	 * returns an array that can be used in self::addUrls()
	 * 
	 * @param string $sLocation
	 * @param int $iLastmod Last modification timestamp
	 * @param string $eChangefreq
	 * @param float $fPriority
	 * @return array
	 */
	public static function createArray($sLocation, $iLastmod = NULL, $eChangefreq = NULL, $fPriority = NULL) {
		return array('loc' => $sLocation, 'lastmod' => $iLastmod, 'changefreq' => $eChangefreq, 'priority' => $fPriority);
	}
}

?>