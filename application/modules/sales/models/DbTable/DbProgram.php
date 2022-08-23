<?php

class Sales_Model_DbTable_DbProgram extends Zend_Db_Table_Abstract
{

    protected $_name = 'tb_program';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    
    function getAllProgram($search=null){
    	$db = $this->getAdapter();
    	$sql = " SELECT 
    					id,
    					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = p.branch_id AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
    					CONCAT(dead_name,'-',dead_name_chinese) as name,
    					(select name_kh from tb_view as v where v.type=19 and v.key_code = dead_sex) as dead_sex,
    					date_time_dead, 					
    					CONCAT(partner_name,'-',partner_name_chinese) as partner_name,
    					(select name_kh from tb_view as v where v.type=19 and v.key_code = partner_sex) as partner_sex,
    					
    					place_of_program,
    					place_pjos_sop,
    					
    					create_date,
    					(select fullname from tb_acl_user as u where u.user_id=p.user_id) as user_id,
    					
    					(select name_kh from tb_view as v where v.type=5 and v.key_code = status) as status
			    	FROM 
			    		tb_program as p
			    	WHERE 
    					1 
    		";
    	
    	$order=" order by id DESC";
    	$where = '';
    	
    	$from_date =(empty($search['start_date']))? '1': " create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['title'])){
	    	$s_where=array();
	    	$s_search=addslashes(trim($search['title']));
	    	$s_where[]=" dead_name LIKE '%{$s_search}%'";
	    	$s_where[]=" dead_name_chinese LIKE '%{$s_search}%'";
	    	$s_where[]=" partner_name LIKE '%{$s_search}%'";
	    	$s_where[]=" partner_name_chinese LIKE '%{$s_search}%'";
	    	$s_where[]=" place_of_program LIKE '%{$s_search}%'";
	    	$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND status = ".$db->quote($search['status']);
    	}
    	if(!empty($search['branch'])){
    		$where .= " AND p.branch_id = ".$search['branch'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    
    public function addProgram($data){
    	//print_r($data);exit();
    	$db=$this->getAdapter();
    	$db->beginTransaction();
    	
    	$array_photo_name = "";
    	
    	$part= PUBLIC_PATH.'/images/';
    	$stu_photo_name = $_FILES['photo']['name'];
    	
    	if (!empty($stu_photo_name)){
    		foreach($stu_photo_name as $key=>$tmp_name){
	    		$tem = explode(".", $stu_photo_name[$key]);
	    		$image_name = time().$key.".".end($tem);
	    		$tmp = $_FILES['photo']['tmp_name'][$key];
	    		if(move_uploaded_file($tmp, $part.$image_name)){
	    			move_uploaded_file($tmp, $part.$image_name);
	    			if($key==0){
    					$comma = "";
	    			}else{
	    				$comma = ",";
	    			}
	    			$array_photo_name = $array_photo_name.$comma.$image_name;
	    		}
    		}
    	}
    	
    	try{
    		$branch_id = empty($data["branch"])?1:$data["branch"];
    		
	    	$_arr=array(
	    			'branch_id'	=>	$branch_id,
	    			'dead_name' 			=> $data['dead_name'],
	    			'dead_name_chinese' 	=> $data['dead_name_chinese'],
	    			'dead_sex' 				=> $data['dead_sex'],
	    			'dead_khmer_year'	    => $data['dead_khmer_year'],
	    			'dead_age'      		=> $data['dead_age'],
	    			'dead_dob'           	=> empty($data['dead_dob'])?null:date("Y-m-d",strtotime($data['dead_dob'])),
	    			'dead_status'	    	=> $data['dead_status'],
	    			'date_time_dead'	    => empty($data['date_time_dead'])?null:date("Y-m-d H:i:s",strtotime($data['date_time_dead'])),
	    			'dead_pob'           	=> $data['dead_pob'],
	    			
	    			'partner_name' 			=> $data['partner_name'],
	    			'partner_name_chinese' 	=> $data['partner_name_chinese'],
	    			'partner_sex' 			=> $data['partner_sex'],
	    			'partner_khmer_year'	=> $data['partner_khmer_year'],
	    			'partner_age'      		=> $data['partner_age'],
	    			'partner_dob'           => empty($data['partner_dob'])?null:date("Y-m-d",strtotime($data['partner_dob'])),
	    			'partner_status'	    => $data['partner_status'],
	    			'date_time_female'	    => empty($data['date_time_female'])?null:date("Y-m-d H:i:s",strtotime($data['date_time_female'])),
	    			'partner_pob'           => $data['partner_pob'],
	    			
	    			'place_of_program'      => $data['place_of_program'],
	    			'type_romleay_sop'      => $data['type_romleay_sop'],
	    			'place_pjos_sop'	    => $data['place_pjos_sop'],
	    			'membersone'           	=> $data['membersone'],
	    			'note'           		=> $data['note'],
	    			
	    			'lerk_sop_date'      	=> empty($data['lerk_sop_date'])?null:date("Y-m-d",strtotime($data['lerk_sop_date'])),
	    			'lerk_sop_time'     	=> $data['lerk_sop_time'],
	    			'lerk_sop_time_to'     	=> $data['lerk_sop_time_to'],
	    			'lerk_sop_time_mail'    => $data['lerk_sop_time_mail'],
	    			'lerk_sop_opposite_year'=> $data['lerk_sop_opposite_year'],
	    			
	    			'hae_sop_date'      	=> empty($data['hae_sop_date'])?null:date("Y-m-d",strtotime($data['hae_sop_date'])),
	    			'hae_sop_time'      	=> $data['hae_sop_time'],
	    			'hae_sop_time_to'      	=> $data['hae_sop_time_to'],
	    			'hae_sop_mail'      	=> $data['hae_sop_mail'],
	    			'hae_sop_opposite_year'	=> $data['hae_sop_opposite_year'],
	    			
	    			'pjos_sop_date'      	=> empty($data['pjos_sop_date'])?null:date("Y-m-d",strtotime($data['pjos_sop_date'])),
	    			'pjos_sop_time'      	=> $data['pjos_sop_time'],
	    			'pjos_sop_time_to'      => $data['pjos_sop_time_to'],
	    			'pjos_sop_mail'      	=> $data['pjos_sop_mail'],
	    			'pjos_sop_opposite_year'=> $data['pjos_sop_opposite_year'],
	    			
	    			'photo'      			=> $array_photo_name,
	    			
	    			'create_date'      		=> date("Y-m-d"),
	    			'modify_date'      		=> date("Y-m-d"),
	    			'user_id'	       		=> $this->getUserId(),
	    			'modify_by'	       		=> $this->getUserId()
	    	);
	    	
	    	$program_id = $this->insert($_arr);
	    	
	    	$this->_name = "tb_program_son_khmer_year";
	    	
	    	if(!empty($data['identity_boy'])){
	    		$ids = explode(",", $data['identity_boy']);
	    		foreach ($ids as $i){
	    			$array = array(
		    			'program_id'    => $program_id,
		    			'type'      	=> 1, // 1 = ឆ្នាំកូនប្រុស
		    			'khmer_year_id'	=> $data['khmer_year_boy_'.$i],
	    				'note'      	=> $data['boy_note_'.$i],
	    			);
	    			
	    			$this->insert($array);
	    		}
	    	}
	    	
	    	if(!empty($data['identity_girl'])){
	    		$ids1 = explode(",", $data['identity_girl']);
	    		foreach ($ids1 as $j){
	    			$array = array(
	    					'program_id'    => $program_id,
	    					'type'      	=> 2, // 2 = ឆ្នាំកូនស្រី
	    					'khmer_year_id'	=> $data['khmer_year_girl_'.$j],
	    					'note'      	=> $data['girl_note_'.$j],
	    			);
	    			 
	    			$this->insert($array);
	    		}
	    	}
	    	
	    	$db->commit();
	    	
    	}catch (Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();exit();
    	}
    }
    
    public function editProgram($data,$id){
    	$db=$this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$branch_id = empty($data["branch"])?1:$data["branch"];
	    	$_arr=array(
	    			'branch_id'	=>	$branch_id,
	    			'dead_name' 			=> $data['dead_name'],
	    			'dead_name_chinese' 	=> $data['dead_name_chinese'],
	    			'dead_sex' 				=> $data['dead_sex'],
	    			'dead_khmer_year'	    => $data['dead_khmer_year'],
	    			'dead_age'      		=> $data['dead_age'],
	    			'dead_dob'           	=> empty($data['dead_dob'])?null:date("Y-m-d",strtotime($data['dead_dob'])),
	    			'dead_status'	    	=> $data['dead_status'],
	    			'date_time_dead'	    => empty($data['date_time_dead'])?null:date("Y-m-d H:i:s",strtotime($data['date_time_dead'])),
	    			'dead_pob'           	=> $data['dead_pob'],
	    			
	    			'partner_name' 			=> $data['partner_name'],
	    			'partner_name_chinese' 	=> $data['partner_name_chinese'],
	    			'partner_sex' 			=> $data['partner_sex'],
	    			'partner_khmer_year'	=> $data['partner_khmer_year'],
	    			'partner_age'      		=> $data['partner_age'],
	    			'partner_dob'           => empty($data['partner_dob'])?null:date("Y-m-d",strtotime($data['partner_dob'])),
	    			'partner_status'	    => $data['partner_status'],
	    			'date_time_female'	    => empty($data['date_time_female'])?null:date("Y-m-d H:i:s",strtotime($data['date_time_female'])),
	    			'partner_pob'           => $data['partner_pob'],
	    			
	    			'place_of_program'      => $data['place_of_program'],
	    			'type_romleay_sop'      => $data['type_romleay_sop'],
	    			'place_pjos_sop'	    => $data['place_pjos_sop'],
	    			'membersone'           	=> $data['membersone'],
	    			'note'           		=> $data['note'],
	    			
	    			'lerk_sop_date'      	=> empty($data['lerk_sop_date'])?null:date("Y-m-d",strtotime($data['lerk_sop_date'])),
	    			'lerk_sop_time'     	=> $data['lerk_sop_time'],
	    			'lerk_sop_time_to'     	=> $data['lerk_sop_time_to'],
	    			'lerk_sop_time_mail'    => $data['lerk_sop_time_mail'],
	    			'lerk_sop_opposite_year'=> $data['lerk_sop_opposite_year'],
	    			
	    			'hae_sop_date'      	=> empty($data['hae_sop_date'])?null:date("Y-m-d",strtotime($data['hae_sop_date'])),
	    			'hae_sop_time'      	=> $data['hae_sop_time'],
	    			'hae_sop_time_to'      	=> $data['hae_sop_time_to'],
	    			'hae_sop_mail'      	=> $data['hae_sop_mail'],
	    			'hae_sop_opposite_year'	=> $data['hae_sop_opposite_year'],
	    			
	    			'pjos_sop_date'      	=> empty($data['pjos_sop_date'])?null:date("Y-m-d",strtotime($data['pjos_sop_date'])),
	    			'pjos_sop_time'      	=> $data['pjos_sop_time'],
	    			'pjos_sop_time_to'      => $data['pjos_sop_time_to'],
	    			'pjos_sop_mail'      	=> $data['pjos_sop_mail'],
	    			'pjos_sop_opposite_year'=> $data['pjos_sop_opposite_year'],
	    			
	    			'status'      			=> $data['status'],
	    			
	    			'modify_date'      		=> date("Y-m-d"),
	    			'modify_by'	       		=> $this->getUserId()
	    	);
	    	$where = " id=$id ";
	    	$this->update($_arr, $where);
	    	
	    	
	    	$this->_name="tb_program_son_khmer_year";
	    	$where1 = " program_id = $id";
	    	$this->delete($where1);
	    	
	    	
	    	$this->_name = "tb_program_son_khmer_year";
	    	if(!empty($data['identity_boy'])){
	    		$ids = explode(",", $data['identity_boy']);
	    		foreach ($ids as $i){
	    			$array = array(
		    			'program_id'    => $id,
		    			'type'      	=> 1, // 1 = ឆ្នាំកូនប្រុស
		    			'khmer_year_id'	=> $data['khmer_year_boy_'.$i],
	    				'note'      	=> $data['boy_note_'.$i],
	    			);
	    			
	    			$this->insert($array);
	    		}
	    	}
	    	
	    	if(!empty($data['identity_girl'])){
	    		$ids1 = explode(",", $data['identity_girl']);
	    		foreach ($ids1 as $j){
	    			$array = array(
	    					'program_id'    => $id,
	    					'type'      	=> 2, // 2 = ឆ្នាំកូនស្រី
	    					'khmer_year_id'	=> $data['khmer_year_girl_'.$j],
	    					'note'      	=> $data['girl_note_'.$j],
	    			);
	    			 
	    			$this->insert($array);
	    		}
	    	}
	    	$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();
    	}
    }
    
    public function getProgramById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM tb_program WHERE id = $id LIMIT 1";
    	return $db->fetchRow($sql);
    }
    
