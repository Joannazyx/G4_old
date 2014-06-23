<?php
// this model is to record the trade deal
	class TradeDeal extends CI_Model{
		public function __construct(){
			parent::__construct();
			$this -> load -> database();
		}
		public function insert(){//record the deal record so only insert
			
		}	

		public function GetCurrentPrice($stockId){
			
		}

		public function GetHighestBuyPrice($stockId){
			
		}

		public function GetLowerestSellPrice($stockId){

		}
		//public function...
		//...some other function 
		// just provide some results from the db.
	}
?>