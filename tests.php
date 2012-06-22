<?php

$c = 0;


function printtext($text) {
	echo $text;
}

function asrt( $a, $b ) {
	if ($a === $b) {
		global $tests;
		$tests++;
		print( "[".$tests."]" );
	}
	else {
		printtext("FAILED TEST: EXPECTED $b BUT GOT: $a ");
		fail();
	}
}

function pass() {
	global $tests;
	$tests++;
	print( "[".$tests."]" );
}

function fail() {
	printtext("FAILED TEST");
	debug_print_backtrace();
	exit(1);
}

function clean($s) {
	return preg_replace("/\s/m","",$s);
}


function testpack($name) {
	printtext("\ntesting: ".$name);
}


require('StampTE.php');



testpack("Basics");


$template = '<message>#greet#</message>';
$s = new StampTE($template);
$s->inject('greet','<HELLO>');

asrt('<message>&lt;HELLO&gt;</message>',$s->__toString());



//does the StampTE class exist?
if (class_exists('StampTE')) pass();

//Can we succesfully create an instance of the StampTE class?
$stamp = new StampTE('');

//Test the wash() function
$template = "HELL<!-- paste:me -->OWORLD";
$stamp = new StampTE($template);
asrt("HELLOWORLD",trim($stamp));


$template = "HELL<!-- paste:me(and,me) -->OWORLD";
$stamp = new StampTE($template);
asrt("HELLOWORLD",trim($stamp));

$template = "HELL<!-- paste:test -->OWORLD";
$stamp = new StampTE($template);
asrt("HELLOWORLD",trim($stamp));


$template = "HELL<!-- cut:wow -->OW<!-- /cut:wow -->ORLD";
$stamp = new StampTE($template);
asrt("HELLORLD",trim($stamp));


$template = "HELLO



