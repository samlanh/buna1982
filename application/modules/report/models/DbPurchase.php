<?php 
Class report_Model_DbPurchase extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	public function getAllPurchaseReport($search){//1
		$db= $this->getAdapter();
		$sql=" SELECT id,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
					(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=tb_purchase_order.vendor_id LIMIT 1 ) AS vendor_name,
					order_number,
					date_order,
					total_payment,
					paid,
					balance,
					(SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=5 LIMIT 1 ) As purchase_status,
					(SELECT name_kh FROM `tb_view` WHERE key_code =tb_purchase_order.status AND type=5 LIMIT 1) as status,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = user_mod LIMIT 1 ) AS user_name
				FROM 
					`tb_purchase_order`
		
			";
	
		$from_date =(empty($search['start_date']))? '1': " date_order >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_order <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
	
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " order_number LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=tb_purchase_order.vendor_id LIMIT 1 ) LIKE '%{$s_search}%'";
			$s_where[] = " net_total LIKE '%{$s_search}%'";
			$s_where[] = " paid LIKE '%{$s_search}%'";
			$s_where[] = " balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if($search['status']>-1){
			$where .= " AND status = ".$search['status'];
		}
		if(!empty($search['branch'])){
			$where .= " AND branch_id =".$search['branch'];
		}
	
