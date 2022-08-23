<?php 
Class report_Model_DbOther extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
function getAllDonors($search){
		$db = $this->getAdapter();
		$sql="  SELECT 
					id,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = d.branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
					donor,
					tel,
					address,
					required_using,
					invalid_date,
					detail_chi,
					detail_kh,
					note,
					receipt_no,
					paid_date,
					qtys,
					qty,
					unit_price,
					total_amount, 
					payment_note,
					create_date,
					(SELECT fullname FROM `tb_acl_user` as u WHERE u.user_id=d.user_id LIMIT 1) AS user_name,
					(SELECT name_en FROM `tb_view` WHERE type=5 AND key_code=d.status LIMIT 1) status
		 		FROM 
					tb_donors as d
				WHERE 
					donor!='' 
			";
		
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " donor_name LIKE '%{$s_search}%'";
			$s_where[] = " donor_female LIKE '%{$s_search}%'";
			$s_where[] = " receipt_no LIKE '%{$s_search}%'";
			$s_where[] = " tel LIKE '%{$s_search}%'";
			$s_where[] = " address LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if($search['branch']>0){
			$where .= " AND branch_id = ".$search['branch'];
		}
		if($search['status']>-1){
			$where .= " AND status = ".$search['status'];
		}
		$order=" ORDER BY id DESC ";
		
