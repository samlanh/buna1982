<?php 

class Rsvacl_Model_DbTable_DbAcl extends Zend_Db_Table_Abstract
{
	protected  $_name = "tb_acl_acl";
	//get infomation of acl
	public function getAclInfo($sql)
	{
		$db = Rsvacl_Model_DbTable_DbAcl::getAdapter();  		
  		$stm=$db->query($sql);
  		$row=$stm->fetchAll();
  		if(!$row) return NULL;
  		return $row;
	}
	
	//function for getting record acl by acl_id
	public function getAcl($acl_id)
	{
		$select=$this->select();		
		$select->where('acl_id=?',$acl_id);
		$row=$this->fetchRow($select);
		if(!$row) return NULL;
		return $row->toArray();
	}
	
	//get user name
	public function getUserName($user_id)
	{
		$select=$this->select();
		$select->from($this,'username')
			->where("user_id=?",$user_id);
		$row=$this->fetchRow($select);
		if(!$row) return null; 
		return $row['username'];
	}
	//change password user wanted
	public function changePassword($user_id,$password)
	{
		$data=array('password'=>$password);
		$where=$this->getAdapter()->quoteInto('user_id=?',$user_id);
		$this->update($data,$where);
	}
	//is valid password
	public function isValidCurrentPassword($user_id,$current_password)
	{
		$select=$this->select();
		$select->from($this,'password')
			->where("user_id=?",$user_id);
		$row=$this->fetchRow($select);
		if($row){
			$current_password=md5($current_password);
			$password=$row['password'];			 
			if($password==$current_password) return true;
		}
		return false;
	}
	//get infomation of user
	public function getUserInfo($sql)
	{
		$db=$this->getAdapter();
  		$stm=$db->query($sql);
  		$row=$stm->fetchAll();
  		if(!$row) return NULL;
  		return $row;
	}
	//function get user id from database
	public function getUserID($username)
	{
		$select=$this->select();
			$select->from($this,'user_id')
			->where('username=?',$username);
		$row=$this->fetchRow($select);
		if(!$row) return NULL;
		return $row['user_id'];
	}
	//function retrieve record users by column 
	
	//function check actopm have exist
	public function isActionExist($action)
	{
		$select=$this->select();
		$select->from($this,'action')
			->where("action=?",$action);
		$row=$this->fetchRow($select);
		if(!$row) return false;
		return true;
	}
	//function check id number have exist
	public function isIdNubmerExist($id_number)
	{
		$select=$this->select();
		$select->from($this,'id_number')
			->where("id_number=?",$id_number);
		$row=$this->fetchRow($select);
		if(!$row) return false;
		return true;
	}
	//add acl
	public function insertAcl($arr)
	{
		$data=array(); 
		$data['label']=$arr['label'];
		$data['module']=$arr['module'];   
		$data['controller']=$arr['controller'];   
		$data['action']=$arr['action'];
		$data['rank']=$arr['order'];
		$data['is_menu']=$arr['type'];
     	$data['status']='1';
     	
    	return $this->insert($data); 
	}

	public function updateAcl($arr,$acl_id)
	{
		$data=array(); 	
		$data['label']=$arr['label'];
		$data['module']=$arr['module'];   
		$data['controller']=$arr['controller'];   
		$data['action']=$arr['action'];
		$data['rank']=$arr['order'];
		$data['is_menu']=$arr['type'];
     	$data['status']=$arr['status'];	
		
    	$where=" acl_id = $acl_id ";
		$this->update($data,$where); 
	}
	
	function getAclById($id){
		$db = $this->getAdapter();
		$sql="select * from tb_acl_acl where acl_id = $id limit 1";
		return $db->fetchRow($sql);
	}
}

?>





