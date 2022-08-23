<?php
class Product_measureController extends Zend_Controller_Action
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
		$db = new Product_Model_DbTable_DbMeasure();
		$formFilter = new Measure_Form_FrmMeasure();
		$frmsearch = $formFilter->MeasureFilter();
		$this->view->formFilter = $frmsearch;
		if($this->getRequest()->isPost()){
		    $data = $this->getRequest()->getPost();
		}else{
		    $data = array(
		        'name'	     =>	'',
		        'status'	 =>	-1,
		    );
		}
		$result = $db->getAllMeasure($data);
		$columns=array("MEASURE NAME","សម្គាល់","STATUS");
		$link=array(
				'module'=>'product','controller'=>'measure','action'=>'edit',
		);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $result,array('name'=>$link));
		$this->view->resulr = $result;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$session_stock = new Zend_Session_Namespace('stock');
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Product_Model_DbTable_DbMeasure();
			$db->add($data);
				Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ", '/product/measure/index');
		}
		$formFilter = new Measure_Form_FrmMeasure();
		$formAdd = $formFilter->measure();
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
	}
	public function editAction()
	{
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Product_Model_DbTable_DbMeasure();
		
		if($id==0){
			$this->_redirect('/product/measure/index/add');
		}
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			//$db = new Measure_Model_DbTable_DbMeasure();
			$db->edit($data);
			if(isset($data['save_close'])){
				Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ", '/product/measure/index');
			}
		}
		$rs = $db->getMeasure($id);
		$formFilter = new Measure_Form_FrmMeasure();
		$formAdd = $formFilter->measure($rs);
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
	}
	//view Measure 27-8-2013
	
	public function addNewLocationAction(){
		$post=$this->getRequest()->getPost();
		$add_new_location = new Product_Model_DbTable_DbAddProduct();
		$location_id = $add_new_location->addStockLocation($post);
		$result = array("LocationId"=>$location_id);
		if(!$result){
			$result = array('LocationId'=>1);
		}
		echo Zend_Json::encode($result);
		exit();
	}
	
}

