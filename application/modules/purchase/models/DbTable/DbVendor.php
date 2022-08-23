<?php

class Purchase_Model_DbTable_DbVendor extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_vendor";
	public function setName($name)
	{
		$this->_name=$name;
	}
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	function getAllVender($search){
		$db = $this->getAdapter();
		$sql=" SELECT 
					v.vendor_id,
					v.v_name,
					v.v_phone,
					v.contact_person,
					v.phone_person,
					v.email,
					v.website,
					v.address,
					(SELECT vi.`name_en` FROM `tb_view` AS vi WHERE vi.`type`=5 AND vi.key_code=v.status) AS status
				FROM 
					tb_vendor AS v 
				WHERE 
					v_name!='' 
			";
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " v.v_name LIKE '%{$s_search}%'";
			$s_where[] = " v.v_phone LIKE '%{$s_search}%'";
			$s_where[] = " v.contact_person LIKE '%{$s_search}%'";
			$s_where[] = " v.phone_person LIKE '%{$s_search}%'";
			$s_where[] = " v.address LIKE '%{$s_search}%'";
			$s_where[] = " v.email LIKE '%{$s_search}%'";
			$s_where[] = " v.website LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['suppliyer_id']>0){
			$where .= " AND vendor_id = ".$search['suppliyer_id'];
		}
		if($search['status']>-1){
			$where .= " AND v.status = ".$search['status'];
		}
		$order=" ORDER BY v.vendor_id DESC";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	function getvendorById($id){
		$sql = "SELECT * FROM tb_vendor WHERE vendor_id=".$id;
		$db = $this->getAdapter();
		return $db->fetchRow($sql);
	}
	final public function addVendor($post){
		
		$db=$this->getAdapter();
		$db->beginTransaction();
		
		$is_over_sea = 0;
		if(!empty($post['is_over_sea'])){
			$is_over_sea=1;
		}
		
		try{
			$data=array(
					'v_name'		=> $post['v_name'],
					'v_phone'		=> $post['v_phone'],
					'contact_person'=> $post['contact_person'],
					'phone_person'	=> $post['phone_person'],
					'address'		=> $post['address'],
					'email'			=> $post['email'],
					'website'		=> $post['website'],
					'note'			=> $post['note'],
					'is_over_sea'	=> $is_over_sea,
					'last_usermod'	=> $this->getUserId(),
					'last_mod_date' => new Zend_Date(),
					'create_date'	=>	date("Y-m-d"),
			);
			if(!empty($post['id'])){
				$data['status']=$post['status'];
				$where = "vendor_id = ".$post["id"];
				$this->update($data, $where);
			}else{
				$data['status']=1;
				$db->insert("tb_vendor", $data);
			}
			return $db->commit();
		}
		catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();exit();
		}
	}
	
	public function addnewvendor($post){//ajax
		try{
			$data=array(
				'v_name'		=> $post['vendor_name'],
				'v_phone'		=> $post['v_phone'],
				'contact_person'=> $post['contact_person'],
				'phone_person'	=> $post['phone_person'],
				'address'		=> $post['address'],
				'email'			=> $post['email'],
				'website'		=> $post['website'],
				'note'			=> $post['note'],
				'is_over_sea'	=> 0,
				'last_usermod'	=> $this->getUserId(),
				'last_mod_date' => new Zend_Date(),
				'create_date'	=>	date("Y-m-d"),
				'status'		=>	1
			);
		    return $this->insert($data);
		}catch(Exception $e){
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
		
	}

	
	
}