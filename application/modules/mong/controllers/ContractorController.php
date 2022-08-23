<?php
class Mong_ContractorController extends Zend_Controller_Action
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
		$rows = $db->getAllProductForAdmin($data);
		$columns=array("BRANCH_NAME","ITEM_CODE","ITEM_NAME",
					"PRODUCT_CATEGORY","OPTION_TYPE","MEASURE","QTY","SOLD_PRICE","COST_PRICE","USER","STATUS");
		$link=array(
				'module'=>'product','controller'=>'index','action'=>'edit',
		);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows,array('item_name'=>$link,'item_code'=>$link,'barcode'=>$link,'branch'=>$link));
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
// 		$db = new Product_Model_DbTable_DbProduct();
// 			if($this->getRequest()->isPost()){ 
// 				try{
// 					$post = $this->getRequest()->getPost();
// 					$db->add($post);
// 					if(isset($post["save_close"]))
// 					{
// 						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/product/index');
// 					}else{
// 						Application_Form_FrmMessage::message("INSERT_SUCCESS");
// 					}
// 				  }catch (Exception $e){
// 				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
// 				  }
// 			}
// 			$rs_branch = $db->getBranch();
// 			$this->view->branch = $rs_branch;
			
// 			$this->view->price_type = $db->getPriceType();
			
// 			$formProduct = new Product_Form_FrmProduct();
// 			$formStockAdd = $formProduct->add(null);
// 			Application_Model_Decorator::removeAllDecorator($formStockAdd);
// 			$this->view->form = $formStockAdd;
	}
	public function editAction()
	{
// 		$id = $this->getRequest()->getParam("id"); 
// 		$db = new Product_Model_DbTable_DbProduct();
// 		if($this->getRequest()->isPost()){ 
// 				try{
// 					$post = $this->getRequest()->getPost();
// 					$post["id"] = $id;
// 					$db->edit($post);
// 					if(isset($post["save_close"]))
// 					{
// 						Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", '/product/index');
// 					}
// 				  }catch (Exception $e){
// 				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
// 				  }
// 		}
// 		$this->view->rs_location = $db->getProductLocation($id);
// 		$this->view->rs_price = $db->getProductPrcie($id);
// 		$rs = $db->getProductById($id);
// 		$formProduct = new Product_Form_FrmProduct();
// 		$formStockAdd = $formProduct->add($rs);
// 		Application_Model_Decorator::removeAllDecorator($formStockAdd);
// 		$this->view->form = $formStockAdd;

	}
}