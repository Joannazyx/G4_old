<?php
/*
 * TradeMatch控制器，用来处理带匹配指令，将结果告知成功交易模型
 * @author zlq
 * @version 1.0
 */
class TradeMatch extends CI_Controller {
	private $pair = NULL;  // 该成交的买卖对
	private $m_cnt = 0;    // 匹配个数计数
	private $m_log = NULL;  // 记录
	
	// TODO::涨跌停限制昨日收盘价怎么获取？
	private $yesterday_end_price;
	private $latitude = 0.1;
	
	public function __construct() {
		date_default_timezone_set("Asia/Shanghai");
		parent::__construct();
		$this->load->database();
		$sql = 'select StockID, StartPrice
					from data_of_the_day
				';
		$cmd = $this->db->query($sql);
		$prices = $cmd->result_array();
		foreach ($prices as $item) {
			$this->yesterday_end_price[(string)$item['StockID']] = $item['StartPrice'];
		}
		//echo '</br> yesterday_end_price:   ' ;
		//var_export($this->yesterday_end_price);
		//echo '</br>';
/*
|   for test
| 
		$this->yesterday_end_price['S1'] = 100;
		$this->yesterday_end_price['S2'] = 100;
		$this->yesterday_end_price['S3'] = 100;
		$this->yesterday_end_price['S4'] = 100;
		$this->yesterday_end_price['S5'] = 100;
*/
	/*
	 *	for systematic test
	 *  version 1.1
	 */
	 $data['yest'] = $this->yesterday_end_price;
	 $string = $this->load->view('tradeview_yesterday', $data, true);
	 echo $string;
	 ob_flush();
	 flush();
	}
	
	/*
	 * 从数据库获取带匹配指令
	 * 匹配好后放到类成员$this->pair中
	 */
	protected function retrieveCmd() {
		// TODO:: 数据库组件呢?
		/* 
		|  取带匹配指令的两个原则
		|  首先CommissionPrice=max，
		|  然后按时间CommissionTime升序排列
		|  取第一个
		|  这里是价格优先和时间优先原则
		*/
		// 返回每只股票的买家和卖家
/*		OLD IMP
		$sql = 'drop view maxbuy;
				create view maxbuy as
					select max(CommissionPrice) as maxp, CommissionTime, StockID 
					from pending_buy_table
					group by StockID;
				drop view maxsell;
				create view minsell as
					(select min(CommissionPrice) as minp, StockID
					from pending_sell_table
					group by StockID);
				create view match as
					select buymap.maxp as buyprice, sellmap.minp as sellprice, buymap.CommissionTime as buytime, sellmap.CommissionTime as selltime, buymap.StockID) from (
						(select * from maxbuy 
							where CommissionTime = 
								(select min(CommissionTime) group by StockID)) as buymap
						inner join
						(select * from minsell
							where CommissionTime = 
								(select min(CommissionTime) group by StockID)) as sellmap
						on buymap.StockID = sellmap.StockID
						)
				select * from (pending_buy_table inner join match
						on (pending_buy_table.StockID = match.StockID AND pending_buy_table.CommissionPrice = match.buyprice AND pending_buy_table.CommissionTime = match.buytime))
				'; 
				
//		$sql = 'select * from pending_buy_table
//					where CommissionPrice=(select max(CommissionPrice) from pending_buy_table group by StockID)
//				group by StockID
//				order by CommissionTime asc';
*/
		$t1 = 'drop view if exists maxbuy';
		$t2 = 'drop view if exists minsell';
		$t3 = 'create view maxbuy as
					select CommissionPrice as maxp, CommissionTime, StockID 
					from pending_buy_table as A 
					where not exists (
						select * from pending_buy_table 
						where CommissionPrice > A.CommissionPrice
							AND StockID = A.StockID
							AND Suspend = false
					) AND Suspend = false';
		$t4 = 'create view minsell as
					select CommissionPrice as minp, CommissionTime, StockID
					from pending_sell_table as A
					where not exists (
						select * from pending_sell_table
						where CommissionPrice < A.CommissionPrice
							AND StockID = A.StockID
							AND Suspend = false
					) AND Suspend = false';
		$tmp1 = 'drop view if exists buymap';
		$tmp2 = 'drop view if exists sellmap';
		$tmp3 = 'create view buymap as
					select maxp, min(CommissionTime) as CommissionTime, StockID from maxbuy 
					group by StockID'; 
		$tmp4 = 'create view sellmap as
					select minp, min(CommissionTime) as CommissionTime, StockID from minsell
					group by StockID';
		$t5 = 'drop view if exists matchs';
		$t6 = 'create view matchs as
			   select buymap.maxp as buyprice, sellmap.minp as sellprice, buymap.CommissionTime as buytime, sellmap.CommissionTime as selltime, buymap.StockID 
				from (buymap join sellmap on buymap.StockID = sellmap.StockID)';
		$v1 = 'select *
				from pending_buy_table join matchs
				on pending_buy_table.StockID = matchs.StockID AND pending_buy_table.CommissionPrice = matchs.buyprice AND pending_buy_table.CommissionTime = matchs.buytime
				where Suspend = false
				order by matchs.StockID asc
			  ';
		$v2 = 'select *
				from pending_sell_table join matchs
				on pending_sell_table.StockID = matchs.StockID AND pending_sell_table.CommissionPrice = matchs.sellprice AND pending_sell_table.CommissionTime = matchs.selltime
				where Suspend = false
				order by matchs.StockID asc
			  ';
		$this->db->query($t1);
		$this->db->query($t2);
		$this->db->query($t3);
		$this->db->query($t4);
		$this->db->query($tmp1);
		$this->db->query($tmp2);
		$this->db->query($tmp3);
		$this->db->query($tmp4);
		$this->db->query($t5);
		$this->db->query($t6);
		$Cmd = $this->db->query($v1);		// an db object array
		$list['buy'] = $Cmd->result_array();
		$Cmd = $this->db->query($v2);		// an db object array
		$list['sell'] = $Cmd->result_array();
		
	//	echo '</br>BUY\'s LIST:        ';
	//	var_export($list['buy']);
	//	echo '</br>';
	//	echo '</br>SELL\'s LIST:        ';
	//	var_export($list['sell']);
	//	echo '</br>';
//		$this->pair['buy'] = $buyCmd->row_array();    // drag the first one
//		$sellCmd = $this->db->query($sql);
//		$this->pair['sell'] = $sellCmd->row_array();
		// version 1.1
		if (empty($list['buy']) || empty($list['sell']))
			$list = NULL;
		$this->pair = $list;
	}
	
