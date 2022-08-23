<?php 
Class report_Model_DbSale extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	
	public function getAllSaleOrderReport($search){//4
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
					s.all_total,
					s.paid,
					s.balance,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = s.user_id LIMIT 1 ) AS user_name,
					(SELECT name_kh FROM tb_view WHERE type=5 AND key_code=s.status LIMIT 1) status_name,
					s.status
				FROM 
					`tb_sales_order` AS s 
		
			";
		
		$from_date =(empty($search['start_date']))? '1': " s.date_sold >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_sold <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " s.sale_no LIKE '%{$s_search}%'";
			$s_where[] = " s.place_bun LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT CONCAT(cust_name) FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) LIKE '%{$s_search}%'";
			$s_where[] = " s.all_total LIKE '%{$s_search}%'";
			$s_where[] = " s.paid LIKE '%{$s_search}%'";
			$s_where[] = " s.balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['customer_id']!=-1){
			$where .= " AND s.customer_id = ".$search['customer_id'];
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
		if($search['is_complete']==1){
			$where .= " AND s.balance_after = 0 ";
		}
		if($search['is_complete']==2){
			$where .= " AND s.balance_after > 0 ";
		}
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		
		$order=" ORDER BY id DESC ";
		echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getSalePaymentById($id){//5
		$db = $this->getAdapter();
		$sql=" SELECT
					r.id,
					(SELECT name FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
					c.cust_name,
					c.phone,
					s.sale_no as invoice_num,
					r.receipt_no,
					r.receipt_date,
					r.total,
					r.paid,
					r.balance,
					r.remark,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = s.user_id LIMIT 1 ) AS user_name,
					(SELECT name_kh FROM tb_view WHERE type=5 AND key_code=r.status LIMIT 1) status
				FROM 
					tb_receipt as r,
					tb_sales_order AS s,
					tb_customer as c
				WHERE 
					r.invoice_id = s.id
					and s.customer_id = c.id
					AND s.status=1 
					and r.type=1
					AND s.id = $id 
				order by
					id ASC	
					
			";
		return $db->fetchAll($sql);
	}
	
	function getProductSaleById($id){//5
		$db = $this->getAdapter();
		$sql=" SELECT
		(SELECT name FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
		s.sale_no,s.date_sold,s.remark,
		(SELECT name FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
		(SELECT item_code FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS item_code,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		(SELECT address FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS add_name,
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		so.qty_order,so.price,so.old_price,so.sub_total,s.net_total,
		s.paid,s.discount_value,s.tax,
		s.balance
		FROM `tb_sales_order` AS s,
		`tb_salesorder_item` AS so WHERE s.id=so.saleorder_id
		AND s.status=1 AND s.id = $id ";
		return $db->fetchAll($sql);
	}

	
	
	function getSaleProductDetail($search){//6
		$db = $this->getAdapter();
		$sql=" SELECT
		(SELECT name FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
		it.item_name,
		it.item_code,
		it.qty_perunit AS qty_perunit,
		it.unit_label AS unit_label,
		(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=it.measure_id LIMIT 1) as measue_name,
		(SELECT name FROM `tb_category` WHERE id=it.cate_id LIMIT 1) AS cate_name,
		(SELECT name FROM `tb_brand` WHERE id=it.brand_id LIMIT 1) AS brand_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
	
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		so.qty_order,
		so.price,
		so.cost_price,
		so.sub_total,s.net_total,
		s.id,s.sale_no,s.date_sold,s.remark,
		s.paid,s.tax,
		s.balance
		FROM `tb_sales_order` AS s,
		`tb_salesorder_item` AS so,
		tb_product AS it
		WHERE s.id=so.saleorder_id AND it.id=so.pro_id
		AND s.status=1 ";
		$from_date =(empty($search['start_date']))? '1': " s.date_sold >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_sold <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['txt_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['txt_search']));
			$s_where[] = " it.item_name LIKE '%{$s_search}%'";
			$s_where[] = " it.item_code LIKE '%{$s_search}%'";
			$s_where[] = " it.barcode LIKE '%{$s_search}%'";
			$s_where[] = " s.sale_no LIKE '%{$s_search}%'";
			$s_where[] = " s.net_total LIKE '%{$s_search}%'";
			$s_where[] = " s.paid LIKE '%{$s_search}%'";
			$s_where[] = " s.balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['item']>0){
			$where .= " AND it.id =".$search['item'];
		}
		if($search['category_id']>0){
			$where .= " AND it.cate_id =".$search['category_id'];
		}
		if($search['customer_id']>0){
			$where .= " AND s.customer_id =".$search['customer_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND s.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY s.customer_id ,s.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	
	function getProductSoldDetail($search){//6
		$db = $this->getAdapter();
		$sql=" SELECT
		(SELECT name FROM `tb_sublocation` WHERE id=v.branch_id) AS branch_name,
		it.item_name,
		it.item_code,
		(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=it.measure_id LIMIT 1) as measue_name,
		it.qty_perunit AS qty_perunit,
		it.unit_label AS unit_label,
		(SELECT name FROM `tb_category` WHERE id=it.cate_id LIMIT 1) AS cate_name,
		(SELECT name FROM `tb_brand` WHERE id=it.brand_id LIMIT 1) AS brand_name,
		SUM(so.qty_order) AS qty_order,
		SUM(so.qty_unit) AS qty_unit,
		SUM(so.qty_detail) AS qty_detail
		FROM `tb_invoice` AS v,
		`tb_salesorder_item` AS so,
		tb_product AS it
		WHERE v.sale_id=so.saleorder_id AND it.id=so.pro_id
		AND v.status =1 AND v.is_approved=1 ";
		$from_date =(empty($search['start_date']))? '1': " v.invoice_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " v.invoice_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['txt_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['txt_search']));
			$s_where[] = " it.item_name LIKE '%{$s_search}%'";
			$s_where[] = " it.item_code LIKE '%{$s_search}%'";
			$s_where[] = " it.barcode LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['item']>0){
			$where .= " AND it.id =".$search['item'];
		}
		if($search['category_id']>0){
			$where .= " AND it.cate_id =".$search['category_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND v.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order="  GROUP BY so.pro_id ORDER BY v.branch_id ,qty_order DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function totalSale($search){
		$db = $this->getAdapter();
		$sql="SELECT 
				  SUM(s.all_total) AS total,
				  SUM(s.`paid`) AS paid,
				  SUM(s.`balance_after`) AS balance
				FROM
				  `tb_sales_order` AS s 
				WHERE 
				  s.status = 1 
			";
		$from_date =(empty($search['start_date']))? '1': " s.date_sold >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_sold <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['branch_id'])){
			$where .= " AND s.branch_id =".$search['branch_id'];
		}
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		
		return $db->fetchRow($sql.$where);
	}
	
	function totalMong($search){
		$db = $this->getAdapter();
		$sql="SELECT 
				  SUM(m.`sub_total`) AS total,
				  SUM(m.`paid`) AS paid,
				  SUM(m.`balance_after`) AS balance
				FROM
				  `tb_mong` AS m 
				WHERE 
				  m.status = 1 
			";
		$from_date =(empty($search['start_date']))? '1': " m.sale_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " m.sale_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
	
		if(!empty($search['branch_id'])){
			$where .= " AND m.branch_id =".$search['branch_id'];
		}
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
	
		return $db->fetchRow($sql.$where);
	}
	function totalDonor($search){
		$db = $this->getAdapter();
		$sql="SELECT 
				  SUM(d.`total_amount`) AS total 
				FROM
				  `tb_donors` AS d 
				WHERE 
				  d.status = 1 
		";
		$from_date =(empty($search['start_date']))? '1': " d.paid_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " d.paid_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
	
		if(!empty($search['branch_id'])){
			$where .= " AND d.branch_id =".$search['branch_id'];
		}
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
	
		return $db->fetchRow($sql.$where);
	}
	
	
	
}

?>