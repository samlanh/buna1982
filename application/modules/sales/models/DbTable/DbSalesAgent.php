<?php

class Sales_Model_DbTable_DbSalesAgent extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_sale_agent";
	function getAllSaleAgent($search){
		$start_date=$search["start_date"];
		$end_date=$search["end_date"];
		$sql = "SELECT 
				  sg.id,
				  l.name AS branch_name,
				  sg.name,
				  sg.phone,
				  sg.email,
				  sg.address,
				  (SELECT v.name_kh FROM tb_view as v WHERE v.key_code=sg.status AND v.type=5) AS status
				FROM
				  tb_sale_agent AS sg 
				  INNER JOIN tb_sublocation AS l 
					ON sg.branch_id = l.id 
				WHERE 1 
				  AND sg.name != ''
			";
		$order=" ORDER BY sg.id DESC ";
		
		$from_date =(empty($search['start_date']))? '1': " sg.date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " sg.date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " l.name LIKE '%{$s_search}%'";
			$s_where[] = " sg.name LIKE '%{$s_search}%'";
			$s_where[] = " sg.phone LIKE '%{$s_search}%'";
			$s_where[] = " sg.email LIKE '%{$s_search}%'";
			$s_where[] = " sg.address LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch_id']>0){
			$where .= " AND branch_id = ".$search['branch_id'];
		}
		if($search['status']!=-1){
			$where .= " AND sg.status = ".$search['status'];
		}
		$order=" ORDER BY id DESC ";
		$db =$this->getAdapter();
		return $db->fetchAll($sql.$where.$order);
	}
	public function getSaleAgentCode($id){
		$db = $this->getAdapter();
		$sql = "SELECT s.`prefix` FROM `tb_sublocation` AS s WHERE s.id=$id";
		$prefix = $db->fetchOne($sql);
	
		$sql=" SELECT id FROM $this->_name AS s WHERE s.`branch_id`=$id ORDER BY id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);

		//$pre = $prefix."EID";
		$pre = "EID";
		for($i = $acc_no;$i<4;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	public function addSalesAgent($data)
	{
		$session_user=new Zend_Session_Namespace('auth');
		$db =$this->getAdapter();
		$db->beginTransaction();
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		try{
			$datainfo=array(
					"name"		 			=>	$data['name'],
					"phone"      			=>	$data['phone'],
					"email"      			=>	$data['email'],
					"address"    			=>	$data['address'],
					"branch_id"   			=>	$data['branch_id'],
					"note"					=>	$data['description'],
					'user_id'				=>	$GetUserId,
					"date"					=>	date("Y-m-d"),
			);
			$this->_name="tb_sale_agent";
			$this->insert($datainfo);
			
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			$err = $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	
	public function editSalesAgent($data)
	{
		$session_user=new Zend_Session_Namespace('auth');
		$db =$this->getAdapter();
		$db->beginTransaction();
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		try{
			$datainfo=array(
					"name"		 	=>	$data['name'],
					"phone"      	=>	$data['phone'],
					"email"      	=>	$data['email'],
					"address"    	=>	$data['address'],
					"branch_id"   	=>	$data['branch_id'],
					"note"			=>	$data['description'],
					'user_id'		=>	$GetUserId,
					"date"			=>	date("Y-m-d"),
					"status"		=>	$data["status"],
			);
			$this->_name="tb_sale_agent";
			$where=$this->getAdapter()->quoteInto('id=?',$data['id']);
			$this->update($datainfo,$where);
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			$err = $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			echo $err; exit();
		}
	}
	
	public function uploadFile($part,$name){
		$adapter = new Zend_File_Transfer_Adapter_Http();
		$adapter->setDestination($part);
		$files = $adapter->getFileInfo();
		//
		foreach($files as $file => $fileInfo) {
			if ($adapter->isUploaded($file)) {
				if ($adapter->isValid($file)) {
					if ($adapter->receive($file)) {
						$info = $adapter->getFileInfo($file);
						$tmp  = $info[$file]['tmp_name'];
						// here $tmp is the location of the uploaded file on the server
						// var_dump($info); to see all the fields you can use
						print_r($tmp);
						$adapter->addFilter(new Zend_Filter_File_Rename( array('target' => $part.$name)));
						//$adapter->receive();
					}
				}
			}
		}
		
	}
	public function addNewAgent($data){
		$db = new Application_Model_DbTable_DbGlobal();
		$datainfo=array(
				"name"		 =>$data['agent_name'],
				"phone"      =>$data['phone'],
				"job_title"  =>$data['position'],
				"stock_id"   =>$data['location'],
				"description"=>$data['desc'],
		);
		$agent_id=$db->addRecord($datainfo,"tb_sale_agent");
		return $agent_id; 
	}
}