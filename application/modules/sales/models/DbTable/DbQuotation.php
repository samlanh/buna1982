<?php

class Sales_Model_DbTable_DbQuotation extends Zend_Db_Table_Abstract
{
	protected $_name="tb_sales_order";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	function getBranchId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->branch_id;
	}
	
	function getAllQuote($search=null){
			$db= $this->getAdapter();
			$sql=" SELECT 
						s.id,
						(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = s.branch_id AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
						place_bun,
						(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
						phone,	
						
						s.quote_num,
						s.quote_date,
						s.total_payment,
						(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = user_id LIMIT 1) AS user_name
					FROM 
						`tb_quotation` AS s 
				";
			
			$from_date =(empty($search['start_date']))? '1': " s.quote_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " s.quote_date <= '".$search['end_date']." 23:59:59'";
			$where = " WHERE ".$from_date." AND ".$to_date;
			if(!empty($search['ad_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['ad_search']));
				$s_where[] = " s.quote_num LIKE '%{$s_search}%'";
				$s_where[] = " s.total_payment LIKE '%{$s_search}%'";
				$s_where[] = " (SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['customer_id']>=0){
				$where .= " AND s.customer_id =".$search['customer_id'];
			}
			if(!empty($search['branch'])){
				$where .= " AND s.branch_id =".$search['branch'];
			}
			
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
			return $db->fetchAll($sql.$where.$order);
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

	public function addQuote($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$branch_id = empty($data['branch'])?1:$data['branch'];
			
			$db_global = new Application_Model_DbTable_DbGlobal();
			$quote_num = $db_global->getQuoteNumber($branch_id);
			$info_purchase_order=array(
					"branch_id"   	=> $branch_id,//$this->getBranchId(),
					
					'place_bun'		=> $data['place_bun'],
					'type_pjos'		=> $data['type_pjos'],
					'place_pjos'	=> $data['place_pjos'],
					"customer_id" 	=> $data['customer_id'],
					"phone" 		=> $data['phone'],
					"address"   	=> $data['address'],
					
					"quote_num" 	=> $quote_num,
					"quote_date"    => date("Y-m-d",strtotime($data['quote_date'])),
					'note'			=> $data['note'],
					
					"exchange_rate" => $data['exchange_rate'],
					"total_payment" => $data['sub_total'],
					"user_id"       => $this->getUserId(),
					"create_date" 	=> date("Y-m-d H:i:s"),
			);
			$this->_name="tb_quotation";
			$quote_id = $this->insert($info_purchase_order);
			
			if(!empty($data['identity'])){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$data_item = array(
							'quoat_id'	=> $quote_id,
							'pro_id'	=> $data['product_id'.$i],
							
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
					$this->_name='tb_quotation_item';
					$this->insert($data_item);
				}
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();exit();
		}
	}
	function editQuote($data,$id){
		//print_r($data);exit();
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			$branch_id = empty($data['branch'])?1:$data['branch'];
			$info_purchase_order=array(
					"branch_id"   	=> $branch_id,//$this->getBranchId(),
					
					'place_bun'		=> $data['place_bun'],
					'type_pjos'		=> $data['type_pjos'],
					'place_pjos'	=> $data['place_pjos'],
					"customer_id" 	=> $data['customer_id'],
					"phone" 		=> $data['phone'],
					"address"   	=> $data['address'],
					
					"quote_num" 	=> $data['quote_num'],
					"quote_date"    => date("Y-m-d",strtotime($data['quote_date'])),
					'note'			=> $data['note'],
						
					"exchange_rate" => $data['exchange_rate'],
					"total_payment" => $data['sub_total'],
					"user_id"       => $this->getUserId(),
					//"create_date" 	=> date("Y-m-d H:i:s"),
						
			);
			$this->_name="tb_quotation";
			$where = " id = $id ";
			$this->update($info_purchase_order, $where);
			
			$this->_name='tb_quotation_item';
			$where1 = " quoat_id = $id";
			$this->delete($where1);
			
			if(!empty($data['identity'])){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$data_item = array(
							'quoat_id'	=> $id,
							'pro_id'	=> $data['product_id'.$i],
								
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
					$this->_name='tb_quotation_item';
					$this->insert($data_item);
				}
			}
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			echo $e->getMessage();exit();
		}
	}
	function getQuoteById($id){
		$sql=" SELECT 
					s.*,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = s.branch_id  LIMIT 1) AS branch_name,
					total_payment,
					(SELECT (cust_name) FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
					(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id =s.user_id LIMIT 1) AS user_name,
					(select name_kh from tb_view where type=17 and key_code=type_pjos) as type_pjos_name,
					DATE_FORMAT(quote_date, '%d-%m-%Y') AS quote_date
				FROM 
					tb_quotation AS s 
				WHERE 
					s.id = $id
			";
		$dbg = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbg->getAccessPermission('s.branch_id');
		$sql.=" LIMIT 1";
		return $this->getAdapter()->fetchRow($sql);
	}
	function getQuoteDetailById($id){
		$sql=" SELECT 
					qi.*,
					p.item_name As pro_name,
					p.item_code,
					p.is_service,
					p.is_package,
					(select name from tb_measure where tb_measure.id = p.measure_id) as measure_name
				FROM 
					tb_quotation_item as qi,
					tb_product as p 
				WHERE 
					p.id = qi.pro_id
					and qi.quoat_id = $id
				order by
					qi.id ASC	
			";
		return $this->getAdapter()->fetchAll($sql);
	}
	
	function getType($product_id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM tb_product WHERE id=$product_id ";
		return $db->fetchRow($sql);
	}
	
	function getProductByCategoryId($category,$type=0){
		$sql="SELECT
					*
				FROM
					tb_product
				WHERE
					cate_id=$category
					and status=1
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