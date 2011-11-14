# ViewHelpers

## ApiViewHelper
This ViewHelper provides access to the \Admin\Core\API:: functions.

get
:	specifies the variable or function to trigger on the API

as
:	specifies the variable which will contain the result

## DashboardWidgetsViewHelper
This ViewHelper renders the currently active Widgets

## LayoutViewHelper
This ViewHelper extends the regular LayoutViewHelper with the ability to specifiy an package to search for the layout

name
:	name of the layout

package
:	name of the package to look for the layout

## NavigationViewHelper
Helps by rendering previously registered Navigation Items

position
:	specifies the region of this navigation (top, left, ...)

as
:	specifies the variable which will contain the navigation items

## RenderViewHelper
This ViewHelper extends the regular RenderViewHelper with these features:

optional
:	you can set the optional parameter to true in conjunction with the section attribute. In contrast to the regular RenderViewHelper this one renders it's childs if the section isn't overidden instead of an empty string

fallbacks
:	with this function you can specify an fallback path from the settings to search for the partial in conjunction with the vars parameter

## SettingsViewHelper
This ViewHelper gives you access to global Settings from the view

path
:	specifies the path to the setting

## UserViewHelper
This ViewHelper gives you access to the current user

as
:	specifies the variable which will contain the user