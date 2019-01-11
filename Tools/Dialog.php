<?php

namespace PHPDialog;

use StampTemplateEngine\StampTE;

/**
 * Dialog class.
 * 
 * PHPDialog is a PHP library to display dialog-like HTML documents
 * with just one function call:
 *
 * Usage:
 * Dialog::show( 'Hello', 'World' );
 *
 */
class Dialog {

	private static $template        = '@C_DEFAULT_TEMPLATE@';
	private static $language        = 'en';
	private static $backgroundColor = '#ccc';
	private static $foregroundColor = '#fff';
	private static $textColor       = '#444';
	private static $font            = '18px sans-serif';
	private static $headerFont      = '40px Arial';
	private static $css             = '';

	/**
	 * Sets the template to be used by the Dialog class
	 * to generate dialog-like HTML documents.
	 * If you use the all-in-one file, by default the
	 * 'onboard' template will be used.
	 *
	 * @param string $templateParam template (full HTML StampTE template)
	 *
	 * @return void
	 */
	public static function setTemplate( $templateParam ) {
		self::$template = $templateParam;
	}

	/**
	 * Sets the language to be used by the Dialog class
	 * to generate dialog-like HTML documents.
	 * The default is: 'en'.
	 *
	 * @param string $languageParam language code
	 *
	 * @return void
	 */
	public static function setLanguage( $languageParam ) {
		self::$language = $languageParam;
	}

	/**
	 * Sets the colors to be used by the Dialog class
	 * to generate dialog-like HTML documents.
	 * The defaults are: #ccc #fff #444.
	 *
	 * Note:
	 * RGB(A) notations are also allowed.
	 *
	 * @param string $backgroundColor color code
	 * @param string $foregroundColor color code
	 * @param string $textColor       color code
	 *
	 * @return void
	 */
	public static function colors( $backgroundColor, $foregroundColor = '#fff', $textColor = '#000' ) {
		self::$backgroundColor = $backgroundColor;
		self::$foregroundColor = $foregroundColor;
		self::$textColor       = $textColor;
	}

	/**
	 * Sets the theme to be used by the Dialog class
	 * to generate dialog-like HTML documents.
	 * The theme consists of the text font and the 
	 * header/title font.
	 *
	 * Defaults:
	 * '18px sans-serif' for text
	 * '40px Arial' for header
	 *
	 * @param string $font       font code
	 * @param string $headerFont font code
	 *
	 * @return void
	 */
	public static function theme( $font, $headerFont = null ) {
		if ( !is_null( $font ) ) {
			self::$font = $font;
		}
		if ( !is_null( $headerFont ) ) {
			self::$headerFont = $headerFont;
		}
	}

	/**
	 * Sets additional CSS rules to be incorporated in the
	 * resulting HTML document.
	 *
	 * @param string $css custom css code
	 *
	 * @return void
	 */
	public static function css( $css ) {
		self::$css = $css;
	}

	/**
	 * Renders a dialog-like document.
	 *
	 * Alert:
	 * Dialog::render( 'Notice', 'Mind the gap!' );
	 *
	 * Confirm:
	 * Dialog::render( 'Confirm', 'Do you agree', ['no'=>'/'], ['yes'=>'/proceed'] );
	 *
	 * Prompt:
	 * Dialog::render( 'Question', 'What is your name?', [], ['register'=>'/register'], ['name'=>'myname', 'type'=>'text'] );
	 *
	 * 'Promptfirm':
	 * Dialog::render( 'Question', 'What is your name?', ['wont tell'=>'/'], ['register'=>'/register'], ['name'=>'myname', 'type'=>'text'] );
	 *
	 * Custom:
	 * Dialog::render( some other combination );
	 *
	 * Note:
	 * Although multiple submit buttons are allowed, only the action of
	 * the last button will be used.
	 *
	 * Note:
	 * For every field element, at least the properties name and type
	 * need to be specified.
	 *
	 * @param string $title   title of the dialog box
	 * @param string $message message to display in the box
	 * @param array  $links   a series of links to display (format is: href => label)
	 * @param array  $buttons a series of submit buttons (format is: name => action)
	 * @param array  $fields  additional input fields (format is: [ property => value ])
	 *
	 * @return void
	 */
	public static function render( $title, $message, $links, $buttons=[], $fields=[] ) {
		$dialog = new StampTE( self::$template );
		$dialog->setTitle( $title );
		$dialog->setMessage( $message );
		$dialog->setLanguage( self::$language );
		$dialog->setBackgroundColor( self::$backgroundColor );
		$dialog->setForegroundColor( self::$foregroundColor );
		$dialog->setTextColor( self::$textColor );
		$dialog->setFont(self::$font);
		$dialog->setHeaderFont(self::$headerFont);
		$dialog->setCss(self::$css);
		foreach( $buttons as $name => $action ) {
			$dialog->setMethod('POST');
			$dialog->setAction($action);
			$button = $dialog->getButton();
			$button->setLabel($name);
			$button->setName($name);
			$dialog->add( $button );
		}
		foreach( $links as $label => $href ) {
			$link = $dialog->getLink();
			$link->setHref( $href );
			$link->setLabel( $label );
			$dialog->add( $link );
		}
		foreach( $fields as $definition ) {
			$field = $dialog->getField();
			foreach( $definition as $key => $value ) {
				$field->inject( $key, $value );
			}
			$dialog->add( $field );
		}
		return $dialog;
	}

	/**
	 * Renders and displays a dialog box.
	 * For more details see Dialog::render().
	 *
	 * @see Dialog::render()
	 */
	public static function show( $title, $message, $links, $buttons=[], $fields=[] ) {
		echo strval( self::render( $title, $message, $links, $buttons, $fields ) );
	}
}
