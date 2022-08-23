<?php 
Class report_Model_DbTransferStock extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getAllTransferStock($search){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT 
				   t.id,
				   (select name from tb_sublocation as l where l.id = t.from_location) as from_loc,
				   (select name from tb_sublocation as l where l.id = t.to_location) as to_loc,
				   note,
				   create_date,
				   (SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id=t.user_id) AS user,
				   (select name_kh from tb_view as v where v.type=5 and v.key_code = t.status) as status
				FROM
				  	tb_product_transfer as t
			";
		$where = '';
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		if($search['status']!=-1){
			$where .= " and status = ".$search['status'];
		}
		if(!empty($search['from_location'])){
			$where .= " and from_location = ".$search['from_location'];
		}
		if(!empty($search['to_location'])){
			$where .= " and to_location = ".$search['to_location'];
		}
 		if(!empty($search["ad_search"])){
 			$s_where=array();
 			$s_search = addslashes(trim($search['ad_search']));
 			$s_where[]= " (select name from tb_sublocation as l where l.id = t.from_location) LIKE '%{$s_search}%'";
 			$s_where[]= " (select name from tb_sublocation as l where l.id = t.to_location) LIKE '%{$s_search}%'";
 			$s_where[]= " t.note LIKE '%{$s_search}%'";
 			$where.=' AND ('.implode(' OR ', $s_where).')';
 		}
 		$order=" order by id DESC";
 		//echo $sql.$where.$order;exit();
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllTransferStockById($id){
		$db = $this->getAdapter();
		$sql="select 
					(select name from tb_sublocation as l where l.id = t.from_location) as from_loc,
				    (select name from tb_sublocation as l where l.id = t.to_location) as to_loc,
				    (select item_name from tb_product as p where p.id = td.pro_id) as pro_name,
				    (select item_code from tb_product as p where p.id = td.pro_id) as pro_code,
					td.*
				from 
					tb_product_transfer as t,
					tb_product_transfer_detail as td
				where 
					t.id = td.transfer_id
					and transfer_id = $id		
			";
		return $db->fetchAll($sql);
	}
	
	function getAllTransferStockDetail($search){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT
					t.id,
					(select name from tb_sublocation as l where l.id = t.from_location) as from_loc,
					(select name from tb_sublocation as l where l.id = t.to_location) as to_loc,
					td.note,
					create_date,
					(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id=t.user_id) AS user,
					(select name_kh from tb_view as v where v.type=5 and v.key_code = t.status) as status,
					(select item_name from tb_product as p where p.id = td.pro_id) as pro_name,
				    (select item_code from tb_product as p where p.id = td.pro_id) as pro_code,
				    td.*
				FROM
					tb_product_transfer as t,
					tb_product_transfer_detail as td
				where 
					t.id = td.transfer_id	
			";
		$where = '';
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
	
		if($search['status']!=-1){
			$where .= " and t.status = ".$search['status'];
		}
		if(!empty($search['from_location'])){
			$where .= " and from_location = ".$search['from_location'];
		}
		if(!empty($search['to_location'])){
			$where .= " and to_location = ".$search['to_location'];
		}
		if(!empty($search["ad_search"])){
			$s_where=array();
			$s_search = addslashes(trim($search['ad_search']));
			$s_where[]= " (select name from tb_sublocation as l where l.id = t.from_location) LIKE '%{$s_search}%'";
			$s_where[]= " (select name from tb_sublocation as l where l.id = t.to_location) LIKE '%{$s_search}%'";
			$s_where[]= " td.note LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		$order=" order by t.id DESC";
		//echo $sql.$where.$order;exit();
		return $db->fetchAll($sql.$where.$order);
	}
	
}

?>