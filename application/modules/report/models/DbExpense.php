<?php 
Class report_Model_DbExpense extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	
	function getAllExpense($search){
		$db=$this->getAdapter();
		$sql = "SELECT 
					e.*,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id=e.branch_id LIMIT 1) AS branch_name,
					(SELECT fullname FROM `tb_acl_user` AS u WHERE u.user_id = e.user_id)  AS user_name,
					(select name_kh from tb_view where type = 5 and key_code = e.status) as status_label
				FROM 
					tb_expense as e  
				WHERE 
					1  
			";
		$where= ' ';
		$order=" ORDER BY e.receipt DESC  ";
		 
		$from_date =(empty($search['start_date']))? '1': " e.for_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " e.for_date <= '".$search['end_date']." 23:59:59'";
		$where .= "  AND ".$from_date." AND ".$to_date;
		 
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['user'])){
			$where.=" AND e.user_id = ".$search['user'] ;
		}
		if($search['branch_id']!=''){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		 
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['text_search']));
			$s_where[] = " e.receipt LIKE '%{$s_search}%'";
			$s_where[] = " e.total_amount LIKE '%{$s_search}%'";
			$s_where[] = " e.note LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql.$where.$order);
	}

	function getAllExpenseById($id){
		$db=$this->getAdapter();
		$sql = "SELECT
					e.*,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id=e.branch_id LIMIT 1) AS branch_name,
					(SELECT fullname FROM `tb_acl_user` AS u WHERE u.user_id = e.user_id)  AS user_name,
					(select name_en from tb_view where type = 5 and key_code = e.status) as status
				FROM
					tb_expense as e
				WHERE
					e.id = $id
			";
		return $db->fetchRow($sql);
	}
	
	function getAllExpenseDetailById($id){
		$db=$this->getAdapter();
		$sql = "SELECT
					e.*,
					(SELECT title FROM tb_expensetitle WHERE tb_expensetitle.id=e.expense_type_id LIMIT 1) AS expense_type
				FROM
					tb_expense_detail as e
				WHERE
					e.expense_id = $id
			";
		return $db->fetchAll($sql);
	}
	
	function getAllExpenseType($search){
		$db=$this->getAdapter();
		$sql = "SELECT 
					e.*,
					ed.*,
					(select title from tb_expensetitle where tb_expensetitle.id = ed.expense_type_id) as expense_type,
					(SELECT name FROM `tb_sublocation` WHERE id=e.branch_id LIMIT 1) AS branch_name,
					(SELECT fullname FROM `tb_acl_user` AS u WHERE u.user_id = e.user_id)  AS user_name,
					(select name_kh from tb_view where type=5 and key_code = e.status) as status_title
				FROM 
					tb_expense as e,
					tb_expense_detail as ed
				WHERE 
					e.id = ed.expense_id
			";
		$where= ' ';
		
		$order=" ORDER BY e.branch_id ASC , ed.expense_type_id ASC ";
			
		$from_date =(empty($search['start_date']))? '1': " e.for_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " e.for_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where .= "  AND ".$from_date." AND ".$to_date;
			
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['user'])){
			$where.=" AND e.user_id = ".$search['user'] ;
		}
		if($search['branch_id']!=''){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		if($search['title']>-0){
			$where.= " AND ed.expense_type_id = ".$search['title'];
		}
			
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['text_search']));
			$s_where[] = " e.expense_title LIKE '%{$s_search}%'";
			$s_where[] = " e.total_amount LIKE '%{$s_search}%'";
			$s_where[] = " e.note LIKE '%{$s_search}%'";
			$s_where[] = " e.receipt LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql.$where.$order);
	}
}

?>