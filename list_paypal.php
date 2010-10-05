<?php
// $Header: /cvsroot/bitweaver/_bit_paypal/list_paypals.php,v 1.4 2010/02/08 21:27:22 wjames5 Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
// Initialization
require_once( '../kernel/setup_inc.php' );
require_once( PAYPAL_PKG_PATH.'Paypal.php' );

$gBitSystem->isPackageActive('paypal', TRUE);

// Now check permissions to access this page
$gBitSystem->verifyPermission('p_read_paypal');

$paypals = new Paypals( 0 );

if ( empty( $_REQUEST["sort_mode"] ) ) {
	$sort_mode = 'title_asc';
}

// Get a list of Paypals 
$paypals->getList( $_REQUEST );

$smarty->assign_by_ref('listInfo', $_REQUEST['listInfo']);
$smarty->assign_by_ref('list', $paypals);


// Display the template
$gBitSystem->display( 'bitpackage:paypal/list_paypals.tpl', NULL, array( 'display_mode' => 'list' ));
?>
