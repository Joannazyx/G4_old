<?php
	class TradeHistory extends CI_Model{
		public function __construct(){
			parent::__construct();
			$this -> load -> database();
		}
		
		public function GetDealid($dealid){
			$sql = "select * from deal_of_the_history where DealID = ?";
			$res = $this -> db -> query($sql,array($dealid));
			$arr = array();
			foreach ($res -> result() as $row) {
				array_push($arr, $row);
			}
			return $arr;
		}
		public function interval_search($start, $end, $stockid, $ot){
			$sql = "select * from deal_of_the_history where DealTime > ? and DealTime < ? and StockID = ? order by ".$ot;
			$res = $this -> db -> query($sql,array(date('Y-m-d H:i:s',$start),date('Y-m-d H:i:s',$end),$stockid));
			return $res;
		}
		public function GetintervalOpen($start, $end, $stockid){
			$res = $this -> interval_search($start, $end, $stockid,"DealTime");
			$row = $res -> first_row('array');
			return $row;
		}
		public function GetintervalClose($start, $end, $stockid){
			$res = $this -> interval_search($start, $end, $stockid,"DealTime");
			$row = $res -> last_row('array');
			return $row;
		}
		public function GetintervalHighest($start, $end, $stockid){
			$res = $this -> interval_search($start, $end, $stockid, "DealPrice");
			$row = $res -> last_row('array');
			return $row;
		}
		public function GetintervalLowest($start, $end, $stockid){
			$res = $this -> interval_search($start, $end, $stockid, "DealPrice");
			$row = $res -> first_row('array');
			return $row;
		}
		public function GetCurrentPrice($stockid){
			$sql = "select * from deal_of_the_history where StockID = ? order by DealTime";
			$res = $this -> db -> query($sql, array($stockid));
			$row = $res -> last_row('array');
			return $row;
		}
		public function SortBuyPrice($stockid, $order){
			$sql = "select * from deal_of_the_history where StockID = ? order by DealPrice";
			if($order == 1)
				$sql = $sql." desc";
			$price_arr = array();
			$res = $this -> db -> query($sql, array($stockid));
			foreach ($res -> result() as $row) {
				array_push($price_arr, $row);
			}
			return $price_arr;
		}
		public function GetPrice($stockid, $type, $order){
			$now = time();
			if($type == "month"){
				$now = $now - 30 * 3600 * 24;
			}
			else
				if($type == "day"){
					$now = $now - 3600 * 24;
				}
				else
					if($type == "week"){
						$now = $now - 7 * 3600 * 24;
					}
					else{
						echo "undefine type";
					}
			$sql = "select *  from deal_of_the_history where StockID = ? and DealTime < ? order by DealPrice";
			if($order == 1){
				$sql = $sql." desc";
			}
			$res = $this -> db -> query($sql, array($stockid, date('Y-m-d H:i:s',$now)));
			$row = $res -> first_row('array');
			return $row;
		}
		
	}
?>