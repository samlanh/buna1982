<?php 
class Sales_Form_FrmCustomer extends Zend_Form
{
	public function init()
    {	
	}
	/////////////	Form vendor		/////////////////
public function Formcustomer($data=null) {
		$db=new Application_Model_DbTable_DbGlobal();
		$db_cu= new Sales_Model_DbTable_DbCustomer();
		
		$code = $db_cu->getCustomerCode(1);
		
		$nameElement = new Zend_Form_Element_Text('txt_name');
		$nameElement->setAttribs(array('class'=>'validate[required] form-control','placeholder'=>'Enter Name'));
    	$this->addElement($nameElement);
    	
    	$rowsStock = $db->getGlobalDb('SELECT id,name FROM tb_sublocation WHERE name!="" ORDER BY id DESC ');
    	$optionsStock = array('1'=>'Default Location','-1'=>'Add New Location');
    	if(count($rowsStock) > 0) {
    		foreach($rowsStock as $readStock) $optionsStock[$readStock['id']]=$readStock['name'];
    	}
    	$mainStockElement = new Zend_Form_Element_Select('branch_id');
    	$mainStockElement->setAttribs(array("onChange"=>"getCustomerCode()",'class'=>'form-control select2me'));
    	$mainStockElement->setMultiOptions($optionsStock);
    	$this->addElement($mainStockElement);
    	
    	$phoneElement = new Zend_Form_Element_Text('txt_phone');
    	$phoneElement->setAttribs(array('placeholder'=>'Enter Contact Number',"class"=>"form-control"));
    	$this->addElement($phoneElement);
    	
    	$contact_phone = new Zend_Form_Element_Text('contact_phone');
    	$contact_phone->setAttribs(array('placeholder'=>'Enter Contact Number',"class"=>"validate[required] form-control"));
    	$this->addElement($contact_phone);
    	
    	$emailElement = new Zend_Form_Element_Text('txt_mail');
    	$emailElement->setAttribs(array('class'=>'form-control','placeholder'=>'Enter Email Address'));
    	$this->addElement($emailElement);
    	
    	$remarkElement = new Zend_Form_Element_Textarea('remark');
    	$remarkElement->setAttribs(array('placeholder'=>'Remark Here...',"class"=>"form-control","rows"=>3));
    	$this->addElement($remarkElement);
    	         
    	$addressElement = new Zend_Form_Element_Textarea('txt_address');
    	$addressElement->setAttribs(array('placeholder'=>'Enter Adress','class'=>'form-control',"rows"=>3));
    	$this->addElement($addressElement);
    	
    	$cus_code = new Zend_Form_Element_Text("cu_code");
    	$cus_code->setValue($code);
    	$cus_code->setAttribs(array("class"=>"form-control","readOnly"=>"readOnly"));
    	$this->addElement($cus_code);
		
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	$status = new Zend_Form_Element_Select('status');
    	$status->setAttribs(array('class'=>'demo-code-language form-control select2me'));
    	$opt = array(1=>"Active");
    	if($result['level']==1){
    		$opt[0]="Deactive";
    	}
    	$status->setMultiOptions($opt);
    	$this->addElement($status);
    	
    	if($data != null) {
			$id = new Zend_Form_Element_Hidden("id");
			$id->setAttribs(array("class"=>"form-control","readOnly"=>"readOnly"));
			$this->addElement($id);
		
		    $id->setValue($data['id']);
    	    $nameElement->setValue($data['cust_name']);
    		$addressElement->setValue($data["address"]);
    		$emailElement->setValue($data['email']);
    		$remarkElement->setValue($data['remark']);
    		$contact_phone->setValue($data['phone']);
    		$cus_code->setValue($data["cu_code"]);
    		$phoneElement->setValue($data["phone"]);
    		$mainStockElement->setValue($data["branch_id"]);
			$status->setValue($data["status"]);
    	}
    	return $this;
	}
}