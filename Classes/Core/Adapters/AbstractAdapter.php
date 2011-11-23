<?php

namespace Admin\Core\Adapters;

/*                                                                        *
 * This script belongs to the FLOW3 package "Contacts".                   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * abstract base class for the Adapters
 *
 * @version $Id: AbstractValidator.php 3837 2010-02-22 15:17:24Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
abstract class AbstractAdapter implements \Admin\Core\Adapters\AdapterInterface {
	/**
	 * @var TYPO3\FLOW3\Cache\CacheManager
	 * @FLOW3\Inject
	 */
	protected $cacheManager;
	
	/**
	 * @var \Admin\Core\ConfigurationManager
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
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $objectManager;
	
	/**
	 * @var \TYPO3\FLOW3\Package\PackageManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $packageManager;
	
	/**
	 * @var TYPO3\FLOW3\Property\PropertyMapper
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $propertyMapper;
	
	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $reflectionService;

	/**
	 * Initialize the Adapter
	 *
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function init() {
		$this->settings = $this->helper->getSettings("Admin");
	}
	
	/**
	 * Gets the Processed Being
	 *
	 * @param string $being Name of Class of the Being
	 * @param string $id Identifier of the Being
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function getBeing($being, $id = null) {
		$being = ltrim($being, "\\");
		
		$b = new \Admin\Core\Being($this);
		if($id !== null){
			$b->setObject($this->getObject($being, $id));
		}
		$b->setClass($being);
		
		return $b;
	}
	
	/**
	 * Gets multiple Processed Beings
	 *
	 * @param string $being Name of Class of the Being
	 * @author Marc Neuhaus <mneuhaus@famelo.com>
	 * */
	public function getBeings($being, $filters = null) {
		
		$objects = $this->getObjects($being);
		$beings = array();
		if(!empty($objects)){
			foreach($objects as $object) {
				$b = $this->getBeing($being,$this->getId($object));
				$beings[] = $b;
			}
		}
		
		if($filters != null)
			$beings = $this->applyFilters($beings,$filters);
		
		return $beings;
	}
	
	public function getFilter($being,$selected = array()){
		$beings = $this->getBeings($being);
		$filters = array();
		foreach($beings as $being){
			$properties = $being->properties;
			foreach($properties as $property){
				if($property->isFilter()){
					if(!isset($filters[$property->getName()]))
						$filters[$property->getName()] = new \Admin\Core\Filter();

					if(isset($selected[$property->getName()]) && $selected[$property->getName()] == $property->getString()){
						$property->setSelected(true);
					}
					#$string = $property->getString();
					#if(!empty($string))
						$filters[$property->getName()]->addProperty($property);
				}
			}
		}
		return $filters;
	}
	
	/**
	 * returns the specified property of the mixed variable
	 *
	 * @param string $property 
	 * @param string $mixed 
	 * @return void
	 * @author Marc Neuhaus
	 */
	public function getValue($property, $mixed){
		$value = null;
		try {
			if(is_object($mixed) || is_array($mixed))
				$value = \TYPO3\FLOW3\Reflection\ObjectAccess::getProperty($mixed, $property);
		} catch(\TYPO3\FLOW3\Reflection\Exception\PropertyNotAccessibleException $e) {
			var_dump($e);
		}
		return $value;
	}
}

?>