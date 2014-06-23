<?php
class Pressing extends CI_Controller{
		public function __construct(){
		parent::__construct();
		
		$this->load->model('pending_model','pm');
	}

}

?>