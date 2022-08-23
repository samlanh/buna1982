<?php

class Purchase_Model_DbTable_Dbpayment extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_vendor_payment";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	function getAllReciept($search){
			$db= $this->getAdapter();
			$sql=" SELECT 
						r.id,
						(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id = r.`branch_id` AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
						(select v_name from tb_vendor where tb_vendor.vendor_id = p.vendor_id) as vendor_name,
						p.order_number,
						r.`expense_date`,
						r.payment_type,
						r.`total`,
						r.`paid`,
						r.`balance`,
						(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name ,
						(select name_kh from tb_view where type=5 and key_code = r.status) as status_name 
					FROM 
						`tb_vendor_payment` AS r,
						tb_purchase_order as p
					where 
						p.id = r.purchase_id		 
				";
			
			$from_date =(empty($search['start_date']))? '1': " r.`expense_date` >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " r.`expense_date` <= '".$search['end_date']." 23:59:59'";
			$where = " and ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " r.`total` LIKE '%{$s_search}%'";
				$s_where[] = " r.`paid` LIKE '%{$s_search}%'";
				$s_where[] = " r.`balance` LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['branch']>0){
				$where .= " AND r.`branch_id` = ".$search['branch'];
			}
			if($search['purchase_id']>0){
				$where .= " AND r.purchase_id =".$search['purchase_id'];
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
			return $db->fetchAll($sql.$where.$order);
	}
	public function addPurchasePayment($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$branch_id = empty($data['branch_id'])?1:$data['branch_id'];
			$array=array(
					"branch_id"   	=> 	$branch_id,
					"purchase_id" 	=> 	$data['purchase_id'],
					"payment_type"  => 	$data["payment_type"],//payment by cash/cheque/Credit/Bank Transtransfer
					"bank_name" 	=> 	$data['bank_name'],
					"cheque_number" => 	$data['cheque'],
					"expense_date"  =>  date("Y-m-d",strtotime($data['expense_date'])),
					"remark"        => 	$data['remark'],
					
					"total"         => 	$data['all_total'],
					"paid"          => 	$data['paid'],
					"balance"       => 	$data['balance'],
					
					"user_id"       => 	$this->getUserId(),
					'status'        =>  1,
					'create_date'	=>  date("Y-m-d H:i:s"),
					
			);
			$this->_name="tb_vendor_payment";
			$reciept_id = $this->insert($array); 
			
			if($data['balance']>0){
				$compelted = 0;
			}else{
				$compelted = 1;
			}
			
			$rspurchase = $this->getPurchaseInfo($data['purchase_id']);
			if(!empty($rspurchase)){
				$arr = array(
						'paid'			=>	$rspurchase['paid']+$data['paid'],
						'balance'		=>	$rspurchase['balance']-$data['paid'],
						'is_completed'	=> 	$compelted,
					);
				$this->_name='tb_purchase_order';
				$where = 'id = '.$data['purchase_id'];
				$this->update($arr, $where);
			}
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();exit();
		}
	}
	public function updatePurchasePayment($data,$id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			if($data['status']==0){
				$rs = $this->getPaymentById($id);
				if(!empty($rs)){
					$rspurchase = $this->getPurchaseInfo($data['purchase_id']);
					if(!empty($rspurchase)){
						$arr = array(
								'paid'		=>	$rspurchase['paid']-$data['paid'],
								'balance'	=>	$rspurchase['balance']+$data['paid'],
								'is_completed'	=> 	0,
							);
						$this->_name='tb_purchase_order';
						$where = 'id = '.$data['purchase_id'];
						$this->update($arr, $where);
					}
				}
				
				$this->_name="tb_vendor_payment";
				$array= array(
						'status' =>$data['status'],
				);
				$where1 = "id = $id ";
				$this->update($array,$where1);
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();
		}
	}
	function getPurchaseInfo($purchase_id){
		$db =$this->getAdapter();
		$sql="SELECT * FROM `tb_purchase_order` AS p WHERE p.`id` = $purchase_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getPaymentById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM tb_vendor_payment WHERE id = $id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	
	function getAllVendor(){
		$db = $this->getAdapter();
		$sql=" SELECT id,(select v_name from tb_vendor where tb_vendor.vendor_id = p.vendor_id) as name FROM tb_purchase_order as p WHERE status=1 and p.balance>0 ";
		return $db->fetchAll($sql);
	}
	function getAllPurchaseNo(){ // for form add
		$db = $this->getAdapter();
		$sql=" SELECT id,order_number as name FROM tb_purchase_order WHERE status=1 and balance>0 ";
		return $db->fetchAll($sql);
	}
	
	function getAllPurchase(){ // for search index
		$db = $this->getAdapter();
		$sql=" SELECT 
					id,
					order_number as name,
					(select v_name from tb_vendor where tb_vendor.vendor_id = p.vendor_id) as vendor 
				FROM 
					tb_purchase_order as p
				WHERE 
					status=1 
			";
		return $db->fetchAll($sql);
	}
	
	function getAllInvoicePaymentPurchase($purchase_id,$action){
		$db= $this->getAdapter();
		$status="";
		if($action=="add"){
			$status = " and vp.status=1";
		}
		$sql="select 
					vp.*,
					p.order_number,
					DATE_FORMAT(vp.expense_date, '%d-%m-%Y') AS expense_date,
					(select v_name from tb_vendor as v where v.vendor_id = p.vendor_id) as vendor,
					p.balance as total_payment,
					(select name_kh from tb_view where type=5 and key_code = vp.status) as status_name 
				from 	
		 			tb_vendor_payment as vp,
		 			tb_purchase_order as p
		 		where 
		 			p.id = vp.purchase_id
		 			and vp.purchase_id = $purchase_id	
		 			$status
		";
		 
		return  $db->fetchAll($sql);
	}
	
	function getAllPurchaseNoByBranch($_data){ 
		$db = $this->getAdapter();
		$sql=" SELECT p.id,
					p.order_number as name,
					(SELECT v_name FROM tb_vendor WHERE tb_vendor.vendor_id = p.vendor_id LIMIT 1) AS vendor_name
				FROM tb_purchase_order AS p WHERE p.status=1  ";
		
		if (empty($_data['edit'])){
			$sql.=" AND p.balance>0 ";
		}
		$sql.=" AND p.branch_id = ".$_data['branch_id'];
		$row = $db->fetchAll($sql);
		
		if (!empty($_data['notOpt'])){
			return $row;
		}else{
			$postype = $_data['postype'];
			$option = '<option value="0">'.htmlspecialchars("ជ្រើសរើសលេខបញ្ជាទិញ", ENT_QUOTES).'</option>';
			if ($postype!=1){
				$option = '<option value="0">'.htmlspecialchars("ជ្រើសរើសអ្នកផ្គត់ផ្គង់", ENT_QUOTES).'</option>';
			}
			if(!empty($row)){
				foreach ($row as $rs){
					if ($postype==1){
						$option .= '<option value="'.$rs['id'].'">'.htmlspecialchars($rs['name'], ENT_QUOTES).'</option>';
					}else{
						$option .= '<option value="'.$rs['id'].'">'.htmlspecialchars($rs['vendor_name'], ENT_QUOTES).'</option>';
					}
				}
			}
			return $option;
		}
		
	}
	
}