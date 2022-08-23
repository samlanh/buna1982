<?php
class Mong_ConstructorController extends Zend_Controller_Action
{
	public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    
    public function indexAction()
    {
    	$db = new Mong_Model_DbTable_DbConstructor();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    			'status'	=>	1
    		);
    	}
		$rows = $db->getAllConstructor($data);
		$columns=array("ឈ្មោះអ្នកម៉ៅការ","ភេទ","លេខទូរស័ព្ទ","អ៊ីម៉ែល","អាស័យដ្ឋាន","ប្រភេទជាង","សម្គាល់","ថ្ងៃបង្កើត","USER","STATUS");
		$link=array(
				'module'=>'mong','controller'=>'constructor','action'=>'edit',
		);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(10, $columns, $rows,array('name'=>$link,'sex'=>$link,'phone'=>$link,'constructor_type'=>$link));
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	public function addAction()
	{
		$db = new Mong_Model_DbTable_DbConstructor();
		if($this->getRequest()->isPost()){ 
			try{
				$data = $this->getRequest()->getPost();
				$db->addConstructor($data);			
					Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ", '/mong/constructor/index');			
			  }catch (Exception $e){
			  	Application_Form_FrmMessage::messageError("បញ្ចូលមិនត្រឹមត្រូវ",$err = $e->getMessage());
			  }
		}
	}
	
	public function editAction()
	{
		$id = $this->getRequest()->getParam("id"); 
		$db = new Mong_Model_DbTable_DbConstructor();
		if($this->getRequest()->isPost()){ 
			try{
				$data = $this->getRequest()->getPost();
				$db->editConstructor($data,$id);
				if(isset($data["save_close"]))
				{
					Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ", '/mong/constructor/index');
				}
			  }catch (Exception $e){
			  	Application_Form_FrmMessage::messageError("កែប្រែមិនត្រឹមត្រូវ",$err = $e->getMessage());
			  }
		}
		$this->view->row = $db->getConstructorById($id);
	}
}