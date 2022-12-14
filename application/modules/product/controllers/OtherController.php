<?php
class Product_otherController extends Zend_Controller_Action
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
    	try{
    		$db = new Product_Model_DbTable_DbOther();
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'adv_search' => '',
    					'status_search' => '',
    					'type' => ''
    			);
    		}
    		$rs_rows= $db->getAllView($search);
    		$columns=array("ឈ្មោះ","ប្រភេទ","ស្ថានការ");
    		$link=array(
    				'module'=>'product','controller'=>'other','action'=>'edit',
    		);
    		$list = new Application_Form_Frmlist();
    		$this->view->list=$list->getCheckList(0, $columns, $rs_rows,array('item_name'=>$link,'item_code'=>$link,'barcode'=>$link,'cat'=>$link));
    		$this->view->rs = $rs_rows;
    			}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$fm = new Product_Form_FrmOther();
    	$frm = $fm->search();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->Form = $frm;
    }
    public function addAction()
    {
    	$db = new Product_Model_DbTable_DbOther();
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost(); 		 		
    		try {
    			$db->add($data);
    			if(isset($data['save_new'])){   			
    				Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
    			}	
    			if(isset($data['save_close'])){	
    				Application_Form_FrmMessage::message("ការ​បញ្ចូល​​ជោគ​ជ័យ", '/product/other/index');
    				Application_Form_FrmMessage::redirectUrl('/other/loantype');
    				}
    			} catch (Exception $e) {
    			Application_Form_FrmMessage::message("បញ្ចូលមិនត្រឹមត្រូវ");
    			$err = $e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	$fm = new Product_Form_FrmOther();
    	$frm = $fm->add();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->Form = $frm;
    }
    public function editAction()
    {
    	$id = $this->getRequest()->getParam("id");
    	$db = new Product_Model_DbTable_DbOther();
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$data["id"] = $id;
    		try {
    			$db->edit($data);
//     			if(isset($data['save_new'])){
    
//     				Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
//     			}
//     			if(isset($data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ',"/product/other");
//     				Application_Form_FrmMessage::redirectUrl('/other/loantype');
//     			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("បញ្ចូលមិនត្រឹមត្រូវ");
    			$err = $e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	$rs = $db->getViewById($id);
    	$fm = new Product_Form_FrmOther();
    	$frm = $fm->add($rs);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->Form = $frm;
    }	
	
}

