<?php
class Purchase_Model_DbTable_DbExpense extends Zend_Db_Table_Abstract
{
	protected $_name = 'tb_expense';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	function getAllExpense($search=null){
		$db = $this->getAdapter();
		$sql=" SELECT
					id,
					(SELECT NAME FROM `tb_sublocation` WHERE id=branch_id) AS branch_name,
					expense_title,
					receipt,
					total_amount,
					note,
					for_date,
					(SELECT fullname FROM `tb_acl_user` WHERE user_id=tb_expense.user_id LIMIT 1) AS user_name,
					(SELECT tb_view.name_kh FROM `tb_view` WHERE tb_view.type=5 AND tb_view.key_code=tb_expense.status LIMIT 1) AS status,
					'បង្កាន់ដៃ'
				FROM
					tb_expense
			
		";
	
		$from_date =(empty($search['start_date']))? '1': " for_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " for_date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
	
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " expense_title LIKE '%{$s_search}%'";
			$s_where[] = " total_amount LIKE '%{$s_search}%'";
			$s_where[] = " receipt LIKE '%{$s_search}%'";
			$s_where[] = " note LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch_id']!=''){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
	
		$order=" order by receipt desc ";
// 		echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	
	function addexpense($data){
		
		$db = new Application_Model_DbTable_DbGlobal();
		$branch_id = empty($data['branch'])?1:$data['branch'];
		$receipt = $db->getExpenseReceiptNumber($branch_id);
		
		$arr = array(
			'branch_id'		=>$branch_id,//$this->getUserId(),
			'expense_title'	=>$data['expense_title'],
			'receipt'		=>$receipt,
			'total_amount'	=>$data['total_amount'],
			'for_date'		=>date("Y-m-d",strtotime($data['for_date'])),
			'note'			=>$data['note'],
				
			'create_date'	=>date('Y-m-d H:i:s'),
			'status'		=>1,
			'user_id'		=>$this->getUserId(),
		);
		$id = $this->insert($arr);

		if(!empty($data['identity'])){
			$ids = explode(",", $data['identity']);
			foreach ($ids as $i){
				$array = array(
					'expense_id'		=>$id,
					'expense_type_id'	=>$data['title_'.$i],
					'price'				=>$data['price_'.$i],
					'note'				=>$data['note_'.$i],
				);
				$this->_name="tb_expense_detail";
				$this->insert($array);
			}
		}
		
	}
	function updateExpense($data,$id){
		$arr = array(
				'status'=>$data['status'],
				'user_id'=>$this->getUserId(),
			);
		$where=" id = $id ";
		$this->_name="tb_expense";
		$this->update($arr, $where);
	}

	function getexpensebyid($id){
		$db = $this->getAdapter();
		$sql=" SELECT *,DATE_FORMAT(for_date, '%d-%m-%Y') AS for_date FROM tb_expense WHERE id=$id ";
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbg->getAccessPermission('branch_id');
		$sql.=" LIMIT 1";
		
		return $db->fetchRow($sql);
	}
	
	function getexpenseDetailbyid($id){
		$db = $this->getAdapter();
		$sql=" SELECT 
					*,
					(select title from tb_expensetitle where tb_expensetitle.id = expense_type_id) as expense_title
				FROM 
					tb_expense_detail 
				where 
					expense_id=$id 
			";
		return $db->fetchAll($sql);
	}
	
	function getAllExpenseReport($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace('auth');
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		$sql=" SELECT id,
		(SELECT name FROM `tb_sublocation` WHERE id=branch_id) AS branch_name,
		invoice,
		(SELECT tb_expensetitle.title FROM `tb_expensetitle` WHERE tb_expensetitle.id=tb_income_expense.title LIMIT 1) as title,
		(SELECT description FROM tb_currency WHERE tb_currency.id = curr_type LIMIT 1) as currency_type,
		total_amount,`desc`,for_date,(SELECT name_en FROM `tb_view` WHERE TYPE=5 AND key_code=status LIMIT 1) FROM tb_expense ";
		
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " total_amount LIKE '%{$s_search}%'";
			$s_where[] = " invoice LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch_id']>-1){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		if($search['title']>-1){
			$where.= " AND title = ".$search['title'];
		}
        $order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}



}