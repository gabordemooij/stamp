<?php

xdebug_start_code_coverage( XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE );

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

testpack('Test Whitespace handling');
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
asrt($template,trim($StampTE));

//test whitespace
$template ="<div>
	<!-- cut:region -->
	<div>region</div>
	<!-- /cut:region -->
	<!-- paste:regions -->
</div>";

$expect = "<div>
</div>";

$stampTE = new StampTE($template);
asrt((string)$stampTE, $expect);


$template ="<div>
	<!-- cut:region -->
	<div>region</div>
	<!-- /cut:region -->
	<!-- paste:regions -->
</div>";

$expect = "<div>
	<div>region</div>
</div>";

$stampTE = new StampTE($template);
$stampTE->add($stampTE->getRegion());
asrt((string)$stampTE, $expect);

$template ="<div>
	<!-- cut:region -->
	<div>region</div>
	<!-- /cut:region -->
	<!-- paste:regions -->
</div>";

$expect = "<div>
	<div>region</div>
</div>";

$stampTE = new StampTE($template);
$stampTE->regions->add($stampTE->getRegion());
asrt((string)$stampTE, $expect);

$template ="<div>
	<!-- cut:region -->
	<div>region</div>
	<!-- /cut:region -->
	<!-- paste:regions -->
	<!-- paste:regions1 -->
	<!-- paste:regions2 -->
	<!-- cut:regionx -->
	<div>region</div>
	<!-- /cut:regionx -->
	<!-- paste:regions3 -->
</div>";

$expect = "<div>
	<div>region</div>
</div>";

$stampTE = new StampTE($template);
$stampTE->add($stampTE->getRegion());
asrt((string)$stampTE, $expect);


$template ="<div>
	<!-- cut:region -->
	<div>|#value#|</div>
	<!-- /cut:region -->
	<!-- paste:regions -->
</div>";

$expect = "<div>
	<div>|lorem ipsum|</div>
</div>";

$stampTE = new StampTE($template);
$stampTE->regions->add($stampTE->getRegion()->setValue('lorem ipsum'));
asrt((string)$stampTE, $expect);

$template ="<div>
	<!-- cut:region -->
	<div>| 
	#value#|</div>
	<!-- /cut:region -->
	<!-- paste:regions -->
</div>";

$expect = "<div>
	<div>| 
	lorem ipsum|</div>
</div>";

$stampTE = new StampTE($template);
$stampTE->regions->add($stampTE->getRegion()->setValue('lorem ipsum'));
asrt((string)$stampTE, $expect);


$template ="<div>
	<!-- cut:region -->
	<div>|<!-- slot:value --><!-- /slot:value -->|</div>
	<!-- /cut:region -->
	<!-- paste:regions -->
</div>";

$expect = "<div>
	<div>|lorem ipsum|</div>
</div>";

$stampTE = new StampTE($template);
$stampTE->regions->add($stampTE->getRegion()->setValue('lorem ipsum'));
asrt((string)$stampTE, $expect);


$template ="<div>
	<!-- cut:region -->
	<div>|<!-- slot:value -->DEMO<!-- /slot:value -->|</div>
	<!-- /cut:region -->
	<!-- paste:regions -->
</div>";

$expect = "<div>
	<div>|lorem ipsum|</div>
</div>";

$stampTE = new StampTE($template);
$stampTE->regions->add($stampTE->getRegion()->setValue('lorem ipsum'));
asrt((string)$stampTE, $expect);


$template ="<div>
	<!-- cut:region -->
	<div>|#value?#|</div>
	<!-- /cut:region -->
	<!-- paste:regions -->
</div>";

$expect = "<div>
	<div>|lorem ipsum|</div>
</div>";

$stampTE = new StampTE($template);
$stampTE->regions->add($stampTE->getRegion()->setValue('lorem ipsum'));
asrt((string)$stampTE, $expect);


