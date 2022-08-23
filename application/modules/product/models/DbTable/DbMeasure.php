<?php

class Product_Model_DbTable_DbMeasure extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_measure";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	function getBranchId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->branch_id;
	}
	
	public function add($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["measure_name"],
// 				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
		);
		$this->_name = "tb_measure";
		$this->insert($arr);
	}
	public function edit($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["measure_name"],
// 				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
		);
		$this->_name = "tb_measure";
		$where = $db->quoteInto("id=?", $data["id"]);
		$this->update($arr, $where);
	}
	//Insert Popup=============================================================================
	public function addNew($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["measure_name"],
// 				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
		);
		$this->_name = "tb_measure";
		return $this->insert($arr);
	}
	public function addSaleagent($data){
		$db = $this->getAdapter();
		$arr1 = array(
				"branch_id" => $this->getBranchId(),
				'name'		=>	$data["name_saleagent"],
				'email'		=>	$data["email"],
				'phone'		=>	$data["phone"],
				'user_id'	=>	$this->getUserId(),
				'address'	=>	$data["address"],
				'note'		=>	$data["note"],
				'date'		=>	date("Y-m-d"),
		);
		$this->_name = "tb_sale_agent";
		return $this->insert($arr1);
	}
	
	public function getAllMeasure($data){
		$db = $this->getAdapter();
		$sql = "SELECT m.id,
						m.`name`,
						m.`remark`,
						(SELECT v.`name_en` FROM tb_view AS v WHERE v.`type`=5  AND m.`status`=v.`key_code` LIMIT 1) AS status 
						FROM `tb_measure` AS m 
						WHERE m.id";
		$where = '';
		if($data["name"]!=""){
		    $s_where=array();
		    $s_search = addslashes(trim($data['name']));
		    $s_where[]= " m.`name` LIKE '%{$s_search}%'";
		    $s_where[]= " m.`remark` LIKE '%{$s_search}%'";
		    $where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["status"]>-1!=""){
			$where.=' AND m.`status`='.$data["status"];
		}
		//echo $sql.$where;
		$where.=" ORDER BY id DESC";
		return $db->fetchAll($sql.$where);
	}
	
	public function getMeasure($id){
		$db = $this->getAdapter();
		$sql = "SELECT m.id,m.`name`,m.`status`,m.`remark` FROM `tb_measure` AS m  WHERE m.`id`= $id";
		return $db->fetchRow($sql);
	}
}