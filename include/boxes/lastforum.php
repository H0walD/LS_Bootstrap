<?php 
#   Copyright by Manuel Staechele
#   Support www.ilch.de

defined ('main') or die ( 'no direct access' );

$query = "SELECT a.id, a.name, a.rep,b.name as top, b.id as fid, c.erst as last, c.erstid, c.id as pid, c.time, a.rep, a.erst, a.hit, a.art, a.stat, d.name as kat
FROM prefix_topics a
  LEFT JOIN prefix_forums b ON b.id = a.fid
  LEFT JOIN prefix_posts c ON c.id = a.last_post_id
	LEFT JOIN prefix_forumcats d ON d.id = b.cid AND b.id = a.fid
  LEFT JOIN prefix_groupusers vg ON vg.uid = ".$_SESSION['authid']." AND vg.gid = b.view
  LEFT JOIN prefix_groupusers rg ON rg.uid = ".$_SESSION['authid']." AND rg.gid = b.reply
  LEFT JOIN prefix_groupusers sg ON sg.uid = ".$_SESSION['authid']." AND sg.gid = b.start
WHERE ((".$_SESSION['authright']." <= b.view AND b.view < 1) 
   OR (".$_SESSION['authright']." <= b.reply AND b.reply < 1)
   OR (".$_SESSION['authright']." <= b.start AND b.start < 1)
	 OR vg.fid IS NOT NULL
	 OR rg.fid IS NOT NULL
	 OR sg.fid IS NOT NULL
	 OR -9 >= ".$_SESSION['authright'].")
ORDER BY c.time DESC
LIMIT 0,4";
echo '<ul class="list-group list-group-boxen text-left">';
$resultID = db_query($query);
while ($row = db_fetch_assoc($resultID)) {
	$row['date'] = date('d.m.y - H:i',$row['time']);
	$row['page'] = ceil ( ($row['rep']+1)  / $allgAr['Fpanz'] );
	$row['ORD']  = forum_get_ordner($row['time'],$row['id'],$row['fid']);
	
	echo '<a href="?forum-showposts-'.$row['id'].'-p'.$row['page'].'#'.$row['pid'].'" class="list-group-item"><p class="text-muted">Kategorie: '.$row['kat'].'</p><h4><i class="fa fa-angle-double-right"></i> '.$row['name'].'</h4><p class="text-muted">
Last Post:&nbsp;'.$row['last'].' | '.$row['date'].' Uhr</p></a>';

}
echo '</ul>';

?>








