<?php
class Sales_PossaleController extends Zend_Controller_Action
{	
    public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
	
	public function indexAction()
	{
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'ad_search'		=>'',
					'start_date'	=>date("Y-m-d"),
					'end_date'		=>date("Y-m-d"),
					'branch'		=>'',
					'customer_id'	=>-1,
					'status'		=>-1,
					'is_complete'	=>'',
				);
		}
		$this->view->search = $search;
		
		$db = new Sales_Model_DbTable_DbSaleOrder();
		$rows = $db->getAllSaleOrder($search);
		$columns=array("BRANCH_NAME","ទីតាំងបុណ្យ","ឈ្មោះអតិថិជន","លេខទូរស័ព្ទ","លេខវិក័យបត្រ","ថ្ងៃលក់","តម្លៃសរុប","ប្រាក់បានបង់","ប្រាក់នៅខ្វះ","អ្នកប្រើប្រាស់","ស្ថានការណ៍");
		$link=array(
			'module'=>'sales','controller'=>'possale','action'=>'edit',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(10, $columns, $rows, array('phone'=>$link,'branch_name'=>$link,'customer_name'=>$link,'sale_no'=>$link,'place_bun'=>$link));
		
	    $formFilter = new Product_Form_FrmProduct();
	    $this->view->formFilter = $formFilter->productFilter();
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}	
	
	function lastreceiptAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Sales_Model_DbTable_DbSaleOrder();
		$last_receipt_id = $db->getLastReceipt($id,1);  // 1=sale receipt
		if(!empty($last_receipt_id)){
			$this->_redirect("/sales/payment/receipt/id/".$last_receipt_id);
		}
	}
	
	public function addAction()
	{
		$db = new Sales_Model_DbTable_Dbpos();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				if(!empty($data['identity'])){
					$db->addSaleOrder($data);
				}
				Application_Form_FrmMessage::message("INSERT_SUCESS",'/sales/possale');
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$this->view->branch_id = $db->getBranchId();
		
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->category = $db->getAllProductCategory();
		$this->view->rsproduct = $db->getAllProductName();
		$this->view->rsservice = $db->getAllProductName(1);
		$this->view->rscustomer = $db->getAllCustomerName();
		$this->view->partner = $db->getAllPartnerService();
		
		$this->view->sale_agent = $db->getAllSaleagent();
		$this->view->receiver_name = $db->getAllReceiverName();
		
		$form = new Sales_Form_FrmCustomer(null);
		$formpopup = $form->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
		
		$db = new Application_Model_DbTable_DbGlobal();
// 		$this->view->invoice = $db->getInvoiceNumber(1);
//		$this->view->saleagent = $db->getSaleAgent();
		$this->view->diepeople = $db->getAllDiePeople();
	
		$this->view->exchange_rate = $db->getExchangeRate();
		$this->view->branch = $db->getAllBranch();
		
	}

	public function editAction()
	{
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/index");
		}
		$query = new Sales_Model_DbTable_Dbpos();
		
		$db = new Sales_Model_DbTable_Dbpos();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			//print_r($data);exit();
			try {
				if(!empty($data['identity'])){
					$db->editSale($data,$id);
				}
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS", '/sales/possale/index');
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				echo $e->getMessage();
			}
		}
		$db = new Sales_Model_DbTable_Dbpos();
		
		$row = $db->getSaleById($id);
		$this->view->row = $row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/sales/possale");
			exit();
		}
		
		$this->view->row_detail = $db->getSaleDetailById($id);
		//print_r($this->view->row_detail);exit();
		$this->view->row_partner = $db->getPartnerServiceById($id);
		
		$this->view->sale_agent = $db->getAllSaleagent();
		
		$this->view->rsproduct = $db->getAllProductName();
		$this->view->rsservice = $db->getAllProductName(1);
		$this->view->rscustomer = $db->getAllCustomerName();
		
		$this->view->partner = $db->getAllPartnerService();
		$this->view->receiver_name = $db->getAllReceiverName();
		$this->view->category = $db->getAllProductCategory();
	
		$form = new Sales_Form_FrmCustomer(null);
		$formpopup = $form->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->invoice = $db->getInvoiceNumber(1);
