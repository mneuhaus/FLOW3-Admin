<?php

namespace Admin\Controller;

/* *
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

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Standard controller for the Admin package
 *
 * @version $Id: $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class StandardController extends \TYPO3\FLOW3\MVC\Controller\ActionController {
	/**
	 * @var \Admin\Core\CacheManager
	 * @FLOW3\Inject
	 */
	protected $cacheManager;
		
	/**
	 * @var \Admin\Core\ConfigurationManager
	 * @FLOW3\Inject
	 */
	protected $configurationManager;
	
	/**
	 * @var \Admin\Core\Helper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $helper;

	/**
	 * @var \TYPO3\FLOW3\Session\PhpSession
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $session;

	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $objectManager;

	/**
	 * @var Admin\Security\SecurityManager
	 * @FLOW3\Inject
	 */
	protected $securityManager;

	protected $model = "";
	protected $being = null;
	protected $id = null;
	
	public function addLog($description = ""){
		if($this->settings["Logging"]["Active"]){
			$action = \Admin\Core\API::get("action");
			if(!in_array($action, $this->settings["Logging"]["Ignore"])){
				$log = new \Admin\Domain\Model\Log();
				$log->setUser($this->user);
				$log->setAction($action);
				
				if(isset($this->adapter))
					$log->setAdapter($this->adapter);
					
				if(isset($this->being))
					$log->setBeing(\Admin\Core\Helper::getShortName("\\".$this->being));
					
				if(isset($this->id))
					$log->setIdentity($this->id);
					
				$this->objectManager->get("\Admin\Domain\Repository\LogRepository")->add($log);
				$this->persistenceManager->persistAll();
			}
		}
	}
	
	/**
	 * Resolves and checks the current action method name
	 *
	 * @return string Method name of the current action
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function resolveActionMethodName() {
		$actionMethodName = $this->request->getControllerActionName() . 'Action';
		$action = $this->getActionByShortName($actionMethodName);
		if (!method_exists($this, $actionMethodName) && $action !== null) {
			#throw new \TYPO3\FLOW3\MVC\Exception\NoSuchActionException('An action "' . $actionMethodName . '" does not exist in controller "' . get_class($this) . '".', 1186669086);
		}
		return $actionMethodName;
	}

	/**
	 * Calls the specified action method and passes the arguments.
	 *
	 * If the action returns a string, it is appended to the content in the
	 * response object. If the action doesn't return anything and a valid
	 * view exists, the view is rendered automatically.
	 *
	 * @param string $actionMethodName Name of the action method to call
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function callActionMethod() {
		$start = microtime(true);
		
		if( $this->objectManager->getContext() == "Development" 
			&& ( $this->helper->getSettings("Admin.FlushCacheInDevelopment") || $this->request->hasArgument("flush_cache") ) ){
			$this->cacheManager->flushAdminCaches();
		}
		
		$this->indexAction();
		$preparedArguments = array();
		foreach ($this->arguments as $argument) {
			$preparedArguments[] = $argument->getValue();
		}
		
		$actionResult = $this->__call($this->actionMethodName, $preparedArguments);
		
		$debugInfo = array();
		if($this->objectManager->getContext() == "Development"){
			$debugInfo[] = "exectime: " . number_format(microtime(true) - $start, 4) . "s";
			$this->view->assign("devmode", "true");
		}
		$debugInfo[] = "";
		$this->view->assign("admin-debug-info", implode(" ", $debugInfo));
		
		if ($actionResult === NULL && $this->view instanceof \TYPO3\FLOW3\MVC\View\ViewInterface) {
			$this->response->appendContent($this->view->render());
		} elseif (is_string($actionResult) && strlen($actionResult) > 0) {
			$this->response->appendContent($actionResult);
		} elseif (is_object($actionResult) && method_exists($actionResult, '__toString')) {
			$this->response->appendContent((string)$actionResult);
		}
	}
	
	/**
	 * Dummy function to add this Controller to the Navigation
	 *
	 * @return void
	 * @author Marc Neuhaus
	 * @Admin\Annotations\Navigation(title="Models", position="top", priority="1000")
	 */
	public function indexAction(){
		
	}

	public function __call($name, $args){
		$actionName = str_replace("Action","",$name);
		$this->prepare($actionName);
		$action = $this->getActionByShortName($name);

		if(!is_object($action))
			parent::redirect("index");
			
		\Admin\Core\API::addTitleSegment("Admin");
		\Admin\Core\API::addTitleSegment($action->__toString());
		if(isset($this->being))
			\Admin\Core\API::addTitleSegment(\Admin\Core\Helper::getShortName($this->being));
		
		if($action !== null){
			$ids = explode(",", $this->id);
			$action->execute($this->being, $ids);
		}
	}
	
	public function compileShortNames(){
		$cache = $this->cacheManager->getCache('Admin_Cache');
		$identifier = "ClassShortNames-".sha1(implode("-",$this->adapters));

		if(!$cache->has($identifier) || true){
			$shortNames = array();
			foreach ($this->adapters as $adapter) {
				$adapters[$adapter] = $this->objectManager->get($adapter);
				foreach ($adapters[$adapter]->getGroups() as $group => $beings) {
					foreach ($beings as $conf) {
						$being = $conf["being"];
						$shortName = str_replace("domain_model_", "", strtolower(str_replace("\\", "_", $being)));
						$shortNames[$being] = $shortName;
						$shortNames[$shortName] = $being;
					}
				}
			}
			
			$cache->set($identifier,$shortNames);
		}else{
			$shortNames = $cache->get($identifier);
		}
		
		return $shortNames;
	}

	private function prepare($action){
		$this->start = microtime();

		$this->adapters = $this->helper->getAdapters();
		$this->settings = $this->helper->getSettings();
		
		\Admin\Core\API::set("classShortNames", $this->compileShortNames());
		\Admin\Core\API::set("action", $action);
		
		if($this->request->hasArgument("being")){
			$this->being = $this->request->getArgument("being");
			if(!stristr($this->being, "\\"))
				$this->being = \Admin\Core\API::get("classShortNames", $this->being);
			\Admin\Core\API::set("being", $this->being);
			
			$this->adapter = $this->helper->getAdapterByBeing($this->being);
			\Admin\Core\API::set("adapter", $this->adapter);

			$this->group = $this->helper->getGroupByBeing($this->being);
			\Admin\Core\API::set("group", $this->group);
		}

		if($this->request->hasArgument("id")){
			$this->id = $this->request->getArgument("id");
			if(is_array($this->id))
				$this->id = implode(",", $this->id);
		}

		$user = $this->securityManager->getUser();
		
		if(!isset($user) || !is_object($user)){
			$this->securityManager->redirectToLogin();
		}else{
			$allowedBeings = array("view"=>array());
			try{
				foreach ($user->getRoles() as $role) {
					foreach ($role->getGrant() as $policy) {
						$allowedBeings[$policy->getAction()][] = $policy->getBeing();
					}
				}
			} catch (\Doctrine\ORM\EntityNotFoundException $e){
				unset($user);
				$this->securityManager->redirectToLogin();
			}
			$this->user = $user;
		}

		$groups = $this->helper->getGroups();
		ksort($groups);
		foreach($groups as $package => $group){
			foreach($group["beings"] as $key => $being){
				if( !in_array($being["being"],$allowedBeings["view"]) )
					if( !$user->isAdmin() )
						unset($groups[$package]["beings"][$key]);
				
				if(!empty($this->adapter)){
					if($being["being"] == $this->being && $being["adapter"] == $this->adapter){
						$groups[$package]["beings"][$key]["active"] = true;
					}else{
						$groups[$package]["beings"][$key]["active"] = false;
					}
				}
			}
			if(empty($groups[$package]["beings"]))
				unset($groups[$package]);
		}

		$this->view = $this->resolveView();
		
		\Admin\Core\API::set("user", $user);
			
		if ($this->view !== NULL) {
			$this->view->assign('settings', $this->settings);
			$this->initializeView($this->view);
		}
		
		$this->view->assign('groups',$groups);

		$this->setTemplate($action);
		$context = getenv("FLOW3_CONTEXT") ? getenv("FLOW3_CONTEXT") : "Production";
		$this->view->assign("context",$context);
		
		$hasId = isset($this->id) ? true : false;
		$topBarActions = $this->getActions($action, $this->being, $hasId);
		$this->view->assign('topBarActions',$topBarActions);
	}

	public function setTemplate($action){
		$replacements = array(
			"@action" => ucfirst($action),
			"@variant" => "Default",
			"@package" => "Admin",
		);
		
		if(!empty($this->being)){
			if(class_exists($this->being, false)){
				$replacements["@package"] = $this->helper->getPackageByClassName($this->being) ? $this->helper->getPackageByClassName($this->being) : "Admin";
				$replacements["@being"] =\Admin\Core\Helper::getShortName($this->being);
				
				$being = $this->helper->getBeing($this->being);
				$replacements["@variant"] = $being->variant->getVariant($action);
			}
		}
		
		if($this->request->hasArgument("variant")){
			$replacements["@variant"] = $this->request->getArgument("variant");
		}

		$cache = $this->cacheManager->getCache('Admin_TemplateCache');
		$identifier = str_replace(".", "_", implode("-",$replacements));
		$noTemplate = false;
		if(!$cache->has($identifier)){
			try{
				$template = $this->helper->getPathByPatternFallbacks("Views",$replacements);
			}catch (\Exception $e){
				$noTemplate = true;
			}
			if(!$noTemplate)
				$cache->set($identifier,$template);
		}else{
			$template = $cache->get($identifier);
		}
		
		if(!$noTemplate){
			$this->view->setTemplatePathAndFilename($template);
			
			if($this->request->hasArgument("being")){
				$meta["being"]["identifier"] = $this->request->getArgument("being");
				$meta["being"]["name"] = $this->request->getArgument("being");
				\Admin\Core\API::set("package",$replacements["@package"]);
			}
		}
	}

	private function getAdapter(){
		if(isset($this->adapter)){
			$adapter =  $this->objectManager->get($this->adapter);
			if(!empty($this->being) && class_exists($this->being, false)){
				$value = $this->reflectionService->getClassAnnotation($this->being, 'adapter');
                                if($value !== NULL && class_exists("\\".$value[0], false)){
					$adapter = $this->objectManager->get($value[0]);
				}
			}
			$adapter->init();

			return $adapter;
		}else{
			return null;
		}
	}
	
	public function getActions($action = null, $being = null, $id = false){
#		$cache = $this->cacheManager->getCache('Admin_ActionCache');
#		$identifier = sha1($action.$being.$id.$this->adapter);

#		if(!$cache->has($identifier) && false){
			$actions = array();
			foreach($this->reflectionService->getAllImplementationClassNamesForInterface('Admin\Core\Actions\ActionInterface') as $actionClassName) {
				$inheritingClasses = $this->reflectionService->getAllSubClassNamesForClass($actionClassName);
				foreach($inheritingClasses as $inheritingClass){
					$inheritedObject = new $inheritingClass($this->getAdapter(), $this->request, $this->view, $this);
					if($inheritedObject->override($actionClassName,$being)){
						$actionClassName = $inheritedObject;
					}
					unset($inheritedObject);
				}
				
				#$a = $this->objectManager->get($actionClassName, $this->getAdapter(), $this->request, $this->view, $this);
				$a = new $actionClassName($this->getAdapter(), $this->request, $this->view, $this);
				if($a->canHandle($being, $action, $id)){
					if($this->securityManager->isAllowed($being,$a->getAction())){
						$actionName = \Admin\Core\Helper::getShortName($actionClassName);
						$actionName = str_replace("Action","",$actionName);
						$actions[$actionName] = $a;
					}
				}
			}
			ksort($actions);
			#$cache->set($identifier,$actions);
#		}else{
#			$actions = $cache->get($identifier);
#		}
		
		return $actions;
	}

	public function getActionByShortName($action = null){
		$actions = array();
		foreach($this->reflectionService->getAllImplementationClassNamesForInterface('Admin\Core\Actions\ActionInterface') as $actionClassName) {
			$actionName = \Admin\Core\Helper::getShortName($actionClassName);
			if(strtolower($actionName) == strtolower($action)){
				return $this->objectManager->get($actionClassName, $this->getAdapter(), $this->request, $this->view, $this);
			}
		}
		return null;
	}

	public function getRequest(){
		return $this->request;
	}

	public function getAction(){
		return str_replace("Action","",$this->actionMethodName);
	}

	public function redirect($actionName, $controllerName = NULL, $packageKey = NULL, array $arguments = NULL, $delay = 0, $statusCode = 303, $format = NULL) {
		return parent::redirect($actionName, $controllerName, $packageKey, $arguments, $delay, $statusCode, $format);
	}

	public function forward($actionName, $controllerName = NULL, $packageKey = NULL, array $arguments = NULL) {
		return parent::forward($actionName, $controllerName, $packageKey, $arguments);
	}

	/**
	 * Redirects the web request to another uri.
	 *
	 * NOTE: This method only supports web requests and will throw an exception
	 * if used with other request types.
	 *
	 * @param mixed $uri Either a string representation of a URI or a \TYPO3\FLOW3\Property\DataType\Uri object
	 * @param integer $delay (optional) The delay in seconds. Default is no delay.
	 * @param integer $statusCode (optional) The HTTP status code for the redirect. Default is "303 See Other"
	 * @throws \TYPO3\FLOW3\MVC\Exception\UnsupportedRequestTypeException If the request is not a web request
	 * @throws \TYPO3\FLOW3\MVC\Exception\StopActionException
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	protected function redirectToUri($uri, $delay = 0, $statusCode = 303) {
		if (!$this->request instanceof \TYPO3\FLOW3\MVC\Web\Request) throw new \TYPO3\FLOW3\MVC\Exception\UnsupportedRequestTypeException('redirect() only supports web requests.', 1220539734);

#		$uri = $this->request->getBaseUri() . (string)$uri;
		$escapedUri = htmlentities($uri, ENT_QUOTES, 'utf-8');
		$this->response->setContent('<html><head><meta http-equiv="refresh" content="' . intval($delay) . ';url=' . $escapedUri . '"/></head></html>');
		$this->response->setStatus($statusCode);
		$this->response->setHeader('Location', (string)$uri);
		throw new \TYPO3\FLOW3\MVC\Exception\StopActionException();
	}



	
	/**
	 * compares a security policy
	 *
	 * @param string $arguments 
	 * @param string $policy 
	 * @return void
	 * @author Marc Neuhaus
	 */
	public function comparePolicy($arguments, $policy){
		$being = $policy->getBeing();
		$action = $policy->getAction();

		if( $being == $arguments["being"]  && $action == $arguments["action"] )
			return true;

		return false;
	}
}

?>