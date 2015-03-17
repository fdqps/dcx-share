<?php
// START config
$base_url = "http://sport1.teknograd.no/";
$install_path = "share/";
$file_path = "/var/www/sport1/".$install_path;
putenv('DC_CONFIGDIR=/etc/opt/dcx');
putenv('DC_APP=sport1');
// END Config
	
    	
if(!isset($_REQUEST["type"])) {
	echo json_encode(array("return" => "Error: Type missing."));
	exit();
	} else {
	$type = $_REQUEST["type"];
	}

if(isset($_REQUEST["action"]) && $_REQUEST["action"]=="zip") {
	$action = "zip";
	} else {
	$action = "gallery";
	}


if ($type!="dcx" && $type!="ean") {
	echo json_encode(array("return" => "Error: Wrong type (use dcx or ean)."));
	exit();
	}

if(!isset($_REQUEST["data"])) {
	echo json_encode(array("return" => "Error: Data missing."));
	exit();
	} else {
	$data = json_decode(urldecode($_REQUEST["data"]));
	}

// http://sport1.teknograd.no/share/api/?type=dcx&data=%255B%2522doc6i421jziluee4btrcay%2522%252C%2522doc6i2ypnhlahhnok7pcax%2522%255D
if(!is_array($data)) {
	echo json_encode(array("return" => "Error: Could not handle JSON data."));
	exit();	
	}


$doc_array = array();
foreach($data as $obj) {
	$doc_array[] = cleanData($obj);
	}
// echo "<pre>".print_r($doc_array, true)."</pre>";
// exit;
$ean_doc_array = array();
if($type=="ean") {
	include_once("../../dcx_sport1_class.php");
	$c = new DCX_Sport1_class($app);
	foreach($doc_array as $artno) {
		// echo "<br>EAN: " . $artno;
		$c->getPicDocIdBySpArtNr("EANNO", $artno, $docid);
		$ean_doc_array[] = $docid;
		}
	$doc_array = $ean_doc_array;
	}


if(!is_array($doc_array)) {
	echo json_encode(array("return" => "Error: Could not create JSON data."));
	exit();	
	}

$file_contents = json_encode($doc_array);
$filename = sha1($file_contents);
file_put_contents($file_path."files/".$filename.".json", $file_contents);

// DC original
// URL to the original document (filetype: PDF)
if($action=="zip") {
	// http://sport1.teknograd.no/share/zip.php?89b9a7d49739704253b1bc11e9bb6f05a771010f
	$getUrl = $base_url.$install_path."zip.php?".$filename;
	} else {
	$getUrl = $base_url.$install_path."?".$filename;
	}
	
$l2w_url = "";
try { 
	$l2w_url = callLinkShortner($getUrl);
	} catch (SoapFault $E) { 
	// Bad programming, sorry!.
	// We see network errors and know nothing is wrong so we just press on.
	$l2w_url= callLinkShortner($getUrl);
	}
echo json_encode(array("return" => $l2w_url));


// Functions
function callLinkShortner($url) {
	$client = @new SoapClient('http://l2w.no/l2w.wsdl', array('encoding'=>'UTF-8')); 
	$result = $client->makeLink($url, "t7d");
	// $result = str_replace("http:", "https:", $result);
	return $result;
	}	

function cleanData($string) {
	$string = strip_tags($string);
	$string = preg_replace("/[^0-9a-zA-Z_]/","",$string);
	return $string;
	}
?>