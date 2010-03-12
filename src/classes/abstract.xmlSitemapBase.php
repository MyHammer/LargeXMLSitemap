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
 * Sitemap Base Class. Provides common methods to load and save a sitemap
 * 
 * @package LargeXMLSitemap
 * @subpackage Base
 */
abstract class cXmlSitemapBase {
	/**
	 * DOMDocument object for the whole XML document
	 * 
	 * @var DOMDocument
	 */
	protected $oDocument;
	
	/**
	 * DOMElement object represents the root tag
	 * 
	 * @var DOMElement
	 */
	protected $oRootNode;
	
	/**
	 * Name of the rootnode
	 * 
	 * @var string
	 */
	protected $sRootNodeName;
	
	/**
	 * Name of the nodes
	 * 
	 * @var string
	 */
	protected $sNodeName;
	
	/**
	 * Maximum filesize in bytes
	 * 
	 * @var int
	 */
	protected $iMaxFileSize = 10485760;			// 10 MB
	
	/**
	 * XML NS Path
	 * 
	 * @var string
	 */
	protected $sXMLNamespace = '';
	
	/**
	 * is the opened file a compressed file?
	 * 
	 * @var bool
	 */
	private $bCompressed = null;
	
	
	public function __construct() {
		// create a new dom object
		$this->oDocument = new DOMDocument('1.0', 'UTF-8');
		$this->oDocument->formatOutput = true;
		
		// add a root node with the name specified in the implementing class
		$this->oRootNode = $this->oDocument->createElement($this->sRootNodeName);
		$this->oRootNode->setAttribute('xmlns', $this->sXMLNamespace);
		$this->oDocument->appendChild($this->oRootNode);
	}
	
	/**
	 * opens an existing XML file. Throws an Exception if file not found or <urlset> is missing
	 * 
	 * @param string $sFileName
	 * @throws Exception
	 */
	public function open($sFileName) {
		if (!file_exists($sFileName)) {
			throw new Exception("File ".$sFileName." not found");
		}
		
		$this->bCompressed = $this->checkCompression($sFileName);
		if($this->bCompressed) {
			$this->loadFromCompressed($sFileName);
		} else {
			$this->loadFromUncompressed($sFileName);
		}
	}
	
	/**
	 * opens an existing uncompressed XML file. Throws an Exception if <urlset> is missing
	 * 
	 * @param string $sFilename
	 */
	public function loadFromUncompressed($sFilename) {
		$this->oDocument->preserveWhiteSpace = false;
		$this->oDocument->load($sFilename);
		$this->createDOMObject();
	}
	
	/**
	 * opens an existing compressed XML file. Throws an Exception if <urlset> is missing
	 * 
	 * @param mixed $sFileName
	 * @throws Exception
	 */
	public function loadFromCompressed($sFileName) {
		$rGzFile = gzopen($sFileName, "rb");
		if ($rGzFile === false) {
			throw new Exception("Could not open file $sFileName for reading");
		}
		
		$sXML = "";
		while (!feof($rGzFile)) {
			$sXML .= gzread($rGzFile, 8192);
		}
		$this->createDOMFromString($sXML);
	}

	/**
	 * creates the internal DOM objects from a XML String
	 *
	 * @access protected
	 * @param string $sXML
	 * @return void
	 * @throws Exception
	 */
	protected function createDOMFromString($sXML) {
		$this->oDocument->preserveWhiteSpace = false;
		$this->oDocument->loadXML($sXML);
		$this->createDOMObject();
	}

	/**
	 * returns true if $sFilename is a compressed file and false if not
	 * 
	 * currently only filename checking implemented. a realy better way would be to look in the first bytes of the file
	 * 
	 * @param string $sFilename
	 * @return bool
	 */
	public function checkCompression($sFilename) {
		// checks if the last 3 chars are .gz
		return (substr($sFilename, -3) == '.gz') ? true : false;
	}
	
