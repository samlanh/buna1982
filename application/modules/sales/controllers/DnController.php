<?php
class Sales_DnController extends Zend_Controller_Action
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
					'branch'		=>0,
					'customer_id'	=>-1,
					'is_complete'	=>'',
					);
		}
		$this->view->search = $search;
		
		$db = new Sales_Model_DbTable_DbDn();
		$rows = $db->getAllDn($search);
		$columns=array("BRANCH_NAME","ទីតាំងបុណ្យ","ឈ្មោះអតិថិជន","លេខទូរស័ព្ទ","លេខ DN","ថ្ងៃដឹកទំនិញ","អ្នកប្រើប្រាស់");
		$link=array(
			'module'=>'sales','controller'=>'dn','action'=>'edit',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(10, $columns, $rows, array('phone'=>$link,'place_bun'=>$link,'customer_name'=>$link,'dn_num'=>$link));
		
	    $formFilter = new Product_Form_FrmProduct();
	    $this->view->formFilter = $formFilter->productFilter();
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}	
	
	public function addAction()
	{
		$db = new Sales_Model_DbTable_DbDn();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				if(!empty($data['identity'])){
					$db->addDn($data);
				}
				Application_Form_FrmMessage::message("បញ្ចូលដោយជោគជ័យ",'/sales/index');
			}catch (Exception $e){
				Application_Form_FrmMessage::message('បញ្ចូលមិនត្រឹមត្រូវ');
				echo $e->getMessage();exit();
			}
		}
		
		$this->view->branch_id = $db->getBranchId();
		
		$db = new Sales_Model_DbTable_DbDn();
		$this->view->category = $db->getAllProductCategory();
		$this->view->rsproduct = $db->getAllProductName();
		$this->view->rscustomer = $db->getAllCustomerName();
		
		$form = new Sales_Form_FrmCustomer(null);
		$formpopup = $form->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->exchange_rate = $db->getExchangeRate();
		$this->view->branch = $db->getAllBranch();
		
	}
	
	public function editAction()
	{
		$id = $this->getRequest()->getParam('id');
		$db = new Sales_Model_DbTable_DbDn();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db->editDn($data,$id);
				Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ", '/sales/dn/index');
			}catch (Exception $e){
				Application_Form_FrmMessage::message('បញ្ចូលមិនត្រឹមត្រូវ');
				echo $e->getMessage();exit();
			}
		}

		$this->view->branch_id = $db->getBranchId();
		
		$db = new Sales_Model_DbTable_DbDn();
		
		$row = $db->getDnById($id);
		$this->view->row = $row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/sales/dn");
			exit();
		}
		$this->view->row_detail = $db->getDnDetailById($id);
		
		$this->view->category = $db->getAllProductCategory();
		$this->view->rsproduct = $db->getAllProductName();
		$this->view->rscustomer = $db->getAllCustomerName();
		
		$form = new Sales_Form_FrmCustomer(null);
		$formpopup = $form->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->exchange_rate = $db->getExchangeRate();
		$this->view->branch = $db->getAllBranch();
	}
	
	public function convertAction()
	{
		$id = $this->getRequest()->getParam('id');
		$db = new Sales_Model_DbTable_DbDn();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db->addDn($data);
				Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ", '/sales/dn/index');
			}catch (Exception $e){
				Application_Form_FrmMessage::message('បញ្ចូលមិនត្រឹមត្រូវ');
				echo $e->getMessage();exit();
			}
		}
	
		$this->view->branch_id = $db->getBranchId();
	
		$db = new Sales_Model_DbTable_Dbpos();
	
		$this->view->row = $db->getSaleById($id);
		$this->view->row_detail = $db->getSaleDetailById($id);
	
		$this->view->category = $db->getAllProductCategory();
		$this->view->rsproduct = $db->getAllProductName();
		$this->view->rscustomer = $db->getAllCustomerName();
	
		$form = new Sales_Form_FrmCustomer(null);
		$formpopup = $form->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
	
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->dn_num = $db->getDnNumber();
		$this->view->exchange_rate = $db->getExchangeRate();
	}
	
	function dnAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/dn");
		}
		
		$db = new Sales_Model_DbTable_DbDn();
		
		$row = $db->getDnById($id);
		$this->view->rs =$row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/sales/dn");
			exit();
		}
		
		$this->view->rsdetail = $db->getDnDetailById($id);
		
	}
	
	function getproductAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbQuotation();
			$rs =$db->getProductById($post['product_id'],$post['branch_id']);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
	function getTypeAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbQuotation();
			$type =$db->getType($post['product_id']);
			print_r(Zend_Json::encode($type));
			exit();
		}
	}
	
	function refreshProductAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbQuotation();
			$product = $db->getAllProductName();
			$service = $db->getAllProductName(1);
			$result = array('product'=>$product,'service'=>$service);
			print_r(Zend_Json::encode($result));
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
			$db = new Sales_Model_DbTable_DbQuotation();
			$rs = $db->getProductByCategoryId($post['category'],$post['type']);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
	function getdnnoAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$post['branch_id'] = empty($post['branch_id'])?1:$post['branch_id'];
			$_db = new Application_Model_DbTable_DbGlobal();
			$rs = $_db->getDnNumber($post['branch_id']);
			echo $rs;
			exit();
		}
	}
}