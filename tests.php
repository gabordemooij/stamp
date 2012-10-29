<?php

/**
 * Basic testing functions
 */
$c = 0;
function printtext($text) { echo $text; }
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
function clean($s) { return preg_replace("/\s/m","",$s); }
function testpack($name) { printtext("\ntesting: ".$name); }

/**
 * The real testing stuff
 */
require('StampTE.php');
testpack("Basics");

$template = '<message>#greet#</message>';
$s = new StampTE($template);
$s->inject('greet','<HELLO>');
asrt('<message>&lt;HELLO&gt;</message>',$s->__toString());

//does the StampTETE class exist?
if (class_exists('StampTE')) pass();

//Can we succesfully create an instance of the StampTETE class?
$StampTE = new StampTE('');

//Test the wash() function
$template = "HELL<!-- paste:me -->OWORLD";
$StampTE = new StampTE($template);
asrt("HELLOWORLD",trim($StampTE));

$template = "HELL<!-- paste:me(and,me) -->OWORLD";
$StampTE = new StampTE($template);
asrt("HELLOWORLD",trim($StampTE));

$template = "HELL<!-- paste:test -->OWORLD";
$StampTE = new StampTE($template);
asrt("HELLOWORLD",trim($StampTE));

$template = "HELL<!-- cut:wow -->OW<!-- /cut:wow -->ORLD";
$StampTE = new StampTE($template);
asrt("HELLORLD",trim($StampTE));

$template = "HELLO