	/**
	 * creates the oRootNode object from oDocument
	 * 
	 * @throws Exception
	 */
	private function createDOMObject() {
		$oRootNode = $this->oDocument->getElementsByTagName($this->sRootNodeName)->item(0);
		if ($oRootNode == NULL) {
			throw new Exception("DOM not valid (<".$this->sRootNodeName."> missing)");
		}
		$this->oRootNode = $oRootNode;
	}
	
	/**
	 * saves the current file.
	 * if an exisiting file was opened, it will be saved in the same compression (compressed or not) as opened
	 * 
	 * @param string $sFileName
	 * @return bool
	 */
	public function save($sFileName = NULL) {
		if ($this->bCompressed) {
			return $this->saveCompressed($sFileName);
		} else {
			return $this->saveUncompressed($sFileName);
		}
	}
	
	/**
	 * saves the current XML tree to a file
	 * 
	 * @param string $sFileName
	 * @return bool
	 * @throws Exception
	 */
	public function saveUncompressed($sFileName) {
		$mSaveReturn = file_put_contents($sFileName, $this->createXMLString());
		if ($mSaveReturn === false) {
			throw new Exception("Could not save file");
		}
		return true;
	}
	
	/**
	 * saves the current XML tree to a gz compressed file
	 * 
	 * @param string $sFileName
	 * @return bool
	 * @throws Exception
	 */
	public function saveCompressed($sFileName) {
		$rGzFile = gzopen($sFileName, "wb9");
		if ($rGzFile === false) {
			throw new Exception("Could not open file $sFileName for writing");
		}
		$bReturn = gzwrite($rGzFile, $this->createXMLString());
		gzclose($rGzFile);
		return true;
	}
	
	/**
	 * creates a string from current XML tree
	 * 
	 * @return string
	 * @throws cXmlSitemapMaximumError
	 */
	private function createXMLString() {
		//$this->oDocument->appendChild($this->oRootNode);
		$sXML = $this->oDocument->saveXML();
		if (strlen($sXML) > $this->iMaxFileSize) {
			new cXmlSitemapMaximumError("File too big (".strlen($sXML).")", cXmlSitemapMaximumError::maxNumberOfBytes);
		}
		return $sXML;
	}
	
	/**
	 * returns the number of nodes in this tree
	 * 
	 * @return int
	 */
	public function getNumberOfChildNodes() {
		return $this->oRootNode->getElementsByTagName($this->sNodeName)->length;
	}
	
	/**
	 * encodes a url so it can used in a xml file (entities are not escaped)
	 * 
	 * @param string $sUrl
	 * @return string
	 */
	public static function parseUrl($sUrl) {
		//return $sUrl;
		$aParametersEncoded = array();
		$sUrlEncoded = "";
		
		// create an array with the query string (index 1) and everything else before (index 0)
		$aUrlParts = explode('?', $sUrl, 2);
		$aParameters = array();
		if (isset($aUrlParts[1])) {
			// create an array with the parameters
			$aParameters = explode('&', $aUrlParts[1]);
		}
		
		// lets take every parameter
		for ($i = 0; $i < count($aParameters); $i++) {
			$aParameter = explode('=', $aParameters[$i]);
			
			// and encode the name and the value (if exits)
			$aParameter[0] = urlencode($aParameter[0]);
			if (isset($aParameter[1])) {
				$aParameter[1] = urlencode($aParameter[1]);
			}
			
			// rebuild the encoded parameter
			$aParametersEncoded[] = $aParameter[0].'='.$aParameter[1];
		}
		
		// rebuild the url
		$sUrlEncoded = $aUrlParts[0];
		if (count($aParametersEncoded) > 0) {
			$sUrlEncoded .= '?'.implode('&', $aParametersEncoded);
		}
		return $sUrlEncoded;
	}
	
	/**
	 * convert special xml chars to their entities
	 * 
	 * @param string $sString
	 * @return string
	 */
	protected function parseEntities($sString) {
		$sString = str_replace("&", "&amp;", $sString);
		$sString = str_replace("<", "&lt;", $sString); 
		$sString = str_replace(">", "&gt;", $sString); 
		$sString = str_replace("'", "&apos;", $sString);  
		$sString = str_replace("\"", "&quot;", $sString);
		return $sString;
	}
}

?>