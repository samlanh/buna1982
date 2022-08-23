<?php 
class Sales_Form_FrmStock extends Zend_Form
{
	public function init()
    {
    	
	}
	public function showSaleAgentForm($data=null, $stockID=null) {

		$db=new Application_Model_DbTable_DbGlobal();
		$db_sale = new Sales_Model_DbTable_DbSalesAgent();
		$codes = $db_sale->getSaleAgentCode(1);
		$date =new Zend_Date();
		$nameElement = new Zend_Form_Element_Text('name');
		$nameElement->setAttribs(array('class'=>'validate[required] form-control','placeholder'=>'Enter Agent Name'));
    	$this->addElement($nameElement);
    	
    	$phoneElement = new Zend_Form_Element_Text('phone');
    	$phoneElement->setAttribs(array('class'=>'validate[required] form-control','placeholder'=>'Enter Phone Number'));
    	$this->addElement($phoneElement);
    	
    	$emailElement = new Zend_Form_Element_Text('email');
    	$emailElement->setAttribs(array('class'=>'form-control','placeholder'=>'Enter Email Address'));
    	$this->addElement($emailElement);
    	
    	$addressElement = new Zend_Form_Element_Text('address');
    	$addressElement->setAttribs(array('placeholder'=>'Enter Current Address',"class"=>"form-control"));
    	$this->addElement($addressElement);
    	
    	
		$descriptionElement = new Zend_Form_Element_Textarea('description');
		$descriptionElement->setAttribs(array('placeholder'=>'Descrtion Here...',"class"=>"form-control","rows"=>3));
    	$this->addElement($descriptionElement);
    	
    	$rowsStock = $db->getGlobalDb('SELECT id,name FROM tb_sublocation WHERE name!=""  ORDER BY id DESC ');
    	$optionsStock = array('1'=>'Default Location','-1'=>'Add New Location');
    	if(count($rowsStock) > 0) {
    		foreach($rowsStock as $readStock) $optionsStock[$readStock['id']]=$readStock['name'];
    	}
    	$mainStockElement = new Zend_Form_Element_Select('branch_id');
    	$mainStockElement->setAttribs(array('OnChange'=>'getSaleCode()','class'=>'form-control select2me'));
    	$mainStockElement->setMultiOptions($optionsStock);
    	$this->addElement($mainStockElement);
		
		$row_status = $db->getGlobalDb('SELECT v.key_code,v.name_kh FROM tb_view as v WHERE v.type=5 AND v.status=1');
     	$option_status = array();
     	if(count($row_status) > 0) {
     		foreach($row_status as $rs) $option_status[$rs['key_code']]=$rs['name_kh'];
     	}
		$status=new Zend_Form_Element_Select("status");
		$status->setAttribs(array('class'=>'form-control select2me'));
		$status->setMultiOptions($option_status);
		$this->addElement($status);
		$status=new Zend_Form_Element_Select("status");
		$status->setAttribs(array('class'=>'form-control select2me'));
		$status->setMultiOptions($option_status);
		$this->addElement($status);
        	
    	
    	//set value when edit
    	if($data != null) {
    		$idElement = new Zend_Form_Element_Hidden('id');
    	    $this->addElement($idElement);
    	    $idElement->setValue($data['id']);
    		$nameElement->setValue($data['name']);
    		$phoneElement->setValue($data['phone']);
    		$emailElement->setValue($data['email']);
    		$addressElement->setValue($data['address']);
    		$mainStockElement->setValue($data["branch_id"]);
    		$descriptionElement->setValue($data['note']);
    		
			$status->setValue($data["status"]);
    	}
    	return $this;
	}
}