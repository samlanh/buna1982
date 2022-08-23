<?php

class Mong_Model_DbTable_DbConstructor extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_constructor";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	function getAllConstructor($search){
		$db = $this->getAdapter();
		
		$sql=" SELECT 
					id,
					name,
					(select name_kh from tb_view where type=19 and key_code=sex LIMIT 1) as sex,
					phone,
					email,
					address,
					(select name_kh from tb_view where type=20 and key_code=constructor_type LIMIT 1) as constructor_type,
					note,
					create_date,
					(SELECT fullname FROM tb_acl_user as u WHERE user_id=user_id LIMIT 1) AS user_name,
					(SELECT name_en FROM tb_view WHERE type=5 AND key_code=status LIMIT 1) status
		 		FROM 
					tb_constructor
				WHERE 
					name!=''
			";
		
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " name LIKE '%{$s_search}%'";
			$s_where[] = " phone LIKE '%{$s_search}%'";
			$s_where[] = " email LIKE '%{$s_search}%'";
			$s_where[] = " address LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where .= " AND status = ".$search['status'];
		}
		$order=" ORDER BY id DESC ";
//		echo $sql.$where.$order; exit();
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function addConstructor($data)
	{
		$db=$this->getAdapter();
		$array=array(
 				'name'			=> $data['name'],
				'sex'			=> $data['sex'],
				'phone'			=> $data['phone'],
				'email'			=> $data['email'],
				'address'		=> $data['address'],
				'constructor_type'=> $data['constructor_type'],
				'note'			=> $data['note'],
				'user_id'		=> $this->getUserId(),
				'status'		=> 1,
				'create_date'	=> date("Y-m-d H:i:s"),
		);
		
		$this->insert($array);
	}
	
	public function editConstructor($data,$id)
	{
		$db=$this->getAdapter();
		$array=array(
				'name'			=> $data['name'],
				'sex'			=> $data['sex'],
				'phone'			=> $data['phone'],
				'email'			=> $data['email'],
				'address'		=> $data['address'],
				'constructor_type'=> $data['constructor_type'],
				'note'			=> $data['note'],
				'user_id'		=> $this->getUserId(),
				'status'		=> $data['status'],
		);
		$where = " id = $id";
		$this->update($array, $where);
	}
		
	function getConstructorById($id){
		$db=$this->getAdapter();
		$sql="select * from tb_constructor where id = $id";
		return $db->fetchRow($sql);
	}

}