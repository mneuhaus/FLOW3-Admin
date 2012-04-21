<?php
namespace Admin\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 *
 * @api
 */
class LessViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {
	/**
	 * @var \Admin\Core\CacheManager
	 * @FLOW3\Inject
	 */
	protected $cacheManager;

	/**
	 * @var \TYPO3\Fluid\ViewHelpers\Uri\ResourceViewHelper
	 */
	protected $resourceViewHelper;

	/**
	 * @param \TYPO3\Fluid\ViewHelpers\Uri\ResourceViewHelper $resourceViewHelper
	 * @return void
	 */
	public function injectResourcePublisher(\TYPO3\Fluid\ViewHelpers\Uri\ResourceViewHelper $resourceViewHelper) {
		$this->resourceViewHelper = $resourceViewHelper;
	}

	/**
	 * @var string
	 */
	protected $tagName = 'link';

	/**
	 * Initialize arguments
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		$this->registerUniversalTagAttributes();
		$this->registerTagAttribute('rel', 'string', 'Specifies the relationship between the current document and the linked stylesheet');
	}

	/**
	 * Render the link.
	 *
	 * @param string $src Less file to Compile
	 * @return string The rendered link
	 * @api
	 */
	public function render($src) {
		$target = str_replace(".less", ".css", $src);

		$cache = $this->cacheManager->getCache('Admin_Cache');
		$identifier = $this->cacheManager->createIdentifier($src);
		if(!$cache->has($identifier)){
			$compilationCache = \lessc::cexecute($src, true);
			file_put_contents($target, $compilationCache['compiled']);
			$cache->set($identifier, $compilationCache);
		}else{
			$compilationCache = $cache->get($identifier);
			// the next time we run, write only if it has updated
			$last_updated = $compilationCache['updated'];
			$cache = \lessc::cexecute($compilationCache);
			if ($compilationCache['updated'] > $last_updated) {
				file_put_contents($css_file, $compilationCache['compiled']);
			}
		}
		
		$uri = $this->resourceViewHelper->render(null, null, null, $target);
		
		$this->tag->addAttribute("rel", "stylesheet");
		$this->tag->addAttribute("href", $uri);
		return $this->tag->render();
	}
}


?>