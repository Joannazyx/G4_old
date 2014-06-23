<?php

class Pending extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('pending_model');
		$this->load->helper('string');
		$this->load->model('LogManager');
	//	$this->load->helper('form');
	}

	public function database_clear($stockID=array(), $startPrice=array()){
		$this->db->truncate('data_of_the_day');
		$this->db->truncate('data_of_the_history');
		$this->db->truncate('deal_of_the_day');
		$this->db->truncate('deal_of_the_history');
		$this->db->truncate('pending_buy_table');
		$this->db->truncate('pending_sell_table');
		$this->db->truncate('withdraw_request_table');
		for($i=0;$i<count($stockID);$i++){
			$this->db->insert('data_of_the_day', array(
								'StockID'				=> 	$stockID[$i],
								'StartPrice'			=> 	$startPrice[$i],
								'EndPrice'				=> 	0,
								'HighestPrice'			=> 	0,
								'LowestPrice'			=>	400000,
								'DayAmount'				=>	0,
								'DayValue'				=>	0
								));
		}
		
			
	}
	public function test_1_2_6(){
		$stockID=array('11111111');
		$startPrice=array('60.0');
		$this->database_clear($stockID,$startPrice);
		$this->AddRecord(0,'11111111',40,60,date('Y-m-d H:i:s', mktime(14,38,59,6,3,2014)), '22222222','33333333','CNY');
		$this->AddRecord(1,'11111111',40,60,date('Y-m-d H:i:s', mktime(14,40,59,6,3,2014)), '44444444','55555555','CNY');
		$this->AddRecord(1,'11111111',40,60,date('Y-m-d H:i:s', mktime(14,42,59,6,3,2014)), '12345678','87654321','CNY');

	}

	public function index()
	{		
		

		//header("Location: http://localhost:8980/SE/index.php/TradeMatch/jump/"); 
		//$this->database_clear();
		//$this->AddRecord(0,'11111111',40,60,"2014-06-03 14:38:59",'22222222','33333333','USD');
		//$this->AddRecord(0,'11111111',5,70,"2014-06-03 14:40:40",'12345678','87654321','USD');
		//$this->AddRecord(1,'12345678',20,40,"2014-06-04 14:52:04",'22222222','33333333','USD');
		//$this->AddRecord(1,'12345678',60,40,"2014-06-04 14:56:04",'22222222','33333333','USD');
	}

	public function test_1_1_1(){
		$this->database_clear();
		$this->AddRecord(0,'11111111',40,60,"2014-06-03 14:38:59",'22222222','33333333','USD');
		$this->AddRecord(0,'11111111',5,70,"2014-06-03 14:40:40",'12345678','87654321','USD');

	}
	public function test_1_1_2(){
		$this->database_clear();
		$this->AddRecord(1,'12345678',20,40,"2014-06-04 14:52:04",'22222222','33333333','USD');
		$this->AddRecord(1,'12345678',60,40,"2014-06-04 14:56:04",'22222222','33333333','USD');

	}
		public function test_1_3_1()
	{
		$this->database_clear();
		$commissionID=$this->AddRecord(0,'11111111',40,60,"2014-06-04 14:52:04", '22222222', '33333333','USD');
		echo $commissionID;


	}
		public function test_1_3_2()
	{
		$this->database_clear();
		$commissionID=$this->AddRecord(1,'12345678',20,40,"2014-06-04 14:52:04",'22222222','33333333','USD');
		echo $commissionID;

	}
	//buy partial match then cancel
	public function test_1_3_3() 
	{

		
				$stockID=array('11111111');
				$startPrice=array('60.0');
				$this->database_clear($stockID,$startPrice);
				
				$commissionID=$this->AddRecord(0,'11111111',50,60,date('Y-m-d H:i:s', mktime(14,38,59,6,3,2014)), '22222222','33333333','CNY');
				$this->AddRecord(1,'11111111',40,60,date('Y-m-d H:i:s', mktime(14,40,59,6,3,2014)), '44444444','55555555','CNY');
				echo 'To be deleted '.$commissionID;
				//echo 'jumping to match';
				//sleep(10);
				//header("Location: http://localhost:8980/SE/index.php/TradeMatch/jump/".$commissionID); 

	}

	public function test_1_3_4()
	{

				$stockID=array('11111111');
				$startPrice=array('60.0');
				$this->database_clear($stockID,$startPrice);
				
				$commissionID=$this->AddRecord(1,'11111111',50,60,date('Y-m-d H:i:s', mktime(14,38,59,6,3,2014)), '22222222','33333333','CNY');
				$this->AddRecord(0,'11111111',40,60,date('Y-m-d H:i:s', mktime(14,40,59,6,3,2014)), '44444444','55555555','CNY');
				echo 'To be deleted '.$commissionID;
				//echo 'jumping to match';
				//sleep(10);
				//header("Location: http://localhost:8980/SE/index.php/TradeMatch/match"); 

	}

	public function test_1_3_5($process=0)
	{
		if($process=='shut'){
			$this->Shutdown();
		}
		else{
				$this->database_clear();
				$this->AddRecord(0,'11111111',50,60,"2014-06-03 14:38:59",'22222222','33333333','USD');
			}

	}
		public function test_1_3_6($process=0)
	{
		if($process=='shut'){
			$this->Shutdown();
		}
		else{
				$this->database_clear();
				$this->AddRecord(1,'11111111',50,60,"2014-06-03 14:38:59",'22222222','33333333','USD');
			}

	}
		public function test_1_3_7($process=0)
		{
		if($process=='shut'){
			$this->Shutdown();
		}
		else{
					$stockID=array('11111111');
					$startPrice=array('60.0');
					$this->database_clear($stockID,$startPrice);
				
				$this->AddRecord(0,'11111111',40,60,"2014-06-03 14:38:59",'22222222','33333333','USD');
				$this->AddRecord(1,'11111111',50,60,"2014-06-03 14:38:59",'22222222','33333333','USD');
				echo 'jumping to match';
				sleep(10);
				header("Location: http://localhost:8980/SE/index.php/TradeMatch/match"); 
			}

	}
	public function test_1_3_8($process=0)
	{
		if($process=='shut'){
			$this->Shutdown();
		}
		else{
					$stockID=array('11111111');
					$startPrice=array('60.0');
					$this->database_clear($stockID,$startPrice);
				
				$this->AddRecord(0,'11111111',50,60,"2014-06-03 14:38:59",'22222222','33333333','USD');
				$this->AddRecord(1,'11111111',40,60,"2014-06-03 14:38:59",'22222222','33333333','USD');
				echo 'jumping to match';
				sleep(10);
				header("Location: http://localhost:8980/SE/index.php/TradeMatch/match"); 
			}

	}
		public function test_1_3_9($process=0)
	{
		if($process=='unsuspend'){
				$this->unSuspendStock('1000');
				echo 'unsuspend stock 1000,jumping to match';
				sleep(10);
				header("Location: http://localhost:8980/SE/index.php/TradeMatch/match"); 
		}
		else{
					$stockID=array('1000','2000');
					$startPrice=array('60.0','60.0');
					$this->database_clear($stockID,$startPrice);

				$this->AddRecord(1,'1000',50,60,date('Y-m-d H:i:s', mktime(14,38,59,6,3,2014)),'22222222','33333333','USD');
				$this->AddRecord(0,'1000',40,60,date('Y-m-d H:i:s', mktime(14,38,59,6,3,2014)),'44444444','55555555','USD');
				$this->AddRecord(1,'2000',50,60,date('Y-m-d H:i:s', mktime(14,38,59,6,3,2014)),'22222222','33333333','USD');
				$this->AddRecord(0,'2000',40,60,date('Y-m-d H:i:s', mktime(14,38,59,6,3,2014)),'22222222','33333333','USD');
				$this->SuspendStock('1000');
				echo 'suspend stock 1000,jumping to match';
				sleep(10);
				header("Location: http://localhost:8980/SE/index.php/TradeMatch/match"); 
			}

	}
	
	public function testpressing(){
			$stockID=array('S1000','S2000','S0001','S0002');
			$startPrice=array('200.0','200.0','200','200');
			$this->database_clear($stockID,$startPrice);
		    $handle = fopen('application/controllers/testdata.txt', 'r');
		    $length=7;
		    $cm= array("111","111","111");
    while(!feof($handle)){
    	echo '<br>';
    	for($i = 0; $i < $length; $i++) {
    		$cm[$i]=fgets($handle, 1024);
    		$cm[$i] = trim($cm[$i]);   		
    	}
    	echo '</br>';

    	//$this->pd->AddRecord()
    	$this->AddRecord($cm[0],$cm[1],$cm[2],$cm[3],$cm[4],$cm[5],$cm[6],'USD');      
    }
    fclose($handle);
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
			$data['CommissionType'] = 'BUY';
			$state = $this->pending_model->add_buy_pending($data);
		} else {
			//若为卖出指令冻结股票账户
			$data['CommissionType'] = 'SELL';
			$state = $this->pending_model->add_sell_pending($data);
		}
		//writelog

		if($state == TRUE)
			return $commissionID;
		else 
			return 'Add record failed!';
	}

	/*@author KHC	@version 1.0	
	 * @parameter $commissionID: 订单号
	 * @return 根据订单号删除订单，返回执行结果的布尔量*/
	public function DeleteRecord($commissionID)
	{
		$type = $this->pending_model->get_type($commissionID);
		
		if($type == 0) {
			//若为撤销买入指令解冻资金账户；
			$state = $this->pending_model->withdraw_buy_pending($commissionID);
		} else {
			//若为撤销卖出指令解冻股票账户；
			$state = $this->pending_model->withdraw_sell_pending($commissionID);
		}

		return $state;
	}

	/*@author KHC	@version 1.0	
	 * @parameter $stockID: 股票ID
	 * @return 根据股票号挂起股票，返回执行结果的布尔量*/
	public function SuspendStock($stockID)
	{
		return $this->pending_model->suspendPending($stockID);
	}

	/*@author KHC	@version 1.0	
	 * @parameter $stockID: 股票ID
	 * @return 根据股票号不挂起股票，返回执行结果的布尔量*/
	public function unSuspendStock($stockID)
	{
		return $this->pending_model->unsuspendPending($stockID);	
	}

	/*@author KHC	@version 1.0	
	 * @parameter 无
	 * @return 股市停盘时调用，返回执行结果的布尔值*/
	public function Shutdown() {
		//全部撤销解冻
		return $this->pending_model->shutdownPending();	
	}		
}

?>
