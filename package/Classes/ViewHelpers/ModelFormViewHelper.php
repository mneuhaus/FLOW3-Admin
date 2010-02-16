<?php

namespace F3\Admin\ViewHelpers;

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
* @scope prototype
*/
class ModelFormViewHelper extends \F3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper {

	/**
		* @var \F3\Admin\Utilities
		*/
	protected $utilities;

	/**
	*
	* @param \F3\Admin\Utilities $utilities
	*/
	public function injectUtilities(\F3\Admin\Utilities $utilities) {
		$this->utilities = $utilities;
	}
	
	/**
	* If the ObjectAccessorPostProcessor should be disabled inside this ViewHelper, then set this value to FALSE.
	* This is internal and NO part of the API. It is very likely to change.
	*
	* @var boolean
	* @internal
*/
	protected $objectAccessorPostProcessorEnabled = FALSE;

	/**
	* Reflection service
	* @var F3\FLOW3\Reflection\ReflectionService
*/
	private $reflection;

	/**
	* Inject a Reflection service
	* @param \F3\FLOW3\Reflection\ReflectionService $reflectionService Reflection service
	* @author Sebastian Kurfürst <sebastian@typo3.org>
*/
	public function injectReflection(\F3\FLOW3\Reflection\ReflectionService $reflectionService) {
		$this->reflection = $reflectionService;
	}

	/**
	* @var \F3\FLOW3\Object\ObjectManagerInterface
	* @api
*/
	protected $objectManager;

	/**
	* Injects the object manager
	*
	* @param \F3\FLOW3\Object\ObjectManagerInterface $manager
	* @return void
	* @author Robert Lemke <robert@typo3.org>
*/
	public function injectManager(\F3\FLOW3\Object\ObjectManagerInterface $manager) {
		$this->objectManager = $manager;
	}

	/**
	* @var \F3\FLOW3\Object\ObjectFactoryInterface
*/
	protected $objectFactory;

	/**
	* Injects the object factory
	*
	* @param \F3\FLOW3\Object\ObjectFactoryInterface $objectFactory
	* @return void
	* @author Robert Lemke <robert@typo3.org>
*/
	public function injectObjectFactory(\F3\FLOW3\Object\ObjectFactoryInterface $objectFactory) {
		$this->objectFactory = $objectFactory;
	}

	/**
	* @var \F3\FLOW3\Package\PackageManagerInterface
*/
	protected $packageManager;

	/**
	* Injects the packageManager
	*
	* @param \F3\FLOW3\Package\PackageManagerInterface $packageManager
	* @return void
	* @author Marc Neuhaus <apocalip@gmail.com>
*/
	public function inject(\F3\FLOW3\Package\PackageManagerInterface $packageManager) {
		$this->packageManager = $packageManager;
	}

	/**
	 * @var \F3\FLOW3\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;
	
	/**
	 * Injects the persistence manager
	 *
	 * @param \F3\FLOW3\Persistence\PersistenceManagerInterface $persistenceManager
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function injectPersistenceManager(\F3\FLOW3\Persistence\PersistenceManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	*
	* @param string $model
	* @param object $object
	* @param object $errors
	* @param boolean $root
	* @param string $namespace
	* @param string $var
	* @param string $properties
	* @return string "Form"-Tag.
*/
	public function render($model, $object, $errors, $root=true, $namespace="item", $var="props", $properties = ""){
		$output = "";
		if($root)
			$output.= "<input type='hidden' name='model' value='".$model."' />";
		
		$settings = $this->utilities->getSettings();
		
		$properties = explode(",",$properties);
		if(count($properties) < 1 || empty($properties[0]))
			$properties = $this->reflection->getClassPropertyNames($model);
		$props = array();
		foreach($properties as $property){
			$property = trim($property);
			$tags = $this->reflection->getPropertyTagsValues($model,$property);
			
			if(!array_key_exists("var",$tags)) continue;
			if(!array_key_exists("widget",$tags)){
				$type = $this->utilities->getWidgetType($tags["var"][0]);
			}else{
				$type = $tags["widget"][0];
			}
			
			$widgetClass = $this->utilities->getWidgetClass($type);
			$propertyErrors = array();
			foreach ($this->utilities->getErrorsForProperty($property,$errors) as $error) {
				$propertyErrors[] = $error->getMessage();
			}

			if(\F3\FLOW3\Reflection\ObjectAccess::isPropertyGettable($object,$property)){
				$propertyValue = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property);
				$propertyClass = substr($tags["var"][0],1);
				$context = array(
					"widget"    => "Widget not found: "."\\".$widgetClass,
					"label"     => ucfirst($property),
					"error" => implode("<br />",$propertyErrors),
					"name" => $property,
					"inlines" => array(),
					"sortable" => false
				);
				
				if(class_exists($widgetClass)){
					$widget = $this->objectFactory->create($widgetClass);
					$widget->setContext("Admin", $type, $this->controllerContext);
					$widgetClass = $this->utilities->getWidgetClass($tags["var"][0]);
					$context = array_merge($context,$widget->render($property,$object,$namespace,$tags));
				}
				
				if(array_key_exists("inline",$tags) && $root){
					$mode = "update";
					if($type == "SingleRelation"){
						if(!is_object($propertyValue)){
							$propertyValue = $this->objectFactory->create($propertyClass);
							$mode = "create";
						}
						$children = array($propertyValue);
						$propertyNamespace = $namespace."[".$property."]";
					}else{
						$propertyClass=\F3\FLOW3\Utility\TypeHandling::parseType($propertyClass);
						$propertyClass = $propertyClass['elementType'];
						if(substr($propertyClass,0,1) == "\\") $propertyClass = substr($propertyClass,1);
						
						if(	is_object($propertyValue)  && is_callable(array($propertyValue,"count")) && $propertyValue->count() < 1){
							$newInlines = (isset($tags["inline"][0]) && intval($tags["inline"][0]) > 0) ? 
											$tags["inline"][0] 
											: $settings["Defaults"]["newinlines"];
							$x=0;
							while($x < $newInlines){
								$x++;
								$propertyValue->attach($this->objectFactory->create($propertyClass));
							}
							$mode = "create";
						}
						$children = $propertyValue;
						$propertyNamespace = $namespace."[".$property."][]";
						
						$context["sortable"] = true;
					}
					
					if($mode == "create"){
						$inlineProperties = isset($tags["properties"]) ? $tags["properties"][0] : "";
						foreach ($children as $key => $child) {
							$tmpNamespace = str_replace("@counter",$key,$propertyNamespace);
							$identity = $this->persistenceManager->getIdentifierByObject($child);
							if($mode == "update"){
								$output.= "<input type='hidden' name='".$tmpNamespace."[__identity]' value='".$identity."' />";
							}
							$context["inlines"][] = $this->render($propertyClass,$child,array(),false,$tmpNamespace,$var,$inlineProperties);
						}
						$context["widget"] = "";
					}
				}
				$props[] = $context;
			}
		}
		if($root){
			$this->templateVariableContainer->add($var, $props);
			$output .= $this->renderChildren();
			$this->templateVariableContainer->remove($var);
			return $output;
		}else{
			return $props;
		}
	}
}

?>
