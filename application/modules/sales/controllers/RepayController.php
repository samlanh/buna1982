<?php
class Sales_RepayController extends Zend_Controller_Action
{
	public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	$db = new Sales_Model_DbTable_DbRepay();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    			'ad_search'		=>	'',
    			'start_date'	=>	date("Y-m-d"),
    			'end_date'		=>	date("Y-m-d"),
     			'status'		=>	-1,
    			'type'			=>	2,
    		);
    	}
		$rows = $db->getAllRepay($data);
		$columns=array("BRANCH_NAME","ឈ្មោះអ្នកសង","ភេទ","លេខទូរស័ព្ទ","ថ្ងៃសង","ចំនួនទឹកប្រាក់ខ្ចី","កំណត់សម្គាល់","ស្ថានការ");
		$link=array('module'=>'sales','controller'=>'repay','action'=>'edit',);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows,array('name_borrow'=>$link,'notesss'=>$link));
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$db = new Sales_Model_DbTable_DbRepay();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->addRepays($post);
						Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ", '/sales/repay/index');
						Application_Form_FrmMessage::message("បញ្ចូលដោយជោគជ័យ");
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("បញ្ចូលមិនត្រឹមត្រូវ",$err = $e->getMessage());
				  }
			}
 			$this->view->name_pay = $db->getAllRepays();
			$form = new Sales_Form_FrmCustomer(null);
			$formpopup = $form->Formcustomer(null);
			Application_Model_Decorator::removeAllDecorator($formpopup);
			
			$db = new Application_Model_DbTable_DbGlobal();
			$this->view->branch = $db->getAllBranch();
	}
	function editAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Sales_Model_DbTable_DbRepay();
			if($this->getRequest()->isPost()){
				try{
					$post = $this->getRequest()->getPost();
					$db->updateRepay($post, $id);

						Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ", '/sales/repay/index');
						Application_Form_FrmMessage::message("កែប្រែដោយជោគជ័យ");

				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("កែប្រែមិនត្រឹមត្រូវ",$err = $e->getMessage());
				  }
			}	
			$row = $db->getRepayById($id);	
			$this->view->row = $row;
			if (empty($row)){
				Application_Form_FrmMessage::Sucessfull("NO_RECORD","/sales/repay/index");
				exit();
			}
// 			$this->view->name_pay = $db->getAllRepays();
			$post =array(
					'branch_id' =>$row['branch_id'],
					'notOpt' =>1
					);
			$this->view->name_pay = $db->getAllRepaysOption($post);
			
			$form = new Sales_Form_FrmCustomer(null);
			$formpopup = $form->Formcustomer(null);
			Application_Model_Decorator::removeAllDecorator($formpopup);	

			$db = new Application_Model_DbTable_DbGlobal();
			$this->view->branch = $db->getAllBranch();
	}

	function getRepayDetailAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbRepay();
// 			$detail = $db->getRepayDetail($data['name_borrow']);
			$detail = $db->getRepayDetailByBranch($data);
			print_r(Zend_Json::encode($detail));
			exit();
		}
	}
	
	function getborrowerbybranchAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$post['branch_id'] = empty($post['branch_id'])?1:$post['branch_id'];
				
			$db = new Sales_Model_DbTable_DbRepay();
			$rs = $db->getAllRepaysOption($post);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
}