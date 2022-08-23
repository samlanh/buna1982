<?php
class Mong_ResponsibleController extends Zend_Controller_Action
{
	public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	$db = new Mong_Model_DbTable_DbResponsible();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    		);
    	}
		$rows = $db->getAllResponsible($data);
		$columns=array("ឈ្មោះអ្នកទទួលខុសត្រូវ","GENDER","CONTACT_NUMBER","NOTE","STATUS");
		$link=array('module'=>'mong','controller'=>'responsible','action'=>'edit',);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(10, $columns, $rows,array('name'=>$link,'tel'=>$link,'description'=>$link));
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$db = new Mong_Model_DbTable_DbResponsible();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->addResposible($post);
						Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ", '/mong/responsible/index');
						Application_Form_FrmMessage::message("បញ្ចូលដោយជោគជ័យ");
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}			
//		$this->view->rsservice = $db->getAllService();
	}
	function editAction(){
		$id = $this->getRequest()->getParam('id') ;
		$db = new Mong_Model_DbTable_DbResponsible();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post['id']=$id;
					$db->updateResponsible($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ", '/mong/responsible/index');
					}else{
						Application_Form_FrmMessage::message("កែប្រែដោយជោគជ័យ");
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("UPDATE_ERROR",$err = $e->getMessage());
				  }
			}			
	 		$row = $db->getResponsbileById($id);
	 		$this->view->responsible = $row;
	 		//print_r($row); exit();
//	 		$this->view->rsresponsible = $db->getAllService();				 
	}	
}