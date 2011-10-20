<?php

class Stamp_Painters_Select {
	
	protected $stamp;
	protected $options;
	protected $name;
	protected $selected;
	
	
	public function __construct( Stamp $stamp = null ) {
	
		if (!$stamp) {
			
			$stamp = '
				<select name="#name#">
					<!-- cut:option -->
					<option #selected# value="#value#">#label#</option>
					<!-- /cut:option -->
					<!-- paste:options(option) -->
				</select>
			';
			$stamp = new Stamp($stamp);	
		}
		
		$this->stamp = $stamp;
	
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function addOption($optionName, $optionValue=null) {
		if (is_null($optionValue)) {
			$optionValue = $optionName;
		}
		$this->options[] = array($optionName, $optionValue); 
	}
	
	public function setSelectedValue($value) {
		$this->selected = $value; 
	}
	
	public function paint() {
		$this->stamp->put('name',$this->name);
		foreach($this->options as $option) {
			$optionStamp = $this->stamp->fetch('option');
			$optionStamp->put('label',$option[0]);
			$optionStamp->put('value',$option[1]);
			if ($this->selected == $option[1]) {
				$optionStamp->put('selected','selected');
			}
			else {
				$optionStamp->put('selected','');
			}
			$this->stamp->pasteIn('options',$optionStamp);
		}
		return $this->stamp;
	}
	
	public static function paintListKeyAsLabel($name,$options,$selected=null) {
		$list = new self;
		$list->setName($name);
		if (!is_null($selected)) $list->setSelectedValue($selected);
		foreach($options as $label=>$value) $list->addOption($label,$value);
		return $list->paint();
	}
	
	public static function paintListValueAsLabel($name,$options,$selected=null) {
		$list = new self;
		$list->setName($name);
		if (!is_null($selected)) $list->setSelectedValue($selected);
		foreach($options as $label=>$value) $list->addOption($value,$label);
		return $list->paint();
	}
	
	public static function paintListSimple($name,$options,$selected=null) {
		$list = new self;
		$list->setName($name);
		if (!is_null($selected)) $list->setSelectedValue($selected);
		foreach($options as $o) $list->addOption($o);
		return $list->paint();
	}
	
	
	
}