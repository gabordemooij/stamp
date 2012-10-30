<?php

require('StampTE.php');


$t = '
<table>
<thead><tr><th>Pizza</th><th>Price</th></tr></thead>
<tbody>
<!-- cut:pizza -->
<tr><td>#name#</td><td>#price#</td></tr>
<!-- /cut:pizza -->
</tbody>
</table>
';

$data = array(
	'Magaritha' => '6.99',
	'Funghi' => '7.50',
	'Tonno' => '7.99'
);



$priceList = new StampTE($t);

$dish = $priceList->getPizza(); 

foreach($data as $name=>$price) {
	$pizza = $dish->copy(); 
	$pizza->setName($name);
	$pizza->setPrice($price);
	$priceList->add( $pizza ); 
}

echo $priceList;
//exit;


$t = '
<form action="#action#" method="post">
<!-- cut:textField -->
<label>#label#</label><input type="text" name="#name#" value="#value#" />
<!-- /cut:textField -->
</form>
';

$form = new StampTE($t);

$textField = $form->getTextField();
$textField->setLabel('Your Name')
	->setName('person')
	->setValue('It\'s me!');


$form->add($textField);
echo "\n\n\n\n".$form;



$vt = '<div id="forest"><div id="village"><!-- paste:village --></div></div>';
$bt = '
	<div class="catalogue">
		<!-- cut:tavern -->
			<img src="tavern.gif" />
		<!-- /cut:tavern -->
	</div>
';

$v = new StampTE($vt);
$b = new StampTE($bt);
$tavern = $b->getTavern();
$v->village->add($tavern);

echo "\n\n\n".$v;



