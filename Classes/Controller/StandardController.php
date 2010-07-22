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

	/**
	 * @var F3\FLOW3\Security\Context
	 * @inject
	 */
	protected $securityContext;
    
    /**
	 * @var F3\FLOW3\Cache\CacheManager
	 * @inject
	 */
	protected $cacheManager;

	protected $model = "";
    protected $being = null;
    protected $id = null;
    
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
			#throw new \F3\FLOW3\MVC\Exception\NoSuchActionException('An action "' . $actionMethodName . '" does not exist in controller "' . get_class($this) . '".', 1186669086);
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
		$preparedArguments = array();
		foreach ($this->arguments as $argument) {
			$preparedArguments[] = $argument->getValue();
		}

        if ($this->argumentsMappingResults->hasErrors()) {
			$actionResult = call_user_func(array($this, $this->errorMethodName));
		} else {
			#$actionResult = call_user_func_array(array($this, $this->actionMethodName), $preparedArguments);
            $actionResult = $this->__call($this->actionMethodName, $preparedArguments);
		}

		if ($actionResult === NULL && $this->view instanceof \F3\FLOW3\MVC\View\ViewInterface) {
			$this->response->appendContent($this->view->render());
		} elseif (is_string($actionResult) && strlen($actionResult) > 0) {
			$this->response->appendContent($actionResult);
		} elseif (is_object($actionResult) && method_exists($actionResult, '__toString')) {
			$this->response->appendContent((string)$actionResult);
		}
	}

    public function __call($name, $args){
        $actionName = str_replace("Action","",$name);
		$this->prepare($actionName);
        $action = $this->getActionByShortName($name);
        
        if($action !== null){
           $result = $action->execute($this->being, $this->id);
           #\F3\var_dump($result);
           if(is_array($result)){
               #$this->redirect($result[0],$result[1],$result[1],$result[3]);
           }
        }
    }

	private function prepare($action){
		$this->start = microtime();
		if(class_exists("\F3\Dump\Dump")){
			\F3\Dump\Dump::getInstance();
		}
        $title = array("Admin",ucfirst($action));

		$this->adapters = $this->helper->getAdapters();
		$this->settings = $this->helper->getSettings();
		\F3\Admin\Register::set("settings",$this->settings);
		\F3\Admin\Register::set("session",$this->session);
		\F3\Admin\Register::set("action",$action);

		if($this->request->hasArgument("adapter")){
			$this->adapter = $this->request->getArgument("adapter");
			\F3\Admin\Register::set("adapter",$this->adapter);
            #$title[] = \F3\Admin\Core\Helper::getShortName($this->adapter);
		}

		if($this->request->hasArgument("being")){
			$this->being = $this->request->getArgument("being");
			\F3\Admin\Register::set("being",$this->being);

			$this->group = $this->helper->getGroupByBeing($this->being);
			\F3\Admin\Register::set("group",$this->group);
            $title[] = \F3\Admin\Core\Helper::getShortName($this->being);
		}

		if($this->request->hasArgument("id")){
			$this->id = $this->request->getArgument("id");
			\F3\Admin\Register::set("being_id",$this->id);
		}

        $activeTokens = $this->securityContext->getAuthenticationTokens();
        $allowedBeings = array("view"=>array());
		foreach ($activeTokens as $token) {
			if ( $token->isAuthenticated() && is_callable(array($token,"getUser")) ) {
				$user = $token->getUser();
				$this->view->assign('user', $user);

                foreach ($user->getRoles() as $role) {
                    foreach ($role->getGrant() as $policy) {
                        $allowedBeings[$policy->getAction()][] = $policy->getBeing();
                    }
                }
                break;
			}
		}

        if(!isset($user) || !is_object($user)){
			parent::redirect('index', 'Login');
            throw new \F3\FLOW3\MVC\Exception\StopActionException();
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

		$this->view->assign('groups',$groups);

		$this->setTemplate($action);
		$context = getenv("FLOW3_CONTEXT") ? getenv("FLOW3_CONTEXT") : "Production";
		$this->view->assign("context",$context);

        $topBarActions = $this->getActions($action, $this->being, false);
		$this->view->assign('topBarActions',$topBarActions);

        $this->view->assign("title",implode(" - ",array_reverse($title)));
	}

	public function setTemplate($action){
		$replacements = array(
			"@action" => ucfirst($action),
			"@variant" => "Default",
			"@package" => "Admin",
		);
		if(!empty($this->being)){
			if(class_exists($this->being)){
				$tags = $this->reflectionService->getClassTagsValues($this->being);
				if(in_array($action."view",array_keys($tags))){
					$variant = $tags[$action."view"][0];
				}
				$replacements["@package"] = $this->helper->getPackageByClassName($this->being) ? $this->helper->getPackageByClassName($this->being) : "Admin";
				$replacements["@being"] =\F3\Admin\Core\Helper::getShortName($this->being);
				if(array_key_exists("variant-".$action,$tags)){
					$replacements["@variant"] = ucfirst(current($tags["variant-".$action]));
				}
			}
		}

        $cache = $this->cacheManager->getCache('Admin_TemplateCache');
        $identifier = implode("-",$replacements);
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
				$meta["being"]["name"] = $this->getAdapter()->getName($this->request->getArgument("being"));
				\F3\Admin\Register::set("package",$replacements["@package"]);
			}
		}
	}

	private function getAdapter(){
        if(isset($this->adapter)){
            $adapter =  $this->objectManager->getObject($this->adapter);
            if(!empty($this->being) && class_exists($this->being)){
                $tags = $this->reflectionService->getClassTagsValues($this->being);
                if(array_key_exists("adapter",$tags) && class_exists("\\".$tags["adapter"][0])){
                    $adapter = $this->objectManager->getObject($tags["adapter"][0]);
                }
            }
            $adapter->init();

            return $adapter;
        }else{
            return null;
        }
	}
    
    public function getActions($action = null, $being = null, $id = false){
#        $cache = $this->cacheManager->getCache('Admin_ActionCache');
#        $identifier = sha1($action.$being.$id.$this->adapter);

#        if(!$cache->has($identifier) && false){
            $actions = array();
            foreach($this->reflectionService->getAllImplementationClassNamesForInterface('F3\Admin\Controller\Actions\ActionInterface') as $actionClassName) {
				$inheritingClasses = $this->reflectionService->getAllSubClassNamesForClass($actionClassName);
				foreach($inheritingClasses as $inheritingClass){
					$inheritedObject = new $inheritingClass($this->getAdapter(), $this->request, $this->view, $this);
					if($inheritedObject->override($actionClassName,$being)){
						$actionClassName = $inheritedObject;
					}
					unset($inheritedObject);
				}
				
                #$a = $this->objectManager->create($actionClassName, $this->getAdapter(), $this->request, $this->view, $this);
                $a = new $actionClassName($this->getAdapter(), $this->request, $this->view, $this);
                if($a->canHandle($being, $action, $id)){
                    if($this->isAllowed($being,$a->getAction())){
                        $actionName = \F3\Admin\Core\Helper::getShortName($actionClassName);
                        $actionName = str_replace("Action","",$actionName);
                        $actions[$actionName] = $a;
                    }
                }
            }
            ksort($actions);
            #$cache->set($identifier,$actions);
#        }else{
#            $actions = $cache->get($identifier);
#        }
        
        return $actions;
    }

    public function getActionByShortName($action = null){
        $actions = array();
        foreach($this->reflectionService->getAllImplementationClassNamesForInterface('F3\Admin\Controller\Actions\ActionInterface') as $actionClassName) {
            $actionName = \F3\Admin\Core\Helper::getShortName($actionClassName);
            if(strtolower($actionName) == strtolower($action)){
                #return $this->objectManager->create($actionClassName, $this->getAdapter(), $this->request, $this->view, $this);
                return new $actionClassName($this->getAdapter(), $this->request, $this->view, $this);
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

	public function redirect($actionName, $controllerName = NULL, $packageKey = NULL, array $arguments = NULL, $delay = 0, $statusCode = 303) {
        return parent::redirect($actionName, $controllerName, $packageKey, $arguments, $delay, $statusCode);
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
	 * @param mixed $uri Either a string representation of a URI or a \F3\FLOW3\Property\DataType\Uri object
	 * @param integer $delay (optional) The delay in seconds. Default is no delay.
	 * @param integer $statusCode (optional) The HTTP status code for the redirect. Default is "303 See Other"
	 * @throws \F3\FLOW3\MVC\Exception\UnsupportedRequestTypeException If the request is not a web request
	 * @throws \F3\FLOW3\MVC\Exception\StopActionException
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	protected function redirectToUri($uri, $delay = 0, $statusCode = 303) {
		if (!$this->request instanceof \F3\FLOW3\MVC\Web\Request) throw new \F3\FLOW3\MVC\Exception\UnsupportedRequestTypeException('redirect() only supports web requests.', 1220539734);

		$uri = $this->request->getBaseUri() . (string)$uri;
		$escapedUri = htmlentities($uri, ENT_QUOTES, 'utf-8');
		$this->response->setContent('<html><head><meta http-equiv="refresh" content="' . intval($delay) . ';url=' . $escapedUri . '"/></head></html>');
		$this->response->setStatus($statusCode);
		$this->response->setHeader('Location', (string)$uri);
        return;
		throw new \F3\FLOW3\MVC\Exception\StopActionException();
	}

    public function isAllowed($being,$action){
        foreach ($this->securityContext->getAuthenticationTokens() as $token){
            if(is_callable(array($token,"getUser"))){
                $user = $token->getUser();
                if($user->isAdmin())
                    return true;

                foreach ($user->getRoles() as $role) {
                    foreach ($role->getGrant() as $policy) {
                        if($this->comparePolicy(array("action"=>$action,"being"=>$being), $policy)) return true;
                    }
                }
            }
        }
        return false;
    }
    
    public function comparePolicy($arguments,$policy){
        $being = $policy->getBeing();
        $action = $policy->getAction();

        if( $being == $arguments["being"]  && $action == $arguments["action"] )
            return true;

        return false;
    }
}

?>