<?php

class Centerupdate extends CI_Model
{
	public function __construct() 
	{
		parent::__construct();
		$this->load->model('Zj_model', 'Funds_Account');//ZJ 
		$this->load->model('Zq_model', 'Stock_Account');//ZQ
		$this->load->database();
	}


	/*
	*	指令成功匹配后操作，包括
	*		1.更新资金账户和证券账户(调用资金和证券账户的接口)
	*		2.写日志
	*	@param $seller_ZJ_id $buyer_ZJ_id 	买卖双方对应资金账户
	*	@param $seller_ZQ_id $buyer_ZQ_id 	买卖双方对应资金账户
	*	@param $deal_id						当前交易commision_id
	*	@param $stock_id					股票id
	*	@param $deal_time 					交易时间
	*	@param $deak_price  				交易股票价格
	*	@param $deal_amount 				交易股票数目
	*	@param $currency 					币种
	*	@param $seller $buyer 				买卖双方
	*	@param $seller_isall $buyer_isall   买卖双方是否全部交易
	*/
	public function updateacc(	$seller_ZJ_id, $buyer_ZJ_id,
								$seller_ZQ_id, $buyer_ZQ_id,
								$deal_id, $stock_id, 
								$deal_time, $deal_price, $deal_amount, $currency,
								$seller, $buyer, //comssion_id
								$seller_isall, $buyer_isall,
								$sell_price, $buy_price,
								$sell_time, $buy_time,
								$sell_remain, $buy_remain)
	{
		$err = true;

		//updata seller ZQ
		$err = $this->Stock_Account->IfZQTradeSuccess($seller_ZQ_id, $seller, $deal_amount, $stock_id, $seller_isall);
		if ($err != true)
		{
			$err = "卖方证券账户更新失败";
			return $err;
		}
		//update buyer ZQ
		$err = $this->Stock_Account->IfZQTradeSuccess($buyer_ZQ_id, $buyer, $deal_amount, $stock_id, $buyer_isall);
		if ($err != true)
		{
			// rollback 1
			$err = "买方证券账户更新失败";
			return $err;
		}
		//update seller ZJ
		$err = $this->Funds_Account->IfZJTradeSuccess($seller_ZJ_id, $deal_amount, $deal_price, $seller, $seller_isall);
		if ($err != true)
		{
			//rollback 1,2
			$err = "卖方资金账户更新失败";
			return $err;
		}
		//update buyer ZJ
		$err = $this->Funds_Account->IfZJTradeSuccess($buyer_ZJ_id, $deal_amount, $deal_price, $buyer, $buyer_isall);
		if ($err != true)
		{
			// rollback 1, 2, 3
			$err = "买方资金账户更新失败";
			return $err;
		}
		//write log
		$err = $this->writedeal($deal_id, $stock_id, $deal_time, $deal_price, $deal_amount, $currency, $seller, $buyer);
		if ($err != true)
		{
			//rollback 1, 2, 3, 4
			$err = "更新数据库失败";
			return $err;
		}

		$err = $this->writelog($stock_id, $seller, $buyer,
							   $seller_ZJ_id, $buyer_ZJ_id,
							   $seller_ZQ_id, $buyer_ZQ_id,
							   $sell_price, $buy_price,
							   $seller_isall, $buyer_isall,
							   $sell_time, $buy_time,
							   $sell_remain, $buy_remain,
							   $currency);
		if ($err != true)
		{
			//rollback 1, 2, 3, 4
			$err = "更新日志失败";
			return $err;
		}		
		return true;
	}

