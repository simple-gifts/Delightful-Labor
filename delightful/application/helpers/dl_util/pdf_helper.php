<?php
/*
      $this->load->helper('dl_util/pdf');
*/

function strXlatePaperSize($enumPaperSize, $bMetric){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $strOut = '';
   if ($bMetric){
      switch ($enumPaperSize){
         case CENUM_PDFPSIZE_LETTER: $strOut = 'Letter (216 x 279 mm)'; break;
         case CENUM_PDFPSIZE_LEGAL:  $strOut = 'Legal (216 x 356 mm)';  break;
         case CENUM_PDFPSIZE_A3:     $strOut = 'A3 (297 x 420 mm)';     break;
         case CENUM_PDFPSIZE_A4:     $strOut = 'A4 (210 x 297 mm)';     break;
         case CENUM_PDFPSIZE_A5:     $strOut = 'A5 (148 x 210 mm)';     break;
         default: $strOut = '#error#';  break;
      }
   }else {
      switch ($enumPaperSize){
         case CENUM_PDFPSIZE_LETTER: $strOut = 'Letter (8.5 x 11 inches)'; break;
         case CENUM_PDFPSIZE_LEGAL:  $strOut = 'Legal (8.5 x 14 inches)';  break;
         case CENUM_PDFPSIZE_A3:     $strOut = 'A3 (11.7 x 16.5 inches)';  break;
         case CENUM_PDFPSIZE_A4:     $strOut = 'A4 (8.3 x 11.7 inches)';   break;
         case CENUM_PDFPSIZE_A5:     $strOut = 'A5 (5.8 x 8.3 inches)';    break;
         default: $strOut = '#error#';  break;
      }
   }
   return($strOut);
}

function paperSizeDimPts($enumPaperSize, &$width, &$height){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   switch ($enumPaperSize){
      case CENUM_PDFPSIZE_LETTER:   $width =  8.5 * 72;   $height = 11.0 * 72; break;
      case CENUM_PDFPSIZE_LEGAL:    $width =  8.5 * 72;   $height = 14.0 * 72; break;
      case CENUM_PDFPSIZE_A3:       $width = 11.7 * 72;   $height = 16.5 * 72; break;
      case CENUM_PDFPSIZE_A4:       $width =  8.3 * 72;   $height = 11.7 * 72; break;
      case CENUM_PDFPSIZE_A5:       $width =  5.8 * 72;   $height =  8.3 * 72; break;
      default: $width = $height = null;  break;
   }
}

function strPaperSizeDDL($strDDLName, $bAddBlank, $enumMatch, $bMetric){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $strOut = '<select name="'.$strDDLName.'">'."\n";
   
   if ($bAddBlank){
      $strOut .= '<option value="-1">&nbsp;</option>'."\n";
   }   

   $strOut .= '<option value="'.CENUM_PDFPSIZE_LETTER.'" '.($enumMatch==CENUM_PDFPSIZE_LETTER ? 'SELECTED' : '').' >'.strXlatePaperSize(CENUM_PDFPSIZE_LETTER, $bMetric).'</option>
               <option value="'.CENUM_PDFPSIZE_LEGAL .'" '.($enumMatch==CENUM_PDFPSIZE_LEGAL  ? 'SELECTED' : '').' >'.strXlatePaperSize(CENUM_PDFPSIZE_LEGAL,  $bMetric).'</option>
               <option value="'.CENUM_PDFPSIZE_A3    .'" '.($enumMatch==CENUM_PDFPSIZE_A3     ? 'SELECTED' : '').' >'.strXlatePaperSize(CENUM_PDFPSIZE_A3,     $bMetric).'</option>
               <option value="'.CENUM_PDFPSIZE_A4    .'" '.($enumMatch==CENUM_PDFPSIZE_A4     ? 'SELECTED' : '').' >'.strXlatePaperSize(CENUM_PDFPSIZE_A4,     $bMetric).'</option>
               <option value="'.CENUM_PDFPSIZE_A5    .'" '.($enumMatch==CENUM_PDFPSIZE_A5     ? 'SELECTED' : '').' >'.strXlatePaperSize(CENUM_PDFPSIZE_A5,     $bMetric).'</option>'."\n";
   
   $strOut .= '</select>'."\n";
   return($strOut);

}

function optimumImageWidth(&$imgSize, $maxWidth, $maxHeight, &$sngAspect, &$sngOptHeight){
/*
   This function examines the aspect ratio of an image and return the appropriate
   width based on the maxWidth/Height
   
   $imgSize = getimagesize($strFN, $imageinfo);
*/
   $width  = $imgSize[0];
   $height = $imgSize[1];
   if ($maxHeight <= 0.001) return(null);
   if ($height <= 0.001) return(null);
   
   $sngAspect = $width/$height;
   $sngTargetAR = $maxWidth/$maxHeight;
   if ($sngAspect >= $sngTargetAR){
      $sngOptHeight = ($maxWidth/$width)*$height;
      return($maxWidth);
   }else {
      $sngOptHeight = $maxHeight;
      return(($maxHeight/$height)*$width);
   }
}

