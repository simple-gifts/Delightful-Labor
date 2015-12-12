<?php
/*---------------------------------------------------------------------
 Delightful Labor!

 copyright (c) 2012-2013 by Database Austin

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
   thanks to
      http://ellislab.com/codeigniter%20/user-guide/helpers/captcha_helper.html
---------------------------------------------------------------------
      $this->load->model('util/mcaptcha', 'clsCaptcha');
---------------------------------------------------------------------*/
class mcaptcha extends CI_Model{



   function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
		parent::__construct();
   }



   function setCaptchaDBEntry(&$vals, &$cap){
   /*---------------------------------------------------------------------
         $vals = array(
             'word'	     => 'Random word',
             'img_path'	  => './captcha/',
             'img_url'	  => 'http://example.com/captcha/',
             'font_path'  => './path/to/fonts/texb.ttf',
             'img_width'  => '150',
             'img_height' => 30,
             'expiration' => 7200
             );

         image html returned in $cap['image']

   --------------------------------------------------------------------- */
      $cap = create_captcha($vals);
 
         // thanks to http://stackoverflow.com/questions/1703320/remove-excess-whitespace-from-within-a-string
         // the reason the word is being modified is to assist the user if he/she
         // is confused by blanks and case
      $cap['word'] = strtolower(preg_replace( '/\s+/', '', $cap['word']));
      $data = array(
          'captcha_time' => $cap['time'],
          'ip_address'	 => $this->input->ip_address(),
          'word'         => $cap['word']
          );

      $query = $this->db->insert_string('captcha', $data);
      $this->db->query($query);
   }
   
   function bVerifyCaptchaEntry($strText){
   //---------------------------------------------------------------------
   // http://ellislab.com/codeigniter/user-guide/helpers/captcha_helper.html
   //---------------------------------------------------------------------   
         // First, delete old captchas
      $expiration = time()-7200; // Two hour limit
      $this->db->query("DELETE FROM captcha WHERE captcha_time < ".$expiration);	

      $strText = strtolower(preg_replace( '/\s+/', '', $strText));      
      
         // Then see if a captcha exists:
      $sql = "SELECT COUNT(*) AS count FROM captcha WHERE word = ? AND ip_address = ? AND captcha_time > ?";
      $binds = array($strText, $this->input->ip_address(), $expiration);
      $query = $this->db->query($sql, $binds);
      $row = $query->row();

      return($row->count > 0);
   }

   function strRandomWord(){
   //---------------------------------------------------------------------
   // thanks to
   // http://stackoverflow.com/questions/1216010/best-way-to-get-a-random-word-for-a-captcha-script-in-php
   //---------------------------------------------------------------------
      $words = array(         'abracadabra',  'apple',     'apparition', 'arugula', 'askew', 'audacious',
         'balderdash',   'bambino',   'bauble', 'beanie', 'bellow', 'bigwig', 'boatswain',
         'boggle',       'bombastic', 'boobalah', 'boogaloo', 'boomerang', 'bouffant', 'bouyant',
         'brainstorm',   'brouhaha',  'bungee', 'caboose', 'cahoots', 'camouflage', 'canoodle',
         'cantankerous', 'cello',     'cheeky', 'chicanery', 'chipper', 'chortle',
         'chuckwagon',   'cobweb',    'corny', 'crackpot', 'cummerbund', 'curmudgeon', 'dashing',
         'dawdle',       'dazzling',  'debonair', 'doodad', 'doohickey', 'dovetail',
         'drench',       'dropkick',  'dust bunny', 'earshot', 'effervescent', 'enchilada',
         'entourage',    'erroneous', 'festoon', 'fickle', 'fiddaddle',
         'fiddlesticks', 'fidgety',   'filibuster', 'flabbergasted', 'flambe',
         'flummox',      'foghorn',   'fracas', 'fritter', 'frolic', 'gadabout',
         'gallivant', 'galoshes', 'gargoyle', 'gazebo', 'glitzy', 'glockenspiel', 'goblin',
         'goose', 'goosebumps', 'gossamer', 'goulash', 'graffiti', 'greenhorn', 'gremlin',
         'gridlock', 'guacamole', 'guestimate', 'ragamuffin', 'rambunctious',
         'ramshackled', 'rapscallion', 'rascal', 'rattletrap', 'razzmatazz',
         'relish',  'rhubarb', 'rickety', 'rigmarole', 'ritzy', 'rubberneck',
         'ruckus', 'ruffian', 'sappy', 'sasquatch', 'sassafras', 'saucy', 'sauerkraut',
         'scarecrow', 'schnitzel', 'scooch', 'scoot', 'scoundrel', 'scram', 'scramble',
         'scruffy', 'scrumptious', 'scuttlebutt', 'shanghai', 'shazam', 'shoofly', 'shrill',
         'shrimp', 'sidekick', 'sidewinder', 'siesta', 'skedaddle', 'slacker', 'sloop', 'smidgen',
         'smithereens', 'smitten', 'smooch', 'smorgasbord', 'snailmail', 'snazzy', 'snickerdoodle',
         'snoop', 'snorefest', 'sockpuppet', 'souffle', 'spelunking', 'spigot',
         'splashy', 'spiffy', 'spittoon', 'splayed', 'splitsville', 'sprocket', 'spurious',
         'squeamish', 'squeegee', 'stupefy', 'succotash', 'sumptuous', 'swabbie',
         'swashbuckling', 'swoon', 'tattletale', 'teehee', 'tinsel',
         'tiptoe', 'tizzy', 'toboggan', 'tomfoolery', 'toupee', 'traipse', 'treacherous',
         'trilling', 'truculent', 'turncoat', 'tussle', 'tycoon', 'ukulele', 'vagabond',
         'vamoose', 'vapor', 'vaporware', 'verbose', 'vexed', 'vivacious', 'violin', 'voila',
         'vortex', 'vroom', 'wackadoodle', 'wacky', 'wallop', 'warthog', 'werewolf',
         'whinny', 'whirligigs', 'whirlpool', 'windjammer', 'wingding', 'woebegone',
         'wombat', 'wrangle', 'wretched', 'xylophone', 'yammer', 'yodel', 'yummy', 'zamboni',
         'zealot', 'zepplin', 'zigzag', 'zombie');

      return(($words[mt_rand(0, count($words)-1)].' '.random_string('nozero', 4)));
   }
   
   
   
}
      
      
      
      