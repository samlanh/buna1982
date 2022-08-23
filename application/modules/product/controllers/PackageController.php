<?php
class Product_PackageController extends Zend_Controller_Action
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
    public function indexAction()
    {
    	$db = new Product_Model_DbTable_DbPackage();
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
    			'model'		=>	'',
    			'color'		=>	'',
    			'size'		=>	'',
    			'status'	=>	-1
    		);
    	}
			$rows = $db->getAllProductForAdmin($data);
			$columns=array("ITEM_CODE","ITEM_NAME","PRODUCT_CATEGORY","ប្រភេទ","SOLD_PRICE","USER","STATUS");
		$link=array(
				'module'=>'product','controller'=>'package','action'=>'edit',
		);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows,array('item_name'=>$link,'item_code'=>$link,'barcode'=>$link,'cat'=>$link));
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
        
	}
	public function addAction()
	{
			$db = new Product_Model_DbTable_DbPackage();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->addPackage($post);
						Application_Form_FrmMessage::Sucessfull("បញ្ចូលដោយជោគជ័យ", '/product/package');
						Application_Form_FrmMessage::message("បញ្ចូលដោយជោគជ័យ");
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			$db = new Product_Model_DbTable_DbProduct();
			$this->view->caters = $db->getCategory();
			$this->view->pro_code = $db->getProductCode();
			
			$test = $this->view->barcode = $db->getProductbarcode();
			
			$db = new Sales_Model_DbTable_Dbpos();
			$this->view->rsproduct = $db->getAllProductName();
	}
	public function editAction()
	{
		$id = $this->getRequest()->getParam("id"); 
		$dbp = new Product_Model_DbTable_DbPackage();
		if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post["id"] = $id;
					$dbp->editPackage($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ", '/product/package');
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
		}
		$db = new Product_Model_DbTable_DbProduct();
		$this->view->rs = $db->getProductById($id);
		
		$this->view->caters = $db->getCategory();
		$this->view->pro_code = $db->getProductCode();
			
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->rsproduct = $db->getAllProductName();
		
		$this->view->rspakage = $dbp->getProductByPackageid($id);
	}
	
	function getproductAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Product_Model_DbTable_DbPackage();
			$rs =$db->getProductById($post['product_id']);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
}

