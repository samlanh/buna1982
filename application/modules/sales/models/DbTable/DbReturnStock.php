<?php

class Sales_Model_DbTable_DbReturnStock extends Zend_Db_Table_Abstract
{
    protected $_name = 'tb_product';
    public function setName($name)
    {
    	$this->_name=$name;
    }
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	function getAllReturnStock($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$user_id = $this->getUserId();
		$sql ="SELECT
					id,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = rs.branch_id AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
					return_code,
					title,
					total_amount,
					note,
					create_date,
					(SELECT `fullname` FROM `tb_acl_user` WHERE `user_id`=rs.`user_id` LIMIT 1) AS user_name,
					(SELECT v.`name_en` FROM tb_view AS v WHERE v.`type`=5  AND rs.`status`=v.`key_code` LIMIT 1) AS status
				FROM
					tb_return_stock AS rs 
				WHERE 	
					1 
			";
		$from_date =(empty($data['start_date']))? '1': " create_date >= '".$data['start_date']." 00:00:00'";
		$to_date = (empty($data['end_date']))? '1': " create_date <= '".$data['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if($data["ad_search"]!=""){
			$string = str_replace(' ','',$data['ad_search']);
			$s_where=array();
			$s_search = addslashes(trim($string));
			$s_where[]=" return_code LIKE '%{$s_search}%'";
			$s_where[]=" title LIKE '%{$s_search}%'";
			$s_where[]=" total_amount LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["status"]!=-1){
			$where.=' AND status='.$data["status"];
		}
		if(!empty($data['branch'])){
			$where .= " AND rs.branch_id = ".$data['branch'];
		}
		
		$group_by = " GROUP BY id DESC ";
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
			 (SELECT item_name FROM `tb_product` WHERE id=p.product_id limit 1) AS product_name 
			FROM
			  `tb_product_package` AS p 
			WHERE p.package_id = $id ";
  	return $db->fetchAll($sql);
  }
  
  // Insert 
  
    public function addReturnStock($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try {
    		$branch_id = empty($data["branch"])?1:$data["branch"];
    		$arr = array(
    			'branch_id'	=>	$branch_id,
    			'return_code'	=>	$this->getReturnCode(),
    			'title'			=>	$data["title"],
    			'total_amount'	=>	$data["total_amount"],
    			'note'			=>	$data["note"],
    			'create_date'	=>	date("Y-m-d H:i:s"),
    			'user_id'		=>	$this->getUserId(),
    			'status'		=>	1,
    		);
    		$this->_name="tb_return_stock";
    		$id = $this->insert($arr);
			
    		if(!empty($data['identity'])){
    			$identitys = explode(',',$data['identity']);
    			foreach($identitys as $i)
    			{
//     				$rs = $this->getProductByProductId($data['product_id'.$i], 1);
    				$rs = $this->getProductByProductId($data['product_id'.$i], $branch_id);
    				if(!empty($rs)){
    					$this->_name='tb_prolocation';
    					$arr = array(
    							'qty'=>$rs['qty']+$data['qty_'.$i]
    					);
    					$where=" id =".$rs['id'];
    					$this->update($arr, $where);
    				}
    				
    				$arr1 = array(
    					'return_id'   => $id,
    					'product_id'  => $data["product_id".$i],
    					'qty'		  => $data["qty_".$i],
    					'price'		  => $data["price_".$i],
    					'total'	      => $data["total_".$i],
    					'note'		  => $data["note_".$i],
    				);
    				$this->_name = "tb_return_stock_detail";
    				$this->insert($arr1);
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    public function editReturnStock($data,$id){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try {
    		
    		$rsdetail = $this->getReturnStockDetailById($id);
    		if(!empty($rsdetail)){ // reset old return
    			foreach($rsdetail as $row){
    				//  $rs = $this->getProductByProductId($row['product_id'], 1);
    				$rs = $this->getProductByProductId($row['product_id'], $row['branch_id']);
    				if(!empty($rs)){
    					$this->_name='tb_prolocation';
    					$arr = array(
    							'qty'=>$rs['qty']-$row['qty']
    					);
    					$where=" id =".$rs['id'];
    					$this->update($arr, $where);
    				}
    			}
    		}
    		
    		$branch_id = empty($data["branch"])?1:$data["branch"];
    		$arr = array(
    			'branch_id'	=>	$branch_id,
    			'title'			=>	$data["title"],
    			'total_amount'	=>	$data["total_amount"],
    			'note'			=>	$data["note"],
    			'create_date'	=>	date("Y-m-d H:i:s"),
    			'user_id'		=>	$this->getUserId(),
    			'status'		=>	1,
    		);
    		$this->_name="tb_return_stock";
    		$where = " id = $id ";
    		$this->update($arr, $where);
    		

    		$this->_name = "tb_return_stock_detail";
    		$where = " return_id = $id ";
    		$this->delete($where);
    		
    		if(!empty($data['identity'])){
    			$identitys = explode(',',$data['identity']);
    			foreach($identitys as $i)
    			{
//     				$rs = $this->getProductByProductId($data['product_id'.$i], 1);
    				$rs = $this->getProductByProductId($data['product_id'.$i], $branch_id);
    				if(!empty($rs)){
    					$this->_name='tb_prolocation';
    					$arr = array(
    							'qty'=>$rs['qty']+$data['qty_'.$i]
    					);
    					$where=" id =".$rs['id'];
    					$this->update($arr, $where);
    				}
    				
    				$arr1 = array(
    					'return_id'   => $id,
    					'product_id'  => $data["product_id".$i],
    					'qty'		  => $data["qty_".$i],
    					'price'		  => $data["price_".$i],
    					'total'	      => $data["total_".$i],
    					'note'		  => $data["note_".$i],
    				);
    				$this->_name = "tb_return_stock_detail";
    				$this->insert($arr1);
    			}
    		}
    		
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();exit();
    	}
    }
    
    function getReturnStockById($id){
    	$sql="SELECT * FROM tb_return_stock WHERE id=$id "; 
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbg->getAccessPermission('branch_id');
    	$sql.=" LIMIT 1	";
    	
    	return $this->getAdapter()->fetchRow($sql);
    }
    function getReturnStockDetailById($return_id){
    	$sql=" SELECT *,
    			(select branch_id from tb_return_stock where tb_return_stock.id = return_id LIMIT 1) as branch_id,
    			(select item_name from tb_product where tb_product.id = product_id LIMIT 1) as pro_name 
    		FROM tb_return_stock_detail WHERE return_id = $return_id";
    	return $this->getAdapter()->fetchAll($sql);
    }
    
    function getAllProductName(){
    	$sql="SELECT id,CONCAT(item_name) AS name,item_code FROM `tb_product` WHERE item_name!='' AND status=1 AND is_service=0 and is_package=0";
    	return $this->getAdapter()->fetchAll($sql);
    }
    
    function getProductByProductId($product_id,$location){
    	$sql=" SELECT * FROM tb_prolocation WHERE pro_id = $product_id AND location_id = $location ";
    	return $this->getAdapter()->fetchRow($sql);
    }
    function getReturnCode(){
	    $db = $this->getAdapter();
	    $sql=" SELECT COUNT(id) FROM tb_return_stock LIMIT 1 ";
	    $pre = "RT";
	    $acc_no = $db->fetchOne($sql);
	    
	    $new_acc_no= (int)$acc_no+1;
	    $acc_no= strlen((int)$acc_no+1);
	    for($i = $acc_no;$i<5;$i++){
	    	$pre.='0';
	    }
	    return $pre.$new_acc_no;
    }
}