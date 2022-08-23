<?php
class Donors_DonateController extends Zend_Controller_Action
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
    	$db = new Donors_Model_DbTable_DbDonate();
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
		$rows = $db->getAllDonate($data);
		$columns=array("BRANCH_NAME","ឈ្មោះសព","ភេទ","អាយុ","ថ្ងៃស្លាប់","អាស័យដ្ឋាន","ឈ្មោះសប្បុរសជន","ថ្ងៃឧបត្ថម","ថ្ងៃបង្កើត","USER","STATUS");
		$link=array(
				'module'=>'donors','controller'=>'donate','action'=>'edit',
		);
		$link1=array(
				'module'=>'donors','controller'=>'donate','action'=>'donorpeople',
		);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(10, $columns, $rows,array('សប្បុរសជន'=>$link1,'dead_name'=>$link,'donor_name'=>$link,'dat_jenh'=>$link,'dead_sex'=>$link));
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$db = new Donors_Model_DbTable_DbDonate();
		if($this->getRequest()->isPost()){ 
			try{
				$data = $this->getRequest()->getPost();
				$db->addDonate($data);
				Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ", '/donors/donate/index');
			  }catch (Exception $e){
			  	Application_Form_FrmMessage::message("បញ្ចូលមិនត្រឹមត្រូវ");
			  	echo $e->getMessage();exit();
			  }
		}

		$this->view->donor = $db->getAllDonor();
		
		$db=new Sales_Model_DbTable_DbProgram();
		$khmer_year = $db->getAllKhmerYear();
		$this->view->khmer_year = $khmer_year;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $db->getAllBranch();
		
	}
	public function editAction()
	{
		$id = $this->getRequest()->getParam("id"); 
		$db = new Donors_Model_DbTable_DbDonate();
		if($this->getRequest()->isPost()){ 
			try{
				$data = $this->getRequest()->getPost();
				$db->editDonate($data,$id);
				if(isset($data["save_close"]))
				{
					Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ",'/donors/donate/index');
				}
			}catch (Exception $e){
			  	Application_Form_FrmMessage::messageError("កែប្រែមិនត្រឹមត្រូវ",$err = $e->getMessage());
			}
		}
		$this->view->donor = $db->getAllDonor();
		$row = $db->getDonateById($id);
		$this->view->row = $row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/donors/donate");
			exit();
		}
		
		$db=new Sales_Model_DbTable_DbProgram();
		$khmer_year = $db->getAllKhmerYear();
		$this->view->khmer_year = $khmer_year;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $db->getAllBranch();
	}
	function donorpeopleAction(){
		$id = $this->getRequest()->getParam("id");
 		$db = new Donors_Model_DbTable_DbDonate();
 		
 		$this->view->row = $db->getDonorpeopleById($id);		
	}
	function getDonateDetailAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Donors_Model_DbTable_DbDonate();
			$detail = $db->getDonorDetail($data['donor_id']);
			print_r(Zend_Json::encode($detail));
			exit();
		}		
	}
}