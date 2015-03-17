<?php
/* CONFIG START */

// DC
putenv('DC_CONFIGDIR=/etc/opt/dcx');
putenv('DC_APP=sport1');

// FSI
define("FSI_INSTALL", true);
define("FSI_URL", "http://mamssi03.teknograd.no/fsi/server?type=image&source=sport1_fsi");
define("FSI_PATH", "/data/kunder01/mam01/sport1/fsi1/");

// Service
$protocol = "http";

/* CONFIG END */

require('/opt/dcx/include/init.inc.php');
$file_content = json_decode(file_get_contents("files/".key($_REQUEST).".json"));

$client = @new SoapClient('http://l2w.no/l2w.wsdl', array('encoding'=>'UTF-8')); 

// We do below to route FB through stat on link shortner.
$me_url = $client->makeLink($protocol . "://". $_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI']);
?>
<!doctype html>
<html>
<head>
<title>Shared documents</title>
<meta http-equiv="X-UA-Compatible" content="IE=9" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<script type='text/javascript'></script>
<link rel='stylesheet' href='css/customer.css' type='text/css' /></head>
<body>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=170945986385865&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

	<div id='main'>
		<?php
		if(!is_array($file_content)) { ?>
		<h1>Unknown document link</h1>
		<?php
		} else { 
		?>
		<table width="100%" border="0"><tr>
		<td><h1>Shared documents</h1>
		<td><a id="zip-button" href="zip.php?<?php echo key($_REQUEST); ?>">Download all (ZIP)</a></td>
		<td width="80px"><div class="fb-share-button" data-href="<?php echo $me_url; ?>" data-type="button"></div></td>
		</tr></table>
		<?php
		$doc = new DCX_Document($app);
		
		foreach($file_content as $docid) {

			$ok = $doc->load($docid);
			$dcx_file_original = $doc->getFile('original');
			$path = $dcx_file_original->getPath();
			$bodytag = urlencode(str_replace(FSI_PATH, "", $path));
			
			echo "<div class='imgBox'>";
			echo "<div class='imgText'>";
			echo "<a href='download.php?docid=".$docid."&type=original' target='_blank'>Download</a>";
			echo "</div>";
			$getImage = FSI_URL. $bodytag."&quality=95&profile=jpeg&width=280&height=200";
			if(check1byte($getImage)=="") {
				$dcx_file_layout = $doc->getFile('layout');
				$url_to_file = $dcx_file_layout->getURL();
				echo "<div class='img'><img src='".$url_to_file."' width='280px' class='imgtarget'  /></div>";
				} else {
				echo "<div class='img'><img src='".$getImage."' class='imgtarget' /></div>";
				}
			echo "</div>";
			}
		}
		?>


	<div id="meta">
		<ul>
			<li id="li1"><a href="https://teknograd.no"><img src="http://l2w.no/imagelink_files/20130114/c6f76fe38158f9993658f7866879dd58.png"></a></li>
			<?php if(is_array($file_content)) { 
			$stat = json_decode($client->getStat($me_url));
			$total = 0;
			$print_stat = array();
			foreach($stat as $cHost) {
				$total = $total+$cHost->total;
				if ($cHost->host=="") {
					$cPHost = "Direct traffic";
					} else {
					$cPHost = $cHost->host;
					}
				$print_stat[] = $cPHost. " (".$cHost->percent."%)";
				}
			?>
			<li id="li2">Total hits: <?php echo $total; ?>.<br />
			<?php echo implode(", ", $print_stat); ?>.
			</li>
			<?php } ?>
		</ul>
	</div>
	
	</div>
</body>
</html>
<?php
function check1byte($getImage) {
	if(FSI_INSTALL==true) {
		$fp = fopen($getImage, 'r');
		$first10bytes = fread($fp, 10);
		fclose($fp);
		return $first10bytes;
		} else {
		return false;
		}
	}
?>