WORLD";
$stamp = new StampTE($template);
asrt("HELLO
WORLD",trim($stamp));


testpack("Test Cut and Paste Metaphor");
$template = "
	<box>
		<!-- cut:fish -->
			<fish>
				<eye></eye>
				<eye></eye>
			</fish>
		<!-- /cut:fish -->
	</box>
	<bowl>
		<!-- paste:water -->
	</bowl>
";

$stamp = new StampTE($template);
$fish = $stamp->get("fish");
$stamp->glue('water',$fish);



$expectation = "
	<box>
	</box>
	<bowl>
			<fish>
				<eye></eye>
				<eye></eye>
			</fish>
	</bowl>
";
asrt(clean($stamp),clean($expectation));

//Does it work with more than one cut area?
$template = "
	<box>
		<!-- cut:fish -->
			<fish>
				<eye></eye>
				<eye></eye>
			</fish>
		<!-- /cut:fish -->
		<!-- cut:castle -->
			<castle/>
		<!-- /cut:castle -->
	</box>
	<bowl>
		<!-- paste:water -->
	</bowl>
";


$stamp = new StampTE($template);
$fish = $stamp->get("fish");
$stamp->glue('water',$fish);



$expectation = "
	<box>
	</box>
	<bowl>
			<fish>
				<eye></eye>
				<eye></eye>
			</fish>
	</bowl>
";
asrt(clean($stamp),clean($expectation));

//Can we put more than one fish in the bowl?

$stamp = new StampTE($template);
$fish = $stamp->get("fish");
$stamp->glue('water',$fish);
$stamp->glue('water',$fish);


$expectation = "
	<box>
	</box>
	<bowl>
			<fish>
				<eye></eye>
				<eye></eye>
			</fish>
			<fish>
				<eye></eye>
				<eye></eye>
			</fish>
	</bowl>
";
asrt(clean($stamp),clean($expectation));

//What about multiple slots?
$template = "
	<box>
		<!-- cut:redfish -->
			<fish color='red'></fish>
		<!-- /cut:redfish -->
		<!-- cut:yellowfish -->
			<fish color='yellow'></fish>
		<!-- /cut:yellowfish -->
	</box>
	<bowl>
		<!-- paste:bowl1 -->
		<!-- cut:castle -->
			<castle/>
		<!-- /cut:castle -->
	</bowl>
	<bowl>
		<!-- paste:bowl2 -->
	</bowl>
";

$stamp = new StampTE($template);
$redfish = $stamp->get("redfish");
$yellowfish = $stamp->get("yellowfish");
$stamp->glue('bowl1',$redfish);
$stamp->glue('bowl2',$yellowfish);


$expectation = "
	<box>
	</box>
	<bowl>
		<fish color='red'></fish>
	</bowl>
	<bowl>
		<fish color='yellow'></fish>
	</bowl>
";

asrt(clean($stamp),clean($expectation));

//Now put the castle from the bowl in the box as well.
$template = "
	<box>
		<!-- cut:redfish -->
			<fish color='red'></fish>
		<!-- /cut:redfish -->
		<!-- cut:yellowfish -->
			<fish color='yellow'></fish>
		<!-- /cut:yellowfish -->
		<!-- paste:box -->
	</box>
	<bowl>
		<!-- paste:bowl1 -->
		<!-- cut:castle -->
			<castle/>
		<!-- /cut:castle -->
	</bowl>
	<bowl>
		<!-- paste:bowl2 -->
	</bowl>
";

$stamp = new StampTE($template);
$redfish = $stamp->get("redfish");
$yellowfish = $stamp->get("yellowfish");
$stamp->glue('bowl1',$redfish);
$stamp->glue('bowl2',$yellowfish);
$castle = $stamp->get('castle');
$stamp->glue('box',$castle);


$expectation = "
	<box>
		<castle/>
	</box>
	<bowl>
		<fish color='red'></fish>
	</bowl>
	<bowl>
		<fish color='yellow'></fish>
	</bowl>
";

asrt(clean($stamp),clean($expectation));

//Test same, in combination with slots (complex template)
$template = "
	<box>
		<!-- cut:fish -->
			<fish color='#color#'></fish>
		<!-- /cut:fish -->
		<!-- paste:box -->
	</box>
	<bowl water='#liters1#'>
		<!-- paste:bowl1 -->
		<!-- cut:castle -->
			<castle/>
		<!-- /cut:castle -->
	</bowl>
	<bowl water='#liters2#'>
		<!-- paste:bowl2 -->
	</bowl>
";

$stamp = new StampTE($template);
$redfish = $stamp->get("fish");
$redfish->inject('color','red');
$yellowfish = $stamp->get("fish");
$yellowfish->inject('color','yellow');
$stamp->glue('bowl1',$redfish);
$stamp->glue('bowl2',$yellowfish);
$castle = $stamp->get('castle');
$stamp->glue('box',$castle);
$stamp->injectAll(array('liters1'=>'50','liters2'=>'100'));




$expectation = "
	<box>
		<castle/>
	</box>
	<bowl water='50'>
		<fish color='red'></fish>
	</bowl>
	<bowl water='100'>
		<fish color='yellow'></fish>
	</bowl>
";

asrt(clean($stamp),clean($expectation));

//Nest and restrictions
$template = "
	<box>
		<!-- cut:fish -->
			<fish color='#color#'></fish>
		<!-- /cut:fish -->
		<!-- paste:box -->
	</box>
	<bowl water='#liters1#'>
		<!-- paste:bowl1 -->
		<!-- cut:castle -->
			<castle>
				<!-- paste:incastle(fish) -->
			</castle>
		<!-- /cut:castle -->
		<!-- cut:jellyfish -->
			<jellyfish/>
		<!-- /cut:jellyfish -->
	</bowl>
	<bowl water='#liters2#'>
		<!-- paste:bowl2 -->
	</bowl>
";

$stamp = new StampTE($template);
$redfish = $stamp->get("fish");
$redfish->inject('color','red');
$greenfish = $stamp->get("fish");
$greenfish->inject('color','green');
$yellowfish = $stamp->get("fish");
$yellowfish->inject('color','yellow');
$stamp->glue('bowl1',$redfish);
$stamp->glue('bowl2',$yellowfish);
$castle = $stamp->get('castle');
$castle->glue('incastle',$greenfish);
$jelly = $stamp->get('jellyfish');
try{
$castle->glue('incastle',$jelly); //jellyfish not allowed in castle
	fail();

}
catch(Exception $e){
	pass();
}
$stamp->glue('box',$castle);
$stamp->inject('liters1','50');
$stamp->inject('liters2','100');



$expectation = "
	<box>
		<castle>
			<fish color='green'></fish>
		</castle>
	</box>
	<bowl water='50'>
		<fish color='red'></fish>
	</bowl>
	<bowl water='100'>
		<fish color='yellow'></fish>
	</bowl>
";

asrt(clean($stamp),clean($expectation));

//Nest and restrictions part 2
$template = "
	<box>
		<!-- cut:fish -->
			<fish color='#color#'></fish>
		<!-- /cut:fish -->
		<!-- paste:box -->
	</box>
	<bowl water='#liters1#'>
		<!-- paste:bowl1 -->
		<!-- cut:castle -->
			<castle>
				<!-- paste:incastle(fish,jellyfish) -->
			</castle>
		<!-- /cut:castle -->
		<!-- cut:jellyfish -->
			<jellyfish/>
		<!-- /cut:jellyfish -->
	</bowl>
	<bowl water='#liters2#'>
		<!-- paste:bowl2 -->
	</bowl>
";

$stamp = new StampTE($template);
$redfish = $stamp->get("fish");
$redfish->inject('color','red');
$greenfish = $stamp->get("fish");
$greenfish->inject('color','green');
$yellowfish = $stamp->get("fish");
$yellowfish->inject('color','yellow');
$stamp->glue('bowl1',$redfish);
$stamp->glue('bowl2',$yellowfish);
$castle = $stamp->get('castle');
$castle->glue('incastle',$greenfish);
$jelly = $stamp->get('jellyfish');
$castle->glue('incastle',$jelly); //jellyfish IS allowed in castle
$stamp->glue('box',$castle);
$stamp->inject('liters1','50');
$stamp->inject('liters2','100');



$expectation = "
	<box>
		<castle>
			<fish color='green'></fish>
			<jellyfish/>
		</castle>
	</box>
	<bowl water='50'>
		<fish color='red'></fish>
	</bowl>
	<bowl water='100'>
		<fish color='yellow'></fish>
	</bowl>
";

asrt(clean($stamp),clean($expectation));

testpack('Test StampTE metaphor');

$template = "
	<garden>
		<!-- paste:flowers -->
		<!-- cut:flower -->
			<flower type='rose'></flower>
		<!-- /cut:flower -->
	</garden>
";

$stamp = new StampTE($template);
$flower1 = $stamp->get('flower')->copy();
$flower2 = $stamp->get('flower')->copy();
$flowers = array('flowers'=>array($flower1,$flower2));
$stamp->glueAll($flowers);

$expectation = "
	<garden>
		<flower type='rose'></flower>
		<flower type='rose'></flower>
	</garden>
";

asrt(clean($stamp),clean($expectation));

//StampTE and slots

$template = "
	<garden>
		<!-- paste:flowers -->
		<!-- cut:flower -->
			<flower type='#type#'></flower>
		<!-- /cut:flower -->
	</garden>
";

$stamp = new StampTE($template);
$flower = $stamp->get('flower')->copy();
$flower2 = $flower->copy();
$flower->inject('type','lily');
$flower2->inject('type','phlox');
$flowers = $flower . $flower2;
$stamp->glue('flowers',$flowers);

$expectation = "
	<garden>
		<flower type='lily'></flower>
		<flower type='phlox'></flower>
	</garden>
";

asrt(clean($stamp),clean($expectation));


//Complex, put lily in pond

$template = "
	<garden>
		<water>
			<!-- paste:pond -->
		</water>
		<!-- paste:flowers -->
		<!-- cut:flower -->
		<flower type=\"#type#\"></flower>
		<!-- /cut:flower -->
	</garden>
";

$stamp = new StampTE($template);
$flower = $stamp->get('flower')->copy();
$flower2 = $flower->copy();
$flower->inject('type','lily');
$pond = $stamp->glue('pond',$flower);
$flower2->inject('type','phlox');
$flowers = $flower2;
$stamp->glue('flowers',$flowers);

$expectation = "
	<garden>
		<water>
			<flower type=\"lily\"></flower>
		</water>
		<flower type=\"phlox\"></flower>
	</garden>
";

asrt(clean($stamp),clean($expectation));


testpack('Infinte loop - no longer an issue, preserving tests.');
$template = '<!-- cut:hello -->hello there';
$stamp = new StampTE($template);
asrt(strval($stamp),'<!-- cut:hello -->hello there');
	
$stamp = new StampTE('<!-- cut:hello ');
asrt(strval($stamp),'<!-- cut:hello');

testpack('Wrong regions');
$stamp = new StampTE('data<!-- cut:and logic');
pass();
$stamp = new StampTE('cut:end --!> without a beginning.');
pass();
$stamp = new StampTE('--!>');
pass();
$stamp = new StampTE('<!--');
pass();
$stamp = new StampTE('<!-- cut:logic -->');
pass();
$stamp = new StampTE('<!-- /cut:logic -->');
pass();
$stamp = new StampTE('a<!-- cut:chest -->treasure<!-- /cut:chest -->b');
asrt(strval($stamp->get('chest')),'treasure');
pass();
$stamp = new StampTE('a<!-- cut:chest -->treasure<!-- /cut:chest -->b');
asrt(strval($stamp->get('chest')),'treasure');
pass();
$stamp = new StampTE('a<!-- cut:chest -->treasure<!-- /cat:chest -->b');
asrt(strval($stamp) ,'a<!-- cut:chest -->treasure<!-- /cat:chest -->b');


testpack('Test Filters');
$template = '<b>#test#</b>';

class InternationalStampTE extends StampTE {

	
	protected function filter($data) {
		$data = DICT($data);
		$data = parent::filter($data);
		return $data;
	}
	
}
function DICT($text) {
	if ($text=='hello') return 'Allo';
}

$stamp = new InternationalStampTE($template);
$stamp->inject('test','hello');
asrt(strval($stamp),'<b>Allo</b>');

testpack('Test Cleaning');

$stamp = new StampTE('Test <!-- paste:test --> test <!-- cut:piece -->piece<!-- /cut:piece -->');
$str = strval($stamp);
asrt(strpos('<!--',$str),false);
$p = $stamp->get('piece');
$stamp->glue('test',$p);
$str = strval($stamp);
asrt(strpos('<!--',$str),false);

exit(0);
