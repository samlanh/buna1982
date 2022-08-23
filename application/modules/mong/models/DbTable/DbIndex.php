<?php

class Mong_Model_DbTable_DbIndex extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_mong";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	function getBranchId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->branch_id;
	}
	
	function getAllMong($search){
		//print_r($search);//exit();
		$db = $this->getAdapter();
		$sql=" SELECT 
					id,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = m.branch_id AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
					invoice_no,
					place_bun,
					(SELECT cust_name FROM `tb_customer` AS c WHERE c.id=m.customer_id LIMIT 1 ) AS customer_name,
					
					(select dead_name from tb_program as p where p.id=m.dead_id LIMIT 1) as dead_id,
					(select name_kh from tb_view where type=20 and key_code=m.construct_type LIMIT 1) as construct_type,
					mong_code,
					
					(SELECT name FROM `tb_constructor` AS c WHERE c.id=m.constructor LIMIT 1 ) as constructor,
					sale_date,
					sub_total,
					paid,
					balance_after,
					other_note,
					(SELECT fullname FROM tb_acl_user as u WHERE user_id=user_id LIMIT 1) AS user_name,
					(SELECT name_kh FROM tb_view as v WHERE v.type=5 AND v.key_code=m.status LIMIT 1) status
		 		FROM 
					tb_mong as m
				WHERE 
					1
			";
		
		$from_date =(empty($search['start_date']))? '1': " sale_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " sale_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " mong_code LIKE '%{$s_search}%'";
			$s_where[] = " invoice_no LIKE '%{$s_search}%'";
			$s_where[] = " place_bun LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT cust_name FROM `tb_customer` AS c WHERE c.id=m.customer_id LIMIT 1 ) LIKE '%{$s_search}%'";
			$s_where[] = " (select dead_name from tb_program as p where p.id=m.dead_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name FROM `tb_person_in_charge` AS p WHERE p.id=m.person_in_charge LIMIT 1 ) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name FROM `tb_constructor` AS c WHERE c.id=m.constructor LIMIT 1 ) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']!=-1){
			$where .= " AND m.status = ".$search['status'];
		}
		if($search['customer_id']>-1){
			$where .= " AND customer_id = ".$search['customer_id'];
		}
		if($search['is_complete']==1){
			$where .= " AND m.balance_after = 0 ";
		}
		if($search['is_complete']==2){
			$where .= " AND m.balance_after > 0 ";
		}
		if(!empty($search['branch'])){
			$where .= " AND m.branch_id = ".$search['branch'];
		}
		$order=" ORDER BY id DESC ";
