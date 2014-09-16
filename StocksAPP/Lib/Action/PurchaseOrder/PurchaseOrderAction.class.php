<?php
class PurchaseOrderAction
{
	/**
	 * 查询申请单头
	 *
	 * @param $condition
	 * @return boolean
	 */
	public function orderHeadQuery($condition)
	{
		$order = new PurchaseOrderHeadDao();
		$result = $order->findAll($condition);
		if(!$result->isEmpty())
		{
			$result = $result->toResultSet();
		}

		return $result ;
	}

	/**
	 * 查询申请单明细
	 *
	 * @param  $condition
	 * @return
	 */
	public function orderDetailQuery($condition)
	{
		$order = new PurchaseOrderDetailDao();
		$result = $order->findAll($condition);
		if(!$result->isEmpty())
		{
			$result = $result->toResultSet();
		}

		return $result ;
	}

	/**
	 * 保存申请单
	 *
	 * @param $data
	 */
	public function orderSave($data)
	{
		import("@.Action.AutoNo.AutoNoAction");

		$orderno = '';
		$orderno = '';
		$orderhead = new PurchaseOrderHeadDao() ;
		$orderdetail = new PurchaseOrderDetailDao() ;
		$head = $data["head"] ;
		$detail = $data["detail"] ;

		try
		{
			$orderhead->startTrans();
			$orderdetail->startTrans();
			//head
			if($head["id"] == null)
			{
				$orderno = AutoNoAction::getAutoNo('orderno');
				$head["orderNo"] = $orderno ;
				$reuslt = $orderhead->add($head);
				if(!$reuslt)
				{
					throw new Exception("新增申请单头出错!");
					$orderhead->rollback() ;
				}
			}
			else
			{
				$orderno = $head["orderNo"];
				$reuslt = $orderhead->save($head);
				if(!$reuslt)
				{
					throw new Exception("保存申请单头出错!");
					$orderhead->rollback() ;
				}
			}

			//detail
			$orderdetail->startTrans();
			$result = $orderdetail->deleteAll("orderNo = '"  . $head["orderNo"] . "'");
			if(!$result)
			{
				throw new Exception("删除申请单明细出错!");
				$orderhead->rollback() ;
				$orderdetail->rollback() ;
			}

			foreach ($detail as $item)
			{
				$item["orderNo"] = $head["orderNo"];
				if(trim($item["goodsNo"]) == '' || $item["goodsNo"] == null)
				{
					continue ;
				}

				$vo = $orderdetail->createVo('add','','id',0,$item);
				$result = $orderdetail->add($vo);
				if(!$result)
				{
					throw new Exception("保存申请单明细出错!");
					$orderhead->rollback();
					$orderdetail->rollback();
				}
			}
		}
		catch (Exception $e)
		{
			throw new ExcelDateUtil($e);
			$orderdetail->rollback() ;
			$orderhead->rollback() ;
		}

		//commit;
		$orderhead->commit() ;
		$orderdetail->commit() ;

		return $orderno ;
	}


	/**
	 * 删除申请单
	 *
	 * @param $data
	 */
	public function orderDel($data)
	{
		$condition = "orderNo = '" . $data["orderNo"] . "'" ;
		$orderdetail = new PurchaseOrderDetailDao();
		$orderhead = new PurchaseOrderHeadDao();

		try
		{
			$orderdetail->startTrans();
			$orderhead->startTrans();

			//删除申请单明细
			//删除申请单明细
			$result = $orderdetail->deleteAll($condition);
			if(!$result)
			{
				throw new Exception("删除申请单明细出错!");
				$orderdetail->rollback() ;
			}

			//删除申请单头
			$result = $orderhead->deleteAll($condition);
			if(!$result)
			{
				throw new Exception("删除申请单头出错!");
				$orderhead->rollback() ;
			}
		}
		catch (Exception $e)
		{
			$orderdetail->rollback() ;
			$orderhead->rollback() ;
			throw new ExecBackEnd($e);
		}

		$orderhead->commit() ;
		$orderdetail->commit() ;

		return true ;
	}

	/**
	 * 从MIS下载申请单信息
	 *
	 */
	public function downloadFromMIS($orderno)
	{
		$head["orderNo"] = "00001212";
		$head["orderDate"] = date("2007-01-01");
		$head["orderer"] = "zhangken" ;
		$head["supplier"] = "supply" ;
		$head["needdate"] = date("2008-01-01");

		$detail[0]["goodsNo"] = "010101" ;
		$detail[0]["goodsName"] = "电流表" ;
		$detail[0]["spec"] = "三相" ;
		$detail[0]["qty"] = 300 ;

		$result["head"][0] = $head ;
		$result["detail"] = $detail ;

		return $result ;
	}


	/**
	 * 获取申请单头结构
	 *
	 * @return unknown
	 */
	public function getHeadStruct()
	{
		$vo = new PurchaseOrderHeadVo(array());
		$item  = array_keys(get_object_vars($vo));
		$label = array(	"id"=>"id" ,
						"orderNo"=>"申请单号" ,
						"orderDate"=>"申请日期" ,
						"orderer"=>"申请人" ,
						"supplier"=>"生产厂商" ,
						"needdate"=>"需货日期" ,
						"checkState"=>"批复状态" 	,
						"checkBudget"=>"批复预算"	 ,
						"checker"=>"批复人"	,
						"operator"=>"操作员" ,
						"updated"=>"制单日期"
					);
		$columnitem = $vo;
		$enabled = array("_type_"=>"Not","data"=>array("id") );
		$visible = array("_type_"=>"Not","data"=>array("id") ) ;
		$align = array("orderNo"=>"center");
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


	/**
	 *获取申请单明细结构
	 *
	 * @return unknown
	 */
	public function getDetailStruct()
	{
		$vo = new PurchaseOrderDetailVo(array());
		$item  = array_keys(get_object_vars($vo));
		$label = array(	"id"=>"id" ,
						"goodsNo"=>"产品编号" ,
						"goodsName"=>"产品名称" ,
						"spec"=>"型号" ,
						"current"=>"电流" ,
						"voltage"=>"电压" ,
						"qty"=>"数量"
					);
		$columnitem = $vo;
		$enabled = array("_type_"=>"Not","data"=>array("id","orderNo") );
		$visible = array("_type_"=>"Not","data"=>array("id","orderNo") ) ;
		$align = array("goodsNo"=>"center");
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