    public function getTravelById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT *,
    				(SELECT name_kh FROM tb_view WHERE TYPE=17 AND key_code = type_romleay_sop) AS type_romleay,
    				(select name from tb_year_khmer where tb_year_khmer.id = tb_program.lerk_sop_opposite_year limit 1) as hae_sop_jol_mchhos,
					(select name from tb_year_khmer where tb_year_khmer.id = tb_program.hae_sop_opposite_year limit 1) as hae_sop_jenh,
					(select name from tb_year_khmer where tb_year_khmer.id = tb_program.pjos_sop_opposite_year limit 1) as pjos_sop
    			FROM tb_program WHERE id = $id LIMIT 1";
    	return $db->fetchRow($sql);
    }
    public function getLocationmongById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT *,
    					(SELECT name_kh FROM tb_view WHERE TYPE=18 AND key_code = partner_status) AS partner,
			    		(SELECT name_kh FROM tb_view WHERE TYPE=18 AND key_code = dead_status) AS dead,
			    		(select name from tb_year_khmer where tb_year_khmer.id = tb_program.lerk_sop_opposite_year limit 1) as hae_sop_jol_mchhos,
			    		(select name from tb_year_khmer where tb_year_khmer.id = tb_program.hae_sop_opposite_year limit 1) as hae_sop_jenh,
			    		(select name from tb_year_khmer where tb_year_khmer.id = tb_program.pjos_sop_opposite_year limit 1) as pjos_sop
    				FROM tb_program WHERE id = $id LIMIT 1";
    	return $db->fetchRow($sql);
    }
   
    function getAllKhmerYearBoyById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
    				*,
					(select name from tb_year_khmer as y where y.id = khmer_year_id) as khmer_year_name,
					note
	    			FROM 
	    				tb_program_son_khmer_year 
	    			WHERE 
	    				type=1 
	    				and program_id = $id 
    		";
    	return $db->fetchAll($sql);
    }
    
    function getAllKhmerYearGirlById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
    				*,
					(select name from tb_year_khmer as y where y.id = khmer_year_id) as khmer_year_name,
					note
	    			FROM 
	    				tb_program_son_khmer_year 
	    			WHERE 
	    				type=2 
	    				and program_id = $id 
    		";
    	return $db->fetchAll($sql);
    }
    
    function getAllKhmerYear(){
    	$db=$this->getAdapter();
    	$sql="select id,name from tb_year_khmer where status=1";
    	return $db->fetchAll($sql);
    }  
}

