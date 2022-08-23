<?php

class Sales_Model_DbTable_Dbpayment extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_receipt";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	function getAllReciept($search){
			$db= $this->getAdapter();
			$sql=" SELECT 
						r.id,
						(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = r.branch_id AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
						r.`receipt_no`,
						(SELECT place_bun FROM `tb_sales_order` WHERE tb_sales_order.id=r.invoice_id limit 1) AS place_bun,
						(SELECT sale_no FROM `tb_sales_order` WHERE tb_sales_order.id=r.invoice_id limit 1) AS invoice_no,
						(SELECT cust_name FROM `tb_customer` AS c WHERE c.id=r.customer_id LIMIT 1 ) AS customer_name,
						r.`date_input`,
						r.`total`,
						r.`paid`,
						r.`balance`,
						r.remark,
						(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name ,
						(select name_kh from tb_view where type=5 and key_code = r.status) as status_name
					FROM 
						`tb_receipt` AS r 
					where 
						type=1
			";
			
			$from_date =(empty($search['start_date']))? '1': " r.`receipt_date` >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " r.`receipt_date` <= '".$search['end_date']." 23:59:59'";
			$where = " and ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " r.`receipt_no` LIKE '%{$s_search}%'";
				$s_where[] = " r.`total` LIKE '%{$s_search}%'";
				$s_where[] = " r.`paid` LIKE '%{$s_search}%'";
				$s_where[] = " r.`balance` LIKE '%{$s_search}%'";
				$s_where[] = " r.`remark` LIKE '%{$s_search}%'";
				$s_where[] = " (SELECT sale_no FROM `tb_sales_order` WHERE tb_sales_order.id=r.invoice_id) LIKE '%{$s_search}%'";
				$s_where[] = " (SELECT place_bun FROM `tb_sales_order` WHERE tb_sales_order.id=r.invoice_id limit 1) LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['branch_id']>0){
				$where .= " AND r.`branch_id` = ".$search['branch_id'];
			}
			if($search['customer_id']>0){
				$where .= " AND r.customer_id =".$search['customer_id'];
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
			return $db->fetchAll($sql.$where.$order);
	}
	public function addReceiptPayment($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$branch_id = empty($data['branch_id'])?1:$data['branch_id'];
			$receipt = $db_global->getReceiptNumber($branch_id);
			
			$array=array(
					'branch_id'			=> $branch_id,
					'invoice_id'		=> $data['invoice_id'],
					'customer_id'		=> $data['cus_id'],
					'payment_id'		=> $data['payment_id'],
					'receipt_no'		=> $receipt,
					'receipt_date'		=> date("Y-m-d",strtotime($data['date_in'])),
						
					'begining_balance'	=> $data['all_total'],
					'total'				=> $data['all_total'],
					'paid'				=> $data['paid'],
					'balance'			=> $data['balance'],
					'remark'			=> $data['other_note'],

					'receiver_name'		=> $data['receiver_name'],
					
					'cheque_number'		=> $data['cheque'],
					'bank_name'			=> $data['bank_name'],
						
					'type'				=> 1,// 1=sale payment
						
					'status'			=> 1,
					'date_input'		=> date("Y-m-d"),
					'user_id'			=> $this->getUserId(),
			);
			$this->_name="tb_receipt";
			$this->insert($array); 
			
			$sql="select * from tb_sales_order where id = ".$data['invoice_id'];
			$row_sale = $db->fetchRow($sql);
			//print_r($row_sale);exit();
			
			if(!empty($row_sale)){
				$balance_after = $row_sale['balance_after'] - $data['paid'];
				$paid = $row_sale['paid'] + $data['paid'];
			}
			$arr = array(
					'balance_after'	=> $balance_after,
					'paid'			=> $paid,
			);
			$where = " id = ".$row_sale['id'];
			$this->_name="tb_sales_order";
			$this->update($arr, $where);
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();
		}
	}
	public function updatePayment($data,$id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			if($data['status']==0){
				$rs = $this->getRecieptDetail($id);
				if(!empty($rs)){
					$rssale = $this->getSaleById($rs['invoice_id']);
					if(!empty($rssale)){
						$arr= array(
							'balance_after'=>$rssale['balance_after']+$rs['paid'],
							'paid'	=>$rssale['paid']-$rs['paid'],
						);
						$this->_name="tb_sales_order";
						$where = " id = ".$rs['invoice_id'];
						$this->update($arr, $where);
					}
				}
				$this->_name="tb_receipt";
				$array= array(
					'status' =>$data['status'],
				);
				$where1 = "id =  ".$id;
				$this->update($array,$where1);
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();
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
	
	function getRecieptById($id){
		$db = $this->getAdapter();
		$sql="SELECT 
					r.*,
					(select b.name from tb_sublocation as b where b.id = s.branch_id limit 1) as branch_name,
					(select name from tb_sale_agent as sa where sa.id=s.saleagent_id limit 1) as sale_agent,
					s.sale_no as invoice,
					c.cust_name AS customer_name,
					s.phone as contact_phone,
					s.address AS address,
					s.place_bun,
					s.place_pjos,
					(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name,
					(select name_kh from tb_view where tb_view.type=17 and key_code=s.type_pjos) as type_pjos_name
				FROM 
					tb_receipt as r,
					tb_customer as c,
					tb_sales_order as s
				WHERE 
					c.id = r.customer_id
					and s.id = r.invoice_id
					and r.type=1
					and r.id = $id 
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
				
			";
		$dbg = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbg->getAccessPermission('d.branch_id');
		$sql.=" LIMIT 1	";
		
		return $db->fetchRow($sql);
	}
	function getRecieptDetailforPrint($reciept_id){
		$db= $this->getAdapter();
		$sql="SELECT d.`id`,d.`receipt_id`,d.`invoice_id`,
		(SELECT s.sale_no FROM `tb_sales_order` AS s WHERE s.id=d.invoice_id) AS invoice_name,
		(SELECT i.invoice_no FROM `tb_invoice` AS i  WHERE i.id = d.`invoice_id` ) AS invoice_no,
		d.`total`,d.`paid`,d.`balance`,d.`discount`,d.`date_input`,
		(SELECT s.all_total FROM `tb_sales_order` AS s WHERE s.id=d.invoice_id) AS all_total,
		(SELECT SUM(rd.paid) FROM tb_receipt_detail AS rd WHERE rd.`invoice_id` = d.invoice_id AND rd.id<d.id) AS all_paid
		FROM `tb_receipt_detail` AS d,
			tb_receipt as r
		WHERE 
			r.id=d.`receipt_id`
			AND d.`receipt_id` =".$reciept_id;
		return $db->fetchAll($sql);
	}
	function getSaleorderItemDetailid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_salesorder_item` WHERE saleorder_id=$id ";
		return $db->fetchAll($sql);
	}
	function getTermconditionByid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_quoatation_termcondition` WHERE quoation_id=$id AND term_type=2 ";
		return $db->fetchAll($sql);
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
	
	function getSaleCustomerName(){
		$db = $this->getAdapter();
		$sql = "SELECT 
					id,
					(select cust_name from tb_customer where tb_customer.id = customer_id) as name,
					place_bun,
					phone 
				FROM 
					tb_sales_order 
				WHERE 
					balance_after>0 
					and status=1 
			";
		return $db->fetchAll($sql);
	}
	
	function getSaleInvoice(){
		$db = $this->getAdapter();
		$sql = "SELECT id,sale_no as name FROM tb_sales_order WHERE balance_after>0 and status=1 ";
		return $db->fetchAll($sql);
	}
	
	
	function getCustomerInfo($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_sales_order as s,tb_customer as c where c.id = s.customer_id and s.id=$id";
		return $db->fetchRow($sql);
	}
	
	function getReceipt($mong_id,$cus_id,$type,$action){
		$db = $this->getAdapter();
		$status="";
		if($action=="add"){
			$status = " and status=1 ";
		}
		$sql = "SELECT *,DATE_FORMAT(receipt_date, '%d-%M-%Y') AS receipt_date,(select name_kh from tb_view where type=5 and key_code = tb_receipt.status) as status_name FROM tb_receipt WHERE invoice_id=$mong_id and customer_id=$cus_id and type=$type $status ";
		return $db->fetchAll($sql);
	}
	
	function getInvoiceByReceiptId($id,$type){
		$db = $this->getAdapter();
		$sql = "select invoice_id from tb_receipt where id = $id and type=$type order by id DESC limit 1";
		return $db->fetchOne($sql);
	}
	
	
	function getPOSByBranch($_data){
		$db = $this->getAdapter();
		$sql = "
				SELECT
					id,
					place_bun,
					CONCAT(
						COALESCE( (select cust_name from tb_customer where tb_customer.id = customer_id LIMIT 1) ,''),
						'-',
						COALESCE(place_bun,''),
						'-',
						COALESCE(phone,'')
					
					) as name,
					sale_no
				
				FROM
					tb_sales_order
				WHERE
					 status=1
		";
		if (empty($_data['edit'])){
			$sql.=" AND balance_after>0";
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
						$option .= '<option value="'.$rs['id'].'">'.htmlspecialchars($rs['sale_no'].' '.$rs['place_bun'], ENT_QUOTES).'</option>';
					}
				}
			}
			return $option;
		}
	}
	
}