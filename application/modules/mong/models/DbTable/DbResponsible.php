<?php

class Mong_Model_DbTable_DbResponsible extends Zend_Db_Table_Abstract
{

    protected $_name = 'tb_person_in_charge';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    function getAllResponsible($search=null){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
    				id,
    				`name`,
     				(SELECT v.name_kh FROM `tb_view` AS v WHERE v.key_code=sex AND v.type=19 LIMIT 1)AS gender,phone,note,
					(SELECT v.name_kh FROM `tb_view` AS v WHERE v.key_code=p.`status` AND v.type=5 LIMIT 1) AS meng
				FROM tb_person_in_charge as p WHERE `name`!='' ";
    	$where = '';
    	if(!empty($search['ad_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['ad_search']));
    		$s_where[]=" name LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	} 
     	$order=" ORDER BY id DESC ";
     //	echo $sql.$where.$order; exit();
    	return $db->fetchAll($sql.$where.$order);
    }
    public function addResposible($post){
    	$_arr=array(
    			'name' 			=> $post['responsible_name'],
    			'sex' 			=> $post['gender'],
    			'phone' 		=> $post['tel_phone'],
    			'note'	      	=> $post['note'],
    			'status'	    => $post['status'],
    			'user_id'	     	=> $this->getUserId()
    	);
    	return  $this->insert($_arr);
    } 
  public function updateResponsible($post){
    	$_arr=array(
    			'name' 			=> $post['responsible_name'],
    			'sex' 			=> $post['gender'],
    			'phone' 		=> $post['tel_phone'],
    			'note'	      	=> $post['note'],
    			'status'	    => $post['status'],
    			'user_id'	     	=> $this->getUserId()
    	);
    	$where=" id=".$post['id'];
    	$this->update($_arr, $where);
    }   
    public function getResponsbileById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * 
		FROM tb_person_in_charge WHERE id= $id LIMIT 1";
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