	/*
	*	写数据库，包括
	*		1.将当前交易加入到deal_of_the_day和deal_of_the_history
	*		2.更新data_of_the_day(HighestPrice, LowestPrice, DayAmount, EndPrice)
	*	@param $deal_id						当前交易commision_id
	*	@param $stock_id					股票id
	*	@param $deal_time 					交易时间
	*	@param $deak_price  				交易股票价格
	*	@param $deal_amount 				交易股票数目
	*	@param $currency 					币种
	*	@param $seller $buyer 				买卖双方
	*/
	public function writedeal($deal_id, $stock_id, $deal_time, $deal_price, $deal_amount, $currency, $seller, $buyer)
	{
		$this->db->trans_start();
		//deal_of_the_day
		
		$this->db->insert('deal_of_the_day', array(
							'DealID' 				=> 	$deal_id,
							'StockID'				=> 	$stock_id,
							'DealTime'				=> 	$deal_time,
							'DealPrice'				=> 	$deal_price,
							'DealAmount'			=> 	$deal_amount,
							'SellerCommissionID'	=>	$seller,
							'BuyerCommisonID'		=>	$buyer,
							'Currency'				=>	$currency
							));
		//deal_of_the_history
		$this->db->insert('deal_of_the_history', array(
							'DealID' 				=> 	$deal_id,
							'StockID'				=> 	$stock_id,
							'DealTime'				=> 	$deal_time,
							'DealPrice'				=> 	$deal_price,
							'DealAmount'			=> 	$deal_amount,
							'SellerCommissionID'	=>	$seller,
							'BuyerCommisonID'		=>	$buyer,
							'Currency'				=>	$currency
							));

		//update data_of_the_day;
		$result = $this->db->get_where('data_of_the_day', array(
											'StockID' => $stock_id
										));
		$result_arr = $result->result_array();
		$this->db->where('StockID', $stock_id);
		$this->db->update('data_of_the_day', array(
							'HighestPrice' 	=> max($deal_price, $result_arr[0]['HighestPrice']),
							'LowestPrice' 	=> min($deal_price, $result_arr[0]['LowestPrice']),
							'DayAmount'		=> ($result_arr[0]['DayAmount'] + $deal_amount),
							'EndPrice'      => $deal_price
							));

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
			return "更新记录数据库失败";
		}
		return true;
	}	


	/*
	*	写日志，将指令状态写到日志中
	*		
	*	@param $deal_id						当前交易生成交易号
	*	@param $stock_id					股票id
	*	@param $deal_time 					交易时间
	*	@param $deal_price  				交易股票价格
	*	@param $deal_amount 				交易股票数目
	*	@param $currency 					币种
	*   @param $sell_Commission_ID          卖方委托号
	*   @param $buy_Commission_ID           买方委托号
	*	@param $sell_ZJ_id $buy_ZJ_id 	    买卖双方对应资金账户
	*	@param $sell_ZQ_id $buy_ZQ_id 	    买卖双方对应资金账户
	*	@param $sell_isall $buy_isall       买卖双方是否全部交易
	*   @param $sell_price                  卖方委托价
	*   @param $buy_price				 	买方委托价
	*   @param $sell_time					卖方委托时间
	*   @param $buy_time					买方委托时间
	
	*/
	public function writelog($stock_id, 
							 $sell_Commission_ID, $buy_Commission_ID,
							 $sell_ZJ_id, $buy_ZJ_id,
							 $sell_ZQ_id, $buy_ZQ_id,
							 $sell_price, $buy_price,
							 $sell_isall, $buy_isall,
							 $sell_time, $buy_time,
							 $sell_remain, $buy_remain,
							 $currency)
	{
	    $filename = dirname(__FILE__) . "/../logs/" . date("YMD") . ".log";
		$logstr = 	"Time: " . date("M-d-Y h:i:s", mktime()) . 
					"  State: ". ($sell_isall? "COMPLETE":"PARTIALPROCESS").
					"  CommissionID: ". $sell_Commission_ID.
					"  StockID: ". $stock_id.
					"  StockHolderID: ". $sell_ZQ_id.
					"  StockAccountID: ". $sell_ZJ_id.
					"  CommissionPrice: ". $sell_price.
					"  CommissionTime: ". $sell_time.
					"  CommissionAmount: ". $sell_remain.
					"  CommissionType: SELL".
					"  State: ". ($sell_isall? "COMPLETE":"PARTIALPROCESS").
					"  Currency: ". $currency.
					"\n". 
					"Time: " . date("M-d-Y h:i:s", mktime()) . 
					"  State: ". ($buy_isall? "COMPLETE":"PARTIALPROCESS").
					"  CommissionID: ". $buy_Commission_ID.
					"  StockID: ". $stock_id.
					"  StockHolderID: ". $buy_ZQ_id.
					"  StockAccountID: ". $buy_ZJ_id.
					"  CommissionPrice: ". $buy_price.
					"  CommissionTime: ". $buy_time.
					"  CommissionAmount: ". $buy_remain.
					"  CommissionType: BUY".
					"  State: ". ($buy_isall? "COMPLETE":"PARTIALPROCESS").
					"  Currency: ". $currency.
				    "\n" ;
		$fp = fopen($filename, "a+");
		if (!$fp)
		{
			return "无法打开日志文件";
		}
		fwrite($fp, $logstr);
        fclose($fp);
        
        return true;
	}

	/*
	*	1.将当前日期的data_of_the_day迁移到data_of_the_history中
	*	2.设置第二天的开盘价(StartPrice)设置为今天的收盘价(EndPrice)
	*/
	public function trans_data_history()
	{
		$this->db->trans_start();
		$query = $this->db->query('select * from data_of_the_day');
		foreach ($query->result() as $row)
		{
			$this->db->insert('data_of_the_history', array(
								'StockID' 		=> $row->StockID,
								'StartPrice'	=> $row->StartPrice,
								'EndPrice'		=> $row->EndPrice,
								'HighestPrice'	=> $row->HighestPrice,
								'LowestPrice'	=> $row->LowestPrice,
								'DayAmount'		=> $row->DayAmount,
								'DayValue'		=> $row->DayValue
							));
			$this->db->where('StockID', $row->StockID);		
			$this->db->update('data_of_the_day', array(
							'StartPrice'		=> $row->EndPrice,
							'EndPrice'			=> 0,
							'HighestPrice'		=> 0,
							'LowestPrice'		=> 10000,
							'DayAmount'			=> 0,
							'DayValue'			=> mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"))
							));
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
			return "更新数据数据库失败";
		}
		return true;
	}



	/*

	data_of_the_day
	+--------------+------------+------+-----+---------+-------+
	| Field        | Type       | Null | Key | Default | Extra |
	+--------------+------------+------+-----+---------+-------+
	| StockID      | varchar(6) | NO   |     | NULL    |       |
	| StartPrice   | double     | NO   |     | NULL    |       |
	| EndPrice     | double     | NO   |     | NULL    |       |
	| HighestPrice | double     | NO   |     | NULL    |       |
	| LowestPrice  | double     | NO   |     | NULL    |       |
	| DayAmount    | bigint(20) | NO   |     | NULL    |       |
	| DayValue     | double     | NO   |     | NULL    |       |
	+--------------+------------+------+-----+---------+-------+

	*/

	/*
	写入账户 deal_of_the_day
	 Field              | Type        | Null | Key | Default           | Extra |
	--------------------+-------------+------+-----+-------------------+-------+
	 DealID             | varchar(20) | NO   |     | NULL              |       |
	 StockID            | varchar(6)  | NO   |     | NULL              |       |
	 DealTime           | timestamp   | NO   |     | CURRENT_TIMESTAMP |       |
	 DealPrice          | double      | NO   |     | NULL              |       |
	 DealAmount         | int(11)     | NO   |     | NULL              |       |
	 SellerCommissionID | varchar(20) | NO   |     | NULL              |       |
	 BuyerCommisonID    | varchar(20) | NO   |     | NULL              |       |
	 
	*/
}

?>
