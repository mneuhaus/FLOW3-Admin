# Widgets

## Available Properties in a Widget Template
Inside the Widget Partial there is one essential Variable available called {property}.
This Variable Provides the following values:


{property.adapter}
:	Classname of the current Adapter

{property.widget}
:	Name of the Widget

{property.value}
:	Unprocessed value of the Property, this might be almost anything depending on the Data in the Object.
	Handle this with care, because it might cause Rendering errors. 
	In most cases you should simply use the {property.string} option to get an String representation of the Value.

{property.infotext}
:	￼Informational Text for the Property

{property.label}
:	Label for the Property

{property.string}
:	String representation for the property's value

{property.inputName}
:	Appropriate name for an input including the proppert prefix (item[propertyname])

{property.type}
:	DataType of the property

{property.name}
:	Name of the Property

## Replace the default Widget for a specific datatype
Widgets are assigned to datatypes by a fallback system configured in Settings.yaml

	Doctrine:
	  Widgets:
	    Mapping:
	      string:   Textfield
	      readonly:   TextfieldReadonly
	      integer:  Spinner
	      float:    Textfield
	      boolean:  Boolean
	      \TYPO3\FLOW3\Resource\Resource: Upload
	      \DateTime: DateTime
	      ^\\[A-Za-z]+\\Domain\\Model\\[A-Za-z]+$: SingleRelation
	      ^\\[A-Za-z]+\\Security\\[A-Za-z]+$: SingleRelation
	      ^\\Doctrine\\Common\\Collections\\Collection\<\\*\[A-Za-z]+\\Domain\\Model\\[A-za-z]+>$: MultipleRelation
	      ^\\Doctrine\\Common\\Collections\\Collection\<\\*\[A-Za-z]+\\Security\\[A-za-z]+>$: MultipleRelation

On The Left side you have your Classes or Names of the DataTypes and on the Right Side is the repsonsible Widget to use. You can override any of these Widgets in your Production/Development Settings.yaml. Aside from Classes or DataType Names you can specify an Regular Expression to Match more Complex things, like in this Case Entity Models.