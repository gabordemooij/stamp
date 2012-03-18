<?php
/**
 * 
 *           _                                                                
 *         /' `\     /'                                         /'            
 *       /'   ._)--/'--                                     --/'--            
 *      (____    /' ____     ,__________      ____          /'          ____  
 *           ) /' /'    )   /'    )     )   /'    )--     /'          /'    ) 
 *         /'/' /'    /'  /'    /'    /'  /'    /'      /'          /(___,/'  
 *(_____,/' (__(___,/(__/'    /'    /(__/(___,/'       (__        O(________ O
 *                                    /'                                      
 *                                  /'                                        
 *                                /'                                          
 *
 *  ---------------------------------------------------------------------------
 *  Stamp t.e.
 *  The Beautiful Template Engine
 *  @author Gabor de Mooij
 *  @copyright 2012
 *  @license New BSD License
 *  ---------------------------------------------------------------------------
 */
class StampTE {

	/**
	 * Holds the template
	 * @var string
	 */
	private $template; 
	
	/**
	 * Collection of initial matches from template
	 * @var array
	 */
	private $matches;
	
	/**
	 * Processed array of HTML parts found in template,
	 * keyed by IDs.
	 * @var array
	 */
	private $catelogue;
	
	/**
	 * Identifier of current template snippet.
	 * @var string 
	 */
	private $id;
	
	/**
	 * Cache array.
	 * @var array 
	 */
	private $cache = array();

	/**
	 * Constructor. Pass nothing if you plan to use cache.
	 * 
	 * @param string $tpl HTML Template
	 * @param string $id  identification string for this template 
	 */
	public function __construct($tpl='',$id='root') {
		$this->id = $id;
		$this->template = $tpl;
		$this->matches = array();
		$pattern = '/<!\-\-\scut:(\w+)\s\-\->(.*)?<!\-\-\s\/cut:\1\s\-\->/sU';
		preg_match_all($pattern, $this->template, $this->matches);
		$this->template = preg_replace($pattern,'',$this->template);
		$this->catelogue = array_flip($this->matches[1]);
		$this->sketchBook = $this->matches[2];
		
	}
	
	/**
	 * Checks whether a snippet with ID $id is in the catelogue.
	 * 
	 * @param string $id identifier you are looking for
	 * 
	 * @return boolean $yesNo whether the snippet with this ID is available or not. 
	 */
	public function inCatelogue($id) {
		return (boolean) (isset($this->catelogue[$id]));
	}
	
	/**
	 * Returns a new instance of StampEngine configured with the template
	 * that corresponds to the specified ID.
	 * 
	 * @param string $id identifier
	 * 
	 * @return StampEngine $snippet 
	 */
	public function get($id) {
		if (strpos($id,'.')!==false) {
			$parts = (explode('.',$id));
			$id = reset($parts);
			array_shift($parts);
			$rest = implode('.',$parts);
		}
		if ($this->inCatelogue($id)) {
			$snippet = $this->sketchBook[$this->catelogue[$id]];
			$new = new self($snippet,$id);
		}
		if (isset($parts)) { 
			return $new->get($rest);
		}
		else {
			return $new;
		}
	}
	
	/**
	 * Collects snippets from the template.
	 * $list needs to be a | pipe separated list of snippet IDs. The snippets
	 * will be returned as an array so you can obtain them using the list()
	 * statement.
	 * 
	 * @param string $list List of IDs you want to fetch from template
	 * 
	 * @return array $snippets Snippets obtained from template 
	 */
	public function collect($list) {
		if (isset($this->cache[$list])) return $this->cache[$list];
		$listItems = explode('|',$list);
		$collection = array();
		foreach($listItems as $item) {
			$collection[] = $this->get($item);
		}
		return $collection;
	}
	
