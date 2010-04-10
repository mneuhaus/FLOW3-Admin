<?php

namespace F3\Admin;

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

/**
 * A controller argument
 *
 * @version $Id: Argument.php 4018 2010-03-29 08:21:36Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class Argument extends \F3\FLOW3\MVC\Controller\Argument{
	/**
	 * Checks if the value is a UUID or an array but should be an object, i.e.
	 * the argument's data type class schema is set. If that is the case, this
	 * method tries to look up the corresponding object instead.
	 *
	 * Additionally, it maps arrays to objects in case it is a normal object.
	 *
	 * @param mixed $value The value of an argument
	 * @return mixed
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	
	/**
	 * @var \F3\FLOW3\Object\ObjectFactoryInterface
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $objectFactory;
	
	/**
	 * Injects the FLOW3 Property Mapper
	 *
	 * @param \F3\FLOW3\Property\PropertyMapper $propertyMapper
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function replacePropertyMapper($propertyMapper) {
		$this->propertyMapper = $propertyMapper;
	}
	
	public function setTarget($target){
		$this->target = $target;
	}
	
	protected function transformValue($value) {
		if ($value === NULL) {
			return NULL;
		}
		if (!class_exists($this->dataType)) {
			return $value;
		}
		if(isset($this->target))
			$transformedValue = $this->target;
		else if(array_key_exists("__identity",$value))
			$transformedValue = clone $this->persistenceManager->getObjectByIdentifier($value["__identity"]);
		else
			$transformedValue = $this->objectFactory->create($this->dataType);
			
		if ($this->dataTypeClassSchema !== NULL) {
				// The target object is an Entity or ValueObject.
			if (is_string($value) && preg_match(self::PATTERN_MATCH_UUID, $value) === 1) {
				$this->origin = self::ORIGIN_PERSISTENCE;
				$transformedValue = $this->persistenceManager->getObjectByIdentifier($value);
			} elseif (is_array($value)) {
				if (array_keys($value) === array('__identity')) { // If there is only an __identity array _and nothing else_, then the property mapper will not clone the object.
					$this->origin = self::ORIGIN_PERSISTENCE;
				} else {
					$this->origin = self::ORIGIN_PERSISTENCE_AND_MODIFIED;
				}
				$this->propertyMapper->mapAndValidate(array_keys($value), $value, $transformedValue, array(), $this->validator);
			}
		} else {
			if (!is_array($value)) {
				throw new \F3\FLOW3\MVC\Exception\InvalidArgumentValueException('The value was a simple type, so we could not map it to an object. Maybe the @entity or @valueobject annotations are missing?', 1251730701);
			}
			$this->origin = self::ORIGIN_NEWLY_CREATED;
			$this->propertyMapper->mapAndValidate(array_keys($value), $value, $transformedValue, array(), $this->validator);
		}
		
		if (!($transformedValue instanceof $this->dataType)) {
			throw new \F3\FLOW3\MVC\Exception\InvalidArgumentValueException('The value must be of type "' . $this->dataType . '", but was of type "' . (is_object($transformedValue) ? get_class($transformedValue) : gettype($transformedValue)) . '".', 1269616784);
		}
		return $transformedValue;
	}
}
?>