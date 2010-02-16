<?php
 
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
	
	protected $fallbackPatterns = array(
#		"package://@specified",
		"package://@package/Private/Templates/@model/@action/@variant.html",
		"package://@package/Private/Templates/Admin/@action/@variant.html",
		"package://@package/Private/Templates/@model/@action.html",
		"package://@package/Private/Templates/Admin/@action.html",
		"package://Admin/Private/Templates/Model/@action/@variant.html",
		"package://Admin/Private/Templates/Model//@action.html"
	);
	
	/**
	 * @var \F3\Admin\Utilities
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	protected $utilities;

	/**
	 *
	 * @param \F3\Admin\Utilities $utilities
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function injectUtilities(\F3\Admin\Utilities $utilities) {
		$this->utilities = $utilities;
	}

	/**
	 * @var \F3\FLOW3\Property\Mapper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	protected $mapper;
	
	/**
	 * Injects the property mapper
	 *
	 * @param \F3\FLOW3\Property\Mapper $mapper The property mapper
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function injectMapper(\F3\FLOW3\Property\Mapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	protected $objectManager;

	/**
	 * Injects the object manager
	 *
	 * @param \F3\FLOW3\Object\ObjectManagerInterface $manager
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function injectManager(\F3\FLOW3\Object\ObjectManagerInterface $manager) {
		$this->objectManager = $manager;
	}

	/**
	 * @var \F3\FLOW3\Reflection\ReflectionService
	 * @author Marc Neuhaus
	 */
	protected $reflectionService;

	/**
	 * Injects the reflection service
	 *
	 * @param \F3\FLOW3\Reflection\ReflectionService $reflectionService
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function injectReflectionService(\F3\FLOW3\Reflection\ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * @var \F3\FLOW3\Configuration\ConfigurationManager
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	protected $configurationManager;

	/**
	 * @var \F3\FLOW3\Package\PackageManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
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
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	protected $persistenceManager;

	/**
	 * Injects the FLOW3 Persistence Manager
	 *
	 * @param \F3\FLOW3\Persistence\PersistenceManagerInterface $persistenceManager
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function injectPersistenceManager(\F3\FLOW3\Persistence\PersistenceManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}
	
	/**
	 * Creates a new blog
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function createAction() {
		$modelClass = $this->request->getArgument("model");
		$model = $this->objectFactory->create($modelClass);

		$properties = $this->utilities->getModelProperties($modelClass);
		
		$tags = $this->reflectionService->getClassTagsValues($modelClass);
		$sets = array();
		if(isset($tags["set"])){
			foreach ($tags["set"] as $set) {
				preg_match("/(.*)\(([a-z,]+)\)/",$set,$matches);
				$sets[] = array(
					"name" => isset($matches[1]) ? $matches[1] : "General",
					"properties" => isset($matches[2]) ? $matches[2] : ""
				);
			}
		}else{
			$sets[] = array(
				"name" => "General",
				"properties" => ""
			);
		}
		
		
		$this->view->assign('sets', $sets);
		
		$this->view->assign('model', $modelClass);
		$this->view->assign('properties',$properties);

		$object = $this->objectFactory->create($modelClass);
		
		if($this->request->hasArgument("create")){
			$errors = $this->createUpdateObject("create",$object);
			if($errors === false){
				$arguments = array("model"=>$this->request->getArgument("model"));
				$this->redirect('list',NULL,NULL,$arguments);
			}else{
				$this->view->assign("errors",$errors);
			}
		}
		
		$this->view->assign('object',$object);
		
		$this->setTemplate($modelClass,"create");
	}
	
	/**
	 * Confirm previous requested action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function confirmAction() {
		$tmp = $this->request->getArgument("item");
		$model = $this->request->getArgument("model");
		$this->view->assign('model', $model);
		
		$properties = $this->utilities->getModelProperties($model);
		$this->view->assign('properties',$properties);

		$object = $this->persistenceManager->getObjectByIdentifier($tmp["__identity"]);
		
		$objects = array( $this->objectRelations($object) );
		
		$this->view->assign('root',$objects);
		$this->view->assign('identity',$tmp["__identity"]);
		
		$this->setTemplate($model,"confirm");
	}
	
	/**
	 * delete action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function deleteAction() {
		$tmp = $this->request->getArgument("item");
		$model = $this->request->getArgument("model");
		$this->view->assign("model",$model);
		
		$object = $this->persistenceManager->getObjectByIdentifier($tmp["__identity"]);
		
		if($this->request->hasArgument("confirm")){
			$repository = str_replace("Domain\Model","Domain\Repository",$model) . "Repository";
			$repositoryObject = $this->objectManager->getObject($repository);
			$repositoryObject->remove($object);
			$this->persistenceManager->persistAll();
			
			$arguments = array("model"=>$model);
			$this->redirect('list',NULL,NULL,$arguments);
		}else{
			$this->redirect('confirm',NULL,NULL,$this->request->getArguments());
		}
	}
	
	/**
	 * Index action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function indexAction() {
		if($this->request->hasArgument("package")){
			$package = $this->request->getArgument("package");
			$allPackages = $this->utilities->getEnabledModels();
			$packages = array(
				"models" => array(
					$package => $allPackages[$package]
				),
				//"actions" => $this->utilities->getEnabledActions()
			);
			$this->view->assign('packages', $packages);
		}else{
			$packages = array(
				"models" => $this->utilities->getEnabledModels(),
				"actions" => $this->utilities->getEnabledActions()
			);
			$this->view->assign('packages', $packages);
		}

		$current = $this->request->hasArgument("package") ? $this->request->getArgument("package") : "Overview";
		$this->view->assign('current', $current);
		$overview = $this->request->hasArgument("package") ? false : true;
		$this->view->assign('overview', $overview);
		
#		$this->setTemplate($model,"index");
	}

	/**
	 * View action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function listAction() {
		$model = $this->request->getArgument("model");
		$this->view->assign("model",$model);

		$repository = str_replace("Domain\Model","Domain\Repository",$model) . "Repository";
		$repositoryObject = $this->objectManager->getObject($repository);
		
		$properties = $this->utilities->getModelProperties($model);
		$this->view->assign("properties",$properties);
		
		$query = $repositoryObject->createQuery();
		
		// Applying Filters
		$filters = array();
		if($this->request->hasArgument("filters")){
			$filters = $this->request->getArgument("filters");
			foreach ($filters as $filter => $value) {
				if($value != "all")
					$query->matching($query->equals($filter, $value));
			}
		}
		$filters = $this->utilities->getFilters($properties,$query->execute(),$filters);
		$this->view->assign("filters",$filters);
		
		
		
		// Get total amount of Objects before limitations
		$count = $query->count();
		
		
		
		// Set Limits
		$limits = array(
			10 => false,
			20 => false,
			50 => false,
			100 => false,
			"All" => false
		);
		
		// Contextualize the Limt Choices
		foreach ($limits as $key => $value) {
			if(intval($key) > $count)
				unset($limits[$key]);
		}
		
		// Show Limt Choices if there is more than One Choice
		if(count($limits) == 1){
			$limits = array();
			$limit = 0;
		}else{
			if($this->request->hasArgument("limit")){
				$limit = $this->request->getArgument("limit");
	#			echo $limit;
				if( intval($limit) > 0 ){
					$query->setLimit(intval($limit));
				}
				$limits[$limit] = true;
			}else{
				$limit = key($limits);
				if( intval($limit) > 0 ){
					$query->setLimit(intval($limit));
				}
				$limits[$limit] = true;
			}
		}
		$this->view->assign("limits",$limits);
		
		
		
		// Pagination if required
		if( intval($limit) > 0 ){
			// Pagination
			if($count > $limit){
				$pages = range(1, ( $count / $limit ) );
				$this->view->assign("pages",$pages);
			}
		}
		if($this->request->hasArgument("page")){
			$page = $this->request->getArgument("page");
			$query->setOffset((intval($limit) * $page) - 1);
		}else{
			$page = 1;
		}
		$this->view->assign("currentpage",$page);
		
		
		
		$objects = $query->execute();
		
		
		// Redirect to creating a new Object if there aren't any (Clean Slate)
		if(count($objects) < 1){
			$arguments = array("model"=>$model);
			$this->redirect("create",NULL,NULL,	$arguments);
		}
		
		$this->view->assign("objects",$objects);
		$this->view->assign("total",$count);

		$propertyCount = count($properties) + 1;
		$this->view->assign("propertyCount",$propertyCount);

		$bulkActions = array(
			"none"=>' ',
			"F3\Admin\BulkActions\DeleteBulkAction"=>'Delete selected Items'
		);
		$this->view->assign("bulkActions",$bulkActions);
		
		
		$tags = $this->reflectionService->getClassTagsValues($model);
		if(in_array("quickadd",array_keys($tags))){
			$object = $this->objectFactory->create($model);
			$this->view->assign('quickobject',$object);
			$this->view->assign('quickproperties',$tags["quickadd"][0]);
		}
		
		$this->setTemplate($model,"list");
	}
	
	/**
	 * update action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function updateAction() {
		$tmp = $this->request->getArgument("item");
		$modelClass = $this->request->getArgument("model");
		$model = $this->objectFactory->create($modelClass);

		$properties = $this->utilities->getModelProperties($modelClass);

		$this->view->assign('model', $modelClass);
		$this->view->assign('properties',$properties);

		$object = $this->persistenceManager->getObjectByIdentifier($tmp["__identity"]);

		if($this->request->hasArgument("update")){
			$errors = $this->createUpdateObject("update",$object);
			if($errors === false){
				$arguments = array("model"=>$this->request->getArgument("model"));
				$this->redirect('list',NULL,NULL,$arguments);
			}else{
				$this->view->assign("errors",$errors);
			}
		}
		
		if($this->request->hasArgument("delete")){
			$arguments = $this->request->getArguments();
			$this->redirect('confirm',NULL,NULL,$arguments);
		}
		
		if($this->request->hasArgument("confirmed")){
			$repository = str_replace("Domain\Model","Domain\Repository",$modelClass) . "Repository";
			$repositoryObject = $this->objectManager->getObject($repository);
			$repositoryObject->remove($object);
			$this->persistenceManager->persistAll();
			
			$arguments = array("model"=>$this->request->getArgument("model"));
			$this->redirect('list',NULL,NULL,$arguments);
		}
		
		$this->view->assign('object',$object);
		
		$this->setTemplate($modelClass,"update");
	}

	/**
	 * view action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function viewAction() {
		$tmp = $this->request->getArgument("item");
		$object = $this->persistenceManager->getObjectByIdentifier($tmp["__identity"]);
		$this->view->assign('object',$object);
		
		$model = $this->request->getArgument("model");
		$this->view->assign('model',$model);

		$properties = $this->utilities->getModelProperties($model);
		$this->view->assign('properties',$properties);
		
		$this->setTemplate($model,"view");
	}
	
	/**
	 * bulk action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
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

		$this->redirect('list',NULL,NULL,array("model"=>$this->request->getArgument("model")));
	}
	
	/**
	 * Checks if the Widget provides a Function to convert the incoming Form 
	 * Data into ContentRepository Data.
	 * 
	 * Note: This might become obsolete because of the enhancement of the Propertymapper
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	public function convertArray($array,$class){
		$properties = $this->reflectionService->getClassPropertyNames($class);
		foreach ($properties as $property) {
			$tags = $this->reflectionService->getPropertyTagsValues($class,$property);
			if(in_array($property,array_keys($array))){
				if(is_array($array[$property])){
					$array[$property] = $this->utilities->groupArrayByKeys($array[$property]);
				}
				
				$widgetClass = $this->utilities->getWidgetClass($tags["var"][0]);
                $widget = $this->objectFactory->create($widgetClass);
				if(is_callable(array($widget,"convert")))
					$array[$property] = $widget->convert($array[$property]);
			}
		}
		return $array;
	}

	/**
	 * This Method handles the Creation or Update of the Posted Model
	 * 
	 * TODO: The Validation isn't working
	 *
	 * @param $mode String Mode Create/Update
	 * @param $targetObject Object
	 * @return $success Boolean
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	public function createUpdateObject($mode,$targetObject){
		$model = $this->request->getArgument("model");
		$modelName = $this->utilities->getObjectNameByClassName($model);
		$repository = str_replace("Domain\Model","Domain\Repository",$model) . "Repository";
		$modelValidator = $this->utilities->getModelValidator($model);

		$item = $this->convertArray($this->request->getArgument("item"),$model);
		$item = $this->cleanUpItem($item);

		$arg = $this->objectFactory->create("F3\FLOW3\MVC\Controller\Argument","item",$model);
		$arg->setValue($item);
		
		$properties = array_keys($item);
		if($mode == "update")
			unset($properties[array_search("__identity",$properties)]);
		
		$this->propertyMapper->mapAndValidate($properties, $item, $targetObject,array(),$modelValidator);
		
		$targetObject = $arg->getValue();
		
		$repositoryObject = $this->objectManager->getObject($repository);
		
		$errors = $modelValidator->getErrors();


		if(count($errors)>0){
			return $errors;
		}else{
			if($mode=="create"){
				$repositoryObject->add($targetObject);
			}
			if($mode=="update")
				$repositoryObject->update($targetObject);
			return false;
		}
	}
	
	public function cleanUpItem($item){
		foreach ($item as $key => $value) {
			if(is_array($value)){
				$item[$key] = $this->cleanUpItem($value);
			}
			if(empty($item[$key])){
				unset($item[$key]);
			}
		}
		return $item;
	}
	
	public function setTemplate($model,$action){
		$tags = $this->reflectionService->getClassTagsValues($model);
		if(in_array($action."view",array_keys($tags))){
			$variant = $tags[$action."view"][0];
		}else{
			$variant = "default";
		}
		
		$template = $this->utilities->getTemplateByPatternFallbacks($this->fallbackPatterns,array(
			"@action" => $action,
			"@package" => $this->utilities->getPackageByClassName($model),
			"@model" => $this->utilities->getObjectNameByClassName($model),
			"@variant" => $variant
		));
		
		$this->view->setTemplatePathAndFilename($template);
	}
	
	public function objectRelations($object){
		$class = get_class($object);
		$properties = $this->utilities->getModelProperties($class);
		$name = $object->__toString();
		$return = array(
			"type"=>$this->utilities->getObjectNameByClassName($class),
			"name"=> !empty($name) ? $name : "no name"
		);
		foreach ($properties as $property => $tags) {
			if(in_array("var",array_keys($tags)) && count($tags["var"]>0)){
				$type = current($tags["var"]);
				if($this->utilities->isEntity($type) 
					&& !class_exists($this->utilities->getModelRepository($type))){
#					$childs = array(\F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property));
				}
				
				$typeInformation = \F3\FLOW3\Utility\TypeHandling::parseType($type);
				if($this->utilities->isEntity($typeInformation["elementType"])
					&& !class_exists($this->utilities->getModelRepository($typeInformation["elementType"]))){
#					$childs = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property);
				}
				if(isset($childs)){
					foreach ($childs as $child) {
						$return["childs"][] = $this->objectRelations($child);
					}
				}
			}
		}

		return $return;
	}
}

?>
