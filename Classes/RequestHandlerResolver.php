<?php
declare(ENCODING = 'utf-8');
namespace F3\Admin;

/*                                                                        *
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

/**
 * Analyzes the raw request and delivers a request handler which can handle it.
 *
 * @version $Id: RequestHandlerResolver.php 3837 2010-02-22 15:17:24Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class RequestHandlerResolver extends \F3\FLOW3\MVC\RequestHandlerResolver {
	/**
	 * Analyzes the raw request and tries to find a request handler which can handle
	 * it. If none is found, an exception is thrown.
	 *
	 * @return \F3\FLOW3\MVC\RequestHandler A request handler
	 * @throws \F3\FLOW3\MVC\Exception
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function resolveRequestHandler() {
		\F3\Admin\Register::set("objectManager",$this->objectManager);
		return parent::resolveRequestHandler();
	}
}

?>