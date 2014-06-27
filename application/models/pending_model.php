<?php

class Pending_model extends CI_Model {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Funds_Account');
		$this->load->model('StockTrade');
		$this->load->database();
	}

	/*@author KHC @version 1.0
	 * @parameter $data:待插入数据
	 * @return 布尔值*/
	public function add_sell_pending($data)
	{
		$this->db->trans_start();
		$this->db->insert('pending_sell_table', $data); 
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE) {
			return FALSE;
		} else {
		///////////////
			//writelog
	    $filename = dirname(__FILE__) . "/../logs/" . date("YMD") . ".log";	    	
		$logstr = 	"Time: ".date("M-d-Y h:i:s",mktime()).
					" State: ".'PENDING'.					
					" Info: ".json_encode($data)."\n";

		$fp = fopen($filename, "a+");
		if (!$fp)
		{
			return "无法打开日志文件";
		}
		fwrite($fp, $logstr);
        fclose($fp);


		////////////////////
			return TRUE;
		}
	}
	
	/*@author KHC @version 1.0
	 * @parameter $data:待插入数据
	 * @return 布尔值*/
	public function add_buy_pending($data)
	{
		$this->db->trans_start();
		$this->db->insert('pending_buy_table', $data);
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE) {
			return FALSE;
		} else {
			///////////////
			//writelog
		$filename = dirname(__FILE__) . "/../logs/" . date("YMD") . ".log";	
		$logstr = 	"Time: ".date("M-d-Y h:i:s",mktime()).
					" State: ".'PENDING'.					
					" Info: ".json_encode($data)."\n";

		$fp = fopen($filename, "a+");
		if (!$fp)
		{
			return "无法打开日志文件";
		}
		fwrite($fp, $logstr);
        fclose($fp);


////////////////////
			return TRUE;
		}
	}


	/*@author KHC	@version 1.0	
	 * @parameter $type:买卖类型,$stockID:股票ID， $commission_amount:交易总量, $commission_price:交易价格, $commission_time:交易时间, $stockholderID:交易发起证券账户, $stockaccountID：交易发起资金账户, $suspend:股票是否挂起, $currency交易币种
	 * @return 正确插入待处理指令表执行返回commissionID订单号，否则返回错误信息*/
	/*
	*change 2014.06.17 by ZYX
	*remove input suspend, default value 0
	*/
	public function AddRecord($type, $stockID, $commission_amount, $commission_price,  $commission_time, $stockholderID, $stockaccountID,  $currency,$suspend=0) 
	{

		$commissionID = random_string('unique', 0);
		
		$data = array('CommissionID' => $commissionID,
					  'StockID' => $stockID,
					  'StockHolderID' => $stockholderID,
					  'StockAccountID' => $stockaccountID,
					  'CommissionPrice' => $commission_price,
					  'CommissionTime' => $commission_time,
					  'CommissionAmount' => $commission_amount,
					  'CommissionType' => '',
					  'CommissionState' => 'PENDING',
					  'Suspend' => $suspend,
					  'Currency' => $currency);

		if($type == 0) {
			//若为买入指令冻结资金账户
			$state = false;
			$freeze = $this->Funds_Account->central_freeze($commissionID, $stockaccountID, $currency, $commission_amount*$commission_price);
			//////			
			$data['CommissionType'] = 'BUY';

			if($freeze === true) {
				$this->StockTrade->stockTrade($commissionID,$stockholderID,$stockID,$commission_amount,1);
				$state = $this->add_buy_pending($data);
			}
		} else {
			//若为卖出指令冻结股票账户
			$state = false;
			$freeze = $this->StockTrade->stockTrade($commissionID,$stockholderID,$stockID,$commission_amount,0);
			/////////
			$data['CommissionType'] = 'SELL';
			if($freeze === true)
				//tongzhi
				$state = $this->add_sell_pending($data);
		}
		//writelog

		if($state == TRUE)
			return $commissionID;
		else 
			return 'Add record failed!';
	}


	/*@author KHC @version 1.0
	 * @parameter $commissionID:订单号
	 * @return 返回订单号对应的买卖操作*/
	public function get_type($commissionID)
	{
		$this->db->select('CommissionType');
		$query = $this->db->get_where('pending_buy_table', array('CommissionID' => $commissionID));
		if($query->num_rows() > 0)
			return 0;
		else {
			return 1;
		}
	}

	/*@author KHC @version 1.0
	 * @parameter $commissionID:待删除订单号
	 * @return 布尔值*/
	public function withdraw_buy_pending($commissionID)
	{
		$this->db->trans_start();
		$query = $this->db->get_where('pending_buy_table', array('CommissionID' => $commissionID));

		foreach ($query->result_array() as $row) {
			$this->db->insert('withdraw_request_table',$row);
		}
		$this->db->delete('pending_buy_table', array('CommissionID' => $commissionID)); 
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE) {
			return FALSE;
		} else {
///////////////
		//writelog
			$filename = dirname(__FILE__) . "/../logs/" . date("YMD") . ".log";	    	
		$logstr = 	"Time: ".date("M-d-Y h:i:s",mktime()).
					" State: ".'WITHDRAW'.					
					" Info: ".json_encode($query->result_array())."\n";

			$fp = fopen($filename, "a+");
			if (!$fp)
			{
				return "无法打开日志文件";
			}
			fwrite($fp, $logstr);
		        fclose($fp);
	
	
////////////////////			
			return TRUE;
		}
	}

	/*@author KHC @version 1.0
	 * @parameter $commissionID:待删除订单号
	 * @return 布尔值*/
	public function withdraw_sell_pending($commissionID)
	{
		$this->db->trans_start();
		$query = $this->db->get_where('pending_sell_table', array('CommissionID' => $commissionID));
		foreach ($query->result_array() as $row) {
			$this->db->insert('withdraw_request_table',$row);
		}
		$this->db->delete('pending_sell_table', array('CommissionID' => $commissionID)); 
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE) {
			return FALSE;
		} else {
///////////////
			//writelog
	    $filename = dirname(__FILE__) . "/../logs/" . date("YMD") . ".log";	    	
		$logstr = 	"Time: ".date("M-d-Y h:i:s",mktime()).
					" State: ".'WITHDRAW'.					
					" Info: ".json_encode($query->result_array())."\n";

		$fp = fopen($filename, "a+");
		if (!$fp)
		{
			return "无法打开日志文件";
		}
		fwrite($fp, $logstr);
        fclose($fp);


////////////////////	
			return TRUE;
		}
	}

	
	/*@author KHC	@version 1.0	
	 * @parameter $commissionID: 订单号
	 * @return 根据订单号删除订单，返回执行结果的布尔量*/
	public function DeleteRecord($commissionID)
	{
		$type = $this->get_type($commissionID);
		$state = false;
		if($type === 0) {
			//若为撤销买入指令解冻资金账户；
			$query = $this->db->get_where('pending_buy_table', array('CommissionID' => $commissionID));

			foreach ($query->result_array() as $row) {
				$unfreeze = $this->Funds_Account->central_unfreeze($commissionID);
				if($unfreeze === true) {  
					$this->StockTrade->stockTradeReverse($commissionID,$row['StockHolderID'],$row['StockID'],$row['CommissionAmount'],1);
					$state = $this->withdraw_buy_pending($commissionID);
				}
			}
		} else {
			//若为撤销卖出指令解冻股票账户；
			$query = $this->db->get_where('pending_sell_table', array('CommissionID' => $commissionID));
			foreach ($query->result_array() as $row) {
				$freeze = $this->StockTrade->stockTradeReverse($commissionID,$row['StockHolderID'],$row['StockID'],$row['CommissionAmount'],0);
				if($freeze == true) {
				//tongzhi
					$state = $this->withdraw_sell_pending($commissionID);
				}
			}
		}

		return $state;
	}



	/*@author KHC @version 1.0
	 * @parameter $stockID:待挂起股票号
	 * @return 布尔值*/
	public function suspendPending($stockID)
	{
		$this->db->trans_start();
		$data = array('Suspend' => 1);
		$this->db->where('StockID', $stockID);
		$this->db->update('pending_buy_table', $data);
		$this->db->where('StockID', $stockID);
		$this->db->update('pending_sell_table', $data);
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE) {
			return FALSE;
		} else {
///////////////
		//writelog
			$logstr = '';
			$query = $this->db->get_where('pending_sell_table', array('StockID' => $stockID));
			foreach($query->result_array() as $row) {
				$logstr = $logstr."Time: ".date("M-d-Y h:i:s",mktime()).
						" State: ".'SUSPEND'.					
						" Info: ".json_encode($row)."\n";
			}	
			$query = $this->db->get_where('pending_buy_table', array('StockID' => $stockID));
			foreach($query->result_array() as $row) {
				$logstr = $logstr."Time: ".date("M-d-Y h:i:s",mktime()).
						" State: ".'SUSPEND'.					
						" Info: ".json_encode($row)."\n";
			}	
			
			$filename = dirname(__FILE__) . "/../logs/" . date("YMD") . ".log";	    	
			
			$fp = fopen($filename, "a+");
			if (!$fp)
			{
				return "无法打开日志文件";
			}
			fwrite($fp, $logstr);
		        fclose($fp);
////////////////////			

			return TRUE;
		}
	}

	/*@author KHC @version 1.0
	 * @parameter $stockID:待不挂起股票号
	 * @return 布尔值*/
	public function unsuspendPending($stockID)
	{
		$this->db->trans_start();
		$data = array('Suspend' => FALSE);
		$this->db->where('StockID', $stockID);
		$this->db->update('pending_buy_table', $data);
		$this->db->where('StockID', $stockID);
		$this->db->update('pending_sell_table', $data);
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE) {
			return FALSE;
		} else {
///////////////
		//writelog
			$logstr = '';
			$query = $this->db->get_where('pending_sell_table', array('StockID' => $stockID));
			foreach($query->result_array() as $row) {
				$logstr = $logstr."Time: ".date("M-d-Y h:i:s",mktime()).
						" State: ".'UNSUSPEND'.					
						" Info: ".json_encode($row)."\n";
			}	
			$query = $this->db->get_where('pending_buy_table', array('StockID' => $stockID));
			foreach($query->result_array() as $row) {
				$logstr = $logstr."Time: ".date("M-d-Y h:i:s",mktime()).
						" State: ".'UNSUSPEND'.					
						" Info: ".json_encode($row)."\n";
			}	
			
			$filename = dirname(__FILE__) . "/../logs/" . date("YMD") . ".log";	    	
			
			$fp = fopen($filename, "a+");
			if (!$fp)
			{
				return "无法打开日志文件";
			}
			fwrite($fp, $logstr);
		        fclose($fp);
////////////////////		
			return TRUE;
		}
	}

	/*@author KHC @version 1.0
	 * @parameter 无
	 * @return 停盘操作结果布尔值*/
	public function shutdownPending()
	{
		$this->db->trans_start();
		$logstr = '';
		$query = $this->db->get('pending_buy_table');
		foreach ($query->result_array() as $row) {
			$this->Funds_Account->central_unfreeze($row['CommissionID']);
			$this->StockTrade->stockTradeReverse($row['CommissionID'],$row['StockHolderID'],$row['StockID'],$row['CommissionAmount'],1);	
			$this->db->insert('withdraw_request_table',$row);
			$logstr = $logstr."Time: ".date("M-d-Y h:i:s",mktime()).
					" State: ".'SHUTDOWN'.					
					" Info: ".json_encode($row)."\n";
		}
		$this->db->empty_table('pending_buy_table'); 
		$query = $this->db->get('pending_sell_table');
		foreach ($query->result_array() as $row) {
			$this->StockTrade->stockTradeReverse($row['CommissionID'],$row['StockHolderID'],$row['StockID'],$row['CommissionAmount'],0);
			//tongzhi
			$this->db->insert('withdraw_request_table',$row);
			$logstr =  $logstr."state: ".'SHUTDOWN'.
				          " time: ".mktime().
				          " info: ".json_encode($row)."\n";
		}
		$this->db->empty_table('pending_sell_table'); 
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE) {
			return FALSE;
		} else {
			$filename = dirname(__FILE__) . "/../logs/" . date("YMD") . ".log";	    	
			
			$fp = fopen($filename, "a+");
			if (!$fp)
			{
				return "无法打开日志文件";
			}
			fwrite($fp, $logstr);
		        fclose($fp);
			
			return TRUE;
		}
	}

	/*@author KHC @version 1.0
	 * @parameter $stockID:待查询股票ID, $order：升序还是降序
	 * @return 在待处理买指令列表对交易价格排序后的查询结果*/
	public function sortcommbuyprice($stockID, $order)
	{
		$sql = "select *  from pending_buy_table where StockID = ? order by CommissionPrice";
		if($order == 1){
			$sql = $sql." desc";
		}
		$res = $this -> db -> query($sql, array($stockID));
		return $res->result_array();
	}

	
	/*@author KHC @version 1.0
	 * @parameter $stockID:待查询股票ID, $order：升序还是降序
	 * @return 在待处理卖指令列表对交易价格排序后的查询结果*/
	public function sortcommsellprice($stockID, $order)
	{
		$sql = "select *  from pending_sell_table where StockID = ? order by CommissionPrice";
		if($order == 1){
			$sql = $sql." desc";
		}
		$res = $this -> db -> query($sql, array($stockID));
		return $res->result_array();
	}

	/*@author KHC @version 1.0
	 * @parameter $commissionID:待查找交易号
	 * @return 查询结果*/
	public function getRecord($commissionID)
	{
		if($this->get_type($commissionID) == 0)
			$database = 'pending_buy_table';
		else
			$database = 'pending_sell_table';
		
		$query = $this->db->get_where($database, array('CommissionID' => $commissionID));

		return $query->result_array();
	}
}

?>
