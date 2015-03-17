<?php
// This is basiclly a copy of image.php with some differences.
// The main diff is the header.
putenv('DC_CONFIGDIR=/etc/opt/dcx');
putenv('DC_APP=sport1');

require('/opt/dcx/include/init.inc.php');

$docid   = isset($_REQUEST[ 'docid'   ]) ? trim($_REQUEST[ 'docid'   ]) : false;
$type    = isset($_REQUEST[ 'type'    ]) ? trim($_REQUEST[ 'type'    ]) : 'layout';
$variant = isset($_REQUEST[ 'variant' ]) ? trim($_REQUEST[ 'variant' ]) : 'master';

$dcx_document = new DCX_Document($app);

$ok = $dcx_document->load($docid);
if ($ok < 0)
{
    $app->log(DCX_Logger::LOG_ERR, sprintf('Load document with id <%s> returns error: %s', $docid, $ok));
    DCX_Image_Not_Found();
}

if (! $dcx_document->hasFile($type, $variant))
{
    $app->log(DCX_Logger::LOG_ERR, sprintf('Document <%s> has no file with type <%s>, filevariant: <%s>.', $docid, $type, $variant));
    DCX_Image_Not_Found();
}

$dcx_file = $dcx_document->getFile($type, $variant);

$filename = trim((string) $dcx_file->getDisplayName());
if (!$filename) {
	$filename = $dcx_document->getTagValue("Filename");
	}


// OLD DC-4 headers
/*
header("Content-type: download-application/unknown; name=$filename");
header("Content-Disposition: attachment; filename=$filename");
header("Content-Length: ".filesize($dcx_file->getPath()));
header("Content-Transfer-Encoding: binary");
header("Cache-Control: maxage=1");
header("Pragma: public");     
*/
						
header("Content-Description: File Transfer");
header("Content-type: " . $dcx_file->getMimeType());
header("Content-Disposition: attachment; filename=\"".$filename."\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . filesize($dcx_file->getPath()));
readfile($dcx_file->getPath());

require(dirname(__DIR__) . '/include/exit.inc.php');

function DCX_Image_Not_Found()
{
    global $app;
    
    header("HTTP/1.0 404 Not Found");
    header("Status: 404 Not Found");
    
    require(dirname(__DIR__) . '/include/exit.inc.php');
    exit();
}

?>