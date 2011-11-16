OptionsProviders
================

An Options Provider creates the List of Options for the SingleRelation and MultipleRelation Widgets. Currently there is just the Default implementation which creates the Options Using the ID and String Representation of the Object. But for Example the PolicyOptionsProvider ensures that there are all needed Options as Policy available when the Roles Object is loaded

RelationOptionsProvider
***********************

This Optionsprovider gives available options based on the entity's relation


PolicyOptionsProvider
*********************

Similar to the RelationOptionsProvider with the difference, that it populates the Policy table with policies based on available entities and actions
