<?php
require(dirname(__FILE__).'/../../../wp-load.php');
global $autologon_object;
header("Content-type: text/csv; charset=UTF-8'");
//header ("Content-Type: application/msexcel");
header("Content-Disposition: attachment; filename=".str_replace(" ","",preg_replace("/[^a-zA-Z0-9]+/", "",get_bloginfo('name')))."_User_".date("d_m_Y_H-i-s").".csv");

header("Pragma: no-cache");
header("Expires: 0");
echo "\xEF\xBB\xBF"; // Carattere BOM per visualizare il cirillico in excel.


//$where="";
$where="blog_id=".get_current_blog_id() ;


if(isset($_POST['dataStart']) && $_POST['dataStart']!=""){
	if ($where!="" ){
		$where .=' AND ';
	}

	$where .=" timestamp >='".$_POST['dataStart']."'";

}

if(isset($_POST['dataEnd']) && $_POST['dataEnd']!=""){
	if ($where!="" ){
		$where .=' AND ';
	}

	$where .=" timestamp <='".$_POST['dataEnd']."'";

}



$table_name = $autologon_object->autologon_log;

if($where!="")  $where=" where ".$where;


mysql_query("SET CHARACTER SET utf8");
mysql_query("SET NAMES utf8");
mysql_query("SET character_set_results utf8");
mysql_set_charset('utf8');

$output = fopen("php://output", "w");
//$rowCsv=array();
$row=array();

mysql_query("SET CHARACTER SET 'utf8'");
mysql_query("SET NAMES 'utf8'");
$ro=array();
$ro[]="Blog";
$ro[]="Data Accesso";
$ro[]="Lingua";
$ro[]="Country";
$ro[]="Matricola";
//$ro[]="Email";
fputcsv($output, $ro,';', '"');
	



$queryDati = "SELECT blog_name,timestamp,locId,countryName,user_login FROM  $table_name  $where   ORDER BY timestamp DESC" ;
//die("Query= ".$query);
$resultDati = mysql_query( $queryDati ) or die("Couldn t execute query.".$queryDati."  error=".mysql_error());

if ($resultDati) {

	while($row = mysql_fetch_array($resultDati, MYSQL_NUM))
	{
		$ro=array();
		foreach($row as $key=>$value)
		{
				$ro[]=$value;
		}
		//$ro=array_shift($ro);
		fputcsv($output, $ro,';', '"');
	}
}
fclose($output);
?>