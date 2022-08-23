<?php
class Mong_MongexpenseController extends Zend_Controller_Action
{	
	
public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }

   	public function indexAction()
	{
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'ad_search'=>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
					'branch'=>-1,
					'mong_id'=>"",
				);
		}
		$db = new Mong_Model_DbTable_DbMongExpense();
		$rows = $db->getAllMongExpense($search);
		$columns=array("សាខា","លេខចំណាយ","ចំណាយលើវិក័យបត្រ","ទីតាំងបុណ្យ","ថ្ងៃចំណាយ","ចំណាយសរុប","សម្គាល់","អ្នកប្រើប្រាស់","ស្ថានភាព");
		$link=array(
			'module'=>'mong','controller'=>'mongexpense','action'=>'edit',
		);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('invoice_no'=>$link,'expense_number'=>$link,'branch_name'=>$link,
				'date_input'=>$link));
		
		$this->view->search = $search;
		
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}	
	function addAction(){
		$db = new Mong_Model_DbTable_DbMongExpense();
		
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db->addMongExpense($data);
				Application_Form_FrmMessage::message("INSERT_SUCESS");
				Application_Form_FrmMessage::redirectUrl("/mong/mongexpense/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				echo $e->getMessage();
			}
		}
		
		
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $_db->getAllBranch();
		$this->view->invoice = $_db->getMongExpenseInvoice();
		
		$db_mong = new Mong_Model_DbTable_DbIndex();
		$this->view->constructor_item = $db_mong->getConstructorItem();
		
	}
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Mong_Model_DbTable_DbMongExpense();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db->updateMongExpense($data,$id);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/mong/mongexpense");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $db->getEdit($id);
		$this->view->row = $row;
		
		$rs = $db->getEditDetail($id);
		$this->view->rs = $rs;
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $_db->getAllBranch();
		$this->view->invoice = $_db->getMongExpenseInvoice();
		
		$db_mong = new Mong_Model_DbTable_DbIndex();
		$this->view->constructor_item = $db_mong->getConstructorItem();
	}	
	
	public function getConstructorInfoAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Mong_Model_DbTable_DbMongExpense();
			$rs = $db->getConstructorInfo($post['id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	public function getExpenseNumberAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db->getMongExpenseInvoice($post['branch_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
	function getmongbybranchAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$post['branch_id'] = empty($post['branch_id'])?1:$post['branch_id'];
			$post['postype'] = empty($post['postype'])?1:$post['postype'];
				
			$db = new Mong_Model_DbTable_DbMongExpense();
			$rs = $db->getMongByBranch($post);
			// 			echo Zend_Json::encode($rs);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
}