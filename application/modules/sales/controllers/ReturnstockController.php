<?php
class Sales_ReturnstockController extends Zend_Controller_Action
{
public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function indexAction()
    {
    	$db = new Sales_Model_DbTable_DbReturnStock();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$level = $result["level"];
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    			'start_date'=>date("Y-m-d"),
    			'end_date'	=>date("Y-m-d"),
    			'status'	=>	-1,
    			'branch'		=>'',
    		);
    	}
		$rows = $db->getAllReturnStock($data);
		$columns=array("BRANCH_NAME","លេខកូដ","ចំណងជើង","តម្លៃសរុប","សម្គាល់","ថ្ងៃបង្កើត","អ្នកប្រើប្រាស់","ស្ថានការ");
		$link=array(
				'module'=>'sales','controller'=>'returnstock','action'=>'edit',
		);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows,array('branch_name'=>$link,'title'=>$link,'total_amount'=>$link,'return_code'=>$link));
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
        
	}
	public function addAction()
	{
			$db = new Sales_Model_DbTable_DbReturnStock();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->addReturnStock($post);
						Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ", '/sales/returnstock');
						Application_Form_FrmMessage::message("បញ្ចូលដោយជោគជ័យ");
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			$db = new Sales_Model_DbTable_DbReturnStock();
			$this->view->rsproduct = $db->getAllProductName();
			$this->view->return_code = $db->getReturnCode();
			
			$db = new Application_Model_DbTable_DbGlobal();
			$this->view->branch = $db->getAllBranch();
	}
	public function editAction()
	{
		$id = $this->getRequest()->getParam("id"); 
		$db = new Sales_Model_DbTable_DbReturnStock();
		if($this->getRequest()->isPost()){ 
			try{
				$post = $this->getRequest()->getPost();
				$db->editReturnStock($post,$id);
				Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ", '/sales/returnstock');
			  }catch (Exception $e){
			  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
			  }
		}
		$row = $db->getReturnStockById($id);
		$this->view->rs = $row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/sales/returnstock");
			exit();
		}
		$this->view->rs_detail = $db->getReturnStockDetailById($id);
		
		$this->view->rsproduct = $db->getAllProductName();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $db->getAllBranch();
	}
	
	function getproductAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbReturnStock();
			$rs =$db->getProductById($post['product_id']);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
}

