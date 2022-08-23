<?php
class Sales_IndexController extends Zend_Controller_Action
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
					'branch'		=>-1,
					'customer_id'	=>-1,
					'is_complete'	=>'',
					);
		}
		$this->view->search = $search;
		
		$db = new Sales_Model_DbTable_DbSaleOrder();
		$rows = $db->getAllSaleOrder($search);
		$columns=array("BRANCH_NAME","ទីតាំងបុណ្យ","ឈ្មោះអតិថិជន","លេខទូរស័ព្ទ","ឈ្មោះសព","លេខវិក័យបត្រ","ថ្ងៃលក់","តម្លៃសរុប","ប្រាក់បានបង់","ប្រាក់នៅខ្វះ","អ្នកប្រើប្រាស់");
		$link=array(
			'module'=>'sales','controller'=>'possale','action'=>'edit',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(10, $columns, $rows, array('phone'=>$link,'branch_name'=>$link,'customer_name'=>$link,'program_name'=>$link,'place_bun'=>$link));
		
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
	
}