<?php 
class Purchase_Form_FrmVendor extends Zend_Form
{
	public function init()
    {	
	}
	/////////////	Form vendor		/////////////////
public function AddVendorForm($data=null) {
		$db=new Application_Model_DbTable_DbGlobal();
		
		$nameElement = new Zend_Form_Element_Text('v_name');
		$nameElement->setAttribs(array('required'=>1,'class'=>'validate[required] form-control','placeholder'=>'ឈ្មោះក្រុមហ៊ុន'));
    	$this->addElement($nameElement);
    	
    	$vendor_phoneElement = new Zend_Form_Element_Text('v_phone');
    	$vendor_phoneElement->setAttribs(array('placeholder'=>'ទូរស័ព្ទក្រុមហ៊ុន',"class"=>"form-control"));
    	$this->addElement($vendor_phoneElement);
    	
    	$contactElement = new Zend_Form_Element_Text('contact_person');
    	$contactElement->setAttribs(array('placeholder'=>'អ្នកទំនាក់ទំនង',"class"=>"form-control"));
    	$this->addElement($contactElement);

    	$contact_phone = new Zend_Form_Element_Text('phone_person');
    	$contact_phone->setAttribs(array('placeholder'=>'លេខអ្នកទំនាក់ទំនង',"class"=>"form-control"));
    	$this->addElement($contact_phone);
    	
    	$emailElement = new Zend_Form_Element_Text('email');
    	$emailElement->setAttribs(array('class'=>'validate[custom[email]] form-control','placeholder'=>'អ៊ីម៉ែល'));
    	$this->addElement($emailElement);
    	
    	$websiteElement = new Zend_Form_Element_Text('website');
    	$websiteElement->setAttribs(array('placeholder'=>'គេហទំព័រ',"class"=>"form-control"));
    	$this->addElement($websiteElement);
    	
    	///update 
    	$remarkElement = new Zend_Form_Element_Textarea('note');
    	$remarkElement->setAttribs(array('placeholder'=>'សម្គាល់',"class"=>"form-control","rows"=>1));
    	$this->addElement($remarkElement);
    	         
    	$addressElement = new Zend_Form_Element_Textarea('address');
    	$addressElement->setAttribs(array('placeholder'=>'អាស័យដ្ឋាន',"class"=>"form-control","rows"=>1));
    	$this->addElement($addressElement);
    	
    	$balancelement = new Zend_Form_Element_Text('balance');
    	$balancelement->setValue("0.00");
    	$balancelement->setAttribs(array('readonly'=>'readonly',"class"=>"form-control"));
    	$this->addElement($balancelement); 

		$_stutas = new Zend_Form_Element_Select('status');
		$_stutas ->setAttribs(array(
				'class'=>' form-control',			
		));
		$options= array(1=>"ប្រើប្រាស់",0=>"មិនប្រើប្រាស់");
		$_stutas->setMultiOptions($options);
		$this->addElement($_stutas); 
    	
    	if($data != null) {
	      	$idElement = new Zend_Form_Element_Hidden('id');
   		   	$this->addElement($idElement);
    	   	$idElement->setValue($data['vendor_id']);
    	    $nameElement->setValue($data['v_name']);
    	    $vendor_phoneElement->setValue($data['v_phone']);
    	    $contactElement->setValue($data['contact_person']);
    	    $contact_phone->setValue($data['phone_person']);
    	    $addressElement->setValue($data["address"]);
    	    $emailElement->setValue($data['email']);
    	    $websiteElement->setValue($data['website']);
    		$remarkElement->setValue($data['note']);
    		$balancelement->setValue($data['balance']);
			$_stutas->setValue($data['status']);
    	}
    	return $this;
	}
}