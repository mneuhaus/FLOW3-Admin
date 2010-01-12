<?php
 
namespace F3\Admin;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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
 * @api
 * @scope singleton
 */
class Utilities{
	/**
	 * Reflection service
	 * @var F3\FLOW3\Reflection\Service
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	private $reflection;

	/**
	 * Inject a Reflection service
	 * @param \F3\FLOW3\Reflection\Service $reflectionService Reflection service
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function injectReflection(\F3\FLOW3\Reflection\Service $reflectionService) {
		$this->reflection = $reflectionService;
	}

	/**
	 * @var \F3\FLOW3\Package\ManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	protected $packageManager;

	/**
	 * Injects the packageManager
	 *
	 * @param \F3\FLOW3\Package\ManagerInterface $packageManager
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function inject(\F3\FLOW3\Package\ManagerInterface $packageManager) {
		$this->packageManager = $packageManager;
	}

	/**
	 *
	 * @var \F3\FLOW3\Validation\ValidatorResolver
	 * @inject
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	protected $ValidatorResolver;
	
	/**
	 * Match validator names and options
	 * @var string
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	const PATTERN_MATCH_VALIDATORS = '/
			(?:^|,\s*)
			(?P<validatorName>[a-z0-9\\\\]+)
			\s*
			(?:\(
				(?P<validatorOptions>(?:\s*[a-z0-9]+\s*=\s*(?:
					"(?:\\\\"|[^"])*"
					|\'(?:\\\\\'|[^\'])*\'
					|(?:\s|[^,"\']*)
				)(?:\s|,)*)*)
			\))?
		/ixS';

	/**
	 * Match validator options (to parse actual options)
	 * @var string
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	const PATTERN_MATCH_VALIDATOROPTIONS = '/
			\s*
			(?P<optionName>[a-z0-9]+)
			\s*=\s*
			(?P<optionValue>
				"(?:\\\\"|[^"])*"
				|\'(?:\\\\\'|[^\'])*\'
				|(?:\s|[^,"\']*)
			)
		/ixS';

    /**
	 * Parses the validator options given in @validate annotations.
	 *
	 * @return array
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function parseValidatorAnnotation($validateValue) {
		$matches = array();
		if ($validateValue[0] === '$') {
			$parts = explode(' ', $validateValue, 2);
			$validatorConfiguration = array('argumentName' => ltrim($parts[0], '$'), 'validators' => array());
			preg_match_all(self::PATTERN_MATCH_VALIDATORS, $parts[1], $matches, PREG_SET_ORDER);
		} else {
			$validatorConfiguration = array('validators' => array());
			preg_match_all(self::PATTERN_MATCH_VALIDATORS, $validateValue, $matches, PREG_SET_ORDER);
		}

		foreach ($matches as $match) {
			$validatorOptions = array();
			if (isset($match['validatorOptions'])) {
				$validatorOptions = $this->parseValidatorOptions($match['validatorOptions']);
			}
			$validatorConfiguration['validators'][] = array('validatorName' => $match['validatorName'], 'validatorOptions' => $validatorOptions);
		}

		return $validatorConfiguration;
	}

	/**
	 * Parses $rawValidatorOptions not containing quoted option values.
	 * $rawValidatorOptions will be an empty string afterwards (pass by ref!).
	 *
	 * @param string &$rawValidatorOptions
	 * @return array An array of optionName/optionValue pairs
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function parseValidatorOptions($rawValidatorOptions) {
		$validatorOptions = array();
		$parsedValidatorOptions = array();
		preg_match_all(self::PATTERN_MATCH_VALIDATOROPTIONS, $rawValidatorOptions, $validatorOptions, PREG_SET_ORDER);
		foreach ($validatorOptions as $validatorOption) {
			$parsedValidatorOptions[trim($validatorOption['optionName'])] = trim($validatorOption['optionValue']);
		}
		array_walk($parsedValidatorOptions, array($this, 'unquoteString'));
		return $parsedValidatorOptions;
	}

	/**
	 * Removes escapings from a given argument string.
	 *
	 * This method is meant as a helper for regular expression results.
	 *
	 * @param string &$quotedValue Value to unquote
	 * @return string Unquoted value
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function unquoteString(&$quotedValue) {
		switch ($quotedValue[0]) {
			case '"':
				$quotedValue = str_replace('\"', '"', trim($quotedValue, '"'));
			break;
			case '\'':
				$quotedValue = str_replace('\\\'', '\'', trim($quotedValue, '\''));
			break;
		}
		$quotedValue = str_replace('\\\\', '\\', $quotedValue);
	}

	/**
	 * Returns the Repository for a Model based on the Class name
	 *
	 * @param $model String Name of the Model
	 * @return $repository String Repository Name
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
    public function getModelRepository($model){
		$repository = str_replace("Domain\Model","Domain\Repository",$model) . "Repository";
		if(substr($repository,0,1) == "\\"){
			$repository = substr($repository,1);
		}
		return $repository;
	}

	/**
	 * Returns the Name of the Model without Namespace
	 *
	 * @param $model String Name of the Model with Namespace
	 * @return $name String Model Name
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	public function getObjectNameByClassName($model){
		$parts = explode("\\",$model);
		return end($parts);
	}

	/**
	 * Returns the Main Type of the Model Property
	 * 
	 * For example: SplObjectStorage<DateTime> -> SplObjectStorage
	 *
	 * @param $raw String raw Type
	 * @return $name String Type
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
    public function getType($raw){
        $parts = explode("<",$raw);
        $name = current($parts);
        if(substr($name,0,1) == "\\") $name = substr($name,1);
        return $name;
    }

	/**
	 * Checks if the Class is a manageble Entity
	 *
	 * @param $checkClass String Name of the Class
	 * @return $isEntity Boolean
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
    public function isEntity($checkClass){
		if(!isset($this->cached["entities"])){
			$activePackages = $this->packageManager->getActivePackages();
			$this->cached["entities"] = array();
			foreach ($activePackages as $packageName => $package) {
				foreach ($package->getClassFiles() as $class => $file) {
					if(strpos($class,"\Model\\")>0){
						$tags = $this->reflection->getClassTagsValues($class);
						$parts = explode('\\',$class);
						$name = end($parts);
						if(in_array("autoadmin",array_keys($tags))){
							$this->cached["entities"][$class] = $name;
						}
					}
				}
			}
		}
		return isset($this->cached["entities"][$checkClass]) ? $this->cached["entities"][$checkClass] : false;
	}

	/**
	 * Returns the Class of the Widget for a property Type
	 *
	 * @param $type String Type of the Property
	 * @return $widgetClass String Class of the Widget
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
    public function getWidgetClass($type){
        $widgetType = $type = $this->getType($type);

        if(class_exists($type)){
            if($this->isEntity($type) !== FALSE){
                $widgetType = "entity";
            }
        }

        return str_replace("@type",ucfirst($widgetType),"F3\Admin\Widgets\@typeWidget");
    }

	/**
	 * Returns all Models wich are enabled through the @autoadmin tag
	 *
	 * @return $packages Array of the Models grouped by Packages
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	public function getEnabledModels()
	{
		if(!isset($this->cached["packages"])){
			$activePackages = $this->packageManager->getActivePackages();
			$this->cached["packages"] = array();
			foreach ($activePackages as $packageName => $package) {
				foreach ($package->getClassFiles() as $class => $file) {
					if(strpos($class,"\Model\\")>0){
						$tags = $this->reflection->getClassTagsValues($class);
						$parts = explode('\\',$class);
						$name = end($parts);
						$repository = $this->getModelRepository($class);
						if(in_array("autoadmin",array_keys($tags)) && class_exists($repository)){
							$this->cached["packages"][$packageName][] = array(
								"class" => $class,
								"name"	=> $name
							);
						}
					}
				}
			}
		}
		return $this->cached["packages"];
	}
	
	/**
	 * Returns a Validator for the given Modem
	 *
	 * @param $model String Class of the Model
	 * @return $validator GenericObjectValidator
	 * @author Marc Neuhaus
	 **/
	public function getModelValidator($model){
		$objectValidator = $this->ValidatorResolver->createValidator('F3\FLOW3\Validation\Validator\GenericObjectValidator');
		if (class_exists($model)) {
			$validatorCount = 0;

			foreach ($this->reflection->getClassPropertyNames($model) as $classPropertyName) {
				$classPropertyTagsValues = $this->reflection->getPropertyTagsValues($model, $classPropertyName);
				if (!isset($classPropertyTagsValues['validate'])) continue;

				foreach ($classPropertyTagsValues['validate'] as $validateValue) {
					$parsedAnnotation = $this->parseValidatorAnnotation($validateValue);
					foreach ($parsedAnnotation['validators'] as $validatorConfiguration) {
						$newValidator = $this->ValidatorResolver->createValidator($validatorConfiguration['validatorName'], $validatorConfiguration['validatorOptions']);
						if ($newValidator === NULL) {
							throw new \F3\FLOW3\Validation\Exception\NoSuchValidator('Invalid validate annotation in ' . $model . '::' . $classPropertyName . ': Could not resolve class name for  validator "' . $validatorConfiguration['validatorName'] . '".', 1241098027);
						}
						$objectValidator->addPropertyValidator($classPropertyName, $newValidator);
						$validatorCount ++;
					}
				}
			}
		}
		return $objectValidator;
	}
	
	/**
	 * Find errors for a specific property in the given errors array
	 *
	 * @param string $propertyName The property name to look up
	 * @param array $errors An array of F3\FLOW3\Error\Error objects
	 * @return array An array of errors for $propertyName
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function getErrorsForProperty($propertyName, $errors) {
		if(is_array($errors)){
			foreach ($errors as $error) {
				if ($error instanceof \F3\FLOW3\Validation\PropertyError) {
					if ($error->getPropertyName() === $propertyName) {
						return $error->getErrors();
					}
				}
			}
		}
		return array();
	}
}

?>
