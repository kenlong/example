<?php
class Billtype
{
	public function getBilltype($condition='',$field='*')
	{
		$dao = new BilltypeDao();
		$result = $dao->findAll($condition,'',$field)->toResultSet();

		return $result ;

	}
}
?>