<?php

/**
 * Stamp Painter for Selectboxes
 * Stamp Template Engine Painter Class
 * Paints a selectbox
 */
class Stamp_Painters_Select {
	
	/**
	 * Holds a reference to the Stamp object
	 * @var Stamp
	 */
	protected $stamp;
	
	/**
	 * Holds the options that should be stored in the selectbox HTML
	 * @var array
	 */
	protected $options;
	
	/**
	 * Name of the HTML form element
	 * @var string
	 */
	protected $name;
	
	/**
	 * Selected value
	 * @var string
	 */
	protected $selected;
	
	/**
	 * 
	 * Constructor. Creates an instance of this Painter.
	 * 
	 * Default Template:
	 * 
	 * <select name="#name#">
	 * 		<!-- cut:option -->
	 * 		<option #selected# value="#value#">#label#</option>
	 * 		<!-- /cut:option -->
	 * 		<!-- paste:options(option) -->
	 * </select>
	 *				
	 * Cut points:
	 * 
	 * option		An option for the selectbox
	 * 
	 * 
	 * Glue Points:
	 * 
	 * options		A place to attach new options
	 * 
	 * 
	 * Slots:
	 * 
	 * in option snippet:
	 * 
	 * selected		A place to put the selected attribute
	 * value		A place to put the value of an option
	 * label		A place to put the label of an option				
	 * 
	 * 
	 * @param Stamp $stamp Stamp instance that contains template (optional)
	 */
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
	
	/**
	 * Sets the name of the HTML form element
	 * 
	 * @param string $name form element name
	 * 
	 * @return Stamp_Painters_Select $self Chainable
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}
	
	/**
	 * Adds an option to the selectbox.
	 * 
	 * @param string $optionName  Name of the option / label
	 * @param string $optionValue Value of the option (optional, otherwise same as name)
	 * 
	 * @return Stamp_Painters_Select $self Chainable
	 */
	public function addOption($optionName, $optionValue=null) {
		if (is_null($optionValue)) {
			$optionValue = $optionName;
		}
		$this->options[] = array($optionName, $optionValue); 
		return $this;
	}
	
	/**
	 * Sets the selected value for the HTML form element.
	 * 
	 * @param string $value value
	 * 
	 * @return Stamp_Painters_Select $self Chainable
	 */
	public function setSelectedValue($value) {
		$this->selected = $value; 
		return $this;
	}
	
	/**
	 * Returns the modified Stamp that contains the generated HTML for this
	 * element.
	 * 
	 * @return Stamp $stamp resulting stamp object.
	 */
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
	
	/**
	 * One-command interface for rendering a selectbox.
	 * Renders a selectbox with a name and options and selects the indicated option by value.
	 * This function expects $options to be an associative array. The keys are used for labels
	 * and the values are used for option values.
	 * 
	 * @param string $name    name of the HTML form element
	 * @param array  $options list of options
	 * @param mixed  $select  value in option list that needs to be marked as selected
	 * 
	 * @return Stamp $stamp resulting stamp instance
	 */
	public static function paintListKeyAsLabel($name,$options,$selected=null) {
		$list = new self;
		$list->setName($name);
		if (!is_null($selected)) $list->setSelectedValue($selected);
		foreach($options as $label=>$value) $list->addOption($label,$value);
		return $list->paint();
	}
	
	
	
	/**
	 * One-command interface for rendering a selectbox.
	 * Renders a selectbox with a name and options and selects the indicated option by value.
	 * This function expects $options to be an associative array. The values are used for labels
	 * and the keys are used for option values.
	 * 
	 * @param string $name    name of the HTML form element
	 * @param array  $options list of options
	 * @param mixed  $select  value in option list that needs to be marked as selected
	 * 
	 * @return Stamp $stamp resulting stamp instance
	 */
	public static function paintListValueAsLabel($name,$options,$selected=null) {
		$list = new self;
		$list->setName($name);
		if (!is_null($selected)) $list->setSelectedValue($selected);
		foreach($options as $label=>$value) $list->addOption($value,$label);
		return $list->paint();
	}
	
	/**
	 * One-command interface for rendering a selectbox.
	 * Renders a selectbox with a name and options and selects the indicated option by value.
	 * This function expects $options to be an associative array. The values are used for BOTH
	 * labels and values in the selectbox.
	 * 
	 * @param string $name    name of the HTML form element
	 * @param array  $options list of options
	 * @param mixed  $select  value in option list that needs to be marked as selected
	 * 
	 * @return Stamp $stamp resulting stamp instance
	 */
	public static function paintListSimple($name,$options,$selected=null) {
		$list = new self;
		$list->setName($name);
		if (!is_null($selected)) $list->setSelectedValue($selected);
		foreach($options as $o) $list->addOption($o);
		return $list->paint();
	}
	
	
	
}