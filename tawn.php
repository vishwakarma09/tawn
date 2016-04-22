<?php
include_once __DIR__.'/simple_html_dom.php';
include_once __DIR__.'/ASPBrowser.php';
                                       
if(isset($_GET['district']) and trim($_GET['district']) != ""){
	$district = trim($_GET["district"]);
}else die("Please frame request with tawn.php?district=23&block=165");

if(isset($_GET['block']) and trim($_GET['block']) != ""){
	$block = trim($_GET["block"]);
}else die("Please frame request with tawn.php?district=23&block=165");
												 
$dom = new simple_html_dom();
// $dom->load(file_get_contents('source.txt'));
$dom->load(GetDom($district, $block));

$rowArray = array();
foreach($dom->find('#DynamicWeatherDataDiv div') as $tr) {
	$first = true;
	foreach($tr->find('div') as $td){
		if($first){
			$first = false;
			$column = array();
			$column[] = $td->innertext;
		}else{
			$column[] = $td->innertext;
		}
	}
	$rowArray[] = $column;
}
// echo json_encode($rowArray);

$filename = "csvfile.csv";
$hCSV = fopen($filename, "w");
if($hCSV){
	foreach($rowArray as $row){
		fputcsv($hCSV, $row);	
	}
}

header('Content-Disposition: attachment; filename=' . $filename);
readfile($filename);
die();

function GetDom($district, $block){
	$url = 'http://tawn.tnau.ac.in/';
	$browser = new ASPBrowser();
	$html = $browser->doGetRequest($url); // get form

	$url = "http://tawn.tnau.ac.in/General/DistrictWiseSummaryPublicUI.aspx?RW=1";
	$html = $browser->doGetRequest($url); // get form

	$url = "http://tawn.tnau.ac.in/General/DistrictWiseSummaryPublicUI.aspx?RW=1";

	//session set the district
	$postArray = array(
	'__EVENTTARGET'	=>	'ddlDistrict',
	'ddlDistrict'	=>	$district,
	'ddlBlock'	=>	'0',
	);

	$browser->doPostRequest($url, $postArray);

	//get page by get request
	$url = "http://tawn.tnau.ac.in/General/BlockWiseSummaryPublicUI.aspx?EntityHierarchyOn";
	$html = $browser->doGetRequest($url); // get form
	// echo $html;

	//session to set final request for block
	$url = "http://tawn.tnau.ac.in/General/BlockWiseSummaryPublicUI.aspx?EntityHierarchyOneKey=23&lang=en";
	//session set the district
	$postArray = array(
	'__EVENTTARGET'	=>	'ddlBlock',
	'ddlDistrict'	=>	$district,
	'ddlBlock'	=>	$block,
	);
	$browser->doPostRequest($url, $postArray);

	//now get the data from GET request
	$url = "http://tawn.tnau.ac.in/General/BlockCurrentDayDataPublicUI.aspx?EntityHierarchyOneKey=23&EntityHierarchyTwoKey=165&lang=en";
	$html = $browser->doGetRequest($url); // get form
	// echo $html;

	//finally to get to the GET tab, do another get request
	$url = "http://tawn.tnau.ac.in/General/BlockLastMonthSummaryPublicUI.aspx?EntityHierarchyOneKey=23&EntityHierarchyTwoKey=165&lang=en";
	$html = $browser->doGetRequest($url); // get form
	return $html;
}