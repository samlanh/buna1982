<?php

class Sales_Model_DbTable_DbBorrowers extends Zend_Db_Table_Abstract
{

    protected $_name = 'tb_borrowers';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    public function getAllBorrower($search){
    	$db = $this->getAdapter();
    	$sql=" SELECT id,
    				(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
					name_borrow,
					(SELECT name_kh FROM tb_view WHERE TYPE=19 AND key_code=gender) AS gender,
					phone,
					date,
					qtys,
					notes,
					(SELECT name_kh FROM tb_view AS v WHERE v.type=5 AND v.key_code = tb_borrowers.status) AS STATUS
					FROM `tb_borrowers`
					WHERE name_borrow!='' AND type=1
    	";
    	$where = ''; 

    	$from_date =(empty($search['start_date']))? '1': " date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    	$where = " and ".$from_date." AND ".$to_date;
    	if(!empty($search['ad_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['ad_search']));
    		$s_where[] = " name_borrow LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch'])){
    		$where .= " AND branch_id = ".$search['branch'];
    	}
    	if($search['status']>-1){
    		$where .= " AND status = ".$search['status'];
    	}
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbg->getAccessPermission();
    	$order=" ORDER BY id DESC ";
    	
    	return $db->fetchAll($sql.$where.$order);
    }
    public function addBorrowers($post){
    	$_arr=array(
    			'branch_id' 		 => $post['branch'],
    			'name_borrow' 		 => $post['name_borrow'],
    			'gender'			 => $post['gender'],
    			'phone' 			 => $post['phone'],
    			'date'				 => empty($post['date'])?null:date("Y-m-d H:i:s",strtotime($post['date'])),
    			'qtys'	     	     => $post['qtys'],
    			'qtys_after'	     => $post['qtys'],
    			'notes'	     	     => $post['notes'],
    			'status'	         => 1,
    			'type'	         	 => 1,
    	);
    	return  $this->insert($_arr);
    }
    
    function getBorrowersDetail($name_borrow){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    				*,
					DATE_FORMAT(DATE, '%d-%m-%Y') AS date_borrrow		
				FROM 
					`tb_borrowers` 
				WHERE
					status=1
				    AND name_borrow!='' 
				    AND name_borrow='$name_borrow'					
					AND type=1 LIMIT 1
    		";
    	return $db->fetchRow($sql);  
    }
    
    function getAllBorrowers(){
    	$db = $this->getAdapter();
    	$sql=" SELECT DISTINCT(name_borrow) As name FROM tb_borrowers WHERE name_borrow!='' ";
    	return $db->fetchAll($sql);
    }
    public function updateBorrow($post, $id){
    	$_arr=array(
    			'name_borrow' 		 => $post['name_borrow'],
    			'gender'			 => $post['gender'],
    			'phone' 			 => $post['phone'],
    			'date'				 => empty($post['date'])?null:date("Y-m-d H:i:s",strtotime($post['date'])),
    			'qtys'	     	     => $post['qtys'],
    			'qtys_after'	     => $post['qtys'],
    			'notes'	     	     => $post['notes'],
    			'status'	         => $post['status'],
    			'type'	        	 => 1,
    	);
    	$where="id= $id";
		$this->update($_arr, $where);
    }
    public function getBorrowById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM tb_borrowers WHERE id = $id ";
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbg->getAccessPermission();
    	$sql.=" LIMIT 1 ";
    	
    	return $db->fetchRow($sql);
    }  
}

