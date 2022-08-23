<?php

class Product_Model_DbTable_DbAdjustStock extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_product_adjust";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
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
	public function add($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$array = array(
						'branch_id'		=>	$data["branch_id"],
						'code'			=>	$data["code"],
						'note'			=>	$data["note"],
						'user_id'		=>	$this->getUserId(),
						'create_date'	=>	date("Y-m-d H:i:s"),
					);
			$id = $this->insert($array);
			
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr = array(
						'adj_id'		=>	$id,
						'pro_id'		=>	$data["pro_id_".$i],
						'cur_qty'		=>	$data["current_qty_".$i],
						'qty_unit'		=>	$data["qty_unit_".$i],
						'qty_detail'	=>	$data["qty_detail_".$i],
						'total_qty'		=>	$data["total_qty_".$i],
						'qty_remain'	=>	$data["qty_remain_".$i],
						'remark'		=>	$data["remark_".$i],
					);
					$this->_name="tb_product_adjust_detail";
					$this->insert($arr);
	
					$arr_p = array(
							'qty'	=>	$data["qty_remain_".$i],
					);
					$this->_name="tb_prolocation";
					$where = "pro_id = ".$data["pro_id_".$i]." and location_id=".$data["branch_id"];
					$this->update($arr_p, $where);
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	public function edit($data,$id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$array = array(
					'branch_id'		=>	$data["branch_id"],
					'code'			=>	$data["code"],
					'note'			=>	$data["note"],
					'user_id'		=>	$this->getUserId(),
					'status'		=>	$data["status"],
			);
			$this->_name = "tb_product_adjust";
			$where = " id = $id";
			$this->update($array, $where);
			
			// update adjust stock back
			$rs = $this->getAdjustByID($id);
			$row_detail = $this->getAdjustDetailByID($id);
			if(!empty($row_detail)){foreach ($row_detail as $row){
				$sql="select id,qty from tb_prolocation where pro_id = ".$row["pro_id"]." and location_id=".$rs["branch_id"];
				$qty = $db->fetchRow($sql);
				if(!empty($qty)){
					$arr_p = array(
						'qty'	=>	$qty['qty'] + $row['total_qty'],
					);
					$this->_name="tb_prolocation";
					$where=" id = ".$qty['id'];
					$this->update($arr_p, $where);
				}
			}}
			
			if($data['status']==1){
				// delete detail old record
				$this->_name="tb_product_adjust_detail";
				$where1 = " adj_id = $id";
				$this->delete($where1);
					
				if(!empty($data['identity'])){
					$identitys = explode(',',$data['identity']);
					foreach($identitys as $i)
					{
						$arr = array(
								'adj_id'		=>	$id,
								'pro_id'		=>	$data["pro_id_".$i],
								'cur_qty'		=>	$data["current_qty_".$i],
								'qty_unit'		=>	$data["qty_unit_".$i],
								'qty_detail'	=>	$data["qty_detail_".$i],
								'total_qty'		=>	$data["total_qty_".$i],
								'qty_remain'	=>	$data["qty_remain_".$i],
								'remark'		=>	$data["remark_".$i],
						);
						$this->_name="tb_product_adjust_detail";
						$this->insert($arr);
		
						$sql="select id,qty from tb_prolocation where pro_id = ".$data["pro_id_".$i]." and location_id=".$data["branch_id"];
						$qty = $db->fetchRow($sql);
						if(!empty($qty)){
							$arr_pro = array(
								'qty'	=>	$qty['qty'] - $data['total_qty_'.$i],
							);
							$this->_name="tb_prolocation";
							$where=" id = ".$qty['id'];
							$this->update($arr_pro, $where);
						}
					}
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	function getProductName(){
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`id`,
				  p.`item_name` ,
				  p.`item_code`,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id` limit 1) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id` limit 1) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.key_code=p.`color_id` and type=4 limit 1) AS color
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl 
				WHERE 
					p.`id` = pl.`pro_id` 
					AND p.status=1 
					and p.is_service=0
		";
		//$location = $db_globle->getAccessPermission('pl.`location_id`');
		return $db->fetchAll($sql);
	}
	function getProductById($id){
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$db = $this->getAdapter();
		$sql = "SELECT
				  p.`id`,
				  p.`item_name` ,
				  p.`qty_perunit` ,
				  p.`item_code`,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size,
				  pl.`qty`
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl
				WHERE p.`id` = pl.`pro_id` AND p.`id`=$id ";
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		return $db->fetchRow($sql.$location);
	}
	
	function getProductQtyById($pro_id,$branch_id){
		$db = $this->getAdapter();
		$sql = "SELECT
				  p.`id`,
				  p.`item_name` ,
				  p.`qty_perunit` ,
				  p.`item_code`,
				  p.`unit_label`,
				  (SELECT m.`name` FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
				  pl.`qty`,
				  pl.damaged_qty
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl
				WHERE p.`id` = pl.`pro_id` AND p.`id`=$pro_id AND pl.`location_id` = $branch_id ";
		
		return $db->fetchRow($sql);
	}
	function getAdjustByID($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_product_adjust where id = $id";
		return $db->fetchRow($sql);
	}
	function getAdjustDetailByID($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_product_adjust_detail where adj_id = $id";
		return $db->fetchAll($sql);
	}
}