WORLD";
$StampTE = new StampTE($template);
asrt("HELLO
WORLD",trim($StampTE));

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

$StampTE = new StampTE($template);
$fish = $StampTE->get("fish");
$StampTE->glue('water',$fish);

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
asrt(clean($StampTE),clean($expectation));

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

$StampTE = new StampTE($template);
$fish = $StampTE->get("fish");
$StampTE->glue('water',$fish);

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
asrt(clean($StampTE),clean($expectation));

//Can we put more than one fish in the bowl?
$StampTE = new StampTE($template);
$fish = $StampTE->get("fish");
$StampTE->glue('water',$fish);
$StampTE->glue('water',$fish);

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
asrt(clean($StampTE),clean($expectation));

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

$StampTE = new StampTE($template);
$redfish = $StampTE->get("redfish");
$yellowfish = $StampTE->get("yellowfish");
$StampTE->glue('bowl1',$redfish);
$StampTE->glue('bowl2',$yellowfish);


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

asrt(clean($StampTE),clean($expectation));

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

$StampTE = new StampTE($template);
$redfish = $StampTE->get("redfish");
$yellowfish = $StampTE->get("yellowfish");
$StampTE->glue('bowl1',$redfish);
$StampTE->glue('bowl2',$yellowfish);
$castle = $StampTE->get('castle');
$StampTE->glue('box',$castle);


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

asrt(clean($StampTE),clean($expectation));

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

$StampTE = new StampTE($template);
$redfish = $StampTE->get("fish");
$redfish->inject('color','red');
$yellowfish = $StampTE->get("fish");
$yellowfish->inject('color','yellow');
$StampTE->glue('bowl1',$redfish);
$StampTE->glue('bowl2',$yellowfish);
$castle = $StampTE->get('castle');
$StampTE->glue('box',$castle);
$StampTE->injectAll(array('liters1'=>'50','liters2'=>'100'));

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

asrt(clean($StampTE),clean($expectation));

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

$StampTE = new StampTE($template);
$redfish = $StampTE->get("fish");
$redfish->inject('color','red');
$greenfish = $StampTE->get("fish");
$greenfish->inject('color','green');
$yellowfish = $StampTE->get("fish");
$yellowfish->inject('color','yellow');
$StampTE->glue('bowl1',$redfish);
$StampTE->glue('bowl2',$yellowfish);
$castle = $StampTE->get('castle');
$castle->glue('incastle',$greenfish);
$jelly = $StampTE->get('jellyfish');
try{
$castle->glue('incastle',$jelly); //jellyfish not allowed in castle
	fail();

}
catch(Exception $e){
	pass();
}
$StampTE->glue('box',$castle);
$StampTE->inject('liters1','50');
$StampTE->inject('liters2','100');

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

asrt(clean($StampTE),clean($expectation));

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

$StampTE = new StampTE($template);
$redfish = $StampTE->get("fish");
$redfish->inject('color','red');
$greenfish = $StampTE->get("fish");
$greenfish->inject('color','green');
$yellowfish = $StampTE->get("fish");
$yellowfish->inject('color','yellow');
$StampTE->glue('bowl1',$redfish);
$StampTE->glue('bowl2',$yellowfish);
$castle = $StampTE->get('castle');
$castle->glue('incastle',$greenfish);
$jelly = $StampTE->get('jellyfish');
$castle->glue('incastle',$jelly); //jellyfish IS allowed in castle
$StampTE->glue('box',$castle);
$StampTE->inject('liters1','50');
$StampTE->inject('liters2','100');

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

asrt(clean($StampTE),clean($expectation));

testpack('Test StampTETE metaphor');

$template = "
	<garden>
		<!-- paste:flowers -->
		<!-- cut:flower -->
			<flower type='rose'></flower>
		<!-- /cut:flower -->
	</garden>
";

$StampTE = new StampTE($template);
$flower1 = $StampTE->get('flower')->copy();
$flower2 = $StampTE->get('flower')->copy();
$flowers = array('flowers'=>array($flower1,$flower2));
$StampTE->glueAll($flowers);

$expectation = "
	<garden>
		<flower type='rose'></flower>
		<flower type='rose'></flower>
	</garden>
";

asrt(clean($StampTE),clean($expectation));

//StampTETE and slots

$template = "
	<garden>
		<!-- paste:flowers -->
		<!-- cut:flower -->
			<flower type='#type#'></flower>
		<!-- /cut:flower -->
	</garden>
";

$StampTE = new StampTE($template);
$flower = $StampTE->get('flower')->copy();
$flower2 = $flower->copy();
$flower->inject('type','lily');
$flower2->inject('type','phlox');
$flowers = $flower . $flower2;
$StampTE->glue('flowers',$flowers);

$expectation = "
	<garden>
		<flower type='lily'></flower>
		<flower type='phlox'></flower>
	</garden>
";

asrt(clean($StampTE),clean($expectation));

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

$StampTE = new StampTE($template);
$flower = $StampTE->get('flower')->copy();
$flower2 = $flower->copy();
$flower->inject('type','lily');
$pond = $StampTE->glue('pond',$flower);
$flower2->inject('type','phlox');
$flowers = $flower2;
$StampTE->glue('flowers',$flowers);

$expectation = "
	<garden>
		<water>
			<flower type=\"lily\"></flower>
		</water>
		<flower type=\"phlox\"></flower>
	</garden>
";

asrt(clean($StampTE),clean($expectation));


testpack('Infinte loop - no longer an issue, preserving tests.');
$template = '<!-- cut:hello -->hello there';
$StampTE = new StampTE($template);
asrt(strval($StampTE),'<!-- cut:hello -->hello there');
	
$StampTE = new StampTE('<!-- cut:hello ');
asrt(strval($StampTE),'<!-- cut:hello');

testpack('Wrong regions');
$StampTE = new StampTE('data<!-- cut:and logic');
pass();
$StampTE = new StampTE('cut:end --!> without a beginning.');
pass();
$StampTE = new StampTE('--!>');
pass();
$StampTE = new StampTE('<!--');
pass();
$StampTE = new StampTE('<!-- cut:logic -->');
pass();
$StampTE = new StampTE('<!-- /cut:logic -->');
pass();
$StampTE = new StampTE('a<!-- cut:chest -->treasure<!-- /cut:chest -->b');
asrt(strval($StampTE->get('chest')),'treasure');
pass();
$StampTE = new StampTE('a<!-- cut:chest -->treasure<!-- /cut:chest -->b');
asrt(strval($StampTE->get('chest')),'treasure');
pass();
$StampTE = new StampTE('a<!-- cut:chest -->treasure<!-- /cat:chest -->b');
asrt(strval($StampTE) ,'a<!-- cut:chest -->treasure<!-- /cat:chest -->b');

testpack('Test Self-Replace');
$stampTE = new StampTE('
<ul>
	<!-- cut:todo -->
	<li>#todo#</li>
	<!-- /cut:todo -->
</ul>
');
$todoItem = $stampTE->get('todo');
$todoItem->inject('todo','Make Coffee');
$stampTE->add($todoItem);
$expectation = '
<ul>
	<li>Make Coffee</li>
</ul>
';
asrt(trim(strval($stampTE)),trim($expectation));

//Now with two lists
$stampTE = new StampTE('
<ul>
	<!-- cut:todo -->
	<li>#todo#</li>
	<!-- /cut:todo -->
	<!-- cut:todo2 -->
	<li>#todo#</li>
	<!-- /cut:todo2 -->
</ul>
');
$todoItem = $stampTE->get('todo');
$todoItem->inject('todo','Make Coffee');
$stampTE->add($todoItem);
$expectation = '
<ul>
	<li>Make Coffee</li>
</ul>
';
asrt(trim(strval($stampTE)),trim($expectation));

$stampTE = new StampTE('
<ul>
	<!-- cut:todo -->
	<li>#todo#</li>
	<!-- /cut:todo -->
	<!-- cut:todo2 -->
	<li><b>#todo#</b></li>
	<!-- /cut:todo2 -->
</ul>
');
$todoItem2 = $stampTE->get('todo2');
$todoItem2->inject('todo','Clean the house');
$todoItem = $stampTE->get('todo');
$todoItem->inject('todo','Make Coffee');
$stampTE->add($todoItem2);
$stampTE->add($todoItem);
$expectation = '
<ul>
	<li>Make Coffee</li>
	<li><b>Clean the house</b></li>
</ul>
';
asrt(trim(strval($stampTE)),trim($expectation));

testpack('Test Filters');
$template = '<b>#test#</b>';

class InternationalStampTETE extends StampTE {
	protected function filter($data) {
		$data = DICT($data);
		$data = parent::filter($data);
		return $data;
	}
}
function DICT($text) {
	if ($text=='hello') return 'Allo';
}

$StampTE = new InternationalStampTETE($template);
$StampTE->inject('test','hello');
asrt(strval($StampTE),'<b>Allo</b>');

testpack('Test Cleaning');

$StampTE = new StampTE('Test <!-- paste:test --> test <!-- cut:piece -->piece<!-- /cut:piece -->');
$str = strval($StampTE);
asrt(strpos('<!--',$str),false);
$p = $StampTE->get('piece');
$StampTE->glue('test',$p);
$str = strval($StampTE);
asrt(strpos('<!--',$str),false);

exit(0);
