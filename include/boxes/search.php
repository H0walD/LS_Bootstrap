<?php
#   Copyright by Manuel
#   Support www.ilch.de


defined ('main') or die ( 'no direct access' );

$suchtpl = <<<HTML
<div style="width: 100%;padding-left:10%;padding-right:10%;">
<form action="index.php?search" method="GET" role="form">
<div class="input-group">
      <input type="text" value="{search}" name="search" size="{size}" class="form-control" placeholder="Was suchst du?"><input type="hidden" name="in" value="2" />
      <span class="input-group-btn">
        <input type="submit" value="{_lang_search}" class="btn btn-primary">
      </span>
    </div>
</form>
<div class="text-right"><small><a href="index.php?search">{_lang_exsearch}</a></small></div></div>
HTML;

$tpl = new tpl ($suchtpl,3);
$tpl->set ('size', 16);
if(isset($_GET['search']))
	$tpl->set ('search', escape($_GET['search'],'string'));
else $tpl->set ('search', '');
$tpl->out(0);

?>



