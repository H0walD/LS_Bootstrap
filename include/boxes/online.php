<?php
if (!defined('main')) {die("no direct access");}
$dif = date('Y-m-d H:i:s', time() - 60);
$abf = "SELECT uid FROM `prefix_online` WHERE uptime > '". $dif."'";
$resultID = db_query($abf);
$brk='';
$uid = array();
$guests = 0;
$guestn = $lang['guests'];
$content='';

while ($row = db_fetch_object($resultID)) {
	if ($row->uid != 0 AND $brk!=$row->uid) {
		$name=@db_result(db_query('SELECT name FROM prefix_user WHERE id='.$row->uid),0);
		$content.= '<tr><td><i style="color:#00E21A;text-shadow: #000 2px 2px 1px;" class="fa fa-male"></i>&nbsp;&nbsp;</td>';
		$content.='<td><a href="index.php?user-details-'.$row->uid.'">'.$name.'</a></td></tr>'."\n";
		$uid[] = $row->uid;
	}
	if ($row->uid == 0) { $guests++; }
	$brk=$row->uid;
}
if ($guests == 1) { $guestn = $lang['guest']; }
if (empty($content)) { $content.='<tr><td><i style="color:#FF0000;text-shadow: #000 2px 2px 1px;" class="fa fa-male"></i>&nbsp;&nbsp;</td><td><font color="#003366">0 User </font></td></tr>'."\n"; }

$content.='<tr><td colspan="2"><hr></td></tr>'."\n";
$where = (count($uid)>0) ? 'WHERE id NOT IN ('.implode(', ', $uid).')' : '';
$abf2 = 'SELECT * FROM prefix_user '.$where.' ORDER BY llogin DESC LIMIT 0,5';
$erg2 = db_query($abf2);

while ($row2 = db_fetch_object($erg2)) {
	$datum = date('H:i d.m.y',$row2->llogin);
	$user = $row2->name;
	$content.='<tr><td><i style="color:#FF0000;text-shadow: #000 2px 2px 1px;" class="fa fa-male"></i>&nbsp;&nbsp;</td><td><a href="index.php?user-details-'.$row2->id.'" rel="tooltip" title="'.$lang['lasttimeonline'].$datum.'">'.$user.'</a></td></tr>'."\n";
}
if ($guests == 0) {
	$content.= '<tr><td colspan="2"><hr></td></tr>'."\n".'
		<tr><td><i style="color:#FF0000;text-shadow: #000 2px 2px 1px;" class="fa fa-male"></i>&nbsp;&nbsp;</td><td><font size="-1" class="text-primary">0 '.$lang['guests'].'</td></tr>'."\n";
} else {
	$content.= '<tr><td colspan="2"><hr></td></tr>'."\n".'
		<tr><td><i style="color:#00E21A;text-shadow: #000 2px 2px 1px;" class="fa fa-male"></i>&nbsp;&nbsp;</td><td><font size=-1>'.$guests.' '.$guestn.'</font></td></tr>'."\n";
}
?>
<table align="center" border="0" cellpadding="0" cellspacing="0">
 <?php echo $content; ?>
</table>
