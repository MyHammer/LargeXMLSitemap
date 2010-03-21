<?php

/**
 * MyHammer XML Sitemap Framework
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
 * @package    MyHammer LargeXMLSitemap
 * @subpackage Examples
 * @license    http://www.myhammer.de/opensource/license/gpl.txt GNU General Public License Version 3
 * @version    1.0
 * @author     Jan Christiansen <christiansen@myhammer.de>
 */


require_once ('../init/init.main.php');
set_time_limit(0);

// init the config
cXmlSitemapConfig::loadConfig();


// creates a creator object for fast writing (cant read/edit existing XML Files)
$oSitemapCreator = new cXmlSitemapGenerator(
	cXmlSitemapConfig::getSitemapFilename('items'),
	TRUE,
	cXmlSitemapConfig::getSitemapDirectoryPath()
);

$oSitemapCreator->open();


// Get shop articles
$aItemIds = array(1001, 1002, 1003, 1004, 1005, 1006, 1007, 1008, 1009, 1010);

// add them
foreach($aItemIds as $iItemId)
{
	$oSitemapCreator->addUrl(
		'http://www.example.com/shop/item/'.$iItemId,
		time(),
		cXmlSitemap::changeFreqYearly,
		0.3
	);
}

// close the creator and the last sitemap
$aCreatedSitemaps = $oSitemapCreator->save();


// Add sitemap to index
$oSitemapCreator->updateSitemapIndex(cXmlSitemapConfig::getSitemapDirectoryPath().cXmlSitemapConfig::getSitemapIndexFilename());

