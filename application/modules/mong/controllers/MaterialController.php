<?php
class Mong_MaterialController extends Zend_Controller_Action
{
	public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	$db = new Mong_Model_DbTable_DbMaterial();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    		);
    	}
		$rows = $db->getAllMaterial($data);
		$columns=array("ឈ្មោះចំណាយ","PRICE_CURRENCY","NOTE","STATUS");
		$link=array('module'=>'mong','controller'=>'material','action'=>'edit',);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows,array('title'=>$link,'tel'=>$link,'description'=>$link));
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$db = new Mong_Model_DbTable_DbMaterial();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->addMaterial($post);
						Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ", '/mong/material/index');
						Application_Form_FrmMessage::message("បញ្ចូលដោយជោគជ័យ");
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}			
//		$this->view->rsservice = $db->getAllService();
	}
	function editAction(){
		$id = $this->getRequest()->getParam('id') ;
		$db = new Mong_Model_DbTable_DbMaterial();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post['id']=$id;
					$db->updateMaterial($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ", '/mong/material/index');
					}else{
						Application_Form_FrmMessage::message("កែប្រែដោយជោគជ័យ");
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("UPDATE_ERROR",$err = $e->getMessage());
				  }
			}			
	 		$row = $db->getMaterialById($id);
	 		$this->view->material = $row;
	 		//print_r($row); exit();
//	 		$this->view->rsresponsible = $db->getAllService();				 
	}	
}