//		$this->view->saleagent = $db->getSaleAgent();
		$this->view->diepeople = $db->getAllDiePeople();
		$this->view->branch = $db->getAllBranch();
	
		$db = new Sales_Model_DbTable_Dbexchangerate();
		$this->view->rsrate= $db->getExchangeRate();
	}
	
	public function convertAction()
	{
		$id = $this->getRequest()->getParam('id');
		$db = new Sales_Model_DbTable_Dbpos();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db->addSaleOrder($data,$id);
				Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ", '/sales/index/index');
			}catch (Exception $e){
				Application_Form_FrmMessage::message('បញ្ចូលមិនត្រឹមត្រូវ');
				echo $e->getMessage();
			}
		}
		$_db = new Sales_Model_DbTable_DbQuotation();
		$this->view->row = $_db->getQuoteById($id);
		$this->view->row_detail = $_db->getQuoteDetailById($id);
		
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->rsproduct = $db->getAllProductName();
		$this->view->rsservice = $db->getAllProductName(1);
		$this->view->rscustomer = $db->getAllCustomerName();
		$this->view->partner = $db->getAllPartnerService();
		$this->view->receiver_name = $db->getAllReceiverName();
		$this->view->category = $db->getAllProductCategory();
	
		$form = new Sales_Form_FrmCustomer(null);
		$formpopup = $form->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
	
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->invoice = $db->getInvoiceNumber(1);
		$this->view->saleagent = $db->getSaleAgent();
		$this->view->diepeople = $db->getAllDiePeople();
	
		$db = new Sales_Model_DbTable_Dbexchangerate();
		$this->view->rsrate= $db->getExchangeRate();
	}
	
	
	public function deleteAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Sales_Model_DbTable_Dbpos();
		echo "<script language='javascript'>
		var txt;
		var r = confirm('តើលោកអ្នកពិតចង់លុបប្រតិបត្តិការណ៍នេះឫ!');
		if (r == true) {";
			echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/sales/possale/deleteitem/id/".$id."'";
		echo"}";
		echo"else {";
			echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/sales/index/'";
		echo"}
		</script>";
	}
	function deleteitemAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Sales_Model_DbTable_Dbpos();
		$db->deleteSale($id);
		$this->_redirect("sales/index");
	}
	function invoiceAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/index");
		}
		$query = new Sales_Model_DbTable_Dbpos();
		$rs = $query->getSaleById($id);
		$this->view->rs = $rs;
		$this->view->rsdetail = $query->getSaleDetailById($id);
		if(empty($rs)){
			$this->_redirect("/sales/");
		}
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->exchange_rate = $db->getExchangeRateSell();
	}
	function comlistingAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/index");
		}
		$query = new Sales_Model_DbTable_Dbpos();
		$rs = $query->getSaleById($id);
		$this->view->rs = $rs;
		$this->view->rsdetail = $query->getSaleDetailById($id);
		if(empty($rs)){
			$this->_redirect("/sales/");
		}
	}
		
	function getproductAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpos();
			$rs =$db->getProductById($post['product_id'],$post['branch_id']);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
	function getServicePartnerPriceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpos();
			$fee =$db->getServicePartnerPrices($post['partner_id'],$post['service_id']);
			print_r(Zend_Json::encode($fee));
			exit();
		}
	}
	
	function getTypeAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpos();
			$type =$db->getType($post['product_id']);
			print_r(Zend_Json::encode($type));
			exit();
		}
	}
	
	function refreshProductAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpos();
			$product = $db->getAllProductName();
			$service = $db->getAllProductName(1);
			$result = array('product'=>$product,'service'=>$service);
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
	function refreshProgramAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Mong_Model_DbTable_DbIndex();
			//$program = $db->getDeadPerson();
			$program = $db->getRefreshProgram();
			print_r(Zend_Json::encode($program));
			exit();
		}
	}

	function getPackageproductAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpos();
			$package =$db->getPackageProduct($post['product_id']);
			print_r(Zend_Json::encode($package));
			exit();
		}
	}
	
	function getProductbycategoryAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpos();
			$rs = $db->getProductByCategoryId($post['category'],$post['type']);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
	function expensenumberAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$post['branch_id'] = empty($post['branch_id'])?1:$post['branch_id'];
			$_db = new Application_Model_DbTable_DbGlobal();
			$rs = $_db->getInvoiceNumber($post['branch_id']);
			echo $rs;
			exit();
		}
	}
	
	function getinvocenoAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$post['branch_id'] = empty($post['branch_id'])?1:$post['branch_id'];
			$_db = new Application_Model_DbTable_DbGlobal();
			$rs = $_db->getInvoiceNumber($post['branch_id']);
			echo $rs;
			exit();
		}
	}
}