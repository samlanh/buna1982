<?php

class Mong_Model_DbTable_DbMongExpense extends Zend_Db_Table_Abstract
{
protected $_name="tb_receipt";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	function getAllMongExpense($search){
			$db= $this->getAdapter();
			$sql=" SELECT 
						me.id,
						(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id = me.`branch_id` AND status=1 AND name!='' LIMIT 1) AS branch_name,
						expense_number,
						(SELECT invoice_no FROM tb_mong WHERE tb_mong.id=me.mong_id LIMIT 1) AS invoice_no,
						(SELECT place_bun FROM tb_mong WHERE tb_mong.id=me.mong_id LIMIT 1) AS place_bun,
						me.`date_payment`,
						me.`total_payment`,
						me.note,
						(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = me.`user_id`) AS user_name,
						(select name_kh from tb_view where type=5 and key_code = me.status) as status_name 
					FROM 
						`tb_mong_expense` AS me 
					where 
						1
			";
			
			$from_date =(empty($search['start_date']))? '1': " me.`date_payment` >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " me.`date_payment` <= '".$search['end_date']." 23:59:59'";
			$where = " and ".$from_date." AND ".$to_date;
			if(!empty($search['ad_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['ad_search']));
				$s_where[] = " me.`total_payment` LIKE '%{$s_search}%'";
				$s_where[] = " me.`payment_type` LIKE '%{$s_search}%'";
				$s_where[] = " me.`note` LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['branch']>0){
				$where .= " AND me.`branch_id` = ".$search['branch'];
			}
			if($search['mong_id']>0){
				$where .= " AND me.`mong_id` = ".$search['mong_id'];
			}
			
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			
			$order=" ORDER BY me.id DESC ";
			
			return $db->fetchAll($sql.$where.$order);
	}
	public function addMongExpense($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$branch_id = empty($data['branch'])?1:$data['branch'];
			$arr = array(
						"branch_id"   		=> $branch_id,
						"mong_id"    		=> $data['customer_id'],
						"expense_number"   	=> $data['expense_number'],
						'constructor'		=> $data['constructor_id'],
						"date_payment"  	=> date("Y-m-d",strtotime($data['date_in'])),
						"payment_type"    	=> "Cash",
						"note"    			=> $data['other_note'],
						
						"total_payment"     => $data['total_payment'],
						
						"create_date"  		=> date("Y-m-d H:i:s"),
						"user_id"       	=> $this->getUserId(),
						"status"        	=> 1,
				);
			$this->_name="tb_mong_expense";
			$expense_id = $this->insert($arr); 
			
			if(!empty($data['identity'])){
				$iden = explode(",", $data['identity']);
				foreach ($iden as $i){
					$arra=array(
						'expense_id'	=> $expense_id,
						'item_id'		=> $data['item_'.$i],
						'item_price'	=> $data['item_price_'.$i],
						'item_qty'		=> $data['item_qty_'.$i],
						'item_total'	=> $data['item_total_'.$i],
					);
					$this->_name="tb_mong_expense_detail";
					$this->insert($arra);
				}
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();exit();
		}
	}
	public function updateMongExpense($data,$id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			if($data['status']==1){
				$arr = array(
						"branch_id"   		=> $data['branch'],
						"mong_id"    		=> $data['customer_id'],
						"expense_number"   	=> $data['expense_number'],
						'constructor'		=> $data['constructor_id'],
						"date_payment"  	=> date("Y-m-d",strtotime($data['date_in'])),
						"payment_type"    	=> "Cash",
						"note"    			=> $data['other_note'],
						"total_payment"     => $data['total_payment'],
						"user_id"       	=> $this->getUserId(),
						"status"        	=> 1,
				);
				$this->_name="tb_mong_expense";
				$where=" id =".$id;
				$this->update($arr, $where);
				
				
				$this->_name="tb_mong_expense_detail";
				$where2=" expense_id = $id";
				$this->delete($where2);
				
				if(!empty($data['identity'])){
					$iden = explode(",", $data['identity']);
					foreach ($iden as $i){
						$arra=array(
								'expense_id'	=> $id,
								'item_id'		=> $data['item_'.$i],
								'item_price'	=> $data['item_price_'.$i],
								'item_qty'		=> $data['item_qty_'.$i],
								'item_total'	=> $data['item_total_'.$i],
						);
						$this->_name="tb_mong_expense_detail";
						$this->insert($arra);
					}
				}
			}else{
				$arr = array(
					"user_id"	=> $this->getUserId(),
					"status"    => 0,
				);
				$this->_name="tb_mong_expense";
				$where=" id =".$id;
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
	
	function getEdit($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
					me.*,
					(select name from tb_constructor as c where c.id = me.constructor limit 1) as constructor_name
				FROM 
					tb_mong_expense as me
				where 
					me.id=$id
			";
		return $db->fetchRow($sql);
	}
	function getEditDetail($id){
		$sql="SELECT  
					med.*,
					p.title AS name_s			
				FROM tb_mong_expense_detail AS med,
				     tb_constructor_item  AS p
				 WHERE 
					p.id = med.item_id
					AND med.expense_id = $id
				";
			return $this->getAdapter()->fetchAll($sql);
	}
	
	function getConstructorInfo($id){
		$db = $this->getAdapter();
		$sql = "SELECT c.* FROM tb_constructor as c,tb_mong as m where c.id = m.constructor and m.id=$id";
		return $db->fetchRow($sql);
	}
	
	function getMongByBranch($_data){
    	$db = $this->getAdapter();
    	$sql = "SELECT
			    	id,
			    	
			    	CONCAT(
						(select cust_name from tb_customer where tb_customer.id = customer_id LIMIT 1),
						' - ',
						place_bun,
						' - ',
						phone
					
					) as name,
			    	invoice_no,
			    	phone
		    	FROM
		    		tb_mong
		    	WHERE
			    	 status=1
    	";
    	
    	$sql.=" AND branch_id = ".$_data['branch_id'];
    	$row = $db->fetchAll($sql);
    	
    	if (!empty($row)){
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