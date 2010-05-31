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
 * Standard controller for the Admin package
 *
 * @version $Id: $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class StandardController extends \F3\FLOW3\MVC\Controller\ActionController {
	/**
	 * @var \F3\Admin\Helper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $helper;
	
	/**
	 * @var \F3\FLOW3\Session\PhpSession
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $session;
	
	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $objectManager;
	
	protected $model = "";
	
	/**
	 * Create action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function createAction() {
		$this->prepare("create");
		$being = $this->request->getArgument("being");
		$attributeSets = $this->getAdapter()->getAttributeSets($being);
		
		if($this->request->hasArgument("create")){
			$errors = $this->getAdapter()->createObject($being,$this->request->getArgument("item"));
			if(empty($errors)){
				$arguments = array("being"=>$this->being,"adapter" => $this->adapter);
				$this->redirect('list',NULL,NULL,$arguments);
			}else{
				foreach ($attributeSets as $set => $attributes) {
					foreach ($attributes as $key => $attribute) {
						if(array_key_exists($attribute["name"],$errors)){
							$attributeSets[$set][$key]["error"] = $errors[$attribute["name"]];
						}
					}
				}
			}
		}
		
		$this->view->assign("sets",$attributeSets);
	}
	
	/**
	 * Index action
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->prepare("index");
	}
	
	/**
	 * List action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function listAction() {
		$this->prepare("list");
		
		$objects = $this->getAdapter()->getBeings($this->being);
		
		foreach ($objects as $key => $object) {
			foreach($object["properties"] as $property => $meta){
				if(is_array($meta["value"])){
					$values = array();
					foreach ($meta["value"] as $value) {
						$values[] = $value["name"];
					}
					$objects[$key]["properties"][$property]["value"] = implode(",",$values);
				}
			}
		}
		
		$this->view->assign("objects",$objects);
		
		// Redirect to creating a new Object if there aren't any (Clean Slate)
		if(count($objects) < 1){
			$arguments = array( "being" => $this->being , "adapter" => $this->adapter);
			$this->redirect("create",NULL,NULL,	$arguments);
		}
	}
	
	/**
	 * Confirm previous requested action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function confirmAction() {
		$this->prepare("confirm");
		$object = $this->getAdapter()->getBeing($this->being,$this->request->getArgument("id"));
		$this->view->assign("object",$object);
	}
	
	/**
	 * delete action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function deleteAction() {
		$this->prepare("delete");
		if($this->request->hasArgument("confirm")){
			$this->getAdapter()->deleteObject($this->being,$this->request->getArgument("id"));
			
			$arguments = array("adapter"=>$this->adapter,"being"=>$this->being);
			$this->redirect('list',NULL,NULL,$arguments);
		}else{
			$this->redirect('confirm',NULL,NULL,$this->request->getArguments());
		}
	}
	
	/**
	 * update action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function updateAction() {
		$this->prepare("update");
		
#		if($this->request->hasArgument("delete")){
#			$arguments = $this->request->getArguments();
#			$this->redirect('confirm',NULL,NULL,$arguments);
#		}
		
		$being = $this->request->getArgument("being");
		$attributeSets = $this->getAdapter()->getAttributeSets($being,$this->request->getArgument("id"));
		
		if($this->request->hasArgument("update")){
			$errors = $this->getAdapter()->updateObject($being,$this->id,$this->request->getArgument("item"));
			if(empty($errors)){
				$arguments = array("being"=>$this->being,"adapter" => $this->adapter);
				$this->redirect('list',NULL,NULL,$arguments);
			}else{
				foreach ($attributeSets as $set => $attributes) {
					foreach ($attributes as $key => $attribute) {
						if(array_key_exists($attribute["name"],$errors)){
							$attributeSets[$set][$key]["error"] = $errors[$attribute["name"]];
						}
					}
				}
			}
		}
		
		$this->view->assign("sets",$attributeSets);
	}
	
	/**
	 * view action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function viewAction() {
		$this->prepare("view");
		
		$being = $this->getAdapter()->getBeing($this->being,$this->request->getArgument("id"));
		$this->view->assign("being",$being);
	}
	
	
	private function prepare($action){
		\F3\Dump\Dump::getInstance();
		$this->adapters = $this->helper->getAdapters();
		$this->settings = $this->helper->getSettings();
		$GLOBALS["Admin"]["session"] = $this->session;
		$GLOBALS["objectManager"] = $this->objectManager;
		
		$GLOBALS["Admin"]["action"] = $action;
		
		if($this->request->hasArgument("being")){
			$GLOBALS["Admin"]["being"] = $this->being = $this->request->getArgument("being");
			$GLOBALS["Admin"]["group"] = $this->group = $this->helper->getGroupByBeing($this->being);
		}
		if($this->request->hasArgument("adapter"))
			$GLOBALS["Admin"]["adapter"] = $this->adapter = $this->request->getArgument("adapter");
		
		if($this->request->hasArgument("id"))
			$GLOBALS["Admin"]["id"] = $this->id = $this->request->getArgument("id");
		
		$groups = $this->helper->getGroups();
		if(!empty($this->adapter)){
			foreach($groups as $package => $group){
				foreach($group["beings"] as $key => $being){
					if($being["being"] == $this->being && $being["adapter"] == $this->adapter){
						$groups[$package]["beings"][$key]["active"] = true;
					}else{
						$groups[$package]["beings"][$key]["active"] = false;
					}
				}
			}
		}
		
		$this->view->assign('groups',$groups);
			
		$this->setTemplate($action);
	}
	
	public function setTemplate($action){
		$replacements = array(
			"@action" => $action,
			"@variant" => "default",
			"@package" => "Admin",
		);
		if(!empty($this->being)){
			if(class_exists($this->being)){
				$tags = $this->reflectionService->getClassTagsValues($this->being);
				if(in_array($action."view",array_keys($tags))){
					$variant = $tags[$action."view"][0];
				}
				$replacements["@package"] =$this->helper->getPackageByClassName($this->being);
				$replacements["@model"] =$this->helper->getObjectNameByClassName($this->being);
				if(array_key_exists("variant-".$action,$tags)){
					$replacements["@model"] = current($tags["variant-".$action]);
				}
			}
		}
		
		$template = $this->helper->getPathByPatternFallbacks("Views",$replacements);
		$this->view->setTemplatePathAndFilename($template);
		
		$meta = array();
		if($this->request->hasArgument("being")){
			$meta["being"]["identifier"] = $this->request->getArgument("being");
			$meta["being"]["name"] = $this->getAdapter()->getName($this->request->getArgument("being"));
		}
			
		if($this->request->hasArgument("adapter")){
			$meta["adapter"]["identifier"] = $this->request->getArgument("adapter");
		}
		
		if($this->request->hasArgument("id")){
			$meta["id"] = $this->request->getArgument("id");
		}
		
		$this->view->assign("meta",$meta);
	}
	
	private function getAdapter(){
		$adapter =  $this->objectManager->getObject($this->adapter);
		if(!empty($this->being) && class_exists($this->being)){
			$tags = $this->reflectionService->getClassTagsValues($this->being);
			if(array_key_exists("adapter",$tags) && class_exists("\\".$tags["adapter"][0])){
				$adapter = $this->objectManager->getObject($tags["adapter"][0]);
			}
		}
		$adapter->init();
		
		return $adapter;
	}
}

?>
