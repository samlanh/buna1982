<?php 
Class report_Model_DbProduct extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getBranch($id){
		$db = $this->getAdapter();
		$sql ="SELECT b.`name` FROM `tb_sublocation` AS b WHERE b.`id`='".$id."'";
		return $db->fetchOne($sql);
	}
	function getAllProduct($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT
					  p.`id`,
					  p.`barcode`,
					  p.`item_code`,
					  p.`item_name` ,
		  			  p.`status`,
		  			  p.`unit_label`,
					  p.`qty_perunit`,
					  p.`price`,
					  p.selling_price,
					  p.is_service,
					  (SELECT b.`name` FROM `tb_brand` AS b WHERE b.`id`=p.`brand_id` LIMIT 1) AS brand,
					  (SELECT c.name FROM `tb_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
					  (SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure
				FROM
					  `tb_product` AS p 
				WHERE 
				    p.status=1
			";
		$where = '';
		
		if($data["ad_search"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['ad_search']));
			$s_where[]= " p.item_name LIKE '%{$s_search}%'";
			$s_where[]= " p.barcode LIKE '%{$s_search}%'";
			$s_where[]= " p.item_code LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["brand"]!=""){
			$where.=' AND p.brand_id='.$data["brand"];
		}
		if(!empty($data["category"])){
			$where.=' AND p.cate_id='.$data["category"];
		}
		if($data["type"]>-1){
			$where.=' AND p.is_service='.$data["type"];
		}
		$group = " GROUP BY p.`id` ORDER BY p.`id`";
		return $db->fetchAll($sql.$where.$group);
	}
	function getOneProduct($data){
		$db = $this->getAdapter();
		$sql ="SELECT
					p.`id`,
					p.`barcode`,
					p.`item_code`,
					p.`item_name` ,
					p.`status`,
					p.`unit_label`,
					p.`qty_perunit`,
					p.`price`,
					p.selling_price,
					p.is_service,
					(SELECT b.`name` FROM `tb_brand` AS b WHERE b.`id`=p.`brand_id` LIMIT 1) AS brand,
					(SELECT c.name FROM `tb_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
					(SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure
				FROM
					`tb_product` AS p
				WHERE
					p.status=1
					and p.id = ".$data['pro_id'];
		return $db->fetchRow($sql);
	}
	
	
	function getProduct($data){
		$db = $this->getAdapter();
		$newarray = array();
		if(!empty($data['identity'])){
			$ids=explode(',',$data['identity']);
			foreach ($ids as $i){
				
				$sql ="SELECT
						p.`id`,
						p.`barcode`,
						p.`item_code`,
						p.`item_name` ,
						p.`status`,
						p.`price`,
						p.selling_price
					FROM
						`tb_product` AS p
					WHERE
						p.status=1
						and p.id = ".$data['pro_'.$i];
				
				$result = $db->fetchRow($sql);
				
				$newarray = array_merge($newarray,$result);
				
			}
		}
		//return $db->fetchRow($sql);
		print_r($newarray);
	}
	
	function getAllProductSold($data){
		$db = $this->getAdapter();
		
		$from_date =(empty($data['start_date']))? '1': " s.date_sold >= '".date("Y-m-d",strtotime($data['start_date']))." 00:00:00'";
		$to_date = (empty($data['end_date']))? '1': " s.date_sold <= '".date("Y-m-d",strtotime($data['end_date']))." 23:59:59'";
		$sale = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($data['branch'])){
			$sale.= " AND s.branch_id =".$data['branch'];
		}
		
		$from_date =(empty($data['start_date']))? '1': " m.sale_date >= '".date("Y-m-d",strtotime($data['start_date']))." 00:00:00'";
		$to_date = (empty($data['end_date']))? '1': " m.sale_date <= '".date("Y-m-d",strtotime($data['end_date']))." 23:59:59'";
		$mong = " AND ".$from_date." AND ".$to_date;
		if(!empty($data['branch'])){
			$mong.= " AND m.branch_id =".$data['branch'];
		}
		$sql ="SELECT 
					p.*,
					(SELECT b.`name` FROM `tb_brand` AS b WHERE b.`id`=p.`brand_id` LIMIT 1) AS brand,
					(SELECT c.name FROM `tb_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
					(SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
					(SELECT SUM(si.qty_order) FROM `tb_salesorder_item` AS si,`tb_sales_order` AS s WHERE s.status=1 and s.id = si.saleorder_id AND si.pro_id = p.id $sale limit 1) AS from_sale,
					(SELECT SUM(msi.qty_order) FROM `tb_mong_sale_item` AS msi,`tb_mong` AS m WHERE m.status=1 and m.id = msi.mong_id AND msi.pro_id = p.id $mong limit 1) AS from_mong,
					
					(SELECT SUM(si.sub_total) FROM `tb_salesorder_item` AS si,`tb_sales_order` AS s WHERE s.status=1 and s.id = si.saleorder_id AND si.pro_id = p.id $sale limit 1) AS total_from_sale,
					(SELECT SUM(msi.sub_total) FROM `tb_mong_sale_item` AS msi,`tb_mong` AS m WHERE m.status=1 and m.id = msi.mong_id AND msi.pro_id = p.id $mong limit 1) AS total_from_mong
				FROM
					`tb_product` AS p 
				WHERE 
					p.`status` = 1 		
					and ((SELECT SUM(si.qty_order) FROM `tb_salesorder_item` AS si,`tb_sales_order` AS s WHERE s.status=1 and s.id = si.saleorder_id AND si.pro_id = p.id $sale limit 1)>0
					or (SELECT SUM(msi.qty_order) FROM `tb_mong_sale_item` AS msi,`tb_mong` AS m WHERE m.status=1 and m.id = msi.mong_id AND msi.pro_id = p.id $mong limit 1)>0)
			";
		$where = '';
	
		if($data["ad_search"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['ad_search']));
			$s_where[]= " p.item_name LIKE '%{$s_search}%'";
			$s_where[]= " p.barcode LIKE '%{$s_search}%'";
			$s_where[]= " p.item_code LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["type"]>-1){
			$where.=' AND p.is_service='.$data["type"];
		}
		if(!empty($data["category"])){
			$where.=' AND p.cate_id='.$data["category"];
		}
		
		$group = " GROUP BY p.`id` ORDER BY p.`id`";
		//echo $sql.$where;
		return $db->fetchAll($sql.$where.$group);
	}
	function getAllcurrentstock($data){
		$db = $this->getAdapter();
		$db_global = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT
					p.`id`,
					p.`barcode`,
					p.`item_code`,
					p.`item_name` ,
					p.`status`,
					p.`unit_label`,
					p.`qty_perunit`,
					p.`price`,
					p.selling_price,
					p.is_service,
					pl.`location_id`,
					pl.qty,
					pl.qty_warning,
					(SELECT c.name FROM `tb_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
					(SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
					(SELECT name FROM tb_sublocation AS s WHERE s.id=pl.`location_id` LIMIT 1) as location_name
				FROM
					`tb_product` AS p ,
					`tb_prolocation` AS pl
				WHERE
					p.status=1
					AND p.`id`=pl.`pro_id` 
			";
		
		$where = '';
		if($data["ad_search"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['ad_search']));
			$s_where[]= " p.item_name LIKE '%{$s_search}%'";
			$s_where[]=" p.barcode LIKE '%{$s_search}%'";
			$s_where[]= " p.item_code LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["branch"]!=""){
			$where.=' AND pl.`location_id`='.$data["branch"];
		}
		if($data["brand"]!=""){
			$where.=' AND p.brand_id='.$data["brand"];
		}
		if($data["category"]!=""){
			$where.=' AND p.cate_id='.$data["category"];
		}
		if($data["status_qty"]>-1){
			if($data["status_qty"]==1){
				$where.=' AND pl.qty>0';
			}else{
				$where.=' AND pl.qty=0';
			}
		}
		$location = $db_global->getAccessPermission('pl.`location_id`');
		$group = " ORDER BY pl.location_id ASC,p.id ASC ";
		//echo $sql.$where.$location.$group;
		return $db->fetchAll($sql.$where.$location.$group);
	}
	
	function getQtyProductByProIdLoca($id,$loc_id){
		$db = $this->getAdapter();
		$sql = "SELECT pl.`qty` FROM `tb_prolocation` AS pl  WHERE pl.`pro_id`=$id AND pl.`location_id`=$loc_id";
		return $db->fetchOne($sql);
	}
	function getAllLOcation(){
		$db = $this->getAdapter();
		$sql = "SELECT s.`prefix`,s.`id`  FROM `tb_sublocation` AS s WHERE s.`status`=1";
		return $db->fetchAll($sql);
	}
	
	function getAllAdjustStock($search){
		$db = $this->getAdapter();
		$sql ="SELECT 
				   adj.id,
				  (SELECT sl.name FROM `tb_sublocation` AS sl WHERE sl.id=adj.`branch_id`) AS branch,
				  code,
				  note,
				  create_date,
				  (SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id=adj.`user_id`) AS `user`,
				  (select name_kh from tb_view where type=5 and key_code = adj.status) as status
				FROM
				  `tb_product_adjust` AS adj  
				WHERE 
					1
			";
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
 		if($search["ad_search"]!=""){
 			$s_where=array();
 			$s_search = addslashes(trim($search['ad_search']));
 			$s_where[]= " code LIKE '%{$s_search}%'";
 			$s_where[]= " note LIKE '%{$s_search}%'";
 			$s_where[]= " (SELECT sl.name FROM `tb_sublocation` AS sl WHERE sl.id=adj.`branch_id`) LIKE '%{$s_search}%'";
 			$where.=' AND ('.implode(' OR ', $s_where).')';
 		}
 		if(!empty($search['branch'])){
 			$where .= " and branch_id = ".$search['branch'];
 		}
		return $db->fetchAll($sql.$where);
	}
	
	function getAllAdjustStockDetail($search){
		$db = $this->getAdapter();
		$sql ="SELECT
					adj.id,
					(SELECT sl.name FROM `tb_sublocation` AS sl WHERE sl.id=adj.`branch_id`) AS branch,
					adj.code,
					adj.note,
					adj.create_date,
					(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id=adj.`user_id`) AS `user`,
					(select name_kh from tb_view where type=5 and key_code = adj.status) as status,
					adjd.*,
					(select item_name from tb_product as p where p.id = adjd.pro_id) as pro_name,
					(select item_code from tb_product as p where p.id = adjd.pro_id) as pro_code,
					(select qty_perunit from tb_product as p where p.id = adjd.pro_id) as qty_perunit,
					(select unit_label from tb_product as p where p.id = adjd.pro_id) as unit_label,
					(select name from tb_measure as m where m.id = (select measure_id from tb_product as p where p.id = adjd.pro_id)) as measure_name
				FROM
					`tb_product_adjust` AS adj,
					tb_product_adjust_detail as adjd
				WHERE
					adj.id = adjd.adj_id
			";
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
	
		if($search["ad_search"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($search['ad_search']));
			$s_where[]= " adj.code LIKE '%{$s_search}%'";
			$s_where[]= " adj.note LIKE '%{$s_search}%'";
			$s_where[]= " (SELECT sl.name FROM `tb_sublocation` AS sl WHERE sl.id=adj.`branch_id`) LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if(!empty($search['branch'])){
			$where .= " and branch_id = ".$search['branch'];
		}
		if(!empty($search['pro_id'])){
			$where .= " and adjd.pro_id = ".$search['pro_id'];
		}
		return $db->fetchAll($sql.$where);
	}
	
	function getAdjustStockById($id){
		$db = $this->getAdapter();
		$sql ="SELECT
					adj.id,
					(SELECT sl.name FROM `tb_sublocation` AS sl WHERE sl.id=adj.`branch_id`) AS branch,
					adj.code,
					adj.note,
					adj.create_date,
					(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id=adj.`user_id`) AS `user`,
					(select name_kh from tb_view where type=5 and key_code = adj.status) as status,
					adjd.*,
					(select item_name from tb_product as p where p.id = adjd.pro_id) as pro_name,
					(select item_code from tb_product as p where p.id = adjd.pro_id) as pro_code,
					(select qty_perunit from tb_product as p where p.id = adjd.pro_id) as qty_perunit,
					(select unit_label from tb_product as p where p.id = adjd.pro_id) as unit_label,
					(select name from tb_measure as m where m.id = (select measure_id from tb_product as p where p.id = adjd.pro_id)) as measure_name
				FROM
					`tb_product_adjust` AS adj,
					tb_product_adjust_detail as adjd
				WHERE
					adj.id = adjd.adj_id
					and adjd.adj_id = $id
			";
		//echo $sql;exit();
		return $db->fetchAll($sql);
	}
	
}

?>