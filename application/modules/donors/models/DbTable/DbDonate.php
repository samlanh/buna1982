<?php

class Donors_Model_DbTable_DbDonate extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_donor_donate";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	function getAllDonate($search){
		$db = $this->getAdapter();
		$sql=" SELECT 
					id,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = d.branch_id AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
					dead_name,
					(select name_kh from tb_view where type=19 and key_code=dead_sex) as dead_sex,
					dead_age,
					date_jom,
					dead_address,
					(select donor from tb_donors where tb_donors.id = donor_id) as donor,
					date_jenh,
					create_date,
					(SELECT fullname FROM `tb_acl_user` as u WHERE u.user_id=d.user_id LIMIT 1) AS user_name,
					(SELECT name_en FROM `tb_view` WHERE TYPE=5 AND key_code=d.status LIMIT 1) STATUS
		 		FROM 
					tb_donor_donate as d
				WHERE 
					dead_name!='' 
					and donor_id>0
			";
		
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " dead_name LIKE '%{$s_search}%'";
			$s_where[] = " (select donor from tb_donors where tb_donors.id = donor_id) LIKE '%{$s_search}%'";
			$s_where[] = " (select name_kh from tb_view where type=19 and key_code=dead_sex) LIKE '%{$s_search}%'";
			$s_where[] = " dead_address LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if($search['status']>-1){
			$where .= " AND status = ".$search['status'];
		}
		if(!empty($search['branch'])){
			$where .= " AND branch_id = ".$search['branch'];
		}
		$order=" ORDER BY id DESC ";
		
// 		echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	public function addDonate($data)
	{
		$db=$this->getAdapter();
		
		$branch_id = empty($data["branch"])?1:$data["branch"];
		$arr=array(
				'branch_id'	=>	$branch_id,
				'dead_name'			=> $data['dead_name'],
 				'dead_sex'			=> $data['dead_sex'],
				'dead_age'			=> $data['dead_age'],
				
// 				'dead_khmer_year'	=> $data['dead_khmer_year'],

				'date_jom'			=> date("Y-m-d",strtotime($data['date_jom'])),
				'dead_address'		=> $data['dead_address'],
				
				'donor_id'			=> $data['donor_id'],
				'qty_donate'		=> 1,
				'date_jenh'			=> date("Y-m-d",strtotime($data['date_jenh'])),
				'donor_address'		=> $data['donor_address'],
				'note'				=> $data['note'],
				'notes'				=> $data['notes'],
				
				'user_id'			=> $this->getUserId(),
				'create_date'		=> date("Y-m-d H:i:s"),
				'status'			=> 1,
		);
		$this->insert($arr);
		
		$sql="select qty from tb_donors where id = ".$data['donor_id']." limit 1 ";		
		$qty=$db->fetchOne($sql);
		
		$this->_name = "tb_donors";
		$array = array(
				'qty'=>$qty-1,
				);
		$where = " id = ".$data['donor_id'];
		$this->update($array, $where);
	}
	public function editDonate($data,$id){
		
		$branch_id = empty($data["branch"])?1:$data["branch"];
		$arr=array(
				'branch_id'	=>	$branch_id,
				'dead_name'			=> $data['dead_name'],
 				'dead_sex'			=> $data['dead_sex'],
				'dead_age'			=> $data['dead_age'],

				//'dead_khmer_year'	=> $data['dead_khmer_year'],

				'date_jom'			=> date("Y-m-d",strtotime($data['date_jom'])),
				'dead_address'		=> $data['dead_address'],
				
				'donor_id'			=> $data['donor_id'],
				'qty_donate'		=> 1,
				'date_jenh'			=> date("Y-m-d",strtotime($data['date_jenh'])),
				'donor_address'		=> $data['donor_address'],
				'note'				=> $data['note'],
				'notes'				=> $data['notes'],
				
				'modify_by'			=> $this->getUserId(),
				'modify_date'		=> date("Y-m-d H:i:s"),
				'status'			=> $data['status'],
		);
		
		$where=" id = $id ";
		$this->_name="tb_donor_donate";
		$this->update($arr,$where);
		
		if($data['status']==0){
			$db=$this->getAdapter();
			$sql="select qty from tb_donors where id = ".$data['donor_id']." limit 1 ";
			$qty=$db->fetchOne($sql);
			
			$this->_name = "tb_donors";
			$array = array(
					'qty'=>$qty+1,
			);
			$where = " id = ".$data['donor_id'];
			$this->update($array, $where);
		}
	}
	
	function getDonorDetail($id){
		$db=$this->getAdapter();
		$sql=" SELECT id,address,detail_kh,detail_chi  FROM `tb_donors` where id=$id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getDonorpeopleById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
					*,
					(select name_kh from tb_view where type=19 and key_code = dead_sex) as dead_sex,
					(select tel from tb_donors where tb_donors.id = donor_id) as donor_phone,
					(select address from tb_donors where tb_donors.id = donor_id) as donor_address,
					(select donor from tb_donors where tb_donors.id = donor_id) as donor  
				FROM 
					tb_donor_donate 
				where 
					id = $id 
				limit 1
			";
		return $db->fetchRow($sql);
	}
	
	function getDonateById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM tb_donor_donate where id = $id ";
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbg->getAccessPermission('branch_id');
    	$sql.=" LIMIT 1	";
    	
    	return $db->fetchRow($sql);
    }
    
    function getAllDonor(){
    	$db = $this->getAdapter();
    	$sql = "SELECT id,donor As name FROM tb_donors where status=1 and qty>0";
    	return $db->fetchAll($sql);
    }

}