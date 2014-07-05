<?php
#   Copyright by Manuel
#   Support www.ilch.de


defined ('main') or die ( 'no direct access' );

$farbe = '';
$farb2 = '';

echo '<table class="table table-condensed table-hover">';
$erg = db_query('SELECT * FROM prefix_wars WHERE status = "3" ORDER BY datime DESC LIMIT 3');
while ($row = db_fetch_object($erg) ) {
	$row->tag = ( empty($row->tag) ? $row->gegner : $row->tag );

  if ($row->wlp == 1) {
    $bild = '<i rel="tooltip" title="Gewonnen" style="color:#00E21A;text-shadow: #000 2px 2px 1px;" class="fa fa-thumbs-up"></i>';

  } elseif ($row->wlp == 2) {
    $bild = '<i rel="tooltip" title="Verloren" style="color:#FF0000;text-shadow: #000 2px 2px 1px;" class="fa fa-thumbs-down"></i>';

  } elseif ($row->wlp == 3) {
    $bild = '<i rel="tooltip" title="Unentschieden" style="color:#FFD800;text-shadow: #000 2px 2px 1px;" class="fa fa-minus-square"></i>';

  }

	
	echo '<tr><td>'.get_wargameimg($row->game).'</td><td class="text-left">';
	echo '<a href="index.php?wars-more-'.$row->id.'">';
	echo $row->owp.' '.$lang['at2'].' '.$row->opp.' - '.$row->tag.'</a></td><td class="text-right">'.$bild.'</td></tr>';
}
echo '</table>';
?>