// 		echo $sql.$where.$order;exit();
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function addMong($data)
	{
		try{	
			$db = $this->getAdapter();
			$db->beginTransaction();
			
			$branch_id = empty($data["branch"])?$this->getBranchId():$data["branch"];
			
			$db_global = new Application_Model_DbTable_DbGlobal();
// 			$invoice = $db_global->getInvoiceNumber($this->getBranchId());
// 			$receipt = $db_global->getReceiptNumber($this->getBranchId());

			$invoice = $db_global->getInvoiceNumber($branch_id);
			$receipt = $db_global->getReceiptNumber($branch_id);
				
			
			$array_photo_name = "";
			 
			$part= PUBLIC_PATH.'/images/';
			$photo_name = $_FILES['photo']['name'];
			 
			if (!empty($photo_name)){
				foreach($photo_name as $key=>$tmp_name){
					$tem = explode(".", $photo_name[$key]);
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
			
			$array=array(
				'branch_id'   			=> $branch_id,
					
				'place_bun'				=> $data['place_bun'],
				'type_pjos'				=> $data['type_pjos'],
				'place_pjos'			=> $data['place_pjos'],
				"customer_id"   		=> $data['customer_id'],
				"phone"   				=> $data['phone'],
				"address"   			=> $data['address'],
					
				
				'invoice_no'			=> $invoice,
				'sale_date'				=> date("Y-m-d",strtotime($data['sale_date'])),
				'sale_agent'			=> $data['sale_agent'],
				'comission'				=> $data['comission'],
				'receiver_name'			=> $data['receiver_name'],
				
				'other_note'			=> $data['other_note'],
				
				'sub_total'				=> $data['sub_total'],
//				'paid_before'			=> $data['paid_before'],
				'paid'					=> $data['paid'],
				'balance'				=> $data['balance'],
				'balance_after'			=> $data['balance'],
		//		'return_amount'			=> $data['return_amount'],
					
				'construct_type'		=> $data['construct_type'],
				'mong_type'				=> $data['mong_type'],
				'builder'				=> $data['builder'],
				'mong_code'				=> $data['mong_code'],
				'mong_number'			=> $data['mong_number'],
				'mong_address'			=> $data['mong_address'],
				'person_in_charge'		=> $data['person_in_charge'],
				'mong_note'				=> $data['mong_note'],
				'land_longitude'		=> $data['land_longitude'],
				'land_width'			=> $data['land_width'],
				'mong_longitude'		=> $data['mong_longitude'],
				'mong_width'			=> $data['mong_width'],
				'date_finish'			=> date("Y-m-d",strtotime($data['date_finish'])),
				'date_sen'				=> date("Y-m-d",strtotime($data['date_sen'])),
				'time_sen'				=> $data['time_sen'],
				'time_sen_to'			=> $data['time_sen_to'],
				'time_mas'				=> $data['time_mas'],
				'date_chlong_mong'		=> date("Y-m-d",strtotime($data['date_chlong_mong'])),
				'time_chlong_mong'		=> $data['time_chlong_mong'],
				'time_chlong_mong_to'	=> $data['time_chlong_mong_to'],
				'time_mole'				=> $data['time_mole'],
				'photo'					=> $array_photo_name,
					
				'dead_id'				=> $data['dead_id'],
					
				'constructor'			=> $data['constructor'],
				'constructor_price'		=> 0,//$data['constructor_price'],
				'constructor_paid'		=> 0,
				'constructor_balance'	=> 0,//$data['constructor_price'],
				'total_construct_item'	=> 0,//$data['total_construct_item'],
					
				'user_id'			=> $this->getUserId(),
				'status'			=> 1,
				'create_date'		=> date("Y-m-d H:i:s"),
			);
			$this->_name="tb_mong";
			$mong_id = $this->insert($array);
			
			if($data['paid']>0){
				$arr_receipt = array(
						"branch_id"   		=> $branch_id,
						"invoice_id"    	=> $mong_id,
						"customer_id"   	=> $data['customer_id'],
						"payment_id"    	=> 1,//payment by cash/paypal/cheque
						"receipt_no"    	=> $receipt,
						"receipt_date"  	=> date("Y-m-d",strtotime($data['sale_date'])),
						"date_input"    	=> date("Y-m-d"),
						"begining_balance"	=> $data['sub_total'],
						"total"         	=> $data['sub_total'],
						"paid"          	=> $data['paid'],
						"balance"       	=> $data['balance'],
						
						'receiver_name'		=> $data['receiver_name'],
						
						"remark" 			=> $data['other_note'],
						"user_id"       	=> $this->getUserId(),
						"status"        	=> 1,
						"bank_name"     	=> '',
						"cheque_number" 	=> '',
						"type"        		=> 2, // from mong sale 
				);
				$this->_name="tb_receipt";
				$this->insert($arr_receipt);
			}

			if(!empty($data['identity_sale'])){
				$iden = explode(",", $data['identity_sale']);
				foreach ($iden as $i){
					
					$_db = new Sales_Model_DbTable_Dbpos();
					$is_service = $_db->getType($data['pro_'.$i]); //check if service not need update stock
					if($is_service['is_service'] == 0 && $is_service['is_package'] == 0){ // product បានចូលធ្វើ
						
						
// 						$rs = $_db->getProductByProductId($data['pro_'.$i], $this->getBranchId());
						$rs = $_db->getProductByProductId($data['pro_'.$i], $branch_id);
						if(!empty($rs)){
							$this->_name='tb_prolocation';
							$arr = array(
								'qty'=>$rs['qty']-$data['qty_sold_'.$i]
							);
							$where=" id =".$rs['id'];
							$this->update($arr, $where);
						}
					}
					
					$arr=array(
						'mong_id'		=> $mong_id,
						'pro_id'		=> $data['pro_'.$i],
							
						'is_package_cost'=> $data['is_package_cost_'.$i],
						'is_package'	=> $data['is_package_'.$i],
						'package_id'	=> $data['packageid_'.$i],
							
						'qty_unit'		=> $data['qtyunit_'.$i],
						'qty_detail'	=> $data['qtydetail_'.$i],
						'qty_order'		=> $data['qty_sold_'.$i],
							
						'cost_price'	=> $data['cost_price_'.$i],
						'price_riel'	=> $data['price_reil_'.$i],
						'price'			=> $data['selling_price_'.$i],
						
						'total'	  	  => $data['total_'.$i],
						'discount'	  => $data['discount_'.$i],
						'sub_total'	  => $data['sale_total_'.$i],
					);
					$this->_name="tb_mong_sale_item";
					$this->insert($arr);
				}
			}
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			echo $e->getMessage();
		}
	}
	
	public function editMong($data,$mong_id)
	{
		try{
			$db = $this->getAdapter();
			$db->beginTransaction();				
			
			$branch_id = empty($data["branch"])?$this->getBranchId():$data["branch"];
			$db_global = new Application_Model_DbTable_DbGlobal();
// 			$invoice = $db_global->getInvoiceNumber($this->getBranchId());
// 			$receipt = $db_global->getReceiptNumber($this->getBranchId());

// 			$invoice = $db_global->getInvoiceNumber($branch_id);
			$receipt = $db_global->getReceiptNumber($branch_id);
			
			
			$rsdetail = $this->getMongDetailById($mong_id);
			if(!empty($rsdetail)){
				foreach($rsdetail as $row){
					$_db = new Sales_Model_DbTable_Dbpos();
					$is_service = $_db->getType($row['pro_id']); //check if service not need update stock
					if($is_service['is_service']==0){ // product បានចូលធ្វើ
// 						$rs = $this->getProductByProductId($row['pro_id'], 1);
						$rs = $_db->getProductByProductId($row['pro_id'], $branch_id);
						if(!empty($rs)){
							$this->_name='tb_prolocation';
							$arr = array(
									'qty'=>$rs['qty']+$row['qty_order']
							);
							$where=" id =".$rs['id'];
							$this->update($arr, $where);
						}
					}
				}
			}
			
			if($data['status']==0){
				$info_mong=array(
					'status'		=> $data['status'],
					"user_id"       => $this->getUserId(),
				);
				$this->_name="tb_mong";
				$where=" id = ".$mong_id;
				$this->update($info_mong, $where);
				
				
				$db_sale = new Sales_Model_DbTable_Dbpos();
				$rsreceipt = $db_sale->getReceiptBySaleId($mong_id,2);
				if($data['paid']>0){
					$info_receipt=array(
							"user_id"       => $this->getUserId(),
							'status'        => $data['status'],
					);
					$this->_name="tb_receipt";
					if(!empty($rsreceipt)){
						$where1 = " type = 2 and id = ".$rsreceipt['id'];
						$this->update($info_receipt, $where1);
					}
				}
				
				$db->commit();
				return 0;
			}
			
			
			
			
			$array=array(
					'branch_id'   			=> $branch_id,
					'place_bun'				=> $data['place_bun'],
					'type_pjos'				=> $data['type_pjos'],
					'place_pjos'			=> $data['place_pjos'],
					"customer_id"   		=> $data['customer_id'],
					"phone"   				=> $data['phone'],
					"address"   			=> $data['address'],
					
					'invoice_no'			=> $data['invoice_no'],
					'sale_date'				=> date("Y-m-d",strtotime($data['sale_date'])),
					'sale_agent'			=> $data['sale_agent'],
					'comission'				=> $data['comission'],
					'receiver_name'			=> $data['receiver_name'],
					
					'other_note'			=> $data['other_note'],
					
					'sub_total'				=> $data['sub_total'],
			//		'paid_before'			=> $data['paid_before'],
					'paid'					=> $data['paid'],
					'balance'				=> $data['balance'],
					'balance_after'			=> $data['balance'],
			//		'return_amount'			=> $data['return_amount'],	
				
					'construct_type'		=> $data['construct_type'],
					'mong_type'				=> $data['mong_type'],
					'builder'				=> $data['builder'],
					'mong_code'				=> $data['mong_code'],
					'mong_number'			=> $data['mong_number'],
					'mong_address'			=> $data['mong_address'],
					'person_in_charge'		=> $data['person_in_charge'],
					'mong_note'				=> $data['mong_note'],
					'land_longitude'		=> $data['land_longitude'],
					'land_width'			=> $data['land_width'],
					'mong_longitude'		=> $data['mong_longitude'],
					'mong_width'			=> $data['mong_width'],
					'date_finish'			=> date("Y-m-d",strtotime($data['date_finish'])),
					'date_sen'				=> date("Y-m-d",strtotime($data['date_sen'])),
					'time_sen'				=> $data['time_sen'],
					'time_sen_to'			=> $data['time_sen_to'],
					'time_mas'				=> $data['time_mas'],
					'date_chlong_mong'		=> date("Y-m-d",strtotime($data['date_chlong_mong'])),
					'time_chlong_mong'		=> $data['time_chlong_mong'],
					'time_chlong_mong_to'	=> $data['time_chlong_mong_to'],
					'time_mole'				=> $data['time_mole'],
			//		'photo'					=> $array_photo_name,		
							
					'dead_id'				=> $data['dead_id'],		
									
					'constructor'			=> $data['constructor'],
					'constructor_price'		=> 0,//$data['constructor_price'],
					'constructor_paid'		=> 0,
					'constructor_balance'	=> 0,//$data['constructor_price'],
					'total_construct_item'	=> 0,//$data['total_construct_item'],						
					'user_id'				=> $this->getUserId(),
					'status'				=> 1,
					//'create_date'			=> date("Y-m-d H:i:s"),
			);
			$this->_name="tb_mong";
			$where=" id = ".$mong_id;
			$this->update($array, $where);

			$db_sale = new Sales_Model_DbTable_Dbpos();
			$rsreceipt = $db_sale->getReceiptBySaleId($mong_id,2);
			
			if($data['paid']>0){
				$arr_receipt = array(
						"branch_id"   		=> $branch_id,
						"invoice_id"    	=> $mong_id,
						"customer_id"   	=> $data['customer_id'],
						"payment_id"    	=> 1,//payment by cash/paypal/cheque
						
						"receipt_date"  	=> date("Y-m-d",strtotime($data['sale_date'])),
						
						"begining_balance"	=> $data['sub_total'],
						"total"         	=> $data['sub_total'],
						"paid"          	=> $data['paid'],
						"balance"       	=> $data['balance'],
	
						'receiver_name'		=> $data['receiver_name'],
	
						"remark" 			=> $data['other_note'],
						"user_id"       	=> $this->getUserId(),
						"status"        	=> 1,
						"bank_name"     	=> '',
						"cheque_number" 	=> '',
						"type"        		=> 2, // from mong sale
				);
				$this->_name="tb_receipt";
				if(!empty($rsreceipt)){
					$where = " type = 2 and id = ".$rsreceipt['id'];
					$this->update($arr_receipt, $where);
				}else{
					$arr_receipt['receipt_no'] = $receipt;
					$arr_receipt['date_input'] = date("Y-m-d");
					
					$reciept_id = $this->insert($arr_receipt);
				}
			}
	
			$this->_name="tb_mong_sale_item";
			$where1=" mong_id = $mong_id";
			$this->delete($where1);
			
			if(!empty($data['identity_sale'])){
				$iden = explode(",", $data['identity_sale']);
				foreach ($iden as $i){
						
					$_db = new Sales_Model_DbTable_Dbpos();
					$is_service = $_db->getType($data['pro_'.$i]); //check if service not need update stock
					if($is_service['is_service'] == 0 && $is_service['is_package'] == 0){ // product បានចូលធ្វើ
// 						$rs = $_db->getProductByProductId($data['pro_'.$i], $this->getBranchId());
						$rs = $_db->getProductByProductId($data['pro_'.$i], $branch_id);
						if(!empty($rs)){
							$this->_name='tb_prolocation';
							$arr = array(
									'qty'=>$rs['qty']-$data['qty_sold_'.$i]
							);
							$where=" id =".$rs['id'];
							$this->update($arr, $where);
						}
					}
						
					$arr=array(
							'mong_id'		=> $mong_id,
							'pro_id'		=> $data['pro_'.$i],
							'qty_unit'		=> $data['qtyunit_'.$i],
							'qty_detail'	=> $data['qtydetail_'.$i],
							'qty_order'		=> $data['qty_sold_'.$i],
							'cost_price'	=> $data['cost_price_'.$i],
							'price_riel'	=> $data['price_reil_'.$i],
							'price'			=> $data['selling_price_'.$i],
							
							'total'	  	  => $data['total_'.$i],
							'discount'	  => $data['discount_'.$i],
							'sub_total'	  => $data['sale_total_'.$i],
					);
					$this->_name="tb_mong_sale_item";
					$this->insert($arr);
				}
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			echo $e->getMessage();
		}
	}
	
	public function editConstructor($data,$id)
	{
		$db=$this->getAdapter();
		$array=array(
				'name'			=> $data['name'],
				'sex'			=> $data['sex'],
				'phone'			=> $data['phone'],
				'email'			=> $data['email'],
				'address'		=> $data['address'],
				'constructor_type'=> $data['constructor_type'],
				'note'			=> $data['note'],
				'user_id'		=> $this->getUserId(),
				'status'		=> $data['status'],
		);
		$where = " id = $id";
		$this->update($array, $where);
	}
	
	function getMongDetailById($id){
		$sql=" SELECT
					msi.*,
					p.item_name AS pro_name,
					p.item_code,
					p.is_service,
					p.is_package,
					(SELECT NAME FROM tb_measure WHERE tb_measure.id = p.measure_id) AS measure_name
				FROM
					tb_mong_sale_item AS msi,
					tb_product AS p
				WHERE
					p.id = msi.pro_id
					AND msi.mong_id = $id
				order by
					msi.id ASC		
			";
		return $this->getAdapter()->fetchAll($sql);
	}
	
	function getMongAll($id){
		$db=$this->getAdapter();
		$sql="select * from tb_mong where id = $id";
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbg->getAccessPermission('branch_id');
		$sql.=" LIMIT 1	";
		
		return $db->fetchRow($sql);
	}
		
	function getGoodtimeById($id){
		$db=$this->getAdapter();
		$sql="select * from tb_mong where id = $id";
		return $db->fetchRow($sql);
	}
	function getTimemolById($id){
		$db=$this->getAdapter();
		$sql="select * from tb_mong where id = $id";
		return $db->fetchRow($sql);
	}
	
	function getConstructorById($id){
		$db=$this->getAdapter();
		$sql="select * from tb_constructor where id = $id";
		return $db->fetchRow($sql);
	}
	
	function getMongType(){
		$db=$this->getAdapter();
		$sql="select id,title from tb_mong_type where status=1 and title!='' ";
		return $db->fetchAll($sql);
	}
	
	function getPersonInCharge(){
		$db=$this->getAdapter();
		$sql="select id,name from tb_person_in_charge where status=1 and name!='' ";
		return $db->fetchAll($sql);
	}
	
	function getDeadPerson(){
		$db=$this->getAdapter();
		$sql="SELECT 
					id,
					dead_name,
					dead_name_chinese,
					dead_dob ,
					membersone,
					memberstwo,
					create_date
				FROM 
					`tb_program` 
				WHERE 
					status=1 
				ORDER BY 
					id DESC
			 ";
		return $db->fetchAll($sql);
	}
	
	function getConstructor(){
		$db=$this->getAdapter();
		$sql="select id,name from tb_constructor where status=1 and name!='' ";
		return $db->fetchAll($sql);
	}
	function getConstructorItem(){
		$db=$this->getAdapter();
		$sql="select id,title,price from tb_constructor_item where status=1 and title!='' ";
		return $db->fetchAll($sql);
	}
	function getDeadDetail($id){
		$db=$this->getAdapter();
		$sql="select 
					*,
					DATE_FORMAT(dead_dob, '%d-%m-%Y') AS dead_dob,
					DATE_FORMAT(date_time_dead, '%d-%m-%Y %H:%i:%s') AS date_time_dead,
					DATE_FORMAT(partner_dob, '%d-%m-%Y') AS partner_dob
				from 
					tb_program 
				where 
					status=1 
					and dead_name!='' 
					and id=$id 
				limit 1 
			";
		return $db->fetchRow($sql);
	}
	
	function getConstructorDetail($id){
		$db=$this->getAdapter();
		$sql="select * from tb_constructor where id=$id limit 1 ";
		return $db->fetchRow($sql);
	}
	
	function getItemPrice($id){
		$db=$this->getAdapter();
		$sql="select price from tb_constructor_item where id=$id limit 1 ";
		return $db->fetchOne($sql);
	}
	
	function getInvoiceById($id){
		$sql=" SELECT 
					m.*,
					(select sa.name from tb_sale_agent as sa where sa.id = m.sale_agent limit 1) as sale_agent,
					(select b.name from tb_sublocation as b where b.id = m.branch_id limit 1) as branch_name,
					(SELECT (cust_name) FROM `tb_customer` WHERE tb_customer.id=m.customer_id LIMIT 1 ) AS customer_name,
					(select name_kh from tb_view where type=17 and key_code=type_pjos) as type_pjos_name,
					(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = m.user_id LIMIT 1) AS user_name
				FROM 
					tb_mong AS m 
				WHERE 
					m.id = $id 
			";
		return $this->getAdapter()->fetchRow($sql);
	}
	
	function getInvoiceDetailById($id){
		$sql=" SELECT 
					msi.*,
					p.item_name As pro_name,
					p.item_code,
					p.is_service,
					p.is_package,
					(select name from tb_measure where tb_measure.id = p.measure_id) as measure_name
				FROM 
					tb_mong_sale_item as msi,
					tb_product as p  
				WHERE 
					p.id = msi.pro_id
					and msi.mong_id = $id
			";
		return $this->getAdapter()->fetchAll($sql);
	}
	function getRefreshProgram(){
		$db=$this->getAdapter();
		$sql="SELECT
					id,
					dead_name,
					dead_name_chinese,
					dead_dob ,
					membersone,
					memberstwo,
					create_date
				FROM
					`tb_program`
				WHERE
					status=1
				ORDER BY 
					id DESC	
			";
		$result = $db->fetchAll($sql);
		$option = "<option value='0'>ជ្រើសរើសកម្មវិធីបុណ្យ</option> <option value='-1'>បន្ថែមកម្មវិធីបុណ្យ</option>";
		if(!empty($result)){
			foreach ($result as $rs){
				$option .= '<option value="'.$rs['id'].'">'.htmlspecialchars($rs['dead_name']." - ".$rs['dead_name_chinese']." - ".$rs['membersone']." - ".$rs['memberstwo'], ENT_QUOTES).'</option>';
			}
		}
		return $option;
	}
	
}