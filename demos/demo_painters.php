<?php

require "../Stamp.php";

require('../Painters/Select.php');
require('../Painters/Table.php');

echo Stamp_Painters_Select::paintListKeyAsLabel('pizza',array(
	'Margharita'=>1,
	'Funghi'=>2,
	'Venezia'=>3
),2);


echo Stamp_Painters_Select::paintListValueAsLabel('pizza',array(
	'Margharita'=>1,
	'Funghi'=>2,
	'Venezia'=>3
));


echo Stamp_Painters_Select::paintListSimple('pizza',array(
	'Margharita',
	'Funghi',
	'Venezia'
));


echo Stamp_Painters_Table::paintTableFromArray(array(
	array( 'Pizza','Price' ),
	array( 'Margaretha','7.00'),
	array( 'Funghi','8.00'),
	array( 'Venezia','9.00')
))->wash();

echo Stamp_Painters_Table::paintTableWithHeaders(array(
	array( 'Margaretha','7.00'),
	array( 'Funghi','8.00'),
	array( 'Venezia','9.00')
),array( 'Pizza','Price' ))->wash();

