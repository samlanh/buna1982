<?php
class Mong_IndexController extends Zend_Controller_Action
{
	public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
//     protected function GetuserInfoAction(){
//     	$user_info = new Application_Model_DbTable_DbGetUserInfo();
//     	$result = $user_info->getUserInfo();
//     	return $result;
//     }
// 	function updatecodeAction(){
// 		$db = new Product_Model_DbTable_DbProduct();
// 		$db->getProductCoded();
// 	}
    public function indexAction()
    {
    	$db = new Mong_Model_DbTable_DbIndex();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    			'ad_search'		=>	'',
    			'customer_id'	=>	-1,
				'status'		=>	-1,
    			'is_complete'	=>	'',
    			'branch'		=>	'',
    			'start_date'	=>date("Y-m-d"),
				'end_date'		=>date("Y-m-d"),
    		);
    	}
    	$this->view->search = $data;
    	
		$rows = $db->getAllMong($data);
		$columns=array("BRANCH_NAME","វិក័យបត្រ","ទីតាំងបុណ្យ","អតិថិជន","ឈ្មោះអ្នកស្លាប់","ប្រភេទ","លេខកូដម៉ុង","ជាងសាងសង់","ថ្ងៃលក់","តម្លៃសរុប","បានបង់","នៅខ្វះ","សម្គាល់","USER","ស្ថានការ");
		$link=array(
				'module'=>'mong','controller'=>'index','action'=>'edit',
		);

		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(10, $columns, $rows,array('branch_name'=>$link,'invoice_no'=>$link,'customer_name'=>$link,'sale_date'=>$link,'dead_id'=>$link,'place_bun'=>$link));
    	
		$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    	
	}
	
	function lastreceiptAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Sales_Model_DbTable_DbSaleOrder();
		$last_receipt_id = $db->getLastReceipt($id,2); // 2=mong receipt
		if(!empty($last_receipt_id)){
			$this->_redirect("/mong/customerpayment/receipt/id/".$last_receipt_id);
		}else{
			Application_Form_FrmMessage::Sucessfull("មិនមានទិន្នន័យ", '/mong/index/index');
		}
	}
	
	public function addAction()
	{
		$db = new Mong_Model_DbTable_DbIndex();
		if($this->getRequest()->isPost()){ 
			try{
				$data = $this->getRequest()->getPost();
				$db->addMong($data);
				Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ", '/mong/index/index');
			  }catch (Exception $e){
			  	Application_Form_FrmMessage::messageError("បញ្ចូលមិនត្រឹមត្រូវ",$err = $e->getMessage());
			  }
		}
		
		$this->view->branch_id = $db->getBranchId();
		
		$this->view->mong_type = $db->getMongType();
		$this->view->person_in_charge = $db->getPersonInCharge();
		$this->view->all_dead = $db->getDeadPerson();
		
		$this->view->constructor = $db->getConstructor();
		
		$_db=new Sales_Model_DbTable_DbProgram();	
		$this->view->khmer_year = $_db->getAllKhmerYear();
		
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->rsproduct = $db->getAllProductName();
		$this->view->rsservice = $db->getAllProductName(1);
		$this->view->rscustomer = $db->getAllCustomerName();
		$this->view->category = $db->getAllProductCategory();
		
		$this->view->sale_agent = $db->getAllSaleagent();	
		$this->view->receiver_name = $db->getAllReceiverName();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->diepeople = $db->getAllDiePeople();
		$this->view->branch = $db->getAllBranch();
		
		$form = new Sales_Form_FrmCustomer(null);
		$formpopup = $form->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->exchange_rate = $db->getExchangeRate();
	//	print_r($this->view->sale_agent); exit();
	}
	public function editAction()
	{
		$id = ($this->getRequest()->getParam('id'));
		$db = new Mong_Model_DbTable_DbIndex();
		if($this->getRequest()->isPost()){
		//	$data["id"] = $id;
			try{
				$data = $this->getRequest()->getPost();
				$db->editMong($data,$id);
				Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ", '/mong/index/index');
			  }catch (Exception $e){
			  	Application_Form_FrmMessage::message("កែប្រែមិនត្រឹមត្រូវ");
			  	echo $e->getMessage();exit();
			  }			 
		}
		$row = $db->getMongAll($id);
		$this->view->row =$row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/mong/index");
			exit();
		}
		
		$this->view->row_detail = $db->getMongDetailById($id);
		
		$this->view->branch_id = $db->getBranchId();
		
		$this->view->mong_type = $db->getMongType();
		$this->view->person_in_charge = $db->getPersonInCharge();
		$this->view->all_dead = $db->getDeadPerson();		
		$this->view->constructor = $db->getConstructor();
		
		$_db=new Sales_Model_DbTable_DbProgram();	
		$this->view->khmer_year = $_db->getAllKhmerYear();
		
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->rsproduct = $db->getAllProductName();
		$this->view->rsservice = $db->getAllProductName(1);
		$this->view->customer_id = $db->getAllCustomerName();
		$this->view->category = $db->getAllProductCategory();
		
		$this->view->sale_agent = $db->getAllSaleagent();
		$this->view->receiver_name = $db->getAllReceiverName();
//		print_r($this->view->sale_agent); exit();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->invoice = $db->getInvoiceNumber(1);
//		$this->view->sale_agent = $db->getSaleAgent();
		$this->view->diepeople = $db->getAllDiePeople();
		$this->view->branch = $db->getAllBranch();
		
		$form = new Sales_Form_FrmCustomer(null);
		$formpopup = $form->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->exchange_rate = $db->getExchangeRate();
	}
	
	public function goodtimeAction()
	{
		$id = $this->getRequest()->getParam("id");
		$db = new Mong_Model_DbTable_DbIndex();
		
		$this->view->row = $db->getGoodtimeById($id);
	}
	public function timemolAction()
	{
		$id = $this->getRequest()->getParam("id");
		$db = new Mong_Model_DbTable_DbIndex();

		$this->view->row = $db->getTimemolById($id);
	}
		
	public function invoiceAction()
	{
		$id = $this->getRequest()->getParam("id");
		$db = new Mong_Model_DbTable_DbIndex();
		$row = $this->view->row = $db->getInvoiceById($id);
		$row_detail = $this->view->row_detail = $db->getInvoiceDetailById($id);	
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->exchange_rate = $db->getExchangeRateSell();
	}
	
	
	function getDeadDetailAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Mong_Model_DbTable_DbIndex();
			$detail = $db->getDeadDetail($data['dead_id']);
			print_r(Zend_Json::encode($detail));
			exit();
		}
	}
	
	function getConstructorDetailAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Mong_Model_DbTable_DbIndex();
			$detail = $db->getConstructorDetail($data['constructor']);
			print_r(Zend_Json::encode($detail));
			exit();
		}
	}
	
	function getItemPriceAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Mong_Model_DbTable_DbIndex();
			$price = $db->getItemPrice($data['item_id']);
			print_r(Zend_Json::encode($price));
			exit();
		}
	}
	
	function refreshProductAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpos();
			$product = $db->getAllProductName(2);
			print_r(Zend_Json::encode($product));
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
	
}