	/*
	 * 检查双方价格可否撮合
	 * 以及价格是否在涨跌停区间内
	 * @param $Sid 股票ID号
	 * @param $buyp 买方出价
	 * @param $sellp 卖方出价
	 * @return true 若检查通过返回
	 */
	protected function checkMatchness($Sid, $buyp, $sellp) {

		 $lat = $this->yesterday_end_price[(string)$Sid] * $this->latitude;
		 if ($buyp < $sellp)
		 	return false;
		if ($buyp < $this->yesterday_end_price[(string)$Sid] - $lat)
			return false;
		if ($sellp > $this->yesterday_end_price[(string)$Sid] + $lat)
			return false;
		return true;
	}

	/*
	 * 若可匹配，限制涨跌的过程
	 * @param $Sid 股票ID号
	 * @param $p 当前匹配价
	 * @return 限制后的价格
	 */
	protected function restrictRange($Sid, $p) {
		//echo $Sid;
		//echo ':';
		//echo $this->yesterday_end_price[$Sid];
		$lat = $this->yesterday_end_price[$Sid]*$this->latitude;
		if ($this->yesterday_end_price[$Sid] - $lat > $p)
			$p = $this->yesterday_end_price[$Sid] - $lat;
		if ($this->yesterday_end_price[$Sid] + $lat < $p)
			$p = $this->yesterday_end_price[$Sid] + $lat;
		return $p;
	}
	
