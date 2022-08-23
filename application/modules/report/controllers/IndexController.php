<?php
class report_indexController extends Zend_Controller_Action
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
    		$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
    		$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
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
    
    public function rptSalesAction()//purchase report
    {
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    				'ad_search'		=>'',
    				'start_date'	=>date("Y-m-d"),
    				'end_date'		=>date("Y-m-d"),
    				'customer_id'	=>0,
    				'is_complete'	=>'',
    		);
    	}
    	$this->view->rssearch = $data;
    	
    	$query = new report_Model_DbSale();
    	$this->view->repurchase =  $query->getAllSaleOrderReport($data);
    	
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_purchase = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    public function rptSalesPaymentAction(){
    	$id = $this->getRequest()->getParam('id');
    	$query = new report_Model_DbSale();
    	$this->view->sale_payment =  $query->getSalePaymentById($id);
    }
   
    public function salesdetailAction(){
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/report/index/rpt-sales");
    	}
    	$query = new report_Model_DbQuery();
    	$this->view->product =  $query->getProductSaleById($id);
		$rs = $query->getProductSaleById($id);
    	if(empty($rs)){
    		$this->_redirect("/report/index/rpt-sales");
    	}
    }
    
    public function rptsalemongAction()
    {
    	$db = new report_Model_DbOther();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']	= date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']	= date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    				'ad_search'		=>	'',
    				'is_complete'	=>	'',
    				'start_date'	=>	date("Y-m-d"),
    				'end_date'		=>	date("Y-m-d"),
    				'customer_id'	=>0,
    		);
    	}
    	$this->view->rssearch = $data;
    	$this->view->other = $db->getAllsaleMong($data);
    	
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_salemong = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    
    function rptSaleitemAction(){
    	if($this->getRequest()->isPost()){
    		$search = $this->getRequest()->getPost();
    		$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
    		$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
    	}else{
    		$search = array(
    				'txt_search'=>'',
    				'start_date'=>date("Y-m-d"),
    				'end_date'=>date("Y-m-d"),
    				'item'=>0,
    				'category_id'=>0,
    				'customer_id'=>0,
    				'branch_id'=>0,
    		);
    	}
    	$this->view->rssearch=$search;
    	$query = new report_Model_DbQuery();
    	$this->view->product_rs =  $query->getSaleProductDetail($search);
    	 
    	$frm = new Application_Form_FrmReport();
    	$form_search=$frm->productDetailReport($search);
    	Application_Model_Decorator::removeAllDecorator($form_search);
    	$this->view->form_search = $form_search;
    }
    public function rptCustomerAction()//purchase report
    {
    	if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}else{
			$search =array(
					'text_search'=>'',
					'branch_id'=>0,
					'customer_id'=>0,
					'level'=>0,
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
			);
		}
		
		$query = new report_Model_DbQuery();
		$this->view->repurchase =  $query->getAllCustomer($search);
		
    	$this->view->rssearch = $search;
    	$frm = new Application_Form_FrmReport();
    
    	$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    public function rptSalepersonAction()//
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
    				'branch_id'=>-1);
    	}
    
    	$query = new report_Model_DbQuery();
    	$this->view->repurchase =  $query->getAllSaleAgent($search);
    
    	$this->view->rssearch = $search;
    	$frm = new Application_Form_FrmReport();
    
    	$formFilter = new Sales_Form_FrmSearchStaff();
    	$this->view->formFilter = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
	public function indexAction()
	{
	}
	//for view-report /27/8/13
	public function veiwReportAction(){
		$this->_helper->layout->disableLayout();
	}	
	public function printReportAction(){
		$this->_helper->layout->disableLayout();
	}
	public  function monthAction(){
		
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
    	}else{
    		$data = array(
    				'text_search'=>'',
    				'start_date'=>date("Y-m-d"),
    				'end_date'=>date("Y-m-d"),
    				'suppliyer_id'=>0,
    				'branch_id'=>0,
    		);
    	}
    	$query = new report_Model_DbQuery();
    	$this->view->rssearch = $data;	
    	if($data['suppliyer_id']==0){
    	$this->view->purchase_balance =  $query->getVendorBalance($data);
    	$this->view->partner_service_balance =  $query->getPartnerServiceBalance($data);
    	$this->view->constructor_balance =  $query->getConstructorBalance($data);
    	}else if($data['suppliyer_id']==1){
    		$this->view->purchase_balance =  $query->getVendorBalance($data);
    	}else if($data['suppliyer_id']==2){
    	$this->view->partner_service_balance =  $query->getPartnerServiceBalance($data);
    	}else if($data['suppliyer_id']==3){
    		$this->view->constructor_balance =  $query->getConstructorBalance($data);
    	}    	
    	$formFilter = new Application_Form_Frmsearch();
    	$this->view->formFilter = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }

	public function rptReceiptAction()//purchase report
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
    				'suppliyer_id'=>0,
    				'branch_id'=>-1,
    				'status_paid'=>-1,
    				'saleagent_id'=>-1
    		);
    	}
