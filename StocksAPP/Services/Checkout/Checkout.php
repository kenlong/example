<?php
import("@.Action.Checkout.CheckoutCardAction");
import("@.Action.Checkout.CheckoutCardTransformerAction");
import("@.Action.Checkout.CheckoutLocaleLogAction");
class Checkout
{
	//query list
	public function queryCardList($condition='')
	{
		try {
			$action = new CheckoutCardAction();
			$result = $action->queryList($condition);
			
			return $result ;
		}
		catch (Exception $e)
		{
			system_out("queryCardList error:".$e);
			throw new Exception($e);
		}
	}
	
	//query checkoutcard
	public function queryCard($condition='')
	{
		try
		{
			$action = new CheckoutCardAction();
			$result = $action->query($condition);
			
			return $result ;
		}
		catch (Exception $e)
		{
			system_out("queryCard error:".$e);
			throw new Exception($e);
		}
	}
	
	//query checkoutcarTransformer
	public function queryTransformer($condition='')
	{
		try
		{
			$action = new CheckoutCardTransformerAction();
			$result = $action->query($condition);

			return $result ;
		}
		catch (Exception $e)
		{
			system_out("queryTransformer error:".$e);
			throw new Exception($e);
		}
	}
	
	//query checkoutLocale
	public function queryLocaleLog($condition)
	{
		try
		{
			$action = new CheckoutLocaleLogAction();
			$result = $action->query($condition);
			
			return $result ;
		}
		catch (Exception $e)
		{
			system_out("queryLocale error:".$e);
			throw new Exception($e);
		}
	}
	
	//save card
	public function saveCard($data)
	{
		try {
			$action = new CheckoutCardAction();
			$result = $action->save($data);
			return $result?true:false ;
		}
		catch (Exception $e)
		{
			system_out("save car error:".$e);
			throw new Exception($e);
		}
	}
	
	//save transformer
	public function saveTransformer($data)
	{
		try {
			$action = new CheckoutCardTransformerAction();
			$result = $action->save($data);
			
			return $result?true:false ;
		}
		catch (Exception $e)
		{
			system_out("save transformer error:".$e);
			throw new Exception($e);
		}
	}
	
	//save locale
	public function saveLocaleLog($data)
	{
		try {
			$action = new CheckoutLocaleLogAction();
			
			if(!is_array($data))
			{
				$result = $action->save($data);
			}
			else 
			{
				for ($i=0;$i<sizeof($data);$i++)
				{
					$itemdata = $data[$i];
					
					unset($itemdata["mx_internal_uid"]);
					
					if(sizeof($itemdata)<=0) continue ;
					
					$result = $action->save($data[$i]);
				}
			}
			
			return $result?true:false ;
		}
		catch (Exception $e)
		{
			system_out("save locale error:".$e);
			throw new Exception($e);
		}
	}
	
	//delete
	public function delCard($data)
	{
		try {
			$action = new CheckoutCardAction();
			$result = $action->del($data);
						
			return $result?true:false ;
		}
		catch (Exception $e)
		{
			system_out("del card error:".$e);
			throw new Exception($e);
		}
	}
	
	//del transformer
	public function delTransformer($data)
	{
		try {
			$action = new CheckoutCardTransformerAction();
			$result = $action->del($data);
			
			return $result?true:false ;
		}
		catch (Exception $e)
		{
			system_out("del transformer error:".$e);
			throw new Exception($e);
		}
	}
	
	//del locale
	public function delLocaleLog($data)
	{
		try {
			$action = new CheckoutLocaleLogAction();
			$result = $action->del($data);
			
			return $result?true:false ;
		}
		catch (Exception $e)
		{
			system_out("del locale error:".$e);
			throw new Exception($e);
		}
	}
	
	/**
	 * 获取datawindw结构
	 *
	 */
	public function getStruct_card()
	{
		$action = new CheckoutCardAction();
		$result = $action->getStruct();
		
		return $result;
	}
	
	/**
	 * 获取datawindw结构
	 *
	 */
	public function getStruct_former()
	{
		$action = new CheckoutCardTransformerAction();
		$result = $action->getStruct();

		return $result;
	}
	
	/**
	 * 获取datawindw结构
	 *
	 */
	public function getStruct_locale()
	{
		$action = new CheckoutLocaleLogAction();
		$result = $action->getStruct();

		return $result;
	}
	
}
?>