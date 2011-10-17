<?php

require('stamp.php');

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
	exit;
}

function clean($s) {
	return preg_replace("/\s/m","",$s);
}


function testpack($name) {
	printtext("\ntesting: ".$name);
}


testpack("Basics");

//does the Stamp class exist?
if (class_exists('Stamp')) pass();

//Can we succesfully create an instance of the Stamp class?
$stamp = new Stamp('');

//Test the wash() function
$template = "HELL<!-- erase:me -->OWORLD";
$stamp = new Stamp($template);
$stamp->wash();
asrt("HELLOWORLD",trim($stamp));


$template = "HELL<!-- erase:me(and,me) -->OWORLD";
$stamp = new Stamp($template);
$stamp->wash();
asrt("HELLOWORLD",trim($stamp));

$template = "HELL<!-- eraseme -->OWORLD";
$stamp = new Stamp($template);
$stamp->wash();
asrt("HELLOWORLD",trim($stamp));


$template = "HELL<!-- eraseme -->OW<!-- and:me(yeah) -->ORLD";
$stamp = new Stamp($template);
$stamp->wash();
asrt("HELLOWORLD",trim($stamp));


$template = "HELLO



WORLD";
$stamp = new Stamp($template);
$stamp->wash();
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

$stamp = new Stamp($template);
$fish = $stamp->fetch("fish");
$stamp->pasteIn('water',$fish);
$stamp->wash();


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


$stamp = new Stamp($template);
$fish = $stamp->fetch("fish");
$stamp->pasteIn('water',$fish);
$stamp->wash();


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

$stamp = new Stamp($template);
$fish = $stamp->fetch("fish");
$stamp->pasteIn('water',$fish);
$stamp->pasteIn('water',$fish);
$stamp->wash();

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

$stamp = new Stamp($template);
$redfish = $stamp->fetch("redfish");
$yellowfish = $stamp->fetch("yellowfish");
$stamp->pasteIn('bowl1',$redfish);
$stamp->pasteIn('bowl2',$yellowfish);
$stamp->wash();


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

$stamp = new Stamp($template);
$redfish = $stamp->fetch("redfish");
$yellowfish = $stamp->fetch("yellowfish");
$stamp->pasteIn('bowl1',$redfish);
$stamp->pasteIn('bowl2',$yellowfish);
$castle = $stamp->fetch('castle');
$stamp->pasteIn('box',$castle);
$stamp->wash();


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

$stamp = new Stamp($template);
$redfish = $stamp->fetch("fish");
$redfish->put('color','red');
$yellowfish = $stamp->fetch("fish");
$yellowfish->put('color','yellow');
$stamp->pasteIn('bowl1',$redfish);
$stamp->pasteIn('bowl2',$yellowfish);
$castle = $stamp->fetch('castle');
$stamp->pasteIn('box',$castle);
$stamp->put('liters1','50');
$stamp->put('liters2','100');
$stamp->wash();



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

$stamp = new Stamp($template);
$redfish = $stamp->fetch("fish");
$redfish->put('color','red');
$greenfish = $stamp->fetch("fish");
$greenfish->put('color','green');
$yellowfish = $stamp->fetch("fish");
$yellowfish->put('color','yellow');
$stamp->pasteIn('bowl1',$redfish);
$stamp->pasteIn('bowl2',$yellowfish);
$castle = $stamp->fetch('castle');
$castle->pasteIn('incastle',$greenfish);
$jelly = $stamp->fetch('jellyfish');
$castle->pasteIn('incastle',$jelly); //jellyfish not allowed in castle
$stamp->pasteIn('box',$castle);
$stamp->put('liters1','50');
$stamp->put('liters2','100');
$stamp->wash();



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

$stamp = new Stamp($template);
$redfish = $stamp->fetch("fish");
$redfish->put('color','red');
$greenfish = $stamp->fetch("fish");
$greenfish->put('color','green');
$yellowfish = $stamp->fetch("fish");
$yellowfish->put('color','yellow');
$stamp->pasteIn('bowl1',$redfish);
$stamp->pasteIn('bowl2',$yellowfish);
$castle = $stamp->fetch('castle');
$castle->pasteIn('incastle',$greenfish);
$jelly = $stamp->fetch('jellyfish');
$castle->pasteIn('incastle',$jelly); //jellyfish IS allowed in castle
$stamp->pasteIn('box',$castle);
$stamp->put('liters1','50');
$stamp->put('liters2','100');
$stamp->wash();



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

