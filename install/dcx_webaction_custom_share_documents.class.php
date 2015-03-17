<?php
class DCX_WebAction_custom_share_documents extends DCX_WebAction {

	protected $action_result_format = 'no_content';
    protected function prepareResults($msg_h = '') {

    	$base_url = "http://dcx.teknograd.no/";
    	$install_path = "share/";
    	$file_path = "/var/www/t7d/".$install_path;
    	
    	// End of above settings
		$doc = new DCX_Document($this->app);

            		
		// Building head info.
		$html_head_output = "<!doctype html>\n<html>\n<head>\n<title>Share documents</title>\n";		
		$html_head_output .= "<meta http-equiv='X-UA-Compatible' content='IE=8' />\n";
		$html_head_output .= "<script type='text/javascript'>";
		$html_head_output .="</script>\n";


					
		$html_head_output .="<link rel='stylesheet' href='".$base_url.$install_path."css/customer.css' type='text/css' /></head>\n<body>\n";
		$html_head_output .= "<div id='main'>";
		
		// Add head to content. We use head also for errors.
		
		$html_output = $html_head_output . "<h1>Share documents</h1>";
    	$doc_array = array();
        foreach ($this->selected_items as $doc_id) {
			// Write to history
			$ok = $this->app->track(
				'document:share',
				$doc_id,
				'Documents shared externaly.',
				'',
				''
				);
			// $html_output .=  "<h1>$ok - $doc_id</h1>";
			$doc_array[] = $doc_id;
			}
		
		$file_contents = json_encode($doc_array);
		$filename = sha1($file_contents);
		file_put_contents($file_path."files/".$filename.".json", $file_contents);
	
		// DC original
		// URL to the original document (filetype: PDF)
		$getUrl = $base_url.$install_path."?".$filename;

		$l2w_url = "";
		try { 
			$l2w_url = $this->callLinkShortner($getUrl);
			} catch (SoapFault $E) { 
			// Bad programming, sorry!.
			// We see network errors and know nothing is wrong so we just press on.
			$l2w_url= $this->callLinkShortner($getUrl);
			}
		
		$html_output .= "Below you'll find the link to selected documents that you can share.";
		$html_output .= "<br /><input type='text' id='theURL' name='theURL' value='".$l2w_url."' autofocus />";
		$html_output .= "<p><a href='".$l2w_url."' target='_blank'>Link to documents</a></p>";
		$html_output .= "<p><a href='mailto:?subject=Documents shared&body=Hi, Please download documents from ".$l2w_url."'>Open link in your mail program</a></p>";
		$html_output .= "</div></body></html>";

	echo $html_output;
    return 0;
    }

	function callLinkShortner($url) {
		$client = @new SoapClient('http://l2w.no/l2w.wsdl', array('encoding'=>'UTF-8')); 
		$result = $client->makeLink($url, "t7d");
		// $result = str_replace("http:", "https:", $result);
		return $result;
		}			
}
?>
