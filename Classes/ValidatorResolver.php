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
 * Validator resolver to automatically find a appropriate validator for a given subject
 *
 * @version $Id: ValidatorResolver.php 4013 2010-03-25 16:49:27Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ValidatorResolver extends \F3\FLOW3\Validation\ValidatorResolver{
	/**
	 * Builds a base validator conjunction for the given data type.
	 *
	 * The base validation rules are those which were declared directly in a class (typically
	 * a model) through some @validate annotations on properties.
	 *
	 * Additionally, if a custom validator was defined for the class in question, it will be added
	 * to the end of the conjunction. A custom validator is found if it follows the naming convention
	 * "Replace '\Model\' by '\Validator\' and append "Validator".
	 *
	 * Example: $dataType is F3\Foo\Domain\Model\Quux, then the Validator will be found if it has the
	 * name F3\Foo\Domain\Validator\QuuxValidator
	 *
	 * @param string $targetClassName The data type to build the validation conjunction for. Needs to be the fully qualified class name.
	 * @return F3\FLOW3\Validation\Validator\ConjunctionValidator The validator conjunction or NULL
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getModelValidator($targetClassName) {
			// Model based validator
		if (class_exists($targetClassName)) {
			$validatorCount = 0;
			$objectValidator = $this->createValidator('F3\FLOW3\Validation\Validator\GenericObjectValidator');

			foreach ($this->reflectionService->getClassPropertyNames($targetClassName) as $classPropertyName) {
				$classPropertyTagsValues = $this->reflectionService->getPropertyTagsValues($targetClassName, $classPropertyName);
				
				if(array_key_exists("var",$classPropertyTagsValues)){
					$propertyTargetClassName = trim(implode('' , $classPropertyTagsValues['var']));
					if (class_exists($propertyTargetClassName)) {
						$subObjectValidator = $this->buildBaseValidatorConjunction($propertyTargetClassName);
						if ($subObjectValidator !== NULL) {
							$objectValidator->addPropertyValidator($classPropertyName, $subObjectValidator);
						}
					}
				}

				if (!isset($classPropertyTagsValues['validate'])) continue;

				foreach ($classPropertyTagsValues['validate'] as $validateValue) {
					$parsedAnnotation = $this->parseValidatorAnnotation($validateValue);
					foreach ($parsedAnnotation['validators'] as $validatorConfiguration) {
						$newValidator = $this->createValidator($validatorConfiguration['validatorName'], $validatorConfiguration['validatorOptions']);
						if ($newValidator === NULL) {
							throw new \F3\FLOW3\Validation\Exception\NoSuchValidatorException('Invalid validate annotation in ' . $targetClassName . '::' . $classPropertyName . ': Could not resolve class name for  validator "' . $validatorConfiguration['validatorName'] . '".', 1241098027);
						}
						$objectValidator->addPropertyValidator($classPropertyName, $newValidator);
						$validatorCount ++;
					}
				}
			}
			
			if ($validatorCount > 0) return $objectValidator;
		}
		
			// Custom validator for the class
		$possibleValidatorClassName = str_replace('\\Model\\', '\\Validator\\', $targetClassName) . 'Validator';
		$customValidator = $this->createValidator($possibleValidatorClassName);
		if ($customValidator !== NULL) {
			return $customValidator;
		}
		
		return $objectValidator;
	}
}

?>