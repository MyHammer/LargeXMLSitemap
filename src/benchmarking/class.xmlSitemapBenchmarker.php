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
 * @subpackage Benchmark
 * @license    http://www.myhammer.de/opensource/license/gpl.txt GNU General Public License Version 3
 * @version    1.0
 * @author     Jan Christiansen <christiansen@myhammer.de>
 */

/**
 * Benchmark launcher.
 * Launches the benchmarks as separate processes, so every benchmark
 * has the same preconditions
 *
 * @package    LargeXMLSitemap
 * @subpackage Benchmark
 */
class cXmlSitemapBenchmarker {
	/**
	 * benchmarks config
	 */
	public $iNumOfMeasurements;
	public $sVerbose = FALSE;

	/**
	 * @var cXmlSitemapBenchmark
	 */
	private $aBenchmarks = array();

	/**
	 * starts the benchmarks and prints the results
	 * 
	 * @return void
	 */
	public function main() {
		for($i = 0; $i < count($this->aBenchmarks); $i++) {
			$this->executeBenchmark($i);
		}

		echo "\n\nResults:\n\n";
		for($i = 0; $i < count($this->aBenchmarks); $i++) {
			echo $this->aBenchmarks[$i]['classname'] . ": " . $this->aBenchmarks[$i]['numberOfUrls'] . " URLs\n";
			echo $this->aBenchmarks[$i]['output'];
		}
	}

	/**
	 * executes the benchmark as separate process
	 *
	 * @param int $i
	 * @return void
	 */
	private function executeBenchmark($i) {
		$aOutput = array();
		$rHandle = popen('php5 internal.run_benchmark.php ' . escapeshellarg($this->aBenchmarks[$i]['classname']) .
				' ' . escapeshellarg($this->iNumOfMeasurements) .
				' ' . escapeshellarg($this->aBenchmarks[$i]['numberOfUrls']) .
				' ' . escapeshellarg($this->sVerbose),
			'r');
		while (!feof($rHandle)) {
			$sOutput = fread($rHandle, 1024);
			$aOutput[] = $sOutput;
			echo $sOutput;
		}
		pclose($rHandle);
		$aOutput = array_filter($aOutput);
		$this->aBenchmarks[$i]['output'] = $aOutput[count($aOutput)-1];
	}

	/**
	 * adds a benchmark
	 *
	 * @param int $sClassName
	 * @param int $iNumberOfUrls
	 * @return void
	 */
	public function addBenchmark($sClassName, $iNumberOfUrls) {
		$this->aBenchmarks[] = array(
			'classname' => $sClassName,
			'numberOfUrls' => $iNumberOfUrls
		);
	}
}

