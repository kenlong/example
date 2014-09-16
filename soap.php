<?php
class test
{   
    function show($row)   
    {   
		try{
		$cnx = new PDO("odbc:Driver={SQL Server};Server=10.10.10.146;Database=LanHu;",'php','php123');
		$date = date("Y-m-d H:i:s", $row['BillDate']);
		$sql = "INSERT INTO [dbo].[C_CusCallServices] (LinkMan, linkTel, Address, PostNetID_Name, CusID_Name, MDD, CustRequery, Packages, Weight, MetailType, BillDate) values ( '{$row['LinkMan']}', '{$row['linkTel']}', '{$row['Address']}', '{$row['NetNo']}', '{$row['CusID_Name']}', '{$row['MDD']}', '{$row['CustRequery']}', {$row['Packages']}, {$row['Weight']} , '{$row['MetailType']}', '{$date}' );";
		$sql=iconv('utf-8','gb2312',$sql);
		//return $sql;
		if($cnx->exec($sql)){//同步成功
			return true;
		}else{
			return false;
		}

		}catch(Exception $e){
			echo $e->getMessage();exit;
		}
    }

	function get($trackingNo){
		try{
			$cnx = new PDO("odbc:Driver={SQL Server};Server=10.10.10.146;Database=LanHu;",'php','php123');
			$sql = "select BillNo, MdisNo, MdisComp from Bill_tab where BillNo='{$trackingNo}'";
			$sth = $cnx->prepare($sql);
			$sth->execute();
			return $sth->fetchAll();
		}catch(Exception $e){
			echo $e->getMessage();
		}
        function track($trackingNo){
               try{
			$cnx = new PDO("odbc:Driver={SQL Server};Server=10.10.10.146;Database=LanHu;",'php','php123');
			$sql="exec pr_BarDescribe_new 
           			@BillNo = '$trackingNo';"; 
			$sth = $cnx->prepare($sql);
			$sth->execute();
			return $sth->fetchAll();
               } catch(Exception $e){
                       echo $e->getMessage();
               }
        }
	}
}
 
$server = new SoapServer(null, array('uri' =>'http://soap/','location'=>'http://localhost:8090/soap.php'));   
$server->setClass('test');
$server->handle();
?>