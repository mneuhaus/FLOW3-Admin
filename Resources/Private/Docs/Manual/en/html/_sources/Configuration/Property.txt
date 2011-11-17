Property Configurations
#######################


Label
*****
With this Tag you can set the Label for a Property which is by Default a CamelCased version of the propertyname

**Class Reflection**::

	/**
	 * @var string
	 * @Admin\Annotations\Label("Post Title")
	 */
	 protected $title;

**YAML**::

	TYPO3\Blog\Domain\Model\Post: 
		Properties:
			Title:
				Label: Post Title


Widget
******
Instead of the automatically Assigned Widget you can use this Tag to specify a specific Widget.

**Class Reflection**::

	/**
	 * @var string
	 * @Admin\Annotations\Widget("TextArea")
	 */
	 protected $content;

**YAML**::

	TYPO3\Blog\Domain\Model\Post: 
		Properties:
			Content:
				Widget: TextArea

Ignore
******
There are a number of Properties which have no use to be Administrated through a GUI. With the ignore Tag you can control the Visibility of the Property to the Admin Interface.

Ignore the Property Completly
=============================
**Class Reflection**::

	/**
	 * @var string
	 * @Admin\Annotations\Ignore 
	 */
	protected $id;
	
**YAML**::

	TYPO3\Blog\Domain\Model\Post: 
		Properties:
			Id:
				Ignore: true

Ignore the Property in specific Views
=====================================
**Class Reflection**::

	/**
	 * @var string
	 * @Admin\Annotations\Ignore list,view */
	protected $content;


**YAML**::

	TYPO3\Blog\Domain\Model\Post: 
		Properties:
			Content:
				Ignore: list,view
				
Infotext
********
For more Information about a Property aside from the Title you can provide an Infotext that will be shown beside/below the Input Widgets in the Create and Update Views

**Class Reflection**::

	/**
	 * @var string
	 * @Admin\Annotations\InfoText("Please tell us who you are")
	 */
	protexted $author;

**YAML**::

	TYPO3\Blog\Domain\Model\Post: 
		Properties:
			Author:
				Infotext: Please tell us who you are

Class
*****
Adds one or more Classes to the Input Widget

**Class Reflection**::

	/**
	 * @string
	 * @Admin\Annotations\Class("content")
	 */
	protected $content;

**YAML**::

	TYPO3\Blog\Domain\Modle\Post: 
		Properties:
			Content:
				Class: content

Validate
********
Please Check the FLOW3 Documentation for the Validation rule: 
http://flow3.typo3.org/documentation/guide/partii/validation.html


Title
*****
This option only works if you use the MagicModel!
You can Specify any Property that can be Converted to a String as a Title to be used as a simple String Repesentation which is for example used in the Single and Multiple Relation Widget to Identify an Model Item

The MagicModel will try the following things to determine a title:

1. Does the Model Provide a __toString() Method  
2. Does one or more @title Tags exist  
3. Are there Properties Tagged as @identity which can be converted to String 
4. Is there a Property with the name "title" or "name"

**Class Reflection**::

	/**
	 * @var string 
	 * @Admin\Annotations\Title
	 */
	protected $title;

**YAML**::

	TYPO3\Blog\Domain\Model\Post: 
		Properties:
			Title: 
				Title: true

First thing that matches will be used in the Order specified


Filter
******
By Tagging a Property with this Tag the Admin Interface will try to provide a Selectbox to Filter the List View by the Possible Values of this Property

**Class Reflection**::

	/**
	 * @var string 
	 * @Admin\Annotations\Filter
	 */
	protected $author;

**YAML**::

	TYPO3\Blog\Domain\Model\Blog: 
		Properties:
			Author: 
				Filter: true

OptionsProvider
***************

**Class Reflection**::

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection<\Admin\Security\Policy>
	 * @Admin\Annotations\OptionsProvider("\Admin\OptionsProvider\PolicyOptionsProvider")
	 */
	protected $grant;

**YAML**::

	TYPO3\Blog\Domain\Model\Blog: 
		Properties:
			Grant: 
				OptionsProvider: \Admin\OptionsProvider\PolicyOptionsProvider 