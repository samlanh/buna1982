<?php 
Class report_Model_DbPaidToSupplyer extends Zend_Db_Table_Abstract{
	
	function getPurchasePayment($search){
		$db= $this->getAdapter();
		$sql="SELECT
					(SELECT name FROM `tb_sublocation` WHERE id=p.branch_id) AS branch_name,
					(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_name,
					(SELECT v_phone FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_tel,
					p.order_number,
					v.remark,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = v.user_id LIMIT 1 ) AS user_name,
					v.expense_date,
					v.payment_type,
					v.total,
					v.paid,
					v.balance,
					v.status,
					(SELECT name_kh FROM `tb_view` WHERE key_code = v.status AND `type`=5 LIMIT 1) As status_name
				FROM
					`tb_purchase_order` AS p,
					`tb_vendor_payment` AS v
				WHERE
					p.id=v.purchase_id
					AND p.status=1
			";
//		$where= '';
		$from_date =(empty($search['start_date']))? '1': " v.expense_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " v.expense_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " and ".$from_date." AND ".$to_date;
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " p.branch_id LIKE '%{$s_search}%'";
			$s_where[] = " p.order_number LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch'])){
			$where .= " AND p.branch_id =".$search['branch'];
		}
		if($search['status']!=-1){
			$where .= " AND v.status = ".$search['status'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY v.id ASC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getPartnerServicePayment($search){
		$db=$this->getAdapter();
		$sql = "SELECT
					(SELECT name FROM `tb_sublocation` WHERE id=pp.branch_id) AS branch_name,
					p.partner_name,
					p.tel,
					pp.date_payment,
					pp.payment_type,
					pp.note,
					pp.total_payment,
					pp.paid,
					pp.balance,
					pp.status,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = pp.user_id LIMIT 1 ) AS user_name,
					(SELECT name_kh FROM `tb_view` WHERE key_code = pp.status AND `type`=5 LIMIT 1) As status_name
				FROM
					`tb_partnerservice` AS p,
					`tb_partnerservice_payment` AS pp
				WHERE
					p.id=pp.partner_id
					AND pp.status=1
			";
//		$where= ' ';
		$from_date =(empty($search['start_date']))? '1': " pp.date_payment >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " pp.date_payment <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " partner_name LIKE '%{$s_search}%'";
			$s_where[] = " tel LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch'])){
			$where.= " AND pp.branch_id =".$search['branch'];
		}
		if($search['status']!=-1){
			$where .= " AND pp.status = ".$search['status'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY pp.id ASC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	
	function getPurchaseExpense($search){
		$db= $this->getAdapter();
		$sql="SELECT
					SUM(paid) AS total_paid
				FROM
					`tb_vendor_payment` AS v
				WHERE
					v.status = 1
			";
		$from_date =(empty($search['start_date']))? '1': " v.expense_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " v.expense_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " and ".$from_date." AND ".$to_date;
		if($search['branch_id']>0){
			$where .= " AND v.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY v.id ASC ";
		return $db->fetchRow($sql.$where.$order);
	}
	
	function getPartnerServiceExpense($search){
		$db=$this->getAdapter();
		$sql="SELECT
					SUM(pp.total_payment) AS total_paid
				FROM
					`tb_partnerservice_payment` AS pp
				WHERE
					pp.status = 1
			";
		$from_date =(empty($search['start_date']))? '1': " pp.date_payment >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " pp.date_payment <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if($search['branch_id']>0){
			$where .= " AND pp.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY pp.id ASC";
		return $db->fetchRow($sql.$where.$order);
	}
	
	function getMongExpense($search){
		$db=$this->getAdapter();
		$sql="SELECT 
				  SUM(total_payment) AS total_paid
				FROM
				  `tb_mong_expense` AS m 
				WHERE 
				  m.status = 1
			";
		$from_date =(empty($search['start_date']))? '1': " m.date_payment >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " m.date_payment <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if($search['branch_id']>0){
			$where .= " AND m.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY m.id ASC ";
		return $db->fetchRow($sql.$where.$order);
	}
	
	function getOtherExpense($search){
		$db=$this->getAdapter();
		$sql="SELECT 
				  SUM(total_amount) AS total_paid
				FROM
				  `tb_expense` AS e 
				WHERE 
				  e.status = 1
		";
		$from_date =(empty($search['start_date']))? '1': " e.for_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " e.for_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if($search['branch_id']>0){
			$where .= " AND e.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY e.id ASC ";
		return $db->fetchRow($sql.$where.$order);
	}
}

?>