<?php
include("class/pData.class.php");
include("class/pDraw.class.php");
include("class/pImage.class.php");

$myData = new pData();
$myData->addPoints(array(64,187,395,249,109,129,136,133,134,95,72,85),"Serie1");
$myData->setSerieDescription("Serie1","Serie 1");
$myData->setSerieOnAxis("Serie1",0);

$myData->addPoints(array("January","February","March", "April", "May", "June", "July", "August", "September", "October", "November", "December"),"Absissa");
$myData->setAbscissa("Absissa");

$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Rejistu sira");
$myData->setAxisUnit(0,"");

$myPicture = new pImage(700,230,$myData);
$myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>TEXT_ALIGN_MIDDLEMIDDLE
, "R"=>5, "G"=>0, "B"=>79);
$myPicture->drawText(350,25,"Andamentu Geral Rejistru Kliente",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,675,190);
$myPicture->setFontProperties(array("R"=>0,"G"=>0,"B"=>0,"FontName"=>"fonts/GeosansLight.ttf","FontSize"=>10));

$Settings = array("Pos"=>SCALE_POS_LEFTRIGHT
, "Mode"=>SCALE_MODE_FLOATING, "LabelSkip"=>1,
"LabelingMethod"=>LABELING_ALL
, "GridR"=>186, "GridG"=>177, "GridB"=>185, "GridAlpha"=>50, "TickR"=>240, "TickG"=>17, "TickB"=>17, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1,"DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("DisplayValues"=>1, "BreakVoid"=>0, "BreakR"=>234, "BreakG"=>55, "BreakB"=>26);
$myPicture->drawLineChart($Config);

$myPicture->stroke();
?>