// 		if($search['status_paid']>0){
// 			if($search['status_paid']==1){
// 				$where .= " AND balance <=0 ";
// 			}
// 			elseif($search['status_paid']==2){
// 				$where .= " AND balance >0 ";
// 			}
// 		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	function getProductPruchaseById($id){//2
		$db = $this->getAdapter();
		$sql=" SELECT
					(SELECT name FROM `tb_sublocation` WHERE id=p.branch_id) AS branch_name,
					p.order_number,p.date_order,p.remark,
					(SELECT item_name FROM `tb_product` WHERE id= po.pro_id LIMIT 1) AS item_name,
					(SELECT item_code FROM `tb_product` WHERE id=po.pro_id LIMIT 1 ) AS item_code,
						
					(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=(SELECT measure_id FROM `tb_product` WHERE id= po.pro_id LIMIT 1)) as measue_name,
					(SELECT qty_perunit FROM `tb_product` WHERE id= po.pro_id LIMIT 1) AS qty_perunit,
					(SELECT unit_label FROM `tb_product` WHERE id=po.pro_id LIMIT 1 ) AS unit_label,
				
					(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_name,
					(SELECT v_phone FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS v_phone,
					(SELECT contact_person FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS contact_name,
					(SELECT address FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS add_name,
					(SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1 LIMIT 1) As purchase_status,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = p.user_mod LIMIT 1 ) AS user_name,
					po.qty_order,
					po.qty_unit,
					po.qty_detail,
					po.price,
					po.disc_value,
					po.sub_total,
					p.net_total,
					p.discount_value,
					p.total_payment,
					p.paid,
					p.balance
				FROM 
					`tb_purchase_order` AS p,
					`tb_purchase_order_item` AS po 
				WHERE 
					p.id=po.purchase_id
					AND po.status=1 
					AND p.id = $id 
		";
		return $db->fetchAll($sql);
	}
	function getPruchaseProductDetail($search){//3
		$db = $this->getAdapter();
		$sql=" SELECT
					(SELECT name FROM `tb_sublocation` WHERE id=p.branch_id) AS branch_name,
					it.item_name,
					it.item_code,
					(SELECT name FROM `tb_category` WHERE id=it.cate_id LIMIT 1) AS cate_name,
					(SELECT name FROM `tb_brand` WHERE id=it.brand_id LIMIT 1) AS brand_name,
					(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_name,
					(SELECT v_phone FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS v_phone,
					(SELECT address FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS address,
					(SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1 LIMIT 1) As purchase_status,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = p.user_mod LIMIT 1 ) AS user_name,
					(SELECT name_kh FROM `tb_view` WHERE key_code = p.status AND `type`=5 LIMIT 1) As status,
					
					po.qty_order,
					po.price,
					po.disc_value,
					po.sub_total,
					
					p.net_total,
					p.discount_value,
					p.total_payment,
					p.paid,
					p.balance,
					p.order_number,
					p.date_order,
					p.remark
					
				FROM 
					tb_purchase_order AS p,
					tb_purchase_order_item AS po,
					tb_product AS it
				WHERE 
					p.id=po.purchase_id 
					AND it.id=po.pro_id
			
			";
		
		$from_date =(empty($search['start_date']))? '1': " p.date_order >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " p.date_order <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " it.item_name LIKE '%{$s_search}%'";
			$s_where[] = " it.item_code LIKE '%{$s_search}%'";
			$s_where[] = " it.barcode LIKE '%{$s_search}%'";
			$s_where[] = " p.order_number LIKE '%{$s_search}%'";
			$s_where[] = " p.total_payment LIKE '%{$s_search}%'";
			$s_where[] = " p.paid LIKE '%{$s_search}%'";
			$s_where[] = " p.balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['product_id']>0){
			$where .= " AND it.id =".$search['product_id'];
		}
		if($search['branch']>0){
			$where .= " AND p.branch_id =".$search['branch'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY p.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getPruchasePaymentById($id){
		$db = $this->getAdapter();
		$sql=" SELECT
					(SELECT name FROM `tb_sublocation` WHERE id=p.branch_id) AS branch_name,
					(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_name,
					p.order_number,
					
					v.remark,
				
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = v.user_id LIMIT 1 ) AS user_name,
					v.expense_date,
					v.payment_type,
					v.total,
					v.paid,
					v.balance,
					(SELECT name_kh FROM `tb_view` WHERE key_code = v.status AND `type`=5 LIMIT 1) As status
				FROM
					`tb_purchase_order` AS p,
					`tb_vendor_payment` AS v
				WHERE
					p.id=v.purchase_id
					AND p.status=1
					AND v.purchase_id = $id
			";
		return $db->fetchAll($sql);
	}
	
	function getPartnerServicePaymentById($id){
		$db = $this->getAdapter();
		$sql=" SELECT
					(SELECT name FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
					s.sale_no,
					pp.date_payment,
					pp.payment_type,
					pp.note,
					pp.total_payment,
					pp.paid,
					pp.balance,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = pp.user_id LIMIT 1 ) AS user_name,
					(SELECT name_kh FROM `tb_view` WHERE key_code = pp.status AND `type`=5 LIMIT 1) As status
				FROM
					`tb_sales_order` AS s,
					`tb_partnerservice_payment` AS pp
				WHERE
					s.id=pp.sale_order_id
					AND s.status=1
					AND pp.sale_order_id = $id
			";
		return $db->fetchAll($sql);
	}
	
	function getConstructorPaymentById($id){
		$db = $this->getAdapter();
		$sql=" SELECT
					(SELECT name FROM `tb_sublocation` WHERE id=m.branch_id) AS branch_name,
					m.invoice_no,
					(select name from tb_constructor as c where c.id = m.constructor) as constructor_name,
					(select phone from tb_constructor as c where c.id = m.constructor) as constructor_tel,
					mp.date_payment,
					mp.payment_type,
					mp.note,
					mp.total_payment,
					mp.paid,
					mp.balance,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = mp.user_id LIMIT 1 ) AS user_name,
					(SELECT name_kh FROM `tb_view` WHERE key_code = mp.status AND `type`=5 LIMIT 1) As status
				FROM
					`tb_mong` AS m,
					`tb_mong_constructor_payment` AS mp
				WHERE
					m.id=mp.mong_id
					AND m.status=1
					AND mp.mong_id = $id
			";
		return $db->fetchAll($sql);
	}
	
	function getAllProduct(){
		$db = $this->getAdapter();
		$sql="select id,item_name,item_code from tb_product where status=1 and is_service=0";
		return $db->fetchAll($sql);
	}
	
}

?>