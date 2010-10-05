<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_paypal/display_paypal.php,v 1.7 2010/02/08 21:27:22 wjames5 Exp $
 *
 * Copyright (c) 2006 bitweaver.org
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
 *
 * @package nlpg
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

include_once( PAYPAL_PKG_PATH.'Paypal.php' );

$gBitSystem->verifyPackage( 'paypal' );

$gBitSystem->verifyPermission( 'p_paypal_view' );

if( !empty( $_REQUEST['content_id'] ) ) {
	$gPaypal = new Paypal( null, $_REQUEST['content_id'] );
	$gPaypal->load();
	$gPaypal->loadXrefList();
} else {
	$gPaypal = new Paypal();
}

$gBitSmarty->assign_by_ref( 'paypalInfo', $gPaypal->mInfo );
if ( $gPaypal->isValid() ) {
	$gBitSystem->setBrowserTitle("Client Activity Item");
	$gBitSystem->display( 'bitpackage:paypal/show_paypal.tpl');
} else {
//	header ("location: ".PAYPAL_PKG_URL."index.php");
//	die;
}
?>
