<?php
class Mong_ConstructorpaymentController extends Zend_Controller_Action
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
					'mong_id'=>"",
				);
		}
		$db = new Mong_Model_DbTable_DbConstructorPayment();
		$rows = $db->getAllConstructorPayment($search);
		$columns=array("សាខា","បង់លើវិក័យបត្រ","ទីតាំងបុណ្យ","កាលបរិច្ឋេទ","បង់ជា","តម្លៃសរុប","ប្រាក់បានបង់","នៅខ្វះ","សម្គាល់","អ្នកប្រើប្រាស់","ស្ថានភាព");
		$link=array(
			'module'=>'mong','controller'=>'constructorpayment','action'=>'edit',
		);
 		
 		$delete=array(
 				'module'=>'mong','controller'=>'constructorpayment','action'=>'deleteitem',);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('invoice_no'=>$link,'payment_type'=>$link,'branch_name'=>$link,
				'date_input'=>$link));
		
		$this->view->mong_invoice = $db->getPartnerPaymentBalance();
		$this->view->search = $search;
		
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}	
	function addAction(){
		$db = new Mong_Model_DbTable_DbConstructorPayment();
		
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db->addConstructorPayment($data);
				Application_Form_FrmMessage::message("INSERT_SUCESS");
				Application_Form_FrmMessage::redirectUrl("/mong/constructorpayment/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				echo $e->getMessage();
			}
		}
		
		$this->view->invoice = $db->getMongInvoice();
		$this->view->constructor = $db->getConstructorInvoice();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $_db->getAllBranch();
		
	}
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Mong_Model_DbTable_DbConstructorPayment();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db->updateConstructorPayment($data,$id);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/mong/constructorpayment");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $db->getConstructorPaymentById($id);
		$this->view->row = $row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/mong/constructorpayment");
			exit();
		}
// 		$this->view->invoice = $db->getMongInvoice();
// 		$this->view->constructor = $db->getConstructorInvoice();
		
		$post = array(
				'branch_id' =>$row['branch_id'],
				'mong_id' =>$row['mong_id'],
				'notOpt' 	=>1,
				'postype' 	=>1,
				'edit' 	=>1,
		);
		$this->view->invoice = $db->getAllMongInvoice($post);
		$post['postype'] =2;
		$this->view->constructor = $db->getAllMongInvoice($post);
		
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $_db->getAllBranch();
	}	
	
	public function getinvoiceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db->getAllInvoicePayment($post['post_id'], $post['type_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	function receiptAction(){
		$dbq = new Mong_Model_DbTable_DbConstructorPayment();
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$this->view->rs = $dbq->getRecieptById($id);
	}
	public function deleteAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Sales_Model_DbTable_Dbpos();
		echo "<script language='javascript'>
		var r = confirm('តើលោកអ្នកពិតចង់លុបប្រតិបត្តិការណ៍នេះឫ!')​​;
		if (r == true) {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/mong/constructorpayment/deleteitem/id/".$id."'";
		echo"}else {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/mong/constructorpayment/'";
		echo"}
		</script>";
	
	}
	function deleteitemAction(){
		$id = $this->getRequest()->getParam("id");
		//echo $id;exit();
		$db = new Mong_Model_DbTable_DbConstructorPayment();
		$db->delettePayment($id);
		$this->_redirect("sales/payment");
	}
	
	public function getCustomerInfoAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Mong_Model_DbTable_DbConstructorPayment();
			$rs = $db->getCustomerInfo($post['id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
	public function getConstructorPaymentAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Mong_Model_DbTable_DbConstructorPayment();
			$rs = $db->getConstructorPayment($post['mong_id'],$post['action']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
	function getconstructbybranchAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$post['branch_id'] = empty($post['branch_id'])?1:$post['branch_id'];
			$post['postype'] = empty($post['postype'])?1:$post['postype'];
	
			$db = new Mong_Model_DbTable_DbConstructorPayment();
			$rs = $db->getAllMongInvoice($post);
			
// 			$db = new Mong_Model_DbTable_DbCustomerPayment();
// 			$rs = $db->getMongAllCustomerName($post);
			// 			echo Zend_Json::encode($rs);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
}