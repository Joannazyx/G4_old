<?php
	class  extends CI_Model{
		public function __construct(){
			parent::__construct();
			$this -> load -> database();
		}
		public function insert(){// contains buy sell

			// assume some interface by user account
			// IfZQTradeSuccess
			// IfZJTradeSuccess

		}	
		public function delete(){// if one is done

		}
		public function movetohistory(){// if one is done ,then move to done db.

		}

		public function match(){//匹配指令

		}
	}
?>