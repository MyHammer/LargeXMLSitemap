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
 * Sitemap Class which can only write to new sitemap files, but a lot faster then cXmlSitemap
 * 
 * @package LargeXMLSitemap
 * @subpackage Sitemap
 */
class cXmlSitemapWrite extends cXmlSitemap {
	/**
	 * filepointer resource to current sitemapfile
	 * 
	 * @var resource
	 */
	private $rSitemapFile;
	
	/**
	 * childnode counter
	 * 
	 * @var int
	 */
	private $iNumberOfChildNodes = 0;
	
	/**
	 * the current filename
	 * 
	 * @var string
	 */
	private $sFilename = "";
	
	/**
	 * creates a new object
	 * 
	 * @return cXmlSitemapWrite
	 * @throws Exception
	 */
	public function __construct($sFilename) {
		$this->sFilename = $sFilename;
		$this->rSitemapFile = fopen($sFilename, "w");
		if ($this->rSitemapFile === false) {
			 new Exception("Could not open file $sFileName for writing");
		}
		
		fwrite($this->rSitemapFile, '<?xml version="1.0" encoding="UTF-8"?>'."\n");
		fwrite($this->rSitemapFile, "\t".'<'.$this->sRootNodeName.' xmlns="'.$this->sXMLNamespace.'">'."\n");
	}
	
	/**
	 * adds an url tag to the XML file
	 * 
	 * @param string $sLocation
	 * @param int $iLastmod Last modification timestamp
	 * @param mixed $eChangefreq
	 * @param mixed $fPriority
	 * @throws cXmlSitemapMaximumError, Exception
	 */
	public function addUrl($sLocation, $iLastmod = NULL, $eChangefreq = NULL, $fPriority = NULL) {
		if ($this->iNumberOfChildNodes >= self::maxNumberOfUrls) {
			throw new cXmlSitemapMaximumError('Maximum number of URLs reached ('.$this->iNumberOfChildNodes.')', cXmlSitemapMaximumError::maxNumberOfChildNodes);
		}
		
		$sNode = "\t<url>\n";
		$sNode .= "\t\t<loc>".self::parseEntities(self::parseUrl($sLocation))."</loc>\n";
		
		if (!empty($iLastmod)) {
			$sNode .= "\t\t<lastmod>".date(DATE_W3C, $iLastmod)."</lastmod>\n";
		}
		
		if (!empty($eChangefreq)) {
			if (!in_array($eChangefreq, self::$aValidChangeFreq)) {
				throw new Exception('$eChangefreq: '.$eChangefreq.' is not a valid value');
			}
			$sNode .= "\t\t<changefreq>".$eChangefreq."</changefreq>\n";
		}
		
		if (!empty($fPriority)) {
			// format priority because 1.0 gets 1 without formatting
			$sNode .= "\t\t<priority>".number_format($fPriority, 1, '.', '')."</priority>\n";
		}
		$sNode .= "\t</url>\n";
		
		// clear clearstatcache, because php caches file infos
		clearstatcache();
		
		// the current filesize + length of node to add + a little safety for closing tags
		$iFileSize = filesize($this->sFilename) + strlen($sNode) + 500;
		if ($iFileSize >= $this->iMaxFileSize) {
			throw new cXmlSitemapMaximumError('Maximum filesize reached ('.$iFileSize.')', cXmlSitemapMaximumError::maxNumberOfBytes);
		}
		
		fwrite($this->rSitemapFile, $sNode);
		$this->iNumberOfChildNodes++;
	}
	
	/**
	 * saves the current sitemapefile
	 * 
	 * @return bool
	 */
	public function save() {
		fwrite($this->rSitemapFile, '</'.$this->sRootNodeName.'>');
		return fclose($this->rSitemapFile);
	}
	
	/**
	 * saves the current sitemapefile to a gz compressed file
	 * 
	 * @return bool
	 * @throws Exception
	 */
	public function saveCompressed() {
		// save the file
		$this->save();
		
		// open a new temporary gz file
		if ($rFileGZ = gzopen($this->sFilename.".temp", "wb9")) {
			// open the uncompressed file
			if ($rFile = fopen($this->sFilename, "rb")) {
				// read it and write to compressed file
				while (!feof($rFile)) {
					gzwrite($rFileGZ, fread($rFile, 1024*512));
				}
				fclose($rFile);
			} else {
				throw new Exception("Could not open file {$this->sFilename} for reading");
			}
			// close compressed file
			gzclose($rFileGZ);
			// delete uncompressed
			unlink($this->sFilename);
			// rename compressed file to the right filename
			rename($this->sFilename.".temp", $this->sFilename);
		} else {
			throw new Exception("Could not open file $sFileName for writing");
		}
		return true;
	}
	
	/**
	 * deletes the file
	 * 
	 */
	public function delete() {
		if ($this->rSitemapFile) {
			fclose($this->rSitemapFile);
		}
		unlink($this->sFilename);
	}
	
	/**
	 * returns the current sitemap-node counter
	 * 
	 */
	public function getNumberOfChildNodes() {
		return $this->iNumberOfChildNodes;
	}
	
	/**
	 * NOT IMPLEMENTED BY THIS CLASS
	 * 
	 * @param string $sFileName
	 * @throws Exception
	 */
	public function open($sFileName) {
		throw new Exception('Method '.__METHOD__.' not implemented by '.__CLASS__);
	}
	
	/**
	 * NOT IMPLEMENTED BY THIS CLASS
	 * 
	 * @param mixed $sFileName
	 * @throws Exception
	 */
	public function openCompressed($sFileName) {
		throw new Exception('Method '.__METHOD__.' not implemented by '.__CLASS__);
	}
}

?>