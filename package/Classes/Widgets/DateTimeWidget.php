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
class DateTimeWidget extends \F3\Admin\Widgets\AbstractWidget{
    /**
	 *
	 * @param string $name
	 * @param object $object
	 * @param object $tags
	 * @return string "Form"-Tag.
	 * @api
	 */
	public function render($name,$object,$objectName,$tags) {
        $value = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$name);
		
        $this->view->assign("name",$name);
        $this->view->assign("object",$object);
        $this->view->assign("objectname",$objectName);
		if(is_object($value)){
	        $this->view->assign("value",date("l, F d, Y h:m:s A",$value->getTimestamp()));
		}else{
			$this->view->assign("value",date("l, F d, Y h:m:s A"));
		}

        return array("widget" => $this->view->render());
	}

    public function convert($value){
        $object = \DateTime::createFromFormat("l, F d, Y h:m:s A",$value);
		return $object;
	}
}

?>
