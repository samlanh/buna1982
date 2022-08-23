<?php 
class Rsvacl_Model_DbTable_DbExchangeRate extends Zend_Db_Table_Abstract
{
	protected  $_name = "tb_exchange_rate";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	
	function submit($data){
		//print_r($data);exit();
		try {
			$arr = array(
					'reil'		=>	$data["reil1"],
					'user_id'	=>	$this->getUserId(),
					'create_date'=>	date("Y-m-d H:i:s"),
			);
			$where = " id = ".$data['id1'];
			$this->update($arr,$where);
			
			$arr1 = array(
					'reil'		=>	$data["reil2"],
					'user_id'	=>	$this->getUserId(),
					'create_date'=>	date("Y-m-d H:i:s"),
			);
			$where1 = " id = ".$data['id2'];
			$this->update($arr1,$where1);
		}catch (Exception $e){
			echo $e->getMessage();
		}
	}
		
	function getExchangeRate(){
		$db = $this->getAdapter();
		$sql="select * from tb_exchange_rate where active=1 order by id ASC ";
		return $db->fetchAll($sql);
	}
	
}
?>
