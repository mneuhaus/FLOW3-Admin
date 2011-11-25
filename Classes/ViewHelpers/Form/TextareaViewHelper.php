<?php
namespace Admin\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * Textarea view helper.
 * The value of the text area needs to be set via the "value" attribute, as with all other form ViewHelpers.
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:form.textarea name="myTextArea" value="This is shown inside the textarea" />
 * </code>
 * <output>
 * <textarea name="myTextArea">This is shown inside the textarea</textarea>
 * </output>
 *
 * <textarea name="{property.inputName}" id="item-{property.name}" rows="4" cols="40" class="{property.class}" data-editor="{property.editor}">{property.string}</textarea>
 * @api
 */
class TextareaViewHelper extends \TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper {
	
	/**
	 * @var \TYPO3\FLOW3\Resource\Publishing\ResourcePublisher
	 */
	protected $resourcePublisher;

	/**
	 * Inject the FLOW3 resource publisher.
	 *
	 * @param \TYPO3\FLOW3\Resource\Publishing\ResourcePublisher $resourcePublisher
	 * @return void
	 */
	public function injectResourcePublisher(\TYPO3\FLOW3\Resource\Publishing\ResourcePublisher $resourcePublisher) {
		$this->resourcePublisher = $resourcePublisher;
	}
	
	/**
	 * @var string
	 */
	protected $tagName = 'textarea';

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerTagAttribute('rows', 'int', 'The number of rows of a text area', TRUE);
		$this->registerTagAttribute('cols', 'int', 'The number of columns of a text area', TRUE);
		$this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
		$this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', FALSE, 'f3-form-error');
		$this->registerUniversalTagAttributes();
	}
	
	/**
	 * Renders the textarea.
	 *
	 * @param object $property
	 * @return string
	 * @api
	 */
	public function render() {
		$this->tag->forceClosingTag(TRUE);
		
		if ($this->hasArgument('property') && is_object($this->arguments['property'])) {
			$property = $this->arguments['property'];
			$this->tag->addAttribute('name', $property->getInputName());
			$this->tag->addAttribute('id', "item-" . $property->name);
			if(isset($property->class))
				$this->tag->addAttribute('class', $property->class);
			
			if(isset($property->editor)){
				$this->addEditor($property->editor, $property->editor->width);
				$this->tag->addAttribute("data-editor", strtolower($property->editor->name));
			}
			
			$this->tag->setContent(htmlspecialchars($this->getValue()));
		}

		$this->setErrorClassAttribute();

		return $this->tag->render();
	}
	
	public function addEditor($editor, $width = 500){
		$editor = strtolower($editor);
		switch ($editor) {
			case 'bbcode':
			case 'dotclear':
			case 'markdown':
			case 'textile':
			case 'texy':
			case 'wiki':
				$markitup = '<script src="' . $this->getResourceUri('admin/js/markitup/jquery.markitup.pack.js') . '"></script>
				<link rel="stylesheet" type="text/css" href="' . $this->getResourceUri('admin/js/markitup/skins/simple/style.css') . '" />';
				\Admin\Core\API::add("WidgetResources", "MarkItUp", $markitup);
				
				$settings = '
				<script src="' . $this->getResourceUri('admin/js/markitup/sets/'.$editor.'/set.js') . '"></script>
				<link rel="stylesheet" type="text/css" href="' . $this->getResourceUri('admin/js/markitup/sets/'.$editor.'/style.css') . '" />
				<script type="text/javascript" charset="utf-8"> jQuery(document).ready(function(){ jQuery("[data-editor='.$editor.']").markItUp('.$editor.'Settings); }); </script>
				<style type="text/css" media="screen">
					.markItUpEditor {width: '.$width.'px;}
				</style>
				';
				
				\Admin\Core\API::add("WidgetResources", $editor, $settings);
				break;
				
			case 'rte':
			case 'richtext':
				$html = '
					<script src="' . $this->getResourceUri('admin/js/ckeditor/ckeditor.js') . '"></script>
					<script src="' . $this->getResourceUri('admin/js/ckeditor/adapters/jquery.js') . '"></script>
					<script type="text/javascript" charset="utf-8">
						jQuery(document).ready(function(){
							var config = {
								toolbar:[
									["Bold","Italic","Underline","Strike","-","Subscript","Superscript"],
									["NumberedList", "BulletedList", "-", "Link", "Unlink"],
									["Source"],
								],
								width: ' . ($width + 15) . '
							};
							jQuery("[data-editor=richtext]").ckeditor(config);
						});
					</script>
				';
				\Admin\Core\API::add("WidgetResources", "richtext", $html);
			
			default:
				# code...
				break;
		}
	}
	
	public function getResourceUri($path, $package = "Admin"){
		return $this->resourcePublisher->getStaticResourcesWebBaseUri() . 'Packages/' . ($package === NULL ? $this->controllerContext->getRequest()->getControllerPackageKey() : $package ). '/' . $path;
	}
}

?>