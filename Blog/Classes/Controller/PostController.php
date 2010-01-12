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
 * The posts controller for the Blog package
 *
 * @version $Id: PostController.php 3550 2009-12-21 16:31:26Z robert $
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PostController extends \F3\FLOW3\MVC\Controller\ActionController {

	/**
	 * @inject
	 * @var \F3\Blog\Domain\Repository\BlogRepository
	 */
	protected $blogRepository;

	/**
	 * @var blog
	 */
	protected $blog;

	/**
	 * @inject
	 * @var \F3\Blog\Domain\Repository\PostRepository
	 */
	protected $postRepository;

	/**
	 * Initializes any action.
	 *
	 * @return void
	 */
	public function initializeAction() {
		$this->blog = $this->blogRepository->findActive();
		if ($this->blog === FALSE) {
			$this->redirect('index', 'Setup');
		}
	}

	/**
	 * List action for this controller. Displays latest posts
	 *
	 * @return string
	 */
	public function indexAction() {
		$posts = $this->postRepository->findByBlog($this->blog);
		$this->view->assign('blog', $this->blog);
		$this->view->assign('posts', $posts);
		$this->view->assign('recentPosts', $this->postRepository->findRecentByBlog($this->blog));
	}

	/**
	 * Action that displays one single post
	 *
	 * @param \F3\Blog\Domain\Model\Post $post The post to display
	 * @param \F3\Blog\Domain\Model\Comment $newComment If the comment form as has been submitted but the comment was not valid, this argument is used for displaying the entered values again
	 * @dontvalidate $newComment
	 * @return void
	 */
	public function showAction(\F3\Blog\Domain\Model\Post $post, \F3\Blog\Domain\Model\Comment $newComment = NULL) {
		$this->view->assign('post', $post);
		$this->view->assign('blog', $post->getBlog());
		$this->view->assign('previousPost', $this->postRepository->findPrevious($post));
		$this->view->assign('nextPost', $this->postRepository->findNext($post));
		$this->view->assign('recentPosts', $this->postRepository->findRecentByBlog($post->getBlog()));
		$this->view->assign('newComment', $newComment);
	}

	/**
	 * Displays a form for creating a new post
	 *
	 * @param \F3\Blog\Domain\Model\Post $newPost A fresh post object taken as a basis for the rendering
	 * @return string An HTML form for creating a new post
	 * @dontvalidate $newPost
	 * @dontverifyrequesthash
	 */
	public function newAction(\F3\Blog\Domain\Model\Post $newPost = NULL) {
		$this->view->assign('blog', $this->blog);
		$this->view->assign('existingPosts', $this->postRepository->findByBlog($this->blog));
		$this->view->assign('newPost', $newPost);
	}

	/**
	 * Creates a new post
	 *
	 * @param \F3\Blog\Domain\Model\Post $newPost A fresh Post object which has not yet been added to the repository
	 * @dontverifyrequesthash
	 * @return void
	 */
	public function createAction(\F3\Blog\Domain\Model\Post $newPost) {
		$this->blog->addPost($newPost);
		$this->flashMessageContainer->add('Your new post was created.');
		$this->redirect('index');
	}

	/**
	 * Override getErrorFlashMessage to present nice flash error messages.
	 *
	 * @return string
	 */
	protected function getErrorFlashMessage() {
		switch ($this->actionMethodName) {
			case 'createAction' :
				return 'Could not create the new post:';
			default :
				return parent::getErrorFlashMessage();
		}
	}
}
?>