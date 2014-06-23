<?php

class LogManager extends CI_Model{
	public function writelog($state,$type,$CommissionID)
	{
	    $filename = dirname(__FILE__) . "/../logs/" . date("YMD") . ".log";
	    echo $type."\n".$CommissionID;
	    if($type=='BUY'){
	    	$info = $this->db->get_where('pending_buy_table', array('CommissionID' => $CommissionID));
	    	$arr = array();
			foreach ($info -> result() as $row) {
				array_push($arr, $row);
			}
	    }
	    else if($type=='SELL'){
	    	$info = $this->db->get_where('pending_sell_table', array('CommissionID' => $CommissionID));
	    	$arr = array();
			foreach ($info -> result() as $row) {
				array_push($arr, $row);
			}

	    }
	    echo json_encode($arr);

		$logstr = 	"state: ".$state.
					"time: ".mktime().
					" info: ".json_encode($arr)."\n";

		$fp = fopen($filename, "a+");
		if (!$fp)
		{
			return "无法打开日志文件";
		}
		fwrite($fp, $logstr);
        fclose($fp);
        
        return true;
	}
}


?>