<?php

class Sales_Model_DbTable_DbPartnerServicepayment extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_receipt";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	function getBranchId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->branch_id;
	}
	
	function getAllPartnerPayment($search){
			$db= $this->getAdapter();
			$sql=" SELECT 
						pp.id,
						(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id = pp.`branch_id` AND status=1 AND name!='' LIMIT 1) AS branch_name,
						(SELECT partner_name FROM `tb_partnerservice` as p WHERE p.id=pp.partner_id) AS partner_name,
						pp.`date_payment`,
						pp.`payment_type`,
						pp.`total_payment`,
						pp.note,
						(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = pp.`user_id`) AS user_name,
						(select name_kh from tb_view where type=5 and key_code = pp.status) as status_name 
					FROM 
						`tb_partnerservice_payment` AS pp 
					where 
						1
			";
			
			$from_date =(empty($search['start_date']))? '1': " pp.`date_payment` >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " pp.`date_payment` <= '".$search['end_date']." 23:59:59'";
			$where = " and ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " pp.`total_payment` LIKE '%{$s_search}%'";
				$s_where[] = " pp.`paid` LIKE '%{$s_search}%'";
				$s_where[] = " pp.`balance` LIKE '%{$s_search}%'";
				$s_where[] = " pp.`note` LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['branch']>0){
				$where .= " AND pp.`branch_id` = ".$search['branch'];
			}
			if($search['sale_order_id']>0){
				$where .= " AND pp.`sale_order_id` = ".$search['sale_order_id'];
			}
			
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			
			$order=" ORDER BY date_payment DESC,id DESC ";
			
			return $db->fetchAll($sql.$where.$order);
	}
	public function addPartnerServicePayment($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$branch_id = empty($data['branch_id'])?1:$data['branch_id'];
			$array=array(
					'branch_id'			=> $branch_id,//$this->getBranchId(),
					'partner_id'		=> $data['partner_id'],
					'payment_type'		=> $data['payment_type'],
					'cheque_number'		=> $data['cheque_number'],
					'bank_name'			=> $data['bank_name'],
					'note'				=> $data['note'],
					
					'date_payment'		=> date("Y-m-d",strtotime($data['date_payment'])),
					'total_payment'		=> $data['total_payment'],
					
					'array_sale_partner_id'	=> $data['array_sale_partner_id'],
					
					'status'			=> 1,
					'create_date'		=> date("Y-m-d H:i:s"),
					'user_id'			=> $this->getUserId(),
			);
			$this->_name="tb_partnerservice_payment";
			$this->insert($array); 
			
			if(!empty($data['array_sale_partner_id'])){
				$ids=explode(',',$data['array_sale_partner_id']);
				foreach ($ids as $i)
				{
					$array= array(
							'is_paid'	=> 1,
					);
					$this->_name='tb_sales_partner_service';
					$where = " id = $i and partner_id = ".$data['partner_id'];
					$this->update($array, $where);
				}
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();exit();
		}
	}
	public function updatePartnerServicePayment($data,$id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		
		//print_r($data);exit();
		try{
			if($data['status']==0){
				//echo "here";exit();
				$rs = $this->getPartnerSerivcePaymentById($id);
				if(!empty($rs['array_sale_partner_id'])){
					$ids=explode(',',$rs['array_sale_partner_id']);
					foreach ($ids as $i)
					{
						$array= array(
								'is_paid'	=> 0,
						);
						$this->_name='tb_sales_partner_service';
						$where = " id = $i and partner_id = ".$data['partner_id'];
						$this->update($array, $where);
					}
				}
				$this->_name="tb_partnerservice_payment";
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
			echo $e->getMessage();exit();
		}
	}
	function getBranchByInvoice($invoice_id){
		$db =$this->getAdapter();
		$sql="SELECT * FROM `tb_invoice` AS i WHERE i.`id` = $invoice_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getSaleById($sale_id){
		$db =$this->getAdapter();
		$sql="SELECT * FROM `tb_sales_order` AS i WHERE i.`id` = $sale_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getPartnerPaymentById($id){
		$db = $this->getAdapter();
		$sql="SELECT 
					pp.*,
					p.partner_name,
					p.tel as phone,
					p.addresss as address,
					(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = pp.`user_id`) AS user_name
				FROM 
					tb_partnerservice as p,
					tb_partnerservice_payment as pp
				WHERE 
					p.id = pp.partner_id
					and pp.id = $id 
				LIMIT 1 
			";
		return $db->fetchRow($sql);
	}
	
	function getSalePartnerServiceById($id){
		$db = $this->getAdapter();
		$sql="SELECT
					sps.id,
					s.sale_no,
					s.place_bun,
					(select item_name from tb_product as p where p.id = sps.service_id ) as service_name,
					sps.price,
					sps.note
				FROM
					tb_sales_partner_service as sps,
					tb_sales_order as s
				WHERE
					s.id = sps.saleorder_id
					and sps.id = $id
				LIMIT 1
			";
		return $db->fetchRow($sql);
	}
	
	function getRecieptDetail($reciept_id){
		$db= $this->getAdapter();
		$sql="SELECT 
					*
				FROM 
					tb_receipt AS d 
				WHERE 
					d.id = $reciept_id
				limit 1	
			";
		return $db->fetchRow($sql);
	}
	
	function delettePayment($id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			$rs = $this->getRecieptDetail($id);
			if(!empty($rs)){
				$rssale = $this->getSaleById($rs['invoice_id']);
				if(!empty($rssale)){
					$data= array(
						'balance_after'=>$rssale['balance_after']+$rs['paid'],
						'paid'	=>$rssale['paid']-$rs['paid'],
					);
					$this->_name="tb_sales_order";
					$where = " id = ".$rs['invoice_id'];
					$this->update($data, $where);
				}
			}
			
			$this->_name="tb_receipt";
			$where = "id =  ".$id;
			$this->delete($where);
			
			$db->commit();
		}Catch(Exception $e){
			$db->rollBack();
		}	
	}
	
	function getSaleInvoice(){
		$db = $this->getAdapter();
		$sql = "SELECT id,sale_no as name FROM tb_sales_order WHERE partner_service_balance>0 and status=1 ";
		return $db->fetchAll($sql);
	}
	
	function getPartnerSerivcePayment($sale_id,$action){
		$db = $this->getAdapter();
		$status="";
		if($action=="add"){
			$status=" and pp.status=1";
		}
		$sql = "SELECT 
					pp.*,
					DATE_FORMAT(pp.date_payment, '%d-%M-%Y') AS receipt_date,
					s.partner_service_balance ,
					(select name_kh from tb_view where type=5 and key_code = pp.status) as status_name
				FROM 
					tb_partnerservice_payment as pp,
					tb_sales_order as s 
				WHERE 
					s.id = pp.sale_order_id 
					and sale_order_id=$sale_id
					$status
			";
		return $db->fetchAll($sql);
	}
	
	function getAllPartner(){
		$db = $this->getAdapter();
		$sql = "SELECT id,partner_name as name,tel FROM tb_partnerservice where status=1 ";
		return $db->fetchAll($sql);
	}
	
	function getPartnerPaymentBalance(){
		$db = $this->getAdapter();
		$sql = "SELECT id,sale_no as name FROM tb_sales_order where 1 ";
		return $db->fetchAll($sql);
	}
	
	function getPartnerSerivcePaymentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_partnerservice_payment where id = $id ";
		$dbg = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbg->getAccessPermission('branch_id');
		$sql.=" LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getAllService($partner_id,$action,$branch_id=1){
		$db = $this->getAdapter();
		$sql = "SELECT 
					sps.*,
					s.date_sold,
					(select item_name from tb_product as p where p.id = sps.service_id ) as service_name,
					s.sale_no as invoice_no,
					s.place_bun 
				FROM 
					tb_sales_partner_service as sps,
					tb_sales_order as s 
				where 
					s.id = sps.saleorder_id 
					and partner_id = $partner_id 
					and is_paid=0 
					and price>0
			";
		$sql.=" AND s.branch_id = $branch_id ";
		return $db->fetchAll($sql);
	}
	function getPaidService($id){
		$db = $this->getAdapter();
		$sql = "SELECT
					sps.*,
					(select item_name from tb_product as p where p.id = sps.service_id ) as service_name,
					s.sale_no as invoice_no,
					s.place_bun
				FROM
					tb_sales_partner_service as sps,
					tb_sales_order as s
				where
					s.id = sps.saleorder_id
					and sps.id = $id
				limit 1	
			";
		return $db->fetchRow($sql);
	}
	
	
}