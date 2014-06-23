<?php
class CenterSearch extends CI_Controller{
	/*
		matching url:http://localhost/se/index.php/CenterSearch/
	*/
	public function __construct(){
		parent::__construct();
		$this->load->model('TradeHistory','th');
		$this->load->model('Pending_Model','tc');
	}
	/*
		given commission id return the record.
	*/
	public function GetCommissionid($commitid){
		$res = $this->tc->GetCommissionid($commitid);
		echo json_encode($res);
	}	
	/*
		given dealid return the all deal record.
	*/
	public function GetDealid($dealid){
		$res = $this->th->GetDealid($dealid);
		echo json_encode($res);
	}
	/*
		given interval and stockid, return the open price record
	*/
	public function GetintervalOpen($start,$end,$stockId){
		$res = $this->th->GetintervalOpen($start, $end, $stockId);
		echo json_encode($res);
	}
	/*
		given interval and stockid, return the close price record
	*/
	public function GetintervalClose($start,$end,$stockId){
		$res = $this->th->GetintervalClose($start, $end, $stockId);
		echo json_encode($res);
	}
	/*
		given interval and stockid, return the highest price record
	*/
	public function GetintervalHighest($start,$end,$stockId){
		$res = $this->th->GetintervalHighest($start,$end,$stockId);
		echo json_encode($res);
	}
	/*
		given interval and stockid, return the lowest price record
	*/
	public function GetintervalLowest($start,$end,$stockId){
		$res = $this->th->GetintervalLowest($start, $end, $stockId);
		echo json_encode($res);
	}
	/*
		given stockid, return the current price record
	*/
	public function GetCurrentPrice($stockId){
		$res = $this->th->GetCurrentPrice($stockId);
		echo json_encode($res);
	}
	/*
		given stockid return the history record sort by price.	
	*/
	public function SortBuyPrice($stockid, $order){
		$res = $this->th->SortBuyPrice($stockid, $order);
		echo json_encode($res);
	}
	/*
		give the highest or lowest price of<br>
		last month or last week or today.
	*/
	public function GetPrice($stockid, $type, $order){
		$res = $this->th->GetPrice($stockid, $type, $order);
		echo json_encode($res);
	}

	public function SortCommSellPirce($stockid,$order){
		$res = $this->tc->sortcommsellprice($stockid,$order);
		echo json_encode($res);
	}

	public function SortCommBuyPirce($stockid,$order){
		$res = $this->tc->sortcommbuyprice($stockid,$order);
		echo json_encode($res);
	}
}
?>
