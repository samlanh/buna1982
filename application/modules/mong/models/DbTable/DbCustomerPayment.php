<?php

class Mong_Model_DbTable_DbCustomerPayment extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_receipt";
	
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
					(select place_bun from tb_mong where tb_mong.id = r.invoice_id LIMIT 1) as place_bun,
					(select invoice_no from tb_mong where tb_mong.id = r.invoice_id LIMIT 1 ) as invoice,
					(SELECT cust_name FROM `tb_customer` AS c WHERE c.id=r.customer_id LIMIT 1 ) AS customer_name,
					r.`date_input`,
					r.`total`,
					r.`paid`,
					r.`balance`,
					r.remark,
					(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name,
					(select name_kh from tb_view where type=5 and key_code = r.status) as status_name
				FROM 
					`tb_receipt` AS r 
				where 
					r.type=2
			";
		//(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id = r.`branch_id` AND STATUS=1 AND name!='' LIMIT 1) AS branch_name,	
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
			$s_where[] = " r.`cheque_number` LIKE '%{$s_search}%'";
			$s_where[] = " r.`type` LIKE '%{$s_search}%'";
			$s_where[] = " (select place_bun from tb_mong where tb_mong.id = r.invoice_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (select invoice_no from tb_mong where tb_mong.id = r.invoice_id LIMIT 1 ) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
// 		if($search['branch_id']>0){
// 			$where .= " AND r.`branch_id` = ".$search['branch_id'];
// 		}
		if($search['status']!=-1){
			$where .= " AND r.status = ".$search['status'];
		}
		if($search['customer_id']>0){
			$where .= " AND r.customer_id =".$search['customer_id'];
		}
		if(!empty($search['branch'])){
			$where .= " AND r.`branch_id` = ".$search['branch'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		
// 		echo $sql.$where;
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function addCustomerPayment($post)
	{
		try{	
			$db=$this->getAdapter();
			$_db = new Application_Model_DbTable_DbGlobal();
			$branch_id = empty($post['branch'])?1:$post['branch'];
			$receipt = $_db->getReceiptNumber($branch_id);
			
			$data=array(
	 				'branch_id'			=> $branch_id,
					'invoice_id'		=> $post['invoice_id'],
					'customer_id'		=> $post['cus_id'],
					'payment_id'		=> $post['payment_id'],
					'receipt_no'		=> $receipt,
					'receipt_date'		=> date("Y-m-d",strtotime($post['date_in'])),
					
					'begining_balance'	=> $post['all_total'],
					'total'				=> $post['all_total'],
					'paid'				=> $post['paid'],
					'balance'			=> $post['balance'],
					'remark'			=> $post['other_note'],
					
					'cheque_number'		=> $post['cheque'],
					'bank_name'			=> $post['bank_name'],
					
					'type'				=> 2,// 2=mong receipt type
					
					'status'			=> 1,
					'date_input'		=> date("Y-m-d"),
					'user_id'			=> $this->getUserId(),
			);
			
			$this->insert($data);
			
			
			$sql="select * from tb_mong where id = ".$post['invoice_id'];
			$row_mong = $db->fetchRow($sql);
			if(!empty($row_mong)){
				$balance_after = $row_mong['balance_after'] - $post['paid'];
				$paid = $row_mong['paid'] + $post['paid'];
			}
			$arr = array(
					'balance_after'	=> $balance_after,
					'paid'			=> $paid,
					);
			$where = " id = ".$row_mong['id'];
			$this->_name="tb_mong";
			$this->update($arr, $where);
		}catch (Exception $e){
			echo $e->getMessage();exit();
		}
		
	}
	public function updateCustomerPayment($data,$id){
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
						$this->_name="tb_mong";
						$where = " id = ".$rs['invoice_id'];
						$this->update($arr, $where);
					}
				}
				$this->_name="tb_receipt";
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
	
	function getRecieptById($id){
		$db = $this->getAdapter();
		$sql="SELECT 
					r.*,
					(select b.name from tb_sublocation as b where b.id = m.branch_id limit 1) as branch_name,
					(select name from tb_sale_agent as sa where sa.id=m.sale_agent limit 1) as sale_agent,
					m.invoice_no as invoice,
					c.cust_name AS customer_name,
					m.phone as contact_phone,
					m.address AS address,
					m.place_bun,
					m.place_pjos,
					(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name,
					(select name_kh from tb_view where tb_view.type=17 and key_code=m.type_pjos) as type_pjos_name
				FROM 
					tb_receipt as r,
					tb_customer as c,
					tb_mong as m
				WHERE 
					c.id = r.customer_id
					and m.id = r.invoice_id
					and r.type=2
					and r.id = $id 
			";
		$dbg = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbg->getAccessPermission('r.branch_id');
		$sql.=" LIMIT 1	";
		return $db->fetchRow($sql);
	}
	
	function deletePayment($id){
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
					$this->_name="tb_mong";
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
	
	function getRecieptDetail($reciept_id){
		$db= $this->getAdapter();
		$sql="SELECT
					*
				FROM
					tb_receipt AS r
				WHERE
					r.id = $reciept_id
					limit 1
			";
		return $db->fetchRow($sql);
	}
	
	function getSaleById($mong_id){
		$db =$this->getAdapter();
		$sql="SELECT * FROM tb_mong AS m WHERE m.id = $mong_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getMongCustomerName(){
		$db = $this->getAdapter();
		$sql = "SELECT 
					id,
					(select cust_name from tb_customer where tb_customer.id = customer_id) as name,
					place_bun,
					phone  
				FROM 
					tb_mong 
				WHERE 
					balance_after>0 
					and status=1 
			";
		return $db->fetchAll($sql);
	}
	
	function getMongInvoice(){
		$db = $this->getAdapter();
		$sql = "SELECT id,invoice_no as name FROM tb_mong WHERE balance_after>0 and status=1 ";
		return $db->fetchAll($sql);
	}
	
	function getCustomerInfo($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_mong as m,tb_customer as c where c.id = m.customer_id and m.id=$id";
		return $db->fetchRow($sql);
	}
	
    function getReceipt($mong_id,$cus_id,$type,$action){
    	$db = $this->getAdapter();
    	$status="";
    	if($action=="add"){
    		$status = " and status=1";
    	}
    	$sql = "SELECT 
    				*,
    				DATE_FORMAT(receipt_date, '%d-%M-%Y') AS receipt_date,
    				(select name_kh from tb_view where type=5 and key_code = tb_receipt.status) as status_name
    			FROM 
    				tb_receipt 
    			WHERE 
    				invoice_id=$mong_id 
    				and customer_id=$cus_id 
    				and type=$type
    				$status
    			order by 
    				id ASC		
    		";
    	return $db->fetchAll($sql);
    }
    
    function getMongAllCustomerName($_data){
    	$db = $this->getAdapter();
    	$sql = "SELECT
			    	id,
			    	
			    	CONCAT(
						COALESCE( (select cust_name from tb_customer where tb_customer.id = customer_id LIMIT 1) ,''),
						' - ',
						COALESCE(place_bun,''),
						' - ',
						COALESCE(phone,'')
					
					) as name,
			    	invoice_no,
			    	phone
		    	FROM
		    		tb_mong
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
    					$option .= '<option value="'.$rs['id'].'">'.htmlspecialchars($rs['invoice_no'], ENT_QUOTES).'</option>';
    				}
    			}
    		}
    		return $option;
    	}
    }

}