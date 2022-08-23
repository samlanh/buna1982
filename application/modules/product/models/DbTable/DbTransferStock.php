<?php

class Product_Model_DbTable_DbTransferStock extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_product_transfer";
	public function setName($name)
	{
		$this->_name=$name;
	}
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
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
 		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	public function addTransfer($data){
		$db = $this->getAdapter();
		 
		$db->beginTransaction();
		try{
			$arr = array(
				'from_location'	=>$data['from_loc'],	
				'to_location'	=>$data['to_loc'],
				'note'			=>$data['note'],
				'create_date'	=>date("Y-m-d H:i:s"),
				'user_id'		=>$this->getUserId(),
			);
			$id = $this->insert($arr);
			
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$from_loc = $this->getProductQtyById($data["pro_id_".$i],$data["from_loc"]);
					$to_loc = $this->getProductQtyById($data["pro_id_".$i],$data["to_loc"]);
					
					if(!empty($from_loc)){
						$arr_from = array(
								'qty'	=> $from_loc["qty"] - $data["total_qty_".$i],
						);
						$this->_name="tb_prolocation";
						$where = " pro_id = ".$data["pro_id_".$i]." and location_id = ".$data["from_loc"];
						$this->update($arr_from, $where);
					}
					
					if(!empty($to_loc)){
						$arr_to = array(
								'qty'	=> $to_loc["qty"] + $data["total_qty_".$i],
						);
						$this->_name="tb_prolocation";
						$where = " pro_id = ".$data["pro_id_".$i]." and location_id = ".$data["to_loc"];
						$this->update($arr_to, $where);
					}else{
						$arr_new = array(
								'location_id'		=>	$data["to_loc"],
								'pro_id'			=>	$data["pro_id_".$i],
								'qty'				=>	$data["total_qty_".$i],
								'last_mod_date'		=>	date('Y-m-d'),
								'user_id'			=>	$this->getUserId(),
								'last_mod_userid'	=>	$this->getUserId(),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr_new);
					}
					
					$array = array(
							'transfer_id'=>	$id,
							'pro_id'	=>	$data["pro_id_".$i],
							'qty'		=>	$data["qty_".$i],
							'qty_detail'=>	$data["qty_detail_".$i],
							'total_qty'	=>	$data["total_qty_".$i],
							'note'		=>	$data["note_".$i],
					);
					$this->_name="tb_product_transfer_detail";
					$this->insert($array);
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	
	public function updateTransfer($data,$id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$transfer = $this->getTransferProductById($id);
			$transfer_detail = $this->getTransferProductDetailById($id);
			if(!empty($transfer) && !empty($transfer_detail)){
				foreach ($transfer_detail as $detail){
					$from_loc = $this->getProductQtyById($detail["pro_id"],$transfer["from_location"]);
					$to_loc = $this->getProductQtyById($detail["pro_id"],$transfer["to_location"]);
					if(!empty($from_loc)){
						$arr_from = array(
								'qty'	=> $from_loc["qty"] + $detail["total_qty"],
						);
						$this->_name="tb_prolocation";
						$where = " pro_id = ".$detail["pro_id"]." and location_id = ".$transfer["from_location"];
						$this->update($arr_from, $where);
					}
					
					
					if(!empty($to_loc)){
						$arr_to = array(
								'qty'	=> $to_loc["qty"] - $detail["total_qty"],
						);
						$this->_name="tb_prolocation";
						$where = " pro_id = ".$detail["pro_id"]." and location_id = ".$transfer["to_location"];
						$this->update($arr_to, $where);
					}
				}
				
			}
			
			if($data['status']==0){
				$this->_name="tb_product_transfer";
				$arr = array(
						'status'	=>$data['status'],
						'user_id'	=>$this->getUserId(),
				);
				$where = " id = $id";
				$this->update($arr, $where);
				
				$db->commit();
				return 0;
			}
		////////////////////////////////////////////////////////////////////////////////////////////////////
			
			
			
			$this->_name="tb_product_transfer";
			$arr = array(
					'from_location'	=>$data['from_loc'],
					'to_location'	=>$data['to_loc'],
					'note'			=>$data['note'],
					'user_id'		=>$this->getUserId(),
			);
			$where = " id = $id";
			$this->update($arr, $where);

			$this->_name="tb_product_transfer_detail";
			$where1 = "transfer_id=$id";
			$this->delete($where1);
			
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$from_loc = $this->getProductQtyById($data["pro_id_".$i],$data["from_loc"]);
					$to_loc = $this->getProductQtyById($data["pro_id_".$i],$data["to_loc"]);
						
					if(!empty($from_loc)){
						$arr_from = array(
								'qty'	=> $from_loc["qty"] - $data["total_qty_".$i],
						);
						$this->_name="tb_prolocation";
						$where = " pro_id = ".$data["pro_id_".$i]." and location_id = ".$data["from_loc"];
						$this->update($arr_from, $where);
					}
						
					if(!empty($to_loc)){
						$arr_to = array(
								'qty'	=> $to_loc["qty"] + $data["total_qty_".$i],
						);
						$this->_name="tb_prolocation";
						$where = " pro_id = ".$data["pro_id_".$i]." and location_id = ".$data["to_loc"];
						$this->update($arr_to, $where);
					}else{
						$arr_new = array(
								'location_id'		=>	$data["to_loc"],
								'pro_id'			=>	$data["pro_id_".$i],
								'qty'				=>	$data["total_qty_".$i],
								'last_mod_date'		=>	date('Y-m-d'),
								'user_id'			=>	$this->getUserId(),
								'last_mod_userid'	=>	$this->getUserId(),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr_new);
					}
						
					$array = array(
							'transfer_id'=>	$id,
							'pro_id'	=>	$data["pro_id_".$i],
							'qty'		=>	$data["qty_".$i],
							'qty_detail'=>	$data["qty_detail_".$i],
							'total_qty'	=>	$data["total_qty_".$i],
							'note'		=>	$data["note_".$i],
					);
					$this->_name="tb_product_transfer_detail";
					$this->insert($array);
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	
	
	function getProductQtyById($id,$location){
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
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
				  pl.`qty`,
				  pl.damaged_qty
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl
				WHERE 
					p.`id` = pl.`pro_id` 
					AND p.`id`=$id 
					AND pl.`location_id` = $location 
					and p.is_service = 0 
				limit 1	
		";
		
		return $db->fetchRow($sql);
	}
	
	function getProductByBranch($branch_id){
		$db=$this->getAdapter();
		$sql = "SELECT 
					p.id,
					p.item_name,
					p.item_name as name,
					p.item_code,
					p.item_code as code
				FROM 
					tb_product AS p,
					tb_prolocation AS pl
				WHERE 
					p.id = pl.pro_id
					AND pl.location_id = $branch_id 
					and p.is_service = 0 
			";
		$result = $db->fetchAll($sql);
		
		$option="";
		$option .= '<option value="-1">ជ្រើសរើសទំនិញ</option>';
		if($result){
			foreach($result as $value){
				$option .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name']." - ".$value['code'], ENT_QUOTES).'</option>';
			}
		}
		return $option;
	}
	
	function getTransferProductById($id){
		$db=$this->getAdapter();
		$sql = "SELECT
					*
				FROM
					tb_product_transfer
				WHERE
					id = $id
				limit 1	
			";
		return $db->fetchRow($sql);
	}
	function getTransferProductDetailById($id){
		$db=$this->getAdapter();
		$sql = "SELECT
					*,
					(select item_name from tb_product where tb_product.id = pro_id) as pro_name,
					(select item_code from tb_product where tb_product.id = pro_id) as pro_code
				FROM
					tb_product_transfer_detail
				WHERE
					transfer_id = $id
			";
		return $db->fetchAll($sql);
	}
}








