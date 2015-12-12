<?php
/*
      $this->load->helper('dl_util/time_duration_helper');
*/
   namespace tdh;

   function sngXlateDuration($enumDuration, $bFreakIfNotFound = false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      switch ($enumDuration){
         case '(all day)':          return( 8.00); break;
         
         case'15 minutes':          return( 0.25); break;
         case'30 minutes':          return( 0.50); break;
         case'45 minutes':          return( 0.75); break;
         case'1 hour':              return( 1.00); break;
         case'1 hour 15 minutes':   return( 1.25); break;
         case'1 hour 30 minutes':   return( 1.50); break;
         case'1 hour 45 minutes':   return( 1.75); break;
         case'2 hours':             return( 2.00); break;
         case'2 hours 15 minutes':  return( 2.25); break;
         case'2 hours 30 minutes':  return( 2.50); break;
         case'2 hours 45 minutes':  return( 2.75); break;
         case'3 hours':             return( 3.00); break;
         case'3 hours 15 minutes':  return( 3.25); break;
         case'3 hours 30 minutes':  return( 3.50); break;
         case'3 hours 45 minutes':  return( 3.75); break;
         case'4 hours':             return( 4.00); break;
         case'4 hours 15 minutes':  return( 4.25); break;
         case'4 hours 30 minutes':  return( 4.50); break;
         case'4 hours 45 minutes':  return( 4.75); break;
         case'5 hours':             return( 5.00); break;
         case'5 hours 15 minutes':  return( 5.25); break;
         case'5 hours 30 minutes':  return( 5.50); break;
         case'5 hours 45 minutes':  return( 5.75); break;
         case'6 hours':             return( 6.00); break;
         case'6 hours 15 minutes':  return( 6.25); break;
         case'6 hours 30 minutes':  return( 6.50); break;
         case'6 hours 45 minutes':  return( 6.75); break;
         case'7 hours':             return( 7.00); break;
         case'7 hours 15 minutes':  return( 7.25); break;
         case'7 hours 30 minutes':  return( 7.50); break;
         case'7 hours 45 minutes':  return( 7.75); break;
         case'8 hours':             return( 8.00); break;
         case'8 hours 15 minutes':  return( 8.25); break;
         case'8 hours 30 minutes':  return( 8.50); break;
         case'8 hours 45 minutes':  return( 8.75); break;
         case'9 hours':             return( 9.00); break;
         case'9 hours 15 minutes':  return( 9.25); break;
         case'9 hours 30 minutes':  return( 9.50); break;
         case'9 hours 45 minutes':  return( 9.75); break;
         case'10 hours':            return(10.00); break;
         case'10 hours 15 minutes': return(10.25); break;
         case'10 hours 30 minutes': return(10.50); break;
         case'10 hours 45 minutes': return(10.75); break;
         case'11 hours':            return(11.00); break;
         case'11 hours 15 minutes': return(11.25); break;
         case'11 hours 30 minutes': return(11.50); break;
         case'11 hours 45 minutes': return(11.75); break;
         case'12 hours':            return(12.00); break;
         case'12 hours 15 minutes': return(12.25); break;
         case'12 hours 30 minutes': return(12.50); break;
         case'12 hours 45 minutes': return(12.75); break;
         case'13 hours':            return(13.00); break;
         case'13 hours 15 minutes': return(13.25); break;
         case'13 hours 30 minutes': return(13.50); break;
         case'13 hours 45 minutes': return(13.75); break;
         case'14 hours':            return(14.00); break;
         case'14 hours 15 minutes': return(14.25); break;
         case'14 hours 30 minutes': return(14.50); break;
         case'14 hours 45 minutes': return(14.75); break;
         case'15 hours':            return(15.00); break;
         case'15 hours 15 minutes': return(15.25); break;
         case'15 hours 30 minutes': return(15.50); break;
         case'15 hours 45 minutes': return(15.75); break;
         case'16 hours':            return(16.00); break;
         case'16 hours 15 minutes': return(16.25); break;
         case'16 hours 30 minutes': return(16.50); break;
         case'16 hours 45 minutes': return(16.75); break;
         case'17 hours':            return(17.00); break;
         case'17 hours 15 minutes': return(17.25); break;
         case'17 hours 30 minutes': return(17.50); break;
         case'17 hours 45 minutes': return(17.75); break;
         case'18 hours':            return(18.00); break;
         case'18 hours 15 minutes': return(18.25); break;
         case'18 hours 30 minutes': return(18.50); break;
         case'18 hours 45 minutes': return(18.75); break;
         case'19 hours':            return(19.00); break;
         case'19 hours 15 minutes': return(19.25); break;
         case'19 hours 30 minutes': return(19.50); break;
         case'19 hours 45 minutes': return(19.75); break;
         case'20 hours':            return(20.00); break;
         case'20 hours 15 minutes': return(20.25); break;
         case'20 hours 30 minutes': return(20.50); break;
         case'20 hours 45 minutes': return(20.75); break;
         case'21 hours':            return(21.00); break;
         case'21 hours 15 minutes': return(21.25); break;
         case'21 hours 30 minutes': return(21.50); break;
         case'21 hours 45 minutes': return(21.75); break;
         case'22 hours':            return(22.00); break;
         case'22 hours 15 minutes': return(22.25); break;
         case'22 hours 30 minutes': return(22.50); break;
         case'22 hours 45 minutes': return(22.75); break;
         case'23 hours':            return(23.00); break;
         case'23 hours 15 minutes': return(23.25); break;
         case'23 hours 30 minutes': return(23.50); break;
         case'23 hours 45 minutes': return(23.75); break;
         default:
            if ($bFreakIfNotFound){
               screamForHelp($enumDuration.': invalid duration detected<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            }
            break;
      }                                    
   }

