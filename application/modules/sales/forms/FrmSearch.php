<?php 
class Sales_Form_FrmSearch extends Zend_Form
{
public function init()
	{
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db=new Application_Model_DbTable_DbGlobal();
		
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$nameValue = $request->getParam('text_search');
		$nameElement = new Zend_Form_Element_Text('text_search');
		$nameElement->setAttribs(array(
				'class'=>'form-control'
				));
		$nameElement->setValue($nameValue);
		$this->addElement($nameElement);
		
		$rs=$db->getGlobalDb('SELECT id,cust_name,`phone` FROM tb_customer WHERE cust_name!="" AND status=1 ');
		$options=array($tr->translate('Choose Customer'));
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
		
		$startDateValue = $request->getParam('start_date');
		$endDateValue = $request->getParam('end_date');
		
		if($endDateValue==""){
			$endDateValue=date("d-m-Y");
		}
		
		$startDateElement = new Zend_Form_Element_Text('start_date');
		$startDateElement->setValue($startDateValue);
		$startDateElement->setAttribs(array(
				'class'=>'form-control form-control-inline date-picker input-icon right',
				'placeholder'=>'Start Date',
				'data-date-format'=>"dd-mm-yyyy"
		));
		$this->addElement($startDateElement);
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$rs = $_dbgb->getAllBranch();
		$options=array(0=>$tr->translate('SELECT_BRANCH'));
		if(!empty($rs)) foreach($rs as $read) $options[$read['id']]=$read['name'];
		$locationID = new Zend_Form_Element_Select('branch_id');
		$locationID ->setAttribs(array('class'=>'validate[required] form-control select2me'));
		$locationID->setMultiOptions($options);
		$locationID->setattribs(array(
				'Onchange'=>'AddLocation()',));
	    $locationID->setValue($request->getParam("branch_id"));
		$this->addElement($locationID);
		
		$endDateElement = new Zend_Form_Element_Text('end_date');
		$endDateElement->setValue($endDateValue);
		$this->addElement($endDateElement);
		$endDateElement->setAttribs(array(
				'class'=>'form-control form-control-inline date-picker',
				'data-date-format'=>"dd-mm-yyyy"
		));
		
		
		$rows= $db->getGlobalDb('SELECT v.key_code,v.`name_en`,v.`name_kh` FROM `tb_view` AS v WHERE v.`status`=1 AND v.`name_en`!="" AND v.`type`=6');
		$opt= array(0=>"Choose Customer Type");
		if(count($rows) > 0) {
			foreach($rows as $readStock) $opt[$readStock['key_code']]=$readStock['name_en'];
		}
		$customer_type = new Zend_Form_Element_Select('customer_type');
		$customer_type->setAttribs(array('class'=>'form-control select2me'));
		$customer_type->setMultiOptions($opt);
		$this->addElement($customer_type);
		
	}
}