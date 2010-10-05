<?php
/*
 * Created on 5 Jan 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

// Initialization
require_once( '../kernel/setup_inc.php' );
require_once( PAYPAL_PKG_PATH.'Paypal.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'paypal' );

// Now check permissions to access this page
$gBitSystem->verifyPermission('p_paypal_admin' );

$paypals = new Paypal();
set_time_limit(0);

if( empty( $_REQUEST["update"] ) ) {
	$paypals->PaypalExpunge();
	$update = 0;
} else {
	$update = $_REQUEST["update"];
}

$row = 0;

$handle = fopen("data/PaypalPayments08-09.csv", "r");
if ( $handle == FALSE) {
	$row = -999;
} else {
	while (($data = fgetcsv($handle, 800, "\t")) !== FALSE) {
    	if ( $row ) {
    		$paypals->PaypalRecordLoad( $data );
    	}
    	$row++;
	}
	fclose($handle);
}

$gBitSmarty->assign( 'paypal', $row );

$gBitSystem->display( 'bitpackage:paypal/load_paypal_cvs.tpl', tra( 'Load results: ' ) );
?>
