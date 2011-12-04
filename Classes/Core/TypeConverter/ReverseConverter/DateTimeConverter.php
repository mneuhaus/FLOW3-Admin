<?php
namespace Admin\Core\TypeConverter\ReverseConverter;

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
 * @api
 * @FLOW3\Scope("singleton")
 */
class DateTimeConverter extends \TYPO3\FLOW3\Property\TypeConverter\DateTimeConverter {
	
	/**
	 * @var array<string>
	 */
	protected $sourceTypes = array("object");
	
	/**
	 * @var string
	 */
	protected $targetType = 'string';
	
	/**
	 * @var integer
	 */
	protected $priority = 30;
	
	/**
	 * Empty strings can't be converted
	 *
	 * @param string $source
	 * @param string $targetType
	 * @return boolean
	 */
	public function canConvertFrom($source, $targetType) {
		if(is_object($source) && $source instanceof \DateTime && $targetType == "string"){
			return TRUE;
		}
	}

	/**
	 * Converts $source to a \DateTime using the configured dateFormat
	 *
	 * @param string $source the string to be converted to a \DateTime object
	 * @param string $targetType must be "DateTime"
	 * @param array $convertedChildProperties not used currently
	 * @param \TYPO3\FLOW3\Property\PropertyMappingConfigurationInterface $configuration
	 * @return \DateTime
	 */
	public function convertFrom($source, $targetType, array $convertedChildProperties = array(), \TYPO3\FLOW3\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		if(is_object($source) && $source instanceof \DateTime && $targetType == "string"){
			return $source->format($this->getDefaultDateFormat($configuration));
		}
	}
	
}
?>