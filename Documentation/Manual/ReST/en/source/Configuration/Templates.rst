Templates
#########
Since the Admin Interface would be not of much use if you could only use the Templates that the Admin Package ships with by default there is a Fallback System in place to automatically choose the most specific Template possible to Render the Admin Interface. The Fallbacks are Configured in the Settings.yaml and can be customized if needed.

Views
*****
The Admin Interface will check for the existence of each of this Fallbacks until a Template is found and then Render accordingly::

    resource://@package/Private/Templates/@being/@action/@variant.html 
    resource://@package/Private/Templates/Admin/@action/@variant.html 
    resource://@package/Private/Templates/@being/@action.html
    resource://@package/Private/Templates/Admin/@action.html
    resource://Admin/Private/Templates/Standard/@action/@variant.html 
    resource://Admin/Private/Templates/Standard/@action.html

@package
    Name of the Package which Contains the Model to be Rendered
    
@being
    Short name of the Model (TYPO3\Blog\Domain\Model\Post -> Post)
    
@action
    Action to Render (List, Create, Confirm, View, ...)
    
@variant
    Variant to Render (Tabular, Block)

Partials
********
Partials are Subparts which can be Reused in more than one View (Form, Table, Toolbar,...)::

	resource://@package/Private/Partials/@being/@action/@partial/@variant.html
	resource://@package/Private/Partials/@being/@action/@partial.html
	resource://@package/Private/Partials/@being/@partial/@variant.html
	resource://@package/Private/Partials/@being/@partial.html
	resource://@package/Private/Partials/@action/@partial/@variant.html
	resource://@package/Private/Partials/@action/@partial.html
	resource://@package/Private/Partials/@partial/@variant.html
	resource://@package/Private/Partials/@partial.html
	resource://Admin/Private/Partials/@action/@partial.html
	resource://Admin/Private/Partials/@action/@partial/@variant.html
	resource://Admin/Private/Partials/@partial/@variant.html
	resource://Admin/Private/Partials/@partial.html
	resource://Admin/Private/Partials/@partial/Default.html

@package
    Name of the Package which Contains the Model to be Rendered
    
@being
    Short name of the Model (TYPO3\Blog\Domain\Model\Post -> Post)
    
@partial
    Name of the Partial (Form, Table, Toolbar,...)

Widgets
*******
::

    resource://@package/Private/Partials/@being/Widgets/@partial.html
    resource://@package/Private/Partials/Widgets/@partial.html
    resource://Admin/Private/Partials/Widgets/@partial.html
    
@package
    Name of the Package which Contains the Model to be Rendered
    
@being
    Short name of the Model (TYPO3\Blog\Domain\Model\Post -> Post)
    
@partial
    Name of the Partial (TextField, Boolean, DateTime,...)

DashboardWidgets
****************
::

    resource://@package/Private/Partials/@being/DashboardWidgets/@partial.html
    resource://@package/Private/Partials/DashboardWidgets/@partial.html
    resource://Admin/Private/Partials/DashboardWidgets/@partial.html

@package
    Name of the Package which Contains the Model to be Rendered
    
@being
    Short name of the Model (TYPO3\Blog\Domain\Model\Post -> Post)
    
@partial
    Name of the Partial (TextField, Boolean, DateTime,...)