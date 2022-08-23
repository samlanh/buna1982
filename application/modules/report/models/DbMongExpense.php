<?php 
Class report_Model_DbMongExpense extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	
	public function getMongExpense($search,$id=0){
		$db= $this->getAdapter();
		//print_r($search);
		$sql=" SELECT 
					me.*,
					me.id,
					(SELECT b.name FROM `tb_sublocation` as b WHERE b.id = me.branch_id AND b.status=1 AND b.name!='' LIMIT 1) AS branch_name,
					(SELECT CONCAT(cust_name) FROM `tb_customer` WHERE tb_customer.id=m.customer_id LIMIT 1 ) AS customer_name,
					m.invoice_no,
					m.phone,
					m.place_bun,
					me.date_payment,
					me.total_payment,
					(select c.name from tb_constructor as c where c.id = me.constructor limit 1) as constructor_name,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = me.user_id LIMIT 1 ) AS user_name,
					(SELECT name_kh FROM tb_view WHERE type=5 AND key_code=me.status LIMIT 1) status_name,
					me.status
				FROM 
					`tb_mong` AS m,
					tb_mong_expense as me
				WHERE
					me.mong_id = m.id
			";
		if($id>0){ // for report mong_expense_detail
			$where = " AND me.id = $id limit 1 ";
			return $db->fetchRow($sql.$where);
		}			
			
		
		$from_date =(empty($search['start_date']))? '1': " me.date_payment >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " me.date_payment <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " m.invoice_no LIKE '%{$s_search}%'";
			$s_where[] = " me.expense_number LIKE '%{$s_search}%'";
			$s_where[] = " m.place_bun LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT CONCAT(cust_name) FROM `tb_customer` WHERE tb_customer.id=m.customer_id LIMIT 1 ) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']!=-1){
			$where .= " AND me.status = ".$search['status'];
		}
// 		if($search['branch_id']>0){
// 			$where .= " AND branch_id =".$search['branch_id'];
// 		}
		if(!empty($search['branch_id'])){
			$where .= " AND me.branch_id =".$search['branch_id'];
		}
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		
		$order=" ORDER BY me.id DESC ";
		//echo $sql.$where.$order;exit();
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getExpenseByMong($id){
		$db= $this->getAdapter();
		$sql=" SELECT 
					me.*,
					me.id,
					(SELECT b.name FROM `tb_sublocation` as b WHERE b.id = me.branch_id AND b.status=1 AND b.name!='' LIMIT 1) AS branch_name,
					(SELECT CONCAT(cust_name) FROM `tb_customer` WHERE tb_customer.id=m.customer_id LIMIT 1 ) AS customer_name,
					m.invoice_no,
					m.phone,
					m.place_bun,
					me.date_payment,
					me.total_payment,
					(select c.name from tb_constructor as c where c.id = me.constructor limit 1) as constructor_name,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = me.user_id LIMIT 1 ) AS user_name,
					(SELECT name_kh FROM tb_view WHERE type=5 AND key_code=me.status LIMIT 1) status_name,
					me.status
				FROM 
					`tb_mong` AS m,
					tb_mong_expense as me
				WHERE
					me.mong_id = m.id
					and m.id = $id
			";
		$where=" ";	
		
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		
		$order=" ORDER BY me.id DESC ";
		//echo $sql.$where.$order;exit();
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getMongExpenseItem($search,$id=0){
		$db= $this->getAdapter();
		//print_r($search);
		$sql=" SELECT 
					me.*,
					me.id,
					(SELECT b.name FROM `tb_sublocation` as b WHERE b.id = me.branch_id AND b.status=1 AND b.name!='' LIMIT 1) AS branch_name,
					(SELECT CONCAT(cust_name) FROM `tb_customer` WHERE tb_customer.id=m.customer_id LIMIT 1 ) AS customer_name,
					m.invoice_no,
					m.phone,
					m.place_bun,
					me.date_payment,
					me.total_payment,
					(select c.name from tb_constructor as c where c.id = me.constructor limit 1) as constructor_name,
					
					(select ci.title from tb_constructor_item as ci where ci.id = med.item_id limit 1) as expense_name, 
					med.item_price,
					med.item_qty,
					med.item_total,
					
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = me.user_id LIMIT 1 ) AS user_name,
					(SELECT name_kh FROM tb_view WHERE type=5 AND key_code=me.status LIMIT 1) status_name,
					me.status
				FROM 
					`tb_mong` AS m,
					tb_mong_expense as me,
					tb_mong_expense_detail as med 
				WHERE
					me.mong_id = m.id
					and me.id=med.expense_id
			";
		if($id>0){ // for report mong_expense_detail
			$where = " AND me.id = $id limit 1 ";
			return $db->fetchRow($sql.$where);
		}			
			
		
		$from_date =(empty($search['start_date']))? '1': " me.date_payment >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " me.date_payment <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " m.invoice_no LIKE '%{$s_search}%'";
			$s_where[] = " me.expense_number LIKE '%{$s_search}%'";
			$s_where[] = " m.place_bun LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT CONCAT(cust_name) FROM `tb_customer` WHERE tb_customer.id=m.customer_id LIMIT 1 ) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']!=-1){
			$where .= " AND me.status = ".$search['status'];
		}
		if(!empty($search['branch_id'])){
			$where .= " AND me.branch_id =".$search['branch_id'];
		}
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		
		$order=" ORDER BY me.id DESC ";
		//echo $sql.$where.$order;exit();
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getMongExpenseDetail($id){
		$db= $this->getAdapter();
		$sql=" SELECT 
					med.*,
					(select ci.title from tb_constructor_item as ci where ci.id = med.item_id limit 1) as expense_name 
				from 
					tb_mong_expense_detail as med 
				where 
					expense_id=$id
			";
		return $db->fetchAll($sql);
	}
	
}

?>