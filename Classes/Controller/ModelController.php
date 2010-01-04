<?php
declare(ENCODING = 'utf-8');
namespace F3\Admin\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Admin".                      *
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
 * Model controller for the Admin package
 *
 * @version $Id: $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ModelController extends \F3\FLOW3\MVC\Controller\ActionController {
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
	 * @var \F3\FLOW3\Property\Mapper
	 */
	protected $mapper;

	/**
	 * Injects the property mapper
	 *
	 * @param \F3\FLOW3\Property\Mapper $mapper The property mapper
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectMapper(\F3\FLOW3\Property\Mapper $mapper) {
		$this->mapper = $mapper;
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
	 * @var \F3\FLOW3\Reflection\Service
	 */
	protected $reflectionService;

	/**
	 * Injects the reflection service
	 *
	 * @param \F3\FLOW3\Reflection\Service $reflectionService
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function injectReflectionService(\F3\FLOW3\Reflection\Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * @var \F3\FLOW3\Configuration\Manager
	 */
	protected $configurationManager;

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
	 * @var \F3\FLOW3\Persistence\ManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Injects the FLOW3 Persistence Manager
	 *
	 * @param \F3\FLOW3\Persistence\ManagerInterface $persistenceManager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectPersistenceManager(\F3\FLOW3\Persistence\ManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * Index action
	 *
	 * @return void
	 */
	public function indexAction() {
		$activePackages = $this->packageManager->getActivePackages();
		$packages = array();
		foreach ($activePackages as $packageName => $package) {
			foreach ($package->getClassFiles() as $class => $file) {
				if(strpos($class,"\Model\\")>0){
					$tags = $this->reflectionService->getClassTagsValues($class);
					$parts = explode('\\',$class);
					$name = end($parts);
					$repository = $this->utilities->getModelRepository($class);
					if(in_array("autoadmin",array_keys($tags)) && class_exists($repository)){
						$packages[$packageName][] = array(
							"class" => $class,
							"name"	=> $name
						);
					}
				}
			}
		}
		$this->view->assign('packages', $packages);

		$current = $this->request->hasArgument("package") ? $this->request->getArgument("package") : "Overview";
		$this->view->assign('current', $current);
		$overview = $this->request->hasArgument("package") ? false : true;
		$this->view->assign('overview', $overview);
	}

	/**
	 * View action
	 *
	 * @return void
	 */
	public function viewAction() {
		$model = $this->request->getArgument("model");
		$this->view->assign("model",$model);

		$repository = str_replace("Domain\Model","Domain\Repository",$model) . "Repository";
		$repositoryObject = $this->objectManager->getObject($repository);

		$objects = $repositoryObject->findAll();
		$this->view->assign("objects",$objects);

		$properties = $this->getModelProperties($model);
		$this->view->assign("properties",$properties);

		$propertyCount = count($properties) + 1;
		$this->view->assign("propertyCount",$propertyCount);

		$bulkActions = array(
			"none"=>' ',
			"F3\Admin\BulkActions\DeleteBulkAction"=>'Delete selected Items'
		);
		$this->view->assign("bulkActions",$bulkActions);
	}

	/**
	 * edit action
	 *
	 * @return void
	 */
	public function editAction() {
		$tmp = $this->request->getArgument("object");
		$modelClass = $this->request->getArgument("model");
		$model = $this->objectFactory->create($modelClass);

		$properties = $this->getModelProperties($modelClass);

		$this->view->assign('model', $modelClass);
		$this->view->assign('properties',$properties);

		$object = $this->persistenceManager->getBackend()->getObjectByIdentifier($tmp["__identity"]);
		$this->view->assign('object',$object);
	}

	/**
	 * Creates a new blog
	 *
	 * @return void
	 */
	public function updateAction() {
		$this->createUpdateObject("update");
		$this->redirect('view',NULL,NULL,array("model"=>$this->request->getArgument("model")));
	}

	/**
	 * bulk action
	 *
	 * @return void
	 */
	public function bulkAction() {
		$tmp = $this->request->getArgument("bulk");
		$identifiers = array();
		foreach(array_keys($tmp["__identity"]) as $identifier){
			$identifiers[] = $identifier;
		}

		$action = $this->request->getArgument("action");
		$actionObject = $this->objectManager->getObject($action);
		$actionObject->action($identifiers);

		$this->redirect('view',NULL,NULL,array("model"=>$this->request->getArgument("model")));
	}

	/**
	 * new action
	 *
	 * @return void
	 */
	public function newAction() {
		$modelClass = $this->request->getArgument("model");
		$model = $this->objectFactory->create($modelClass);

		$properties = $this->getModelProperties($modelClass);

		$this->view->assign('model', $modelClass);
		$this->view->assign('properties',$properties);

		$object = $this->objectFactory->create($modelClass);
		$this->view->assign('object',$object);
	}

	/**
	 * Creates a new blog
	 *
	 * @return void
	 */
	public function createAction() {
		$this->createUpdateObject("create");
		$this->redirect('view',NULL,NULL,array("model"=>$this->request->getArgument("model")));
	}

	public function convertArray($array,$class){
		$properties = $this->reflectionService->getClassPropertyNames($class);
		foreach ($properties as $property) {
			$tags = $this->reflectionService->getPropertyTagsValues($class,$property);
			if(in_array($property,array_keys($array))){
				$widgetClass = $this->utilities->getWidgetClass($tags["var"][0]);
                $widget = $this->objectFactory->create($widgetClass);
				if(method_exists($widget,"convert"))
					$array[$property] = $widget->convert($array[$property]);
			}
		}
		return $array;
	}

	public function getModelProperties($model){
		$tmpProperties = $this->reflectionService->getClassPropertyNames($model);
		foreach ($tmpProperties as $property) {
			$properties[$property] = $this->reflectionService->getPropertyTagsValues($model,$property);
			if(!in_array("var",array_keys($properties[$property]))) continue;
			$properties[$property]["identity"] = in_array("identity",array_keys($properties[$property])) ? "true" : "false";
		}
		unset($tmpProperties);
		return $properties;
	}

	public function createUpdateObject($mode){
		$model = $this->request->getArgument("model");
		$modelName = $this->utilities->getObjectNameByClassName($model);
		$repository = str_replace("Domain\Model","Domain\Repository",$model) . "Repository";
		$this->arguments->addNewArgument("item", $model);
		$this->initializeActionMethodValidators();

		$optionalArgumentNames = array();
		$allArgumentNames = $this->arguments->getArgumentNames();
		foreach ($allArgumentNames as $argumentName) {
			if ($this->arguments[$argumentName]->isRequired() === FALSE) $optionalArgumentNames[] = $argumentName;
		}

		$args = $this->request->getArguments();
		$args["item"] = $this->convertArray($args["item"],$model);

		$properties = $this->getModelProperties($model);

		$validator = $this->objectManager->getObject('F3\FLOW3\MVC\Controller\ArgumentsValidator');
		$this->propertyMapper->mapAndValidate($allArgumentNames, $args, $this->arguments, $optionalArgumentNames, $validator);
		$results = $this->propertyMapper->getMappingResults();
		print_r($results);
		exit;
		$repositoryObject = $this->objectManager->getObject($repository);

		$object = $this->arguments["item"]->getValue();

		if($mode=="create")
			$repositoryObject->add($object);
		if($mode=="update")
			$repositoryObject->update($object);
	}
}

?>
