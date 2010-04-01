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
 * @subpackage Benchmark
 * @license    http://www.myhammer.de/opensource/license/gpl.txt GNU General Public License Version 3
 * @version    1.0
 * @author     Jan Christiansen <christiansen@myhammer.de>
 */

/**
 * Actual benchmark
 * 
 * @package    LargeXMLSitemap
 * @subpackage Benchmark
 */
class cXmlSitemapBenchmark {
	/**
	 * this benchmark
	 */
	public $sClassname;
	public $aMeasuredTimes;
	public $fAverage;
	public $fAveragePerUrl;
	public $fMin;
	public $fMax;

	/**
	 * generator config
	 */
	public $sFilename = 'bm';
	public $bCompress = TRUE;
	public $sDirectory = '.';

	/**
	 * benchmarks config
	 */
	public $iNumOfUrls;
	public $iNumOfMeasurements;
	public $bVerbose = FALSE;

	public $iAveragePerUrlBase = 100;
	
	/**
	 * @var cXmlSitemapGeneratorBase
	 */
	private $oCurrentGenerator;
	private $iCurrentMeasurement = 0;
	private $aUrls = array();

	/**
	 * @param string $sClassName
	 * @return void
	 */
	public function __construct($sClassName) {
		$this->sClassname = $sClassName;
	}

	/**
	 * starts the benchmark, calculates and prints the output
	 * @return void
	 */
	public function main() {
		$this->generateUrls();
		
		for ($this->iCurrentMeasurement = 0; $this->iCurrentMeasurement < $this->iNumOfMeasurements; $this->iCurrentMeasurement++) {
			$this->initializeMeasurement();

			$fStart = microtime(TRUE);
			$this->addUrls();
			$fEnd = microtime(TRUE);

			$this->finalizeMeasurement($fStart, $fEnd);
		}
		
		$this->calculateResult();
		$this->outputResult();
	}

	/**
	 * sets all requirements for the benchmark
	 * @return void
	 */
	private function initializeMeasurement() {
		reset($this->aUrls);
		$sClassName = $this->sClassname;
		$this->oCurrentGenerator = new $sClassName($this->sFilename.'_'.$sClassName, $this->bCompress, $this->sDirectory);
		$this->oCurrentGenerator->deleteCurrent();
		$this->oCurrentGenerator->open();
	}

	/**
	 * adds the urls to the generator
	 * @return void
	 */
	private function addUrls() {
		foreach ($this->aUrls as $iItemId) {
			$this->oCurrentGenerator->addUrl(
				'http://jc.bhn-media.de/shop/item/very/long/url/because/we/need/to/create/a/big/sitemap/without/reaching/the/50000/urls/limit/'.$iItemId,
				null,
				cXmlSitemap::changeFreqHourly,
				0.8
			);
		}
	}

	/**
	 * saves the sitemap, adds to index sitemap and calculates the time of this measurement
	 * @param float $fStart
	 * @param float $fEnd
	 * @return void
	 */
	private function finalizeMeasurement($fStart, $fEnd) {
		$iMeasuredTime = $fEnd - $fStart;
		$this->aMeasuredTimes[] = $iMeasuredTime;

		$this->oCurrentGenerator->save();
		$this->oCurrentGenerator->updateSitemapIndex($this->sDirectory.'index_sitemaps.xml');

		if ($this->bVerbose === TRUE) {
			echo "\n".$this->iCurrentMeasurement.":\t".$iMeasuredTime;
		} else {
			echo '.';
		}
	}

	/**
	 * calculates the result of the benchmark
	 * 
	 * @return void
	 */
	private function calculateResult() {
		$fAverage = array_sum($this->aMeasuredTimes) / count($this->aMeasuredTimes);
		$this->fAverage 		= round($fAverage, 5);
		$this->fMin 			= round(min($this->aMeasuredTimes), 5);
		$this->fMax 			= round(max($this->aMeasuredTimes), 5);
		$this->fAveragePerUrl	= round($fAverage / ($this->iNumOfUrls / $this->iAveragePerUrlBase), 5);
	}

	/**
	 * displays the benchmark result
	 *
	 * @return void
	 */
	private function outputResult() {
		$sAverage 		= str_pad($this->fAverage, 10, ' ', STR_PAD_RIGHT);
		$sMin 			= str_pad($this->fMin, 10, ' ', STR_PAD_RIGHT);
		$sMax 			= str_pad($this->fMax, 10, ' ', STR_PAD_RIGHT);
		$sAveragePerUrl	= str_pad($this->fAveragePerUrl, 10, ' ', STR_PAD_RIGHT);

		echo "\n".$this->sClassname."\n";
		echo "\tAverage: ".$sAverage."Min: ".$sMin."Max: ".$sMax."Av/".$this->iAveragePerUrlBase.": ".$sAveragePerUrl."\n";
	}

	/**
	 * generates the urls. really simple.. but could do more ;)
	 * @return void
	 */
	private function generateUrls() {
		$this->aUrls = array();
		for ($i = 0; $i < $this->iNumOfUrls; $i++) {
			$this->aUrls[] = 'http://www.google.de/boring/url'.$i;
		}
	}
}
