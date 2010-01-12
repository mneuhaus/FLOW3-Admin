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
 * post route part handler
 *
 * @version $Id: PostRoutePartHandler.php 3480 2009-11-19 16:45:21Z robert $
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 */
class PostRoutePartHandler extends \F3\FLOW3\MVC\Web\Routing\DynamicRoutePart {

	/**
	 * Splits the given value into the date and title of the post and sets this
	 * value to an identity array accordingly.
	 *
	 * @param string $value The value (ie. part of the request path) to match. This string is rendered by findValueToMatch()
	 * @return boolean TRUE if the request path formally matched
	 */
	protected function matchValue($value) {
		if (!parent::matchValue($value)) {
			return FALSE;
		}
		$matches = array();
		preg_match('/^([0-9]{4})\/([0-9]{2})\/([0-9]{2})\/([a-zA-Z0-9\-]+)/', $value, $matches);
		$this->value = array(
			'__identity' => array(
				// next line commented, as it currently doesn't work as it should:
				// the date does not contain the time, so no match would be found...
				//'date' => new \DateTime($matches[1] . '-' . $matches[2] . '-' . $matches[3]),
				'title' => str_replace('-', ' ', $matches[4])
			)
		);
		return TRUE;
	}

	/**
	 * Checks if the remaining request path starts with the path signature of a post, which
	 * is: YYYY/MM/DD/TITLE eg. 2009/03/09/my-first-blog-entry
	 *
	 * If the request path matches this pattern, the matching part is returned as the "value
	 * to match" for further processing in matchValue(). The remaining part of the requestPath
	 * (eg. the format ".html") is ignored.
	 *
	 * @param string $requestPath The request path acting as the subject for matching in this Route Part
	 * @return string The post identifying part of the request path or an empty string if it doesn't match
	 */
	protected function findValueToMatch($requestPath) {
		$matches = array();
		preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2}\/[a-z0-9\-]+/', $requestPath, $matches);
		return (count($matches) === 1) ? current($matches) : '';
	}

	/**
	 * Resolves the name of the post
	 *
	 * @param \F3\Blog\Domain\Model\Post $value The Post object
	 * @return boolean TRUE if the post could be resolved and stored in $this->value, otherwise FALSE.
	 */
	protected function resolveValue($value) {
		if (!$value instanceof \F3\Blog\Domain\Model\Post) return FALSE;
		$this->value = $value->getDate()->format('Y/m/d/');
		$this->value .= strtolower(str_replace(' ', '-', $value->getTitle()));
		return TRUE;
	}
}
?>