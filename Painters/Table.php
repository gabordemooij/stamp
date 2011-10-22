<?php
/**
 * Stamp Painter for Basic HTML Tables
 * Stamp Template Engine Painter Class
 * Paints a basic HTML Table
 */
class Stamp_Painters_Table {
	
	/**
	 * Holds a reference to instance of Stamp
	 * @var Stamp
	 */
	protected $stamp;

	
	/**
	 * 
	 * Constructor. Creates an instance of this Painter.
	 * 
	 * Default Template:
	 * 
	 * <table>
	 *		<!-- cut:header -->
	 *		<thead>
	 *			<!-- cut:headrow -->
	 *			<tr>
	 *				<!-- cut:head -->
	 *				<th>#head#</th>
	 *				<!-- /cut:head -->
	 *			</tr>
	 *			<!-- /cut:headrow -->
	 *			<!-- paste:headrows -->
	 *		</thead>
	 *		<!-- /cut:header -->
	 *		<!-- paste:columns -->
	 *		<tbody>
	 *			<!-- cut:row -->
	 *			<tr>
	 *				<!-- cut:cell -->
	 *				<td>#cell#</td>
	 *				<!-- /cut:cell -->
	 *				<!-- paste:cells(cell,head) -->
	 *			</tr>
	 *			<!-- /cut:row -->
	 *			<!-- paste:rows(row) -->
	 *		</tbody>
	 *	</table>
	 *				
	 * Cut points:
	 * 
	 * header:				A table header
	 * header headrow:		One row for a table header
	 * header headrow head:	A cell for a row in a table header
	 * row:					A row for a table body
	 * row cell:			A cell for a row in a table body
	 * 
	 * Glue Points:
	 * 
	 * header headrows:		A place to put header rows
	 * columns:				A place to put the column/head row
	 * rows:				A place to put a single row	
	 * row cells:			A place to put a single cell
	 * 
	 * Slots:
	 * 
	 * 
	 * headrow head		A slot for a column name
	 * row cell cell	A slot for a single cell value
	 * 
	 * @param Stamp $stamp Stamp instance that contains template (optional)
	 */
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
	
	/**
	 * Adds a single row to the table.
	 * 
	 * @param array $r array containing values for the row
	 * 
	 * @return Stamp_Painters_Table $self Chainable
	 */
	public function addRow( $r ) { 
		$row = $this->stamp->fetch('row');
		foreach($r as $col) {
			$cell = $row->fetch('cell');
			$cell->put('cell',$col);
			$row->pasteIn('cells',$cell);	
		}
		$this->stamp->pasteIn('rows',$row);
		return $this;
	}
	
	
	/**
	 * Sets a row of headers/column names to the table.
	 * 
	 * @param array $h array containing values for the row
	 * 
	 * @return Stamp_Painters_Table $self Chainable
	 */
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
		return $this;
	}
	
	
	/**
	 * Paints an HTML table based on the rows provided and the template.
	 * 
	 * @return Stamp $stamp the resulting stamp object
	 */
	public function paint() {
		return $this->stamp;
	}
	
	
	/**
	 * One-command interface for rendering an HTML table.
	 * Renders an HTML table with the data from an array.
	 * This function generates a Stamp instance with the HTML for table.
	 * The rows of the array are used as rows for the table.
	 * 
	 * @param array $rows rows for the table, keys are ignored
	 * 
	 * @return Stamp $stamp resulting stamp instance
	 */
	public static function paintTableFromArray($array) {
		$t = new self;
		foreach($array as $row) $t->addRow($row);
		return $t->paint();
	}	
	
		
	/**
	 * One-command interface for rendering an HTML table.
	 * Renders an HTML table with the data from an array.
	 * This function generates a Stamp instance with the HTML for table.
	 * The rows of the array are used as rows for the table.
	 * This function takes an additional argument for headers. The entries
	 * in the $header array are used to generate THs in the table head.	
	 * 
	 * @param array $rows    rows for the table, keys are ignored
	 * @param array $headers column names for the table, keys are ignored
	 * 
	 * @return Stamp $stamp resulting stamp instance
	 */
	public static function paintTableWithHeaders($array,$headers) {
		$t = new self;
		$t->addHeaders($headers);
		foreach($array as $row) $t->addRow($row);
		return $t->paint();
		
	}
	
	
}