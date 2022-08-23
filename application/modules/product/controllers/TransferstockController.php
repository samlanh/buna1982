<?php
class Product_TransferstockController extends Zend_Controller_Action
{
	const REDIRECT_URL_ADD ='/product/transferstock/add';
	const REDIRECT_URL_ADD_CLOSE ='/product/transferstock/';
	
	public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    } 

   	public function indexAction()
   	{
   		$data = $this->getRequest()->getPost();
   		$list = new Application_Form_Frmlist();
   		$db = new Product_Model_DbTable_DbTransferStock();
		$date =new Zend_Date();
   		if($this->getRequest()->isPost()){   
    		$data = $this->getRequest()->getPost();
    		$data['start_date'] = date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date'] = date("Y-m-d",strtotime($data['end_date']));
    	}else{
			$data = array(
    			'ad_search'		=>	'',
    			'start_date'	=>	date("Y-m-d"),
				'end_date'		=>	date("Y-m-d"),
				'from_location'	=>	'',
				'to_location'	=>	'',
				'status'		=>	-1,
    		);
		}
		$link=array(
				'module'=>'product','controller'=>'transferstock','action'=>'edit',
		);
   		$rows=$db->getAllTransferStock($data);
   		$columns=array("ផ្ទេរពីសាខា","ទៅសាខា","សម្គាល់","ថ្ងៃបង្កើត","អ្នកប្រើប្រាស់","ស្ថានភាព");
   		$this->view->list=$list->getCheckList(0, $columns, $rows,array('from_loc'=>$link,'to_loc'=>$link,'note'=>$link));
   		
   		$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    	
    	$this->view->search = $data;
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch = $db->getAllBranch();
   	}
   	
    public function addAction()
    {   
    	try{
	    	$db = new Product_Model_DbTable_DbTransferStock();
	    	if($this->getRequest()->isPost()){   
	    		$data=$this->getRequest()->getPost();
	    		$db_result = $db->addTransfer($data);
	    		if(isset($data["save_close"])){
	    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL_ADD_CLOSE);
	    		}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
	    	}
	    	$db_global = new Application_Model_DbTable_DbGlobal();
	    	$this->view->branch = $db_global->getAllBranch();
	    	//$this->view->product = $db_global->getAllProduct();
    	}catch(Exception $e){
    		echo $e->getMessage();exit();
    	}
	}
	
	public function editAction()
	{
		$id = $this->getRequest()->getParam("id");
		$db = new Product_Model_DbTable_DbTransferStock();
		try{
			if($this->getRequest()->isPost()){
				$data=$this->getRequest()->getPost();
				$db_result = $db->updateTransfer($data,$id);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL_ADD_CLOSE);
			}
			$db_global = new Application_Model_DbTable_DbGlobal();
			$this->view->branch = $db_global->getAllBranch();
			//$this->view->product = $db_global->getAllProduct();
		}catch(Exception $e){
			echo $e->getMessage();exit();
		}
		
		$this->view->transfer = $db->getTransferProductById($id);
		$this->view->transfer_detail = $db->getTransferProductDetailById($id);
	}
	
	/// Ajax Section
	public function getProductbybranchAction(){
		if($this->getRequest()->isPost()) {
			$db = new Product_Model_DbTable_DbTransferStock();
			$data = $this->getRequest()->getPost();
			$rs = $db->getProductByBranch($data["branch_id"]);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
}