testpack('Test Stamp metaphor');

$template = "
	<garden>
		<!-- flowers -->
			<!-- flower -->
				<flower type='rose'></flower>
			<!-- /flower -->
		<!-- /flowers -->
	</garden>
";

$stamp = new Stamp($template);
$flower = $stamp->copy('flower');
$flowers = $flower . $flower;
$stamp->replace('flowers',$flowers);
$stamp->wash();

$expectation = "
	<garden>
		<flower type='rose'></flower>
		<flower type='rose'></flower>
	</garden>
";

asrt(clean($stamp),clean($expectation));

//Stamp and slots

$template = "
	<garden>
		<!-- flowers -->
			<!-- flower -->
				<flower type='#type#'></flower>
			<!-- /flower -->
		<!-- /flowers -->
	</garden>
";

$stamp = new Stamp($template);
$flower = $stamp->copy('flower');
$flower->put('type','lily');
$flower2 = $stamp->copy('flower');
$flower2->put('type','phlox');
$flowers = $flower . $flower2;
$stamp->replace('flowers',$flowers);
$stamp->wash();

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
			<!-- pond -->
			<!-- /pond -->
		</water>
		<!-- flowers -->
			<!-- flower -->
				<flower type='#type#'></flower>
			<!-- /flower -->
		<!-- /flowers -->
	</garden>
";

$stamp = new Stamp($template);
$flower = $stamp->copy('flower');
$flower->put('type','lily');
$pond = $stamp->replace('pond',$flower);
$flower2 = $stamp->copy('flower');
$flower2->put('type','phlox');
$flowers = $flower2;
$stamp->replace('flowers',$flowers);
$stamp->wash();

$expectation = "
	<garden>
		<water>
			<flower type='lily'></flower>
		</water>
		<flower type='phlox'></flower>
	</garden>
";

asrt(clean($stamp),clean($expectation));

testpack('Extending templates');

$template = "
	<room>
		<wall>
			<!-- wall -->
			<!-- /wall -->
		</wall>
		<floor>
			<!-- floor -->
			<!-- /floor -->
		</floor>
	</room>	
";

$template_wall = "
	<!-- wall -->
	<painting artist='Picasso'>
	</painting>
	<!-- /wall -->
";

$template_floor = "
	<!-- floor -->
	<carpet>
		<table>
			<!-- table -->
			<!-- /table -->
		</table>
	</carpet>
	<!-- /floor -->
";

$template_table = "
	<!-- table -->
	<cookiejar/>
	<!-- /table -->
";

$floor = new Stamp($template_floor);
$floor->extendWith($template_table);

$washed_floor = clone $floor;
$washed_floor->wash();

$expectation = "<carpet><table><cookiejar/></table></carpet>";

asrt(clean($washed_floor),clean($expectation));

$room = new Stamp($template);
$room->extendWith($template_wall)->extendWith($floor);

$expectation = "
	<room>
		<wall>
		<painting artist='Picasso'>
		</painting>
		</wall>
		<floor>
		<carpet>
			<table>
				<cookiejar/>
			</table>
		</carpet>
		</floor>
	</room>
";

asrt(clean($room),clean($expectation));

testpack('Paste and clean');

$stamp = new Stamp('test');
$stamp->paste('hello');
asrt(trim($stamp),'hello');

$stamp = new Stamp('test');
$stamp->clean();
asrt(trim($stamp),'');

testpack('PastePad');

$stamp = new Stamp('<b></b>');
$stamp->pastePad('<hello>');

asrt(trim($stamp),'<b><hello></b>');

testpack('Slot test');

$stamp = new Stamp('#slot1#');
asrt($stamp->hasSimpleSlot('slot1'),true);
asrt($stamp->hasSimpleSlot('slot2'),false);

testpack('Loader');
file_put_contents('loadertest.txt','<mytemplate>');
$stamp = Stamp::load('loadertest.txt');
pass();
asrt(trim($stamp),'<mytemplate>');

try{
	$stamp = Stamp::load('loadertest_nonexistant.txt');
	fail();
}
catch(Exception $e){
	pass();
}