	/**
	 * Returns a snippet/template as a string. Besides converting the instance
	 * to a string this function removes all HTML comments and unnecessary space.
	 * If you don't want this use a different toString method like ->getString()
	 *
	 * @return string $string string representation of HTML snippet/template 
	 */
	public function __toString() {
		$template = $this->template;
		$template = preg_replace("/<!--\s*[a-zA-Z0-9:\(\),\/]*\s*-->/m","",$template);
    	$template = preg_replace("/\n[\n\t\s]*\n/m","\n",$template);
    	$template = trim($template);
		return $template;
	}
	
	/**
	 * Returns the template as a string.
	 * 
	 * @return string $raw raw template 
	 */
	public function getString() {
		return $this->template;
	}
	
	/**
	 * Glues a snippet to a glue point in the current snippet/template.
	 * 
	 * @param string      $what    ID of the Glue Point you want to append the contents of the snippet to.
	 * @param StampEngine $snippet a StampEngine snippet/template to be glued at this point
	 * 
	 * @return StampEngine $snippet self, chainable
	 */
	public function glue($what,$snippet) {
		$matches=array();
		$pattern = '/<!\-\-\spaste:'.$what.'(\(([a-zA-Z0-9,]+)\))?\s\-\->/';
		preg_match($pattern,$this->template,$matches);
		$copyOrig = $matches[0];
		if (isset($matches[2])) {
			$allowedSnippets = $matches[2];
			$allowedMap = array_flip(explode(',',$allowedSnippets));
			if (!isset($allowedMap[$snippet->getID()])) {
				throw new Exception('Snippet '.$snippet->getID().' not allowed in slot '.$what);
			}
		}
		$this->template = preg_replace($pattern,$snippet.$copyOrig,$this->template);
		return $this;
	}

	/**
	 * Injects a piece of data into the slot marker in the snippet/template.
	 * 
	 * @param string  $where ID of the slot where to inject the data
	 * @param string  $data  the data to inject in the slot
	 * @param boolean $raw   if TRUE output will not be escaped
	 * 
	 * @return StampEngine $snippet self, chainable 
	 */
	public function inject($where, $data, $raw=false) {
		if (!$raw) $data = $this->filter($data);
		$where = "#$where#";
		$this->template = str_replace($where,$data,$this->template);
		return $this;
	}
	
	/**
	 * Same as inject() but injects an entire array of slot->data pairs.
	 * 
	 * @param array $array Array of slot->data pairs
	 * @param boolean $raw   if TRUE output will not be escaped
	 * 
	 * @return StampEngine self, chainable
	 */
	public function injectAll($array,$raw=false) {
		foreach($array as $key=>$value) {
			$this->inject($key, $value, $raw);
		}
		return $this;
	}
	
	/**
	 * Returns the identifier of the current snippet/template.
	 * 
	 * @return string $id ID of this snippet/template 
	 */
	public function getID() {
		return $this->id;
	}
	
	/**
	 * Copies the current snippet/template.
	 * 
	 * @return StampEngine $copy Copy of the current template/snippet 
	 */
	public function copy() {
		return clone($this);
	}
	
	/**
	 * Collects a list, just like collect() but stores result in cache array.
	 * 
	 * @param string $list Pipe separated list of IDs. 
	 */
	public function writeToCache($list) {
		$this->cache[$list] = $this->collect($list);
	}
	
	/**
	 * Returns the cache object for storage to disk.
	 * 
	 * @return string $cache serialized cache object. 
	 */
	public function getCache() {
		return serialize($this->cache);
	}
	
	/**
	 * Loads cache data.
	 * 
	 * @param string $rawCacheData the serialized cached string as retrieved from getCache(). 
	 */
	public function loadIntoCache($rawCacheData) {
		$this->cache = unserialize($rawCacheData);
	}	
	
	/**
	 * Filters data.
	 * 
	 * @param string $string
	 * 
	 * @return string $string 
	 */
	protected function filter($data) {
		return htmlspecialchars($data,ENT_COMPAT,'UTF-8');
	}
	
	
}
