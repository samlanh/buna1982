<?php
class Purchase_indexController extends Zend_Controller_Action {
	public function init() {
		/*
		 * Initialize action controller here
		 */
		defined ( 'BASE_URL' ) || define ( 'BASE_URL', Zend_Controller_Front::getInstance ()->getBaseUrl () );
		$tr = Application_Form_FrmLanguages::getCurrentlanguage ();
	}
	public function indexAction() {
		if ($this->getRequest ()->isPost ()) {
			$search = $this->getRequest ()->getPost ();
			$search ['start_date'] = date ( "Y-m-d", strtotime ( $search ['start_date'] ) );
			$search ['end_date'] = date ( "Y-m-d", strtotime ( $search ['end_date'] ) );
		} else {
			$search = array ('text_search' => '', 'start_date' => date ( "Y-m-d" ), 'end_date' => date ( "Y-m-d" ), 'suppliyer_id' => 0, 'branch_id' => - 1, 'status_paid' => - 1 );
		}
		$db = new Purchase_Model_DbTable_DbPurchaseOrder ();
		$rows = $db->getAllPurchaseOrder ( $search );
		$list = new Application_Form_Frmlist ();
		$columns = array ("BRANCH_NAME", "VENDOR_NAME", "PURCHASE_ORDER", "ORDER_DATE", "TOTAL_AMOUNT", "PAID", "BALANCE", "ORDER_STATUS", "STATUS", "BY_USER" );
		$link = array ('module' => 'purchase', 'controller' => 'index', 'action' => 'edit' );
		
		$this->view->list = $list->getCheckList ( 0, $columns, $rows, array ('branch_name' => $link, 'vendor_name' => $link, 'order_number' => $link, 'date_order' => $link ) );
		$formFilter = new Application_Form_Frmsearch ();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator ( $formFilter );
	}
	public function addAction() {
		if ($this->getRequest ()->isPost ()) {
			$data = $this->getRequest ()->getPost ();
			try {
				$db = new Purchase_Model_DbTable_DbPurchaseOrder ();
				if (! empty ( $data ['identity'] )) {
					$db->addPurchaseOrder ( $data );
				}
				Application_Form_FrmMessage::message ( "Purchase has been Saved!" );
				if (! empty ( $data ['btnsavenew'] )) {
					Application_Form_FrmMessage::redirectUrl ( "/purchase/index" );
				}
			} catch ( Exception $e ) {
				Application_Form_FrmMessage::message ( 'INSERT_FAIL' );
				echo $e->getMessage ();
				exit ();
			}
		}
		// /link left not yet get from DbpurchaseOrder
		$frm_purchase = new Application_Form_purchase ( null );
		$form_add_purchase = $frm_purchase->productOrder ();
		Application_Model_Decorator::removeAllDecorator ( $form_add_purchase );
		$this->view->form_purchase = $form_add_purchase;
		
		// item option in select
		$items = new Application_Model_GlobalClass ();
		$this->view->items = $items->getProductOption ();
		
		$formpopup = new Application_Form_FrmPopup ( null );
		// for add vendor
		$formStockAdd = $formpopup->popupVendor ( null );
		Application_Model_Decorator::removeAllDecorator ( $formStockAdd );
		$this->view->form_vendor = $formStockAdd;
		
		$db = new Application_Model_DbTable_DbGlobal ();
		$this->view->puchase_num = $db->getPurchaseNumber ( 1 );
		
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->category = $db->getAllProductCategory();
	}
	public function editAction() {
		$id = ($this->getRequest()->getParam('id'));
		if ($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			try{
				$db = new Purchase_Model_DbTable_DbPurchaseOrder();
				if(!empty($data['identity'])){
					$db->updatePurchaseOrder($data,$id);
				}
				Application_Form_FrmMessage::message("Purchase has been Saved!");
				if(!empty($data['btnsavenew'])){
					Application_Form_FrmMessage::redirectUrl("/purchase/index");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				echo $e->getMessage();
				exit();
			}
		}
		
		$db = new Purchase_Model_DbTable_DbPurchaseOrder();
		$this->view->row = $row = $db->getPurchaseById($id);
		$this->view->row_detail = $db->getPurchaseDetailById($id);
		
		// /link left not yet get from DbpurchaseOrder
		$frm_purchase = new Application_Form_purchase ( null );
		$form_add_purchase = $frm_purchase->productOrder ($row);
		Application_Model_Decorator::removeAllDecorator ( $form_add_purchase );
		$this->view->form_purchase = $form_add_purchase;
		
		// item option in select
		$items = new Application_Model_GlobalClass ();
		$this->view->items = $items->getProductOption ();
		
		$formpopup = new Application_Form_FrmPopup ( null );
		// for add vendor
		$formStockAdd = $formpopup->popupVendor ( null );
		Application_Model_Decorator::removeAllDecorator ( $formStockAdd );
		$this->view->form_vendor = $formStockAdd;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->puchase_num = $db->getPurchaseNumber(1);
		
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->category = $db->getAllProductCategory();
	}
	
	public function getqtybyidAction() {
		if ($this->getRequest ()->isPost ()) {
			$post = $this->getRequest ()->getPost ();
			$item_id = $post ['item_id'];
			$branch_id = $post ['branch_id'];
			$sql = "  SELECT `qty_perunit`,price,
  		                (SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=measure_id) as measue_name,
  		                unit_label,
						(SELECT qty FROM `tb_prolocation` WHERE location_id=$branch_id AND pro_id=$item_id LIMIT 1 ) AS qty 
						FROM tb_product WHERE id= $item_id LIMIT 1  ";
			$db = new Application_Model_DbTable_DbGlobal ();
			$row = $db->getGlobalDbRow ( $sql );
			echo Zend_Json::encode ( $row );
			exit ();
		}
	}
	
	public function refreshProductAction() {
		if ($this->getRequest ()->isPost ()) {
			$items = new Application_Model_GlobalClass ();
			$row = $items->getProductOption ();
			echo Zend_Json::encode ( $row );
			exit ();
		}
	}

}