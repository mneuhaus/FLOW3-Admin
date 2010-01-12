<?php
 
namespace F3\Blog\Controller;

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
 * Comments controller for the Blog package
 *
 * @version $Id: CommentController.php 3251 2009-09-30 17:30:17Z robert $
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CommentController extends \F3\FLOW3\MVC\Controller\ActionController {

	/**
	 * Creates a new comment
	 *
	 * @param \F3\Blog\Domain\Model\Post $post The post which will contain the new comment
	 * @param \F3\Blog\Domain\Model\Comment $newComment A fresh Comment object which has not yet been added to the repository
	 * @return void
	 */
	public function createAction(\F3\Blog\Domain\Model\Post $post, \F3\Blog\Domain\Model\Comment $newComment) {
		$post->addComment($newComment);
		$this->flashMessageContainer->add('Your new comment was created.');
		$this->redirect('show', 'Post', NULL, array('blog' => $post->getBlog(), 'post' => $post));
	}

	/**
	 * Override getErrorFlashMessage to present nice flash error messages.
	 *
	 * @return string
	 */
	protected function getErrorFlashMessage() {
		switch ($this->actionMethodName) {
			case 'createAction' :
				return 'Could not create the new comment.';
			default :
				return parent::getErrorFlashMessage();
		}
	}
}

?>