<?php

namespace StampTE;

class UIElement {
	
	protected $stampTemplate;
	protected $select = null;
	
	public function __construct($template) {
		if (!($template instanceof Stamp)) {
			$template = new Stamp($template);
		}
		$this->stampTemplate = $template;
	}
	
	public function &__get($gluePoint) {
		$this->select = $gluePoint;
		return $this;
	}
	
	
	public function __call($method,$arguments) {
		if (strpos($method,'get')===0) {
			return new self( $this->stampTemplate->get(lcfirst(substr($method,3))) );
		}
		if (strpos($method,'set')===0) {
			$this->stampTemplate->inject(strtolower(substr($method,3)),$arguments[0]);
			return $this;
		}
	}
	
	public function add(UIElement $stamp) {
		if ($this->select === null) {
			$this->select = 'self'.$stamp->stampTemplate->getID();
		}
		$this->stampTemplate->glue($this->select,$stamp->stampTemplate);
		$this->select = null; //reset
		
	}
	
	public function __toString() {
		return strval( $this->stampTemplate );
	}
	
	public function copy() {
		return new self( $this->stampTemplate->copy() );
	}
	
	public function collect($collect) {
		$items = array();
		$stamps = $this->stampTemplate->collect($collect);
		foreach($stamps as $stamp) {
			$items[] = new self($stamp);
		}
		return $items;
	}
	
	public function getTemplate() {
		return $this->stampTemplate;
	}
	
}