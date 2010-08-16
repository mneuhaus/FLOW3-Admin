<?php

namespace F3\Admin\Core;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @version $Id: ForViewHelper.php 3346 2009-10-22 17:26:10Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class Property{
	const INLINE_SINGLE_MODE = 1;
	const INLINE_MULTIPLE_MODE = 2;

	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $objectManager;

    protected $adapter;
    protected $name;
    protected $type = "string";
    protected $widget;
    protected $label;
    protected $infotext = "";
    protected $options;
    protected $conf;
    protected $parent;
    protected $being = null;
    protected $inline = false;
    protected $mode = 0;
    protected $children = array();
    protected $counter = 0;
    protected $value = null;
    protected $filter = false;
    protected $selected = false;

    public function  __construct($adapter) {
        $this->adapter = $adapter;
    }

    public function getName() {
        return $this->name;
    }

    public function getInputName(){
        if($this->mode == self::INLINE_MULTIPLE_MODE){
            return $this->parent->getPrefix()."[".$this->getName()."][]";
        }else{
            return $this->parent->getPrefix()."[".$this->getName()."]";
        }
    }

    public function getPrefix(){
        if($this->mode == self::INLINE_MULTIPLE_MODE){
            return $this->parent->getPrefix()."[".$this->getName()."][".$this->counter."]";
        }else{
            return $this->parent->getPrefix()."[".$this->getName()."]";
        }
    }

    public function setName($name) {
        if(empty($this->label)){
            $this->setLabel($this->adapter->getLabel($name));
        }
        $this->name = $name;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
        if(isset($this->conf["widget"])){
            $this->widget = $this->conf["widget"];
        }else{
            $this->widget = $this->adapter->getWidget($this->type, "Textfield");
        }
    }

    public function getWidget() {
        return $this->widget;
    }

    public function setWidget($widget) {
        if($widget == "MultipleRelation")
            $this->mode = \F3\Admin\Core\Property::INLINE_MULTIPLE_MODE;
        $this->widget = $widget;
    }

    public function getLabel() {
        return $this->label;
    }

    public function setLabel($label) {
        $this->label = $label;
    }

    public function getOptions() {
		$options = array();
        if(isset($this->conf["optionsProvider"])){
			if(is_array($this->conf["optionsProvider"]))
					$this->conf["optionsProvider"] = current($this->conf["optionsProvider"]);
			
            $provider = $this->objectManager->get(ltrim($this->conf["optionsProvider"],"\\"));
            $provider->setProperty($this);
            $options = $provider->getOptions();
        }
        return $options;
    }

    public function setOptions($options) {
        $this->options = $options;
    }

    public function getConf() {
        return $this->conf;
    }

    public function setConf($conf) {
        $this->conf = $conf;

        $properties = \get_object_vars($this);
        foreach($conf as $name => $conf){
            if(array_key_exists($name, $properties) && $conf != null){
                $setter = "set".ucfirst($name);
                if(is_callable(array($this,$setter))){
                    $this->$setter($conf);
                }else{
                    $this->$name = $conf;
                }
            }
        }
        
        $this->value = $this->objectManager->get("F3\Admin\Core\Value", $this,$this->adapter);
    }

    public function getChildren() {
        if($this->inline && empty($this->children)){
            $values = $this->getValue();
            $beings = array();
            if(\F3\Admin\Core\Helper::isIteratable($values)){
                foreach($values as $value){
                    if(is_object($value)){
                        $id = $this->adapter->getId($value);
                        $being = $this->createBeing($this->being, $id);
                        $beings[] = $being;
                    }
                    $this->counter++;
                }
            }elseif(!empty($values) && is_object($values)){
                $id = $this->adapter->getId($values);
                $being = $this->createBeing($this->being, $id);
                $beings[] = $being;
                $this->counter++;
            }else{
                if($this->mode == self::INLINE_MULTIPLE_MODE)
                    $amountOfInlines = 3;
                else
                    $amountOfInlines = 1;

                for ($index = 0; $index < $amountOfInlines; $index++) {
                    $being = $this->createBeing($this->being);
                    $beings[] = $being;
                    $this->counter++;
                }
            }
            $this->children = $beings;
        }
        return $this->children;
    }

    public function createBeing($being, $id = null){
        $b = $this->adapter->getBeing($this->being,$id);
        $b->setPrefix($this->getPrefix());
        
        if(!empty($id)){
            $identity = array( $this->getPrefix() . "[__identity]" => $id );
            $b->addHiddenProperty($identity);
        }

        return $b;
    }

    public function setChildren($children) {
        $this->children = $children;
    }

    public function setParent($parent){
        $this->parent = $parent;
    }

    protected function getParent(){
        return $this->parent;
    }

    public function setValue($value){
        $this->value = $value;
    }

    public function getValue(){
        return $this->parent->getValue($this->name);
    }

    public function getString(){
        return $this->value->__toString();
    }

    public function getIds(){
        return $this->value->getIds();
    }

    public function getBeing() {
        return $this->being;
    }

    public function setBeing($being) {
        $this->being = $being;
    }

    public function getInline() {
        return $this->inline;
    }

    public function setInline($inline) {
        $this->inline = $inline;
    }

    public function getInfotext() {
        return $this->infotext;
    }

    public function setInfotext($infotext) {
        $this->infotext = $infotext;
    }

    public function isFilter(){
        return $this->filter;
    }

    public function getSelected() {
        return $this->selected;
    }

    public function setSelected($selected) {
        $this->selected = $selected;
    }

    public function getAdapter() {
        return $this->adapter;
    }

    public function setAdapter($adapter) {
        $this->adapter = $adapter;
    }

    public function getMode() {
        return $this->mode;
    }

    public function setMode($mode) {
        $this->mode = $mode;
    }

    public function getFilter() {
        return $this->filter;
    }

    public function setFilter($filter) {
        $this->filter = $filter;
    }
	
    public function getError(){
        return $this->getParent()->getErrors($this->getName());
    }
}

?>