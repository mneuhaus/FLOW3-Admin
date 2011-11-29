<?php
 
namespace Admin\Controller;

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

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A disposable controller for some testing
 *
 * @version $Id: $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class DocController extends \TYPO3\FLOW3\MVC\Controller\ActionController {
	
	/**
	 * @var \TYPO3\FLOW3\Package\PackageManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $packageManager;
	
	/**
	 * Show Documentation
	 *
	 * @param string $lang 
	 * @param string $doc 
	 * @param string $page 
	 * @return void
	 * @author Marc Neuhaus
	 */
	public function indexAction($lang = "en", $doc = "Manual", $page = "index.html"){
		\Admin\Core\API::addTitleSegment("Documentation");
		\Admin\Core\API::addTitleSegment($page);
			
		if($this->request->hasArgument("subpage1")){
			$c = 1;
			$directories = array($page);
			while($c < 10){
				if($this->request->hasArgument("subpage".$c))
					$directories[] = $this->request->getArgument("subpage".$c);
				else
					break;
				$c++;
			}
			$page = implode("/", $directories);
		}
		
		if(!stristr($page, ".html"))
			$page.= ".html";
			
		$page = urldecode($page);
		
		if($lang == "index"){
			$lang = "en";
		}
		
		$package = $this->packageManager->getPackage("Admin");
		$path = "resource://Admin/Private/Docs/";
		
		$template = $path . $doc . "/" . $lang . "/html/" . $page ;
		
		$this->view->setTemplatePathAndFilename($template);
		
		$this->view->assign("base", "/admin/doc/en/");
		
		$content = $this->view->render();
		$content = preg_replace('/internal" href="([A-Za-z0-9])/', 'internal" href="/admin/doc/' . $lang . '/\\1', $content);
		$content = str_replace('href="#', 'href="/admin/doc/' . $lang . '/' . $page . '#', $content);
		$content = str_replace('{ ', '{', $content);
		
		return $content;
	}
}

?>