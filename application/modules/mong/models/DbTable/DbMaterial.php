<?php

class Mong_Model_DbTable_DbMaterial extends Zend_Db_Table_Abstract
{

    protected $_name = 'tb_constructor_item';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    function getAllMaterial($search=null){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
	    	id,
	    	title,
			price,
			note,
			(SELECT v.name_kh FROM `tb_view` AS v WHERE v.key_code=p.`status` AND v.type=5 LIMIT 1) AS meng
		FROM `tb_constructor_item` AS p WHERE `title`!='' ";
    	$where = '';
    	if(!empty($search['ad_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['ad_search']));
    		$s_where[]=" title LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	} 
     	$order=" ORDER BY id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    public function addMaterial($post){
    	$_arr=array(
    			'title' 		=> $post['material_title'],
    			'price' 		=> $post['price'],
    			'note'	      	=> $post['note'],
    			'status'	    => $post['status'],
    			'create_date'	=>  date("Y-m-d"),
    			'user_id'	     	=> $this->getUserId()
    	);
    	return  $this->insert($_arr);
    } 
  public function updateMaterial($post){
    	$_arr=array(
    			'title' 		=> $post['material_title'],
    			'price' 		=> $post['price'],
    			'note'	      	=> $post['note'],
    			'status'	    => $post['status'],
    			'create_date'	=>  date("Y-m-d"),
    			'user_id'	     	=> $this->getUserId()
    	);
    	$where=" id=".$post['id'];
    	$this->update($_arr, $where);
    }   
    public function getMaterialById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * 
		FROM tb_constructor_item WHERE id= $id LIMIT 1";
     	return $db->fetchRow($sql);
      	}    
//     function getAllService(){
//      	$db  = $this->getAdapter();
//      	$sql="SELECT id,barcode,item_name FROM tb_product 
//      		WHERE item_name!='' AND STATUS=1 AND is_service=1 
//      		AND is_package=0 ";
//      	return $db->fetchAll($sql);
//      } 
}
