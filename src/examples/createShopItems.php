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
 * @subpackage Examples
 * @license    http://www.myhammer.de/opensource/license/gpl.txt GNU General Public License Version 3
 * @version    1.0
 * @author     Jan Christiansen <christiansen@myhammer.de>
 */

require_once('../init/init.main.php');
set_time_limit(0);

// init the config
cXmlSitemapConfig::loadConfig();


// creates a creator object for fast writing (cant read/edit existing XML Files)
$oSitemapCreator = new cXmlSitemapGeneratorWrite(
	cXmlSitemapConfig::getSitemapFilename('items'),
	TRUE,
	cXmlSitemapConfig::getSitemapDirectoryPath()
);

// delete current sitemap files
$oSitemapCreator->deleteCurrent();

// open the first sitemap
$oSitemapCreator->open();

// Get shop articles
$aItemIds = array();
for ($i = 0; $i<50000; $i++) {
	$aItemIds[] = $i;
}

foreach ($aItemIds as $iItemId) {
	$oSitemapCreator->addUrl(
		'http://jc.bhn-media.de/shop/item/very/long/url/because/we/need/to/create/a/big/sitemap/without/reaching/the/50000/urls/limit/'.$iItemId,
		null,
		cXmlSitemap::changeFreqHourly,
		0.8
	);
}

// close the creator and the last sitemap
$oSitemapCreator->save();

// Add sitemap to index
$oSitemapCreator->updateSitemapIndex(cXmlSitemapConfig::getSitemapDirectoryPath().cXmlSitemapConfig::getSitemapIndexFilename());

