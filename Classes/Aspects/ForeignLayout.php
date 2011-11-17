<?php

namespace Admin\Aspects;

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Aspect
 */
class ForeignLayout {
	
	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $objectManager;
	
	/**
	 * Add the Annotated Method to the Navigation
	 *
	 * @param \TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint
	 * @FLOW3\Before("method(protected TYPO3\Fluid\View\TemplateView->getLayoutPathAndFilename(.*))")
	 * @return void
	 */
	public function addNavigationitem(\TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint) {
		$layout = $joinPoint->getMethodArgument("layoutName");
		if(stristr($layout, "resource://")){
			$joinPoint->getProxy()->setLayoutPathAndFilename($layout);
		}
	}

}