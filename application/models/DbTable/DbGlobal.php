<?php

class Application_Model_DbTable_DbGlobal extends Zend_Db_Table_Abstract
{
    // set name value
// 	public function setName($name){
// 		$this->_name=$name;
// 	}
	protected $_name = 'tb_purchase_order';
	/**
	 * get selected record of $sql
	 * @param string $sql
	 * @return array $row;
	 */
	public function getGlobalDb($sql)
  	{
  		$db=$this->getAdapter();
  		$row=$db->fetchAll($sql);
  		if(!$row) return NULL;
  		return $row;
  	}
  	public function getGlobalDbRow($sql)
  	{
  		$db=$this->getAdapter();
  		$row=$db->fetchRow($sql);
  		if(!$row) return NULL;
  		return $row;
  	}
  	
  	public static function getActionAccess($action)
    {
    	$arr=explode('-', $action);
    	return $arr[0];    	
    }     
    
    /**
     * get CSO options for select box
     * @return array $options
     */
    
    /**
     * boolean true mean record exist already
     * @param string $conditions
     * @param string $tbl_name
     * @return boolean
     */
    public function isRecordExist($conditions,$tbl_name){
		$db=$this->getAdapter();
		$sql="SELECT * FROM ".$tbl_name." WHERE ".$conditions; 
		$stm = $db->query($sql);
		$row = $stm->fetchAll();
    	if(!$row) return false;
    	return true;    	
    }
    
