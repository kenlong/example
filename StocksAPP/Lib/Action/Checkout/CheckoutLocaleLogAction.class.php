<?php
class CheckoutLocaleLogAction
{
	//query
	public function query($condition)
	{
		try
		{
			$dao = new CheckoutLocaleLogDao();
			$result = $dao->findAll($condition)->toResultSet();
			
			return $result ;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
		
	}
	
	//save
	public function save($data)
	{
		try
		{
			$dao = new CheckoutLocaleLogDao();
			if($data["id"]!='' && $data["id"]!=0)
			{
				$vo = $dao->createVo('modify','','id',$data["id"],$data);
				$result = $dao->save($vo);
			}
			else 
			{
				$vo = $dao->createVo('add','','id',0,$data);
				$result = $dao->add($vo);
			}
			
			return $result;
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	
	//del
	public function del($data)
	{
		try
		{
			$dao = new CheckoutLocaleLogDao();
			$id = $data["id"] ;
			if($id==0 || $id== null || $id=='')
				return true ;
			$result = $dao->deleteById($id);
			
			return $result ;
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
		$vo = new CheckoutLocaleLogVo(array());
		$item  = array_keys(get_object_vars($vo));
		$label = array(	"id"=>"id",
						"checkDate"=>"校验日期",
					 	"uabc"=>"Uabc",
					 	"iabc"=>"Iabc",
					 	"pqcos"=>"PQCOSφ",
					 	"lineConnection"=>"接线检查记录",
					 	"shiYaShiJian"=>"失压时间H",
					 	"shiYaDianLiang"=>"失压电量",
					 	"riQiShiZhong"=>"日期时钟",
					 	"shiDuanShiQu"=>"时段时区",
					 	"gongLvGongNeng"=>"功率功能",
					 	"zhuBiaoZhengXiang"=>"主表正相反向或分时",
					 	"fuBiaoZhengXiang"=>"副表正相反向或分时",
					 	"zhuBiaoWuGongBiao"=>"主表无功表",
					 	"fuBiao"=>"副表二副表",
					 	"log"=>"事件记录",
					 	"leadSealing"=>"铅封表更记录",
					 	"recorder"=>"记录人工作人",
					 	"signer"=>"用户确认签名"
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