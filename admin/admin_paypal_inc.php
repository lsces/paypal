<?php

// $Header: /cvsroot/bitweaver/_bit_paypal/admin/admin_paypal_inc.php,v 1.3 2009/10/01 14:16:59 wjames5 Exp $

// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.

include_once( PAYPAL_PKG_PATH.'Paypal.php' );

$formPaypalListFeatures = array(
	"paypal_list_id" => array(
		'label' => 'Paypal Number',
	),
	"paypal_list_forename" => array(
		'label' => 'Forname',
	),
	"paypal_list_surname" => array(
		'label' => 'Surname',
	),
	"paypal_list_home_phone" => array(
		'label' => 'Home Phone',
	),
	"paypal_list_mobile_phone" => array(
		'label' => 'Mobile Phone',
	),
	"paypal_list_email" => array(
		'label' => 'eMail Address',
		'help' => 'Primary paypal email address - additional paypal details can be found in the full record',
	),
	"paypal_list_edit_details" => array(
		'label' => 'Creation and editing details',
		'help' => 'Enable the record modification data in the paypal list. Useful to allow checking when deatils were last changed.',
	),
	"paypal_list_last_modified" => array(
		'label' => 'Last Modified',
		'help' => 'Can be selected to enable filter button, without enabling the details section to allow fast checking of the last paypal records that have been modified.',
	),
);
$gBitSmarty->assign( 'formPaypalListFeatures',$formPaypalListFeatures );

if (isset($_REQUEST["paypallistfeatures"])) {
	
	foreach( $formPaypalListFeatures as $item => $data ) {
		simple_set_toggle( $item, PAYPAL_PKG_NAME );
	}
}

?>
