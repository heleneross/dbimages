<?php
// http://www.webgeekly.com/tutorials/php/how-to-create-an-image-thumbnail-on-the-fly-using-php/
// Marc von Brockdorff 
$sImagePath = $_GET["file"];
 
$iThumbnailWidth = isset($_GET['width'])? (int)$_GET['width'] : null;
$iThumbnailHeight = isset($_GET['height']) ? (int)$_GET['height'] : null;
$iMaxWidth = isset($_GET["maxw"]) ? (int)$_GET["maxw"] : null;
$iMaxHeight = isset($_GET["maxh"]) ? (int)$_GET["maxh"] : null;
 
if ($iMaxWidth && $iMaxHeight) $sType = 'scale';
else if ($iThumbnailWidth && $iThumbnailHeight) $sType = 'exact';
 
$img = NULL;
$end = explode('.', $sImagePath);  
$sExtension = strtolower(end($end));
if ($sExtension == 'jpg' || $sExtension == 'jpeg') {
 
    $img = @imagecreatefromjpeg($sImagePath)
        or die("Cannot create new JPEG image");
 
} else if ($sExtension == 'png') {
 
    $img = @imagecreatefrompng($sImagePath)
        or die("Cannot create new PNG image");
 
} else if ($sExtension == 'gif') {
 
    $img = @imagecreatefromgif($sImagePath)
        or die("Cannot create new GIF image");
 
}
 
if ($img) {
 
    $iOrigWidth = imagesx($img);
    $iOrigHeight = imagesy($img);
 
    if ($sType == 'scale') {
 
        // Get scale ratio
 
        $fScale = min($iMaxWidth/$iOrigWidth, $iMaxHeight/$iOrigHeight);
 
        if ($fScale < 1) {
 
            $iNewWidth = floor($fScale*$iOrigWidth);
            $iNewHeight = floor($fScale*$iOrigHeight);
 
            $tmpimg = imagecreatetruecolor($iNewWidth, $iNewHeight);
 
            imagecopyresampled($tmpimg, $img, 0, 0, 0, 0, $iNewWidth, $iNewHeight, $iOrigWidth, $iOrigHeight);
 
            imagedestroy($img);
            $img = $tmpimg;
        }     
 
    } else if ($sType == "exact") {
 
        $fScale = max($iThumbnailWidth/$iOrigWidth, $iThumbnailHeight/$iOrigHeight);
 
        if ($fScale < 1) {
 
            $iNewWidth = floor($fScale*$iOrigWidth);
            $iNewHeight = floor($fScale*$iOrigHeight);
 
            $tmpimg = imagecreatetruecolor($iNewWidth, $iNewHeight);
            $tmp2img = imagecreatetruecolor($iThumbnailWidth, $iThumbnailHeight);
 
            imagecopyresampled($tmpimg, $img, 0, 0, 0, 0, $iNewWidth, $iNewHeight, $iOrigWidth, $iOrigHeight);
 
            if ($iNewWidth == $iThumbnailWidth) {
 
                $yAxis = ($iNewHeight/2)- ($iThumbnailHeight/2);
                $xAxis = 0;
 
            } else if ($iNewHeight == $iThumbnailHeight)  {
 
                $yAxis = 0;
                $xAxis = ($iNewWidth/2)- ($iThumbnailWidth/2);
 
            } 
 
            imagecopyresampled($tmp2img, $tmpimg, 0, 0,
                       $xAxis, $yAxis,
                       $iThumbnailWidth,
                       $iThumbnailHeight,
                       $iThumbnailWidth,
                       $iThumbnailHeight);
 
            imagedestroy($img);
            imagedestroy($tmpimg);
            $img = $tmp2img;
        }    
 
    }
    switch ($sExtension)
		{
			case 'png' :
				{
					imagesavealpha($img, true);
					imagepng($img);
          //header("Content-type: image/png");
          imagepng($img);
				}
			case 'gif' :
			{
        //header("Content-type: image/gif");
				imagegif($img);
			}	
			default :
			{
				//header("Content-type: image/jpeg");
        imagejpeg($img);
			}
		}
}
