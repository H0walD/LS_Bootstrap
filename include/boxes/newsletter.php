<?php
#   Copyright by Manuel
#   Support www.ilch.de


defined ('main') or die ( 'no direct access' );


if ( empty($_POST['NEWSLETTER'])  ) {

?>

  <form action="index.php" method="POST" class="form-inline" role="form">


 <div class="form-group" style="margin-bottom:4px;">
    <label class="sr-only" for="nlemail">Email-Adresse</label>
    <input type="email" class="form-control" id="nlemail" placeholder="Email-Adresse">
  </div>
		<input type="submit"  class="btn btn-primary" value="<?php echo $lang['newsletterinout']; ?>">
<span class="help-block">Newsletter Ein/Austragen</span>
	</form>

<?php

} else {

	$email = escape ( $_POST['NEWSLETTER'] , 'string' );
	$erg = db_query ("SELECT COUNT(*) FROM prefix_newsletter WHERE email = '".$email."'");
	$anz = db_result($erg,0);
	if ( $anz == 1 ) {
	  db_query("DELETE FROM prefix_newsletter WHERE email = '".$email."'");
		echo $lang['deletesuccessful'];
	} else {
	  db_query("INSERT INTO prefix_newsletter (`email`) VALUES ('".$email."')");
		echo $lang['insertsuccessful'];
	}
}
?>