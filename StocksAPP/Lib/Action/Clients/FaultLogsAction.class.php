<?php
class FaultLogsAction
{
	/**
	 * 查询
	 *
	 * @param String $condition
	 * @return array
	 */
	public function query($condition='')
	{
		try
		{
			$dao = new FaultLogsDao();
			$result = $dao->findAll($condition)->toArray();
			return $result ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}


	/**
	 * 保存
	 *
	 * @param array $data
	 * @return boolean
	 */
	public function save($data)
	{
		try
		{
			$dao = new FaultLogsDao();

			if($data["id"]=='' || $data["id"]==null)
			{
				$vo = $dao->createVo('add','','id',0,$data);
				$result = $dao->add($vo);
			}
			else
			{
				$vo = $dao->createVo('modify','','id',$data["id"],$data);
				$result = $dao->save($vo);
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}


	/**
	 * 删除
	 *
	 * @param array $data
	 * @return boolean
	 */
	public function delete($data)
	{
		try
		{
			$dao = new FaultLogsDao();
			$id = $data["id"];

			$result = $dao->deleteById($id);

		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	
	/**
	 * 获取下拉框的值
	 */
	public function getList()
	{
		try {
			$dao = new FaultLogsDao();
			$result = $dao->findAll('','','reportUnit','','','reportUnit')->toResultSet();
			if($result)
			{
				$reportUnit = array();
				for($i=0;$i<sizeof($result);$i++)
				{
					array_push($reportUnit,$result[$i]['reportUnit']);
				}
			}
			
			$result = $dao->findAll('','','results','','','results')->toResultSet();
			if($result)
			{
				$results = array();
				for($i=0;$i<sizeof($result);$i++)
				{
					array_push($results,$result[$i]["results"]);
				}
			}
			
			$rtn["reportUnit"] = $reportUnit;
			$rtn["results"] = $results;
			
			return $rtn ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	

	/**
	 * 显示的结构
	 *
	 * @return array
	 */
	public function getStruct()
	{
		$vo = new FaultLogsVo(array());
		$item  = array_keys(get_object_vars($vo));
		$label = array(	"id"=>"id",
					 	"reportUnit"=>"填报单位",
					 	"fileNo"=>"报告编号",
					 	"clientNo"=>"故障单位编号",
					 	"clientName"=>"故障单位",
					 	"place"=>"计量点",
					 	"faultDate"=>"故障发现日期",
					 	"description"=>"故障现象",
					 	"discover"=>"发现经过",
					 	"handling"=>"处理故障记录",
					 	"reason"=>"技术分析",
					 	"duty"=>"责任分析",
					 	"improveMeasure"=>"改进措施",
					 	"results"=>"故障处理验证",
					 	"repoter"=>"填报人",
					 	"reportDate"=>"填报日期",
					 	"memo"=>"备注"
					);
		$columnitem = $vo;
		$enabled = array("_type_"=>"Not","data"=>array("id") );
		$visible = array("_type_"=>"Not","data"=>array("id") ) ;
		$align = array();
		$width = array("id"=>0);
		$inputtype = array();

		$struct["item"] = $item ;
		$struct["label"] = $label ;
		$struct["columnitem"] = $columnitem ;
		$struct["enabled"] = $enabled ;
		$struct["visible"] = $visible ;
		$struct["align"] = $align ;
		$struct["width"] = $width ;
		$struct["inputtype"] = $inputtype ;

		return $struct ;
	}

}
?>