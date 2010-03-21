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
 * @subpackage Benchmarking
 * @license    http://www.myhammer.de/opensource/license/gpl.txt GNU General Public License Version 3
 * @version    1.0
 * @author     Jan Christiansen <christiansen@myhammer.de>
 */

/**
 * Benchmark 2
 * Creates sitemaps with both classes, multiplying number of Urls,
 * to show that the cXmlSitemapGenerator, which uses the PHP DOM methods,
 * are using exponential more time with every url added, whereas the time
 * needed by cXmlSitemapGeneratorWrite is linear.
 */


require_once('../init/init.main.php');
require_once('class.xmlSitemapBenchmarker.php');

set_time_limit(0);
$iNumberOfUrls;
$iNumberOfBenchmarks;
$oBenchmark = new cXmlSitemapBenchmarker();

if (count($argv) > 3) {
	$oBenchmark->iNumOfMeasurements = $argv[1];
	$iNumberOfUrls = $argv[2];
	$iNumberOfBenchmarks = $argv[3];
	echo "Running " . $oBenchmark->iNumOfMeasurements . " measurements per benchmark, " . $iNumberOfUrls . " - " . $iNumberOfUrls * $iNumberOfBenchmarks . " URLs per measurement\n";
} else {
	echo "Please provide number of measurements, URLs per measurement and benchmarks\n";
	echo "Example: " . $argv[0] . " 30 5000 10\n";
	die();
}

for($i = 1; $i <= $iNumberOfBenchmarks; $i++) {
	$oBenchmark->addBenchmark('cXmlSitemapGenerator', $iNumberOfUrls * $i);
	$oBenchmark->addBenchmark('cXmlSitemapGeneratorWrite', $iNumberOfUrls * $i);
}

$oBenchmark->main();
