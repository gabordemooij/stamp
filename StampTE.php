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
 *  @version 2.2
 *  @copyright 2013
 *  @license New BSD License
 *  ---------------------------------------------------------------------------
 */
class StampTE 
{
	/**
	 * HTML5 Document template cache.
	 * @var string
	 */
	private static $html5Tpl = null;

	/**
	 * Holds the template
	 * @var string
	 */
	protected $template;

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
	private $catalogue;

	/**
	 * Identifier of current template snippet.
	 * @var string 
	 */
	private $id;

	/**
	 * A Stamp contains a sketchbook with snippets to generate new
	 * stamps from. This way StampTE allows lazy initialization of 
	 * new Stamps as soon as they are fetched using the get() command.
	 * 
	 * @var array 
	 */
	private $sketchBook = array();

	/**
	 * List of slots in the current Stamp.
	 * Mainly for introspection by smart template processors.
	 * 
	 * @var array
	 */
	private $slots = array();

	/**
	 * Selector ID. The currently selected Glue Point.
	 * A Glue Point can be selected using a magic getter.
	 * The ID for this magically selected Glue Point is stored in this
	 * variable.
	 * 
	 * @var string 
	 */
	private $select = null;

	/**
	 * Cache array. Cache keeps the planet from burning up.
	 * 
	 * @var array 
	 */
	private $cache = array();

	/**
	 * Holds the translator function to be used for
	 * translations.
	 * 
	 * @var closure
	 */
	protected $translator = null;
	/**
	 * Holds the factory function to be used whenever
	 * a Stamp template is returned.
	 * 
	 * @var closure 
	 */
	protected $factory = null;

	/**
	 * Returns a StampTE template configured with a proper
	 * HTML 5 document using UTF-8 correct encoding (secure with 
	 * default XSS escaping features).
	 * 
	 * This default template contains two cut markers: link and script,
	 * two glue points: head and body and one slot: title.
	 * 
	 * Usage example:
	 * 
	 * $tpl = StampTE::createHtml5Utf8Document();
	 * $tpl->setTitle('Welcome to StampTE'); //set the title.
	 * $tpl->head->add( $linkTag ); //Add stylesheets and scripts!
	 * $tpl->body->add( $myDocument ); //Add your body content!
	 * 
	 * @return StampTE template
	 */
	public static function createHtml5Utf8Document()
	{
		if (is_null(self::$html5Tpl)) {
			$template = '';
			$template .= "<!DOCTYPE html>\n";
			$template .= "<html>\n";
			$template .= "<head>\n";
			$template .= "<meta charset=\"UTF-8\">\n";
			$template .= "<title>#title?#</title>\n";
			$template .= "<!-- cut:link --><link data-stampte=\"#linkAttributes?#\" rel=\"#rel?#\" type=\"#type?#\" href=\"#href?#\" ><!-- /cut:link -->\n";
			$template .= "<!-- cut:script --><script data-stampte=\"#scriptAttributes?#\" src=\"#src?#\" ></script><!-- /cut:script -->\n";
			$template .= "<!-- paste:head -->";
			$template .= "</head>\n";
			$template .= "<body data-stampte=\"#bodyAttributes?#\">";
			$template .= "<!-- paste:body -->";
			$template .= "</body>\n";
			$template .= "</html>";
			self::$html5Tpl = $template;
		}
		return new self( self::$html5Tpl );
	}

	/**
	 * Constructor. Pass nothing if you plan to use cache.
	 * 
	 * @param string $tpl HTML Template
	 * @param string $id  identification string for this template 
	 */
	public function __construct( $tpl='', $id='root' )
	{
		if ( is_null( $tpl ) ) $tpl = '';
		
		if ( is_array( $tpl ) ) $tpl = implode( ',', $tpl );
		
		if ( is_object( $tpl ) && !method_exists( $tpl, '__toString' ) ) $tpl = '['.get_class( $tpl ).' instance]';
		
		$tpl = (string) $tpl;
		
		$this->id       = strval( $id );
		$this->template = $tpl;
		$this->matches  = array();
		$pattern        = '/\s*<!\-\-\s(cut|slot):(\w+)\s\-\->(.*)?<!\-\-\s\/(cut|slot):\2\s\-\->/sU';
		$me             = $this;

		$this->template = preg_replace_callback( $pattern, function( $matches ) use ( $me ) {
			list(, $type, $id, $snippet) = $matches;
			if ( $type === 'cut' ) {
				$me->addToSketchBook( $id, $snippet );
				return '<!-- paste:self'.$id.' -->';
			} else {
				$me->addToSlots( $id );
				return "#$id#";
			}
		}, $this->template );
		$this->template = preg_replace( '/#(\w+)(\?)?#/sU', '#&$1$2#', $this->template );		
	}
	