$template ="<div>
	<!-- cut:region -->
	<div>|#value?#|</div>
	<!-- /cut:region -->
	<!-- paste:regions -->
	<!-- paste:regions2(france,germany) -->
</div>";

$expect = "<div>
	<div>||</div>
</div>";

$stampTE = new StampTE($template);
$stampTE->regions->add($stampTE->getRegion());
asrt((string)$stampTE, $expect);


$template ="<div>
	<!-- cut:region -->

	<div>region </div>
	<!-- /cut:region -->
	<!-- paste:regions -->
</div>";

$expect = "<div>

	<div>region </div>
</div>";

$stampTE = new StampTE($template);
$stampTE->regions->add($stampTE->getRegion());
asrt((string)$stampTE, $expect);


testpack('UTF8 Tests');

$template ="<div>
	<!-- cut:region -->
	<div>象形字</div>
	<!-- /cut:region -->
	<!-- paste:regions -->
</div>";

$expect = "<div>
	<div>象形字</div>
</div>";

$stampTE = new StampTE($template);
$stampTE->regions->add($stampTE->getRegion());
asrt((string)$stampTE, $expect);


$template ="<div>
	<!-- cut:region -->
	<div>#value#</div>
	<!-- /cut:region -->
	<!-- paste:regions -->
</div>";

$expect = "<div>
	<div>象形字</div>
</div>";

$stampTE = new StampTE($template);
$stampTE->regions->add($stampTE->getRegion()->setValue('象形字'));
asrt((string)$stampTE, $expect);

$template ="<div>象形字
	<!-- cut:region -->
	<div>象形字#value#</div>
	<!-- /cut:region -->
	<!-- paste:regions -->
</div>";

$expect = "<div>象形字
	<div>象形字象形字</div>
</div>";

$stampTE = new StampTE($template);
$stampTE->regions->add($stampTE->getRegion()->setValue('象形字'));
asrt((string)$stampTE, $expect);

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

//now with glueAll, to do it all in one run...
$StampTE = new StampTE($template);
$redfish = $StampTE->get("redfish");
$yellowfish = $StampTE->get("yellowfish");
$StampTE->glueAll(array('bowl1'=>$redfish,'bowl2'=>$yellowfish));


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
asrt(strval($StampTE),'<!-- cut:hello ');

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
//echo $stampTE->getTodo();exit;
$todoItem = $stampTE->get('todo');
$todoItem->inject('todo','Make Coffee');
$stampTE->add($todoItem);
$expectation = '<ul>
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
$expectation = '<ul>
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

testpack('Test Dummy Slots');

$stampTE = new StampTE('<b><!-- slot:lorem -->ipsum<!-- /slot:lorem --></b>');
$stampTE->inject('lorem','Hello');
asrt(trim(strval($stampTE)),'<b>Hello</b>');

testpack('Magic API');

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
$flower = $StampTE->getFlower()->copy();
$flower2 = $flower->copy();
$flower->setType('lily');
$pond = $StampTE->pond->add($flower);
$flower2->setType('phlox');
$StampTE->flowers->add($flower2);
$expectation = "
	<garden>
		<water>
			<flower type=\"lily\"></flower>
		</water>
		<flower type=\"phlox\"></flower>
	</garden>
";

asrt(clean($StampTE),clean($expectation));

testpack('Test Introspection');
$gluePoints = $StampTE->getGluePoints();
asrt($gluePoints[0],'pond');
asrt($gluePoints[1],'flowers');
asrt($gluePoints[2],'selfflower');

$stampTE = new StampTE('<!-- slot:castle --><!-- /slot:castle -->');
$slots = $stampTE->getSlots();
asrt(isset($slots['castle']),true);

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

testpack('Test Translator');
$template = '
	<table>
	<!-- cut:fishBowl -->
		<bowl>
			<fish>#blub#</fish>
			<!-- cut:castle -->
				<castle>#diamond#</castle>
			<!-- /cut:castle -->
		</bowl>
	<!-- /cut:fishBowl -->
	</table>
