<?php
class PurchaseOrder
{
	public function PurchaseOrder()
	{
		import("@.Action.PurchaseOrder.PurchaseOrderAction");

		$this->methodTable = array(
						"getHeadStruct"		=>	array("access"=>"remote"),
						"getDetailStruct"		=>	array("access"=>"remote"),
						"orderHeadQuery"		=>	array("access"=>"remote"),
						"orderQuery"		=>	array("access"=>"remote"),
						"orderSave"		=>	array("access"=>"remote"),
						"orderDel"		=>	array("access"=>"remote"),
						"downloadFromMIS"		=>	array("access"=>"remote")
				);
	}

	//查询申请单头结构
	public function getHeadStruct()
	{
		$order = new PurchaseOrderAction();
		$result = $order->getHeadStruct();
		if(!$result)
		{
			throw new Exception("获取申请单列表结构出错!");
		}

		return $result ;
	}

	//查询申请单明细结构
	public function getDetailStruct()
	{
		$order = new PurchaseOrderAction();
		$result = $order->getDetailStruct();
		if(!$result)
		{
			throw new Exception("获取申请单明细结构出错");
		}

		return $result ;
	}

	//查询申请单头
	public function orderHeadQuery($condition='')
	{
		$order = new PurchaseOrderAction();
		$result = $order->orderHeadQuery($condition);
		if(!$result)
		{
			throw new Exception("查询申请单出错!");
		}

		return $result ;
	}

	//查询申请单结构+数据头+数据明细
	public function orderQuery($condition)
	{
		$order = new PurchaseOrderAction();
		$struct = $order->getDetailStruct();
		if(!$struct)
		{
			throw new Exception("获取申请单明细结构出错!");
		}

		$head = $order->orderHeadQuery($condition);
		if(!$head)
		{
			throw new Exception("获取申请单头出错!");
		}

		$detail = $order->orderDetailQuery($condition) ;
		if(!$detail)
		{
			throw new Exception("获取申请单明细出错!");
		}

		$result["struct"] = $struct ;
		$result["head"] = $head ;
		$result["detail"] = $detail ;

		return $result ;
	}

	//保存申请单
	public function orderSave($data)
	{
		$order = new PurchaseOrderAction();
		$result = $order->orderSave($data);

		return $result ;
	}

	//删除申请单
	public function orderDel($data)
	{
		$order = new PurchaseOrderAction();
		$result = $order->orderDel($data);

		return $result ;

	}

	//重MIS下载顶多那
	public function downloadFromMIS($orderNo)
	{
		$orde = new PurchaseOrderAction();
		$result = $orde->downloadFromMIS($orderNo);

		return $result ;
	}

}
?>