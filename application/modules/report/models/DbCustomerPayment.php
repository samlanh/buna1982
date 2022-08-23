<?php 
Class report_Model_DbCustomerPayment extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	
	function getMongPaymentById($id){
		$db=$this->getAdapter();
		$sql = "SELECT 
					r.*,
					c.cu_code,
					c.cust_name,
					c.phone,
					m.invoice_no,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = r.branch_id LIMIT 1) AS branch_name,
					(SELECT fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.user_id)  AS user_name,
					(select name_kh from tb_view where type = 5 and key_code = r.status) as status
				FROM 
					tb_receipt as r,
					tb_mong as m,
					tb_customer as c
				WHERE 
					r.invoice_id = m.id
					and m.customer_id = c.id
					and r.type = 2 
					and r.invoice_id = $id
			";
		$where= ' ';
		
		$order=" ORDER BY r.id ASC  ";
		 
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getMongInfoById($id){
		$db=$this->getAdapter();
		$sql = "SELECT 
					m.invoice_no,
					m.place_bun,
					c.cu_code,
					c.cust_name,
					c.phone as cus_phone,
					con.name,
					con.phone,
					con.address,
					(select name_kh from tb_view as v where v.type = 19 and v.key_code = con.sex) as sex,
					
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = m.branch_id LIMIT 1) AS branch_name,
					(SELECT fullname FROM `tb_acl_user` AS u WHERE u.user_id = m.user_id limit 1 )  AS user_name,
					(select name_kh from tb_view where type = 5 and key_code = m.status limit 1  ) as status
				FROM 
					tb_mong as m,
					tb_constructor as con ,
					tb_customer as c
				WHERE 
					m.customer_id = c.id
					and m.constructor = con.id
			";
		$where= ' ';
		$order=" ORDER BY m.id ASC  limit 1	";
		return $db->fetchRow($sql.$where.$order);
	}
	
	function getMongItemById($id){
		$db=$this->getAdapter();
		$sql = "SELECT 
					c.title,
					con.item_price,
					con.item_qty,
					con.item_total
				FROM 
					tb_mong as m,
					tb_mong_construct_item as con ,
					tb_constructor_item as c
				WHERE 
					m.id = $id
					and m.id = con.mong_id
					and con.item_id = c.id
					
			";
		$where= ' ';
		$order=" ORDER BY con.id ASC  ";
		return $db->fetchAll($sql.$where.$order);
	}

	function getSaleCustomerPayment($search){
		$db= $this->getAdapter();
		$sql=" SELECT
					r.id,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = r.branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
					(select sale_no from tb_sales_order where tb_sales_order.id = r.invoice_id limit 1) as invoice_num,
					(select place_bun from tb_sales_order where tb_sales_order.id = r.invoice_id limit 1) as place_bun,
					(SELECT CONCAT(cust_name) FROM `tb_customer` WHERE tb_customer.id=r.customer_id LIMIT 1 ) AS cust_name,
					(SELECT phone FROM `tb_customer` WHERE tb_customer.id=r.customer_id LIMIT 1 ) AS phone,
					
					r.receipt_no,
					r.receipt_date,
					r.total,
					r.paid,
					r.balance,
					r.remark,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = r.user_id LIMIT 1 ) AS user_name,
					(select name_kh from tb_view where type = 5 and key_code = r.status limit 1) as status_label,
					r.status
				FROM
					tb_receipt AS r
				where
					type=1
			";
		$from_date =(empty($search['start_date']))? '1': " r.receipt_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " r.receipt_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " and ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " r.receipt_no LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT CONCAT(cust_name) FROM `tb_customer` WHERE tb_customer.id=r.customer_id LIMIT 1 ) LIKE '%{$s_search}%'";
			$s_where[] = " (select sale_no from tb_sales_order where tb_sales_order.id = r.invoice_id limit 1) LIKE '%{$s_search}%'";
			$s_where[] = " (select place_bun from tb_sales_order where tb_sales_order.id = r.invoice_id limit 1) LIKE '%{$s_search}%'";
			$s_where[] = " r.total LIKE '%{$s_search}%'";
			$s_where[] = " r.paid LIKE '%{$s_search}%'";
			$s_where[] = " r.balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['customer_id']>0){
			$where .= " AND r.customer_id = ".$search['customer_id'];
		}
// 		if($search['branch_id']>0){
// 			$where .= " AND branch_id =".$search['branch_id'];
// 		}
		if($search['status']!=-1){
			$where .= " AND r.status = ".$search['status'];
		}
		if(!empty($search['branch_id'])){
			$where .= " AND r.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		if($search['order']==1){
			$order=" ORDER BY r.receipt_date ASC , r.id ASC ";
		}else{
			$order=" ORDER BY r.invoice_id ASC , r.id ASC ";
		}
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getMongCustomerPayment($search){
		$db= $this->getAdapter();
		$sql=" SELECT
					r.id,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = r.branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
					(select invoice_no from tb_mong where tb_mong.id = r.invoice_id limit 1) as invoice_num,
					(select place_bun from tb_mong where tb_mong.id = r.invoice_id limit 1) as place_bun,
					(SELECT CONCAT(cust_name) FROM `tb_customer` WHERE tb_customer.id=r.customer_id LIMIT 1 ) AS cust_name,
					(SELECT phone FROM `tb_customer` WHERE tb_customer.id=r.customer_id LIMIT 1 ) AS phone,
						
					r.receipt_no,
					r.receipt_date,
					r.total,
					r.paid,
					r.balance,
					r.remark,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = r.user_id LIMIT 1 ) AS user_name,
					(select name_kh from tb_view where type = 5 and key_code = r.status) as status_label,
					r.status
				FROM
					tb_receipt AS r
				where
					type=2
			";
		$from_date =(empty($search['start_date']))? '1': " r.receipt_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " r.receipt_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " and ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " r.receipt_no LIKE '%{$s_search}%'";
			$s_where[] = " (select invoice_no from tb_mong where tb_mong.id = r.invoice_id limit 1) LIKE '%{$s_search}%'";
			$s_where[] = " (select place_bun from tb_mong where tb_mong.id = r.invoice_id limit 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT CONCAT(cust_name) FROM `tb_customer` WHERE tb_customer.id=r.customer_id LIMIT 1 ) LIKE '%{$s_search}%'";
			$s_where[] = " r.total LIKE '%{$s_search}%'";
			$s_where[] = " r.paid LIKE '%{$s_search}%'";
			$s_where[] = " r.balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['customer_id']>0){
			$where .= " AND r.customer_id = ".$search['customer_id'];
		}
		if($search['status']!=-1){
			$where .= " AND r.status = ".$search['status'];
		}
		//if($search['branch_id']>0){
		//	$where .= " AND branch_id =".$search['branch_id'];
		//}
		if(!empty($search['branch_id'])){
			$where .= " AND r.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		if($search['order']==1){
			$order=" ORDER BY r.receipt_date ASC , r.id ASC ";
		}else{
			$order=" ORDER BY r.invoice_id ASC , r.id ASC ";
		}
		return $db->fetchAll($sql.$where.$order);
	}
	
}

?>