';
$stampTE = new StampTE($template);
$dict = array(
	'Fish.Sound' => 'Blub Blub %s',
	'Diamond' => 'Wanda'
);
$stampTE->setTranslator(function($word,$params=array()){
	global $dict;
	return vsprintf($dict[$word],$params);
});
$bowl = $stampTE->getFishBowl();
asrt(clean($bowl),'<bowl><fish>#&blub#</fish></bowl>');
$bowl->sayBlub('Fish.Sound',array('says the fish'));
asrt(clean($bowl),'<bowl><fish>BlubBlubsaysthefish</fish></bowl>');
$castle = $bowl->getCastle();
$castle->sayDiamond('Diamond');
asrt(clean($castle),'<castle>Wanda</castle>');
$castle = $stampTE->get('fishBowl.castle');
$castle->setDiamond('Diamond');
asrt(clean($castle),'<castle>Diamond</castle>');
$castle = $stampTE->get('fishBowl.castle');
$castle->sayDiamond('Diamond');
asrt(clean($castle),'<castle>Wanda</castle>');

testpack('Test Factory');
class Pirahna extends StampTE {
	public function isHungry() {
		return 'Sure';
	}
}
$stampTE->setFactory(function($stamp,$id) {
	if ($id=='fishBowl') {
		return new Pirahna($stamp,$id);
	}
	else {
		return new StampTE($stamp,$id);
	}
});


$fish = $stampTE->getFishBowl();
asrt('Sure',$fish->isHungry());
$castle = $stampTE->get('fishBowl.castle');
asrt(null,$castle->isHungry());

class Castle extends StampTE { 
	public function isCastle() { return 'Yes'; }
}

$stampTE->setFactory(function($stamp,$id) {
	if ($id=='castle') {
		return new Castle($stamp,$id);
	}
	else {
		return new StampTE($stamp,$id);
	}
});

$castle = $stampTE->get('fishBowl.castle');
asrt('Yes',$castle->isCastle());
$fish = $stampTE->getFishBowl();
asrt(null,$fish->isHungry());
$castle = $fish->getCastle();
asrt('Yes',$castle->isCastle());

testpack('Test error handling');

try{ $stampTE->get('unknown'); fail(); }catch(StampTEException $e){ pass(); }
try{ $stampTE->getUnknown(); fail(); }catch(StampTEException $e){ pass(); }
try{ $stampTE->get('unknown.unknown'); fail(); }catch(StampTEException $e){ pass(); }
try{ $stampTE->get('fishBowl.unknown'); fail(); }catch(StampTEException $e){ pass(); }
try{ $stampTE->get('fishBowl.castle.unknown'); fail(); }catch(StampTEException $e){ pass(); }

testpack('Test strtolower issue with magic setter');

$template = '#helloWorld#';
$stampTE = new StampTE($template);
$stampTE->setHelloWorld('Hi');
asrt(strval($stampTE),'Hi');

$template = '#helloWorld#';
$stampTE = new StampTE($template);
$stampTE->setTranslator(function($a){ return $a;});
$stampTE->sayHelloWorld('Hi');
asrt(strval($stampTE),'Hi');

testpack('Security Test');
$t = '<input type="#type#" value="#value#">';
$s = new StampTE($t);
$s->setValue('"');
$s->setType('text');
asrt(trim($s),'<input type="text" value="&quot;">');

$t = '<input type="#type#" value="#value#">';
$s = new StampTE($t);
$s->setValue("'");
$s->setType('text');
asrt(trim($s),'<input type="text" value="&#039;">');

$t = '<input type="#type#" value="#value#">';
$s = new StampTE($t);
$s->setValue('#&type#');
$s->setType('text');
asrt(trim($s),'<input type="text" value="#&amp;type#">');

$t = '<input type="#type#" value="#value#">';
$s = new StampTE($t);
$s->setValue('#type#');
$s->setType('text');
asrt(trim($s),'<input type="text" value="#type#">');

