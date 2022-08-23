<?php 
Class report_Model_DbReturnStock extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	public function getAllReturnStock($search){//1
		$db= $this->getAdapter();
		$sql=" SELECT
					*,
					(select name_kh from tb_view where type=5 and key_code = status) as status,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = user_id LIMIT 1 ) AS user_name
				FROM 
					`tb_return_stock`  
			";
	
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
	
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " return_code LIKE '%{$s_search}%'";
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " total_amount LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if($search['status']>-1){
			$where .=' AND status = '.$search['status'];
		}
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	function getReturnStockDetail($search){//3
		$db = $this->getAdapter();
		$sql=" SELECT
					r.return_code,
					r.title,
					p.item_name as product_name,
					rd.qty,
					rd.price,
					rd.total,
					rd.note,
					r.create_date,
					(select name_kh from tb_view where type=5 and key_code = r.status) as status,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = r.user_id LIMIT 1 ) AS user_name
				FROM 
					tb_return_stock AS r,
					tb_return_stock_detail AS rd,
					tb_product AS p
				WHERE 
					r.id=rd.return_id
					AND p.id=rd.product_id
			";
		
		$from_date =(empty($search['start_date']))? '1': " r.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " r.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " r.return_code LIKE '%{$s_search}%'";
			$s_where[] = " r.title LIKE '%{$s_search}%'";
			$s_where[] = " rd.total LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['product_id']>0){
			$where .= " AND p.id =".$search['product_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY r.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function returnDetailById($id){
		$db = $this->getAdapter();
		$sql=" SELECT
					r.return_code,
					r.title,
					p.item_name as product_name,
					rd.qty,
					rd.price,
					rd.total,
					rd.note
				FROM 
					tb_return_stock AS r,
					tb_return_stock_detail AS rd,
					tb_product AS p
				WHERE 
					r.id=rd.return_id
					AND p.id=rd.product_id
					AND r.id = $id
			";
		return $db->fetchAll($sql);
	}
	
	
	function getAllProduct(){
		$db = $this->getAdapter();
		$sql="select id,item_name,item_code from tb_product where status=1 and is_service=0";
		return $db->fetchAll($sql);
	}
	
}

?>