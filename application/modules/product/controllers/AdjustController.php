<?php
class Product_AdjustController extends Zend_Controller_Action
{
	const REDIRECT_URL_ADD_CLOSE ='/product/adjust/';
	public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    } 
   	public function indexAction()
   	{
   		$data = $this->getRequest()->getPost();
   		$list = new Application_Form_Frmlist();
   		$db = new Product_Model_DbTable_DbAdjustStock();
		$date =new Zend_Date();
   		if($this->getRequest()->isPost()){   
    		$data = $this->getRequest()->getPost();
    		$data['start_date'] = date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date'] = date("Y-m-d",strtotime($data['end_date']));
    	}else{
			$data = array(
    			'ad_search'		=>	'',
				'branch'		=>	'',
    			'start_date'	=>	date("Y-m-d"),
				'end_date'		=>	date("Y-m-d"),
    		);
		}
		$link=array(
				'module'=>'product','controller'=>'adjust','action'=>'edit',
		);
   		$rows=$db->getAllAdjustStock($data);
   		$columns=array("សាខា","​លេខសម្គាល់","សម្គាល់","ថ្ងៃបង្កើត","អ្នកប្រើប្រាស់","ស្ថានភាព");
   		$this->view->list=$list->getCheckList(0, $columns, $rows,array('branch'=>$link,'code'=>$link,'note'=>$link));
   		
   		$formFilter = new Product_Form_FrmProduct();
   		$this->view->formFilter = $formFilter->productFilter();
   		Application_Model_Decorator::removeAllDecorator($formFilter);
   	}
   	
    public function addAction()
    {   
    	try{
	    	$db_adjust= new Product_Model_DbTable_DbAdjustStock();
	    	if($this->getRequest()->isPost()){   
	    		$post=$this->getRequest()->getPost();
	    		$db_adjust->add($post);
	    		if(isset($post["save_close"])){
	    			Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ",self::REDIRECT_URL_ADD_CLOSE);
	    		}else{
					Application_Form_FrmMessage::message("បញ្ចូលដោយជោគជ័យ");
				}
	    	}
	    	$db_global = new Application_Model_DbTable_DbGlobal();
	    	$this->view->branch = $db_global->getAllBranch();
	    	$this->view->adjcode = $db_global->getAdjustCode();
    	}catch (Exception $e){
    		echo $e->getMessage();exit();
    	}
	}
	
	public function editAction()
	{
		try{
			$id = $this->getRequest()->getParam("id");
			$db_adjust= new Product_Model_DbTable_DbAdjustStock();
			if($this->getRequest()->isPost()){
				$post=$this->getRequest()->getPost();
				$db_adjust->edit($post,$id);
				Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ",self::REDIRECT_URL_ADD_CLOSE);
			}
			
			$db_global = new Application_Model_DbTable_DbGlobal();
			$this->view->branch = $db_global->getAllBranch();
			$this->view->adjcode = $db_global->getAdjustCode();
			 
			$this->view->row = $db_adjust->getAdjustByID($id);
			$this->view->row_detail = $db_adjust->getAdjustDetailByID($id);
		}catch (Exception $e){
			echo $e->getMessage();exit();
		}
	}
	
	/// Ajax Section
	public function getproductAction(){
		if($this->getRequest()->isPost()) {
			$db = new Product_Model_DbTable_DbAdjustStock();
			$data = $this->getRequest()->getPost();
			$rs = $db->getProductQtyById($data["pro_id"],$data["branch_id"]);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
}