<?php  
/*
 * @author Jacek Materna
 *
 *	One Savant variable: $item which is the processed ouput message content according to lang spec.
 */
 
 global $_base_href;
 
// header
echo '<br /><table border="0" class="wrnbox" cellpadding="3" cellspacing="2" width="90%" summary="" align="center">' .
		'<tr class="wrnbox"><td><h3><img src="' . $_base_href . 'images/warning_x.gif" align="top" class="menuimage5" alt="' .
		_AT('warning') . '" /><small>' . _AT('warning') . '</small></h3>'."\n";	

$body = '';

if (is_object($this->item)) {
	/* this is a PEAR::ERROR object.	*/
	/* for backwards compatability.		*/
	$body .= $this->item->get_message();
	$body .= '.<p>';
	$body .= '<small>';
	$body .= $this->item->getUserInfo();
	$body .= '</small></p>'."\n";

} else if (is_array($this->item)) {
	/* this is an array of items */
	$body .= '<ul>'."\n";
	foreach($this->item as $e){
		$body .= '<li><small>'. $e .'</small></li>'."\n";
	}
	$body .= '</ul>'."\n";
}

// body
echo $body;

// footer
echo '</td></tr></table><br />'."\n";

?>