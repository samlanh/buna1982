<?php
class report_ProductController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	
    }
	public function rptallcurrentstockAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'branch'	=>	'',
    				'brand'		=>	'',
    				'category'	=>	'',
    				'model'		=>	'',
    				'color'		=>	'',
    				'size'		=>	'',
    				'status_qty'=>	-1
    		);
    	}
		$this->view->rssearch = $data;
    	$this->view->product = $db->getAllcurrentstock($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    
    }
    public function rptproductlistAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'		=>	'',
    				'brand'			=>	'',
    				'category'		=>	'',
    				'type'			=>	-1,
    		);
    	}
    	$this->view->product = $db->getAllProduct($data);
    	$this->view->search = $data;
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);    
    }    
    
    public function rptadjuststockAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'branch'	=>	'',
    				'start_date'=>	date("Y-m-d"),
    				'end_date'	=>	date("Y-m-d"),
    		);
    	}
    	$this->view->product = $db->getAllAdjustStock($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    	
    	$this->view->rssearch = $data;
    }
    
    public function rptadjuststockbyidAction()
    {
    	$id = $this->getRequest()->getParam("id");
    	$db = new report_Model_DbProduct();
    	$this->view->adjust_detail = $db->getAdjustStockById($id);
    	print_r($this->view->adjust_detail);
    }
    
    public function rptadjuststockdetailAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'pro_id'	=>	'',
    				'branch'	=>	'',
    				'start_date'=>	'',
    				'end_date'	=>	date("Y-m-d"),
    		);
    	}
    	$this->view->adjust_detail = $db->getAllAdjustStockDetail($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    	
    	$this->view->rssearch = $data;
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->product = $_db->getAllProduct();
    }
    public function productBarcodeAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'		=>	'',
    				'brand'			=>	'',
    				'category'		=>	'',
    				'type'			=>	-1,
    		);
    	}
    	$this->view->product = $db->getAllProduct($data);
    	$this->view->search = $data;
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    public function allBarcodeAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'		=>	'',
    				'brand'			=>	'',
    				'category'		=>	'',
    				'type'			=>	-1,
    		);
    	}
    	$this->view->product = $db->getAllProduct($data);
    	$this->view->search = $data;
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    public function oneBarcodeAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'pro_id'	=>	0,
    				'qty'		=>	1,
    		);
    	}
    	if(!empty($data['pro_id'])){
	    	$this->view->product = $db->getOneProduct($data);
    	}
    	$this->view->search = $data;
    	
    	$db = new Sales_Model_DbTable_Dbpos();
    	$this->view->rsproduct = $db->getAllProductName(2);
    	//print_r($this->view->rsproduct);exit();
    }
	public function oneBarcodeRowAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'pro_id'	=>	0,
    				'qty'		=>	1,
    		);
    	}
    	if(!empty($data['pro_id'])){
	    	$this->view->product = $db->getOneProduct($data);
    	}
    	$this->view->search = $data;
    	
    	$db = new Sales_Model_DbTable_Dbpos();
    	$this->view->rsproduct = $db->getAllProductName(2);
    	//print_r($this->view->rsproduct);exit();
    }
	public function printBarcodeAction()
    {
    	$db = new report_Model_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
			
			
			$this->view->product = $db->getProduct($data);
			
    	}else{
    		$data = array(
    				
    		);
    	}
    	
	    //$this->view->product = $db->getOneProduct($data);
    	//print_r($data);
    }
    function showbarcodeAction(){
    	$id = ($this->getRequest()->getParam('id'));
    	$sql ="SELECT id,barcode,item_name,item_code,cate_id,
			((SELECT name FROM `tb_category` WHERE id=cate_id)) as cate_name
    	FROM `tb_product` WHERE id IN (".$id.")";
    	$db = new Application_Model_DbTable_DbGlobal();
//     	print_r($db->getGlobalDb($sql));
    	$this->view->rsproduct = $db->getGlobalDb($sql);
    }
    public function generateBarcodeAction(){
    	$pro_code = $this->getRequest()->getParam('pro_code');
    	header('Content-type: image/png');
    	$this->_helper->layout()->disableLayout();
    	//$barcodeOptions = array('text' => "$_itemcode",'barHeight' => 30);
    	$barcodeOptions = array('text' => "$pro_code",'barHeight' => 40);
    	//'font' => 4(set size of label),//'barHeight' => 40//set height of img barcode
    	$rendererOptions = array();
    	$renderer = Zend_Barcode::factory(
    			'Code128', 'image', $barcodeOptions, $rendererOptions
    	)->render();
    }
    
    public function rptTransferstockAction()
    {
    	$db = new report_Model_DbTransferStock();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date'] = date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date'] = date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    				'ad_search'		=>	'',
    				'from_location'	=>	'',
    				'to_location'	=>	'',
    				'status'		=>	-1,
    				'start_date'	=>	date("Y-m-d"),
    				'end_date'		=>	date("Y-m-d"),
    		);
    	}
    	$this->view->transfer = $db->getAllTransferStock($data);
    	$this->view->search = $data;
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch = $db->getAllBranch();
    	
    }
    
    public function rptTransferstockbyidAction()
    {
    	$id = $this->getRequest()->getParam("id");
    	$db = new report_Model_DbTransferStock();
    	$this->view->transfer_detaiil = $db->getAllTransferStockById($id);
    }
	
    public function rptTransferstockdetailAction()
    {
    	$db = new report_Model_DbTransferStock();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date'] = date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date'] = date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    				'ad_search'		=>	'',
    				'from_location'	=>	'',
    				'to_location'	=>	'',
    				'status'		=>	-1,
    				'start_date'	=>	date("Y-m-d"),
    				'end_date'		=>	date("Y-m-d"),
    		);
    	}
    	$this->view->transfer_detail = $db->getAllTransferStockDetail($data);
    	$this->view->search = $data;
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    	 
    	$db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch = $db->getAllBranch();
    	 
    }
    
    
    public function rptReturnStockAction()//purchase report
    {
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
			$data['start_date'] = date("Y-m-d",strtotime($data['start_date']));
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
    				'branch'		=>	'',
    		);
    	}
    	$this->view->product = $db->getAllProductSold($data);
    	$this->view->search = $data;
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    
    
}