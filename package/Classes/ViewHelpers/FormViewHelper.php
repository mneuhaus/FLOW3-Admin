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
class FormViewHelper extends \F3\Fluid\ViewHelpers\FormViewHelper {

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
	 * Initialize arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
		$this->registerTagAttribute('enctype', 'string', 'MIME type with which the form is submitted');
		$this->registerTagAttribute('method', 'string', 'Transfer type (GET or POST)');
		$this->registerTagAttribute('name', 'string', 'Name of form');
		$this->registerTagAttribute('onreset', 'string', 'JavaScript: On reset of the form');
		$this->registerTagAttribute('onsubmit', 'string', 'JavaScript: On submit of the form');

		$this->registerUniversalTagAttributes();
	}

	/**
	 * Render the form.
	 *
	 * @param string $action target action
	 * @param array $arguments additional arguments
	 * @param string $controller name of target controller
	 * @param string $package name of target package
	 * @param string $subpackage name of target subpackage
	 * @param mixed $object object to use for the form. Use in conjunction with the "property" attribute on the sub tags
	 * @param string $section The anchor to be added to the URI
	 * @param string $fieldNamePrefix Prefix that will be added to all field names within this form
	 * @param string $actionUri can be used to overwrite the "action" attribute of the form tag
	 * @param string $model used to automatically generate needed fields
	 * @return string rendered form
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @api
	 */
	public function render( $action = '',
                            array $arguments = array(),
                            $controller = NULL,
                            $package = NULL,
                            $subpackage = NULL,
                            $object = NULL,
                            $section = '',
                            $fieldNamePrefix = NULL,
                            $actionUri = NULL,
                            $model = NULL) {
		$this->setFormActionUri();

		if (strtolower($this->arguments['method']) === 'get') {
			$this->tag->addAttribute('method', 'get');
		} else {
			$this->tag->addAttribute('method', 'post');
		}

		$this->addFormNameToViewHelperVariableContainer();
		$this->addFormObjectToViewHelperVariableContainer();
		$this->addFieldNamePrefixToViewHelperVariableContainer();
		$this->addFormFieldNamesToViewHelperVariableContainer();

		if(!empty($model)){
            $formContent = "";
            $properties = $this->reflection->getClassPropertyNames($model);

            foreach($properties as $property){
                $tags = $this->reflection->getPropertyTagsValues($model,$property);

                if(!in_array("var",array_keys($tags))) continue;
                $type = $this->getType($tags["var"][0]);

                $widgetClass = str_replace("@type",ucfirst($type),"F3\Admin\Widgets\@typeWidget");

                $context = array(
                    "widget"    => "Widget not found: "."\\".$widgetClass,
                    "label"     => ucfirst($property),
                    "error"     => ""
                );
                if(class_exists($widgetClass)){
                    $widget = $this->objectFactory->create($widgetClass,"Admin", $type, $this->controllerContext);
                    $context = array_merge($context,$widget->render($property,$object,$this->arguments['name'],$tags));
                }

                foreach($context as $key => $value)
                    $this->templateVariableContainer->add($key, $value);
                $formContent .= $this->renderChildren();
                foreach($context as $key => $value)
                    $this->templateVariableContainer->remove($key);
            }
        }else{
            $formContent = $this->renderChildren();
        }

		$content = $this->renderHiddenIdentityField($this->arguments['object'], $this->arguments['name']);
		$content .= $this->renderAdditionalIdentityFields();
		$content .= $this->renderHiddenReferrerFields();
		$content .= $this->renderRequestHashField(); // Render hmac after everything else has been rendered
		$content .= $formContent;

		$this->tag->setContent($content);

		$this->removeFieldNamePrefixFromViewHelperVariableContainer();
		$this->removeFormObjectFromViewHelperVariableContainer();
		$this->removeFormNameFromViewHelperVariableContainer();
		$this->removeFormFieldNamesFromViewHelperVariableContainer();

		return $this->tag->render();
	}

    protected function getType($raw){
        $parts = explode("<",$raw);
        $name = current($parts);
        if(substr($name,0,1) == "\\") $name = substr($name,1);
        return $name;
    }

	public function getObjectNameByClassName($model){
		$parts = explode("\\",$model);
		return end($parts);
	}
}

?>
