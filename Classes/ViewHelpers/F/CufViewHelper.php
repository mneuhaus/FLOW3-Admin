<?php
 
namespace F3\Admin\ViewHelpers\F;

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
class CufViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {
	/**
	 * Renders a hidden form field containing the technical identity of the given object.
	 *
	 * @param $value String
	 * @param $funcs String
	 */
	public function render($value,$funcs) {
		$functions = explode(",",$funcs);
		foreach ($functions as $func) {
			if(function_exists($func)){
				$value = call_user_func($func,$value);
			}
			
			if(method_exists($this,$func)){
				$value = call_user_func(array($this,$func),$value);
			}
		}
		return $value;
	}

	function m($t){
		if(substr($t,-1) == "s")
			return $t."es";
		if(substr($t,-1) == "y")
			return substr($t,0,-1)."ies";
		else
			return $t."s";
	}
}

?>