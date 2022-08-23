<?php

class Sales_Model_DbTable_Dbpos extends Zend_Db_Table_Abstract
{
	protected $_name="tb_invoice";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	function getBranchId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->branch_id;
	}
	
	function getAllProductName($is_service=null){
		$sql="SELECT id,CONCAT(item_name,' - ',item_code) AS name,item_name,item_code  FROM `tb_product` WHERE item_name!='' AND status=1 ";
		if($is_service==1){ // only service
			$sql.=" AND is_service=1 ";
		}
		if($is_service==2){ // only product
			$sql.=" AND is_service = 0 ";
		}
		return $this->getAdapter()->fetchAll($sql);
	}
	function getAllProductCategory($is_service=null){
		$sql="SELECT id, name FROM `tb_category` WHERE NAME!='' AND STATUS=1";
		if($is_service!=null){
			$sql.=" AND is_service=1";
		}
		return $this->getAdapter()->fetchAll($sql);
	}	
	function getAllCustomerName(){
		$sql="SELECT id,cust_name AS name,phone FROM `tb_customer` WHERE status=1 AND cust_name!='' ";
		return $this->getAdapter()->fetchAll($sql);
	}
	function getProductById($product_id,$branch_id){
		$sql="SELECT 
					*,
					price as cost_price,
					(SELECT qty FROM `tb_prolocation` WHERE pro_id=$product_id AND location_id=$branch_id LIMIT 1) AS qty,
					(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=measure_id) as measue_name
				FROM 
					tb_product 
				WHERE 
					id=$product_id 
				LIMIT 1
			";
		return $this->getAdapter()->fetchRow($sql);
	}
	function getProductByProductId($product_id,$location){
		$sql=" SELECT * FROM tb_prolocation WHERE pro_id = $product_id AND location_id = $location ";
		return $this->getAdapter()->fetchRow($sql);
	}
	
	
	public function addSaleOrder($data)
	{
		//print_r($data);exit();
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			$db_global = new Application_Model_DbTable_DbGlobal();
			
			$branch_id = empty($data['branch'])?1:$data['branch'];
			$invoice = $db_global->getInvoiceNumber($branch_id);
			$receipt = $db_global->getReceiptNumber($branch_id);
			
// 			$invoice = $db_global->getInvoiceNumber($this->getBranchId());
// 			$receipt = $db_global->getReceiptNumber($this->getBranchId());
			
			$info_purchase_order=array(
					"branch_id"     => $branch_id,//$this->getBranchId(),
					
					'place_bun'		=> $data['place_bun'],
					'type_pjos'		=> $data['type_pjos'],
					'place_pjos'	=> $data['place_pjos'],
					"customer_id"   => $data['customer_id'],
					"phone"   		=> $data['phone'],
					"address"   	=> $data['address'],
					'program_id'	=> $data['program_id'],
					
					"sale_no"       => $invoice,
					"date_sold"     => date("Y-m-d",strtotime($data['sale_date'])),
					"saleagent_id"  => $data["saleagent_id"],
					'comission' 	=> $data['comission'],
					'receiver_name' => $data['receiver_name'],
					
					'other_note'	=> $data['other_note'],
					
					"exchange_rate" => $data['exchange_rate'],					
					"all_total"     => $data['sub_total'],
					"paid"          => $data['paid'],
					'balance'		=> $data['balance'],
					"balance_after" => $data['balance'],
					
					'status'		=> 1,
					"user_id"       => $this->getUserId(),
					"create_date"	=> date("Y-m-d H:i:s"),
			
					'partner_service_total'  	=> $data['total_partner_service'],
					'partner_service_balance'  	=> $data['total_partner_service'],
			);
			$this->_name="tb_sales_order";
			$sale_id = $this->insert($info_purchase_order);
			
			if($data['paid']>0){
				$info_purchase_order=array(
						"branch_id"   	=> $branch_id,//$this->getBranchId(),
						'invoice_id'    => $sale_id,
						"customer_id"   => $data["customer_id"],
						"payment_id"    => 1,	//payment by cash/paypal/cheque
						"receipt_no"    => $receipt,
						"receipt_date"  => date("Y-m-d",strtotime($data['sale_date'])),
						"date_input"    => date("Y-m-d"),
						'begining_balance'=>$data['sub_total'],
						"total"         => $data['sub_total'],
						"paid"          => $data["paid"],
						"balance"       => $data['balance'],
						
						'receiver_name'	=> $data['receiver_name'],
						
						"user_id"       => $this->getUserId(),
						'status'        => 1,
						"bank_name"     => 	'',
						"cheque_number" => 	'',
						"type"        	=> 1, // 
						
				);
				$this->_name="tb_receipt";
				$this->insert($info_purchase_order);
			}
	
// 			if($data['total_partner_service']>0){
// 				$arra=array(
// 						"sale_order_id"  => $sale_id,
// 						"date_payment"  => date("Y-m-d",strtotime($data['sale_date'])),
// 						"total_payment" => $data['total_partner_service'],
// 						"paid"          => 0,
// 						"balance"       => $data['total_partner_service'],
// 						"user_id"       => $this->getUserId(),
// 						"status"        => 1,
// 						"bank_name"     => 	'',
// 						"cheque_number" => 	'',
// 				);
// 				$this->_name="tb_partnerservice_payment";
// 				$this->insert($arra);
// 			}
			
			if(!empty($data['identity'])){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$is_service = $this->getType($data['product_id'.$i]);//check if service not need update stock
					if($is_service['is_service'] == 0 && $is_service['is_package'] == 0){ // product បានចូលធ្វើ
						
						
// 						$rs = $this->getProductByProductId($data['product_id'.$i], $this->getBranchId());//check if service not need update stock
						$rs = $this->getProductByProductId($data['product_id'.$i], $branch_id);//check if service not need update stock
						if(!empty($rs)){
							$this->_name='tb_prolocation';
							$arr = array(
									'qty'=>$rs['qty']-$data['qty_sold'.$i]
									);
							$where=" id =".$rs['id'];
							$this->update($arr, $where);
						}
					}
					$data_item= array(
							'saleorder_id'=> $sale_id,
							'pro_id'	  => $data['product_id'.$i],
							
							'is_package_cost'=> $data['is_package_cost_'.$i],
							'is_package'	=> $data['is_package_'.$i],
							'package_id'	=> $data['packageid_'.$i],
							
							'qty_unit'	  => $data['qty_'.$i],
							'qty_detail'  => $data['qtydetail_'.$i],
							'qty_order'	  => $data['qty_sold'.$i],
							
						//	'cost_price'  => $data['cost_price'.$i],
							'price_reil'  => $data['price_reil_'.$i],
							'price'		  => $data['price_'.$i],
							
							'total'	  	  => $data['total_'.$i],
							'discount'	  => $data['discount_'.$i],
							'sub_total'	  => $data['sub_total_'.$i],
					);
					$this->_name='tb_salesorder_item';
					$this->insert($data_item);
					
				}
			}
			
			if(!empty($data['identity_partner'])){
				$ids=explode(',',$data['identity_partner']);
				foreach ($ids as $i)
				{
					$array= array(
							'saleorder_id'	=> $sale_id,
							'service_id'	=> $data['service_id_'.$i],
							'partner_id'	=> $data['partner_'.$i],
							'price'  		=> $data['price_service_'.$i],
							'note'	  		=> $data['note_'.$i],
					);
					$this->_name='tb_sales_partner_service';
					$this->insert($array);
				}
			}
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();
		}
	}
	function editSale($data,$sale_id){
		//print_r($data);exit();
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$branch_id = empty($data['branch'])?1:$data['branch'];
			$rsdetail = $this->getSaleDetailById($sale_id);
			if(!empty($rsdetail)){
				foreach($rsdetail as $row){
					$is_service = $this->getType($row['pro_id']);
					if($is_service['is_service']==0){ // product បានចូលធ្វើ
// 						$rs = $this->getProductByProductId($row['pro_id'], 1);
						$rs = $this->getProductByProductId($row['pro_id'], $branch_id);
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
				$info_purchase_order=array(
					'status'		=> $data['status'],
					"user_id"       => $this->getUserId(),
				);
				$this->_name="tb_sales_order";
				$where=" id = ".$sale_id;
				$this->update($info_purchase_order, $where);
				
				
				
				$rsreceipt = $this->getReceiptBySaleId($sale_id,1);
				if($data['paid']>0){
					$info_receipt=array(
							"user_id"       => $this->getUserId(),
							'status'        => $data['status'],
					);
					$this->_name="tb_receipt";
					if(!empty($rsreceipt)){
						$where1 = " type = 1 and id = ".$rsreceipt['id'];
						$this->update($info_receipt, $where1);
					}
				}
				
				$db->commit();
				return 0;
			}
			
			
			
	
			$info_purchase_order=array(
				/////////////////////////////////////////////////////////////////	
					"branch_id"     => $branch_id,//$this->getBranchId(),
						
					'place_bun'		=> $data['place_bun'],
					'type_pjos'		=> $data['type_pjos'],
					'place_pjos'	=> $data['place_pjos'],
					"customer_id"   => $data['customer_id'],
					"phone"   		=> $data['phone'],
					"address"   	=> $data['address'],
					'program_id'	=> $data['program_id'],
						
					"sale_no"       => $data["sale_no"],
					"date_sold"     => date("Y-m-d",strtotime($data['sale_date'])),
					"saleagent_id"  => $data["saleagent_id"],
					'comission' 	=> $data['comission'],
					'receiver_name' => $data['receiver_name'],
						
					'other_note'	=> $data['other_note'],
						
					"exchange_rate" => $data['exchange_rate'],
					"all_total"     => $data['sub_total'],
					"paid"          => $data['paid'],
					'balance'		=> $data['balance'],
					"balance_after" => $data['balance'],
						
					'status'		=> 1,
					"user_id"       => $this->getUserId(),
					//"create_date"	=> date("Y-m-d H:i:s"),
						
					'partner_service_total'  	=> $data['total_partner_service'],
					'partner_service_balance'  	=> $data['total_partner_service'],
			);
			$this->_name="tb_sales_order";
			$where=" id = ".$sale_id;
			$this->update($info_purchase_order, $where);
				
			$rsreceipt = $this->getReceiptBySaleId($sale_id,1);
			
			if($data['paid']>0){
				$db_global = new Application_Model_DbTable_DbGlobal();
// 				$receipt = $db_global->getReceiptNumber(1);
				$receipt = $db_global->getReceiptNumber($branch_id);
				$info_purchase_order=array(
						"branch_id"   	=> $branch_id,//$this->getBranchId(),
						'invoice_id'    => $sale_id,
						"customer_id"   => $data["customer_id"],
						"payment_id"    => 1,	//payment by cash/paypal/cheque						
						"receipt_date"  => date("Y-m-d",strtotime($data['sale_date'])),
						'begining_balance'=>$data['sub_total'],
						//'paid_before'	=> $data['paid_before'],
						"total"         => $data['sub_total'],
						"paid"          => $data["paid"],
						"balance"       => $data['balance'],
						
						'receiver_name'	=> $data['receiver_name'],						
						
						"user_id"       => $this->getUserId(),
						'status'        => 1,
						"bank_name"     => 	'',
						"cheque_number" => 	'',
						"type"        	=> 1, // 
				);
				$this->_name="tb_receipt";
				
				if(!empty($rsreceipt)){
					$where = " type = 1 and id = ".$rsreceipt['id'];
					$this->update($info_purchase_order, $where);
				}else{
					$info_purchase_order['receipt_no'] = $receipt;
					$info_purchase_order['date_input'] = date("Y-m-d");
					
					$reciept_id = $this->insert($info_purchase_order);
				}
			}
				
			$this->_name='tb_salesorder_item';
			$where=" saleorder_id = ".$sale_id;
			$this->delete($where);
			
			if(!empty($data['identity'])){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$is_service = $this->getType($data['product_id'.$i]);//check if service not need update stock
					if($is_service['is_service']==0 && $is_service['is_package']==0){ // product បានចូលធ្វើ
// 						$rs = $this->getProductByProductId($data['product_id'.$i], $this->getBranchId());
						$rs = $this->getProductByProductId($data['product_id'.$i], $branch_id);
						if(!empty($rs)){
							$this->_name='tb_prolocation';
							$arr = array(
									'qty'=>$rs['qty']-$data['qty_sold'.$i]
									);
							$where=" id =".$rs['id'];
							$this->update($arr, $where);
						}
					}
					
					$data_item= array(
							'saleorder_id'=> $sale_id,
							'pro_id'	  => $data['product_id'.$i],
							
							'is_package_cost'=> $data['is_package_cost_'.$i],
							'is_package'	=> $data['is_package_'.$i],
							'package_id'	=> $data['packageid_'.$i],
							
							'qty_unit'	  => $data['qty_'.$i],
							'qty_detail'  => $data['qtydetail_'.$i],
							'qty_order'	  => $data['qty_sold'.$i],
							
							'cost_price'  => $data['cost_price'.$i],
							'price_reil'  => $data['price_reil_'.$i],
							'price'		  => $data['price_'.$i],
							
							'total'	  	  => $data['total_'.$i],
							'discount'	  => $data['discount_'.$i],
							'sub_total'	  => $data['sub_total_'.$i],
					);
					$this->_name='tb_salesorder_item';
					$this->insert($data_item);
				}
			}
			$this->_name='tb_sales_partner_service';
			$where_partner = " saleorder_id = $sale_id";
			$this->delete($where_partner);
			
			if(!empty($data['identity_partner'])){
				$ids=explode(',',$data['identity_partner']);
				foreach ($ids as $i)
				{
					$array= array(
							'saleorder_id'	=> $sale_id,
							'service_id'	=> $data['service_id_'.$i],
							'partner_id'	=> $data['partner_'.$i],
							'price'  		=> $data['price_service_'.$i],
							'note'	  		=> $data['note_'.$i],
					);
					$this->insert($array);
				}
			}			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			echo $e->getMessage();exit();
		}
	}
	function getSaleById($id){
		$sql=" SELECT 
					s.*,
					(select sa.name from tb_sale_agent as sa where sa.id = s.saleagent_id limit 1) as sale_agent,
					(select b.name from tb_sublocation as b where b.id = s.branch_id limit 1) as branch_name,
					(all_total) AS net_total,
					(SELECT (cust_name) FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
					(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id =s.user_id LIMIT 1) AS user_name,
					(select name_kh from tb_view where type=17 and key_code=type_pjos) as type_pjos_name,
					DATE_FORMAT(date_sold, '%d-%m-%Y') AS date_sold
				FROM 
					tb_sales_order AS s 
				WHERE 
					s.id = $id
			";
		$dbg = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbg->getAccessPermission('s.branch_id');
		$sql.=" LIMIT 1";
		return $this->getAdapter()->fetchRow($sql);
	}
	function getSaleDetailById($id){
		$sql=" SELECT 
					si.*,
					p.item_name As pro_name,
					p.item_code,
					p.is_service,
					p.is_package,
					(select name from tb_measure where tb_measure.id = p.measure_id) as measure_name
				FROM 
					tb_salesorder_item as si,
					tb_product as p 
				WHERE 
					p.id = si.pro_id
					and si.saleorder_id = $id
				order by 
					si.id ASC
			";
		return $this->getAdapter()->fetchAll($sql);
	}
	function getlistingById($id){
		$sql=" SELECT
		si.*,
		p.item_name As pro_name,
		p.item_code,
		p.is_service,
		p.is_package,
		(select name from tb_measure where tb_measure.id = p.measure_id) as measure_name
		FROM
		tb_salesorder_item as si,
		tb_product as p
		WHERE
		p.id = si.pro_id
		and si.saleorder_id = $id
		";
		return $this->getAdapter()->fetchAll($sql);
	}
	
	function getPartnerServiceById($id){
		$sql=" SELECT
					ps.*,
					(SELECT item_name FROM `tb_product` WHERE tb_product.id=ps.service_id) As service_name,
					(SELECT item_code FROM `tb_product` WHERE tb_product.id=ps.service_id) As service_code
				FROM
					tb_sales_partner_service as ps
				WHERE
					ps.saleorder_id = $id
			";
		return $this->getAdapter()->fetchAll($sql);
	}
	
	
	function deleteSale($sale_id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
		$rsdetail = $this->getSaleDetailById($sale_id);
			if(!empty($rsdetail)){
				foreach($rsdetail as $row){
					$rs = $this->getProductByProductId($row['pro_id'], 1);
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
		
			$this->_name='tb_sales_order';
			$where=" id = ".$sale_id;
			$this->delete($where);
			
			$rsreceipt = $this->getReceiptBySaleId($sale_id,1);
			if(!empty($rsreceipt)){
				$this->_name='tb_receipt';
				$where=" id =".$rsreceipt['receipt_id'];
				$this->delete($where);
			}
			$this->_name='tb_receipt_detail';
			$where=" invoice_id=".$sale_id;
			$this->delete($where);
			
			$this->_name='tb_salesorder_item';
			$where=" saleorder_id = ".$sale_id;
			$this->delete($where);
			$db->commit();
		
		}catch(Exception $e){
			$db->rollBack();
		}
	}
	function getReceiptBySaleId($sale_id,$type){
		$sql=" SELECT * FROM tb_receipt WHERE invoice_id = $sale_id and type=$type ORDER BY id DESC LIMIT 1 ";
		return $this->getAdapter()->fetchRow($sql);				
	}
	
	function getAllPartnerService(){
		$db = $this->getAdapter();
		$sql=" SELECT  id,partner_name as name FROM tb_partnerservice WHERE 1 ";
		return $db->fetchAll($sql);
	}
	
	function getAllReceiverName(){
		$db = $this->getAdapter();
		$sql=" SELECT DISTINCT(receiver_name) AS name FROM tb_receipt WHERE receiver_name!='' ";
		return $db->fetchAll($sql);
	}	
	function getAllSaleagent(){
		$db = $this->getAdapter();
		$sql=" SELECT id,name FROM tb_sale_agent WHERE name!='' ";
		return $db->fetchAll($sql);
	}
	
	function getServicePartnerPrice($partner_id , $service_id){
		$db = $this->getAdapter();
		$sql=" SELECT service_fee FROM tb_partnerservice WHERE id=$partner_id and service_cate=$service_id ";
		return $db->fetchOne($sql);
	}
	
	function getServicePartnerPrices($partner_id, $service_id){
		$db = $this->getAdapter();
		$sql=" SELECT `prices`,notes FROM `tb_partner_cost` WHERE partner_id=$partner_id AND `service_id`=$service_id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	
	function getType($product_id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM tb_product WHERE id=$product_id ";
		return $db->fetchRow($sql);
	}
	
	function getPackageProduct($product_id){
		$db = $this->getAdapter();
		$sql=" SELECT 
					*,
					(SELECT item_name FROM `tb_product` WHERE tb_product.id=tb_product_package.product_id) As name ,
					(SELECT item_code FROM `tb_product` WHERE tb_product.id=tb_product_package.product_id) As code 
				FROM 
					tb_product_package 
				WHERE 
					package_id=$product_id 
				order by 
					ID ASC	
			";
		return $db->fetchAll($sql);
	}
	
	function getProductByCategoryId($category,$type=0){
		$where="";
		if ($category>-1){
			$where="and cate_id=$category ";
		}
		$sql="SELECT
					*
				FROM
					tb_product
				WHERE
					status=1 $where
			";
		if($type==1){
			$sql .= " and is_service=0 ";
		}
		$result = $this->getAdapter()->fetchAll($sql);
		$option = '<option value="-1">'.htmlspecialchars("ជ្រើសរើសមុខទំនិញ", ENT_QUOTES).'</option>';
		if(!empty($result)){foreach ($result as $rs){
			$option .= '<option value="'.$rs['id'].'">'.htmlspecialchars($rs['item_name']." - ".$rs['item_code'], ENT_QUOTES).'</option>';
		}}
		return $option;
	}	
}