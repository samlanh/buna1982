<?php

class Purchase_Model_DbTable_DbPurchaseOrder extends Zend_Db_Table_Abstract
{	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	function getBranchId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->branch_id;
	}
	
	function getAllPurchaseOrder($search){//new
		$db= $this->getAdapter();
		$sql=" SELECT 
					id,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
					(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=tb_purchase_order.vendor_id LIMIT 1 ) AS vendor_name,
					order_number,
					DATE_FORMAT(date_order, '%d-%m-%Y') AS date_order,
					total_payment,
					paid,
					balance,
					(SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1) As purchase_status,
					(SELECT name_en FROM `tb_view` WHERE key_code =tb_purchase_order.status AND type=5 LIMIT 1),
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = user_mod LIMIT 1 ) AS user_name
				FROM 
					`tb_purchase_order` 
			";
		
		$from_date =(empty($search['start_date']))? '1': " date_order >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_order <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " order_number LIKE '%{$s_search}%'";
			$s_where[] = " net_total LIKE '%{$s_search}%'";
			$s_where[] = " paid LIKE '%{$s_search}%'";
			$s_where[] = " balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['suppliyer_id']>0){
			$where .= " AND vendor_id = ".$search['suppliyer_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND branch_id = ".$search['branch_id'];
		}
		if($search['status_paid']>0){
			if($search['status_paid']==1){
				$where .= " AND balance <=0 ";
			}
			elseif($search['status_paid']==2){
				$where .= " AND balance >0 ";
			}
			
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getProductCostAndQty($pro_id){
		$db = $this->getAdapter();
		$sql="SELECT 
					p.id,
					p.`price`,
					sum(pl.`qty`) as qty 
				FROM 
					`tb_product` AS p ,
					`tb_prolocation` AS pl 
				WHERE 
					p.id=pl.`pro_id` 
					AND p.id=$pro_id 
			";
		return $db->fetchRow($sql);
	}
	
	
	public function addPurchaseOrder($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$info_purchase_order=array(
					"vendor_id"      => $data['v_name'],
					"branch_id"      => $data["LocationId"],
					"order_number"   => $db_global->getPurchaseNumber($data["LocationId"]),
					
					"date_order"     => date("Y-m-d",strtotime($data['order_date'])),
					"remark"         => $data['remark'],
					"purchase_status"=> $data['status'],
					
					"net_total"      => $data['totalAmoun'],
					"discount_value" => $data['dis_value'],
					"total_payment"  => $data['all_totalpayment'],
					"paid"           => $data['paid'],
					"balance"        => $data['remain'],
					
					"user_id"        => $this->getUserId(),
					"user_mod"       => $this->getUserId(),
					"create_date"    => date("Y-m-d H:i:s"),
					"status"         => 1,
					
					'is_completed'	 => ($data['remain']==0)?1:0,
			);
			$this->_name="tb_purchase_order";
			$purchase_id = $this->insert($info_purchase_order);
			unset($info_purchase_order);
			
			//if($data['paid']>0){
				$info_purchase_order=array(
						"branch_id"   	=> $data["LocationId"],
						"purchase_id"   => $purchase_id,
						"payment_type"  => "Cash", //payment by cash/paypal/cheque
						"expense_date"  => date("Y-m-d",strtotime($data["order_date"])),
						
						"total"         => $data['all_totalpayment'],
						"paid"          => $data['paid'],
						"balance"       => $data['remain'],
						
						"remark"        => $data['remark'],
						"user_id"       => $this->getUserId(),
						'status'        => 1,
						"create_date"   => date("Y-m-d H:i:s"),
				);
				$this->_name="tb_vendor_payment";
				$reciept_id = $this->insert($info_purchase_order);
			//}

			$ids=explode(',',$data['identity']);
			$locationid=$data['LocationId'];
			foreach ($ids as $i)
			{
				$rsproduct = $this->getProductCostAndQty($data['pro_'.$i]); 
				$cost_avg = (($rsproduct['qty']*$rsproduct['price'])+($data['price'.$i]*$data['qty'.$i])) / ($rsproduct['qty']+$data['qty'.$i]);
				$array=array(
						'price'=>$cost_avg
				);
				$this->_name="tb_product";
				$where = " id= ".$rsproduct['id'];
				$this->update($array, $where);
				
				$data_item= array(
						'purchase_id' => 	$purchase_id,
						'pro_id'	  => 	$data['pro_'.$i],
                        'qty_unit' 	  => 	$data['qty_unit_'.$i],
						'qty_detail'  => 	$data['qty_per_unit_'.$i],
						'qty_order'	  => 	$data['qty'.$i],
						'price'		  => 	$data['price'.$i],
						'disc_value'  => 	$data['dis_value'.$i],
						'sub_total'	  => 	$data['total'.$i],
				);
				$this->_name='tb_purchase_order_item';
				$this->insert($data_item);
	
				if($data["status"]==5 OR $data["status"]==4){
					$rows=$db_global->productLocationInventory($data['pro_'.$i], $locationid);// action add //check stock product location  
					if($rows)
					{
						if($data["status"]==4 OR $data["status"]==5){
							$datatostock = array(
									'qty'   			=> 	$rows["qty"]+$data['qty'.$i],
									'last_mod_date'		=>	date("Y-m-d"),
									'last_mod_userid'	=>	$this->getUserId()
								);
							$this->_name="tb_prolocation";
							$where=" id = ".$rows['id'];
							$this->update($datatostock, $where);
						}
					}
				}
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();exit();
		}
	}
	public function updatePurchaseOrder($data,$id)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$row_oldhistory = $this->getPurchaseById($id);// get prev.item
			$po_item = $this->getPurchaseDetailById($id);//get old item detail
			if(!empty($po_item)){
				foreach ($po_item as $rsitem){
					$rows=$db_global->productLocationInventory($rsitem['pro_id'], $row_oldhistory['branch_id']);// action update //check stock product location
					if($rows)
					{
						$datatostock   = array(
								'qty'   => $rows["qty"]-$rsitem['qty_order'],
						);
						$this->_name="tb_prolocation";
						$where=" id = ".$rows['id'];
						$this->update($datatostock, $where);
					}
				}
			}
				
			$this->_name='tb_purchase_order_item';
			$where =" purchase_id=".$id;
			$this->delete($where);
				
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
	
			$info_purchase_order=array(
					"vendor_id"      => $data['v_name'],
					"branch_id"      => $data["LocationId"],
					"order_number"   => $data["purchase_num"],
					
					"date_order"     => date("Y-m-d",strtotime($data['order_date'])),
					"remark"         => $data['remark'],
					"purchase_status"=> $data['status'],
					
					"net_total"      => $data['totalAmoun'],
					"discount_value" => $data['dis_value'],
					"total_payment"  => $data['all_totalpayment'],
					"paid"           => $data['paid'],
					"balance"        => $data['remain'],
					
					"user_id"        => $this->getUserId(),
					"user_mod"       => $this->getUserId(),
					"create_date"    => date("Y-m-d H:i:s"),
					"status"         => 1,
			);
			
			$this->_name="tb_purchase_order";
			$where=" id=$id ";
			$this->update($info_purchase_order, $where);
				
			$sql1="select id from tb_vendor_payment where purchase_id = $id order by id ASC limit 1 ";
			$result = $db->fetchOne($sql1);
			if(!empty($result)){
				$vendor_payment=array(
						"total"         => $data['all_totalpayment'],
						"paid"          => $data['paid'],
						"balance"       => $data['remain'],
						"user_id"       => $this->getUserId(),
				);
				$this->_name="tb_vendor_payment";
				$where1 = " id = $result ";
				$this->update($vendor_payment, $where1);
			}
			
			
			$ids=explode(',',$data['identity']);
			$locationid=$data['LocationId'];
			if(!empty($data['identity']))foreach ($ids as $i)
			{
				$data_item= array(
						'purchase_id' => 	$id,
						'pro_id'	  => 	$data['pro_'.$i],
                        'qty_unit' 	  => 	$data['qty_unit_'.$i],
						'qty_detail'  => 	$data['qty_per_unit_'.$i],
						'qty_order'	  => 	$data['qty'.$i],
						'price'		  => 	$data['price'.$i],
						'disc_value'  => 	$data['dis_value'.$i],
						'sub_total'	  => 	$data['total'.$i],
				);
				$this->_name='tb_purchase_order_item';
				$this->insert($data_item);
	
				if($data["status"]==5 OR $data["status"]==4){
					$rows=$db_global->productLocationInventory($data['pro_'.$i], $locationid);//check stock product location
					if($rows)
					{
						$arr  = array(
								'qty'   =>$rows["qty"]+$data['qty'.$i],
						);
						$this->_name="tb_prolocation";
						$where=" id = ".$rows['id'];
						$this->update($arr, $where);
					}
						
				}
			}		
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();exit();
		}
	}
	public function getPurchaseID($id){
		$db = $this->getAdapter();
		$sql = "SELECT CONCAT(p.item_name,'(',p.item_code,' )') AS item_name , p.qty_perunit,od.order_id, od.pro_id, od.qty_order,
		od.price, od.total_befor, od.disc_type,	od.disc_value, od.sub_total, od.remark 
		FROM tb_purchase_order_item AS od
		INNER JOIN tb_product AS p ON p.pro_id=od.pro_id WHERE od.order_id=".$id;
		$row = $db->fetchAll($sql);
		return $row;
	}
	
	public function getPurchaseById($id){//just new 
		$db=$this->getAdapter();
		$sql = "SELECT p.* FROM `tb_purchase_order` AS p WHERE id=$id LIMIT 1 ";
		$rows=$db->fetchRow($sql);
		return $rows;
	}
	public function getPurchaseDetailById($id){//just new
		$db=$this->getAdapter();
		$sql = "SELECT poi.*,p.item_name as pro_name,p.item_code FROM `tb_purchase_order_item` as poi,tb_product as p WHERE p.id=poi.pro_id and purchase_id=$id ";
		$rows=$db->fetchAll($sql);
		return $rows;
	}
	public function recieved_info($order_id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_recieve_order WHERE order_id=".$order_id." LIMIT 1";		
		$row =$db->fetchRow($sql);
		return $row;
	}
	//for get left order address change form showsaleorder to below
	public function showPurchaseOrder(){
		$db= $this->getAdapter();
		$sql = "SELECT p.order_id, p.order, p.date_order, p.status, v.v_name, p.all_total,p.paid,p.balance
		FROM tb_purchase_order AS p  INNER JOIN tb_vendor AS v ON v.vendor_id=p.vendor_id";
		$row=$db->fetchAll($sql);
		return $row;
		
	}
	public function getVendorInfo($post){
		$db=$this->getAdapter();
		$sql="SELECT contact_name,phone, add_name AS address 
		FROM tb_vendor WHERE vendor_id = ".$post['vendor_id']." LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	
}