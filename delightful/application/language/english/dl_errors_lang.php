<?php

      // thanks to phpfaculte (http://www.youtube.com/user/fmdamia8791)
      // at http://www.youtube.com/watch?v=56EDSocDhjk
      // custom codeIgniter error messages via callback
   $lang['verifyRelCatSel'] = 'Please select a category for this<br>people relationship';
   $lang['verifyUniqueRelType'] = 'This <b>relationship name</b> is already<br>defined.';

   $lang['groupNameBlankTest'] = 'The group name can not be blank.';
   $lang['groupNameDupTest']   = 'This <b>%s</b><br>is already defined.';

      // generic lists
   $lang['verifyUniqueGenericListItem']     = 'A list entry with this name is already defined.';

      // sponsorship programs
   $lang['sponProgBlankTest']   = 'The sponsorship program name can not be blank.';
   $lang['sponProgDupTest']     = 'A sponsorship program with this name is already defined.';
   $lang['sponProgAcctCountry'] = 'Please select an accounting country.';

      // user accounts
   $lang['verifyPWordsMatch']   = 'The passwords do not match.';
   $lang['verifyPWordRequired'] = 'A password is required.';
   $lang['verifyUniqueUserID']  = 'This user name is already being used.';

      // personalized tables
   $lang['userTableVerifyUnique']   = 'A personalized table with this name is already defined.';
   $lang['userTableVerifyRequired'] = 'The table name is required.';

      // add/edit personalized fields
   $lang['fieldNameRqrtTest']     = 'The field name is required.';
   $lang['ufFieldAddAcctCountry'] = 'Please select an Accounting Country.';
   $lang['fieldNameDupTest']      = 'The field name is already being used in this table.';
   $lang['ufDDLAddEditUnique']    = 'This entry is already used in this drop-down list.';

      // add/edit client
   $lang['addClientVerifyLoc']         = 'Please select a location.';
   $lang['addClientVerifySCat']        = 'Please select a status category.';
   $lang['addClientVerifyVoc']         = 'Please select a client vocabulary.';
   $lang['clientRecVerifyBDateValid']  = 'The client\'s birth date is not valid.';
   $lang['clientRecVerifyBDatePast']   = 'The client\'s birth date is in the future!';
   $lang['clientRecVerifyEDateValid']  = 'The client\'s enrollment date is not valid.';
   $lang['clientRecVerifyEDatePast']   = 'The client\'s enrollment date is in the future!';
   $lang['clientRecVerifyInitStat']    = 'Please select an initial status for this client';

      // client status
   $lang['clientStatEntryDDL']        = 'Please select a status entry.';
   $lang['clientStatVerifyDatePast']  = 'Your status date is in the future!';
   $lang['clientStatVerifyDateValid'] = 'The status entry date is not valid.';

      // user personalized data entry
   $lang['ufFieldVerifyDateValid'] = 'The date is not valid.';

      // add/edit people
   $lang['peopleRecVerifyBDateValid']  = 'The birth date is not valid.';
   $lang['peopleRecVerifyBDatePast']   = 'The birth date is in the future!';

      // reminders
   $lang['remVerifyDateValid']  = 'The reminder date is not valid.';
   $lang['remVerifyDatePast']   = 'The reminder date is in the past!';

      // add/edit donations
   $lang['giftVerifyDDateValid']   = 'The donation date is not valid.';
   $lang['giftVerifyAccountValid'] = 'Please select an account.';
   $lang['giftVerifyCampValid']    = 'Please select a campaign.';
   $lang['giftVerifyPayType']      = 'Please select a payment type.';

      // event date
   $lang['edateVerifyDateValid']  = 'The event date is not valid.';

      // effective date
   $lang['clientRecVerifyEffDateValid']  = 'The effective date is not valid.';

      // add/edit sponsorship
   $lang['sponAddEditStartValid'] = 'The sponsorship start date is not valid.';
   $lang['sponAddEditProgSelect'] = 'Please select a sponsorship program.';

      // sponsorship charge
   $lang['sponChargeAddEditDateValid'] = 'The charge date is not valid.';

      // sponsorship payment
   $lang['sponPaymentAddEditDateValid'] = 'The payment date is not valid.';
   $lang['sponPayVerifyPayType']        = 'Please select a payment type.';

      // sponsorship deactivation
   $lang['sponDeactivateDateValid'] = 'The deactivation date is not valid.';
   $lang['sponDeactivateTermType']  = 'Please select a deactivation reason.';

      // client image upload
   $lang['clientImageUploadVerifyBDateValid'] = 'The image/document date is not valid.';

      // generic report - start/end dates
   $lang['rptStart'] = 'The report start date is not valid.';
   $lang['rptEnd']   = 'The report end date is not valid.';
   $lang['rptCBH']   = 'The report end date is before the start date.';

      // generic report - min/max donation range
   $lang['rptCBHAmount'] = 'The donation minimum amount must be less than or equal to the maximum amount.';

      // change password
   $lang['verifyGoodPW']     = 'The password you entered is not valid';
   $lang['verifyPWordMatch'] = 'The passwords do not match';
   
      // volunteer calendar
   $lang['eventsStartMonth'] = 'The starting date is not valid (expecting mm/yyyy).';

      // relationship B soft cash
   $lang['relBSoftCash'] = 'For soft cash, please select a relationship (B to A)';

      // Add new deposit
   $lang['depositVerifySDateValid'] = 'The deposit start date is not valid.';
   $lang['depositVerifyEDateValid'] = 'The deposit end date is not valid.';
   $lang['depositCBH']              = 'The deposit end date is before the start date!';

      // Log search
   $lang['verifyCheckLogValid']     = 'Please select one or more areas to search.';

      // Custom Report Name
   $lang['cRptNameDupTest']         = 'The report name you entered is currently in use.';

      // Volunteer Activity
   $lang['verifyUnDateValid']       = 'The volunteer activity date is not valid.';
   $lang['vactVerifyStart']         = 'Please select a start time.';
   $lang['vactVerifyAct']           = 'Please select an activity.';
   $lang['vactVerifyDuration']      = 'Please select a duration.';

      // Add/edit Auction
   $lang['auctionDateValid']          = 'The auction date is not valid.';
   $lang['auctionVerifyAccountValid'] = 'Please select a gift account for bid winners.';
   $lang['auctionVerifyCampValid']    = 'Please select a gift campaign for bid winners.';

      // Add/edit Auction Package
   $lang['minPackageAmnt']    = 'The minimum amount must be more than 0.00.';

      // Add/edit Auction Item
   $lang['obtainedDateValid']     = 'Invalid date for when the auction item was obtained.';
   
      // fulfill bid
   $lang['bidVerifyPayType']      = 'Please select a payment type.';
   $lang['bidVerifyFDateValid']   = 'The fulfillment date you specified is not valid.';
   
?>