<?php
global $gBitSystem, $gBitSmarty;
$registerHash = array(
	'package_name' => 'paypal',
	'package_path' => dirname( __FILE__ ).'/',
	'homeable' => TRUE,
);
$gBitSystem->registerPackage( $registerHash );

if( $gBitSystem->isPackageActive( 'paypal' ) ) {
	$menuHash = array(
		'package_name'  => PAYPAL_PKG_NAME,
		'index_url'     => PAYPAL_PKG_URL.'index.php',
		'menu_template' => 'bitpackage:paypal/menu_paypal.tpl',
	);
	$gBitSystem->registerAppMenu( $menuHash );
}

?>
