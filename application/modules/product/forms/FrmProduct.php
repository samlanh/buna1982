<?php 
class Product_Form_FrmProduct extends Zend_Form
{
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
	}
	/////////////	Form Product		/////////////////
	public function add($data=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Product_Model_DbTable_DbProduct();
		$p_code = $db->getProductCode();
		$name = new Zend_Form_Element_Text("name");
		$name->setAttribs(array(
				'class'=>"validate[required] form-control"
			));	
			
		$pro_code = new Zend_Form_Element_Text("pro_code");
		$pro_code->setAttribs(array(
				'class'=>'form-control',
		));
		$pro_code->setValue($p_code);
		 
		$serial = new Zend_Form_Element_Text("serial");
		$serial->setAttribs(array(
				'class'=>'form-control',
		));
		 
		$barcode = new Zend_Form_Element_Text("barcode");
		$barcode->setAttribs(array(
				'class'=>'form-control',
		));
		$barcodevalue = $db->getProductbarcode();
		$barcode->setValue($barcodevalue);
		
		$opt = array(''=>$tr->translate("SELECT_BRAND"),-1=>$tr->translate("ADD_NEW_BRAND"));
		$brand = new Zend_Form_Element_Select("brand");
		$brand->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'getPopupBrand();',
		));
		$row_brand= $db->getBrand();
		if(!empty($row_brand)){
			foreach ($row_brand as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$brand->setMultiOptions($opt);
		 
// 		$opt = array(''=>$tr->translate("SELECT_MODEL"),-1=>$tr->translate("ADD_NEW_MODEL"));
// 		$model = new Zend_Form_Element_Select("model");
// 		$model->setAttribs(array(
// 				'class'=>'form-control select2me',
// 				'onChange'=>'getPopupModel()',
// 		));
// 		$row_model = $db->getModel();
// 		if(!empty($row_model)){
// 			foreach ($row_model as $rs){
// 				$opt[$rs["key_code"]] = $rs["name"];
// 			}
// 		}
// 		$model->setMultiOptions($opt);
		 
		$opt = array(''=>$tr->translate("SELECT_CATEGORY"),-1=>$tr->translate("ADD_NEW_CATEGORY"));
		$category = new Zend_Form_Element_Select("category");
		$category->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'getPopupCategory()',
		));
		$row_cat = $db->getCategory();
		if(!empty($row_cat)){
			foreach ($row_cat as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$category->setMultiOptions($opt);
		
		$opt = array(''=>$tr->translate("SELECT_COLOR"),-1=>$tr->translate("ADD_NEW_COLOR"));
		$color = new Zend_Form_Element_Select("color");
		$color->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'getPopupColor()',
		));
		$row_color = $db->getColor();
		if(!empty($row_color)){
			foreach ($row_color as $rs){
				$opt[$rs["key_code"]] = $rs["name"];
			}
		}
		$color->setMultiOptions($opt);
		 
		$unit = new Zend_Form_Element_Text("unit");
		$unit->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required',
				'readOnly'=>'readOnly'
		));
		$unit->setValue(1);
		 
		$qty_per_unit = new Zend_Form_Element_Text("qty_unit");
		$qty_per_unit->setAttribs(array(
				'class'=>"validate[required,custom[number]] form-control"
			
		));
		$qty_per_unit->setValue(1);
		 
		$opt = array(''=>$tr->translate("SELECT_MEASURE"),-1=>$tr->translate("ADD_NEW_MEASURE"));
		$measure = new Zend_Form_Element_Select("measure");
		$measure->setAttribs(array(
				'class'=>'form-control select2me',
				'Onchange'	=>	'getMeasureLabel();getPopupMeasure();'
		));
		$row_measure= $db->getMeasure();
		if(!empty($row_measure)){
			foreach ($row_measure as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$measure->setMultiOptions($opt);
		 
		$label = new Zend_Form_Element_Text("label");
		$label->setAttribs(array(
				'class'=>"form-control",
				'placeHolder'=>'ឈ្មោះខ្នាតបំបែក'
		));
		 
		$description = new Zend_Form_Element_Text("description");
		$description->setAttribs(array(
				'class'=>'form-control',
		));
		
		$price = new Zend_Form_Element_Text("price");
		$price->setAttribs(array(
				'class'=>'form-control',
				'onkeypress'=>"return isNumberKey(event)",
		));
		$price->setValue(0);
		
		$selling_price_khmer = new Zend_Form_Element_Text("selling_price_khmer");
		$selling_price_khmer->setAttribs(array(
				'class'=>'form-control',
				'onkeypress'=>"return isNumberKey(event)",
				'onkeyup'=>"convertToDollar()",
		));
		
		$selling_price = new Zend_Form_Element_Text("selling_price");
		$selling_price->setAttribs(array(
				'class'=>'form-control',
				'onkeypress'=>"return isNumberKey(event)"
		));
		
		$status = new Zend_Form_Element_Select("status");
		$opt = array('1'=>$tr->translate("ប្រើប្រាស់"),'0'=>$tr->translate("មិនប្រើប្រាស់"));
		$status->setAttribs(array(
				'class'=>'form-control select2me',
				'required'=>'required',
		));
		$status->setMultiOptions($opt);
		
		$branch = new Zend_Form_Element_Select("branch");
		$opt = array(''=>$tr->translate("SELECT_BRANCH"));
		$row_branch = $db->getBranch();
		if(!empty($row_branch)){
			foreach ($row_branch as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$branch->setAttribs(array(
				'class'=>'form-control select2me',
				'Onchange'	=>	'addNewProLocation()'
		));
		$branch->setMultiOptions($opt);
		
		
		if($data!=null){
			$name->setValue($data["item_name"]);
			$pro_code->setValue($data["item_code"]);
			$barcode->setValue($data["barcode"]);
			$selling_price_khmer->setValue($data["selling_price_khmer"]);
			$selling_price->setValue($data["selling_price"]);
			$brand->setValue($data["brand_id"]);
			$category->setValue($data["cate_id"]);
// 			$model->setValue($data["model_id"]);
			$color->setValue($data["color_id"]);
// 			$size->setValue($data["size_id"]);
			$measure->setValue($data["measure_id"]);
			$label->setValue($data["unit_label"]);
			$description->setValue($data["note"]);
			$qty_per_unit->setValue($data["qty_perunit"]);
			$status->setValue($data["status"]);
			$price->setValue($data["price"]);
		}
		
		$this->addElements(array($selling_price_khmer,$selling_price,$price,$branch,$status,$pro_code,$name,$serial,$brand,$barcode,$category,
				$color,$measure,$qty_per_unit,$unit,$label,$description));
		return $this;
	}
	function productFilter(){
		
		$session_user=new Zend_Session_Namespace('auth');
		$userId = $session_user->user_id;
		$branchId = $session_user->branch_id;
		$level = $session_user->level;
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$db = new Product_Model_DbTable_DbProduct();
		
		$ad_search = new Zend_Form_Element_Text("ad_search");
		$ad_search->setAttribs(array(
				'class'=>'form-control',
		));
		$ad_search->setValue($request->getParam("ad_search"));
		
		$branch = new Zend_Form_Element_Select("branch");
		$opt = array(''=>$tr->translate("SELECT_BRANCH"));
		$row_branch = $_dbgb->getAllBranch();;
		if(!empty($row_branch)){
			foreach ($row_branch as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$branch->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$branch->setMultiOptions($opt);
		$branch->setValue($request->getParam("branch"));
		
		/*
		if ($level!=1){
			$branch->setValue($branchId);
			$branch->setAttribs(array(
					'disabled'=>'disabled',
			));
		}
		*/
		
		
		$status = new Zend_Form_Element_Select("status");
		$opt = array('-1'=>$tr->translate("STATUS"),'1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$status->setMultiOptions($opt);
		$status->setValue($request->getParam("status"));
		
		$type = new Zend_Form_Element_Select("type");
		$opt = array('-1'=>$tr->translate("TYPE"),'0'=>$tr->translate("ផលិតផល"),'1'=>$tr->translate("សេវាកម្ម"));
		$type->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$type->setMultiOptions($opt);
		$type->setValue($request->getParam("type"));
		
		$opt = array(''=>$tr->translate("SELECT_BRAND"));
		$brand = new Zend_Form_Element_Select("brand");
		$brand->setAttribs(array(
				'class'=>'form-control select2me',
		));
		
		$row_brand = $db->getBrand();
		if(!empty($row_brand)){
			foreach ($row_brand as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$brand->setMultiOptions($opt);
		$brand->setValue($request->getParam("brand"));
			
		$opt = array(''=>$tr->translate("SELECT_MODEL"));
		$model = new Zend_Form_Element_Select("model");
		$model->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$row_model = $db->getModel();
		if(!empty($row_model)){
			foreach ($row_model as $rss){
				$opt[$rss["key_code"]] = $rss["name"];
			}
		}
		$model->setMultiOptions($opt);
		$model->setValue($request->getParam("model"));
			
		$opt = array(''=>$tr->translate("SELECT_CATEGORY"));
		$category = new Zend_Form_Element_Select("category");
		$category->setAttribs(array(
				'class'=>'form-control select2me',
		));	
		$row_cat = $db->getCategory();
		if(!empty($row_cat)){
			foreach ($row_cat as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$category->setMultiOptions($opt);
		$category->setValue($request->getParam("category"));
		
			
		$opt = array(''=>$tr->translate("ជ្រើសរើសសេវាកម្ម"));
		$service = new Zend_Form_Element_Select("service");
		$service->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$row_cation = $db->getService();
		if(!empty($row_cation)){
			foreach ($row_cation as $rs){
				$opt[$rs["id"]] = $rs["item_name"];
			}
		}
		$service->setMultiOptions($opt);
		$service->setValue($request->getParam("scale"));
		
		$opt = array(''=>$tr->translate("ជ្រើសរើសខ្នាត"));
		$scale = new Zend_Form_Element_Select("scale");
		$scale->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$row_scale = $db->getScale();
		if(!empty($row_scale)){
			foreach ($row_scale as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$scale->setMultiOptions($opt);
		$scale->setValue($request->getParam("scale"));

		
		$opt = array(''=>$tr->translate("SELECT_COLOR"));
		$color = new Zend_Form_Element_Select("color");
		$color->setAttribs(array(
				'class'=>'form-control select2me',
		));	
		$row_color = $db->getColor();
		if(!empty($row_color)){
			foreach ($row_color as $rs){
				$opt[$rs["key_code"]] = $rs["name"];
			}
		}
		$color->setMultiOptions($opt);
		$color->setValue($request->getParam("color"));
			
		$opt = array(''=>$tr->translate("SELECT_SIZE"));
		$size = new Zend_Form_Element_Select("size");
		$size->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$row_size = $db->getSize();
		if(!empty($row_size)){
			foreach ($row_size as $rs){
				$opt[$rs["key_code"]] = $rs["name"];
			}
		}
		$size->setMultiOptions($opt);
		$size->setValue($request->getParam("size"));
		
		$status_qty = new Zend_Form_Element_Select("status_qty");
		$opt = array(-1=>$tr->translate("ទាំងអស់"),1=>$tr->translate("ផលិតផលមានស្តុក"),0=>$tr->translate("ផលិតផលអស់ពីស្តុក"));
		$status_qty->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$status_qty->setMultiOptions($opt);
		$status_qty->setValue($request->getParam("status_qty"));
		
		//date
		$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
				'class'=>'form-control date-picker ',
				'data-date-format'=>"dd-mm-yyyy",
				'placeHolder'=>'start date',
		));
		$_date = $request->getParam("start_date");
		
		if(!empty($_date)){
			$start_date->setValue($_date);
		}
		
		
		$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$date = date("Y-m-d");
		$end_date->setAttribs(array(
				'class'=>'form-control date-picker',
				'data-date-format'=>"dd-mm-yyyy",
		));
		$_date = $request->getParam("end_date");
		if(empty($_date)){
			$_date = date("d-m-Y");
		}
		$end_date->setValue($_date);
		
		$db=new Application_Model_DbTable_DbGlobal();
		$rs=$db->getGlobalDb('SELECT id,cust_name,`phone` FROM tb_customer WHERE cust_name!="" AND status=1 order by id ASC');
		$options=array('-1'=>$tr->translate('Choose Customer'));
		$vendorValue = $request->getParam('customer_id');
		if(!empty($rs)) foreach($rs as $read) $options[$read['id']]=$read['cust_name'];
		$vendor_element=new Zend_Form_Element_Select('customer_id');
		$vendor_element->setMultiOptions($options);
		$vendor_element->setAttribs(array(
				'id'=>'customer_id',
				'class'=>'form-control select2me',
				'data-date-format'=>"dd-mm-yyyy"
		));
		$vendor_element->setValue($vendorValue);
		$this->addElement($vendor_element);
		
		$this->addElements(array($type,$start_date,$end_date,$status_qty,$ad_search,$branch,$brand,$model,$scale,$service,$category,$color,$size,$status));
		return $this;
	}
}