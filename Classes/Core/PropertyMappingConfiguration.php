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


/**
 * Concrete configuration object for the PropertyMapper.
 *
 * @api
 */
class PropertyMappingConfiguration extends \TYPO3\FLOW3\Property\PropertyMappingConfiguration {
	/**
	 * Returns the sub-configuration for the passed $propertyName. Must ALWAYS return a valid configuration object!
	 *
	 * @param string $propertyName
	 * @return \TYPO3\FLOW3\Property\PropertyMappingConfigurationInterface the property mapping configuration for the given $propertyName.
	 * @api
	 */
	public function getConfigurationFor($propertyName) {
		if (isset($this->subConfigurationForProperty[$propertyName])) {
			return $this->subConfigurationForProperty[$propertyName];
		}

		$subConfigurationForProperty = new PropertyMappingConfiguration();
		$subConfigurationForProperty->allowAllProperties();
		$subConfigurationForProperty->setTypeConverterOption('TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter', \TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);
        $subConfigurationForProperty->setTypeConverterOption('TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter', \TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, TRUE);
		return $subConfigurationForProperty;
	}
	
	static public function getConfiguration($type = '\Admin\Core\PropertyMappingConfiguration', $options = array()){
		$configuration = new $type();

		$configuration->setTypeConverterOptions('TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter', array(
			\TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED => TRUE,
			\TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED => TRUE
		));

		$configuration->setTypeConverterOption('TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter', \TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);
        $configuration->setTypeConverterOption('TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter', \TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, TRUE);
        $configuration->allowAllProperties();

		foreach($options as $option){
		    $configuration->setTypeConverterOption($option[0], $option[1], $option[2]);
		}

		return $configuration;
	}
}
?>