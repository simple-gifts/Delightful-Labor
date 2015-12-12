<?php
/*
      $this->load->helper('admin/upgrade_tz');
*/

   function upgrade_addTimeZoneTables(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $CI =& get_instance();
/*
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$CI->config->item('dl_timezone') = ".$CI->config->item('dl_timezone')."<br></font>\n");
die;
*/

      $sqlStr = "DROP TABLE IF EXISTS lists_tz;";
      $CI->db->query($sqlStr);

      $sqlStr =
        "CREATE TABLE IF NOT EXISTS lists_tz (
           tz_lKeyID      int(11) NOT NULL AUTO_INCREMENT,
           tz_strTimeZone varchar(120) NOT NULL DEFAULT '',
           tz_lTZ_Const   int(11) NOT NULL,
           tz_bTopList    tinyint(1) NOT NULL DEFAULT '0' ,
           PRIMARY KEY (tz_lKeyID),
           KEY tz_strTimeZone (tz_strTimeZone)

         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1
         COMMENT='php time zone constants via http://pecl.php.net/get/timezonedb'
         AUTO_INCREMENT=1;";
      $CI->db->query($sqlStr);
      $sqlStr =
"INSERT INTO lists_tz (tz_strTimeZone, tz_lTZ_Const, tz_bTopList) VALUES
   ('US/Eastern'         , 0x03E4DA , 1),    ('US/Central'         , 0x03DFCF , 1),    ('US/Mountain'        , 0x03F35C , 1),
   ('US/Pacific'         , 0x03F6D5 , 1),    ('US/Alaska'          , 0x03D872 , 1),    ('US/Hawaii'          , 0x03EC43 , 1),

	('Canada/Atlantic'                   , 0x02617D , 1),	('Canada/Central'                    , 0x026665 , 1),
	('Canada/East-Saskatchewan'          , 0x026F6F , 1),	('Canada/Eastern'                    , 0x026A7F , 1),
	('Canada/Mountain'                   , 0x0270F8 , 1),	('Canada/Newfoundland'               , 0x02746E , 1),
	('Canada/Pacific'                    , 0x027999 , 1),	('Canada/Saskatchewan'               , 0x027DB2 , 1),
	('Canada/Yukon'                      , 0x027F3B , 1),

	( 'Africa/Abidjan'                    , 0x000000 , 0),	( 'Africa/Accra'                      , 0x000055 , 0),	( 'Africa/Addis_Ababa'                , 0x00019D , 0),	( 'Africa/Algiers'                    , 0x00021C , 0),
	( 'Africa/Asmara'                     , 0x000347 , 0),	( 'Africa/Asmera'                     , 0x0003C6 , 0),	( 'Africa/Bamako'                     , 0x000445 , 0),	( 'Africa/Bangui'                     , 0x00049A , 0),
	( 'Africa/Banjul'                     , 0x0004EF , 0),	( 'Africa/Bissau'                     , 0x000544 , 0),	( 'Africa/Blantyre'                   , 0x0005AA , 0),	( 'Africa/Brazzaville'                , 0x0005FF , 0),
	( 'Africa/Bujumbura'                  , 0x000654 , 0),	( 'Africa/Cairo'                      , 0x0006A9 , 0),	( 'Africa/Casablanca'                 , 0x000A90 , 0),	( 'Africa/Ceuta'                      , 0x000CF2 , 0),
	( 'Africa/Conakry'                    , 0x000FF9 , 0),	( 'Africa/Dakar'                      , 0x00104E , 0),	( 'Africa/Dar_es_Salaam'              , 0x0010A3 , 0),	( 'Africa/Djibouti'                   , 0x001122 , 0),
	( 'Africa/Douala'                     , 0x0011A1 , 0),	( 'Africa/El_Aaiun'                   , 0x0011F6 , 0),	( 'Africa/Freetown'                   , 0x001421 , 0),	( 'Africa/Gaborone'                   , 0x001476 , 0),
	( 'Africa/Harare'                     , 0x0014CB , 0),	( 'Africa/Johannesburg'               , 0x001520 , 0),	( 'Africa/Juba'                       , 0x00158E , 0),	( 'Africa/Kampala'                    , 0x0016A1 , 0),
	( 'Africa/Khartoum'                   , 0x001720 , 0),	( 'Africa/Kigali'                     , 0x001833 , 0),	( 'Africa/Kinshasa'                   , 0x001888 , 0),	( 'Africa/Lagos'                      , 0x0018F4 , 0),
	( 'Africa/Libreville'                 , 0x001949 , 0),	( 'Africa/Lome'                       , 0x00199E , 0),	( 'Africa/Luanda'                     , 0x0019F3 , 0),	( 'Africa/Lubumbashi'                 , 0x001A48 , 0),
	( 'Africa/Lusaka'                     , 0x001AB4 , 0),	( 'Africa/Malabo'                     , 0x001B09 , 0),	( 'Africa/Maputo'                     , 0x001B5E , 0),	( 'Africa/Maseru'                     , 0x001BB3 , 0),
	( 'Africa/Mbabane'                    , 0x001C21 , 0),	( 'Africa/Mogadishu'                  , 0x001C8F , 0),	( 'Africa/Monrovia'                   , 0x001D0E , 0),	( 'Africa/Nairobi'                    , 0x001D74 , 0),
	( 'Africa/Ndjamena'                   , 0x001DF3 , 0),	( 'Africa/Niamey'                     , 0x001E5F , 0),	( 'Africa/Nouakchott'                 , 0x001EB4 , 0),	( 'Africa/Ouagadougou'                , 0x001F09 , 0),
	( 'Africa/Porto-Novo'                 , 0x001F5E , 0),	( 'Africa/Sao_Tome'                   , 0x001FB3 , 0),	( 'Africa/Timbuktu'                   , 0x002008 , 0),	( 'Africa/Tripoli'                    , 0x00205D , 0),
	( 'Africa/Tunis'                      , 0x002166 , 0),	( 'Africa/Windhoek'                   , 0x002278 , 0),	( 'America/Adak'                      , 0x0024BF , 0),	( 'America/Anchorage'                 , 0x002835 , 0),
	( 'America/Anguilla'                  , 0x002BA9 , 0),	( 'America/Antigua'                   , 0x002BFE , 0),	( 'America/Araguaina'                 , 0x002C53 , 0),	( 'America/Argentina/Buenos_Aires'    , 0x002DB8 , 0),
	( 'America/Argentina/Catamarca'       , 0x002F66 , 0),	( 'America/Argentina/ComodRivadavia'  , 0x003127 , 0),	( 'America/Argentina/Cordoba'         , 0x0032CD , 0),	( 'America/Argentina/Jujuy'           , 0x0034A2 , 0),
	( 'America/Argentina/La_Rioja'        , 0x003656 , 0),	( 'America/Argentina/Mendoza'         , 0x00380E , 0),	( 'America/Argentina/Rio_Gallegos'    , 0x0039CE , 0),	( 'America/Argentina/Salta'           , 0x003B83 , 0),
	( 'America/Argentina/San_Juan'        , 0x003D2F , 0),	( 'America/Argentina/San_Luis'        , 0x003EE7 , 0),	( 'America/Argentina/Tucuman'         , 0x0040AD , 0),	( 'America/Argentina/Ushuaia'         , 0x004269 , 0),
	( 'America/Aruba'                     , 0x004424 , 0),	( 'America/Asuncion'                  , 0x00448A , 0),	( 'America/Atikokan'                  , 0x00476F , 0),	( 'America/Atka'                      , 0x004845 , 0),
	( 'America/Bahia'                     , 0x004BAB , 0),	( 'America/Bahia_Banderas'            , 0x004D3E , 0),	( 'America/Barbados'                  , 0x004FB7 , 0),	( 'America/Belem'                     , 0x005051 , 0),
	( 'America/Belize'                    , 0x00514C , 0),	( 'America/Blanc-Sablon'              , 0x0052C8 , 0),	( 'America/Boa_Vista'                 , 0x00537C , 0),	( 'America/Bogota'                    , 0x005485 , 0),
	( 'America/Boise'                     , 0x0054F1 , 0),	( 'America/Buenos_Aires'              , 0x005888 , 0),	( 'America/Cambridge_Bay'             , 0x005A21 , 0),	( 'America/Campo_Grande'              , 0x005D49 , 0),
	( 'America/Cancun'                    , 0x006038 , 0),	( 'America/Caracas'                   , 0x0061A2 , 0),	( 'America/Catamarca'                 , 0x006209 , 0),	( 'America/Cayenne'                   , 0x0063AF , 0),
	( 'America/Cayman'                    , 0x006411 , 0),	( 'America/Chicago'                   , 0x006466 , 0),	( 'America/Chihuahua'                 , 0x00697D , 0),	( 'America/Coral_Harbour'             , 0x006BE8 , 0),
	( 'America/Cordoba'                   , 0x006C7A , 0),	( 'America/Costa_Rica'                , 0x006E20 , 0),	( 'America/Creston'                   , 0x006EAA , 0),	( 'America/Cuiaba'                    , 0x006F36 , 0),
	( 'America/Curacao'                   , 0x007214 , 0),	( 'America/Danmarkshavn'              , 0x00727A , 0),	( 'America/Dawson'                    , 0x0073BE , 0),	( 'America/Dawson_Creek'              , 0x0076DB , 0),
	( 'America/Denver'                    , 0x0078B5 , 0),	( 'America/Detroit'                   , 0x007C3B , 0),	( 'America/Dominica'                  , 0x007F9A , 0),	( 'America/Edmonton'                  , 0x007FEF , 0),
	( 'America/Eirunepe'                  , 0x0083A7 , 0),	( 'America/El_Salvador'               , 0x0084BF , 0),	( 'America/Ensenada'                  , 0x008534 , 0),	( 'America/Fort_Wayne'                , 0x0089DB , 0),
	( 'America/Fortaleza'                 , 0x00889D , 0),	( 'America/Glace_Bay'                 , 0x008C45 , 0),	( 'America/Godthab'                   , 0x008FBC , 0),	( 'America/Goose_Bay'                 , 0x009280 , 0),
	( 'America/Grand_Turk'                , 0x00973D , 0),	( 'America/Grenada'                   , 0x00991C , 0),	( 'America/Guadeloupe'                , 0x009971 , 0),	( 'America/Guatemala'                 , 0x0099C6 , 0),
	( 'America/Guayaquil'                 , 0x009A4F , 0),	( 'America/Guyana'                    , 0x009AAC , 0),	( 'America/Halifax'                   , 0x009B2D , 0),	( 'America/Havana'                    , 0x00A043 , 0),
	( 'America/Hermosillo'                , 0x00A3B6 , 0),	( 'America/Indiana/Indianapolis'      , 0x00A494 , 0),	( 'America/Indiana/Knox'              , 0x00A725 , 0),	( 'America/Indiana/Marengo'           , 0x00AABC , 0),
	( 'America/Indiana/Petersburg'        , 0x00AD62 , 0),	( 'America/Indiana/Tell_City'         , 0x00B2AF , 0),	( 'America/Indiana/Vevay'             , 0x00B548 , 0),	( 'America/Indiana/Vincennes'         , 0x00B783 , 0),
	( 'America/Indiana/Winamac'           , 0x00BA37 , 0),	( 'America/Indianapolis'              , 0x00B045 , 0),	( 'America/Inuvik'                    , 0x00BCF0 , 0),	( 'America/Iqaluit'                   , 0x00BFE7 , 0),
	( 'America/Jamaica'                   , 0x00C309 , 0),	( 'America/Jujuy'                     , 0x00C3CE , 0),	( 'America/Juneau'                    , 0x00C578 , 0),	( 'America/Kentucky/Louisville'       , 0x00C8F6 , 0),
	( 'America/Kentucky/Monticello'       , 0x00CD14 , 0),	( 'America/Knox_IN'                   , 0x00D099 , 0),	( 'America/Kralendijk'                , 0x00D40A , 0),	( 'America/La_Paz'                    , 0x00D470 , 0),
	( 'America/Lima'                      , 0x00D4D7 , 0),	( 'America/Los_Angeles'               , 0x00D57F , 0),	( 'America/Louisville'                , 0x00D990 , 0),	( 'America/Lower_Princes'             , 0x00DD85 , 0),
	( 'America/Maceio'                    , 0x00DDEB , 0),	( 'America/Managua'                   , 0x00DF25 , 0),	( 'America/Manaus'                    , 0x00DFD8 , 0),	( 'America/Marigot'                   , 0x00E0DA , 0),
	( 'America/Martinique'                , 0x00E12F , 0),	( 'America/Matamoros'                 , 0x00E19B , 0),	( 'America/Mazatlan'                  , 0x00E3F4 , 0),	( 'America/Mendoza'                   , 0x00E661 , 0),
	( 'America/Menominee'                 , 0x00E815 , 0),	( 'America/Merida'                    , 0x00EB96 , 0),	( 'America/Metlakatla'                , 0x00EDD1 , 0),	( 'America/Mexico_City'               , 0x00EF0C , 0),
	( 'America/Miquelon'                  , 0x00F187 , 0),	( 'America/Moncton'                   , 0x00F3F9 , 0),	( 'America/Monterrey'                 , 0x00F890 , 0),	( 'America/Montevideo'                , 0x00FAF3 , 0),
	( 'America/Montreal'                  , 0x00FE05 , 0),	( 'America/Montserrat'                , 0x0102F5 , 0),	( 'America/Nassau'                    , 0x01034A , 0),	( 'America/New_York'                  , 0x01068F , 0),
	( 'America/Nipigon'                   , 0x010B9A , 0),	( 'America/Nome'                      , 0x010EEB , 0),	( 'America/Noronha'                   , 0x011269 , 0),	( 'America/North_Dakota/Beulah'       , 0x011399 , 0),
	( 'America/North_Dakota/Center'       , 0x01172D , 0),	( 'America/North_Dakota/New_Salem'    , 0x011AC1 , 0),	( 'America/Ojinaga'                   , 0x011E6A , 0),	( 'America/Panama'                    , 0x0120CB , 0),
	( 'America/Pangnirtung'               , 0x012120 , 0),	( 'America/Paramaribo'                , 0x012456 , 0),	( 'America/Phoenix'                   , 0x0124E8 , 0),	( 'America/Port-au-Prince'            , 0x0125A6 , 0),
	( 'America/Port_of_Spain'             , 0x0128CA , 0),	( 'America/Porto_Acre'                , 0x0127C6 , 0),	( 'America/Porto_Velho'               , 0x01291F , 0),	( 'America/Puerto_Rico'               , 0x012A15 , 0),
	( 'America/Rainy_River'               , 0x012A80 , 0),	( 'America/Rankin_Inlet'              , 0x012DB8 , 0),	( 'America/Recife'                    , 0x01309E , 0),	( 'America/Regina'                    , 0x0131C8 , 0),
	( 'America/Resolute'                  , 0x013386 , 0),	( 'America/Rio_Branco'                , 0x01366E , 0),	( 'America/Rosario'                   , 0x013776 , 0),	( 'America/Santa_Isabel'              , 0x01391C , 0),
	( 'America/Santarem'                  , 0x013CBF , 0),	( 'America/Santiago'                  , 0x013DC4 , 0),	( 'America/Santo_Domingo'             , 0x014094 , 0),	( 'America/Sao_Paulo'                 , 0x01415A , 0),
	( 'America/Scoresbysund'              , 0x014469 , 0),	( 'America/Shiprock'                  , 0x014757 , 0),	( 'America/Sitka'                     , 0x014AD0 , 0),	( 'America/St_Barthelemy'             , 0x014E58 , 0),
	( 'America/St_Johns'                  , 0x014EAD , 0),	( 'America/St_Kitts'                  , 0x015400 , 0),	( 'America/St_Lucia'                  , 0x015455 , 0),	( 'America/St_Thomas'                 , 0x0154AA , 0),
	( 'America/St_Vincent'                , 0x0154FF , 0),	( 'America/Swift_Current'             , 0x015554 , 0),	( 'America/Tegucigalpa'               , 0x015675 , 0),	( 'America/Thule'                     , 0x0156F4 , 0),
	( 'America/Thunder_Bay'               , 0x01593B , 0),	( 'America/Tijuana'                   , 0x015C84 , 0),	( 'America/Toronto'                   , 0x01601D , 0),	( 'America/Tortola'                   , 0x01653D , 0),
	( 'America/Vancouver'                 , 0x016592 , 0),	( 'America/Virgin'                    , 0x0169CF , 0),	( 'America/Whitehorse'                , 0x016A24 , 0),	( 'America/Winnipeg'                  , 0x016D41 , 0),
	( 'America/Yakutat'                   , 0x017181 , 0),	( 'America/Yellowknife'               , 0x0174EC , 0),	( 'Antarctica/Casey'                  , 0x0177FC , 0),	( 'Antarctica/Davis'                  , 0x01789A , 0),
	( 'Antarctica/DumontDUrville'         , 0x01793B , 0),	( 'Antarctica/Macquarie'              , 0x0179CC , 0),	( 'Antarctica/Mawson'                 , 0x017C19 , 0),	( 'Antarctica/McMurdo'                , 0x017C95 , 0),
	( 'Antarctica/Palmer'                 , 0x018040 , 0),	( 'Antarctica/Rothera'                , 0x018283 , 0),	( 'Antarctica/South_Pole'             , 0x0182F9 , 0),	( 'Antarctica/Syowa'                  , 0x018677 , 0),
	( 'Antarctica/Troll'                  , 0x0186E5 , 0),	( 'Antarctica/Vostok'                 , 0x0188B7 , 0),	( 'Arctic/Longyearbyen'               , 0x018928 , 0),	( 'Asia/Aden'                         , 0x018C5A , 0),
	( 'Asia/Almaty'                       , 0x018CAF , 0),	( 'Asia/Amman'                        , 0x018E2E , 0),	( 'Asia/Anadyr'                       , 0x0190E4 , 0),	( 'Asia/Aqtau'                        , 0x0192E6 , 0),
	( 'Asia/Aqtobe'                       , 0x0194E5 , 0),	( 'Asia/Ashgabat'                     , 0x01969D , 0),	( 'Asia/Ashkhabad'                    , 0x0197BA , 0),	( 'Asia/Baghdad'                      , 0x0198D7 , 0),
	( 'Asia/Bahrain'                      , 0x019A4C , 0),	( 'Asia/Baku'                         , 0x019AB2 , 0),	( 'Asia/Bangkok'                      , 0x019D9A , 0),	( 'Asia/Beirut'                       , 0x019DEF , 0),
	( 'Asia/Bishkek'                      , 0x01A0FC , 0),	( 'Asia/Brunei'                       , 0x01A2A8 , 0),	( 'Asia/Calcutta'                     , 0x01A30A , 0),	( 'Asia/Chita'                        , 0x01A383 , 0),
	( 'Asia/Choibalsan'                   , 0x01A598 , 0),	( 'Asia/Chongqing'                    , 0x01A7FF , 0),	( 'Asia/Chungking'                    , 0x01A89F , 0),	( 'Asia/Colombo'                      , 0x01A93F , 0),
	( 'Asia/Dacca'                        , 0x01A9DB , 0),	( 'Asia/Damascus'                     , 0x01AA81 , 0),	( 'Asia/Dhaka'                        , 0x01ADD1 , 0),	( 'Asia/Dili'                         , 0x01AE77 , 0),
	( 'Asia/Dubai'                        , 0x01AF01 , 0),	( 'Asia/Dushanbe'                     , 0x01AF56 , 0),	( 'Asia/Gaza'                         , 0x01B059 , 0),	( 'Asia/Harbin'                       , 0x01B3AC , 0),
	( 'Asia/Hebron'                       , 0x01B44C , 0),	( 'Asia/Ho_Chi_Minh'                  , 0x01B7A8 , 0),	( 'Asia/Hong_Kong'                    , 0x01B84A , 0),	( 'Asia/Hovd'                         , 0x01BA0C , 0),
	( 'Asia/Irkutsk'                      , 0x01BC6A , 0),	( 'Asia/Istanbul'                     , 0x01BE55 , 0),	( 'Asia/Jakarta'                      , 0x01C242 , 0),	( 'Asia/Jayapura'                     , 0x01C2EC , 0),
	( 'Asia/Jerusalem'                    , 0x01C389 , 0),	( 'Asia/Kabul'                        , 0x01C6B8 , 0),	( 'Asia/Kamchatka'                    , 0x01C709 , 0),	( 'Asia/Karachi'                      , 0x01C902 , 0),
	( 'Asia/Kashgar'                      , 0x01C9B7 , 0),	( 'Asia/Kathmandu'                    , 0x01CA0C , 0),	( 'Asia/Katmandu'                     , 0x01CA72 , 0),	( 'Asia/Khandyga'                     , 0x01CAD8 , 0),
	( 'Asia/Kolkata'                      , 0x01CD02 , 0),	( 'Asia/Krasnoyarsk'                  , 0x01CD7B , 0),	( 'Asia/Kuala_Lumpur'                 , 0x01CF68 , 0),	( 'Asia/Kuching'                      , 0x01D025 , 0),
	( 'Asia/Kuwait'                       , 0x01D113 , 0),	( 'Asia/Macao'                        , 0x01D168 , 0),	( 'Asia/Macau'                        , 0x01D2A3 , 0),	( 'Asia/Magadan'                      , 0x01D3DE , 0),
	( 'Asia/Makassar'                     , 0x01D5E2 , 0),	( 'Asia/Manila'                       , 0x01D6A7 , 0),	( 'Asia/Muscat'                       , 0x01D72C , 0),	( 'Asia/Nicosia'                      , 0x01D781 , 0),
	( 'Asia/Novokuznetsk'                 , 0x01DA69 , 0),	( 'Asia/Novosibirsk'                  , 0x01DC89 , 0),	( 'Asia/Omsk'                         , 0x01DE79 , 0),	( 'Asia/Oral'                         , 0x01E065 , 0),
	( 'Asia/Phnom_Penh'                   , 0x01E235 , 0),	( 'Asia/Pontianak'                    , 0x01E28A , 0),	( 'Asia/Pyongyang'                    , 0x01E34C , 0),	( 'Asia/Qatar'                        , 0x01E3D1 , 0),
	( 'Asia/Qyzylorda'                    , 0x01E437 , 0),	( 'Asia/Rangoon'                      , 0x01E60D , 0),	( 'Asia/Riyadh'                       , 0x01E685 , 0),	( 'Asia/Saigon'                       , 0x01E6DA , 0),
	( 'Asia/Sakhalin'                     , 0x01E77C , 0),	( 'Asia/Samarkand'                    , 0x01E979 , 0),	( 'Asia/Seoul'                        , 0x01EAAF , 0),	( 'Asia/Shanghai'                     , 0x01EBA2 , 0),
	( 'Asia/Singapore'                    , 0x01EC4E , 0),	( 'Asia/Srednekolymsk'                , 0x01ED05 , 0),	( 'Asia/Taipei'                       , 0x01EF05 , 0),	( 'Asia/Tashkent'                     , 0x01F036 , 0),
	( 'Asia/Tbilisi'                      , 0x01F167 , 0),	( 'Asia/Tehran'                       , 0x01F321 , 0),	( 'Asia/Tel_Aviv'                     , 0x01F58F , 0),	( 'Asia/Thimbu'                       , 0x01F8BE , 0),
	( 'Asia/Thimphu'                      , 0x01F924 , 0),	( 'Asia/Tokyo'                        , 0x01F98A , 0),	( 'Asia/Ujung_Pandang'                , 0x01FA14 , 0),	( 'Asia/Ulaanbaatar'                  , 0x01FA91 , 0),
	( 'Asia/Ulan_Bator'                   , 0x01FCD2 , 0),	( 'Asia/Urumqi'                       , 0x01FF05 , 0),	( 'Asia/Ust-Nera'                     , 0x01FF67 , 0),	( 'Asia/Vientiane'                    , 0x020179 , 0),
	( 'Asia/Vladivostok'                  , 0x0201CE , 0),	( 'Asia/Yakutsk'                      , 0x0203B8 , 0),	( 'Asia/Yekaterinburg'                , 0x0205A2 , 0),	( 'Asia/Yerevan'                      , 0x0207C3 , 0),
	( 'Atlantic/Azores'                   , 0x0209C3 , 0),	( 'Atlantic/Bermuda'                  , 0x020EC6 , 0),	( 'Atlantic/Canary'                   , 0x0211A7 , 0),	( 'Atlantic/Cape_Verde'               , 0x02147D , 0),
	( 'Atlantic/Faeroe'                   , 0x0214F6 , 0),	( 'Atlantic/Faroe'                    , 0x02179A , 0),	( 'Atlantic/Jan_Mayen'                , 0x021A3E , 0),	( 'Atlantic/Madeira'                  , 0x021D70 , 0),
	( 'Atlantic/Reykjavik'                , 0x022279 , 0),	( 'Atlantic/South_Georgia'            , 0x022446 , 0),	( 'Atlantic/St_Helena'                , 0x022658 , 0),	( 'Atlantic/Stanley'                  , 0x02248A , 0),
	( 'Australia/ACT'                     , 0x0226AD , 0),	( 'Australia/Adelaide'                , 0x0229D0 , 0),	( 'Australia/Brisbane'                , 0x022D02 , 0),	( 'Australia/Broken_Hill'             , 0x022DCF , 0),
	( 'Australia/Canberra'                , 0x023113 , 0),	( 'Australia/Currie'                  , 0x023436 , 0),	( 'Australia/Darwin'                  , 0x02376F , 0),	( 'Australia/Eucla'                   , 0x0237FB , 0),
	( 'Australia/Hobart'                  , 0x0238D7 , 0),	( 'Australia/LHI'                     , 0x023C3B , 0),	( 'Australia/Lindeman'                , 0x023EDC , 0),	( 'Australia/Lord_Howe'               , 0x023FC3 , 0),
	( 'Australia/Melbourne'               , 0x024274 , 0),	( 'Australia/North'                   , 0x02459F , 0),	( 'Australia/NSW'                     , 0x024619 , 0),	( 'Australia/Perth'                   , 0x02493C , 0),
	( 'Australia/Queensland'              , 0x024A1A , 0),	( 'Australia/South'                   , 0x024ACC , 0),	( 'Australia/Sydney'                  , 0x024DEF , 0),	( 'Australia/Tasmania'                , 0x025132 , 0),
	( 'Australia/Victoria'                , 0x02547D , 0),	( 'Australia/West'                    , 0x0257A0 , 0),	( 'Australia/Yancowinna'              , 0x02585C , 0),	( 'Brazil/Acre'                       , 0x025B84 , 0),
	( 'Brazil/DeNoronha'                  , 0x025C88 , 0),	( 'Brazil/East'                       , 0x025DA8 , 0),	( 'Brazil/West'                       , 0x026085 , 0),	( 'Canada/Atlantic'                   , 0x02617D , 0),
	( 'Canada/Central'                    , 0x026665 , 0),	( 'Canada/East-Saskatchewan'          , 0x026F6F , 0),	( 'Canada/Eastern'                    , 0x026A7F , 0),	( 'Canada/Mountain'                   , 0x0270F8 , 0),
	( 'Canada/Newfoundland'               , 0x02746E , 0),	( 'Canada/Pacific'                    , 0x027999 , 0),	( 'Canada/Saskatchewan'               , 0x027DB2 , 0),	( 'Canada/Yukon'                      , 0x027F3B , 0),
	( 'CET'                               , 0x02823E , 0),	( 'Chile/Continental'                 , 0x028547 , 0),	( 'Chile/EasterIsland'                , 0x028809 , 0),	( 'CST6CDT'                           , 0x028A72 , 0),
	( 'Cuba'                              , 0x028DC3 , 0),	( 'EET'                               , 0x029136 , 0),	( 'Egypt'                             , 0x0293E9 , 0),	( 'Eire'                              , 0x0297D0 , 0),
	( 'EST'                               , 0x029CE1 , 0),	( 'EST5EDT'                           , 0x029D25 , 0),	( 'Etc/GMT'                           , 0x02A076 , 0),	( 'Etc/GMT+0'                         , 0x02A142 , 0),
	( 'Etc/GMT+1'                         , 0x02A1CC , 0),	( 'Etc/GMT+10'                        , 0x02A259 , 0),	( 'Etc/GMT+11'                        , 0x02A2E7 , 0),	( 'Etc/GMT+12'                        , 0x02A375 , 0),
	( 'Etc/GMT+2'                         , 0x02A490 , 0),	( 'Etc/GMT+3'                         , 0x02A51C , 0),	( 'Etc/GMT+4'                         , 0x02A5A8 , 0),	( 'Etc/GMT+5'                         , 0x02A634 , 0),
	( 'Etc/GMT+6'                         , 0x02A6C0 , 0),	( 'Etc/GMT+7'                         , 0x02A74C , 0),	( 'Etc/GMT+8'                         , 0x02A7D8 , 0),	( 'Etc/GMT+9'                         , 0x02A864 , 0),
	( 'Etc/GMT-0'                         , 0x02A0FE , 0),	( 'Etc/GMT-1'                         , 0x02A186 , 0),	( 'Etc/GMT-10'                        , 0x02A212 , 0),	( 'Etc/GMT-11'                        , 0x02A2A0 , 0),
	( 'Etc/GMT-12'                        , 0x02A32E , 0),	( 'Etc/GMT-13'                        , 0x02A3BC , 0),	( 'Etc/GMT-14'                        , 0x02A403 , 0),	( 'Etc/GMT-2'                         , 0x02A44A , 0),
	( 'Etc/GMT-3'                         , 0x02A4D6 , 0),	( 'Etc/GMT-4'                         , 0x02A562 , 0),	( 'Etc/GMT-5'                         , 0x02A5EE , 0),	( 'Etc/GMT-6'                         , 0x02A67A , 0),
	( 'Etc/GMT-7'                         , 0x02A706 , 0),	( 'Etc/GMT-8'                         , 0x02A792 , 0),	( 'Etc/GMT-9'                         , 0x02A81E , 0),	( 'Etc/GMT0'                          , 0x02A0BA , 0),
	( 'Etc/Greenwich'                     , 0x02A8AA , 0),	( 'Etc/UCT'                           , 0x02A8EE , 0),	( 'Etc/Universal'                     , 0x02A932 , 0),	( 'Etc/UTC'                           , 0x02A976 , 0),
	( 'Etc/Zulu'                          , 0x02A9BA , 0),	( 'Europe/Amsterdam'                  , 0x02A9FE , 0),	( 'Europe/Andorra'                    , 0x02AE3C , 0),	( 'Europe/Athens'                     , 0x02B0B8 , 0),
	( 'Europe/Belfast'                    , 0x02B3FB , 0),	( 'Europe/Belgrade'                   , 0x02B932 , 0),	( 'Europe/Berlin'                     , 0x02BBFB , 0),	( 'Europe/Bratislava'                 , 0x02BF5F , 0),
	( 'Europe/Brussels'                   , 0x02C291 , 0),	( 'Europe/Bucharest'                  , 0x02C6C8 , 0),	( 'Europe/Budapest'                   , 0x02C9F2 , 0),	( 'Europe/Busingen'                   , 0x02CD5B , 0),
	( 'Europe/Chisinau'                   , 0x02D012 , 0),	( 'Europe/Copenhagen'                 , 0x02D3A0 , 0),	( 'Europe/Dublin'                     , 0x02D6AA , 0),	( 'Europe/Gibraltar'                  , 0x02DBBB , 0),
	( 'Europe/Guernsey'                   , 0x02E012 , 0),	( 'Europe/Helsinki'                   , 0x02E549 , 0),	( 'Europe/Isle_of_Man'                , 0x02E7FF , 0),	( 'Europe/Istanbul'                   , 0x02ED36 , 0),
	( 'Europe/Jersey'                     , 0x02F123 , 0),	( 'Europe/Kaliningrad'                , 0x02F65A , 0),	( 'Europe/Kiev'                       , 0x02F8C5 , 0),	( 'Europe/Lisbon'                     , 0x02FBE1 , 0),
	( 'Europe/Ljubljana'                  , 0x0300E5 , 0),	( 'Europe/London'                     , 0x0303AE , 0),	( 'Europe/Luxembourg'                 , 0x0308E5 , 0),	( 'Europe/Madrid'                     , 0x030D3B , 0),
	( 'Europe/Malta'                      , 0x031101 , 0),	( 'Europe/Mariehamn'                  , 0x0314BA , 0),	( 'Europe/Minsk'                      , 0x031770 , 0),	( 'Europe/Monaco'                     , 0x031983 , 0),
	( 'Europe/Moscow'                     , 0x031DBE , 0),	( 'Europe/Nicosia'                    , 0x032018 , 0),	( 'Europe/Oslo'                       , 0x032300 , 0),	( 'Europe/Paris'                      , 0x032632 , 0),
	( 'Europe/Podgorica'                  , 0x032A78 , 0),	( 'Europe/Prague'                     , 0x032D41 , 0),	( 'Europe/Riga'                       , 0x033073 , 0),	( 'Europe/Rome'                       , 0x0333B8 , 0),
	( 'Europe/Samara'                     , 0x03377B , 0),	( 'Europe/San_Marino'                 , 0x0339E4 , 0),	( 'Europe/Sarajevo'                   , 0x033DA7 , 0),	( 'Europe/Simferopol'                 , 0x034070 , 0),
	( 'Europe/Skopje'                     , 0x0342C1 , 0),	( 'Europe/Sofia'                      , 0x03458A , 0),	( 'Europe/Stockholm'                  , 0x034892 , 0),	( 'Europe/Tallinn'                    , 0x034B41 , 0),
	( 'Europe/Tirane'                     , 0x034E7B , 0),	( 'Europe/Tiraspol'                   , 0x035181 , 0),	( 'Europe/Uzhgorod'                   , 0x03550F , 0),	( 'Europe/Vaduz'                      , 0x035826 , 0),
	( 'Europe/Vatican'                    , 0x035AD5 , 0),	( 'Europe/Vienna'                     , 0x035E98 , 0),	( 'Europe/Vilnius'                    , 0x0361C5 , 0),	( 'Europe/Volgograd'                  , 0x036504 , 0),
	( 'Europe/Warsaw'                     , 0x036729 , 0),	( 'Europe/Zagreb'                     , 0x036B0A , 0),	( 'Europe/Zaporozhye'                 , 0x036DD3 , 0),	( 'Europe/Zurich'                     , 0x037114 , 0),
	( 'Factory'                           , 0x0373C3 , 0),	( 'GB'                                , 0x037434 , 0),	( 'GB-Eire'                           , 0x03796B , 0),	( 'GMT'                               , 0x037EA2 , 0),
	( 'GMT+0'                             , 0x037F6E , 0),	( 'GMT-0'                             , 0x037F2A , 0),	( 'GMT0'                              , 0x037EE6 , 0),	( 'Greenwich'                         , 0x037FB2 , 0),
	( 'Hongkong'                          , 0x037FF6 , 0),	( 'HST'                               , 0x0381B8 , 0),	( 'Iceland'                           , 0x0381FC , 0),	( 'Indian/Antananarivo'               , 0x0383C9 , 0),
	( 'Indian/Chagos'                     , 0x038448 , 0),	( 'Indian/Christmas'                  , 0x0384AA , 0),	( 'Indian/Cocos'                      , 0x0384EE , 0),	( 'Indian/Comoro'                     , 0x038532 , 0),
	( 'Indian/Kerguelen'                  , 0x0385B1 , 0),	( 'Indian/Mahe'                       , 0x038606 , 0),	( 'Indian/Maldives'                   , 0x03865B , 0),	( 'Indian/Mauritius'                  , 0x0386B0 , 0),
	( 'Indian/Mayotte'                    , 0x038726 , 0),	( 'Indian/Reunion'                    , 0x0387A5 , 0),	( 'Iran'                              , 0x0387FA , 0),	( 'Israel'                            , 0x038A68 , 0),
	( 'Jamaica'                           , 0x038D97 , 0),	( 'Japan'                             , 0x038E5C , 0),	( 'Kwajalein'                         , 0x038EE6 , 0),	( 'Libya'                             , 0x038F49 , 0),
	( 'MET'                               , 0x039052 , 0),	( 'Mexico/BajaNorte'                  , 0x03935B , 0),	( 'Mexico/BajaSur'                    , 0x0396C4 , 0),	( 'Mexico/General'                    , 0x039909 , 0),
	( 'MST'                               , 0x039B67 , 0),	( 'MST7MDT'                           , 0x039BAB , 0),	( 'Navajo'                            , 0x039EFC , 0),	( 'NZ'                                , 0x03A275 , 0),
	( 'NZ-CHAT'                           , 0x03A5F3 , 0),	( 'Pacific/Apia'                      , 0x03A8D7 , 0),	( 'Pacific/Auckland'                  , 0x03AA73 , 0),	( 'Pacific/Bougainville'              , 0x03ADFF , 0),
	( 'Pacific/Chatham'                   , 0x03AE76 , 0),	( 'Pacific/Chuuk'                     , 0x03B169 , 0),	( 'Pacific/Easter'                    , 0x03B1C2 , 0),	( 'Pacific/Efate'                     , 0x03B438 , 0),
	( 'Pacific/Enderbury'                 , 0x03B4FE , 0),	( 'Pacific/Fakaofo'                   , 0x03B56C , 0),	( 'Pacific/Fiji'                      , 0x03B5BD , 0),	( 'Pacific/Funafuti'                  , 0x03B750 , 0),
	( 'Pacific/Galapagos'                 , 0x03B794 , 0),	( 'Pacific/Gambier'                   , 0x03B80C , 0),	( 'Pacific/Guadalcanal'               , 0x03B871 , 0),	( 'Pacific/Guam'                      , 0x03B8C6 , 0),
	( 'Pacific/Honolulu'                  , 0x03B91C , 0),	( 'Pacific/Johnston'                  , 0x03B993 , 0),	( 'Pacific/Kiritimati'                , 0x03BA12 , 0),	( 'Pacific/Kosrae'                    , 0x03BA7D , 0),
	( 'Pacific/Kwajalein'                 , 0x03BADA , 0),	( 'Pacific/Majuro'                    , 0x03BB46 , 0),	( 'Pacific/Marquesas'                 , 0x03BBA5 , 0),	( 'Pacific/Midway'                    , 0x03BC0C , 0),
	( 'Pacific/Nauru'                     , 0x03BC91 , 0),	( 'Pacific/Niue'                      , 0x03BD09 , 0),	( 'Pacific/Norfolk'                   , 0x03BD67 , 0),	( 'Pacific/Noumea'                    , 0x03BDBC , 0),
	( 'Pacific/Pago_Pago'                 , 0x03BE4C , 0),	( 'Pacific/Palau'                     , 0x03BEC3 , 0),	( 'Pacific/Pitcairn'                  , 0x03BF07 , 0),	( 'Pacific/Pohnpei'                   , 0x03BF5C , 0),
	( 'Pacific/Ponape'                    , 0x03BFB1 , 0),	( 'Pacific/Port_Moresby'              , 0x03BFF6 , 0),	( 'Pacific/Rarotonga'                 , 0x03C048 , 0),	( 'Pacific/Saipan'                    , 0x03C124 , 0),
	( 'Pacific/Samoa'                     , 0x03C17A , 0),	( 'Pacific/Tahiti'                    , 0x03C1F1 , 0),	( 'Pacific/Tarawa'                    , 0x03C256 , 0),	( 'Pacific/Tongatapu'                 , 0x03C2AA , 0),
	( 'Pacific/Truk'                      , 0x03C336 , 0),	( 'Pacific/Wake'                      , 0x03C37B , 0),	( 'Pacific/Wallis'                    , 0x03C3CB , 0),	( 'Pacific/Yap'                       , 0x03C40F , 0),
	( 'Poland'                            , 0x03C454 , 0),	( 'Portugal'                          , 0x03C835 , 0),	( 'PRC'                               , 0x03CD31 , 0),	( 'PST8PDT'                           , 0x03CDD1 , 0),
	( 'ROC'                               , 0x03D122 , 0),	( 'ROK'                               , 0x03D253 , 0),	( 'Singapore'                         , 0x03D346 , 0),	( 'Turkey'                            , 0x03D3FD , 0),
	( 'UCT'                               , 0x03D7EA , 0),	( 'Universal'                         , 0x03D82E , 0),	( 'US/Alaska'                         , 0x03D872 , 0),	( 'US/Aleutian'                       , 0x03DBDB , 0),
	( 'US/Arizona'                        , 0x03DF41 , 0),	( 'US/Central'                        , 0x03DFCF , 0),	( 'US/East-Indiana'                   , 0x03E9D9 , 0),	( 'US/Eastern'                        , 0x03E4DA , 0),
	( 'US/Hawaii'                         , 0x03EC43 , 0),	( 'US/Indiana-Starke'                 , 0x03ECB4 , 0),	( 'US/Michigan'                       , 0x03F025 , 0),	( 'US/Mountain'                       , 0x03F35C , 0),
	( 'US/Pacific'                        , 0x03F6D5 , 0),	( 'US/Pacific-New'                    , 0x03FADA , 0),	( 'US/Samoa'                          , 0x03FEDF , 0),	( 'UTC'                               , 0x03FF56 , 0),
	( 'W-SU'                              , 0x04024D , 0),	( 'WET'                               , 0x03FF9A , 0),
   ( 'Zulu'                              , 0x040490 , 0);";
      $CI->db->query($sqlStr);

      $sqlStr =
        "ALTER TABLE  admin_chapters
            ADD ch_lTimeZone INT NOT NULL DEFAULT  '2'
            COMMENT  'foreign key to table lists_tz'
            AFTER `ch_bUS_DateFormat`;";
      $CI->db->query($sqlStr);


         // find the current time zone
      $strTZ = $CI->config->item('dl_timezone');
      $sqlStr =
         'SELECT tz_lKeyID
          FROM lists_tz
          WHERE tz_strTimeZone='.strPrepStr($strTZ).'
          ORDER BY tz_lKeyID
          LIMIT 0,1;';
      $query = $CI->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows > 0) {
         $row = $query->row();
         $lTZ = (int)$row->tz_lKeyID;
         $sqlStr = "UPDATE admin_chapters SET ch_lTimeZone = '$lTZ' WHERE ch_lKeyID =1;";
         $CI->db->query($sqlStr);
      }
   }