function lFontSizeThatFits(&$cPDF, $family, $style, $lMaxFS, $lMinFS, $lMaxWidth, $strTest, &$lWidth){
   $cnt = 0;
   for ($lFS = $lMaxFS; $lFS >= $lMinFS; --$lFS){
      $cPDF->SetFont($family, $style, $lFS);
      $lWidth = $cPDF->GetStringWidth($strTest);
      if ($lWidth <= $lMaxWidth){
         return($lFS);
      }
   
      ++$cnt;
      if ($cnt > 200){
         echo(__FILE__.' '.__LINE__.' runaway train!<br>'."\n"); die;
      }
   }
   return (null);
}


function devLongCell(){
   require_once('./application/libraries/fpdf/fpdf.php');

   $myPDF = new FPDF('Portrait', 'pt');
   $myPDF->AddPage();

   $myPDF->SetFont('Arial','', 12);
   $strText = 
'
Gone with the Wind is a novel written by Margaret Mitchell, first published in 1936. It is often placed in the literary sub-genre of the historical romance novel.[2] However, it has been argued the novel is a "near miss" and does not contain all of the elements of the romance genre,[3] making it simply a historical novel.

The story is set in Clayton County, Georgia, and Atlanta during the American Civil War and Reconstruction. It depicts the experiences of Scarlett O\'Hara, the spoiled daughter of a well-to-do plantation owner, who must use every means at her disposal to come out of the poverty she finds herself in after Sherman\'s "March to the Sea".

Gone with the Wind takes place in the southern United States in the state of Georgia during the American Civil War (1861–1865) and the Reconstruction Era (1865–1877) that followed the war. The novel unfolds against the backdrop of rebellion wherein seven southern states, Georgia among them, have declared their secession from the United States (the "Union") and formed the Confederate States of America (the "Confederacy"), after Abraham Lincoln was elected president with no ballots from ten Southern states where slavery was legal. A dispute over states\' rights has arisen[11] involving enslaved African people who were the source of manual labor on cotton plantations throughout the South. The story opens in April 1861 at the "Tara" plantation, which is owned by a wealthy Irish immigrant family, the O\'Haras. The reader is told Scarlett O\'Hara, the sixteen-year-old daughter of Gerald and Ellen O\'Hara, "was not beautiful, but"[12] had an effect on men, especially when she took notice of them. It is the day before the men are called to war, Fort Sumter having been fired on two days earlier.

There are brief but vivid descriptions of the South as it began and grew, with backgrounds of the main characters: the stylish and highbrow French, the gentlemanly English, the forced-to-flee and looked-down-upon Irish. Miss Scarlett learns that one of her many beaux, Ashley Wilkes, is soon to be engaged to his cousin, Melanie Hamilton. She is stricken at heart. The following day at the Wilkeses\' barbecue at "Twelve Oaks," Scarlett informs Ashley she loves him and Ashley admits he cares for her.[11] However, he knows he would not be happily married to Scarlett because of their personality differences. Scarlett loses her temper at Ashley and he silently takes it.
Then Scarlett meets Rhett Butler, a man who wears "the clothes of a dandy"[13] and has a reputation as a rogue. Rhett had been alone in the library when Ashley and Scarlett entered, and felt it wiser to not make his presence known while the argument took place. Rhett applauds Scarlett for the unladylike spirit she displayed with Ashley. Infuriated and humiliated, Scarlett tells Rhett, "You aren\'t fit to wipe Ashley\'s boots!"[11]

Upon leaving the library and rejoining the other party guests, she finds out that war has been declared and the men are going to enlist. Seeking revenge for being jilted by Ashley, Scarlett accepts a proposal of marriage from Melanie\'s brother, Charles Hamilton. They marry two weeks later. Charles dies from measles two months after the war begins. Scarlett is pregnant with her first child. A widow at merely sixteen, she gives birth to a boy, Wade Hampton Hamilton, named after his father\'s general.[14] As a widow, she is bound by tradition to wear black and avoid conversation with young men. Scarlett is despondent as a result of the restrictions placed upon her.
Aunt Pittypat, who is living with Melanie in Atlanta, invites Scarlett to stay with them. In Atlanta, Scarlett\'s spirits revive and she is busy with hospital work and sewing circles for the Confederate army. Scarlett encounters Rhett Butler again at a dance for the Confederacy. Although Rhett believes the war is a lost cause, he is blockade running for the profit in it. The men must bid for a dance with a lady and Rhett bids "one hundred fifty dollars-in gold"[13] for a dance with Scarlett. Everyone at the dance is shocked that Rhett would bid for Scarlett, the widow still dressed in black. Melanie comes to Scarlett\'s defense because she is supporting the Cause for which her husband, Ashley, is fighting.

At Christmas (1863), Ashley has been granted a furlough from the army and returns to Atlanta to be with Melanie. The war is going badly for the Confederacy. Atlanta is under siege (September 1864), "hemmed in on three sides,"[15] it descends into a desperate state while hundreds of wounded Confederate soldiers lie dying or dead in the city. Melanie goes into labor with only the inexperienced Scarlett to assist, as all the doctors are busy attending the soldiers. Prissy, a young Negro servant girl, cries out in despair and fear, "De Yankees is comin!"[16] In the chaos, Scarlett, left to fend for herself, cries for the comfort and safety of her mother and Tara. The tattered Confederate States Army sets flame to Atlanta as they abandon it to the Union Army.
Melanie gives birth to a boy she names "Beau", and now they must hurry for refuge. Scarlett tells Prissy to go find Rhett, but she is afraid to "go runnin\' roun\' in de dahk". Scarlett replies to Prissy, "Haven\'t you any gumption?"[16] Prissy then finds Rhett, and Scarlett begs him to take herself, Wade, Melanie, Beau, and Prissy to Tara. Rhett laughs at the idea, but steals an emaciated horse and a small wagon, and they follow the retreating army out of Atlanta.

Part way to Tara, Rhett has a change of heart and he abandons Scarlett to enlist in the army. Scarlett makes her way to Tara where she is welcomed on the steps by her father, Gerald. It is clear things have drastically changed: Gerald has lost his mind, Scarlett\'s mother is dead, her sisters are sick with typhoid fever, the field slaves left after Emancipation, the Yankees have burned all the cotton and there is no food in the house.

The long tiring struggle for post-war survival begins that has Scarlett working in the fields. There are hungry people to feed and little food. There is the ever present threat of the Yankees who steal and burn, and at one point, Scarlett kills a Yankee marauder with a single shot from Charles\'s pistol leaving "a bloody pit where the nose had been."[17]

A long succession of Confederate soldiers returning home stop at Tara to find food and rest. Two men stay on, an invalid Cracker, Will Benteen, and Ashley Wilkes, whose spirit is broken. Life at Tara slowly begins to recover when a new threat appears in the form of new taxes on Tara.

Scarlett knows only one man who has enough money to help her pay the taxes, Rhett Butler. She goes to Atlanta to find him only to learn Rhett is in jail. As she is leaving the jailhouse, Scarlett runs into Frank Kennedy, who is betrothed to Scarlett\'s sister, Suellen, and running a store in Atlanta. Soon realizing Frank also has money, Scarlett hatches a plot and tells Frank that Suellen has changed her mind about marrying him. Thereafter Frank succumbs to Scarlett\'s feminine charms and he marries her two weeks later knowing he has done "something romantic and exciting for the first time in his life."[18] Always wanting Scarlett to be happy and radiant, Frank gives her the money to pay the taxes on Tara.

While Frank has a cold and is being pampered by Aunt Pittypat, Scarlett goes over the accounts at Frank\'s store and finds many of his friends owe him money. Scarlett is now terrified about the taxes and decides money, a lot of it, is needed. She takes control of his business while he is away and her business practices leave many Atlantans resentful of her. Then with a loan from Rhett she buys a sawmill and runs the lumber business herself, all very unladylike conduct. Much to Frank\'s relief, Scarlett learns she is pregnant, which curtails her activities for a while. She convinces Ashley to come to Atlanta and manage the mill, all the while still in love with him. At Melanie\'s urging, Ashley takes the job at the mill. Melanie soon becomes the center of Atlanta society, and Scarlett gives birth to a girl named Ella Lorena. "Ella for her grandmother Ellen, and Lorena because it was the most fashionable name of the day for girls."[19]

The state of Georgia is under martial law and life there has taken on a new and more frightening tone. For protection, Scarlett keeps Frank\'s pistol tucked in the upholstery of the buggy. Her trips alone to and from the mill take her past a shanty town where criminal elements live. On one evening when she is coming home from the mill, Scarlett is accosted by two men who attempt to rob her, but she escapes with the help of Big Sam, the former negro foreman from Tara. Attempting to avenge the assault on his wife, Frank and the Ku Klux Klan raid the shanty town whereupon Frank is shot dead. Scarlett is a widow for a second time.
';   
   
   $myPDF->MultiCell(0, 13, $strText);
   $myPDF->Output();
die;
}

