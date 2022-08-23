<?php
class Sales_PartnerserviceController extends Zend_Controller_Action
{
	public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	$db = new Sales_Model_DbTable_DbPartnerService();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
     			'status'	=>	-1,
    		);
    	}
		$rows = $db->getAllPartnerService($data);
		$columns=array("ឈ្មោះដៃគូ","ភេទ","លេខទូរស័ព្ទ","អាស័យដ្ឋាន","ពណ៍នា","ប្រើប្រាស់","ស្ថានការ");
		$link=array('module'=>'sales','controller'=>'partnerservice','action'=>'edit',);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows,array('partner_name'=>$link,'gender'=>$link));
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$db = new Sales_Model_DbTable_DbPartnerService();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->addService($post);
						Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ", '/sales/partnerservice/index');
						Application_Form_FrmMessage::message("បញ្ចូលដោយជោគជ័យ");
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("បញ្ចូលមិនត្រឹមត្រូវ",$err = $e->getMessage());
				  }
			}			
		$this->view->rsservice = $db->getAllService();
	}
	function editAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Sales_Model_DbTable_DbPartnerService();
			if($this->getRequest()->isPost()){
		//		$data["id"] = $id;
				try{
					$post = $this->getRequest()->getPost();
					$db->updateservice($post, $id);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ", '/sales/partnerservice/index');
					}else{
						Application_Form_FrmMessage::message("កែប្រែដោយជោគជ័យ");
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("កែប្រែមិនត្រឹមត្រូវ",$err = $e->getMessage());
				  }
			}	
			$this->view->row = $db->getServiceById($id);	 		
	 		$this->view->rsservice = $db->getAllService();

	 		$this->view->partner_cost = $db->getPartnerCostById($id);
	 		
	}	
}