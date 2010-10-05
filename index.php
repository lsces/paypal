<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_paypal/index.php,v 1.7 2010/02/08 21:27:22 wjames5 Exp $
 *
 * Copyright (c) 2006 bitweaver.org
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
 *
 * @package paypal
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

include_once( PAYPAL_PKG_PATH.'Paypal.php' );

$gBitSystem->isPackageActive('paypal', TRUE);

if( !empty( $_REQUEST['content_id'] ) ) {
	$gPaypal = new Paypal( null, $_REQUEST['content_id'] );
	$gPaypal->load();
	$gPaypal->loadXrefList();
} else {
	$gPaypal = new Paypal();
}

// Comments engine!
if( $gBitSystem->isFeatureActive( 'feature_paypal_comments' ) ) {
	$comments_vars = Array('page');
	$comments_prefix_var='paypal note:';
	$comments_object_var='page';
	$commentsParentId = $gContent->mContentId;
	$comments_return_url = PAYPAL_PKG_URL.'index.php?content_id='.$gPaypal->mContentId;
	include_once( LIBERTY_PKG_PATH.'comments_inc.php' );
}

$gBitSmarty->assign_by_ref( 'paypalInfo', $gPaypal->mInfo );
if ( $gPaypal->isValid() ) {
	$gBitSystem->setBrowserTitle("Paypal List Item");
	$gBitSystem->display( 'bitpackage:paypal/show_paypal.tpl', NULL, array( 'display_mode' => 'display' ));
} else {
	header ("location: ".PAYPAL_PKG_URL."list.php");
	die;
}
?>