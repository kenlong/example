<?php
import("@.Action.ParameterSetup.OtherParamSetupAction");
class OtherParmSetup
{
	public function queryParmList($type,$parmname)
	{
		$action = new OtherParmSetupAction();
		$result =$action->queryParmList($type,$parmname);
		//system_out(print_r($result,true));
		return $result ;
	}
	
	
}
?>