$t = '<input type="#type#" value="#value#">';
$s = new StampTE($t);
$s->injectRaw('value','#&type#');
$s->setType('text');
asrt(trim($s),'<input type="text" value="text">');

$t = '<div>#slot#</div>';
$s = new StampTE($t);
$s->inject('slot','<b><!-- paste:hello --></b>');
$s->hello->add(new StampTE('<x>'));
asrt(trim($s),'<div>&lt;b&gt;&lt;!-- paste:hello --&gt;&lt;/b&gt;</div>');

$t = '<div>#slot#</div>';
$s = new StampTE($t);
$s->injectRaw('slot','<b><!-- paste:hello --></b>');
$s->hello->add(new StampTE('<x>'));
asrt(trim($s),'<div><b><x></b></div>');

testpack('Test optional slot marker');

$t = '<div>#slot?#</div>';
$s = new StampTE($t);
asrt(trim($s),'<div></div>');

$t = '<div>#slot?#</div><div>#slot2?#</div>';
$s = new StampTE($t);
asrt(trim($s),'<div></div><div></div>');

$t = '<div>#slot?#</div>';
$s = new StampTE($t);
$s->setSlot('Boo!');
asrt(trim($s),'<div>Boo!</div>');

$t = '<div>#slot?#</div><div>#slot?#</div>';
$s = new StampTE($t);
$s->setSlot('Boo!');
asrt(trim($s),'<div>Boo!</div><div>Boo!</div>');

$t = '<div>#slot?#</div>';
$s = new StampTE($t);
$s->setSlot('#Boo?#');
$s->setBoo('Baa');
asrt(trim($s),'<div>#Boo?#</div>');

$t = '<div>#slot?#</div>';
$s = new StampTE($t);
$s->setSlot('#&Boo?#');
$s->setBoo('Baa');
asrt(trim($s),'<div>#&amp;Boo?#</div>');

$t = '<div>#slot?#</div>';
$s = new StampTE($t);
$s->setSlot('#&Boo#');
$s->setBoo('Baa');
asrt(trim($s),'<div>#&amp;Boo#</div>');

testpack('Test backtick XSS filter for MSIE');

$t = '<b id="#slot#"></b>';
$s = new StampTE($t);
$s->setSlot('`');
asrt(trim($s),'<b id="&#96;"></b>');

$t = '<b id="#slot#"></b>';
$s = new StampTE($t);
$s->setSlot('``');
asrt(trim($s),'<b id="&#96;&#96;"></b>');

testpack('Test attribute injection');

$t = '<input type="checkbox" data-stampte="#state?#" />';
$s = new StampTE($t);
$s->injectAttr('state','checked');
asrt(trim($s), '<input type="checkbox" checked />');

$t = '<input type="checkbox" data-stampte="#state?#" />';
$s = new StampTE($t);
$s->injectAttr('state', '<');
asrt(trim($s), '<input type="checkbox" &lt; />');

$t = '<input type="checkbox" data-stampte="#state?#" />';
$s = new StampTE($t);
$x = $s->injectAttr('state', '<', true);
asrt(($x instanceof StampTE),true);
asrt(trim($s), '<input type="checkbox" < />');

$t = '<input type="checkbox" data-stampte="#state?#" />';
$s = new StampTE($t);
asrt(trim($s), '<input type="checkbox"  />');

$t = '<input type="checkbox" data-stampte="#state#" />';
$s = new StampTE($t);
asrt(trim($s), '<input type="checkbox" data-stampte="#&state#" />');

testpack('Attr-toggler');

$t = '<input type="checkbox" data-stampte="#checked?#" />';
$s = new StampTE($t);
$x = $s->attr('checked', true);
asrt(($x instanceof StampTE),true);
asrt(trim($s), '<input type="checkbox" checked />');

