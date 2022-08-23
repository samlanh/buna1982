<?php

class Mong_Model_DbTable_DbConstructorPayment extends Zend_Db_Table_Abstract
{
protected $_name="tb_receipt";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	function getAllConstructorPayment($search){
			$db= $this->getAdapter();
			$sql=" SELECT 
						cp.id,
						(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id = cp.`branch_id` AND status=1 AND name!='' LIMIT 1) AS branch_name,
						(SELECT invoice_no FROM tb_mong WHERE tb_mong.id=cp.mong_id) AS invoice_no,
						(SELECT place_bun FROM tb_mong WHERE tb_mong.id=cp.mong_id) AS place_bun,
						cp.`date_payment`,
						cp.`payment_type`,
						cp.`total_payment`,
						cp.`paid`,
						cp.`balance`,
						cp.note,
						(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = cp.`user_id`) AS user_name,
						(select name_kh from tb_view where type=5 and key_code = cp.status) as status_name 
					FROM 
						`tb_mong_constructor_payment` AS cp 
					where 
						cp.status=1
			";
			
			$from_date =(empty($search['start_date']))? '1': " cp.`create_date` >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " cp.`create_date` <= '".$search['end_date']." 23:59:59'";
			$where = " and ".$from_date." AND ".$to_date;
			if(!empty($search['ad_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['ad_search']));
				$s_where[] = " cp.`total_payment` LIKE '%{$s_search}%'";
				$s_where[] = " cp.`payment_type` LIKE '%{$s_search}%'";
				$s_where[] = " cp.`paid` LIKE '%{$s_search}%'";
				$s_where[] = " cp.`balance` LIKE '%{$s_search}%'";
				$s_where[] = " cp.`note` LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['branch']>0){
				$where .= " AND cp.`branch_id` = ".$search['branch'];
			}
			if($search['mong_id']>0){
				$where .= " AND cp.`mong_id` = ".$search['mong_id'];
			}
			
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			
			$order=" ORDER BY cp.id DESC ";
			
			return $db->fetchAll($sql.$where.$order);
	}
	public function addConstructorPayment($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			$branch_id = empty($data['branch'])?1:$data['branch'];
			$array=array(
					'branch_id'			=> $branch_id,
					'mong_id'			=> $data['mong_id'],
					'payment_type'		=> $data['payment_type'],
					'cheque_number'		=> $data['cheque_number'],
					'bank_name'			=> $data['bank_name'],
					'note'				=> $data['note'],
					
					'date_payment'		=> date("Y-m-d",strtotime($data['date_payment'])),
						
					'total_payment'		=> $data['total_payment'],
					'paid'				=> $data['paid'],
					'balance'			=> $data['balance'],
					
					'status'			=> 1,
					'create_date'		=> date("Y-m-d H:i:s"),
					'user_id'			=> $this->getUserId(),
			);
			$this->_name="tb_mong_constructor_payment";
			$this->insert($array); 
			
			
			$sql="select * from tb_mong where id = ".$data['mong_id'];
			$row_mong = $db->fetchRow($sql);
			
			if(!empty($row_mong)){
				$balance = $row_mong['constructor_balance'] - $data['paid'];
				$paid = $row_mong['constructor_paid'] + $data['paid'];
			}
			
			$arr = array(
					'constructor_balance'	=> $balance,
					'constructor_paid'		=> $paid,
			);
			$where = " id = ".$row_mong['id'];
			$this->_name="tb_mong";
			$this->update($arr, $where);
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();exit();
		}
	}
	public function updateConstructorPayment($data,$id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			if($data['status']==0){
				$rs = $this->getConstructorPaymentById($id);
				if(!empty($rs)){
					$rssale = $this->getMongById($rs['mong_id']);
					if(!empty($rssale)){
						$arr= array(
								'constructor_balance'	=>$rssale['constructor_balance']+$rs['paid'],
								'constructor_paid'		=>$rssale['constructor_paid']-$rs['paid'],
						);
						$this->_name="tb_mong";
						$where = " id = ".$rs['mong_id'];
						$this->update($arr, $where);
					}
				}
				$this->_name="tb_mong_constructor_payment";
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
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getBranchByInvoice($invoice_id){
		$db =$this->getAdapter();
		$sql="SELECT * FROM `tb_invoice` AS i WHERE i.`id` = $invoice_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getMongById($mong_id){
		$db =$this->getAdapter();
		$sql="SELECT * FROM `tb_mong` WHERE `id` = $mong_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getRecieptById($id){
		$db = $this->getAdapter();
		$sql="SELECT 
					r.*,
					(select invoice_no from tb_mong where tb_mong.id = r.invoice_id) as invoice,
					c.cust_name AS customer_name,
					c.phone contact_phone,
					c.address AS address,
					(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name
				FROM 
					tb_receipt as r,
					tb_customer as c
				WHERE 
					c.id = r.customer_id
					and r.id = $id 
				LIMIT 1 
			";
		return $db->fetchRow($sql);
	}
	
	function getConstructorPaymentById($id){
		$db= $this->getAdapter();
		$sql="SELECT 
					*
				FROM 
					tb_mong_constructor_payment
				WHERE 
					id = $id
			";
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbg->getAccessPermission('branch_id');
		$sql.=" LIMIT 1	";
		
		return $db->fetchRow($sql);
	}
	
	function delettePayment($id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			$rs = $this->getRecieptDetail($id);
			if(!empty($rs)){
				$rssale = $this->getMongById($rs['invoice_id']);
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
	
	function getMongInvoice(){
		$db = $this->getAdapter();
		$sql = "SELECT id,CONCAT(invoice_no,'-',(select cust_name from tb_customer where tb_customer.id=customer_id)) as name FROM tb_mong WHERE constructor_balance>0 and status=1 ";
		return $db->fetchAll($sql);
	}
	function getConstructorInvoice(){
		$db = $this->getAdapter();
		$sql = "SELECT id,(select name from tb_constructor where tb_constructor.id = constructor) as name FROM tb_mong WHERE constructor_balance>0 and status=1 ";
		return $db->fetchAll($sql);
	}
	
	function getConstructorPayment($mong_id,$action){
		$db = $this->getAdapter();
		$status="";
		if($action=="add"){
			$status = " and mp.status=1";
		}
		$sql = "SELECT 
					mp.*,
					DATE_FORMAT(mp.date_payment, '%d-%M-%Y') AS receipt_date,
					m.constructor_balance,
					(select name_kh from tb_view where type=5 and key_code = mp.status) as status_name 
				FROM 
					tb_mong_constructor_payment AS mp,
					tb_mong AS m 
				WHERE 
					m.id = mp.mong_id 
					AND mp.mong_id=$mong_id
					$status
			";
		return $db->fetchAll($sql);
	}
	
	function getPartnerPaymentBalance(){
		$db = $this->getAdapter();
		$sql = "SELECT id,invoice_no as name FROM tb_mong where 1 ";
		return $db->fetchAll($sql);
	}
	

	function getAllMongInvoice($_data){
		$db = $this->getAdapter();
		$sql = "SELECT id,
					(SELECT name FROM tb_constructor WHERE tb_constructor.id = constructor LIMIT 1 ) as name,
					CONCAT(invoice_no,'-',(select cust_name from tb_customer where tb_customer.id=customer_id)) as invoice_no 
			 FROM 
				tb_mong WHERE  status=1 ";
		
		if (empty($_data['edit'])){
			$sql.=" AND constructor_balance>0 ";
		}
		$sql.=" AND branch_id = ".$_data['branch_id'];
		$row = $db->fetchAll($sql);
		 
		if (!empty($_data['notOpt'])){
			return $row;
		}else{
			$postype = $_data['postype'];
			$option = '<option value="0">'.htmlspecialchars("ជ្រើសរើសអតិថិជន", ENT_QUOTES).'</option>';
			if ($postype!=1){
				$option = '<option value="0">'.htmlspecialchars("ជ្រើសរើសវិក័យបត្រ", ENT_QUOTES).'</option>';
			}
			if(!empty($row)){
				foreach ($row as $rs){
					if ($postype==1){
						$option .= '<option value="'.$rs['id'].'">'.htmlspecialchars($rs['name'], ENT_QUOTES).'</option>';
					}else{
						$option .= '<option value="'.$rs['id'].'">'.htmlspecialchars($rs['invoice_no'], ENT_QUOTES).'</option>';
					}
				}
			}
			return $option;
		}
		return $db->fetchAll($sql);
	}
}