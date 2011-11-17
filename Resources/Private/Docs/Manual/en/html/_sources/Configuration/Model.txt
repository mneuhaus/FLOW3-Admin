Model Configuration
###################

Active
*********
Enable the Admin Interface for a Model

**Class Reflection**::

    use Admin\Annotations as Admin;
    /** A Blog post
      * ...
      * @Admin\Active 
      */
    class Post {...
        
**YAML**::

    TYPO3\Blog\Domain\Model\Post:
        Active: true

Group
*****
Specifiy a Group in which the Model will be Listed in the Menu. By Default the Models will be Sorted in Categories based on the Package name.

**Class Reflection**::

    use Admin\Annotations as Admin;
    /** A Blog post
      * ...
      * @Admin\Active
      * @Admin\Group("MyBlog")
      */
    class Post {...
        
**YAML**::

    TYPO3\Blog\Domain\Model\Post:
        Active: true
        Group: MyBlog

Set
***
By Default all [Properties](property) will be in a General Fieldset called General in the Order in which they are listed in the Models class. You can override this by specifiying specific Sets of fields.

**Class Reflection**::

    use Admin\Annotations as Admin;
    /** A Blog post
      * ...
      * @Admin\Active
      * @Admin\Annotations\Set(title="Main", properties="title,content")
      * @Admin\Annotations\Set(title="Extended Informations", properties="linkTitle,date,author,image")
      */
    class Post {...
        
**YAML**::

    TYPO3\Blog\Domain\Model\Post:
        Active: true
        Set: 
            - 
                Title: Main
                Properties: title, content
            -
                Title: Extended Informations
                Properties: linkTitle, date, author, image