	/*
	 * 结束部分,返回是否匹配成功
	 * 在之中通知交易成功组件
	 * @return 返回true如果在此次匹配过程中存在一条匹配成功且交易的指令
	 */
	protected function _exit() {
		$this->load->model('Centerupdate', 'up');
		//  给交易成功组件  如果pair非NULL
		for($iter=0, $size = sizeof($this->pair['debug']); $iter < $size; $iter++) {
			if ($this->pair['debug'][$iter]['OK']) {
				$this->m_cnt ++;
				$this->up->updateacc($this->pair['debug'][$iter]['sellzj'],
								 $this->pair['debug'][$iter]['buyzj'],
								 $this->pair['debug'][$iter]['sellzq'],
								 $this->pair['debug'][$iter]['buyzq'],
								 $this->m_cnt, 
								 (string)$this->pair['debug'][$iter]['StockID'],
								 date('Y-m-d H:i:s', time()),
								 $this->pair['debug'][$iter]['price'],
								 $this->pair['debug'][$iter]['amount'],
								 $this->pair['debug'][$iter]['currency'],
								 $this->pair['debug'][$iter]['seller'],
								 $this->pair['debug'][$iter]['buyer'],
								 $this->pair['debug'][$iter]['isall']>>1,
								 $this->pair['debug'][$iter]['isall']%2,
								 $this->pair['debug'][$iter]['sellprice'],
								 $this->pair['debug'][$iter]['buyprice'],
								 $this->pair['debug'][$iter]['selltime'],
								 $this->pair['debug'][$iter]['buytime'],
								 $this->pair['debug'][$iter]['sellremain'],
								 $this->pair['debug'][$iter]['buyremain']
								 );
			}
		}	
/*		$this->load->model('log_model', 'log');
		$this->log->data = $this->m_log;
		var_dump($this->m_log);
*/		//echo "mcnt::".$this->m_cnt;
		return (0 != $this->m_cnt);
	}
	
