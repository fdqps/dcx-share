<?php
// This is basiclly a copy of image.php with some differences.
// The main diff is the header.
putenv('DC_CONFIGDIR=/etc/opt/dcx');
putenv('DC_APP=sport1');

require('/opt/dcx/include/init.inc.php');
$file_content = json_decode(file_get_contents("files/".key($_REQUEST).".json"));

if(!is_array($file_content)) {
    $app->log(DCX_Logger::LOG_ERR, sprintf('Could not find files in sent JSON.', $docid, $type, $variant));
    DCX_Image_Not_Found();
	}
	
$download_files = array();
$doc = new DCX_Document($app);
foreach($file_content as $docid) {
	$ok = $doc->load($docid);
	$dcx_file_original = $doc->getFile('original');
	$path = $dcx_file_original->getPath();
	$filename = trim((string) $dcx_file_original->getDisplayName());
	if (!$filename) {
		$filename = $doc->getTagValue("Filename");
		}
	
	$download_files[] = array(	"filename" 		=> $path,
							"contents"		=> false,
							"displayname"	=> $filename
						);
	}

// echo "<pre>".print_r($file_array, true)."</pre>";

/*
     *    [0] => Array
     *    (
     *      [filename] => /var/opt/dcx/.../file6d2sunjd5wlpce8kkqe.xml
     *      [contents] => 
     *      [displayname] => F_001613_01_UFA_317.mxf.xml
     *    )
     
downloadMultiple
downloadMultiple(DCX_Application $app, array $download_files, $zip_displayname = false)
*/
$dc_disk = new DCX_FileUtils();
$dc_disk->downloadMultiple($app, $download_files);

require(dirname(__DIR__) . '/include/exit.inc.php');

function DCX_Image_Not_Found() {
    global $app;
    
    header("HTTP/1.0 404 Not Found");
    header("Status: 404 Not Found");
    
    require(dirname(__DIR__) . '/include/exit.inc.php');
    exit();
}
?>