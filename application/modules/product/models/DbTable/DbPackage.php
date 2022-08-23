<?php

class Product_Model_DbTable_DbPackage extends Zend_Db_Table_Abstract
{
    protected $_name = 'tb_product';
    public function setName($name)
    {
    	$this->_name=$name;
    }
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	function getAllProductForAdmin($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$user_id = $this->getUserId();
		$sql ="SELECT
		p.`id`,
		p.`item_code`,
		p.`item_name` ,
		(SELECT c.name FROM `tb_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
		(SELECT v.`name_en` FROM tb_view AS v WHERE v.`type`=16  AND p.`is_service`=v.`key_code` LIMIT 1) AS is_service,
		p.selling_price AS master_price,
		(SELECT `fullname` FROM `tb_acl_user` WHERE `user_id`=p.`user_id` LIMIT 1) AS user_name,
		(SELECT v.`name_kh` FROM tb_view AS v WHERE v.`type`=5  AND p.`status`=v.`key_code` LIMIT 1) AS status
		FROM
		`tb_product` AS p 
		WHERE p.is_package=1 ";
		$where = '';
		if($data["ad_search"]!=""){
			$string = str_replace(' ','',$data['ad_search']);
			$s_where=array();
			$s_search = addslashes(trim($string));
			$s_where[]=" REPLACE(p.item_name,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(p.barcode,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(p.item_code,' ','') LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		
		if($data["category"]!=""){
			$where.=' AND p.cate_id='.$data["category"];
		}
		
		if($data["status"]!=-1){
			$where.=' AND p.status='.$data["status"];
		}
		
		$group_by = " GROUP BY p.id DESC ";
		//echo $sql.$where.$group_by;
		return $db->fetchAll($sql.$where.$group_by);
	}
  public function getProductCode(){
  	$db =$this->getAdapter();
  	$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
  	$acc_no = $db->fetchOne($sql);
  	$new_acc_no= (int)$acc_no+1;
  	$acc_no= strlen((int)$acc_no+1);
  	$pre = "PID";
  	for($i = $acc_no;$i<5;$i++){
  		$pre.='0';
  	}
  	return $pre.$new_acc_no;
  }
  function getProductByPackageid($id){
  	$db = $this->getAdapter();
  	$sql ="SELECT 
				p.*,
				(SELECT item_name FROM `tb_product` WHERE id=p.product_id limit 1) AS product_name,
				(SELECT item_code FROM `tb_product` WHERE id=p.product_id limit 1) AS product_code 
			FROM
			  	`tb_product_package` AS p 
			WHERE 
				p.package_id = $id 
			order by
				id ASC
  		";
  	return $db->fetchAll($sql);
  }
  // Insert and  Update section
    public function addPackage($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try {
    		$arr = array(
    			'item_name'		=>	$data["product_name"],
    			'item_code'		=>	$data["product_code"],
    			'barcode'		=>	$data["barcode"],
    			'cate_id'		=>	$data["cate_id"],
    			'measure_id'	=>  0,
    			'brand_id'		=>	1,
    			'color_id'		=>	0,
    			'is_package'	=>	1,
    			'is_package_cost'=>	$data["is_package_cost"],
    			'is_service'	=>	$data["product_type"],
    			'selling_price'	=>	$data["total_cost"],
    			"price"			=>  0,
    			'qty_perunit'	=>	1,
    			'unit_label'	=>	"",
    			'user_id'		=>	$this->getUserId(),
    			'note'			=>	$data["noted"],
    			'status'		=>	1,
    		);
    		$this->_name="tb_product";
    		$id = $this->insert($arr);
			
    		if(!empty($data['identity'])){
    			$identitys = explode(',',$data['identity']);
    			foreach($identitys as $i)
    			{
    				$arr1 = array(
    					'package_id'  => $id,
    					'product_id'  => $data["product_id".$i],
    					'qty'		  => $data["qty_".$i],
    					'price'		  => $data["price_".$i],
    					'total'	      => $data["total_".$i],
    					'note'		  => $data["note_".$i],
    				);
    				$this->_name = "tb_product_package";
    				$this->insert($arr1);
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    public function EditPackage($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try {
    		$arr = array(
    				'item_name'		=>	$data["product_name"],
    				'item_code'		=>	$data["product_code"],
    				'barcode'		=>	$data["barcode"],
    				'cate_id'		=>	$data["cate_id"],
    				'measure_id'	=>	0,
    				'brand_id'		=>	1,
    				'color_id'		=>	0,
    				'is_package'	=>	1,
    				'is_service'	=>	$data["product_type"],
    				'selling_price'	=>	$data["total_cost"],
    				"price"			=>  0,
    				'qty_perunit'	=>	1,
    				'unit_label'	=>	"",
    				'user_id'		=>	$this->getUserId(),
    				'note'			=>	$data["noted"],
    				'status'		=>	$data["status"],
    		);
    		$this->_name="tb_product";
    		$where="id = ".$data['id'];
    		$this->update($arr, $where);

    		$this->_name = "tb_product_package";
    		$where="package_id = ".$data['id'];
    		$this->delete($where);
    		
    		if(!empty($data['identity'])){
    			$identitys = explode(',',$data['identity']);
    			foreach($identitys as $i)
    			{
    				$arr1 = array(
    						'package_id'  => $data['id'],
    						'product_id'  => $data["product_id".$i],
    						'qty'		  => $data["qty_".$i],
    						'price'		  => $data["price_".$i],
    						'total'	      => $data["total_".$i],
    						'note'		  => $data["note_".$i],
    				);
    				$this->insert($arr1);
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();exit();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    function getProductById($product_id){
    	$sql="SELECT selling_price FROM tb_product WHERE id=$product_id limit 1";
    	return $this->getAdapter()->fetchOne($sql);
    }
    
    
    
}