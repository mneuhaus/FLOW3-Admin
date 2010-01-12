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
	 * @var F3\FLOW3\Reflection\Service
	 */
	private $reflection;

	/**
	 * Inject a Reflection service
	 * @param \F3\FLOW3\Reflection\Service $reflectionService Reflection service
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectReflection(\F3\FLOW3\Reflection\Service $reflectionService) {
		$this->reflection = $reflectionService;
	}

	/**
	 * @var \F3\FLOW3\Object\ManagerInterface
	 * @api
	 */
	protected $objectManager;

	/**
	 * Injects the object manager
	 *
	 * @param \F3\FLOW3\Object\ManagerInterface $manager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectManager(\F3\FLOW3\Object\ManagerInterface $manager) {
		$this->objectManager = $manager;
	}

    /**
	 * @var \F3\FLOW3\Object\FactoryInterface
	 */
	protected $objectFactory;

    /**
	 * Injects the object factory
	 *
	 * @param \F3\FLOW3\Object\FactoryInterface $objectFactory
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectObjectFactory(\F3\FLOW3\Object\FactoryInterface $objectFactory) {
		$this->objectFactory = $objectFactory;
	}

	/**
	 * @var \F3\FLOW3\Package\ManagerInterface
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
	 * @param string $model
	 * @param object $object
	 * @param object $errors
	 * @return string "Form"-Tag.
	 */
	public function render($model, $object, $errors){
        $output = "<input type='hidden' name='model' value='".$model."' />";
        $properties = $this->reflection->getClassPropertyNames($model);
        foreach($properties as $property){
			$tags = $this->reflection->getPropertyTagsValues($model,$property);
			
			if(!in_array("var",array_keys($tags))) continue;
			if(!in_array("widget",array_keys($tags))){
				$type = $this->utilities->getType($tags["var"][0]);
			}else{
				$type = $tags["widget"][0];
			}
			$widgetClass = $this->utilities->getWidgetClass($type);
			$propertyErrors = array();
			foreach ($this->utilities->getErrorsForProperty($property,$errors) as $error) {
				$propertyErrors[] = $error->getMessage();
			}
			
            $context = array(
                "widget"    => "Widget not found: "."\\".$widgetClass,
                "label"     => ucfirst($property),
                "property_errors"     => implode("<br />",$propertyErrors)
            );

            if(class_exists($widgetClass)){
                $widget = $this->objectFactory->create($widgetClass);
				$widget->setContext("Admin", $type, $this->controllerContext);
				$widgetClass = $this->utilities->getWidgetClass($tags["var"][0]);
                $context = array_merge($context,$widget->render($property,$object,"item",$tags));
            }

            foreach($context as $key => $value)
                $this->templateVariableContainer->add($key, $value);
            $output .= $this->renderChildren();
            foreach($context as $key => $value)
                $this->templateVariableContainer->remove($key);
        }
        return $output;
	}
}

?>
