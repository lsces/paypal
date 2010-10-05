<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_paypal/Paypal.php,v 1.13 2010/04/18 02:27:23 wjames5 Exp $
 *
 * Copyright ( c ) 2006 bitweaver.org
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 *
 * @package paypal
 */

/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyContent.php' );		// Paypal base class

define( 'PAYPAL_CONTENT_TYPE_GUID', 'paypal_trans' );

/**
 * @package paypal
 */
class Paypal extends LibertyContent {
	var $mPaypalTrans;
	var $mParentId;

	/**
	 * Constructor 
	 * 
	 * Build a Paypal object based on LibertyContent
	 * @param integer Paypal Id identifer
	 * @param integer Base content_id identifier 
	 */
	function Paypal( $pPaypalId = NULL, $pContentId = NULL ) {
		LibertyContent::LibertyContent();
		$this->registerContentType( PAYPAL_CONTENT_TYPE_GUID, array(
				'content_type_guid' => PAYPAL_CONTENT_TYPE_GUID,
				'content_name' => 'Paypal Entry',
				'handler_class' => 'Paypal',
				'handler_package' => 'paypal',
				'handler_file' => 'Paypal.php',
				'maintainer_url' => 'http://lsces.co.uk'
			) );
		$this->mPaypalTrans = $pPaypalId;
		$this->mContentId = (int)$pContentId;
		$this->mContentTypeGuid = PAYPAL_CONTENT_TYPE_GUID;
				// Permission setup
		$this->mViewContentPerm  = 'p_paypal_view';
		$this->mCreateContentPerm  = 'p_paypal_create';
		$this->mUpdateContentPerm  = 'p_paypal_update';
		$this->mAdminContentPerm = 'p_paypal_admin';
		
	}

	/**
	 * Load a Paypal content Item
	 *
	 * (Describe Paypal object here )
	 */
	function load($pContentId = NULL) {
		if ( $pContentId ) $this->mContentId = (int)$pContentId;
		if( $this->verifyId( $this->mContentId ) ) {
 			$query = "select ci.*, a.*, n.*, p.*, lc.*,
				uue.`login` AS modifier_user, uue.`real_name` AS modifier_real_name,
				uuc.`login` AS creator_user, uuc.`real_name` AS creator_real_name
				FROM `".BIT_DB_PREFIX."paypal` ci
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = ci.`content_id` )
				LEFT JOIN `".BIT_DB_PREFIX."users_users` uue ON (uue.`user_id` = lc.`modifier_user_id`)
				LEFT JOIN `".BIT_DB_PREFIX."users_users` uuc ON (uuc.`user_id` = lc.`user_id`)
				LEFT JOIN `".BIT_DB_PREFIX."paypal_address` a ON a.usn = ci.usn
				LEFT JOIN `".BIT_DB_PREFIX."nlpg_blpu` n ON n.`uprn` = ci.`nlpg`
				LEFT JOIN `".BIT_DB_PREFIX."nlpg_lpi` p ON p.`uprn` = ci.`nlpg` AND p.`language` = 'ENG' AND p.`logical_status` = 1
				WHERE ci.`content_id`=?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );

