<?php    
 /* CAT:Pie charts */ 

 /* pChart library inclusions */ 
 include("../class/pData.class.php"); 
 include("../class/pDraw.class.php"); 
 include("../class/pPie.class.php"); 
 include("../class/pImage.class.php"); 

 /* Create and populate the pData object */ 
 $MyData = new pData();    
 $MyData->addPoints(array(40,50),"ScoreA");   
 $MyData->setSerieDescription("ScoreA","Application A"); 

 /* Define the absissa serie */ 
 $MyData->addPoints(array("MANE","FETO"),"Labels"); 
 $MyData->setAbscissa("Labels"); 

 /* Create the pChart object */ 
 $myPicture = new pImage(600,200,$MyData,TRUE); 

 $Settings = array("R"=>240, "G"=>240, "B"=>240 ); 
 $myPicture->drawFilledRectangle(0,0,700,230,$Settings); 

 /* Add a border to the picture */ 
 $myPicture->drawRectangle(0,0,599,199,array("R"=>0,"G"=>0,"B"=>0)); 

 /* Set the default font properties */  
 $myPicture->setFontProperties(array("FontName"=>"../fonts/GeosansLight.ttf","FontSize"=>15,"R"=>0,"G"=>0,"B"=>0)); 

 /* Create the pPie object */  
 $PieChart = new pPie($myPicture,$MyData); 

 /* Draw a splitted pie chart */  
 $PieChart->draw3DPie(300,125,array("WriteValues"=>TRUE,"Border"=>TRUE, 'ValueR'=> 0, 'ValueG' => 0, 'ValueB' => 0)); 

 /* Write the picture title */  
 $myPicture->setFontProperties(array("FontName"=>"../fonts/GeosansLight.ttf","FontSize"=>12)); 
 $myPicture->drawText(230,25,"Divisaun Rejistu FETO no MANE",array("R"=>0,"G"=>0,"B"=>0)); 

 /* Write the legend box */  
 $myPicture->setFontProperties(array("FontName"=>"../fonts/GeosansLight.ttf","FontSize"=>10,"R"=>0,"G"=>0,"B"=>0)); 
 $PieChart->drawPieLegend(500,40,array("Style"=>LEGEND_NOBORDER)); 

 /* Render the picture (choose the best way) */ 
 $myPicture->autoOutput("pictures/example.draw3DPie.png"); 
?>
