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
 * @subpackage Benchmarking
 * @license    http://www.myhammer.de/opensource/license/gpl.txt GNU General Public License Version 3
 * @version    1.0
 * @author     Jan Christiansen <christiansen@myhammer.de>
 */

/**
 * Benchmark 1
 * Creates the same sitemaps with cXmlSitemapGenerator and cXmlSitemapGeneratorWrite.
 * The results shows that, cXmlSitemapGeneratorWrite is a lot faster especially if
 * there a lot of Urls 
 */

require_once('../init/init.main.php');
require_once('class.xmlSitemapBenchmarker.php');

set_time_limit(0);
$oBenchmark = new cXmlSitemapBenchmarker();

if (count($argv) > 2) {
	$oBenchmark->iNumOfMeasurements = $argv[1];
	echo "Running ".$oBenchmark->iNumOfMeasurements." measurements per benchmark, ".$argv[2]." URLs per measurement\n";
} else {
	echo "Please provide number of measurements and number of URLs per measurement\n";
	echo "Example: ".$argv[0]." 30 50000\n";
	die();
}

$oBenchmark->addBenchmark('cXmlSitemapGenerator', $argv[2]);
$oBenchmark->addBenchmark('cXmlSitemapGeneratorWrite', $argv[2]);

$oBenchmark->main();
