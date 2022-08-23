<?php
class Purchase_vendorController extends Zend_Controller_Action
{	
	const REDIRECT_URL ='/purchase';
	public function init()
	{
		/* Initialize action controller here */
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction()
	{
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}else{
			$search =array(
					'text_search'=>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
					'suppliyer_id'=>0,
					'status'	=>	-1,
					);
		}
		$db = new Purchase_Model_DbTable_DbVendor();
		$rows = $db->getAllVender($search);
		$columns=array("ឈ្មោះក្រុមហ៊ុន","ទូរស័ព្ទក្រុមហ៊ុន","អ្នកទំនាក់ទំនង","លេខអ្នកទំនាក់ទំនង","អ៊ីម៉ែល","គេហទំព័រ","អាស័យដ្ឋាន","ស្ថានការ");
		$link=array(
				'module'=>'purchase','controller'=>'vendor','action'=>'edit',
		);
		$urlEdit = BASE_URL . "/purchase/vendor/edit";
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('v_name'=>$link,'phone_person'=>$link,'v_phone'=>$link,'contact_name'=>$link));
		
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	public function addAction()
	{
		if($this->getRequest()->isPost())
		{
			$post = $this->getRequest()->getPost();
			try{
				$vendor = new Purchase_Model_DbTable_DbVendor();
				$vendor->addVendor($post);
					Application_Form_FrmMessage::Sucessfull('ការបញ្ចូលបានជោគជ័យ', self::REDIRECT_URL . '/vendor/index');
			}catch(Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		/////////////////for veiw form
		$formStock = new Purchase_Form_FrmVendor(null);
		$formStockAdd = $formStock->AddVendorForm(null);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form = $formStockAdd;
		//.end controller
	}
	public function editAction() {
		$db = new Purchase_Model_DbTable_DbVendor();
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if($this->getRequest()->isPost())
		{
			$post = $this->getRequest()->getPost();
			$post['id']=$id;
			$db->addVendor($post);
			Application_Form_FrmMessage::Sucessfull('ការកែប្រែបានជោគជ័យ', self::REDIRECT_URL . '/vendor/index');
		}
		
		$row= $db->getvendorById($id);
		$this->view->is_over_sea = $row["is_over_sea"];
		$formStock = new Purchase_Form_FrmVendor();
		$formStockAdd = $formStock->AddVendorForm($row);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form = $formStockAdd;
	}
	
	//for add vendor from purchase
	function addvendorAction(){
		$post=$this->getRequest()->getPost();
		$db = new Purchase_Model_DbTable_DbVendor();
		$vid = $db->addnewvendor($post);
		$result = array('vid'=>$vid);
		echo Zend_Json::encode($result);
		exit();
	}
	
}