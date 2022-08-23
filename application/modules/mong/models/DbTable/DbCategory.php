<?php

class Mong_Model_DbTable_DbCategory extends Zend_Db_Table_Abstract
{

    protected $_name = 'tb_mong_type';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;	 
    }
    function getAllCategory($search=null){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
    				id,
    				title,
					note,
				(SELECT v.name_kh FROM `tb_view` AS v WHERE v.key_code=p.`status` AND v.type=5 LIMIT 1) AS meng
				FROM `tb_mong_type` AS p WHERE `title`!='' ";
    	$where = '';
    	if(!empty($search['ad_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['ad_search']));
    		$s_where[]=" title LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	} 
     	$order=" ORDER BY id DESC";
//      	echo $sql.$where.$order;exit();
    	return $db->fetchAll($sql.$where.$order);
    }
    public function addCategory($post){
    	$_arr=array(
    			'title' 		=> $post['category_title'],
    			'note' 			=> $post['note'],
    			'status' 		=> $post['status'],
    			'create_date'	=>  date("Y-m-d"),
    			'user_id'	   	=> $this->getUserId()
    	);
    	return  $this->insert($_arr);
    } 
  public function updateCategory($post){
 //	print_r($post); exit();
    	$_arr=array(
    			'title' 		=> $post['category_title'],
    			'note' 			=> $post['note'],
    			'status' 		=> $post['status'],
    			'create_date'	=>  date("Y-m-d"),
    			'user_id'	   	=> $this->getUserId()
    	);
    	$where=" id=".$post['id'];
    	$this->update($_arr, $where);
    }   
    public function getCategoryById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * 
		FROM tb_mong_type WHERE id= $id LIMIT 1";
     	return $db->fetchRow($sql);
      	}    
 
}
