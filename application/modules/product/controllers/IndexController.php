<?php
class Product_indexController extends Zend_Controller_Action
{
public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
	function updatecodeAction(){
		$db = new Product_Model_DbTable_DbProduct();
		$db->getProductCoded();
	}
    public function indexAction()
    {
    	$db = new Product_Model_DbTable_DbProduct();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$level = $result["level"];
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    			'branch'	=>	'',
    			'brand'		=>	'',
    			'category'	=>	'',
    			'scale'		=>	0,
    			'color'		=>	'',
    			'size'		=>	'',
    			'status'	=>	1
    		);
    	}
// 		if($level==1 or $level==2){
			$rows = $db->getAllProductForAdmin($data);
			$columns=array("សាខា","លេខកូតទំនិញ","ទំនិញ","ប្រភេទ","ប្រភេទទំនិញ","ខ្នាត","ចំនួន","តម្លៃលក់","ថ្លៃដើម","អ្នកប្រើប្រាស់","ស្ថានការ");
// 		}else{
// 			$rows = $db->getAllProduct($data);
// 			$columns=array("BRANCH_NAME","ITEM_CODE","ITEM_NAME",
// 					"CATEGORY","MEASURE","QTY","MASTER_PRICE","DEALER_PRICE","USER","STATUS");
// 		}
		$link=array(
			'module'=>'product','controller'=>'index','action'=>'edit',
		);
		$linkcopy=array(
			'module'=>'product','controller'=>'index','action'=>'copy',
		);
	
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(10, $columns, $rows,array('item_name'=>$link,'item_code'=>$link,'barcode'=>$link,'branch'=>$link));
		
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
        
	}
	public function addAction()
	{
		$db = new Product_Model_DbTable_DbProduct();
		if($this->getRequest()->isPost()){ 
			try{
				$post = $this->getRequest()->getPost();
				$db->add($post);
				Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ", '/product/index');
				Application_Form_FrmMessage::message("បញ្ចូលដោយជោគជ័យ");
		  }catch (Exception $e){
		  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
		  }
		}
		$rs_branch = $db->getBranch();
		$this->view->branch = $rs_branch;
		
		$formProduct = new Product_Form_FrmProduct();
		$formStockAdd = $formProduct->add(null);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form = $formStockAdd;
		
		$formBrand = new Product_Form_FrmBrand();
		$frmBrand = $formBrand->Brand();
		$this->view->frmBrand = $frmBrand;
		Application_Model_Decorator::removeAllDecorator($frmBrand);
		
		$formCat = new Product_Form_FrmCategory();
		$frmCat = $formCat->cat();
		$this->view->frmCat = $frmCat;
		Application_Model_Decorator::removeAllDecorator($frmCat);
		
		$formMeasure = new Product_Form_FrmMeasure();
		$frmMesure = $formMeasure->measure();
		$this->view->frmMesure = $frmMesure;
		Application_Model_Decorator::removeAllDecorator($frmMesure);
		
		$fmOther = new Product_Form_FrmOther();
		$frmOther = $fmOther->add();
		Application_Model_Decorator::removeAllDecorator($frmOther);
		$this->view->frmOther = $frmOther;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->exchange_rate = $db->getExchangeRate();
	}
	public function editAction()
	{
		$id = $this->getRequest()->getParam("id"); 
		$db = new Product_Model_DbTable_DbProduct();
		if($this->getRequest()->isPost()){ 
			try{
				$post = $this->getRequest()->getPost();
				$post["id"] = $id;
				$db->edit($post);
				if(isset($post["save_close"]))
				{
					Application_Form_FrmMessage::Sucessfull("កែប្រែជោគជ័យ", '/product/index');
				}
			  }catch (Exception $e){
			  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
			  }
		}
		$this->view->rs_location = $db->getProductLocation($id);
		$rs = $db->getProductById($id);
		$this->view->rs = $rs;
		$formProduct = new Product_Form_FrmProduct();
		$formStockAdd = $formProduct->add($rs);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form = $formStockAdd;
		
		$formBrand = new Product_Form_FrmBrand();
		$frmBrand = $formBrand->Brand();
		$this->view->frmBrand = $frmBrand;
		Application_Model_Decorator::removeAllDecorator($frmBrand);
		
		$formCat = new Product_Form_FrmCategory();
		$frmCat = $formCat->cat();
		$this->view->frmCat = $frmCat;
		Application_Model_Decorator::removeAllDecorator($frmCat);
		
		$formMeasure = new Product_Form_FrmMeasure();
		$frmMesure = $formMeasure->measure();
		$this->view->frmMesure = $frmMesure;
		Application_Model_Decorator::removeAllDecorator($frmMesure);
		
		$fmOther = new Product_Form_FrmOther();
		$frmOther = $fmOther->add();
		Application_Model_Decorator::removeAllDecorator($frmOther);
		$this->view->frmOther = $frmOther;
	}
	public function copyAction()
	{
		$id = $this->getRequest()->getParam("id");
		$db = new Product_Model_DbTable_DbProduct();
		if($this->getRequest()->isPost()){
			try{
				$post = $this->getRequest()->getPost();
				$post["id"] = $id;
				$db->add($post);
				if(isset($post["save_close"]))
				{
					Application_Form_FrmMessage::Sucessfull("កែប្រែជោគជ័យ", '/product/index');
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
			}
		}
		$this->view->rs_location = $db->getProductLocation($id);
		$rs = $db->getProductById($id);
		$this->view->rs = $rs;
		$formProduct = new Product_Form_FrmProduct();
		$formStockAdd = $formProduct->add($rs);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form = $formStockAdd;
	
		$formBrand = new Product_Form_FrmBrand();
		$frmBrand = $formBrand->Brand();
		$this->view->frmBrand = $frmBrand;
		Application_Model_Decorator::removeAllDecorator($frmBrand);
			
		$formCat = new Product_Form_FrmCategory();
		$frmCat = $formCat->cat();
		$this->view->frmCat = $frmCat;
		Application_Model_Decorator::removeAllDecorator($frmCat);
			
		$formMeasure = new Product_Form_FrmMeasure();
		$frmMesure = $formMeasure->measure();
		$this->view->frmMesure = $frmMesure;
		Application_Model_Decorator::removeAllDecorator($frmMesure);
			
		$fmOther = new Product_Form_FrmOther();
		$frmOther = $fmOther->add();
		Application_Model_Decorator::removeAllDecorator($frmOther);
		$this->view->frmOther = $frmOther;
	}
	
	public function addBrandAction(){
		if($this->getRequest()->isPost()){
			try {
				$post=$this->getRequest()->getPost();
				$db = new Product_Model_DbTable_DbBrand();
				$brand_id =$db->addNew($post);
				$result = array('brand_id'=>$brand_id);
				echo Zend_Json::encode($result);
				exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}
	public function addCategoryAction(){
		if($this->getRequest()->isPost()){
			try {
				$post=$this->getRequest()->getPost();
				$db = new Product_Model_DbTable_DbCategory();
				$cat_id =$db->addNew($post);
				$result = array('cat_id'=>$cat_id);
				echo Zend_Json::encode($result);
				exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}

	public function addMeasureAction(){
		if($this->getRequest()->isPost()){
			try {
				$post=$this->getRequest()->getPost();
				$db = new Product_Model_DbTable_DbMeasure();
				if(empty($post['measure_name'])){
					$post['measure_name']=$post['name'];
				}
				$measure_id =$db->addNew($post);
				$result = array('measure_id'=>$measure_id);
				echo Zend_Json::encode($result);
				exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}
	public function addSaleagentAction(){
		if($this->getRequest()->isPost()){
			try {
				$post=$this->getRequest()->getPost();
				$db = new Product_Model_DbTable_DbMeasure();

				$saleagent_id =$db->addSaleagent($post);
				$result = array('saleagent_id'=>$saleagent_id);
				echo Zend_Json::encode($result);
				exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}
	
	public function addOtherAction(){
		if($this->getRequest()->isPost()){
			try {
				$post=$this->getRequest()->getPost();
				$db = new Product_Model_DbTable_DbOther();
				$other_id =$db->addNew($post);
				$result = array('other_id'=>$other_id);
				echo Zend_Json::encode($result);
				exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}
	public function addNewproudctAction(){
		if($this->getRequest()->isPost()){
			try {
				$post=$this->getRequest()->getPost();
				$db = new Product_Model_DbTable_DbProduct();
				$pro_id =$db->addAjaxProduct($post);
				$result = array('pro_id'=>$pro_id);
				echo Zend_Json::encode($result);
				exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}
	
	function outstockAction(){
		$db = new Product_Model_DbTable_DbProduct();
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
    			'status'	=>	1
    		);
    	}
    	$this->view->product = $db->getAllProductOutStock($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	function lowstockAction(){
		$db = new Product_Model_DbTable_DbProduct();
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
    			'status'	=>	1
    		);
    	}
    	$this->view->product = $db->getAllProductLowStock($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
}

