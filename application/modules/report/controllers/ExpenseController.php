<?php
class report_ExpenseController extends Zend_Controller_Action
{
	
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }
    protected function GetuserInfo(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function indexAction()
    {
    	
    }
    
    public function rptAllExpenseAction()//purchase report
    {
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
			$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    				'text_search'=>'',
    				'start_date'=>date("Y-m-d"),
    				'end_date'=>date("Y-m-d"),
    				'type'=>0,
    				'branch_id'=>0,
    		);
    	}
    	$query = new report_Model_DbPaidToSupplyer();
		
    	$this->view->rssearch = $data;
    	if($data['type']==0){
    		$this->view->purchase_expense =  $query->getPurchaseExpense($data);
    		$this->view->partner_service_expense =  $query->getPartnerServiceExpense($data);
    		$this->view->mong_expense =  $query->getMongExpense($data);
    		$this->view->other_expense =  $query->getOtherExpense($data);
    	}else if($data['type']==1){
    		$this->view->purchase_expense =  $query->getPurchaseExpense($data);
    	}else if($data['type']==2){
    		$this->view->partner_service_expense =  $query->getPartnerServiceExpense($data);
    	}else if($data['type']==3){
    		$this->view->mong_expense =  $query->getMongExpense($data);
    	}else if($data['type']==4){
    		$this->view->other_expense =  $query->getOtherExpense($data);
    	}
    	
    	$formFilter = new Application_Form_Frmsearch();
    	$this->view->formFilter = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    
    public function rptPurchaseAction()//purchase report
    {
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    				'ad_search'	=>'',
    				'start_date'=>date("Y-m-d"),
    				'end_date'	=>date("Y-m-d"),
    				'status'	=>-1,
    				'branch'	=>'',
    		);
    	}
    	$this->view->rssearch = $data;
    	
    	$query = new report_Model_DbPurchase();
    	$this->view->repurchase =  $query->getAllPurchaseReport($data);
    	
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_salemong = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    function purproductdetailAction(){
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/report/index/rpt-purchase");
    	}
    	$query = new report_Model_DbPurchase();
    	$this->view->product =  $query->getProductPruchaseById($id);
    	 
    }
    function rptPurchaseitemAction(){
    	if($this->getRequest()->isPost()){
    		$search = $this->getRequest()->getPost();
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
    		$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
    	}else{
    		$search = array(
    				'ad_search'	=>'',
    				'start_date'=>date("Y-m-d"),
    				'end_date'	=>date("Y-m-d"),
    				'product_id'=>'',
    				'branch'	=>0,
    		);
    	}
    	$this->view->rssearch=$search;
    	$query = new report_Model_DbPurchase();
    	$this->view->product_rs =  $query->getPruchaseProductDetail($search);
    	$this->view->product =  $query->getAllProduct();
    	
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_salemong = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    	
    	
//     	$frm = new Application_Form_FrmReport();
//     	$form_search=$frm->productDetailReport($search);
//     	Application_Model_Decorator::removeAllDecorator($form_search);
//     	$this->view->form_search = $form_search;
    }
    
    function rptPurchasePaymentAction(){
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/report/index/rpt-purchase");
    	}
    	$query = new report_Model_DbPurchase();
    	$this->view->purchase_payment = $query->getPruchasePaymentById($id);
    }
    
    function rptPartnerServicePaymentAction(){
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/report/index/rpt-vandorbalance");
    	}
    	$query = new report_Model_DbPurchase();
    	$this->view->partner_service_payment = $query->getPartnerServicePaymentById($id);
    }
    function rptConstructorPaymentAction(){
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/report/index/rpt-vandorbalance");
    	}
    	$query = new report_Model_DbPurchase();
    	$this->view->partner_service_payment = $query->getConstructorPaymentById($id);
    }
    
	public function rptExpenseAction(){
    	try{
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
    			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
    		}
    		else{
    			$search = array(
    					"text_search"=>'',
    					"branch_id"=>"",
    					'title'=>-1,
    					"status"=>-1,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		$db = new report_Model_DbExpense();
    		$this->view->expense = $db->getAllExpense($search);
    		$this->view->search = $search;
    
    	}catch(Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    	}
    	
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    
    public function rptExpenseDetailAction(){
    	$id = $this->getRequest()->getParam('id');
    	$db = new report_Model_DbExpense();
    	$this->view->expense = $db->getAllExpenseById($id);
    	$this->view->expense_detail = $db->getAllExpenseDetailById($id);
    }
    
	public function rptExpenseByTypeAction(){
    	try{
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
				$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
    			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
    		}
    		else{
    			$search = array(
    					"text_search"=>'',
    					"branch_id"=>'',
    					'title'=>-1,
    					"status"=>-1,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		$db = new report_Model_DbExpense();
    		$this->view->expense_type = $db->getAllExpenseType($search);
    		
    		$this->view->search = $search;
    	}catch(Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    	}
    	$formFilter = new Application_Form_Frmsearch();
    	$this->view->formFilter = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
	
	public function rptVandorbalanceAction()//purchase report
    {
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
			$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    	}else{
    		$data = array(
    				'text_search'=>'',
    				'start_date'=>date("Y-m-d"),
    				'end_date'=>date("Y-m-d"),
    				'type'=>0,
    				'branch_id'=>0,
    		);
    	}
    	$query = new report_Model_DbQuery();
    	$this->view->rssearch = $data;	
    	if($data['type']==0){
	    	$this->view->purchase_balance =  $query->getVendorBalance($data);
	    	$this->view->partner_service_balance =  $query->getPartnerServiceBalance($data);
    	}else if($data['type']==1){
    		$this->view->purchase_balance =  $query->getVendorBalance($data);
    	}else if($data['type']==2){
    		$this->view->partner_service_balance =  $query->getPartnerServiceBalance($data);
    	}  	
    	$formFilter = new Application_Form_Frmsearch();
    	$this->view->formFilter = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
		
    }

	public function rptPaidToSupplyerAction()
	{
		$db = new report_Model_DbPaidToSupplyer();
		$db_mong = new report_Model_DbMongExpense();
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
			$data['end_date']	= date("Y-m-d",strtotime($data['end_date']));
		}else{
			$data = array(
					'ad_search'	=>	'',
					'branch'		=>	'',
					'start_date'	=>	date("Y-m-d"),
					'end_date'		=>	date("Y-m-d"),
					'paid_type'		=>0,
					'status'		=>-1,
			);
		}
		$this->view->rssearch = $data;
		
		if($data['paid_type']==0){
			$this->view->purchase_payment = $db->getPurchasePayment($data);
			$this->view->partner_service_payment = $db->getPartnerServicePayment($data);
			$this->view->mong_expense = $db_mong->getMongExpense($data);
		}else if($data['paid_type']==1){
			$this->view->purchase_payment = $db->getPurchasePayment($data);
		}else if($data['paid_type']==2){
			$this->view->partner_service_payment = $db->getPartnerServicePayment($data);
		}else if($data['paid_type']==3){
			//$this->view->mong_expense = $db_mong->getMongExpense($data);
		}
		
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
		$this->view->form_salemong = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
		
// 		$frm = new Application_Form_FrmReport();
// 		$form_search=$frm->FrmReportPurchase($data);
// 		Application_Model_Decorator::removeAllDecorator($form_search);
// 		$this->view->form_purchase = $form_search;
	}
	
	public function rptComisionAction(){
    	try{
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
    			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
    		}
    		else{
    			$search = array(
    					"text_search"	=>'',
    					"branch_id"		=>"",
    					'type'			=>0,
						'sale_id'		=>0,
    					"status"		=>-1,
    					'start_date'	=>date('Y-m-d'),
    					'end_date'		=>date('Y-m-d'),
    			);
    		}
			$db = new report_Model_DbComision();
			if($search['type']==0){
				$this->view->SaleComision = $db->getSaleComision($search);
				$this->view->MongComision = $db->getMongComision($search);
			}else if($search['type']==1){
				$this->view->SaleComision = $db->getSaleComision($search);
			}else if($search['type']==2){
				$this->view->MongComision = $db->getMongComision($search);
			}
			
    		$this->view->search = $search;
			
			$this->view->sale_agent = $db->getAllSaleAgent();
    
    	}catch(Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    	}
    	
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
    }
	
	public function rptMongexpenseAction(){
    	try{
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
    			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
    		}
    		else{
    			$search = array(
    					"text_search"	=>'',
    					"branch_id"		=>"",
    					"status"		=>-1,
    					'start_date'	=>date('Y-m-d'),
    					'end_date'		=>date('Y-m-d'),
    			);
    		}
			$db = new report_Model_DbMongExpense();
			$this->view->MongExpense = $db->getMongExpense($search);
    		
			$this->view->search = $search;
    	}catch(Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    	}
    	
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
    }
	public function rptMongexpenseitemAction(){
    	try{
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
    			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
    		}
    		else{
    			$search = array(
    					"text_search"	=>'',
    					"branch_id"		=>"",
    					"status"		=>-1,
    					'start_date'	=>date('Y-m-d'),
    					'end_date'		=>date('Y-m-d'),
    			);
    		}
			$db = new report_Model_DbMongExpense();
			$this->view->MongExpense = $db->getMongExpenseItem($search);
    		
			$this->view->search = $search;
    	}catch(Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    	}
    	
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
    }
	function mongexpensedetailAction(){
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/report/expense/rpt-mongexpense");
    	}
    	$query = new report_Model_DbMongExpense();
    	$this->view->mong =  $query->getMongExpense(array(),$id);
    	$this->view->expense_detail = $query->getMongExpenseDetail($id); 
    }
	function expensebymongAction(){
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/report/income/rptsalemong");
    	}
    	$query = new report_Model_DbMongExpense();
    	$this->view->expense_by_mong = $query->getExpenseByMong($id); 
    }
}




