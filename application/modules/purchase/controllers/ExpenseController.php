<?php

class Purchase_ExpenseController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/purchase/expense';
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
    	try{
    		$db = new Purchase_Model_DbTable_DbExpense();
    		if($this->getRequest()->isPost()){
    			$formdata=$this->getRequest()->getPost();
    			$formdata['start_date']=date("Y-m-d",strtotime($formdata['start_date']));
    			$formdata['end_date']=date("Y-m-d",strtotime($formdata['end_date']));
    		}
    		else{
    			$formdata = array(
    					"adv_search"=>'',
    					"branch_id"=>"",
    					"status"=>-1,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		
			$rs_rows= $db->getAllExpense($formdata);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		//$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmlist();
    		$collumns = array("BRANCH_NAME","ពណ៌នាចំនាយ","លេខបង្កាន់ដៃ","TOTAL_EXPENSE","NOTE","DATE","BY_USER","STATUS","បោះពុម្ភ");
    		$link=array(
    				'module'=>'purchase','controller'=>'expense','action'=>'edit',
    		);
    		$link1=array(
    				'module'=>'report','controller'=>'index','action'=>'rpt-expense-detail',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('branch_name'=>$link,'expense_title'=>$link,'receipt'=>$link,'note'=>$link,'total_amount'=>$link,'បង្កាន់ដៃ'=>$link1));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Purchase_Model_DbTable_DbExpense();				
			try {
				$db->addexpense($data);
					Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ","/purchase/expense");
					Application_Form_FrmMessage::message("បញ្ចូលដោយជោគជ័យ");			
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("បញ្ចូលមិនត្រឹមត្រូវ");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->expense = $optexpense = $db->getAllExpense();
		$this->view->receipt = $db->getExpenseReceiptNumber(1);
		$this->view->branch = $db->getAllBranch();
		
    }
 
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Purchase_Model_DbTable_DbExpense();				
			try {
				$db->updateExpense($data,$id);				
				Application_Form_FrmMessage::Sucessfull('ការកែប្រែ​​ជោគ​ជ័យ', self::REDIRECT_URL);		
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("ការកែប្រែមិនត្រឹមត្រូវ");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$db = new Purchase_Model_DbTable_DbExpense();
		$row  = $db->getexpensebyid($id);
		$this->view->row = $row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD",self::REDIRECT_URL);
			exit();
		}
		$this->view->row_detail = $db->getexpenseDetailbyid($id);
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->expense = $optexpense = $db->getAllExpense();
		$this->view->branch = $db->getAllBranch();
    }

    function getinvocenoAction(){
    	if($this->getRequest()->isPost()){
    		$post=$this->getRequest()->getPost();
    		$post['branch_id'] = empty($post['branch_id'])?1:$post['branch_id'];
    		$_db = new Application_Model_DbTable_DbGlobal();
    		$rs = $_db->getExpenseReceiptNumber($post['branch_id']);
    		echo $rs;
    		exit();
    	}
    }
}







