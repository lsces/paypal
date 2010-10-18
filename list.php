<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_paypal/list.php,v 1.5 2010/02/08 21:27:22 wjames5 Exp $
 *
 * Copyright (c) 2006 bitweaver.org
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 *
 * @package paypal
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

include_once( PAYPAL_PKG_PATH.'Paypal.php' );

$gBitSystem->verifyPackage( 'paypal' );

$gBitSystem->verifyPermission( 'p_paypal_view' );

$gContent = new Paypal( );

if( !empty( $_REQUEST["find_org"] ) ) {
	$_REQUEST["find_name"] = '';
	$_REQUEST["sort_mode"] = 'organisation_asc';
} else if( empty( $_REQUEST["sort_mode"] ) ) {
	$_REQUEST["sort_mode"] = 'title_asc';
	$_REQUEST["find_name"] = 'a,a';
}

//$paypal_type = $gContent->getPaypalsTypeList();
//$gBitSmarty->assign_by_ref('paypal_type', $paypal_type);
$listHash = $_REQUEST;
// Get a list of matching paypal entries
$listpaypals = $gContent->getList( $listHash );

$gBitSmarty->assign_by_ref( 'listpaypals', $listpaypals );
$gBitSmarty->assign_by_ref( 'listInfo', $listHash['listInfo'] );

$gBitSystem->setBrowserTitle("View Paypals List");
// Display the template
$gBitSystem->display( 'bitpackage:paypal/list.tpl', NULL, array( 'display_mode' => 'list' ));
?>