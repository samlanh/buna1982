<?php
class Sales_BorrowersController extends Zend_Controller_Action
{
	public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	$db = new Sales_Model_DbTable_DbBorrowers();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    			'branch'	=>	'',
    			'start_date'	=>	date("Y-m-d"),
    			'end_date'		=>	date("Y-m-d"),
     			'status'	=>	-1,
    		);
    	}
		$rows = $db->getAllBorrower($data);
		$columns=array("BRANCH_NAME","ឈ្មោះអ្នកខ្ចី","ភេទ","លេខទូរស័ព្ទ","ថ្ងៃខ្ចី","ចំនួនប្រាក់ខ្ចី","កំណត់សម្គាល់","ស្ថានការ");
		$link=array('module'=>'sales','controller'=>'borrowers','action'=>'edit',);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows,array('name_borrow'=>$link,'description'=>$link));
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	public function addAction()
	{
		$db = new Sales_Model_DbTable_DbBorrowers();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->addBorrowers($post);
						Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ", '/sales/borrowers/index');
						Application_Form_FrmMessage::message("បញ្ចូលដោយជោគជ័យ");
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("បញ្ចូលមិនត្រឹមត្រូវ",$err = $e->getMessage());
				  }
			}
			$this->view->name_borrow = $db->getAllBorrowers();
			$form = new Sales_Form_FrmCustomer(null);
			$formpopup = $form->Formcustomer(null);
			Application_Model_Decorator::removeAllDecorator($formpopup);
			
			$db = new Application_Model_DbTable_DbGlobal();
			$this->view->branch = $db->getAllBranch();
	}
	function editAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Sales_Model_DbTable_DbBorrowers();
			if($this->getRequest()->isPost()){
				try{
					$post = $this->getRequest()->getPost();
					$db->updateBorrow($post, $id);

						Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ", '/sales/borrowers/index');
						Application_Form_FrmMessage::message("កែប្រែដោយជោគជ័យ");

				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("កែប្រែមិនត្រឹមត្រូវ",$err = $e->getMessage());
				  }
			}	
			$row = $db->getBorrowById($id);
			$this->view->row = $row;
			if (empty($row)){
				Application_Form_FrmMessage::Sucessfull("NO_RECORD","/sales/borrowers/index");
				exit();
			}	
			$this->view->name_borrow = $db->getAllBorrowers();
			$form = new Sales_Form_FrmCustomer(null);
			$formpopup = $form->Formcustomer(null);
			Application_Model_Decorator::removeAllDecorator($formpopup);
			
			$db = new Application_Model_DbTable_DbGlobal();
			$this->view->branch = $db->getAllBranch();
	}	
	function getBorrowersDetailAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbBorrowers();
			$detail = $db->getBorrowersDetail($data['name_borrow']);
			print_r(Zend_Json::encode($detail));
			exit();
		}
	}	
}