	/*
     * 控制器核心方法
	 * 对且只对每个股票做一次匹配
	 * @return 返回true如果在此次匹配过程中存在一条匹配成功且交易的指令
	 */
	public  function match() {
		$this->pair = NULL;
		$this->debug = NULL;

		/*
		|  Step1 价格优先
		|  Step2 时间优先
		*/
		$this->retrieveCmd();
		if (NULL == $this->pair) {
			$this->debug = '11买指令库或卖指令库为空!';
			return false;
		}
		for ($iterb=0, $iters=0, $iter=0,
		     $sizeb = sizeof($this->pair['buy']), $sizes = sizeof($this->pair['sell']);
			 ($iterb < $sizeb) && ($iters < $sizes);
			  $iter++, $iterb++, $iters++) {
			while ($this->pair['buy'][$iterb]['StockID'] != $this->pair['sell'][$iters]['StockID']) {
				while (($iterb < $sizeb) && 
					($this->pair['buy'][$iterb]['StockID'] < $this->pair['sell'][$iters]['StockID']))
					$iterb++;
				if ($iterb >= $sizeb) break;
				while (($iters < $sizes) &&
					($this->pair['sell'][$iters]['StockID'] < $this->pair['buy'][$iterb]['StockID']))
					$iters++;
				if ($iters >= $sizes) break;
			}
			if (($iterb >= $sizeb) || ($iters >= $sizes))
				break;
				
			$p1 = $this->pair['buy'][$iterb]['CommissionPrice'];
			$p2 = $this->pair['sell'][$iters]['CommissionPrice'];
			
			/*
			|  Step3 成交价协商
			*/
			if (! $this->checkMatchness($this->pair['buy'][$iterb]['StockID'], $p1, $p2)) {
				$this->pair['debug'][$iter]['debugInfo'] = '22买方最高出价 低于 卖方最低出价 或者和涨跌停不符，不成交';
				$this->pair['debug'][$iter]['OK'] = false;
				//sleep(10);
				continue;
			}
			else{
				//echo 'match!!!!';
				//sleep(10);
			}
	
			$price_avg = ($p1 + $p2)/2;
			//echo 'avg';
			//echo $price_avg;
			/*
			|  Step4 涨跌停限制, 交易量
			*/
			$price_res = $this->restrictRange($this->pair['buy'][$iterb]['StockID'], $price_avg);
			//echo 'res';
			//echo $price_res;
			$amount = min($this->pair['buy'][$iterb]['CommissionAmount'], $this->pair['sell'][$iters]['CommissionAmount']);
			$isAll['buy'] = ($this->pair['buy'][$iterb]['CommissionAmount'] == $amount);
			$isAll['sell'] = ($this->pair['sell'][$iters]['CommissionAmount'] == $amount);
		
/*			/
			|  Step5 证券资金账户确认
			/
			$this->load->model('Zq_model', 'zq');
			$this->load->model('Zj_model', 'zj');
			if (!($this->zj->IfZJTradeSuccess($this->pair['buy']['CommissionID'],
										  $this->pair['buy']['StockHolderID'],
										  $amount,
										  $this->pair['buy']['StockID'], 
										  $isAll['buy'])
			  &&
			  $this->zq->IfZQTradeSuccess($this->pair['sell']['CommissionID'], 
			                              $this->pair['sell']['StockHolderID'],
										  $amount, 
										  $this->pair['sell']['StockID'], 
										  $isAll['sell'])
			  )
		    )    $this->pair = NULL;
			*/
			/*
			|  Step5.5 制表
			*/
			$this->pair['debug'][$iter]['OK'] = true;
			$this->pair['debug'][$iter]['price'] = $price_res;
			$this->pair['debug'][$iter]['StockID'] = $this->pair['buy'][$iterb]['StockID'];
			$this->pair['debug'][$iter]['seller'] = $this->pair['sell'][$iters]['CommissionID'];
			$this->pair['debug'][$iter]['buyer'] = $this->pair['buy'][$iterb]['CommissionID'];
			$this->pair['debug'][$iter]['amount'] = $amount;
			$this->pair['debug'][$iter]['isall'] = ($isAll['buy']<<1) + $isAll['sell'];
			$this->pair['debug'][$iter]['sellzj'] = $this->pair['sell'][$iters]['StockAccountID'];
			$this->pair['debug'][$iter]['sellzq'] = $this->pair['sell'][$iters]['StockHolderID'];
			$this->pair['debug'][$iter]['buyzj'] = $this->pair['buy'][$iterb]['StockAccountID'];
			$this->pair['debug'][$iter]['buyzq'] = $this->pair['buy'][$iterb]['StockHolderID'];
			$this->pair['debug'][$iter]['currency'] = $this->pair['buy'][$iterb]['Currency'];
			$this->pair['debug'][$iter]['sellprice'] = $this->pair['sell'][$iters]['CommissionPrice'];
			$this->pair['debug'][$iter]['buyprice'] = $this->pair['buy'][$iterb]['CommissionPrice'];
			$this->pair['debug'][$iter]['selltime'] = $this->pair['sell'][$iters]['CommissionTime'];
			$this->pair['debug'][$iter]['buytime'] = $this->pair['buy'][$iterb]['CommissionTime'];
			$this->pair['debug'][$iter]['sellremain'] = $this->pair['sell'][$iters]['CommissionAmount'] - $amount;
			$this->pair['debug'][$iter]['buyremain'] = $this->pair['buy'][$iterb]['CommissionAmount'] - $amount;
			/*
			|  Step6 修改数据库
			|      ???计算单方价格
			*/
			if ($isAll['buy'])
				$sqlb = "delete from pending_buy_table where CommissionID = '";
			else
				$sqlb = "update pending_buy_table SET CommissionAmount = CommissionAmount - ".strval($amount).
						", CommissionState = 'PARTIALPROCESS' where CommissionID = '";
			$sqlb = $sqlb.$this->pair['buy'][$iterb]['CommissionID']."'";
			if ($isAll['sell'])
				$sqls = "delete from pending_sell_table where CommissionID = '";
			else
				$sqls = "update pending_sell_table SET CommissionAmount = CommissionAmount - ".strval($amount).
						", CommissionState = 'PARTIALPROCESS' where CommissionID = '";
			$sqls = $sqls.$this->pair['sell'][$iters]['CommissionID']."'";
			
			if (!$this->db->query($sqlb) || !$this->db->query($sqls)) {
				//  error handling
				throw new Exception('33Update 待处理指令数据库出错!!!');
			}
			
			/*
			 *  version 1.1 debug显示
			 */
			$data['time'] = date("Y-m-d h:i:s");
			$data['debug'] = $this->pair['debug'][$iter];
			$data['sell'] = $this->pair['sell'][$iters];
			$data['buy'] = $this->pair['buy'][$iterb];
			$string = $this->load->view("tradeview_matchresult", $data, true);
			echo $string;
			ob_flush();
			flush();
		}

		//echo '</br>OUTPUT:        ';
	//	var_export($this->pair['debug']);
		//echo '</br>';
		return $this->_exit();
	}
	
	/*
	 * 缺省调用方法
	 * 后台循环运行匹配match()方法，若匹配成功则持续匹配。若匹配失败即没有一条可以交易，则delay一段时间后继续
	 */
	public function index() {
//		$this->match();
//  TODO::一直运行？？？
//  TODO::flush输出可以动态观看？？？
		while (1) {
			while ($this->match()) {
			// can debug
			}
			sleep(3);
		}
	}
	
	/*
	 * 一天结束后，更新data_of_the_day(history);
	 * 注意可能在一天的match中都没有进入_exit(),此时还未加载Centerupdate模型，此处重新加载 
	 */
	public function endDay() {
		$this->load->model('Centerupdate', 'up');
		$this->up->trans_data_history();
	}

};
?>
