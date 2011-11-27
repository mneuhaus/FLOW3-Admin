<?php
namespace Admin\Core;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * The Property Mapper transforms simple types (arrays, strings, integers, floats, booleans) to objects or other simple types.
 * It is used most prominently to map incoming HTTP arguments to objects.
 *
 * @FLOW3\Scope("singleton")
 * @api
 */
class PropertyMapper extends \TYPO3\FLOW3\Property\PropertyMapper {
	/**
	 * Determine the type of the source data, or throw an exception if source was an unsupported format.
	 *
	 * @param mixed $source
	 * @return string the type of $source
	 */
	protected function determineSourceType($source) {
		if (is_string($source)) {
			return 'string';
		} elseif (is_array($source)) {
			return 'array';
		} elseif (is_float($source)) {
			return 'float';
		} elseif (is_integer($source)) {
			return 'integer';
		} elseif (is_bool($source)) {
			return 'boolean';
		} elseif (is_object($source)) {
			return "object";
		} else {
			throw new \TYPO3\FLOW3\Property\Exception\InvalidSourceException('The source is not of type string, array, object, float, integer or boolean, but of type "' . gettype($source) . '"', 1297773150);
		}
	}
}
?>