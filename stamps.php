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

	public function pasteInto( $paste, $where ) {
		if ($this->hasSimpleSlot($where)) { 
			$this->put($where,$paste);
			
			return $this;
		}
		else {
			$keys = $this->find($where);
			$suffix = substr($this->tpl,$keys["padEnd"]+$keys["padBegin"]);
			$prefix = substr($this->tpl,0,$keys["begin"]);
			$copy = $prefix.$paste.$suffix;
			$this->tpl = $copy;
		}
		return $this; //new Stamp( $copy );
	}

	public function put($slotID, $text) {
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

$template = '
	<!-- table -->
		<table border=1>
			<!-- row -->
			<tr>
				<!-- cell -->
				<td><b>#placeholder#</b></td>
				<!-- /cell -->
			</tr>
			<!-- /row -->
			<!-- row-odd --><tr style="background-color:#ddd;">#cell#</tr><!-- /row-odd -->
		</table>
	<!-- /table -->
';

echo $template;

$s = new Stamp( $template );

$matrix = array(
	array("John","Developer"),array("Joseph","Marketeer"),array("Burt","Gardener")
);


$table = $s->copy("table");
$j=0;
foreach($matrix as $person) {
	$cols = "";
	foreach($person as $info) {
		$cols .= (string) $s->copy("cell")->put("placeholder",$info);
	}
	$rows .= (string) $s->copy("row".(($j++ % 2) ? "":"-odd"))->pasteInto($cols,"cell");
}
echo $table->pastePad($rows);


/**

 <ul>
 <?php foreach($listItems as $listItem) { ?>
 	<li>
 	   <?php echo $listItem; ?>
    </li>
 <?php } ?>
 </ul>	

 */

$template = '


	<ul>
		<!-- item --><li>#listItem#</li><!-- /item -->
	</ul>

';

echo $template;


$todo = array("clean house", "do homework", "go to comic store");
$s = new Stamp($template);


foreach($todo as $task) {
	$listItems .= $s->copy("item")->pasteInto($task,"listItem");
}
echo $s->pastePad($listItems);





//print_r($table);

