<?php

namespace Admin\Core;

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
 * represents a properties value
 * 
 * @version $Id: ForViewHelper.php 3346 2009-10-22 17:26:10Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @FLOW3\Scope("prototype")
 */
class Value{
	/**
	 * @var \Admin\Core\PropertyMapper
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $propertyMapper;

	protected $parentProperty;

	public function  __construct($parentProperty) {
		$this->parentProperty = $parentProperty;
	}

	public function  __toString() {
		$value = $this->parentProperty->getValue();
		$options = array(
		    array(
			'TYPO3\FLOW3\Property\TypeConverter\DateTimeConverter',
			\TYPO3\FLOW3\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT,
			$this->parentProperty->representation->datetimeFormat
		    )
		);
		return $this->propertyMapper->convert($value, "string", \Admin\Core\PropertyMappingConfiguration::getConfiguration('\Admin\Core\PropertyMappingConfiguration', $options));
	}

	public function getValue() {
		return $this->parentProperty->getValue();
	}

	public function getIds(){
		$value = $this->getValue();
		$ids = array();
		if( \Admin\Core\Helper::isIteratable($value) ){
			foreach($value as $object){
				$ids[] = $this->parentProperty->adapter->getId($object);
			}
		}else if (is_object($value)){
			$ids[] = $this->parentProperty->adapter->getId($value);
		}
		return $ids;
	}
}

?>