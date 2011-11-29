<?php

namespace Admin\ViewHelpers\Query;

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
class FilterViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @var \TYPO3\FLOW3\Configuration\ConfigurationManager
	 * @FLOW3\Inject
	 */
	protected $configurationManager;
	
	/**
	 * @var \Admin\Core\Helper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $helper;
	
	/**
	 *
	 * @param mixed $objects
	 * @param string $as
	 * @param string $filtersAs
	 * @return string Rendered string
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @api
	 */
	public function render($objects = null, $as = "filteredObjects", $filtersAs = "filters") {
		$this->objects = $objects;
		$this->query = $objects->getQuery();
		
		$this->request = $this->controllerContext->getRequest();
		
		$filters = $this->handleFilters();
		
		$this->templateVariableContainer->add($filtersAs, $filters);
		$this->templateVariableContainer->add($as, $this->query->execute());
		$content = $this->renderChildren();
		$this->templateVariableContainer->remove($filtersAs);
		$this->templateVariableContainer->remove($as);
		
		return $content;
	}
	
	public function handleFilters(){
		if( $this->request->hasArgument("filters") ) {
			$filters = $this->request->getArgument("filters");
			foreach ($filters as $key => $value)
				if(!empty($value))
					$this->query->matching($this->query->equals($key, $value));
			
			return $this->getFilter($filters);
		}else {
			return $this->getFilter();
		}
	}
	
	public function getFilter($selected = array()){
		$filters = array();
		foreach ($this->objects as $object) {
			$being = $this->helper->getBeing($object);
			
			foreach($being->properties as $property){
				if(isset($property->_filter)){
					if(!isset($filters[$property->name]))
						$filters[$property->name] = new \Admin\Core\Filter();

					if(isset($selected[$property->name]) && $selected[$property->name] == $property->__toString())
						$property->selected = true;
					
					#$string = $property->getString();
					#if(!empty($string))
						$filters[$property->name]->addProperty($property);
				}
			}
		}
		return $filters;
	}
}

?>