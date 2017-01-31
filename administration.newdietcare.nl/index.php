<?php
//echo 'It is administration';
//die();

//if ( ! isset( $_COOKIE[ 'admin_sessionid' ] ) )
//	die();

date_default_timezone_set('Europe/Amsterdam');

define ( 'APP_FULL_PATH', getcwd () );
define ( 'MC_FULL_PATH', APP_FULL_PATH . '/application/library' );
set_include_path ( APP_FULL_PATH . '/application/library/' . PATH_SEPARATOR . get_include_path () );

// include autoloader
require_once ('loader.php');
require_once APP_FULL_PATH . '/application/library/dompdf/autoload.inc.php';

//Zend_Registry object
$registry = Zend_Registry::getInstance ();

//Setup application config
$config = new Zend_Config_Xml ( APP_FULL_PATH . '/application/config/.config' );
$registry->set ( 'config', $config );

//Setup database
$db = Zend_Db::factory ( $config->database->adapter, $config->database->params->toArray () );
$registry->set ( 'db', $db );
Zend_Db_Table::setDefaultAdapter ( $db );
$db->query ( 'SET CHARACTER SET utf8' );

/*
//Setup Zend_Mail
$tr = new Zend_Mail_Transport_Smtp ( 'localhost' );
Zend_Mail::setDefaultTransport ( $tr );
*/

/*
$mail_config = array('auth' => 'login', 'username' => 'noreply@newdietcare.nl', 'password' => 'Rti6rx');
$tr = new Zend_Mail_Transport_Smtp ( 'mail.dualdev.com' );
Zend_Mail::setDefaultTransport ( $tr, $mail_config );
*/

//Setup view
$view = new Zend_View ( );
$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer ( $view );
$viewRenderer->setViewBasePathSpec ( APP_FULL_PATH . '/application/:module/views' );
$viewRenderer->setViewScriptPathSpec ( ':controller/:action.:suffix' );
$viewRenderer->setViewSuffix ( 'phtml' );
Zend_Controller_Action_HelperBroker::addHelper ( $viewRenderer );

//Setup layout
$layout = Zend_Layout::startMvc ();
$layout->setContentKey ( 'content' );

//Setup front controller
$controller = Zend_Controller_Front::getInstance ();
$controller->setParam ( 'prefixDefaultModule', true );
$controller->setControllerDirectory ( array ('default' => APP_FULL_PATH . '/application/default/controllers' ) );
$controller->setBaseUrl ( $config->application->baseUrl );

//TODO Turn it off in the final version!!! 
$controller->throwExceptions ( false );
$controller->registerPlugin ( new Zend_Controller_Plugin_ErrorHandler ( ) );

//run
$controller->dispatch ();