	/**
	 * A stupid method that just returns all the glue points in the template.
	 * Only use this method if you're planning to do some wise-ass shit.
	 * It's ugly, and horribly slow. 
	 * 
	 * @return array 
	 */
	public function getGluePoints()
	{
		$gluePoints = array();
		if ( preg_match_all( '/<!\-\-\spaste:(\w+)\s\-\->/', $this->template, $gluePoints ) ) {
			$gluePoints = ( $gluePoints[1] );
		}
		return $gluePoints;
	}

	/**
	 * Returns the slots in this Stamp.
	 * Normally you don't need them but there are some smartypants template processors out there
	 * that do.
	 * 
	 * @return array 
	 */
	public function getSlots()
	{
		return $this->slots;
	}

	/**
	 * Adds a slot to the map of slots.
	 * @param type $id 
	 */
	public function addToSlots( $id ) 
	{
		$this->slots[$id] = TRUE;
	}
	
	/**
	 * Internal method that needs to be public because PHP is too stupid to understand
	 * $this in closures.
	 * 
	 * @param string $id
	 * @param string $snippet 
	 */
	public function addToSketchBook( $id, $snippet )
	{
			$this->catalogue[$id] = count( $this->sketchBook );
			$this->sketchBook[]   = $snippet;
	}

	/**
	 * Creates an instance of StampTE template using a file.
	 * 
	 * @param string $filename file containing HTML input
	 * 
	 * @return static 
	 */
	public static function load( $filename )
	{
		if ( !file_exists( $filename ) ) throw new StampTEException( '[S001] Could not find file: '.$filename );
		$template = file_get_contents( $filename );
		return new static( $template );
	}

	/**
	 * Checks whether a snippet with ID $id is in the catalogue.
	 * 
	 * @param string $id identifier you are looking for
	 * 
	 * @return boolean $yesNo whether the snippet with this ID is available or not. 
	 */
	public function inCatalogue( $id )
	{
		return ( boolean ) ( isset( $this->catalogue[$id] ) );
	}

	/**
	 * Returns a new instance of StampTE configured with the template
	 * that corresponds to the specified ID.
	 * 
	 * @param string $id identifier
	 * 
	 * @return StampTE $snippet 
	 */
	public function get( $id )
	{
		if ( strpos( $id, '.') !== FALSE) {
			$parts = ( explode( '.', $id) );
			$id    = reset( $parts );
			array_shift( $parts );
			$rest  = implode( '.', $parts );
		}

		if ( $this->inCatalogue( $id ) ) {
			$snippet = $this->sketchBook[$this->catalogue[$id]];
			if ( $this->factory ) {
				$new = call_user_func_array( $this->factory, array( $snippet, $id ) );
			} else {
				$new = new static( $snippet, $id );
			}
			//Pass the translator and the factory.
			$new->translator = $this->translator;
			$new->factory    = $this->factory;

			if ( isset( $parts ) ) { 
				return $new->get( $rest );
			} else {
				return $new;
			}
		} else {
			throw new StampTEException( '[S101] Cannot find Stamp Snippet with ID '.preg_replace( '/\W/', '', $id ) );
		}
	}

