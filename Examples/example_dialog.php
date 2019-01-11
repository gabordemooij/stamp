<?php

use PHPDialog\Dialog;
require '../phpdialog.php';
Dialog::show( 'Welcome', 'This is just a friendly welcome.', ['back to homepage'=>'/'], ['OK'=>'/confirm'], [
	[ 'name'=>'email_address', 'type'=>'email', 'placeholder'=>'your@email.net', 'autofocus'=>'autofocus', 'title'=>'please enter your email' ]] );
