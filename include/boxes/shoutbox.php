<?php
// Copyright by Manuel
// Support www.ilch.de
defined ('main') or die ('no direct access');

$datum=date("j.n.Y");
$zeit=date(" H:i ");

if (loggedin()) {
    $shoutbox_VALUE_name = $_SESSION['authname'];
} else {
    $shoutbox_VALUE_name = 'Nickname';
}
if (has_right($allgAr['sb_recht'])) {
    if (!empty($_POST['shoutbox_submit']) AND chk_antispam ('shoutbox')) {
        $shoutbox_nickname = escape($_POST['shoutbox_nickname'], 'string');
        $shoutbox_nickname = substr($shoutbox_nickname, 0, 15);
        $shoutbox_textarea = escape($_POST['shoutbox_textarea'], 'textarea');
        $shoutbox_textarea = preg_replace("/\[.?(url|b|i|u|img|code|quote)[^\]]*?\]/i", "", $shoutbox_textarea);
        $shoutbox_textarea = strip_tags($shoutbox_textarea);
        if (!empty($shoutbox_nickname) AND !empty($shoutbox_textarea)) {
            db_query('INSERT INTO `prefix_shoutbox` (`nickname`,`textarea`) VALUES ( "' . $shoutbox_nickname . '<br><p>'.$datum.' | '.$zeit.' Uhr</p>" , "' . $shoutbox_textarea . '" ) ');
            header('Location: index.php?' . $menu->get_complete());
        }
    }
    echo '<form action="index.php?' . $menu->get_complete() . '" method="POST" class="form-horizontal" role="form">';
    echo '<input type="text" size="15" name="shoutbox_nickname" class="form-control" value="' . $shoutbox_VALUE_name . '" onFocus="if (value == \'' . $shoutbox_VALUE_name . '\') {value = \'\'}" onBlur="if (value == \'\') {value = \'' . $shoutbox_VALUE_name . '\'}" maxlength="15">';
    echo '<br /><textarea class="form-control" rows="3" name="shoutbox_textarea"  placeholder="Deine Nachricht"></textarea>';
    $antispam = get_antispam ('shoutbox', 0);
	echo $antispam;
	if (!empty($antispam)) {
		echo '<br>';
	}
    echo '<input type="submit" class="btn btn-primary btn-sm" value="' . $lang['formsub'] . '" name="shoutbox_submit"><br><br>';
    echo '</form>';
}
echo '<div class="panel panel-primary">';
$erg = db_query('SELECT * FROM `prefix_shoutbox` ORDER BY id DESC LIMIT ' . (is_numeric($allgAr['sb_limit'])?$allgAr['sb_limit']:5));
while ($row = db_fetch_object($erg)) {
    echo '<div class="panel-heading"><h4 class="panel-title shoutbox-time">'. $row->nickname .'</h4></div><div class="panel-body">'. preg_replace('/([^\s]{' . $allgAr['sb_maxwordlength'] . '})(?=[^\s])/', "$1\n", $row->textarea) .'</div>';
}
echo '</div><span style="float:right;"><br><a  class="btn btn-primary btn-sm" href="index.php?shoutbox">'. $lang['archiv'] .'</a></span>';

?>