	/**
	 * Collects snippets from the template.
	 * $list needs to be a | pipe separated list of snippet IDs. The snippets
	 * will be returned as an array so you can obtain them using the list()
	 * statement.
	 * 
	 * @param string  $list  List of IDs you want to fetch from template
	 * 
	 * @return array $snippets Snippets obtained from template 
	 */
	public function collect( $list )
	{
		if ( isset( $this->cache[$list] ) ) return $this->cache[$list];
		$listItems = explode( '|', $list );

		$collection = array();
		foreach( $listItems as $item ) {
			$collection[] = $this->get( $item );
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
	public function __toString()
	{
		$template = $this->template;
		$template = preg_replace( "/\s*<!--\s*(paste):[a-zA-Z0-9\(\),\/]*\s*-->/m", "", $template );

		if ( strpos($template, '#&' ) !== FALSE) {
			$template = preg_replace( "/data\-stampte=\"#\&\w+\?#\"/m", "", $template );
			$template = preg_replace( "/#\&\w+\?#/m", "", $template );
		}

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
	 * The glue() method also accepts raw strings.
	 * 
	 * @param string  $what    ID of the Glue Point you want to append the contents of the snippet to.
	 * @param StampTE|string   $snippet a StampTE snippet/template to be glued at this point 
	 * 
	 * @return StampTE $snippet self, chainable
	 */
	public function glue( $what, $snippet )
	{
		$matches = array();
		$pattern = '/\s*<!\-\-\spaste:'.$what.'(\(([a-zA-Z0-9,]+)\))?\s\-\->/';

		$this->template = preg_replace_callback( $pattern, function( $matches ) use ( $snippet, $what ) {
			$copyOrig = $matches[0];
	
			if ( isset($matches[2]) ) {
				
				if ( !is_object( $snippet ) ) throw new StampTEException('[S003] Snippet is not an object or string.');

				$allowedSnippets = $matches[2];
				$allowedMap      = array_flip( explode( ',', $allowedSnippets ) );
				if ( !isset( $allowedMap[$snippet->getID()] ) ) throw new StampTEException( '[S102] Snippet '.$snippet->getID().' not allowed in slot '.$what );
			}

			return $snippet.$copyOrig;

		}, $this->template );

		return $this;
	}

	/**
	 * Glues all elements in the specified array.
	 * This is a quick way to glue multiple elements as well.
	 * 
	 * @param array $map list of key=>value pairs to glue
	 * 
	 * @return StampTE $snippet self, chainable  
	 */
	public function glueAll( $map )
	{
		foreach( $map as $slot => $value ) {
			if ( is_array( $value ) ) {
				foreach( $value as $item ) {
					$this->glue( $slot, $item );
				}
			} else {
				$this->glue( $slot, $value );
			}
		}
		return $this;
	}

	/**
	 * Injects a piece of data into the slot marker in the snippet/template.
	 * 
	 * @param string  $where ID of the slot where to inject the data
	 * @param string  $data  the data to inject in the slot
	 * @param boolean $raw   if TRUE output will not be escaped
	 * 
	 * @return StampTE $snippet self, chainable 
	 */
	public function inject( $slot, $data, $raw = FALSE )
	{
		if ( !$raw ) $data = $this->filter( $data );

		$where    = "#&$slot#";
		$whereOpt = "#&$slot?#";

		$this->template = str_replace( $where, $data, $this->template );
		$this->template = str_replace( $whereOpt, $data, $this->template );

		return $this;
	}

	/**
	 * Injects a piece of data into an attribute slot marker in the snippet/template.
	 * 
	 * @param string  $slot name of the slot where the data should be injected
	 * @param string  $data the data to be injected in the slot
	 * @param boolean $raw  if TRUE output will not be escaped
	 * 
	 * @return StampTE 
	 */
	public function injectAttr( $slot, $data, $raw = FALSE )
	{
		if ( !$raw ) $data = $this->filter( $data );

		$where    = "data-stampte=\"#&$slot#\"";
		$whereOpt = "data-stampte=\"#&$slot?#\"";

		$this->template = str_replace( $where, $data, $this->template );
		$this->template = str_replace( $whereOpt, $data, $this->template );

		return $this;
	}

	/**
	 * Alias for inject($where,$data,TRUE)
	 * 
	 * @param string  $where ID of the slot where to inject the data
	 * @param string  $data  the data to inject in the slot
	 *
	 * @return StampTE $snippet self, chainable 
	 */
	public function injectRaw( $where, $data )
	{
		return $this->inject( $where, $data, TRUE );
	}

	/**
	 * Same as inject() but injects an entire array of slot->data pairs.
	 * 
	 * @param array $array Array of slot->data pairs
	 * @param boolean $raw   if TRUE output will not be escaped
	 * 
	 * @return StampTE self, chainable
	 */
	public function injectAll( $array, $raw = FALSE )
	{
		foreach( $array as $key => $value ) {
			$this->inject( $key, $value, $raw );
		}
		return $this;
	}

	/**
	 * Returns the identifier of the current snippet/template.
	 * 
	 * @return string $id ID of this snippet/template 
	 */
	public function getID()
	{
		return $this->id;
	}

	/**
	 * Copies the current snippet/template.
	 * 
	 * @return StampTE $copy Copy of the current template/snippet 
	 */
	public function copy() 
	{
		return clone( $this );
	}

	/**
	 * Collects a list, just like collect() but stores result in cache array.
	 * 
	 * @param string $list Pipe separated list of IDs. 
	 * 
	 * @return self
	 */
	public function writeToCache( $list ) 
	{
		$this->cache[$list] = $this->collect( $list );
		return $this;
	}

	/**
	 * Returns the cache object for storage to disk.
	 * 
	 * @return string $cache serialized cache object. 
	 */
	public function getCache()
	{
		return serialize( $this->cache );
	}

	/**
	 * Loads cache data.
	 * 
	 * @param string $rawCacheData the serialized cached string as retrieved from getCache().
	 * 
	 * @return self 
	 */
	public function loadIntoCache( $rawCacheData )
	{
		$this->cache = unserialize( $rawCacheData );
		
		if ( !is_array( $this->cache ) ) throw new StampTEException( '[S004] Failed to unserialize cache object.' );
		
		return $this;
	}

	/**
	 * Filters data.
	 * 
	 * @param string $string
	 * 
	 * @return string $string 
	 */
	protected function filter( $data )
	{
		//First apply HTML special chars
		$filtered = htmlspecialchars( $data, ENT_QUOTES, 'UTF-8' );

		$filtered = str_replace( '`', '&#96;', $filtered ); //Prevent MSIE backtick XSS hack
		
		return $filtered;
	}

	/**
	 * Selects a Glue Point to attach a Stamp to.
	 * Note that although this seems like a getter this method
	 * actually returns the same StampTE. It's both evil and beautiful at the same time.
	 *  
	 * @param string $gluePoint
	 * 
	 * @return StampTE 
	 */
	public function &__get( $gluePoint )
	{
		$this->select = $gluePoint;
		return $this;
	}

	/**
	 * Call interceptor.
	 * Intercepts:
	 *				- getX(), routes to get('X')
	 *				- setX(Y), routes to inject('X',Y)
	 */
	public function __call($method, $arguments) 
	{
		if ( strpos( $method, 'get' ) === 0 ) {
			return $this->get( lcfirst( substr( $method, 3) ) );
		} elseif ( strpos( $method, 'set' ) === 0 ) {
			$this->inject( lcfirst( substr( $method, 3 ) ), $arguments[0] );
			return $this;
		} elseif ( strpos( $method, 'say' ) ===0 ) {
			$this->inject( lcfirst( substr( $method, 3) ), call_user_func_array( $this->translator, $arguments ) );
			return $this;
		}
	}

	/**
	 * Glues the specified Stamp object to the currently selected
	 * Glue Point.
	 * 
	 * @param StampTE $stamp 
	 */
	public function add( StampTE $stamp )
	{
		if ( $this->select === null ) {
			$this->select = 'self'.$stamp->getID();
		}
		$this->glue( $this->select, $stamp );
		$this->select = null; //reset
		return $this;
	}

	/**
	 * Sets the translator function to be used for translations.
	 * The translator function will be called automatically as soon as you invoke the magic 
	 * method:
	 * 
	 * sayX(Y) where X is the slot you want to inject the contents of Y into.
	 * 
	 * Note that optional arguments are allowed and passed to the translator.
	 * 
	 * @param closure $closure 
	 */
	public function setTranslator( $closure )
	{
		if ( !is_callable( $closure ) ) throw new StampTEException('[S005] Invalid Translator.');
		
		$this->translator = $closure;
	}

	/**
	 * Sets the object factory. If get(X) gets called StampTE will call the
	 * factory with template and ID for X to give you the opportunity to 
	 * wrap the template object in your own wrapper class.
	 * 
	 * @param closure $factory 
	 */
	public function setFactory( $factory )
	{
		if ( !is_callable( $factory ) ) throw new StampTEException('[S006] Invalid Factory.');
		
		$this->factory = $factory;
	}

	/**
	 * Attr is a shortcut to quickly set an attribute.
	 * 
	 * @param string  $slot  slot
	 * @param boolean $onOff whether to fill in the slot or not
	 * 
	 * @return StampTE
	 */
	public function attr( $slot, $onOff = TRUE ) 
	{
		return ( $onOff ) ? $this->injectAttr( $slot, $slot ) : $this;
	}
}
//Stamp Exception
class StampTEException extends Exception {}
