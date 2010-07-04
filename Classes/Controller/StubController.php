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
class StubController extends \F3\FLOW3\MVC\Controller\ActionController {
    public function indexAction(){
		\F3\Dump\Dump::getInstance();
        $pattern = dirname(__FILE__)."/../../Resources/Public/img/cc/*/*/*.png";
        $path = "../img/cc/";
        echo "<pre>";
        $content = "";
        foreach (glob($pattern) as $filename) {
            $size = getimagesize($filename);
            $filename = str_replace("/Users/mneuhaus/Sites/flow3/Packages/Applications/Admin/Classes/Controller/../../Resources/Public/img/cc/","",$filename);
            preg_match_all("/(.).+\/(.+)x.+\/([^.]+).+/",$filename,$matches);
            $selector = ".ui-button-".$matches[1][0].$matches[2][0]."-".$matches[3][0];
            $content.=$selector ."{ background: url(".$path.$filename.") no-repeat; width:".$size[0]."px; height:".$size[1]."px;}\n";
            $selectors[] = $selector;
        }
        $content = implode(",\n",$selectors)." {  }\n".$content;
        return $content;
    }
}

?>