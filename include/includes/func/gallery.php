<?php 
#   Copyright by Manuel
#   Support www.ilch.de


defined ('main') or die ( 'no direct access' );

function create_thumb ( $imgpath, $thumbpath, $neueBreite ) {
	$size=getimagesize($imgpath);
	$breite=$size[0];
  $hoehe=$size[1];
  $neueHoehe=intval($hoehe*$neueBreite/$breite);
	
  if (function_exists('gd_info')) {
    $tmp = gd_info();
    $imgsup = ($tmp['GIF Create Support'] ? 1 : 2);
    unset($tmp);
  }	else $imgsup = 2;
	
  if ($size[2] < $imgsup OR $size[2] > 3) { return (FALSE); }
   
  if ($size[2] == 1) {
    $altesBild = imagecreatefromgif($imgpath);
  } elseif ($size[2] == 2) {
    $altesBild = imagecreatefromjpeg($imgpath);
  } elseif ( $size[2] == 3 ) {
    $altesBild = imagecreatefrompng($imgpath);
  }
  if ( function_exists ( 'imagecreatetruecolor' ) AND $size[2] != 1 ) {
    $neuesBild = imagecreatetruecolor($neueBreite,$neueHoehe);
    imagecopyresampled($neuesBild, $altesBild, 0, 0, 0, 0, $neueBreite,$neueHoehe, $breite, $hoehe);
  } else {
    $neuesBild=imageCreate($neueBreite,$neueHoehe);
    imageCopyResized($neuesBild,$altesBild,0,0,0,0,$neueBreite,$neueHoehe,$breite,$hoehe);
  }
  if ($size[2] == 1) {
    ImageGIF($neuesBild,$thumbpath);
  }	elseif ($size[2] == 2) {
    ImageJPEG($neuesBild,$thumbpath);
  } elseif ( $size[2] == 3 ) {
    ImagePNG($neuesBild,$thumbpath); 
  }
  return (TRUE);
}

?>