    //get value in product inventory with product location (Joint)
    public function productLocationInventory($pro_id, $location_id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,pro_id,location_id,qty,qty_warning,user_id,last_mod_date,last_mod_userid
    	 FROM tb_prolocation WHERE pro_id =".$pro_id." AND location_id=".$location_id." LIMIT 1 "; 
    	
    	$row = $db->fetchRow($sql);
    	
    	if(empty($row)){
    		$session_user=new Zend_Session_Namespace('auth');
    		$userName=$session_user->user_name;
    		$GetUserId= $session_user->user_id;
    		
    		$array = array(
    				"pro_id"=>$pro_id,
    				"location_id"=>$location_id,
    				"qty"=>0,
    				"qty_warning"=>0,
    				"last_mod_userid"=>$GetUserId,
    				"user_id"=>$GetUserId,
    				"last_mod_date"=>date("Y-m-d")
    				);
    		$this->_name="tb_prolocation";
    		$this->insert($array);
    		
    		$sql="SELECT id,pro_id,location_id,qty,qty_warning,user_id,last_mod_date,last_mod_userid
    		FROM tb_prolocation WHERE pro_id =".$pro_id." AND location_id=".$location_id." LIMIT 1 ";
    		return $row = $db->fetchRow($sql);
    	}else{
    		return $row; 
    	}  	
    }
    public function productLocation($pro_id,$location_id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM tb_prolocation WHERE pro_id =".$pro_id." AND LocationId = ".$location_id." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	if(!$row) return false;
    	return $row;
    }
    public function QtyProLocation($pro_id,$location_id){//get qty location
    	$db=$this->getAdapter();
    	$sql="SELECT ProLocationID,pro_id,qty FROM tb_prolocation WHERE pro_id =".$pro_id." AND LocationId = ".$location_id." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
	//if myProductExist
    public function myProductExist($pro_id){
    	$db=$this->getAdapter();
    	$sql="SELECT pro_id FROM tb_product WHERE pro_id =".$pro_id." LIMIT 1";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    
    final public function inventoryLocation($locationid, $itemId){
    	$db=$this->getAdapter();
    	$sql="SELECT pl.ProLocationID, pl.`qty_onorder` ,pl.qty, p.qty_onhand,p.qty_available
    	FROM tb_prolocation AS pl
    	INNER JOIN tb_product AS p ON p.pro_id = pl.pro_id
    	WHERE pl.LocationId = ".$locationid. " AND pl.pro_id= ".$itemId." LIMIT 1";
    	$row=$db->fetchRow($sql);
    	return $row;
    }
    final public function productInvetoryLocation($locationid, $itemId){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    			p.pro_id,
    			pl.ProLocationID, 
    			pl.`qty_onorder`,
    			pl.qty_onsold as prol_qty_onsold ,
    			pl.qty,
    			pl.qty_avaliable,
    			p.qty_onsold,
    			p.qty_onorder as pro_qty_onorder, 
    			p.qty_onhand,
    			p.qty_available
    			
    	FROM tb_prolocation AS pl
    	INNER JOIN tb_product AS p ON p.pro_id = pl.pro_id
    	WHERE pl.LocationId = ".$locationid. " AND pl.pro_id= ".$itemId." LIMIT 1";
    	
//     	$sql="SELECT pl.ProLocationID, pl.`qty_onorder` ,pl.qty, p.qty_onhand,p.qty_available
//     	FROM tb_prolocation AS pl
//     	INNER JOIN tb_product AS p ON p.pro_id = pl.pro_id
//     	WHERE pl.LocationId = ".$locationid. " AND pl.pro_id= ".$itemId." LIMIT 1";
    	$row=$db->fetchRow($sql);
    	return $row;
    }
    
    
    
    /**
     * insert record to table $tbl_name
     * @param array $data
     * @param string $tbl_name
     */
    public function addRecord($data,$tbl_name){
    	$this->setName($tbl_name);
    	return $this->insert($data);
    }
    
    
    /**
     * update record to table $tbl_name
     * @param array $data
     * @param int $id
     * @param string $tbl_name
     */
	public function updateRecord($data,$id,$updateby,$tbl_name){
		$tb = $this->setName($tbl_name);
		$where=$this->getAdapter()->quoteInto($updateby.'=?',$id);
		$rs = $this->update($data,$where);
		//echo $rs;//exit();
		
	}
    
    public function DeleteRecord($tbl_name,$id){
    	$db = $this->getAdapter();
		$sql = "UPDATE ".$tbl_name." SET status=0 WHERE id=".$id;
		return $db->query($sql);
    }

    public function deleteRecords($sql){
    	$db = $this->getAdapter();
		return $db->query($sql);
    } 

     public function DeleteData($tbl_name,$where){
    	$db = $this->getAdapter();
		$sql = "DELETE FROM ".$tbl_name.$where;
		return $db->query($sql);
    } 
    
    public function convertStringToDate($date, $format = "Y-m-d H:i:s")
    {
    	if(empty($date)) return NULL;
    	$time = strtotime($date);
    	return date($format, $time);
    }
    /* @Desc: add or sub qty of item depend on item and stock
     * @param $stockID stock id
     * @param $itemQtys array of item id and item qty
     * @param $sign: + | -
     * */
    public function query($sql){
    	$db = $this->getAdapter();
    	return $db->query($sql);	
    }
    public function fetchArray($result){
    	$db = $this->getAdapter();
    	return mysql_fetch_assoc($result);
    }
    public function getUserInfo(){
    	$session_user=new Zend_Session_Namespace('auth');
    	$userName=$session_user->user_name;
    	$GetUserId= $session_user->user_id;
    	$level = $session_user->level;
    	$location_id = $session_user->location_id;
    	$info = array("user_name"=>$userName,"user_id"=>$GetUserId,"level"=>$level,"branch_id"=>$location_id);
    	return $info;
    }
    
    public function getSetting(){
    	$DB = $this->getAdapter();
    	$sql="SELECT * FROM `tb_setting` ";
    	RETURN $DB->fetchAll($sql);
    }
    public static function GlobalgetUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
	
    public static function writeMessageErr($err=null)
    {
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$action=$request->getActionName();
    	$controller=$request->getControllerName();
    	$module=$request->getModuleName();
    
    	$session = new Zend_Session_Namespace('auth');
    	$user_name = $session->user_name;
    
    	$file = "../logs/error.log";
    	if (!file_exists($file)) touch($file);
    	$Handle = fopen($file, 'a');
    	$stringData = "[".date("Y-m-d H:i:s")."]"." [user]:".$user_name." [module]:".$module." [controller]:".$controller. " [action]:".$action." [Error]:".$err. "\n";
    	fwrite($Handle, $stringData);
    	fclose($Handle);
    
    }
    
    public function getMeasureById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT `qty_perunit` FROM tb_product WHERE pro_id= '$id' LIMIT 1 ";
    }
    function getPrefixCode($branch_id){
    	$db  = $this->getAdapter();
    	$sql = " SELECT prefix FROM `tb_sublocation` WHERE id = $branch_id  LIMIT 1";
    	return $db->fetchOne($sql);
    }
   	function getAllCustomer($opt=null){
   		$db=$this->getAdapter();
   		$sql=" SELECT id, CONCAT(cust_name,',',contact_name) AS cust_name,cu_code FROM tb_customer WHERE 
   		 status=1 AND (cust_name!='' OR contact_name!='' OR cu_code!='') ORDER BY id DESC ";
   		
   		$row =  $db->fetchAll($sql);
   		if($opt==null){
   			return $row;
   		}else{
   			//$options=array(0=>"Select Customer",-1=>"Add New Customer");
   			$options=array(0=>"Select Customer",);
   			if(!empty($row)) foreach($row as $read) $options[$read['id']]=str_replace("-","",$read['cust_name']).'-'.$read['cu_code'];
   			return $options;
   		}
   }
   	function getAllProvince($opt=null){
   		$db=$this->getAdapter();
   		$sql=" SELECT province_id,province_en_name FROM ln_province WHERE province_en_name!='' ";
   		
   		$row =  $db->fetchAll($sql);
   		if($opt==null){
   			return $row;
   		}else{
   			$options=array();
   			if(!empty($row)) foreach($row as $read) $options[$read['province_id']]=str_replace("-","",$read['province_en_name']);
   			return $options;
   		}
   }
   
   function getAllCurrency($opt=null){
   	$db=$this->getAdapter();
   	$sql=" SELECT id, description,symbal FROM tb_currency WHERE status = 1 ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array();
   		if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['description'].$read['symbal'];
   		return $options;
   	}
   }
   function getAllVendor($opt=null){
   	$db=$this->getAdapter();
   	$sql=" SELECT vendor_id As id,vendor_id,v_name as name, v_name FROM tb_vendor WHERE v_name!='' AND status = 1 ORDER BY vendor_id DESC";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array(0=>"Select Vendor",-1=>"Add Vendor");
   		if(!empty($row)) foreach($row as $read) $options[$read['vendor_id']]=$read['v_name'];
   		return $options;
   	}
   }
   function getAllPaymentmethod($opt=null){
   	$db=$this->getAdapter();
   	$sql=" SELECT * FROM tb_paymentmethod ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array();
   		if(!empty($row)) foreach($row as $read) $options[$read['payment_typeId']]=$read['payment_name'];
   		return $options;
   	}
   }
    function getAllExpense($opt=null){
   	$db=$this->getAdapter();
   	$sql=" SELECT * FROM tb_expensetitle where status=1 and title!='' ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array(0=>"Select Expense",-1=>"Add New Expense Title");
   		if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['title'];
   		return $options;
   	}
   }
   function getAllExpensePu($opt=null){
   	$db=$this->getAdapter();
   	$sql=" SELECT * FROM tb_expensetitle where status=1 and title!='' ";
   	$row =  $db->fetchAll($sql);
   	if($opt==null){
   		return $row;
   	}else{
   		$options=array(0=>"Select Expense",-1=>"Add New Expense Title");
   		if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['title'];
   		return $options;
   	}
   }
   
	
	function getSaleAgent($option=null){
    	$db = $this->getAdapter();
    	$sql=' SELECT id ,name FROM `tb_sale_agent` WHERE name!="" AND status=1  ';
    	$result = $this->getUserInfo();
    	$rows = $db->fetchAll($sql);
    	if($option!=null){
    	  $opt=array();  
    	  if(!empty($rows)) {
    		foreach($rows as $rs){
    			$opt[$rs['id']]=$rs['name'];
    		}
    	  }
    	  return $opt;
    	}else{
    		return $rows;
    	}
    }
   	function resizeImase($image,$part,$new_imagename=null){
		$photo = $image;
		$temp = explode(".", $photo["name"]);
		$new_name = $temp[0].end($temp);
		if (!empty($new_imagename)){
			$new_name = $new_imagename;
		}
		move_uploaded_file($image["tmp_name"], $part .$new_name);
			
		$uploadimage=$part.$new_name;
// 		$newname=$image["name"];
// 		// Load the stamp and the photo to apply the watermark to
		if (end($temp) == 'jpg') {
			$im = imagecreatefromjpeg($uploadimage);
		} else
			if (end($temp) == 'jpeg') {
			$im = imagecreatefromjpeg($uploadimage);
		} else
			if (end($temp) == 'png') {
			$im = imagecreatefrompng($uploadimage);
		} else
			if (end($temp) == 'gif') {
			$im = imagecreatefromgif($uploadimage);
		}
	
		if ($image['size']>(1000000*5)){
			// Save the image to file and free memory quality 50%
			imagejpeg($im, $uploadimage, 50);
		}else if($image['size']>(1000000)){
			imagejpeg($im, $uploadimage, 70); //quality 80%
		}else if($image['size']>512000){
			// Save the image to file and free memory quality 60%
			imagejpeg($im, $uploadimage, 80);
		}
		return $new_name;
			
	}
	function getAllDiePeople(){
		$sql="SELECT 
					id,
					dead_name,
					dead_name_chinese,
					dead_dob ,
					membersone,
					memberstwo,
					create_date
				FROM 
					`tb_program` 
				WHERE 
					status=1 
					AND dead_name!='' 
				ORDER BY 
					id DESC
			";
		return $this->getAdapter()->fetchAll($sql);;
	}
	
	
	public function getInvoiceNumber($branch_id = 1){
		$db = $this->getAdapter();
		
		$sql=" SELECT COUNT(id)  FROM tb_sales_order WHERE branch_id=".$branch_id." LIMIT 1 ";
		$sale_order = $db->fetchOne($sql);
		 
		$sql1=" SELECT COUNT(id)  FROM tb_donors WHERE branch_id=".$branch_id." LIMIT 1 ";
		$donor = $db->fetchOne($sql1);
		
		$sql2=" SELECT COUNT(id)  FROM tb_mong WHERE branch_id=".$branch_id." LIMIT 1 ";
		$mong = $db->fetchOne($sql2);
		 
		$pre="IV";
		 
		$new_invoice_no= (int)$sale_order + (int)$donor + (int)$mong + 1;
		 
		$lenght= strlen($new_invoice_no);
		for($i = $lenght;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_invoice_no;
	}
	
	public function getMongExpenseInvoice($branch_id = 1){
		$db = $this->getAdapter();
		
		$sql=" SELECT COUNT(id)  FROM tb_mong_expense WHERE branch_id=".$branch_id." LIMIT 1 ";
		$mong_expense = $db->fetchOne($sql);
		 
		$pre="Exp";
		 
		$new_invoice_no= (int)$mong_expense + 1;
		 
		$lenght= strlen($new_invoice_no);
		for($i = $lenght;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_invoice_no;
	}
	
	public function getReceiptNumber($branch_id = 1){
		$db = $this->getAdapter();
		$sql=" SELECT COUNT(id) FROM tb_receipt WHERE branch_id=".$branch_id." LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
	
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		
		$pre = "R";
		
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	
	public function getExpenseReceiptNumber($branch_id = 1){
		$db = $this->getAdapter();
		$sql=" SELECT COUNT(id) FROM tb_expense WHERE branch_id=".$branch_id." LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "E";
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	
	public function getPurchaseNumber($branch_id = 1){
		$db = $this->getAdapter();
		$sql=" SELECT COUNT(id) FROM tb_purchase_order WHERE branch_id=".$branch_id." LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "P";
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	public function getAdjustCode(){
		$db = $this->getAdapter();
		$branch = $this->getAccessPermission("id");
		$sql=" SELECT COUNT(id) FROM tb_product_adjust WHERE 1 $branch LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "Adj-";
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	public function getQuoteNumber($branch_id=1){
		$db = $this->getAdapter();
// 		$branch = $this->getAccessPermission("id");
// 		$branch
		$sql=" SELECT COUNT(id) FROM tb_quotation WHERE 1 ";
		$sql.="  AND branch_id=$branch_id ";
		$sql.="  LIMIT 1  ";
		
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "Q-";
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	public function getDnNumber($branch_id=1){
		$db = $this->getAdapter();
		$branch = $this->getAccessPermission("id");
		// 		$branch LIMIT 1
		$sql=" SELECT COUNT(id) FROM tb_dn WHERE 1 ";
		$sql.="  AND branch_id=$branch_id ";
		$sql.="  LIMIT 1  ";
		
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "DN-";
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	
	function getExchangeRate(){
		$db = $this->getAdapter();
		$sql="SELECT reil from tb_exchange_rate WHERE active=1 ORDER BY id ASC LIMIT 1";
		return $db->fetchOne($sql);
	}
	
	function getExchangeRateSell(){
		$db = $this->getAdapter();
		$sql="SELECT reil from tb_exchange_rate WHERE active=1 ORDER BY id DESC LIMIT 1";
		return $db->fetchOne($sql);
	}
	
	function getAllBranch(){
		$db=$this->getAdapter();
		$branch = $this->getAccessPermission("id");
		$sql=" SELECT id,`name` FROM `tb_sublocation` WHERE `name`!='' AND status=1 $branch ";
		return $db->fetchAll($sql);
	}
	
	function getAllLocation(){
		$db=$this->getAdapter();
		$branch = $this->getAccessPermission("id");
		$sql=" SELECT id,`name` FROM `tb_sublocation` WHERE `name`!='' AND status=1 $branch ";
		return $db->fetchAll($sql);
	}
	
	public function getAccessPermission($branch_str='branch_id'){
		$session_user=new Zend_Session_Namespace('auth');
		$branch_id = $session_user->branch_id;
		if(!empty($branch_id)){
			$level = $session_user->level;
			if($level==1 || $level==10 || $level==5){// 1=admin , 10=finan manager ,5=sale manager
				$result = "";
				return $result;
			}
			else{
				$result = " AND $branch_str =".$branch_id;
				return $result;
			}
		}
	}
	
	
	function getAllProduct(){
		$db = $this->getAdapter();
		$sql = "SELECT
					p.`id`,
					p.`item_name`,
					p.`item_name` as name,
					p.`item_code`,
					p.`item_code` as code,
					(SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id` limit 1) AS category
				FROM
					`tb_product` AS p,
					`tb_prolocation` AS pl
				WHERE
					p.`id` = pl.`pro_id`
					AND p.status = 1
					and p.is_service = 0
					and p.is_package = 0
			";
		return $db->fetchAll($sql);
	}
	function getNumberInkhmer($number){
		$khmernumber = array("០","១","២","៣","៤","៥","៦","៧","៨","៩");
		$spp = str_split($number);
		$num="";
		foreach ($spp as $ss){
			if (!empty($khmernumber[$ss])){
				$num.=$khmernumber[$ss];
			}else{
				$num.=$ss;
			}
		}
		return $num;
	}
	function getAllAmountSale($search){
		$db = $this->getAdapter();
		
		$from_date =(empty($search['start_date']))? '1': " s.date_sold >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_sold <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		$sql = "select count(id) from tb_sales_order as s where status=1 $where ";
		$sql.=$this->getAccessPermission();
		if(!empty($search['branch'])){
			$sql.= " AND branch_id = ".$search['branch'];
		}
		
		return $db->fetchOne($sql);
	}
	function getAllAmountMong($search){
		$db = $this->getAdapter();
		
		$from_date =(empty($search['start_date']))? '1': " sale_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " sale_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		$sql = "select count(id) from tb_mong where status=1 $where";
		$sql.=$this->getAccessPermission();
		if(!empty($search['branch'])){
			$sql.= " AND branch_id = ".$search['branch'];
		}
		
		return $db->fetchOne($sql);
	}
	function getAllAmountProgram($search){
		$db = $this->getAdapter();
		
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
		
		$sql = "select count(id) from tb_program where status=1 $where ";
		$sql.=$this->getAccessPermission();
		if(!empty($search['branch'])){
			$sql.= " AND branch_id = ".$search['branch'];
		}
		
		return $db->fetchOne($sql);
	}
	function getAllAmountDonor($search){
		$db = $this->getAdapter();
		
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		$sql = "select count(id) from tb_donors where status=1 $where ";
		$sql.=$this->getAccessPermission();
		if(!empty($search['branch'])){
			$sql.= " AND branch_id = ".$search['branch'];
		}
		
		return $db->fetchOne($sql);
	}
	
	function getAllProductType(){
		$db = $this->getAdapter();
		$sql = "select id,name from tb_category where status=1";
		return $db->fetchAll($sql);
	}
	function getDataByProType($pro_type,$start_date,$end_date){
		$db = $this->getAdapter();
		
		$from_date =(empty($start_date))? '1': " s.date_sold >= '".date("Y-m-d",strtotime($start_date))." 00:00:00'";
		$to_date = (empty($end_date))? '1': " s.date_sold <= '".date("Y-m-d",strtotime($end_date))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		$sql = " SELECT 
					  SUM(soi.qty_order)
					FROM
					  tb_sales_order as s,
					  `tb_salesorder_item` AS soi,
					  `tb_product` AS p 
					WHERE 
					  s.id = soi.saleorder_id
					  and p.id = soi.`pro_id` 
					  and p.is_package = 0
					  AND p.`cate_id` = $pro_type
					  $where
					ORDER BY 
						p.id ";
		$sale_qty = $db->fetchOne($sql);
		
		$from_date1 =(empty($start_date))? '1': " m.sale_date >= '".date("Y-m-d",strtotime($start_date))." 00:00:00'";
		$to_date1 = (empty($end_date))? '1': " m.sale_date <= '".date("Y-m-d",strtotime($end_date))." 23:59:59'";
		$where1 = " AND ".$from_date1." AND ".$to_date1;
		
		$sql1 = " SELECT 
					  SUM(msi.qty_order)
					FROM
					  tb_mong as m,
					  `tb_mong_sale_item` AS msi,
					  `tb_product` AS p 
					WHERE 
					  m.id = msi.mong_id
					  and p.id = msi.`pro_id` 
					  and p.is_package = 0
					  AND p.`cate_id` = $pro_type
					  $where1
					ORDER BY 
						p.id ";
		$mong_qty = $db->fetchOne($sql1);
		$total_qty = $sale_qty + $mong_qty;
		return $total_qty;
	}
}
?>