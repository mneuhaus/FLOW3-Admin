<?php
namespace TYPO3\FLOW3\Cache;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \TYPO3\FLOW3\Cache\Frontend\FrontendInterface;

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * The Cache Manager
 *
 * @FLOW3\Scope("singleton")
 * @api
 */
class CacheManager {

	/**
	 * Flushes entries tagged with class names if their class source files have changed.
	 *
	 * This method is used as a slot for a signal sent by the class file monitor defined
	 * in the bootstrap.
	 *
	 * @param string $fileMonitorIdentifier Identifier of the File Monitor (must be "FLOW3_ClassFiles")
	 * @param array $changedFiles A list of full paths to changed files
	 * @return void
	 */
	public function flushCachesByChangedFiles($fileMonitorIdentifier, array $changedFiles) {
		if ($fileMonitorIdentifier !== 'FLOW3_ClassFiles') {
			return;
		}
		
		// $this->flushCachesByTag(self::getClassTag());
		// foreach ($changedFiles as $pathAndFilename => $status) {
		// 	$pathAndFilename = str_replace(FLOW3_PATH_PACKAGES, '', $pathAndFilename);
		// 	$matches = array();
		// 	if (1 === preg_match('/[^\/]+\/(.+)\/(Classes|Tests)\/(.+)\.php/', $pathAndFilename, $matches)) {
		// 		$className = str_replace('/', '\\', $matches[1] . '\\' . ($matches[2] === 'Tests' ? 'Tests\\' : '') . $matches[3]);
		// 		$className = str_replace('.', '\\', $className);
		// 		$this->flushCachesByTag(self::getClassTag($className));
		// 	}
		// }
	}
	
}
?>
