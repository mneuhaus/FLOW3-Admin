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
 *
 * @api
 * @FLOW3\Scope("singleton")
 * @todo Implement functionality for converting collection properties.
 */
class CollectionConverter extends \TYPO3\FLOW3\Property\TypeConverter\AbstractTypeConverter {

	/**
	 * @var string
	 */
	protected $sourceTypes = array('object', 'array', 'ArrayObject', 'SplObjectStorage', 'Doctrine\Common\Collections\Collection', 'Doctrine\Common\Collections\ArrayCollection', 'Doctrine\ORM\PersistentCollection');
	
	/**
	 * @var string
	 */
	protected $targetType = 'string';

	/**
	 * @var integer
	 */
	protected $priority = 51;
	
	/**
	 * @var \Admin\Core\PropertyMapper
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $propertyMapper;
	
	/**
	 * This implementation always returns TRUE for this method.
	 *
	 * @param mixed $source the source data
	 * @param string $targetType the type to convert to.
	 * @return boolean TRUE if this TypeConverter can convert from $source to $targetType, FALSE otherwise.
	 * @api
	 */
	public function canConvertFrom($source, $targetType) {
		if(is_array($source))
			return TRUE;
		return in_array(get_class($source), $this->sourceTypes);
	}
	
	/**
	 * Actually convert from $source to $targetType, taking into account the fully
	 * built $convertedChildProperties and $configuration.
	 *
	 * @param mixed $source
	 * @param string $targetType
	 * @param array $convertedChildProperties
	 * @param \TYPO3\FLOW3\Property\PropertyMappingConfigurationInterface $configuration
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 * @api
	 */
	public function convertFrom($sources, $targetType, array $convertedChildProperties = array(), \TYPO3\FLOW3\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		return implode(", ",$convertedChildProperties);
	}
	
	/**
	 * Returns the source, if it is an array, otherwise an empty array.
	 *
	 * @return array
	 * @api
	 */
	public function getSourceChildPropertiesToBeConverted($source) {
		$array = array();
		
		foreach ($source as $key => $value) {
			$array[$key] = $value;
		}
		
		return $array;
	}

	/**
	 * Return the type of a given sub-property inside the $targetType
	 *
	 * @param string $targetType
	 * @param string $propertyName
	 * @param \TYPO3\FLOW3\Property\PropertyMappingConfigurationInterface $configuration
	 * @return string
	 * @api
	 */
	public function getTypeOfChildProperty($targetType, $propertyName, \TYPO3\FLOW3\Property\PropertyMappingConfigurationInterface $configuration) {
		$parsedTargetType = \TYPO3\FLOW3\Utility\TypeHandling::parseType($targetType);
		if(is_null($parsedTargetType['elementType']))
			return $parsedTargetType["type"];
		else
			return $parsedTargetType['elementType'];
	}
}
?>