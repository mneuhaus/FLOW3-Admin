Basic Usage
===========

There are 2 Ways to Configure the Admin Interface: 

1. Settings.yaml
2. Class Reflections inside the Models

	**Note:** The Settings.yaml overrules the Class Reflections in order to make it Possible to change the Behaviour of 3rd Party Packages without messing with external Code.  

**Settings.yaml**::

	Doctrine:
		Beings: 
			\TYPO3\Blog\Domain\Model\Post:
				autoadmin: true 
				properties:
					content:
						widget: TextArea

This Example Activates the Post model of the Blog Example (autoadmin:true) and Changes the Widget for the Content Property from a simple Textfield to a Textarea

**Class Reflections**::

	/**
	 * A blog post
	 * ...
	 * @Admin\Annotations\Active 
	 */
	class Post { 
		/**
		 * @var string
		 * @Admin\Annotations\Widget("TextArea")
		 */
		protected $content; 
	}

This Example Does the exact same thing as the Settings.yaml Example but this time inside the Post.php file with the Tag @Admin\Annotations\Active and @Admin\Annotations\Widget TextArea
