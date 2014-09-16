<?php
class CheckLogsAction
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
			$dao = new CheckLogsDao();
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
			$dao = new CheckLogsDao();

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
			$dao = new CheckLogsDao();
			$id = $data["id"];

			$result = $dao->deleteById($id);

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
		$vo = new CheckLogsVo(array());
		$item  = array_keys(get_object_vars($vo));
		$label = array(	"id"=>"id" ,
						"clientNo"=>"客户编号"	,
						"checkDate"=>"检测日期",
						"fileNo"=>"档案编号"	,
						"state"=>"设备状态" ,
						"checkMan"=>"检定人"	,
						"memo"=>"备注"
					);
		$columnitem = $vo;
		$enabled = array("_type_"=>"Not","data"=>array("id") );
		$visible = array("_type_"=>"Not","data"=>array("id") ) ;
		$align = array();
		$width = array();
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