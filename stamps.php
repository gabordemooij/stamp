<?php
/**
 * Stamp Template Engine
 * Compact Pure HTML Template Engine
 *
 * @file			Stamp
 * @description		Stamp Template Engine, Main Class
 *
 * @author			Gabor de Mooij
 * @license			BSD
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 *
 */
class Stamp {

	/**
	 * @var string
	 * Holds base template string
	 */
	private $tpl = "";

	/**
	 * @var array
	 */
	private $fcache = array();

	/**
	 * Constructor
	 *
	 * @param  string $templ template string
	 *
	 * @return void
	 */
	public function __construct($templ) {
		$this->tpl=$templ;

	}

	/**
	 * Finds a region in the template marked by the comment markers
	 * <!-- marker --> this area will be selected <!-- /marker -->
	 *
	 * This method returns a Region Struct:
	 *
	 * array(
	 * 	"begin" => $begin -- begin of region (string position),
	 *  "padBegin" => $padBegin -- begin of actual inner HTML (without the begin marker itself)
	 * 	"end" => $end -- end of region before end marker
	 *  "padEnd" => -- end of region after end marker
	 * 	"copy" => -- inner HTML between markers
	 *
	 * );
	 *
	 * @param  string $id marker ID
	 *
	 * @return array $struct
	 */
	public function find( $id ) {
		if (isset($this->fcache[$id])) {
			return $this->fcache[$id];
		}
		$fid = "<!-- ".$id." -->";
		$fidEnd = "<!-- /".$id." -->";
		$len = strlen($fid);
		$begin = strpos($this->tpl,$fid);
		$padBegin = $begin + $len;
		$rest = substr($this->tpl,$padBegin);
		$end = strpos($rest,$fidEnd);
		$padEnd = $end + strlen($fidEnd);
		$stamp = substr($rest,0,$end);
		$keys = array( "begin"=>$begin, "padBegin"=>$padBegin, "end"=>$end, "padEnd"=>$padEnd, "copy"=>$stamp );
		$this->fcache[$id] = $keys;
		return $keys;
	}

	/**
	 * Returns a new Stamp Template, containing a copy of the
	 * specified region ID.
	 *
	 * @param  string $id region id
	 *
	 * @return Stamp $stamp the new Stamp instance
	 */
	public function copy($id) {
		$snip = $this->find($id);
		return new Stamp( $snip["copy"] );
	}

	/**
	 * Cleans the contents of the current stamp
	 *
	 * @return Stamp
	 */
	public function clean() {
		return $this->paste("");
	}

	/**
	 * Replace a region specified by $where region ID with string $paste.
	 *
	 * @param  string $where region ID
	 * @param  string $paste replacement string
	 *
	 * @return Stamp stamp
	 */
	public function replace( $where, $paste ) {
		$keys = $this->find($where);
		$suffix = substr($this->tpl,$keys["padEnd"]+$keys["padBegin"]);
		$prefix = substr($this->tpl,0,$keys["begin"]);
		$copy = $prefix.$paste.$suffix;
		$this->tpl = $copy;
		return $this; //new Stamp( $copy );
	}


	/**
	 * Puts $text in slot Slot ID, marker #slot# will be replaced.
	 *
	 * @param  string $slotID slot identifier
	 * @param  string $text
	 *
	 * @return Stamp
	 */
	public function put($slotID, $text) {
		$text = htmlentities($text);
		$slotFormat = "#$slotID#";
		$this->tpl = str_replace( $slotFormat, htmlspecialchars( $text ), $this->tpl);
		return $this;
	}

	/**
	 * Checks if the template contains slot Slot ID.
	 *
	 * @param  string $slotID slot ID
	 *
	 * @return bool $containsSlot result of check
	 */
	public function hasSimpleSlot($slotID) {
		if (strpos($this->tpl,"#".$slotID."#")!==false) return true; else return false;
	}


	/**
	 * Pastes the contents of string $paste after the first '>'
	 *
	 * @param  $paste string HTML
	 *
	 * @return Stamp $chainable Chainable
	 */
	public function pastePad($paste) {
		$beginOfTag = strpos($this->tpl,">");
		$endOfTag = strrpos($this->tpl,"<");
		$this->tpl = substr($this->tpl, 0, $beginOfTag+1).$paste.substr($this->tpl,$endOfTag);
		return $this;
	}

	/**
	 * Pastes the contents of $paste in the template; replaces entire template.
	 *
	 * @param  string $paste string HTML
	 *
	 * @return Stamp $chainable Chainable
	 */
	public function paste( $paste ) {
		$this->tpl = $paste;
		return $this;
	}

	/**
	 * Renders the contents of the template as a string
	 *
	 * @return string $stringHTML HTML
	 */
	public function __toString() {
		return (string) $this->tpl;
	}

	/**
	 * Returns the list of markers in template
	 *
	 * @return array $markersList array
	 */
    private function getMarkersList() {
        preg_match_all('#<!-- ([a-z0-9]+) -->#', $this->tpl,
                        $markersGroups);
        $markersList = array();
        foreach($markersGroups[1] as $marker) {
            if(strstr($this->tpl, "<!-- /$marker -->")) {
                $markersList []= $marker;
            }
        }
        return $markersList;
    }

	/**
	 * Replaces all markers in current template with child's ones
	 * (like reverse (to make it chainable) of Django's {% extends %})
	 *
	 * @return Stamp $chainable Chainable
	 */
    public function extendWith($childString) {
        $child = new Stamp($childString);
        $parent = $this;
        foreach($child->getMarkersList() as $marker) {
            $copyInChild = $child->copy($marker);
            $parent->replace($marker, $copyInChild);
        }
        return $this;
    }

}
