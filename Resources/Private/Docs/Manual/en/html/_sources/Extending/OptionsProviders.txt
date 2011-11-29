OptionsProviders
================

An Options Provider creates the List of Options for the SingleRelation and MultipleRelation Widgets. 
Currently there is the Default implementation which creates the Options Using the ID and String Representation of the Object and one that load Options from an simple source.
But for Example the PolicyOptionsProvider ensures that there are all needed Options as Policy available when the Roles Object is loaded

RelationOptionsProvider
***********************

This Optionsprovider gives available options based on the entity's relation

ArrayOptionsProvider
********************

This Optionsprovider gives available options based on the entity's relation

**Reflection**::

    /**
     * @var string
     * @Admin\Widget("Dropdown")
     * @Admin\OptionsProvider(name="Array", property="options")
     */
    protected $optionsProvider;
    public $options = array(
    	"Hello" => "World",
    	"Hell" => "Yea"
    );


**YAML**::

    TYPO3\Party\Domain\Model\ElectronicAddress:
      Properties:
        type:
          OptionsProvider:
            name: Array
            options:
              aim: Aim
              email: Email
              gizmo: Gizmo
              icq: Icq
              jabber: Jabber
              msn: Msn
              sip: Sip
              skype: Skype
              url: Url
              yahoo: Yahoo

PolicyOptionsProvider
*********************

Similar to the RelationOptionsProvider with the difference, that it populates the Policy table with policies based on available entities and actions
