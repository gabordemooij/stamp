<?php

require('StampEngine.php');

$se = new StampEngine(file_get_contents('forms.html'));

class FormBuilder {

	private $se;
	private $currentFieldset;
	
	public function __construct(StampEngine $se, $action, $method='post') {
		$this->se = $se->get('form');
		$this->se->inject('action',$action);
		$this->se->inject('method',$method);
	}
	
	public function openFieldset($legend) {
		$this->currentFieldset = $this->se->get('fieldset')->copy();
		$this->currentFieldset->inject('legend',$legend);
	}
	
	public function addTextField($label,$name,$value) {
		$textField = $this->se->get('fieldset.textfield')->copy();
		$textField->injectAll(array(
			'label'=>$label,
			'fieldname'=>$name,
			'value'=>$value
		));
		$this->currentFieldset->glue('formFields',$textField);
	}
	
	public function closeFieldset() {
		$this->se->glue('formElements', $this->currentFieldset);
	}
	
	public function __toString() {
		return (string) $this->se;
	}
	
}


$a = new FormBuilder($se,'action','post');
$a->openFieldset('Account Information');
$a->addTextField('Name:','username', 'anonymous');
$a->addTextField('Password:','password', 'mypassword');
$a->closeFieldset();
echo $a;

