<?php

class Sales_Model_DbTable_DbSaleOrder extends Zend_Db_Table_Abstract
{
	protected $_name="tb_sales_order";
	function getAllSaleOrder($search=null){
			$db= $this->getAdapter();
			$sql=" SELECT 
						s.id,					
						(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = s.branch_id AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
						place_bun,
						(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
						s.phone,
						s.sale_no,
						s.date_sold,
						s.all_total,
						s.paid,
						s.balance_after,
						(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.user_id LIMIT 1) AS user_name,
						(select name_kh from tb_view as v where v.key_code = s.status and v.type=5 limit 1) as status 
					FROM 
						`tb_sales_order` AS s 
				";
			
			$from_date =(empty($search['start_date']))? '1': " s.date_sold >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " s.date_sold <= '".$search['end_date']." 23:59:59'";
			$where = " WHERE ".$from_date." AND ".$to_date;
			if(!empty($search['ad_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['ad_search']));
				$s_where[] = " s.sale_no LIKE '%{$s_search}%'";
				$s_where[] = " s.place_bun LIKE '%{$s_search}%'";
				$s_where[] = " s.phone LIKE '%{$s_search}%'";
				$s_where[] = " s.all_total LIKE '%{$s_search}%'";
				$s_where[] = " (SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) LIKE '%{$s_search}%'";
				$s_where[] = " (SELECT (dead_name) FROM `tb_program` WHERE tb_program.id=s.program_id LIMIT 1) LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
// 			if($search['branch']>0){
// 				$where .= " AND s.branch_id = ".$search['branch'];
// 			}
			if($search['status']!=-1){
				$where .= " AND s.status = ".$search['status'];
			}
			if(!empty($search['branch'])){
				$where .= " AND s.branch_id = ".$search['branch'];
			}
			if($search['customer_id']>=0){
				$where .= " AND s.customer_id =".$search['customer_id'];
			}
			if($search['is_complete']==1){
				$where .= " AND s.balance_after = 0 ";
			}
			if($search['is_complete']==2){
				$where .= " AND s.balance_after > 0 ";
			}
			
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
			return $db->fetchAll($sql.$where.$order);
	}
	public function addSaleOrder($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
			$so = $dbc->getSalesNumber($data["branch_id"]);

			$info_purchase_order=array(
					"customer_id"   => 	$data['customer_id'],
					"branch_id"     => 	$data["branch_id"],
					"sale_no"       => 	$so,//$data['txt_order'],
					"date_sold"     => 	date("Y-m-d",strtotime($data['order_date'])),
					"saleagent_id"  => 	$data['saleagent_id'],
					"currency_id"    => 1,//$data['currency'],
					"remark"         => 	$data['remark'],
					"all_total"      => 	$data['totalAmoun'],
					"discount_value" => 	$data['dis_value'],
					"discount_type"  => 	$data['discount_type'],
					"net_total"      => 	$data['all_total'],
					//"paid"         => 	$data['paid'],
					//"balance"      => 	$data['remain'],
					//"tax"			 =>     $data["total_tax"],
					//"payment_method" => $data['payment_name'],
					"user_mod"       => 	$GetUserId,
					'pending_status' =>3,
					"date"      => 	date("Y-m-d"),
			);
			$this->_name="tb_sales_order";
			$sale_id = $this->insert($info_purchase_order); 
			unset($info_purchase_order);

			$ids=explode(',',$data['identity']);
			$locationid=$data['branch_id'];
			foreach ($ids as $i)
			{
				$data_item= array(
						'saleorder_id'=> $sale_id,
						'pro_id'	  => 	$data['item_id_'.$i],
						'qty_unit'	  =>$data['qty_unit_'.$i],
						'qty_detail'  => 	$data['qty_per_unit_'.$i],
						'qty_order'	  => 	$data['qty'.$i],
						'price'		  => 	$data['price'.$i]+$data['extra_price'.$i],
						'old_price'   =>    $data['oldprice_'.$i],
						'cost_price'  =>    $data['cost_price_'.$i],
						'extra_price' =>    $data['extra_price'.$i],
						'disc_value'  =>    str_replace("%",'',$data['dis_value'.$i]),//check it
						'disc_type'	  =>    $data['discount_type'.$i],//check it
						'sub_total'	  =>    $data['total'.$i],
				);
				$this->_name='tb_salesorder_item';
				$this->insert($data_item);
			
			 }
			 
			 
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	public function RejectSale($data)
	{
		$id=$data["id"];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			if($data['approved_name']==2){//reject sale update to quoatation
					$dbc=new Application_Model_DbTable_DbGlobal();
				    $pending=1;
					$arr=array(
							'is_approved'	=> $data['approved_name'],
							'approved_userid'=> $GetUserId,
							'approval_note'	=> $data['app_remark'],
							'approved_date'	=> date("Y-m-d",strtotime($data['app_date'])),
							'pending_status'=>$pending,
					);
					$this->_name="tb_quoatation";
					$where = " id = ".$data["id"];
					$sale_id = $this->update($arr, $where);
			}else{//can sale quoation or sale;
				$arr = array(
						'is_cancel'=>1,
						'cancel_comment'=>$data['app_remark'],
						'cancel_date'=>date("Y-m-d",strtotime($data['app_date'])),
						'cancel_user'=>$GetUserId,
				);
				$where=" id=".$data['quote_id'];
				if(!empty($data['quote_id'])){
					$this->_name="tb_quoatation";
					$this->update($arr, $where);
				}
				
				$this->_name="tb_sales_order";
				$where=" id=".$data['id'];
				$this->update($arr, $where);
				
			}
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	public function updateSaleOrder($data)
	{
		$id=$data["id"];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
// 			$so = $dbc->getSalesNumber($data["branch_id"]);
			$arr=array(
					"customer_id"   => 	$data['customer_id'],
					"branch_id"     => 	$data["branch_id"],
// 					"sale_no"       => 	$so,//$data['txt_order'],
					"date_sold"     => 	date("Y-m-d",strtotime($data['order_date'])),
					"saleagent_id"  => 	$data['saleagent_id'],
					"currency_id"    => $data['currency'],
					"remark"         => 	$data['remark'],
					"all_total"      => 	$data['totalAmoun'],
					"discount_value" => 	$data['dis_value'],
					"discount_type"  => 	$data['discount_type'],
					"net_total"      => 	$data['all_total'],
					"user_mod"       => 	$GetUserId,
					"is_cancel"       => 	0,
 					'pending_status' =>2,
					'is_approved'=>0,
					'is_toinvocie'=>0,
					"date"      => 	date("Y-m-d"),
			);

			$this->_name="tb_sales_order";
			$where="id = ".$id;
			$this->update($arr, $where);
			unset($arr);
			
			$this->_name='tb_salesorder_item';
			$where = " saleorder_id =".$id;
			$this->delete($where);
			
			$ids=explode(',',$data['identity']);
			$locationid=$data['branch_id'];
			foreach ($ids as $i)
			{
				$data_item= array(
						'saleorder_id'=> $id,
						'pro_id'	  => 	$data['item_id_'.$i],
						'qty_unit'=>$data['qty_unit_'.$i],
						'qty_detail'  => 	$data['qty_per_unit_'.$i],
						'qty_order'	  => 	$data['qty'.$i],
						'price'		  => 	$data['price'.$i]+$data['extra_price'.$i],
						'extra_price' =>    $data['extra_price'.$i],
						'cost_price'   =>    $data['cost_price_'.$i],
						'old_price'   =>    $data['oldprice_'.$i],
						'disc_value'  =>    str_replace("%",'',$data['dis_value'.$i]),//check it
						'disc_type'	  =>    $data['discount_type'.$i],//check it
						'sub_total'	  => $data['total'.$i],
				);
				$this->_name='tb_salesorder_item';
				$this->insert($data_item);
// 				print_r($data_item);exit();
			}
			$this->_name='tb_quoatation_termcondition';
			$where = " term_type=2 AND quoation_id = ".$id;
			$this->delete($where);
			
			$ids=explode(',',$data['identity_term']);
			if(!empty($data['identity_term'])){
				foreach ($ids as $i)
				{
					$data_item= array(
							'quoation_id'=> $id,
							'condition_id'=> $data['termid_'.$i],
							"user_id"   => 	$GetUserId,
							"date"      => 	date("Y-m-d"),
							'term_type'=>2
	
					);
					$this->_name='tb_quoatation_termcondition';
					$this->insert($data_item);
				}
			}
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getSaleorderItemById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM $this->_name WHERE id = $id LIMIT 1 ";
		return $db->fetchRow($sql);
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
	
	function getLastReceipt($id,$type){
		$db = $this->getAdapter();
		$sql = "select id from tb_receipt where invoice_id = $id and type=$type order by id DESC limit 1";
		return $db->fetchOne($sql);
	}
	
	
}