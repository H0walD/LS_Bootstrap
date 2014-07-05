<?php
#   Copyright by Manuel
#   Support www.ilch.de


defined ('main') or die ( 'no direct access' );
echo '<table class="table table-condensed table-hover">';
$akttime = date('Y-m-d');
$erg = @db_query("SELECT DATE_FORMAT(datime,'%d.%m.%y - %H:%i') as time,tag,gegner, id, game FROM prefix_wars WHERE status = 2 AND datime > '".$akttime."' ORDER BY datime");
if ( @db_num_rows($erg) == 0 ) {
	echo '<tr><td>kein War geplant</td></tr>';
} else {
	while ($row = @db_fetch_object($erg) ) {
		$row->tag = ( empty($row->tag) ? $row->gegner : $row->tag );
		echo '<tr>';
		echo '<td class="text-center"><a href="index.php?wars-more-'.$row->id.'">'.$row->tag.'</a></td>';
		echo '<td class="text-right">'.$row->time.' Uhr</td>';
		echo '</tr>';
	}
}
echo '</table>';
?>