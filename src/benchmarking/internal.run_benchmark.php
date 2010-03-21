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
 * This file runs the benchmark. There is no check if input is valid and the Class exits
 * Its just called from the actual benchmarks
 */

require_once('../init/init.main.php');
require_once('class.xmlSitemapBenchmark.php');
set_time_limit(0);

if(count($argv) < 4 || count($argv) > 5) {
	echo "Usage ".$argv[0]." <class name> <number of measurements> <number of URLs> [v(erbose)]\n";
	die();
}

cXmlSitemapConfig::loadConfig();

$oBenchmark = new cXmlSitemapBenchmark($argv[1]);
$oBenchmark->iNumOfMeasurements = $argv[2];
$oBenchmark->iNumOfUrls = $argv[3];
if(isset($argv[4]) && $argv[4] == 'v') {
	$oBenchmark->bVerbose = TRUE;
}

$oBenchmark->main();
