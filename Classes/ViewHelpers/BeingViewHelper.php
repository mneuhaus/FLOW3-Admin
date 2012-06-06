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
class BeingViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {
	
	/**
	 * @var \Admin\Core\Helper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $helper;
	
	/**
	 *
	 * @param object $object
	 * @param string $className
	 * @param string $as
	 * @param string $ignore
	 * @return string Rendered string
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @api
	 */
	public function render($object = null, $className = null, $as = "being", $ignore = null) {
		if(is_null($object) && !is_null($className))
			$object = new $className;
		
		$being = new \Admin\Core\Being($this->helper->getAdapterByBeing(get_class($object)));
		$being->ignoredProperties = $ignore;
		$being->setClass(get_class($object));
		$being->setObject($object);
		
		if($this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix'))
			$being->prefix = $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix');
		
		#$validationResults = $this->controllerContext->getRequest()->getOriginalRequestMappingResults();
		#$being->setErrors($validationResults->forProperty($being->prefix));
		
		$this->templateVariableContainer->add($as, $being);
		$content = $this->renderChildren();
		$this->templateVariableContainer->remove($as);
		return $content;
	}
}

?>