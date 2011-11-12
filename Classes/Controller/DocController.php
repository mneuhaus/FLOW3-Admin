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
	 * @Admin\Annotations\Navigation(title="Documentation", position="top")
	 */
	public function indexAction($lang = "en", $doc = "Manual", $page = "1_Index"){
		
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
		
		$this->renderMarkdown($lang, $doc, $page);
	}
	
	public function renderMarkdown($lang, $doc, $page){
		$package = $this->packageManager->getPackage("Admin");
		$docPath = $package->getDocumentationPath() . sprintf("%s/Markdown/%s/%s.md", $doc, $lang, $page);
		
		include_once($package->getResourcesPath() . "/Private/PHP/ElephantMarkdown/markdown.php");
		$content = \Markdown::parse(file_get_contents($docPath));
		
#		include_once($package->getResourcesPath() . "/Private/PHP/markdown-oo-php/Text.php");
#		$m = new \Markdown_Text(file_get_contents($docPath));
#		$content = $m->__toString();
		
		$content = str_replace('src="img', 'width="100%" src="/_Resources/Static/Packages/Admin/img', $content);
		$content = str_replace("<code>", "<code class='prettyprint'>", $content);
		$this->view->assign("content", $content);
		
		$docPath = $package->getDocumentationPath() . sprintf("%s/Markdown/%s/", $doc, $lang);
		$files = \TYPO3\FLOW3\Utility\Files::readDirectoryRecursively($docPath, "md");
		$pages = array();
		foreach ($files as $file) {
			$filename = pathinfo($file, PATHINFO_FILENAME);
			$pages[ucfirst($filename)] = dirname(str_replace($docPath, "", $file)) . "/" . $filename;
		}
		
		$pages = $this->getFiles($docPath);
		$this->view->assign("pages", $pages);
	}
	
	public function getFiles($path, $filetypes="md"){
		$pages = array();
		$files = scandir($path);

		if(!is_array($filetypes))
			$filetypes = explode(",", $filetypes);
			
		foreach ($files as $file) {
			if(substr($file, 0, 1) == ".") continue;
			if($file == "img") continue;
			
			$filePath = $path . $file;
			$filename = pathinfo($file, PATHINFO_FILENAME);
			$fileTitle = str_replace("_", ". ", ucfirst($filename));
			
			if(!isset($pages[$fileTitle])) 
				$pages[$fileTitle] = array();
				
			if(is_dir($filePath)){
				$pages[$fileTitle]["children"] = $this->getFiles($path . "/" . $filename, $filetypes);
			}else{
				$extension = pathinfo($file, PATHINFO_EXTENSION);
				if(!in_array($extension, $filetypes)) continue;
				
				$pages[$fileTitle]["path"] = $filename;
			}
		}
		return $pages;
	}
}

?>