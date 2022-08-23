<?php
class Sales_PartnerservicepaymentController extends Zend_Controller_Action
{	
	
    public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
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
					'sale_order_id'=>"",
				);
		}
		$db = new Sales_Model_DbTable_DbPartnerServicepayment();
		$rows = $db->getAllPartnerPayment($search);
		$columns=array("សាខា","ដៃគូរសេវាកម្ម","កាលបរិច្ឋេទ","បង់ជា","តម្លៃសរុប","សម្គាល់","អ្នកប្រើប្រាស់","ស្ថានភាព");
		$link=array(
			'module'=>'sales','controller'=>'partnerservicepayment','action'=>'edit',
		);
 		
 		$delete=array(
 				'module'=>'sales','controller'=>'partnerservicepayment','action'=>'deleteitem',);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(10, $columns, $rows, array('total_payment'=>$link,'partner_name'=>$link,'branch_name'=>$link,
				'date_input'=>$link));
		
		$this->view->sale_invoice = $db->getPartnerPaymentBalance();
		$this->view->search = $search;
		
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}	
	function addAction(){
		$db = new Sales_Model_DbTable_DbPartnerServicepayment();
		
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db->addPartnerServicePayment($data);
				Application_Form_FrmMessage::message("INSERT_SUCESS");
				Application_Form_FrmMessage::redirectUrl("/sales/partnerservicepayment/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				echo $e->getMessage();
			}
		}
		
		$this->view->invoice = $db->getSaleInvoice();
		$this->view->partner = $db->getAllPartner();
		
		$_db = new Application_Model_DbTable_DbGlobal();
// 		$this->view->receipt = $_db->getReceiptNumber(1);
		$this->view->branch = $_db->getAllBranch();
		
	}
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Sales_Model_DbTable_DbPartnerServicepayment();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data['id']=$id;
			try {
				$db->updatePartnerServicePayment($data,$id);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/partnerservicepayment");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$row= $db->getPartnerSerivcePaymentById($id);
		$this->view->row = $row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/sales/partnerservicepayment");
			exit();
		}
		
		$this->view->invoice = $db->getSaleInvoice();
		$this->view->partner = $db->getAllPartner();
		
		$_db = new Application_Model_DbTable_DbGlobal();
// 		$this->view->receipt = $_db->getReceiptNumber(1);
		$this->view->branch = $_db->getAllBranch();
	}	
	
	function printAction(){
		$dbq = new Sales_Model_DbTable_DbPartnerServicepayment();
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$this->view->rs = $dbq->getPartnerPaymentById($id);
	}
	
	public function getPartnerServicePaymentAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbPartnerServicepayment();
			$rs = $db->getPartnerSerivcePayment($post['sale_order_id'],$post['action']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
	
	public function getAllserviceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbPartnerServicepayment();
			$post['branch_id'] = empty($post['branch_id'])?1:$post['branch_id'];
			$rs = $db->getAllService($post['partner_id'],$post['action'],$post['branch_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
	public function getPaidserviceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbPartnerServicepayment();
			$rs = $db->getPaidService($post['id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
}