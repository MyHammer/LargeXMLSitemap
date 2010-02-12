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
 * @subpackage Exception
 * @license    http://www.myhammer.de/opensource/license/gpl.txt GNU General Public License Version 3
 * @version    1.0
 * @author     Jan Christiansen <christiansen@myhammer.de>
 */

/**
 * Sitemap Maximum Exception
 * 
 * @package LargeXMLSitemap
 * @subpackage Exception
 */
class cXmlSitemapMaximumError extends Exception {
	const maxNumberOfChildNodes = 1;
	const maxNumberOfBytes = 2;
	
	public function __construct($sMessage = NULL, $iCode = NULL) {
		parent::__construct($sMessage, $iCode);
	}
}

?>