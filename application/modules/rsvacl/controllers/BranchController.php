<?php
class Rsvacl_branchController extends Zend_Controller_Action
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
		$db = new Rsvacl_Model_DbTable_DbBranch();
		$formFilter = new Product_Form_FrmBranchFilter();
		$frmsearch = $formFilter->branchFilter();
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
		}else{
			$data = array(
					'branch_name'	=>	'',
					'status'	=>	1
			);
		}
		$this->view->formFilter = $frmsearch;
		$list = new Application_Form_Frmlist();
		$result = $db->getAllBranch($data);
		$this->view->resulr = $result;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Rsvacl_Model_DbTable_DbBranch();
			$db->add($data);
			if($data['save_close']){
				Application_Form_FrmMessage::Sucessfull("INSER_SUCCESS", '/rsvacl/branch/index');
			}
			else{
				Application_Form_FrmMessage::Sucessfull("INSER_SUCCESS", '/rsvacl/branch/index/add');
			}
		}
		$formFilter = new Product_Form_FrmBranch();
		$formAdd = $formFilter->branch();
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
	}
	public function editAction()
	{
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Rsvacl_Model_DbTable_DbBranch();
		
		if($id==0){
			$this->_redirect('/product/branch/index/add');
		}
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			$db->edit($data);
			if($data['save_close']){
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", '/rsvacl/branch/index');
			}
		}
		$rs = $db->getBranchById($id);
		$formFilter = new Product_Form_FrmBranch();
		$formAdd = $formFilter->branch($rs);
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
	}
	
}