// 		echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
}
	function getAllsposorship($search){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT 
					id,
					dead_name,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = d.branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
					(select name_kh from tb_view where type=19 and key_code=dead_sex) as dead_sex,
					dead_age,date_jom,
					dead_address,
					(select donor from tb_donors where tb_donors.id = donor_id) as donor,
					date_jenh,
					note,notes,
					create_date,'សប្បុរសជន',
					(SELECT fullname FROM `tb_acl_user` as u WHERE u.user_id=d.user_id LIMIT 1) AS user_name,
					(SELECT name_en FROM `tb_view` WHERE type=5 AND key_code=d.status LIMIT 1) status
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
 			$s_where[] = " (select donor_name from tb_donors where tb_donors.id = donor_id) LIKE '%{$s_search}%'";
 			$s_where[] = " (select name_kh from tb_view where type=19 and key_code=dead_sex) LIKE '%{$s_search}%'";
 			$s_where[] = " dead_address LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where .= " AND status = ".$search['status'];
		}
		if(!empty($search['branch'])){
			$where .= " AND d.branch_id =".$search['branch'];
		}
		$order=" ORDER BY id DESC ";
		
 	//	echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	public function getAllPartnerService($search){
    	$db = $this->getAdapter();
    	$sql=" SELECT
		    	id,
		    	partner_name,
		    	(SELECT name_kh FROM tb_view WHERE TYPE=19 AND key_code=gender limit 1) AS gender,
		    	tel,
		    	addresss,
		    	description,
		    	(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = tb_partnerservice.`user_id` ) AS user_name,
		    	(SELECT name_kh FROM tb_view AS v WHERE v.type=5 AND v.key_code = tb_partnerservice.status limit 1) AS STATUS
		    	FROM
		    		tb_partnerservice 
		    	WHERE 
		    		partner_name!=''
    		";
    	$where = ''; 	
    	if(!empty($search['ad_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['ad_search']));
    		$s_where[] = " partner_name LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where .= " AND status = ".$search['status'];
    	}
    	$order=" ORDER BY id DESC ";
   // 	echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
	
	function getAllsaleMong($search){
		$db = $this->getAdapter();	
		$sql=" SELECT
					id,
					invoice_no,
					m.place_bun,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = m.branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
					(SELECT cust_name FROM `tb_customer` AS c WHERE c.id=m.customer_id LIMIT 1 ) AS customer_name,
					(SELECT phone FROM `tb_customer` AS c WHERE c.id=m.customer_id LIMIT 1 ) AS customer_phone,
						
					(select dead_name from tb_program as p where p.id=m.dead_id LIMIT 1) as dead_id,
					(select name_kh from tb_view where type=20 and key_code=m.construct_type LIMIT 1) as construct_type,
					mong_code,
						
					(SELECT name FROM `tb_person_in_charge` AS p WHERE p.id=m.person_in_charge LIMIT 1 ) as person_in_charge,
					(SELECT name FROM `tb_constructor` AS c WHERE c.id=m.constructor LIMIT 1 ) as constructor,
					sale_date,
					sub_total,
					paid,
					balance_after,
					
					(SELECT SUM(me.total_payment) FROM `tb_mong_expense` AS me WHERE me.status = 1 and me.mong_id=m.id limit 1) as mong_expense,
						
					'វិក័យបត្រ',
					'សែនបើកឆាក',
					'សែនឆ្លងម៉ុង',
					other_note,
					(SELECT fullname FROM tb_acl_user as u WHERE user_id=user_id LIMIT 1) AS user_name,
					(SELECT name_kh FROM tb_view WHERE type=5 AND key_code=m.status LIMIT 1) status_name,
					m.status
				FROM
					tb_mong as m
				WHERE
					1
				";
		$where= '';
		$from_date =(empty($search['start_date']))? '1': " m.sale_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " m.sale_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where .= " AND ".$from_date." AND ".$to_date;
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " invoice_no LIKE '%{$s_search}%'";
			$s_where[] = " place_bun LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT cust_name FROM `tb_customer` AS c WHERE c.id=m.customer_id LIMIT 1 ) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['customer_id']>-1){
			$where .= " AND m.customer_id = ".$search['customer_id'];
		}
		if($search['status']!=-1){
			$where .= " AND m.status = ".$search['status'];
		}
		if($search['is_complete']==1){
			$where .= " AND m.balance_after = 0 ";
		}
		if($search['is_complete']==2){
			$where .= " AND m.balance_after > 0 ";
		}
		if(!empty($search['branch'])){
			$where .= " AND m.branch_id =".$search['branch'];
		}
		$order=" ORDER BY id DESC ";
		//echo $sql.$where.$order;// exit();
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllpaymentList($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT
				p.`id`,
				(SELECT b.name FROM `tb_sublocation` AS b WHERE b.id=pl.`location_id` LIMIT 1) AS branch,
				p.`item_code`,
				p.`item_name` ,
				(SELECT c.name FROM `tb_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
				(SELECT v.`name_en` FROM tb_view AS v WHERE v.`type`=16  AND p.`is_service`=v.`key_code` LIMIT 1) AS is_service,
				(SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
				SUM(pl.`qty`) AS qty,
				p.selling_price AS master_price,
				p.price,
				(SELECT `fullname` FROM `tb_acl_user` WHERE `user_id`=p.`user_id` LIMIT 1) AS user_name,
				(SELECT v.`name_en` FROM tb_view AS v WHERE v.`type`=5  AND p.`status`=v.`key_code` LIMIT 1) AS status
				FROM
				`tb_product` AS p ,
				`tb_prolocation` AS pl
				WHERE p.`id`=pl.`pro_id` ";
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
		if($data["scale"]>0){
			$where.=' AND p.`measure_id`='.$data["scale"];
		}
		if($data["category"]>0){
			$where.=' AND p.`cate_id`='.$data["category"];
		}
		if($data["status"]>-1){
			$where.=' AND p.status='.$data["status"];
		}
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		$group_by = " GROUP BY p.id DESC ";
//		echo $sql.$where.$location.$group_by; exit();
		return $db->fetchAll($sql.$where.$location.$group_by);
	}
function getAllworker($search){
		$db = $this->getAdapter();	
		$sql=" SELECT 
					id,
					name,
					(select name_kh from tb_view where type=19 and key_code=sex LIMIT 1) as sex,
					phone,
					email,
					address,
					(select name_kh from tb_view where type=20 and key_code=constructor_type LIMIT 1) as constructor_type,
					note,
					create_date,
					(SELECT fullname FROM tb_acl_user as u WHERE user_id=user_id LIMIT 1) AS user_name,
					(SELECT name_en FROM tb_view WHERE type=5 AND key_code=status LIMIT 1) status
		 		FROM 
					tb_constructor
				WHERE 
					name!=''
			";			
		$where = "  ";
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " name LIKE '%{$s_search}%'";
			$s_where[] = " phone LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where .= " AND status = ".$search['status'];
		}
		$order=" ORDER BY id DESC ";
// 		echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	function getAlllistSponorship($id){
		$db = $this->getAdapter();
		$sql=" SELECT 
					id,
					dead_name,
					(SELECT name_kh FROM tb_view WHERE TYPE=19 AND key_code=dead_sex) AS dead_sex,
					dead_age,date_jom,
					dead_address,
					create_date,
					(SELECT fullname FROM `tb_acl_user` AS u WHERE u.user_id=d.user_id LIMIT 1) AS user_name,
 					(SELECT name_en FROM `tb_view` WHERE TYPE=5 AND key_code=d.status LIMIT 1) STATUS
		 		FROM 
					tb_donor_donate AS d
				WHERE 
					dead_name!='' 
					AND donor_id>0
									
					AND  d.`donor_id`= $id
					";	 
		return $db->fetchAll($sql);
	}
	function getAllDonorship($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT id,donor, tel, address FROM tb_donors WHERE status=1 and id=$id";
    //	echo $sql;exit();
    	return $db->fetchRow($sql);
    }
       
    function getAllprogram($search){
    	$db = $this->getAdapter();
    	$sql = " SELECT
    	id,
    	dead_name,
    	dead_name_chinese,
    	(select name_kh from tb_view as v where v.type=19 and v.key_code = dead_sex) as dead_sex,
    	(select name from tb_year_khmer as y where y.id = dead_khmer_year) as dead_khmer_year,
    	dead_age,
    	date_time_dead,
    		
    	partner_name,
    	partner_name_chinese,
    	(select name_kh from tb_view as v where v.type=19 and v.key_code = partner_sex) as partner_sex,
    	(select name from tb_year_khmer as y where y.id = partner_khmer_year) as partner_khmer_year,
    	partner_age,
    	date_time_female,
    		
    	place_of_program,
    	(select name_kh from tb_view as v where v.type=17 and v.key_code = type_romleay_sop) as type_romleay_sop,
    	place_pjos_sop,
    	note,
    	membersone,
    	memberstwo,
    		
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
    	 
    	if(!empty($search['ad_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['ad_search']));
    		$s_where[]=" dead_name LIKE '%{$s_search}%'";
    		$s_where[]=" dead_name_chinese LIKE '%{$s_search}%'";
    		$s_where[]=" partner_name LIKE '%{$s_search}%'";
    		$s_where[]=" partner_name_chinese LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND status = ".$db->quote($search['status']);
    	}

    	return $db->fetchAll($sql.$where.$order);
    }
	public function getAllRepay($search=null){
		//print_r($search);exit();
    	$db = $this->getAdapter();
    	$sql=" SELECT id,
					name_borrow,
					(SELECT name_kh FROM tb_view WHERE TYPE=19 AND key_code=gender) AS gender,
					phone,
					date,
					qtys,
					notes,
					(SELECT name_kh FROM tb_view AS v WHERE v.type=5 AND v.key_code = tb_borrowers.status) AS status
					FROM `tb_borrowers`
					WHERE name_borrow!='' AND  type=2
    	";
    	$where = '';

    	$from_date =(empty($search['start_date']))? '1': " date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    	$where = " and ".$from_date." AND ".$to_date;
    	if(!empty($search['ad_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['ad_search']));
    		$s_search = str_replace(' ', '', $s_search);
    		$s_where[] = "REPLACE(name_borrow,'','') LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch'])){
    		$where .= " AND branch_id = ".$search['branch'];
    	}
    	if($search['status']>-1){
    		$where .= " AND status = ".$search['status'];
    	}
    	$order=" ORDER BY id DESC ";
   // 	echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
	public function getAllBorrower($search){//print_r($search);exit();
    	$db = $this->getAdapter();
    	$sql=" SELECT id,
					name_borrow,
					(SELECT name_kh FROM tb_view WHERE TYPE=19 AND key_code=gender) AS gender,
					phone,
					date,
					qtys,
					qtys_after,
					is_complete,
					notes,
					(SELECT name_kh FROM tb_view AS v WHERE v.type=5 AND v.key_code = tb_borrowers.status) AS status
					FROM `tb_borrowers`
					where name_borrow!='' AND type=1
    	";
    	$where = ''; 

    	$from_date =(empty($search['start_date']))? '1': " date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    	$where = " and ".$from_date." AND ".$to_date;
    	if(!empty($search['ad_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['ad_search']));
    		$s_where[] = " name_borrow LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch'])){
    		$where .= " AND branch_id = ".$search['branch'];
    	}
    	if($search['status']>-1){
    		$where .= " AND status = ".$search['status'];
    	}
    	$order=" ORDER BY id DESC ";
   //		echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
	
}?>