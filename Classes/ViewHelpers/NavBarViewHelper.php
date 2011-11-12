<?php

namespace Admin\ViewHelpers;

/*                                                                        *
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

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @version $Id: ForViewHelper.php 3346 2009-10-22 17:26:10Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @FLOW3\Scope("prototype")
 */
class NavBarViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {
	
	/**
	 * @var \TYPO3\FLOW3\Configuration\ConfigurationManager
	 * @FLOW3\Inject
	 */
	protected $configurationManager;
	
	/**
	 * @var array
	 */
	protected $defaults = array(
		"Arguments" => array(),
		"Controller"=> null,
		"Package"	=> null,
		"Subpackage"=> null,
		"Children"	=> array()
	);
	
	/**
	 *
	 * @param mixed $items
	 * @param string $namespace
	 * @param string $as
	 * @return string Rendered string
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @api
	 */
	public function render($items = false, $namespace = "Admin.NavBar", $as = "navBar") {
		if($items == false)
			$items = $this->configurationManager->getConfiguration(\TYPO3\FLOW3\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, $namespace);
		
		$content = "";
		foreach ($items as $name => $arguments) {
			$arguments = array_merge($this->defaults, $arguments);
			
			
			$variables = array(
				"link" => $this->getLink($arguments["Action"], $arguments["Arguments"], $arguments["Controller"], $arguments["Package"], $arguments["Subpackage"]),
				"name" => $name,
				"hasChildren" => false,
				"arguments" => $arguments,
				"children" => array()
			);
			
			if(count($arguments["Children"]) > 0){
				$variables["children"] = $this->render($arguments["Children"], $namespace, $as);
				$variables["hasChildren"] = true;
			}
			
			$this->templateVariableContainer->add($as, $variables);
			$content.= $this->renderChildren();
			$this->templateVariableContainer->remove($as);
		}
		return $content;
	}
	
	public function getLink($action, $arguments=array(), $controller = null, $package = null, $subpackage = null){
		$uriBuilder = $this->controllerContext->getUriBuilder();
		try {
			$uri = $uriBuilder
				->reset()
				->setCreateAbsoluteUri(TRUE)
				->uriFor($action, $arguments, $controller, $package, $subpackage);
			return $uri;
		} catch (\TYPO3\FLOW3\Exception $exception) {
			throw new \TYPO3\Fluid\Core\ViewHelper\Exception($exception->getMessage(), $exception->getCode(), $exception);
		}
	}
}

?>