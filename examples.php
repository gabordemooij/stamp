<?php

require('StampEngine.php');

$template = '
	<!-- cut:form -->
	<form action="#action#" method="#method#">
		<!-- paste:formElements -->
		<!-- cut:textfield -->
			<label>#label#</label>
			<input type="text" name="#name#" value="#value#" />
		<!-- /cut:textfield -->
		<input type="submit" name="#send#" />
	</form>
	<!-- /cut:form -->
';


$se = new StampEngine($template);
list($form,$textfield) = $se->collect('form|form.textfield');

$form->glue('formElements',
	$textfield->copy()->injectAll(array(
			'label'=>'Your name',
			'name'=>'username',
			'value'=>'...your name please...'))
	)->inject('send','Update your Profile');

echo $form;

$template =  "
	

	<!-- cut:table -->
	<table>
		<thead>
			<tr>
				<!-- paste:columns(column,data) -->
				<!-- cut:column -->
				<th>#column#</th>
				<!-- /cut:column -->
			</tr>
		</thead>
		<tbody>
			<!-- paste:rows -->
			<!-- cut:row -->
			<tr>
				<!-- paste:cells -->
				<!-- cut:cell -->
				<td>#cell#</td>
				<!-- /cut:cell -->
			</tr>
			<!-- /cut:row -->
		</tbody>
	</table>
	<!-- /cut:table -->
";




$columns = array('Pizza','Price');
$data = array(array('Pepperoni','7.99'),array('Veggie','6.99'));




$se = new StampEngine($template);
list($table,$columnHead,$row,$cell) = $se->collect('table|table.column|table.row|table.row.cell');
foreach($columns as $column) $table->glue('columns',$columnHead->copy()->inject('column',$column));
foreach($data as $pizzaInfoLine) {
	$pizzaRow = $row->copy();
	foreach($pizzaInfoLine as $pizzaInfo) {
		$pizzaRow->glue('cells',$cell->copy()->inject('cell',$pizzaInfo));
	}
	$table->glue('rows',$pizzaRow);
}
echo $table;