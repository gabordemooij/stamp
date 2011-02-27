<?php

//Stamp
//A template Engine by Gabor de Mooij (G.J.G.T de Mooij)
//Licensed New BSD

class Stamp {
	private $tpl = "";
	private $fcache = array();
	public function __construct($templ) {
		$this->tpl=$templ;

	}

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

	public function copy($id) {
		$snip = $this->find($id);
		return new Stamp( $snip["copy"] );
	}

	public function clean() {
		return $this->paste("");
	}

	public function replace( $where, $paste ) {
		$keys = $this->find($where);
		$suffix = substr($this->tpl,$keys["padEnd"]+$keys["padBegin"]);
		$prefix = substr($this->tpl,0,$keys["begin"]);
		$copy = $prefix.$paste.$suffix;
		$this->tpl = $copy;
		return $this; //new Stamp( $copy );
	}

	public function put($slotID, $text) {
		$text = htmlentities($text);
		$slotFormat = "#$slotID#";
		$this->tpl = str_replace( $slotFormat, $text, $this->tpl);
		return $this;
	}

	public function hasSimpleSlot($slotID) {
		if (strpos($this->tpl,"#".$slotID."#")!==false) return true; else return false;
	}



	public function pastePad($paste) {
		$beginOfTag = strpos($this->tpl,">");
		$endOfTag = strrpos($this->tpl,"<");
		$this->tpl = substr($this->tpl, 0, $beginOfTag+1).$paste.substr($this->tpl,$endOfTag);
		return $this;
	}


	public function paste( $paste ) {
		$this->tpl = $paste;
		return $this;
	}

	public function __toString() {
		return (string) $this->tpl;
	}

}


/** EXAMPLES
$current = "news";
$tabs = array("home.html"=>"homepage","news.html"=>"news","about.html"=>"about");
?>
  <ul class="tabs">
  	<?php foreach($tabs as $lnk=>$t): ?>
  		<li>
  			<a class="
  				<?php if ($current==$t): ?>
  					active
  				<?php else: ?>
  					inactive
  				<?php endif; ?>
  			" href="<?php echo $lnk; ?>">
  				<?php echo $t; ?>
  			</a>
  		</li>
 	<?php endforeach; ?>
 </ul>
<?php

$template = '

	<ul class="tabs">
		<!-- tab -->
			<li>
				<a class="#active#" href="#href#">#tab#</a>
			</li>
		<!-- /tab -->
	</ul>
	

';

$tabs = array("home.html"=>"homepage","news.html"=>"news","about.html"=>"about");
$s = new Stamp($template);
$current = "news";

foreach($tabs as $lnk=>$t)
	$menu .= $s->copy("tab")->put("href",$lnk)->put("tab",$t)->put("active",($current==$t)?"active":"inactive");

echo $s->replace("tab",$menu);
**/