<?php
	class ClearData
	{
		/**
		 * 清理数据
		 *
		 * @param array $cleardata 要清理的类型
		 */
		public function clear($cleardata)
		{
			if(!is_array($cleardata)) return "数据清理失败";
			try
			{
				for($i=0;$i<sizeof($cleardata);$i++)
				{
					$datatype = $cleardata[$i];
					switch ($datatype)
					{
						case '采购申请':
							$dao = new PurchaseOrderHeadDao();
							$dao->deleteAll('');
							$dao = new PurchaseOrderDetailDao();
							$dao->deleteAll('');
							break ;
						case '客户资料':
							$dao = new ClientsDao();
							$dao->deleteAll('');
							break ;
						case '故障登记':
							$dao = new FaultLogsDao();
							$dao->deleteAll('');
							break ;
						case '定期检查':
							$dao = new CheckLogsDao();
							$dao->deleteAll('');
							break ;
						case '投诉登记':
							$dao = new ComplaintLogsDao();
							$dao->deleteAll('');
							break ;
						case '产品资料':
							$dao = new GoodsDao();
							$dao->deleteAll('');
							break ;
						case '厂商资料':
							$dao = new SupplyDao();
							$dao->deleteAll('');
							break ;
						case '库房资料':
							$dao = new StorageDao();
							$dao->deleteAll('');
							break ;
						case '库存资料':
							$dao = new StocksIndexDao();
							$result = $dao->findAll()->toResultSet();
							for($j=0;$j<sizeof($result);$j++)
							{
								$tablename = $result[$j]['tablename'];
								$dao->deleteAll('',$tablename);
							}
							
							$dao->deleteAll('','stc_currentstocks');				
							$dao->deleteAll('','stc_codeIndex');
							break ;
						case '条码规则':
							$dao = new CodeDictDao();
							$dao->deleteAll('');
							break ;
						case '开灯记录':
							$dao = new UnconfirmstationDao();
							$dao->deleteAll('');
							break;
					}
				}
			}
			catch (Exception $e)
			{
				throw new Exception($e);
			}
			
			return true ;
			
		}
	}
?>