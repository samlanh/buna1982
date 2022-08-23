<?php
class Rsvacl_ExchangerateController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
    	$db = new Rsvacl_Model_DbTable_DbExchangeRate();
		if($this->getRequest()->isPost()){ 
			try{
				$post = $this->getRequest()->getPost();
				$db->submit($post);
			}catch (Exception $e){
				Application_Form_FrmMessage::messageError("INSERT_ERROR");
				echo $e->getMessage();
			}
		}
		$rs = $this->view->rs = $db->getExchangeRate();
	}		 
    
}

?>