<?php 
Class report_Model_DbComision extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	
	public function getSaleComision($search){
		$db= $this->getAdapter();
		//print_r($search);
		$sql=" SELECT 
					s.*,
					s.id,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = s.branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
					(SELECT CONCAT(cust_name) FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
					(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
					s.sale_no,
					s.date_sold,
					(SELECT a.name FROM tb_sale_agent AS a WHERE a.id = s.saleagent_id LIMIT 1 ) AS sale_name,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = s.user_id LIMIT 1 ) AS user_name,
					(SELECT name_kh FROM tb_view WHERE type=5 AND key_code=s.status LIMIT 1) status_name,
					s.status
				FROM 
					`tb_sales_order` AS s 
				WHERE
					comission > 0 
			";
		
		$from_date =(empty($search['start_date']))? '1': " s.date_sold >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_sold <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " s.sale_no LIKE '%{$s_search}%'";
			$s_where[] = " s.place_bun LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT CONCAT(cust_name) FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT a.name FROM tb_sale_agent AS a WHERE a.id = s.saleagent_id LIMIT 1 ) LIKE '%{$s_search}%'";
			$s_where[] = " comission LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['sale_id']!=-1){
			$where .= " AND s.saleagent_id = ".$search['sale_id'];
		}
		if($search['status']!=-1){
			$where .= " AND s.status = ".$search['status'];
		}
// 		if($search['branch_id']>0){
// 			$where .= " AND branch_id =".$search['branch_id'];
// 		}
		if(!empty($search['branch'])){
			$where .= " AND branch_id =".$search['branch'];
		}
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		
		$order=" ORDER BY id DESC ";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getMongComision($search){
		$db= $this->getAdapter();
		//print_r($search);
		$sql=" SELECT 
					s.*,
					s.id,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = s.branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
					(SELECT CONCAT(cust_name) FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
					(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
					s.invoice_no,
					s.create_date,
					(SELECT a.name FROM tb_sale_agent AS a WHERE a.id = s.sale_agent LIMIT 1 ) AS sale_name,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = s.user_id LIMIT 1 ) AS user_name,
					(SELECT name_kh FROM tb_view WHERE type=5 AND key_code=s.status LIMIT 1) status_name,
					s.status
				FROM 
					`tb_mong` AS s 
				WHERE
					comission > 0 
			";
		
		$from_date =(empty($search['start_date']))? '1': " s.create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " s.invoice_no LIKE '%{$s_search}%'";
			$s_where[] = " s.place_bun LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT CONCAT(cust_name) FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT a.name FROM tb_sale_agent AS a WHERE a.id = s.sale_agent LIMIT 1 ) LIKE '%{$s_search}%'";
			$s_where[] = " comission LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['sale_id']!=-1){
			$where .= " AND s.sale_agent = ".$search['sale_id'];
		}
		if($search['status']!=-1){
			$where .= " AND s.status = ".$search['status'];
		}
// 		if($search['branch_id']>0){
// 			$where .= " AND branch_id =".$search['branch_id'];
// 		}
		if(!empty($search['branch'])){
			$where .= " AND branch_id =".$search['branch'];
		}
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		
		$order=" ORDER BY id DESC ";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getAllSaleAgent(){
		$db= $this->getAdapter();
		$sql=" SELECT id,name from tb_sale_agent where status=1";
		return $db->fetchAll($sql);
	}
	
}

?>