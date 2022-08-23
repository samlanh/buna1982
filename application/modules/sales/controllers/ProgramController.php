<?php
class Sales_ProgramController extends Zend_Controller_Action
{
	public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
//     protected function GetuserInfoAction(){
//     	$user_info = new Application_Model_DbTable_DbGetUserInfo();
//     	$result = $user_info->getUserInfo();
//     	return $result;
//     }
// 	function updatecodeAction(){
// 		$db = new Product_Model_DbTable_DbProduct();
// 		$db->getProductCoded();
// 	}
    public function indexAction()
    {
    	$db = new Sales_Model_DbTable_DbProgram();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    			'branch'	=>	'',
    			'status'	=>	-1,
    			'start_date'=> date('Y-m-d'),
    			'end_date'=>date('Y-m-d')
    		);
    	}
		$rows = $db->getAllProgram($data);
		$this->view->rs = $rows;
		
		$columns=array("BRANCH_NAME","ឈ្មោះសព","ភេទ","ស្លាប់នៅថ្ងៃ","ឈ្មោះដៃគូរសព","ភេទ","ទីតាំងបុណ្យ","ទីតាំងបញ្ចុះ/បូជា","ថ្ងៃបង្កើត","អ្នកប្រើប្រាស់","ស្ថានការណ៍");
		$link=array(
			'module'=>'sales','controller'=>'possale','action'=>'edit',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(10, $columns, $rows, array('phone'=>$link,'branch_name'=>$link,'customer_name'=>$link,'sale_no'=>$link,'place_bun'=>$link));
		
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$db = new Sales_Model_DbTable_DbProgram();
		if($this->getRequest()->isPost()){ 
			try{
				$data = $this->getRequest()->getPost();
				$db->addProgram($data);
				Application_Form_FrmMessage::Sucessfull("បញ្ជូលដោយជោគជ័យ", '/sales/program/index');
			  }catch (Exception $e){
			  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
			  	echo $e->getMessage();exit();
			  }
		}
		
		$db=new Sales_Model_DbTable_DbProgram();
		$khmer_year = $db->getAllKhmerYear();
		$this->view->khmer_year = $khmer_year;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $db->getAllBranch();
		
	}
	public function editAction()
	{
		$id = $this->getRequest()->getParam("id"); 
		$db = new Sales_Model_DbTable_DbProgram();
		if($this->getRequest()->isPost()){ 
			try{
				$data = $this->getRequest()->getPost();
				$db->editProgram($data,$id);
				Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ", '/sales/program/index');
			}catch (Exception $e){
				Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
			}
		}
		
		$this->view->row = $db->getProgramById($id);		
		$this->view->khmer_year_boy = $db->getAllKhmerYearBoyById($id);
		$this->view->khmer_year_girl = $db->getAllKhmerYearGirlById($id);

		$db=new Sales_Model_DbTable_DbProgram();
		$khmer_year = $db->getAllKhmerYear();
		$this->view->khmer_year = $khmer_year;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $db->getAllBranch();
	}
	
	public function copyAction()
	{
		$id = $this->getRequest()->getParam("id");
		$db = new Sales_Model_DbTable_DbProgram();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db->addProgram($data);
				Application_Form_FrmMessage::Sucessfull("ចម្លងដោយជោគជ័យ", '/sales/program/index');
			}catch (Exception $e){
				Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
			}
		}
	
		$this->view->row = $db->getProgramById($id);
	
		$this->view->khmer_year_boy = $db->getAllKhmerYearBoyById($id);
		$this->view->khmer_year_girl = $db->getAllKhmerYearGirlById($id);
	
		$db=new Sales_Model_DbTable_DbProgram();
		$khmer_year = $db->getAllKhmerYear();
		$this->view->khmer_year = $khmer_year;
	
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $db->getAllBranch();
	}
	
	function travelAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Sales_Model_DbTable_DbProgram();
		
		$this->view->row = $db->getTravelById($id);
		
	}
	function locationmongAction(){
		$id = $this->getRequest()->getParam("id");
 		$db = new Sales_Model_DbTable_DbProgram();
	
 		$this->view->row = $db->getLocationmongById($id);
 		$this->view->khmer_year_boy = $db->getAllKhmerYearBoyById($id);
 		$this->view->khmer_year_girl = $db->getAllKhmerYearGirlById($id);
	}
}