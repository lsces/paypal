<?php
$tables = array(

'paypal' => "
  content_id I8 PRIMARY,
  txn_id C(32),
  txn_date D,
  txn_name C(100),
  txn_type c(48),
  txn_status I8,
  currency C(4),
  gross F,
  fee F,
  net F,
  post_pack F,
  post_insure F,
  vat F,
  balance F,
  from_email_address C(100),
  to_email_address C(100),
  email C(256),
  reference_txn_id C(32)
  invoice_no C(20)
  payment_type C(32),
  payment_type C(32),
  cparty_status C(32),
  cust_no C(32),
  cust_status C(32),
  addr_status C(32),
  item_title C(250),
  item_id C(64),
  note C(40),
  memo X
",

'paypal_txn_type' => "
  paypal_txn_type_id I4 PRIMARY,
  type_name	C(64)
",

'paypal_line_item' => "
  paypal_txn_id I8,
  txn_id C(32),
  item_title C(250),
  item_id C(64),
  option_1_name C(64),
  option_1_value C(64),
  option_2_name C(64),
  option_2_value C(64),
  item_qty C(16)
  ",

'paypal_address' => "
  paypal_addr_id C(32) PRIMARY,
  trans_name C(100),
  cust_no C(32),
  usn I8,
  uprn I8,
  organisation C(100),
  phone C(32),
  number C(80),
  street C(250),
  locality C(250),
  town C(80),
  county C(80),
  postcode C(10),
  country C(80),
  zone_id I4,
  country_id I4,
  last_update_date T DEFAULT CURRENT_TIMESTAMP
",

);

global $gBitInstaller;

foreach( array_keys( $tables ) AS $tableName ) {
	$gBitInstaller->registerSchemaTable( PAYPAL_PKG_NAME, $tableName, $tables[$tableName] );
}

$gBitInstaller->registerPackageInfo( PAYPAL_PKG_NAME, array(
	'description' => "Base Paypal management package with paypal item list and address books",
	'license' => '<a href="http://www.gnu.org/licenses/licenses.html#LGPL">LGPL</a>',
) );

// ### Indexes
$indices = array (
	'paypal_transaction_id_idx' => array( 'table' => 'paypal', 'cols' => 'txn_id', 'opts' => NULL ),
);
$gBitInstaller->registerSchemaIndexes( PAYPAL_PKG_NAME, $indices );

// ### Sequences
$sequences = array (
	'paypal_cust_id_seq' => array( 'start' => 1 ),
	'paypal_addr_id_seq' => array( 'start' => 1 ),
	'paypal_id_seq' => array( 'start' => 20 ),
);
$gBitInstaller->registerSchemaSequences( PAYPAL_PKG_NAME, $sequences );

// ### Defaults

// ### Default User Permissions
$gBitInstaller->registerUserPermissions( PAYPAL_PKG_NAME, array(
	array('p_paypal_view', 'Can browse the Paypal List', 'basic', PAYPAL_PKG_NAME),
	array('p_paypal_update', 'Can update the Paypal List content', 'registered', PAYPAL_PKG_NAME),
	array('p_paypal_create', 'Can create a new Paypal List entry', 'registered', PAYPAL_PKG_NAME),
	array('p_paypal_admin', 'Can admin Paypal List', 'admin', PAYPAL_PKG_NAME),
	array('p_paypal_expunge', 'Can remove a Paypal entry', 'editors', PAYPAL_PKG_NAME)
) );

// ### Default Preferences
$gBitInstaller->registerPreferences( PAYPAL_PKG_NAME, array(
	array( PAYPAL_PKG_NAME, 'paypal_default_ordering','title_desc'),
	array( PAYPAL_PKG_NAME, 'paypal_list_created','y'),
	array( PAYPAL_PKG_NAME, 'paypal_list_lastmodif','y'),
	array( PAYPAL_PKG_NAME, 'paypal_list_notes','y'),
	array( PAYPAL_PKG_NAME, 'paypal_list_title','y'),
	array( PAYPAL_PKG_NAME, 'paypal_list_user','y'),
) );

$gBitInstaller->registerSchemaDefault( PAYPAL_PKG_NAME, array(
"INSERT INTO `".BIT_DB_PREFIX."paypal_txn_type` VALUES (0, 'Payment Received')",
"INSERT INTO `".BIT_DB_PREFIX."paypal_txn_type` VALUES (1, 'Shopping Cart Payment Received')",
"INSERT INTO `".BIT_DB_PREFIX."paypal_txn_type` VALUES (2, 'Shopping Cart Item')",
"INSERT INTO `".BIT_DB_PREFIX."paypal_txn_type` VALUES (3, 'Order')",
"INSERT INTO `".BIT_DB_PREFIX."paypal_txn_type` VALUES (10, 'Payment Sent')",
"INSERT INTO `".BIT_DB_PREFIX."paypal_txn_type` VALUES (11, 'eBay Payment Sent')",
"INSERT INTO `".BIT_DB_PREFIX."paypal_txn_type` VALUES (12, 'PayPal Express Checkout Payment Sent')",
"INSERT INTO `".BIT_DB_PREFIX."paypal_txn_type` VALUES (13, 'Pre-approved Payment Sent')",
"INSERT INTO `".BIT_DB_PREFIX."paypal_txn_type` VALUES (14, 'Web Accept Payment Sent')",

) );


?>
