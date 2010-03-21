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
 * @subpackage Index
 * @license    http://www.myhammer.de/opensource/license/gpl.txt GNU General Public License Version 3
 * @version    1.0
 * @author     Jan Christiansen <christiansen@myhammer.de>
 */

/**
 * Sitemap Index Class
 * Creates the index file
 * 
 * @package LargeXMLSitemap
 * @subpackage Index
 */
class cXmlSitemapIndex extends cXmlSitemapBase {
	/**
	 * XML NS Path
	 * @var string
	 */
	protected $sXMLNamespace = 'http://www.google.com/schemas/sitemap/0.84';
	
	/**
	 * Maximum number of sitemaps per file
	 * @var int
	 */
	const maxNumberOfSitemaps = 1000;
	
	/**
	 * Name of the rootnode
	 * 
	 * @var string
	 */
	protected $sRootNodeName = 'sitemapindex';
	
	/**
	 * Name of the nodes
	 * 
	 * @var string
	 */
	protected $sNodeName = 'sitemap';
	
	/**
	 * Maximum filesize in bytes
	 * 
	 * @var int
	 */
	protected $iMaxFileSize = 1;
	
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * adds sitemaps $aSitemaps to index $sFilename. if index doesnt exists it will be created
	 * 
	 * @param string $aSitemaps
	 * @param string $sFilename
	 * @param bool $bSaveCompressed
	 */
	public static function updateSitemapIndex($aSitemaps, $sFilename, $bSaveCompressed = true) {
		if (file_exists($sFilename)) {
			$oSitemapIndex = new self;
			$oSitemapIndex->open($sFilename);
		} else {
			$oSitemapIndex = new self;
		}

		$oSitemapIndex->addSitemaps($aSitemaps);
		if ($bSaveCompressed == true) {
			$oSitemapIndex->saveCompressed($sFilename);
		} else {
			$oSitemapIndex->save($sFilename);
		}
	}
	
	/**
	 * adds a sitemap tag to the XML tree.
	 * if there is a sitemap node with the same location, only the lastmod gets updated or added if set
	 * if $iLastmod is null, the lastmod tag gets deleted.
	 * 
	 * @param string $sLocation
	 * @param int $iLastmod Last modification timestamp
	 * @return bool
	 */
	public function addSitemap($sLocation, $iLastmod = NULL) {
		// check if the sitemap has an entry in index
		$oUrl = $this->getDOMNodeSitemapSearchByLocation($sLocation);
		if ($oUrl !== false) {
			// search for lastmod node
			$oLastmodNode = $this->getDOMNodeLastmodByNode($oUrl);
			if ($oLastmodNode !== false) {
				if ($iLastmod != null) {
					// just update the lastmod time
					$oLastmodNode->nodeValue = date(DATE_W3C, $iLastmod);
				} else {
					// remove the node if $iLastmod is empty
					$oUrl->removeChild($oLastmodNode);
				}
			} else {
				// no lastmode node present, lets create one if $iLastmod is not null
				if (!empty($iLastmod)) {
					$oUrl->appendChild($this->oDocument->createElement('lastmod', date(DATE_W3C, $iLastmod)));
				}
			}
		} else {
			if($this->getNumberOfChildNodes() >= self::maxNumberOfSitemaps) {
				throw new cXmlSitemapMaximumError('Maximum number of Sitemaps reached ('.$this->getNumberOfChildNodes().')', cXmlSitemapMaximumError::maxNumberOfChildNodes);
			}
			
			$oUrl = $this->oDocument->createElement($this->sNodeName);
			$oLoc = $oUrl->appendChild($this->oDocument->createElement('loc'));
			
			// createTextNode() converts special chars to xml entities
			$oLoc->appendChild($this->oDocument->createTextNode(self::parseUrl($sLocation)));
			
			if (!empty($iLastmod)) {
				$oUrl->appendChild($this->oDocument->createElement('lastmod', date(DATE_W3C, $iLastmod)));
			}
			
			$this->oRootNode->appendChild($oUrl);
		}
		return true;
	}
	
	/**
	 * delete all urls from sitemap index with the location matching $sLocation
	 * 
	 * @param string $sLocation
	 */
	public function deleteUrls($sLocation) {
		$oUrls = $this->getDOMNodeListSitemapSearchByLocation($sLocation);
		if ($oUrls !== false) {
			foreach($oUrls as $oUrl) {
				$oUrl->parentNode->removeChild($oUrl);
			}
		}
	}
	
	/**
	 * searches the sitemap index for an entry with $sLocation and returns the node or false if nothing found
	 * 
	 * @param string $sLocation
	 * @return DOMNode
	 */
	private function getDOMNodeSitemapSearchByLocation($sLocation) {
		$oNodelist = $this->getDOMNodeListSitemapSearchByLocation($sLocation);
		if($oNodelist->length > 0) {
			return $oNodelist->item(0);
		} else {
			return false;
		}
	}
	
	/**
	 * searches the sitemap index for a entries with $sLocation and returns them as DOMNodeList or false if nothing found
	 * 
	 * @param string $sLocation
	 * @return DOMNodeList
	 */
	private function getDOMNodeListSitemapSearchByLocation($sLocation) {
		$oXPath = new DOMXPath($this->oDocument);
		// we need to register the namespace with a fakeprefix. without it, xpath wont work
		$oXPath->registerNameSpace('fakeprefix', $this->sXMLNamespace);
		
		// query the DOM Document for sitemap elements with childNode loc and text = $sLocation
		//$oNodelist = $oXPath->evaluate('//fakeprefix:sitemapindex/fakeprefix:sitemap[./fakeprefix:loc/text(), "'.$sLocation.'"]');
		$oNodelist = $oXPath->evaluate('//fakeprefix:sitemapindex/fakeprefix:sitemap[contains(./fakeprefix:loc/text(), "'.$sLocation.'")]');
		
		return $oNodelist;
	}
	
	/**
	 * returns the DOMNode for lastmod tag from $oDOMNode or false if not found
	 * 
	 * @param DOMNode $oDOMNode
	 * @return DOMNode
	 */
	private function getDOMNodeLastmodByNode($oDOMNode) {
		foreach ($oDOMNode->childNodes as $oChildNode) {
			if($oChildNode->nodeName == 'lastmod') {
				return $oChildNode;
			}
		}
		return false;
	}
	
	/**
	 * adds multiple sitemaps to XML tree
	 * 
	 * @param array $aSitemaps
	 */
	public function addSitemaps(array $aSitemaps) {
		foreach ($aSitemaps as $aSitemap) {
			$this->addSitemap($aSitemap['loc'], $aSitemap['lastmod']);
		}
	}
	
	/**
	 * returns an array that can be used in self::addSitemaps()
	 * 
	 * @param string $sLocation
	 * @param int $iLastmod Last modification timestamp
	 * @return array
	 */
	public static function createArray($sLocation, $iLastmod = NULL) {
		return array('loc' => $sLocation, 'lastmod' => $iLastmod);
	}
}

?>