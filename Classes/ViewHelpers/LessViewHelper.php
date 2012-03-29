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


/**
 *
 * @api
 */
class LessViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {
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
		
		\lessc::ccompile($src, $target, 1);
		
		$uri = $this->resourceViewHelper->render(null, null, null, $target);
		
		$this->tag->addAttribute("rel", "stylesheet");
		$this->tag->addAttribute("href", $uri);
		return $this->tag->render();
	}
}


?>