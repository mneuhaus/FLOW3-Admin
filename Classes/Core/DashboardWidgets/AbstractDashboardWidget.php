<?php

namespace Admin\Core\DashboardWidgets;

/* *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Abstract validator
 *
 * @version $Id: AbstractValidator.php 3837 2010-02-22 15:17:24Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
abstract class AbstractDashboardWidget implements DashboardWidgetInterface {
	
	/**
	 * @var \Admin\Core\Helper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $helper;
	
	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @FLOW3\Inject
	 */
	protected $objectManager;
	
	/**
	 * Reflection service
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $reflectionService;
	
	/**
	 * @var \TYPO3\Fluid\Core\Parser\TemplateParser
	 * @FLOW3\Inject
	 */
	protected $templateParser;
	
	
	public function getName(){
		$name = \Admin\Core\Helper::getShortName(get_class($this));
		$name = str_replace("Widget", "", $name);
		return $name;
	}
	
	public function initializeWidget() {
	}
	
	public function __construct($view, \Admin\Core\Helper $helper, \TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager){
		$this->helper = $helper;
		$this->view = $view;
		$this->objectManager = $objectManager;
		
		$partial = $this->getName();
	
		$replacements = array(
			"@partial" => $partial,
			"@package" => $this->helper->getPackageByClassName(get_class($this)),
			"@being" => \Admin\Core\Helper::getShortName(\Admin\Core\API::get("being")),
			"@action" => $partial
		);
	
		$template = $this->helper->getPathByPatternFallbacks("DashboardWidgets",$replacements);
		
		$this->view->setTemplatePathAndFilename($template);
		
		$this->initializeWidget();
	}
	
	public function __toString(){
		return $this->view->render();
	}
	
	protected function parseTemplate($templatePathAndFilename) {
		$templateSource = \TYPO3\FLOW3\Utility\Files::getFileContents($templatePathAndFilename, FILE_TEXT);
		if ($templateSource === FALSE) {
			throw new \TYPO3\Fluid\View\Exception\InvalidTemplateResourceException('"' . $templatePathAndFilename . '" is not a valid template resource URI.', 1257246929);
		}
		return $this->templateParser->parse($templateSource);
	}

	/**
	 * Build the rendering context
	 *
	 * @param \TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer $variableContainer
	 * @return \TYPO3\Fluid\Core\Rendering\RenderingContext
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function buildRenderingContext(\TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer $variableContainer = NULL) {
		if ($variableContainer === NULL) {
			$variableContainer = $this->objectManager->get('TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer', $this->variables);
		}
		
		$renderingContext = $this->objectManager->get('TYPO3\Fluid\Core\Rendering\RenderingContext');
		$renderingContext->injectTemplateVariableContainer($variableContainer);
		if ($this->controllerContext !== NULL) {
			$renderingContext->setControllerContext($this->controllerContext);
		}
		
		$viewHelperVariableContainer = $this->objectManager->get('TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$viewHelperVariableContainer->setView($this->viewHelperVariableContainer->getView());
		$renderingContext->injectViewHelperVariableContainer($viewHelperVariableContainer);
		
		return $renderingContext;
	}
}
?>