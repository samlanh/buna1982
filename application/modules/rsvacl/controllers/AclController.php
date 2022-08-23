<?php
class Rsvacl_AclController extends Zend_Controller_Action
{
	public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
        // action body    	
    	//$this->_helper->layout()->disableLayout();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
        $getAcl = new Rsvacl_Model_DbTable_DbAcl();
        $aclQuery = "SELECT `acl_id`,label,`module`,`controller`,`action`,`status` FROM tb_acl_acl ORDER BY ordering ASC, rank ASC, is_menu DESC ";
        $rows = $getAcl->getAclInfo($aclQuery);
        if($rows){        	
        	$imgnone='<img src="'.BASE_URL.'/images/icon/none.png"/>';
        	$imgtick='<img src="'.BASE_URL.'/images/icon/tick.png"/>';
        	        	        	
        	foreach ($rows as $i =>$row){
        		if($row['status'] == 1){
        			$rows[$i]['status'] = $imgtick;
        		}
        		else{
        			$rows[$i]['status'] = $imgnone;
        		}
        	}
        	
        	$list=new Application_Form_Frmlist();
        	$columns=array($tr->translate('ឈ្មោះសិទ្ធ'),$tr->translate('Module'),$tr->translate('Controller'),$tr->translate('Action'), $tr->translate('Status'));
        	
        	$link = array("rsvacl","acl","edit");
        	$links = array('module'=>$link,'controller'=>$link,"action"=>$link);
        	
        	$this->view->form=$list->getCheckList('radio', $columns, $rows, $links );
        	
        }else $this->view->form = $tr->translate('NO_RECORD_FOUND');
    }
    
    public function viewAclAction()
    {   
    	/* Initialize action controller here */
    	if($this->getRequest()->getParam('id')){
    		$db = new Rsvacl_Model_DbTable_DbAcl();
    		$acl_id = $this->getRequest()->getParam('id');
    		$rs=$db->getAcl($acl_id);
    		$this->view->rs=$rs;
    	}  	 
    	
    }
	public function addAction()
	{
		if($this->getRequest()->isPost())
		{
			$db=new Rsvacl_Model_DbTable_DbAcl();
			$post=$this->getRequest()->getPost();
			$id=$db->insertAcl($post);
// 			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
// 			Application_Form_FrmMessage::message($tr->translate('ROW_AFFECTED'));
// 			Application_Form_FrmMessage::redirector('/rsvAcl/acl/index');
		}
	}
    public function editAction()
    {	
    	$acl_id = $this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost())
    	{
    		
    		$db=new Rsvacl_Model_DbTable_DbAcl();
    		$post=$this->getRequest()->getPost();
    		$id=$db->updateAcl($post,$acl_id);
    		// 			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		// 			Application_Form_FrmMessage::message($tr->translate('ROW_AFFECTED'));
    		// 			Application_Form_FrmMessage::redirector('/rsvAcl/acl/index');
    	}
    	
    	$db=new Rsvacl_Model_DbTable_DbAcl();
    	$this->view->row = $db->getAclById($acl_id);
    	
    }
}