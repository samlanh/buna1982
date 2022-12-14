    <?php

class Product_Model_DbTable_DbBrand extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_brand";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function add($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["brand_name"],
				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
		);
		$this->_name = "tb_brand";
		$this->insert($arr);
	}
	public function edit($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["brand_name"],
				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
		);
		$this->_name = "tb_brand";
		$where = $db->quoteInto("id=?", $data["id"]);
		$this->update($arr, $where);
	}
	
	//Popup Insert=======================================\
	public function addNew($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["brand_name"],
				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
		);
		$this->_name = "tb_brand";
		return $this->insert($arr);
	}
	
	public function getAllBrands($data){
		$db = $this->getAdapter();
		$sql = "SELECT c.id,
				c.`name`,
				(SELECT v.name FROM tb_brand AS v WHERE  v.id=c.`parent_id` LIMIT 1) AS parent,
				c.`remark`,
				(SELECT v.`name_en` FROM tb_view AS v WHERE v.`type`=5  AND c.`status` =v.`key_code` LIMIT 1) AS STATUS
				FROM `tb_brand` AS c 
				WHERE c.id";
		$where = '';
		if($data["name"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['name']));
			$s_where[]= " c.`name` LIKE '%{$s_search}%'";
			$s_where[]=" c.`remark` LIKE '%{$s_search}%'";
			//$s_where[]= " cate LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["status"]>-1!=""){
			$where.=' AND c.`status`='.$data["status"];
		}
		$where.=" ORDER BY id DESC";
		return $db->fetchAll($sql.$where);
	}
	public function getAllBrand(){
		$db = $this->getAdapter();
		$sql = "SELECT c.id,c.`name`,c.`parent_id`,c.`remark`,c.`status` FROM `tb_brand` AS c WHERE c.`status` =1";
		return $db->fetchAll($sql);
	}
	
	public function getBrand($id){
		$db = $this->getAdapter();
		$sql = "SELECT c.id,c.`name`,c.`parent_id`,c.`remark`,c.`status` FROM `tb_brand` AS c WHERE c.`id`= $id";
		return $db->fetchRow($sql);
	}
}