$t = '<input type="checkbox" data-stampte="#checked?#" />';
$s = new StampTE($t);
$x = $s->attr('checked', false);
asrt(($x instanceof StampTE),true);
asrt(trim($s), '<input type="checkbox"  />');

$t = '<input type="checkbox" data-stampte="#checked?#" />';
$s = new StampTE($t);
asrt(trim($s), '<input type="checkbox"  />');

testpack('Test escaping issue');

$s = new StampTE('<!-- cut:item -->#slot#<!-- /cut:item -->');
$s->add($s->getItem()->setSlot('/'));
asrt(trim($s),'/');


$s = new StampTE('<!-- cut:item -->#slot#<!-- /cut:item -->');
$s->add($s->getItem()->setSlot('$1'));
asrt(trim($s),'$1');

//Test cache & collect
testpack('Test Cache and Collect');

$s = new StampTE('
<div>
<!-- cut:textfield --><input type="text" /><!-- /cut:textfield -->
<!-- cut:button --><button>OK</button><!-- /cut:button -->
</div>
<form>
<!-- paste:form(button,textfield) -->
</form>
');

$cacheObject = $s->writeToCache('button|textfield')->getCache();

//make sure cache is used, remove elements from new template...
$s = new StampTE('
<div>
</div>
<form>
<!-- paste:form(button,textfield) -->
</form>
');

$s->loadIntoCache($cacheObject);
$items = $s->collect('button|textfield');
asrt(count($items), 2);

try {
	$s->collect('button|textfield|misc');
	fail();
} catch(Exception $e) {
	pass();
}

$button = $items[0];
$textField = $items[1];

$s->form->add($button->copy());
$s->form->add($textField->copy());

asrt(trim('<div>
</div>
<form><button>OK</button><input type="text" />
</form>
'),trim(strval($s)));


//Test template loader
testpack('Test Template Loader');

$path = '/tmp/dummy.tpl';
$template = '<div>
<!-- cut:message -->Dummy Template for StampTE Unit Test<!-- /cut:message -->
</div>';
file_put_contents($path, $template);
$s = StampTE::load($path);

asrt(strval($s->getMessage()), 'Dummy Template for StampTE Unit Test');
asrt($s->getString(), '<div><!-- paste:selfmessage -->
</div>');

try {
	StampTE::load('/non/existant/file/nowhere.nothing');
	fail();
} catch(Exception $e) {
	pass();
}

$report = xdebug_get_code_coverage();
$misses = 0;
$hits = 0;

$covLines = array();
foreach( $report as $file => $lines ) {
	$pi = pathinfo( $file );
	
	if ( strpos( $file, 'Stamp' ) === false ) continue;
	$covLines[] = '***** File:'.$file.' ******';
	
	$fileData = file_get_contents( $file );
	$fileLines = explode( "\n", $fileData );
	$i = 1;
	foreach( $fileLines as $covLine ) {
		if ( isset( $lines [$i] ) ) {
			if ( $lines[$i] === 1 ) {
				$covLines[] = '[ OK      ] '.$covLine;
				$hits ++;
			} else if ( $lines[$i] === -1 ){
				$covLines[] = '[ MISSED! ] '.$covLine;
				$misses ++;
			} else {
				$covLines[] = '[ -       ] '.$covLine;
			}
		} else {
			$covLines[] = '[ -       ] '.$covLine;
		}
		$i ++;
	}
}
$covFile = implode( "\n", $covLines );
@file_put_contents( 'coverage_log.txt', $covFile );

if ( $hits > 0 ) {
	$perc = ( $hits / ( $hits + $misses ) ) * 100;
} else {
	$perc = 0;
}

echo PHP_EOL;
echo 'Code Coverage: '.PHP_EOL;
echo 'Hits: '.$hits.PHP_EOL;
echo 'Misses: '.$misses.PHP_EOL;
echo 'Percentage: '.$perc.' %'.PHP_EOL;
exit( 0 );


echo PHP_EOL;
echo '--- DONE ---';
echo PHP_EOL;
exit(0);
