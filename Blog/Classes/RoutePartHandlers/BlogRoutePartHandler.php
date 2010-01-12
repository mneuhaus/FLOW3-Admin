<?php
 
namespace F3\Blog\RoutePartHandlers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Blog".                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Blog route part handler
 *
 * @version $Id: BlogRoutePartHandler.php 3546 2009-12-15 15:26:47Z k-fish $
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 */
class BlogRoutePartHandler extends \F3\FLOW3\MVC\Web\Routing\DynamicRoutePart {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * Injects settings of the Blog package
	 *
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Resolves the identifier of the blog
	 *
	 * @param \F3\Blog\Domain\Model\Blog $value The Blog object
	 * @return boolean TRUE if the name of the blog could be resolved and stored in $this->value, otherwise FALSE.
	 */
	protected function resolveValue($value) {
		if (!$value instanceof \F3\Blog\Domain\Model\Blog) return FALSE;
		$this->value = $value->getIdentifier();
		return TRUE;
	}

	/**
	 * While matching, converts the blog identifier into an identifer array
	 *
	 * @param string $value value to match, the blog identifier
	 * @return boolean TRUE if value could be matched successfully, otherwise FALSE.
	 */
	protected function matchValue($value) {
		if ($value === NULL || $value === '') return FALSE;
		$this->value = array('__identity' => array('identifier' => $value));
		return TRUE;
	}
}
?>