//     	$this->view->rssearch = $data;
    	$query = new report_Model_DbQuery();
    	$this->view->rsreceitp = $query->getAllReceipt($data);
    	
    	$formFilter = new Sales_Form_FrmSearch();
    	$this->view->form_purchase = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    	
//     	$frm = new Application_Form_FrmReport();
    
//     	$formFilter = new Application_Form_Frmsearch();
//     	$this->view->form_purchase = $formFilter;
//     	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
	
	function rptMongPaymentAction(){
		$id = $this->getRequest()->getParam('id');
		$query = new report_Model_DbCustomerPayment();
		$this->view->rsreceipt = $query->getMongPaymentById($id);
		 
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->form_purchase = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	function rptMongItemAction(){
		$id = $this->getRequest()->getParam('id');
		$query = new report_Model_DbCustomerPayment();
		$this->view->monginfo = $query->getMongInfoById($id);
		
		$this->view->mong_detail = $query->getMongItemById($id);
		 
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->form_purchase = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	public function rptCustomerPaymentAction()
	{
		$db = new report_Model_DbCustomerPayment();
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data['start_date']	= date("Y-m-d",strtotime($data['start_date']));
			$data['end_date']	= date("Y-m-d",strtotime($data['end_date']));
		}else{
			$data = array(
					'text_search'	=>	'',
					'customer_id'=>	'',
					'start_date'=>	date("Y-m-d"),
					'end_date'	=>	date("Y-m-d"),
					'status'	=>	-1,
					'order'		=>	1,
					'type'		=>	0,
			);
		}
		$this->view->rssearch = $data;
		if($data['type']==0){
			$this->view->sale_payment = $db->getSaleCustomerPayment($data);
			$this->view->mong_payment = $db->getMongCustomerPayment($data);
		}else if($data['type']==1){
			$this->view->sale_payment = $db->getSaleCustomerPayment($data);
		}else{
			$this->view->mong_payment = $db->getMongCustomerPayment($data);
		}
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->form_sale = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	public function rptPaidToSupplyerAction()
	{
		$db = new report_Model_DbPaidToSupplyer();
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data['end_date']	= date("Y-m-d",strtotime($data['end_date']));
		}else{
			$data = array(
					'ad_search'	=>	'',
					'branch'		=>	'',
					'start_date'	=>	date("Y-m-d"),
					'end_date'		=>	date("Y-m-d"),
					'paid_type'		=>0,
			);
		}
		$this->view->rssearch = $data;
		
		if($data['paid_type']==0){
			$this->view->purchase_payment = $db->getPurchasePayment($data);
			$this->view->partner_service_payment = $db->getPartnerServicePayment($data);
			$this->view->constructor_payment = $db->getConstructorPayment($data);
		}else if($data['paid_type']==1){
			$this->view->purchase_payment = $db->getPurchasePayment($data);
		}else if($data['paid_type']==2){
			$this->view->partner_service_payment = $db->getPartnerServicePayment($data);
		}else if($data['paid_type']==3){
			$this->view->constructor_payment = $db->getConstructorPayment($data);
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
	
	
	public function rptReturnStockAction()//purchase report
	{
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
		}else{
			$data = array(
					'ad_search'=>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
					'status'=>-1,
			);
		}
		$this->view->rssearch = $data;
		 
		$query = new report_Model_DbReturnStock();
		$this->view->return_stock =  $query->getAllReturnStock($data);
		 
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
		$this->view->form_salemong = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	function returnDetailAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/report/index/rpt-purchase");
		}
		$query = new report_Model_DbReturnStock();
		$this->view->return_detail =  $query->returnDetailById($id);
	
	}
	function rptReturnStockDetailAction(){
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}else{
			$search = array(
					'ad_search'=>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
					'product_id'=>'',
					'branch'=>0,
			);
		}
		$this->view->rssearch=$search;
		$query = new report_Model_DbReturnStock();
		$this->view->return_detail =  $query->getReturnStockDetail($search);
		$this->view->product =  $query->getAllProduct();
		 
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
		$this->view->form_salemong = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	public function rptproductsoldAction()
	{
		$db = new report_Model_DbProduct();
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
			$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
		}else{
			$data = array(
					'ad_search'		=>	'',
					'type'			=>	-1,
					'category'		=>	'',
					'start_date'	=>date("Y-m-d"),
					'end_date'		=>date("Y-m-d"),
			);
		}
		$this->view->product = $db->getAllProductSold($data);
		$this->view->search = $data;
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
}




