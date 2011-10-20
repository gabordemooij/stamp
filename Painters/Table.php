<?php

class Stamp_Painters_Table {
	
	protected $stamp;

	protected $options;
	protected $name;
	protected $selected;
	
	
	public function __construct( Stamp $stamp = null ) {
	
		if (!$stamp) {
			
			$stamp = '
				<table>
					<!-- cut:header -->
					<thead>
						<!-- cut:headrow -->
						<tr>
							<!-- cut:head -->
							<th>#head#</th>
							<!-- /cut:head -->
						</tr>
						<!-- /cut:headrow -->
						<!-- paste:headrows -->
					</thead>
					<!-- /cut:header -->
					<!-- paste:columns -->
					<tbody>
						<!-- cut:row -->
						<tr>
							<!-- cut:cell -->
							<td>#cell#</td>
							<!-- /cut:cell -->
							<!-- paste:cells(cell,head) -->
						</tr>
						<!-- /cut:row -->
						<!-- paste:rows(row) -->
					</tbody>
				</table>

			';
			$stamp = new Stamp($stamp);	
		}
		
		$this->stamp = $stamp;
	
	}
	
	
	public function addRow( $r ) { 
		$row = $this->stamp->fetch('row');
		foreach($r as $col) {
			$cell = $row->fetch('cell');
			$cell->put('cell',$col);
			$row->pasteIn('cells',$cell);	
		}
		$this->stamp->pasteIn('rows',$row);
		
	}
	
	
	public function addHeaders($h) {
		$hdr = $this->stamp->fetch('header');
		$hr = $hdr->fetch('headrow');
		$row = $this->stamp->fetch('row');
		foreach($h as $th) {
			$head=$hr->fetch('head');
			$head->put('head',$th);
			$row->pasteIn('cells',$head);
		}	
		$hdr->pasteIn('headrows',$row);
		$this->stamp->pasteIn('columns',$hdr);
	}
	
	
	public function paint() {
		return $this->stamp;
	}
	
	public static function paintTableFromArray($array) {
		$t = new self;
		foreach($array as $row) $t->addRow($row);
		return $t->paint();
	}	
	
	public static function paintTableWithHeaders($array,$headers) {
		$t = new self;
		$t->addHeaders($headers);
		foreach($array as $row) $t->addRow($row);
		return $t->paint();
		
	}
	
	
}