			if ( $result && $result->numRows() ) {
				$this->mInfo = $result->fields;
				$this->mContentId = (int)$result->fields['content_id'];
				$this->mPaypalId = (int)$result->fields['usn'];
				$this->mParentId = (int)$result->fields['usn'];
				$this->mPaypalName = $result->fields['title'];
				$this->mInfo['creator'] = (isset( $result->fields['creator_real_name'] ) ? $result->fields['creator_real_name'] : $result->fields['creator_user'] );
				$this->mInfo['editor'] = (isset( $result->fields['modifier_real_name'] ) ? $result->fields['modifier_real_name'] : $result->fields['modifier_user'] );
				$this->mInfo['display_url'] = $this->getDisplayUrl();
				$os1 = new OSRef($this->mInfo['x_coordinate'], $this->mInfo['y_coordinate']);
				$ll1 = $os1->toLatLng();
				$this->mInfo['prop_lat'] = $ll1->lat;
				$this->mInfo['prop_lng'] = $ll1->lng;
			}
		}
		LibertyContent::load();
		return;
	}

	/**
	* verify, clean up and prepare data to be stored
	* @param $pParamHash all information that is being stored. will update $pParamHash by reference with fixed array of itmes
	* @return bool TRUE on success, FALSE if store could not occur. If FALSE, $this->mErrors will have reason why
	* @access private
	**/
	function verify( &$pParamHash ) {
		// make sure we're all loaded up if everything is valid
		if( $this->isValid() && empty( $this->mInfo ) ) {
			$this->load( TRUE );
		}

		// It is possible a derived class set this to something different
		if( empty( $pParamHash['content_type_guid'] ) ) {
			$pParamHash['content_type_guid'] = $this->mContentTypeGuid;
		}

		if( !empty( $this->mContentId ) ) {
			$pParamHash['content_id'] = $this->mContentId;
		} else {
			unset( $pParamHash['content_id'] );
		}

		if ( empty( $pParamHash['parent_id'] ) )
			$pParamHash['parent_id'] = $this->mContentId;
			
		// content store
		// check for name issues, first truncate length if too long
		if( empty( $pParamHash['surname'] ) || empty( $pParamHash['forename'] ) )  {
			$this->mErrors['names'] = 'You must enter a forename and surname for this paypal.';
		} else {
			$pParamHash['title'] = substr( $pParamHash['prefix'].' '.$pParamHash['forename'].' '.$pParamHash['surname'].' '.$pParamHash['suffix'], 0, 160 );
			$pParamHash['content_store']['title'] = $pParamHash['title'];
		}	

		// Secondary store entries
		$pParamHash['paypal_store']['prefix'] = $pParamHash['prefix'];
		$pParamHash['paypal_store']['forename'] = $pParamHash['forename'];
		$pParamHash['paypal_store']['surname'] = $pParamHash['surname'];
		$pParamHash['paypal_store']['suffix'] = $pParamHash['suffix'];
		$pParamHash['paypal_store']['organisation'] = $pParamHash['organisation'];

		if ( !empty( $pParamHash['nino'] ) ) $pParamHash['paypal_store']['nino'] = $pParamHash['nino'];
		if ( !empty( $pParamHash['dob'] ) ) $pParamHash['paypal_store']['dob'] = $pParamHash['dob'];
		if ( !empty( $pParamHash['eighteenth'] ) ) $pParamHash['paypal_store']['eighteenth'] = $pParamHash['eighteenth'];
		if ( !empty( $pParamHash['dod'] ) ) $pParamHash['paypal_store']['dod'] = $pParamHash['dod'];

		return( count( $this->mErrors ) == 0 );
	}

	/**
	* Store paypal data
	* @param $pParamHash contains all data to store the paypal
	* @param $pParamHash[title] title of the new paypal
	* @param $pParamHash[edit] description of the paypal
	* @return bool TRUE on success, FALSE if store could not occur. If FALSE, $this->mErrors will have reason why
	**/
	function store( &$pParamHash ) {
		if( $this->verify( $pParamHash ) ) {
			// Start a transaction wrapping the whole insert into liberty 

			$this->mDb->StartTrans();
			if ( LibertyContent::store( $pParamHash ) ) {
				$table = BIT_DB_PREFIX."paypal";

				// mContentId will not be set until the secondary data has committed 
				if( $this->verifyId( $this->mContentId ) ) {
					if( !empty( $pParamHash['paypal_store'] ) ) {
						$result = $this->mDb->associateUpdate( $table, $pParamHash['paypal_store'], array( "content_id" => $this->mContentId ) );
					}
				} else {
					$pParamHash['paypal_store']['content_id'] = $pParamHash['content_id'];
					$result = $this->mDb->associateInsert( $table, $pParamHash['paypal_store'] );
				}
				// load before completing transaction as firebird isolates results
				$this->load();
				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
				$this->mErrors['store'] = 'Failed to store this paypal.';
			}
		}
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * Delete content object and all related records
	 */
	function expunge()
	{
		$ret = FALSE;
		if ($this->isValid() ) {
			$this->mDb->StartTrans();
			$query = "DELETE FROM `".BIT_DB_PREFIX."paypal` WHERE `content_id` = ?";
			$result = $this->mDb->query($query, array($this->mContentId ) );
			$query = "DELETE FROM `".BIT_DB_PREFIX."paypal_type_map` WHERE `content_id` = ?";
			$result = $this->mDb->query($query, array($this->mContentId ) );
			if (LibertyContent::expunge() ) {
			$ret = TRUE;
				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
			}
		}
		return $ret;
	}
    
	/**
	 * Returns Request_URI to a Paypal content object
	 *
	 * @param string name of
	 * @param array different possibilities depending on derived class
	 * @return string the link to display the page.
	 */
	function getDisplayUrl( $pContentId=NULL ) {
		global $gBitSystem;
		if( empty( $pContentId ) ) {
			$pContentId = $this->mContentId;
		}

		return PAYPAL_PKG_URL.'index.php?content_id='.$pContentId;
	}

	/**
	 * Returns HTML link to display a Paypal object
	 * 
	 * @param string Not used ( generated locally )
	 * @param array mInfo style array of content information
	 * @return the link to display the page.
	 */
	function getDisplayLink( $pText, $aux ) {
		if ( $this->mContentId != $aux['content_id'] ) $this->load($aux['content_id']);

		if (empty($this->mInfo['content_id']) ) {
			$ret = '<a href="'.$this->getDisplayUrl($aux['content_id']).'">'.$aux['title'].'</a>';
		} else {
			$ret = '<a href="'.$this->getDisplayUrl($aux['content_id']).'">'."Paypal - ".$this->mInfo['title'].'</a>';
		}
		return $ret;
	}

	/**
	 * Returns title of an Paypal object
	 *
	 * @param array mInfo style array of content information
	 * @return string Text for the title description
	 */
	function getTitle( $pHash = NULL ) {
		$ret = NULL;
		if( empty( $pHash ) ) {
			$pHash = &$this->mInfo;
		} else {
			if ( $this->mContentId != $pHash['content_id'] ) {
				$this->load($pHash['content_id']);
				$pHash = &$this->mInfo;
			}
		}

		if( !empty( $pHash['title'] ) ) {
			$ret = "Paypal - ".$this->mInfo['title'];
		} elseif( !empty( $pHash['content_name'] ) ) {
			$ret = $pHash['content_name'];
		}
		return $ret;
	}

	/**
	 * Returns list of contract entries
	 *
	 * @param integer 
	 * @param integer 
	 * @param integer 
	 * @return string Text for the title description
	 */
	function getList( &$pListHash ) {
		LibertyContent::prepGetList( $pListHash );
		
		$whereSql = $joinSql = $selectSql = '';
		$bindVars = array();
		array_push( $bindVars, $this->mContentTypeGuid );
		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

		if ( isset($pListHash['find']) ) {
			$findesc = '%' . strtoupper( $pListHash['find'] ) . '%';
			$whereSql .= " AND (UPPER(con.`SURNAME`) like ? or UPPER(con.`FORENAME`) like ?) ";
			array_push( $bindVars, $findesc );
		}

		if ( isset($pListHash['add_sql']) ) {
			$whereSql .= " AND $add_sql ";
		}

		$query = "SELECT con.*, lc.*, 
				uue.`login` AS modifier_user, uue.`real_name` AS modifier_real_name,
				uuc.`login` AS creator_user, uuc.`real_name` AS creator_real_name $selectSql
				FROM `".BIT_DB_PREFIX."paypal` ci 
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = ci.`content_id` )
				LEFT JOIN `".BIT_DB_PREFIX."users_users` uue ON (uue.`user_id` = lc.`modifier_user_id`)
				LEFT JOIN `".BIT_DB_PREFIX."users_users` uuc ON (uuc.`user_id` = lc.`user_id`)
				$joinSql
				WHERE lc.`content_type_guid`=? $whereSql  
				order by ".$this->mDb->convertSortmode( $pListHash['sort_mode'] );
		$query_cant = "SELECT COUNT(lc.`content_id`) FROM `".BIT_DB_PREFIX."paypal` ci
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = ci.`content_id` )
				$joinSql
				WHERE lc.`content_type_guid`=? $whereSql";

		$ret = array();
		$this->mDb->StartTrans();
		$result = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] );
		$cant = $this->mDb->getOne( $query_cant, $bindVars );
		$this->mDb->CompleteTrans();

		while ($res = $result->fetchRow()) {
			$res['paypal_url'] = $this->getDisplayUrl( $res['content_id'] );
			$ret[] = $res;
		}

		$pListHash['cant'] = $cant;
		LibertyContent::postGetList( $pListHash );
		return $ret;
	}

	/**
	* Returns titles of the paypal type table
	*
	* @return array List of paypal type names from the paypal mamanger in alphabetical order
	*/
	function getPaypalTypeList() {
		$query = "SELECT `type_name` FROM `paypal_type`
				  ORDER BY `type_name`";
		$result = $this->mDb->query($query);
		$ret = array();

		while ($res = $result->fetchRow()) {
			$ret[] = trim($res["type_name"]);
		}
		return $ret;
	}

	/**
	 * PaypalBaseRecordLoad( $data ); 
	 * Paypal csv log file import 
	 */
	function PaypalBaseRecordLoad( $data ) {
		$table = BIT_DB_PREFIX."paypal";
		$atable = BIT_DB_PREFIX."paypal_address";

		
		$pDataHash['paypal_store']['txn_date'] = $data[0];
		// Todo - handle timezone offset !!! 
		$pDataHash['paypal_store']['txn_name'] = $data[3];
		$pDataHash['paypal_store']['txn_type'] = $data[4];
		$pDataHash['paypal_store']['txn_status'] = $data[5];
		$pDataHash['paypal_store']['currency'] = $data[6];
		$pDataHash['paypal_store']['gross'] = $data[7];
		if ( $data[8] == '...' ) {
			$pDataHash['paypal_store']['fee'] = 0.0;
			$pDataHash['paypal_store']['net'] = 0.0;
		} else {
			$pDataHash['paypal_store']['fee'] = $data[8];
			$pDataHash['paypal_store']['net'] = $data[9];
		}
		$pDataHash['paypal_store']['from_email_address'] = $data[10];
		$pDataHash['paypal_store']['to_email_address'] = $data[11];
		$pDataHash['paypal_store']['txn_id'] = $data[12];
		$pDataHash['paypal_store']['payment_type'] = $data[13];
		$pDataHash['paypal_store']['cparty_status'] = $data[14];
		$pDataHash['paypal_store']['addr_status'] = $data[15];
		$pDataHash['paypal_store']['item_title'] = $data[16];
		$pDataHash['paypal_store']['item_id'] = $data[17];
		$pDataHash['paypal_store']['post_pack'] = $data[18];
		$pDataHash['paypal_store']['post_insure'] = $data[19];
		$pDataHash['paypal_store']['vat'] = $data[20];
		$pDataHash['paypal_store']['reference_txn_id'] = $data[25];
		$pDataHash['paypal_store']['cust_no'] = $data[26];
		$pDataHash['paypal_store']['vat'] = $data[27];
		$pDataHash['address_store']['txn_name'] = $data[3];
		$pDataHash['address_store']['cust_no'] = $data[26];
		$pDataHash['address_store']['number'] = substr($data[30], 1, 10 );
		$pDataHash['address_store']['street'] = $data[30];
		$pDataHash['address_store']['locality'] = $data[31];
		$pDataHash['address_store']['town'] = $data[32];
		$pDataHash['address_store']['county'] = $data[33];
		$pDataHash['address_store']['postcode'] = $data[34];
		$pDataHash['address_store']['country'] = $data[35];
		$pDataHash['address_store']['phone'] = $data[36];

		$query_cant = "SELECT COUNT(pp.`txn_id`) FROM `".BIT_DB_PREFIX."paypal` pp
				WHERE pp.`txn_id`=?";
		$cant = $this->mDb->getOne( $query_cant, Array( $data[12] ) );
		
		if ( $cant > 0 ) {
		  $query_cant = "SELECT * FROM `".BIT_DB_PREFIX."paypal` pp
				WHERE pp.`txn_id`=?";
		  $current = $this->mDb->query( $query_cant, Array( 9000000000 + $data[0] ) );
		  $cfields = $current->fetchRow();
		  $save = false;
// TODO - Handle duplicate transaction ID from paypal 
		} else {
			$this->mDb->StartTrans();
			$this->mContentId = 0;
			$pDataHash['content_id'] = 0;
			if ( LibertyContent::store( $pDataHash ) ) {
				$pDataHash['paypal_store']['content_id'] = $pDataHash['content_id'];
				$pDataHash['address_store']['paypal_addr_id'] = $pDataHash['content_id'];
				
				$result = $this->mDb->associateInsert( $table, $pDataHash['paypal_store'] );
				$result = $this->mDb->associateInsert( $atable, $pDataHash['address_store'] );

				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
				$this->mErrors['store'] = 'Failed to store this paypal.';
			}				
		}
		return( count( $this->mErrors ) == 0 ); 
	}
	
	/**
	 * PaypalItemRecordLoad( $data );
	 * Paypal shopping basket line item import  
	 */
	function PaypalItemRecordLoad( $data ) {
		$table = BIT_DB_PREFIX."paypal_line_item";
		
		$pDataHash['data_store']['txn_id'] = $data[12];
		$pDataHash['data_store']['item_title'] = $data[16];
		$pDataHash['data_store']['item_id'] = $data[17];
		$pDataHash['data_store']['option_1_name'] = $data[21];
		$pDataHash['data_store']['option_1_value'] = $data[22];
		$pDataHash['data_store']['option_2_name'] = $data[23];
		$pDataHash['data_store']['option_2_value'] = $data[24];
		$pDataHash['data_store']['item_qty'] = $data[28];
		$result = $this->mDb->associateInsert( $table, $pDataHash['data_store'] );
	}

	/**
	 * PaypalRecordLoad( $data ); 
	 * Paypal csv log file import 
	 */
	function PaypalRecordLoad( $data ) {
		if ( $data[4] == 'Shopping Cart Item' ) {
			$this->PaypalItemRecordLoad( $data );
		} else {
			$this->PaypalBaseRecordLoad( $data );
		}
	}

	/**
	 * Delete golden object and all related records
	 */
	function PaypalExpunge()
	{
		$ret = FALSE;
		$query = "DELETE FROM `".BIT_DB_PREFIX."paypal`";
		$result = $this->mDb->query( $query );
		$query = "DELETE FROM `".BIT_DB_PREFIX."paypal_address`";
		$result = $this->mDb->query( $query );
		$query = "DELETE FROM `".BIT_DB_PREFIX."paypal_line_item`";
		$result = $this->mDb->query( $query );
		return $ret;
	}

	/**
	 * getPaypalList( &$pParamHash );
	 * Get list of paypal records 
	 */
	function getPaypalList( &$pParamHash ) {
		global $gBitSystem, $gBitUser;
		
		if ( empty( $pParamHash['sort_mode'] ) ) {
			if ( empty( $_REQUEST["sort_mode"] ) ) {
				$pParamHash['sort_mode'] = 'surname_asc';
			} else {
			$pParamHash['sort_mode'] = $_REQUEST['sort_mode'];
			}
		}
		
		LibertyContent::prepGetList( $pParamHash );

		$findSql = '';
		$selectSql = '';
		$joinSql = '';
		$whereSql = '';
		$bindVars = array();
		$type = 'surname';
		
		// this will set $find, $sort_mode, $max_records and $offset
		extract( $pParamHash );

		if( isset( $find_org ) and is_string( $find_org ) and $find_org <> '' ) {
			$whereSql .= " AND UPPER( ci.`organisation` ) like ? ";
			$bindVars[] = '%' . strtoupper( $find_org ). '%';
			$type = 'organisation';
			$pParamHash["listInfo"]["ihash"]["find_org"] = $find_org;
		}
		if( isset( $find_name ) and is_string( $find_name ) and $find_name <> '' ) {
		    $split = preg_split('|[,. ]|', $find_name, 2);
			$whereSql .= " AND UPPER( ci.`surname` ) STARTING ? ";
			$bindVars[] = strtoupper( $split[0] );
		    if ( array_key_exists( 1, $split ) ) {
				$split[1] = trim( $split[1] );
				$whereSql .= " AND UPPER( ci.`forename` ) STARTING ? ";
				$bindVars[] = strtoupper( $split[1] );
			}
			$pParamHash["listInfo"]["ihash"]["find_name"] = $find_name;
		}
		if( isset( $find_street ) and is_string( $find_street ) and $find_street <> '' ) {
			$whereSql .= " AND UPPER( a.`street` ) like ? ";
			$bindVars[] = '%' . strtoupper( $find_street ). '%';
			$pParamHash["listInfo"]["ihash"]["find_street"] = $find_street;
		}
		if( isset( $find_org ) and is_string( $find_postcode ) and $find_postcode <> '' ) {
			$whereSql .= " AND UPPER( `a.postcode` ) LIKE ? ";
			$bindVars[] = '%' . strtoupper( $find_postcode ). '%';
			$pParamHash["listInfo"]["ihash"]["find_postcode"] = $find_postcode;
		}
		$query = "SELECT ci.*, a.UPRN, a.POSTCODE, a.SAO, a.PAO, a.NUMBER, a.STREET, a.LOCALITY, a.TOWN, a.COUNTY, ci.parent_id as uprn,
			(SELECT COUNT(*) FROM `".BIT_DB_PREFIX."paypal_xref` x WHERE x.content_id = ci.content_id ) AS links, 
			(SELECT COUNT(*) FROM `".BIT_DB_PREFIX."task_ticket` e WHERE e.usn = ci.usn ) AS enquiries $selectSql 
			FROM `".BIT_DB_PREFIX."paypal` ci 
			LEFT JOIN `".BIT_DB_PREFIX."paypal_address` a ON a.content_id = ci.content_id $findSql
			$joinSql 
			WHERE ci.`".$type."` <> '' $whereSql ORDER BY ".$this->mDb->convertSortmode( $sort_mode );
		$query_cant = "SELECT COUNT( * )
			FROM `".BIT_DB_PREFIX."paypal` ci
			LEFT JOIN `".BIT_DB_PREFIX."paypal_address` a ON a.content_id = ci.content_id $findSql
			$joinSql WHERE ci.`".$type."` <> '' $whereSql ";
//			INNER JOIN `".BIT_DB_PREFIX."paypal_address` a ON a.content_id = ci.content_id 
		$result = $this->mDb->query( $query, $bindVars, $max_records, $offset );
		$ret = array();
		while( $res = $result->fetchRow() ) {
			if (!empty($parse_split)) {
				$res = array_merge($this->parseSplit($res), $res);
			}
			$ret[] = $res;
		}
		$pParamHash["cant"] = $this->mDb->getOne( $query_cant, $bindVars );

		LibertyContent::postGetList( $pParamHash );
		return $ret;
	}


	/**
	 * loadPaypal( &$pParamHash );
	 * Get paypal record 
	 */
	function loadPaypal( &$pParamHash = NULL ) {
		if( $this->isValid() ) {
		$sql = "SELECT ci.*, a.*, n.*, p.*
			FROM `".BIT_DB_PREFIX."paypal` ci 
			LEFT JOIN `".BIT_DB_PREFIX."paypal_address` a ON a.usn = ci.usn
			LEFT JOIN `".BIT_DB_PREFIX."nlpg_blpu` n ON n.`uprn` = ci.`nlpg`
			LEFT JOIN `".BIT_DB_PREFIX."nlpg_lpi` p ON p.`uprn` = ci.`nlpg` AND p.`language` = 'ENG' AND p.`logical_status` = 1
			WHERE ci.`content_id` = ?";
			if( $rs = $this->mDb->query( $sql, array( $this->mContentId ) ) ) {
				if(	$this->mInfo = $rs->fields ) {
/*					if(	$this->mInfo['local_custodian_code'] == 0 ) {
						global $gBitSystem;
						$gBitSystem->fatalError( tra( 'You do not have permission to access this client record' ), 'error.tpl', tra( 'Permission denied.' ) );
					}
*/

					$sql = "SELECT x.`last_update_date`, x.`source`, x.`cross_reference` 
							FROM `".BIT_DB_PREFIX."paypal_xref` x
							WHERE x.content_id = ?";
/* Link to legacy system
							CASE
							WHEN x.`source` = 'POSTFIELD' THEN (SELECT `USN` FROM `".BIT_DB_PREFIX."caller` c WHERE ci.`caller_id` = x.`cross_reference`)
							ELSE '' END AS USN 
							
 */

					$result = $this->mDb->query( $sql, array( $this->mContentId ) );

					while( $res = $result->fetchRow() ) {
						$this->mInfo['xref'][] = $res;
						if ( $res['source'] == 'POSTFIELD' ) $ticket[] = $res['cross_reference'];
					}
					if ( isset( $ticket ) )
					{ $sql = "SELECT t.* FROM `".BIT_DB_PREFIX."task_ticket` t 
							WHERE t.caller_id IN(". implode(',', array_fill(0, count($ticket), '?')) ." )";
						$result = $this->mDb->query( $sql, $ticket );
						while( $res = $result->fetchRow() ) {
							$this->mInfo['tickets'][] = $res;
						}
					}
					$os1 = new OSRef($this->mInfo['x_coordinate'], $this->mInfo['y_coordinate']);
					$ll1 = $os1->toLatLng();
					$this->mInfo['prop_lat'] = $ll1->lat;
					$this->mInfo['prop_lng'] = $ll1->lng;
//					$this->mInfo['display_usrn'] = $this->getUsrnEntryUrl( $this->mInfo['usrn'] );
//					$this->mInfo['display_uprn'] = $this->getUprnEntryUrl( $this->mInfo['uprn'] );
//vd($this->mInfo);
				} else {
					global $gBitSystem;
					$gBitSystem->fatalError( tra( 'Client record does not exist' ), 'error.tpl', tra( 'Not found.' ) );
				}
			}
		}
		return( count( $this->mInfo ) );
	}


	/**
	 * getXrefList( &$pParamHash );
	 * Get list of xref records for this paypal record
	 */
	function loadXrefList() {
		if( $this->isValid() && empty( $this->mInfo['xref'] ) ) {
		
			$sql = "SELECT x.`last_update_date`, x.`source`, x.`cross_reference` 
				FROM `".BIT_DB_PREFIX."paypal_xref` x
				WHERE x.content_id = ?";

			$result = $this->mDb->query( $sql, array( $this->mContentId ) );

			while( $res = $result->fetchRow() ) {
				$this->mInfo['xref'][] = $res;
				if ( $res['source'] == 'POSTFIELD' ) $caller[] = $res['cross_reference'];
			}

			$sql = "SELECT t.* FROM `".BIT_DB_PREFIX."task_ticket` t 
				WHERE t.usn = ?";
			$result = $this->mDb->query( $sql, array( '9000000001' ) ); //$this->mPaypalId ) );
			while( $res = $result->fetchRow() ) {
				$this->mInfo['tickets'][] = $res;
			}

		}
	}

}
?>
