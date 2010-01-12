<?php
 
namespace F3\Admin\Widgets;

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
class AbstractWidget{
    var $view;

	/**
	 * @var \F3\FLOW3\Object\FactoryInterface A reference to the Object Factory
	 */
	protected $objectFactory;

	/**
	 * @var \F3\FLOW3\Package\ManagerInterface
	 */
	protected $packageManager;

	/**
	 * @var \F3\FLOW3\Resource\ManagerInterface
	 */
	protected $resourceManager;

	/**
	 * @var \F3\FLOW3\Object\ManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \F3\FLOW3\MVC\Web\Routing\UriBuilder
	 */
	protected $uriBuilder;

	/**
	 * Reflection service
	 * @var F3\FLOW3\Reflection\Service
	 */
	private $reflection;

    /**
	 * @var \F3\FLOW3\Persistence\ManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @var \F3\Admin\Utilities
	 */
	protected $utilities;

	/**
	 * Pattern after which the view object name is built if no Fluid template
	 * is found.
	 * @var string
	 * @api
	 */
	protected $widgetTemplatePattern = 'package://@package/Private/Templates/Widgets/@widget.html';

    public function __construct(\F3\FLOW3\Object\FactoryInterface $objectFactory, \F3\FLOW3\Package\ManagerInterface $packageManager, \F3\FLOW3\Object\ManagerInterface $objectManager, \F3\FLOW3\Reflection\Service $reflectionService, \F3\FLOW3\Persistence\ManagerInterface $persistenceManager, \F3\Admin\Utilities $utilities){
        $this->objectFactory = $objectFactory;
		$this->objectManager = $objectManager;
		$this->packageManager = $packageManager;
        $this->reflection = $reflectionService;
        $this->persistenceManager = $persistenceManager;
		$this->utilities = $utilities;
    }

	public function setContext($package,$type,$context){
		$this->package = $package;

		$this->view = $this->objectFactory->create('F3\Fluid\View\TemplateView');
		$this->view->setControllerContext($context);

		$widgetTemplate = str_replace('@package', $package, $this->widgetTemplatePattern);
		$widgetTemplate = str_replace('@widget', $type, $widgetTemplate);
        $this->view->setTemplatePathAndFilename($widgetTemplate);
	}

	public function getModelRepository($model){
		$repository = str_replace("Domain\Model","Domain\Repository",$model) . "Repository";
		if(substr($repository,0,1) == "\\"){
			$repository = substr($repository,1);
		}
		return $repository;
	}

    public function getTitle($object){
        $class = get_class($object);

        $properties = $this->reflection->getClassPropertyNames($class);
        foreach($properties as $property){
			$tags = $this->reflection->getPropertyTagsValues($class,$property);
            if(in_array("title",array_keys($tags))){
                $method = "get".ucfirst($property);
                return call_user_func(array($object,$method));
            }
        }
    }
}

?>
