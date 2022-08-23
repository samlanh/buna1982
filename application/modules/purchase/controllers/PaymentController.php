<?php
class Purchase_PaymentController extends Zend_Controller_Action
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
					'ad_search'=>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
					'branch'=>-1,
					'purchase_id'=>-1,
					);
		}
		$db = new Purchase_Model_DbTable_Dbpayment();
		$rows = $db->getAllReciept($search);
		$columns=array("សាខា","អតិថិជន","លេខបញ្ជាទិញ","ថ្ងៃចំណាយ","ចំណាយជា","តម្លៃសរុប","ប្រាក់បានបង់","នៅខ្វះ","អ្នកប្រើប្រាស់","ស្ថានភាព");
		$link=array(
				'module'=>'purchase','controller'=>'payment','action'=>'edit',
		);
// 		$link1=array(
// 				'module'=>'sales','controller'=>'index','action'=>'viewapp',
// 		);

		$this->view->search = $search;
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('vendor_name'=>$link,'order_number'=>$link,'branch_name'=>$link,
				'payment_type'=>$link));
		
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	    
	    $db = new Purchase_Model_DbTable_Dbpayment();
	    $this->view->purchase = $db->getAllPurchase();
	    
	}	
	function addAction(){
		$db = new Purchase_Model_DbTable_Dbpayment();
		try {
			if($this->getRequest()->isPost()) {
				$data = $this->getRequest()->getPost();
				$db->addPurchasePayment($data);
				Application_Form_FrmMessage::message("INSERT_SUCESS");
				Application_Form_FrmMessage::redirectUrl("/purchase/payment/");
			}
		}catch (Exception $e){
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();
		}
		 
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $_db->getAllBranch();
		
	}
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'):'0';
		$db = new Purchase_Model_DbTable_Dbpayment();
		try {
			if($this->getRequest()->isPost()) {
				$data = $this->getRequest()->getPost();
				$db->updatePurchasePayment($data,$id);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/purchase/payment");
			}
		}catch (Exception $e){
			Application_Form_FrmMessage::message('UPDATE_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
		$this->view->row = $db->getPaymentById($id);
		$this->view->all_vendor = $db->getAllVendor();
		$this->view->purchase_num = $db->getAllPurchaseNo();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $_db->getAllBranch();
	}
	
	public function getinvoiceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Purchase_Model_DbTable_Dbpayment();
			$rs = $db->getAllInvoicePaymentPurchase($post['post_id'],$post['action']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}	
	
	function getpurchasebybranchAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$post['branch_id'] = empty($post['branch_id'])?1:$post['branch_id'];
			$post['postype'] = empty($post['postype'])?1:$post['postype'];
				
			$db = new Purchase_Model_DbTable_Dbpayment();
			$rs = $db->getAllPurchaseNoByBranch($post);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
}