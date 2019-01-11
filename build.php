#!/usr/bin/env php
<?php
echo "Welcome to Replica 2 Build Script for StampTE\n";
echo "Now building your stamps!\n";
echo "-------------------------------------------\n";
$code = '';
function addFile($file, $only = null) {
	global $code;
	echo 'Added ', $file , ' to package... ',PHP_EOL;
	$raw = file_get_contents($file);
	$raw = preg_replace('/namespace\s+([a-zA-Z0-9\\\;]+);/m', 'namespace $1 {', $raw);
	$raw .= '}';
	$code .= $raw;
}
define('DIR', 'StampTemplateEngine/');
addFile( DIR . 'StampTE.php' );
addFile( 'Tools/Dialog.php' );
$code = '<?php'.str_replace('<?php', '', $code);
/* embed default template */
$template = "<<<HTML\n".file_get_contents('Templates/dialog.html')."\nHTML\n";
$code = str_replace('\'@C_DEFAULT_TEMPLATE@\'', $template, $code);
$files = array( 'phpdialog.php' => $code );
foreach( $files as $file => $content ) {
	echo 'Okay, seems we have all the code.. now writing file: ', $file ,PHP_EOL;
	$b = file_put_contents($file, $content);
	echo 'Written: ',$b,' bytes.',PHP_EOL;
	if ($b > 0) {
		echo 'Done!' ,PHP_EOL;
	} else {
		echo 'Hm, something seems to have gone wrong... ',PHP_EOL;
	}
}



