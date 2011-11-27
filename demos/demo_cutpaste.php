<?php

set_include_path ('/var/www/html/lab/stamp');
require('Stamp.php');

$template = '
	<div id="appwindow">
		<div id="toolbar">
			<!-- cut:toolbarButton -->
			<a class="toolbutton">
				<img src="#icon#" title="#title#" alt="#alt#"/>
				<span class="caption">#caption#</span>
			</a>
			<!-- /cut:toolbarButton -->
			<!-- paste:buttons -->
		</div>
	</div>
';

$window = new Stamp( $template );

//Button Definitions from controller
$buttons = array('Cut'=>'Cuts the currently selected text.',
	'Copy'=>'Copies the currently selected text.',
	'Paste'=>'Pastes contents of clipboard');

//Insert buttons in template without polluting HTML
foreach($buttons as $key=>$text) {
	$button = $window->fetch('toolbarButton');
	$button->put('icon',$key.'.png')
	->put('title',$text)
	->put('alt',$key)->put('caption',$key);
	$window->pasteIn('buttons',$button);
}

echo $window;
