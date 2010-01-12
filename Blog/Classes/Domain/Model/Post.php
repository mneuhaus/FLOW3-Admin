<?php
 
namespace F3\Blog\Domain\Model;

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
 * A blog post
 *
 * @version $Id: Post.php 3550 2009-12-21 16:31:26Z robert $
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 * @entity
 * @autoadmin
 */
class Post {

	/**
	 * @var \F3\Blog\Domain\Model\Blog
	 * @identity
	 */
	protected $blog;

	/**
	 * @var string
	 * @validate StringLength(minimum = 3, maximum = 50)
	 * @identity
	 * @title
	 */
	protected $title;

	/**
	 * @var \DateTime
	 * @identity
	 */
	protected $date;

	/**
	 * @var string
	 * @validate StringLength(minimum = 3, maximum = 50)
	 */
	protected $author;

	/**
	 * @var string
	 * @widget textarea
	 * FIXME validate HTML
	 */
	protected $content;

	/**
	 * @var \F3\Blog\Domain\Model\Image
	 */
	protected $image;

	/**
	 * @var \SplObjectStorage<\F3\Blog\Domain\Model\Tag>
	 */
	protected $tags;

	/**
	 * @var \SplObjectStorage<\F3\Blog\Domain\Model\Category>
	 * FIXME validate Count(atLeast = 1)
	 */
	protected $categories;

	/**
	 * @var \SplObjectStorage<\F3\Blog\Domain\Model\Comment>
	 */
	protected $comments;

	/**
	 * @var \SplObjectStorage<\F3\Blog\Domain\Model\Post>
	 */
	protected $relatedPosts;

	/**
	 * Constructs this post
	 *
	 */
	public function __construct() {
		$this->date = new \DateTime();
		$this->tags = new \SplObjectStorage();
		$this->categories = new \SplObjectStorage();
		$this->comments = new \SplObjectStorage();
		$this->relatedPosts = new \SplObjectStorage();
	}

	/**
	 * Sets the blog this post is part of
	 *
	 * @param \F3\Blog\Domain\Model\Blog $blog The blog
	 * @return void
	 */
	public function setBlog(\F3\Blog\Domain\Model\Blog $blog) {
		$this->blog = $blog;
	}

	/**
	 * Sets the blog this post is part of
	 *
	 * @return void
	 */
	public function clearBlog() {
		$this->blog = null;
	}

	/**
	 * Returns the blog this post is part of
	 *
	 * @return \F3\Blog\Domain\Model\Blog The blog this post is part of
	 */
	public function getBlog() {
		return $this->blog;
	}

	/**
	 * Setter for title
	 *
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Getter for title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Setter for date
	 *
	 * @param \DateTime $date
	 * @return void
	 */
	public function setDate(\DateTime $date) {
		$this->date = $date;
	}

	/**
	 * Getter for date
	 *
	 * @return \DateTime
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * Setter for tags
	 *
	 * @param \SplObjectStorage<\F3\Blog\Domain\Model\Tag> $tags One or more \F3\Blog\Domain\Model\Tag objects
	 * @return void
	 */
	public function setTags(\SplObjectStorage $tags) {
		$this->tags = clone $tags;
	}

	/**
	 * Adds a tag to this post
	 *
	 * @param \F3\Blog\Domain\Model\Tag $tag
	 * @return void
	 */
	public function addTag(\F3\Blog\Domain\Model\Tag $tag) {
		$this->tags->attach($tag);
	}

	/**
	 * Getter for tags
	 *
	 * @return \SplObjectStorage<\F3\Blog\Domain\Model\Tag> The tags
	 */
	public function getTags() {
		return clone $this->tags;
	}

	/**
	 *
	 * @return \SplObjectStorage<\F3\Blog\Domain\Model\Category
	 */
	public function getCategories() {
		return clone $this->categories;
	}

	/**
	 *
	 * @param \SplObjectStorage $categories
	 */
	public function setCategories(\SplObjectStorage $categories) {
		$this->categories = $categories;
	}

	/**
	 * Sets the author for this post
	 *
	 * @param string $author
	 * @return void
	 */
	public function setAuthor($author) {
		$this->author = $author;
	}

	/**
	 * Getter for author
	 *
	 * @return string
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * Sets the content for this post
	 *
	 * @param string $content
	 * @return void
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * Getter for content
	 *
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Sets the image for this post
	 *
	 * @param \F3\Blog\Domain\Model\Image $image
	 * @return void
	 */
	public function setImage(\F3\Blog\Domain\Model\Image $image) {
		$this->image = $image;
	}

	/**
	 * Getter for image
	 *
	 * @return \F3\Blog\Domain\Model\Image
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * Adds a comment to this post
	 *
	 * @param \F3\Blog\Domain\Model\Comment $comment
	 * @return void
	 */
	public function addComment(\F3\Blog\Domain\Model\Comment $comment) {
		$this->comments->attach($comment);
	}

	/**
	 * Returns the comments to this post
	 *
	 * @return array of \F3\Blog\Domain\Model\Comment
	 */
	public function getComments() {
		return $this->comments;
	}

	/**
	 * Returns the number of comments
	 *
	 * @return integer The number of comments
	 */
	public function getNumberOfComments() {
		return count($this->comments);
	}

	/**
	 * Sets the posts related to this post
	 *
	 * @param \SplObjectStorage<\F3\Blog\Domain\Model\Post> $relatedPosts The related posts
	 * @return void
	 */
	public function setRelatedPosts(\SplObjectStorage $relatedPosts) {
		$this->relatedPosts = clone $relatedPosts;
	}

	/**
	 * Returns the posts related to this post
	 *
	 * @return \SplObjectStorage<\F3\Blog\Domain\Model\Post> The related posts
	 */
	public function getRelatedPosts() {
		return clone $this->relatedPosts;
	}

}

?>
