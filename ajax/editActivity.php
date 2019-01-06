<?php
/*
 * CATS
 * AJAX Activity Entry Editing Interface
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * $Id: editActivity.php 2883 2007-08-14 15:25:26Z brian $
 */


include_once('./lib/StringUtility.php');
include_once('./lib/ActivityEntries.php');
include_once('./lib/Pipelines.php');
include_once('./lib/Mailer.php');
include_once('./lib/EmailTemplates.php');


$interface = new SecureAJAXInterface();

if (!$interface->isRequiredIDValid('activityID'))
{
    $interface->outputXMLErrorPage(-1, 'Invalid activity ID.');
    die();
}

if (!$interface->isRequiredIDValid('type'))
{
    $interface->outputXMLErrorPage(-1, 'Invalid activity entry type.');
    die();
}

if (!$interface->isOptionalIDValid('jobOrderID'))
{
    $interface->outputXMLErrorPage(-1, 'Invalid job order ID.');
    die();
}

if (!isset($_REQUEST['notes']))
{
    $interface->outputXMLErrorPage(-1, 'Invalid notes.');
    die();
}

$siteID = $interface->getSiteID();

$activityID = $_REQUEST['activityID'];
$type       = $_REQUEST['type'];
$jobOrderID = $_REQUEST['jobOrderID'];

/* Decode and trim the activity notes from the company. */
// _REQUEST comes from _GET and _POST. _GET and _POST are passed through urldecode()
// '+' is removed after urldecode(). So remove urldecode below.
$activityNote = trim($_REQUEST['notes']);
$activityDate = trim($_REQUEST['date']);
$activityHour = trim($_REQUEST['hour']);
$activityMinute = trim($_REQUEST['minute']);
$activityAMPM = trim($_REQUEST['ampm']);
$locationPathname = trim($_REQUEST['locationPathname']);

if (!DateUtility::validate('-', $activityDate, DATE_FORMAT_MMDDYY))
{
    die('Invalid availability date.');
    return;
}

/* Convert formatted time to UNIX timestamp. */
$time = strtotime(
    sprintf('%02d:%02d %s', $activityHour, $activityMinute, $activityAMPM)
);

/* Create MySQL date string w/ 24hr time (YYYY-MM-DD HH:MM:SS). */
$date = sprintf(
    '%s %s',
    DateUtility::convert(
        '-', $activityDate, DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD
    ),
    date('H:i:00', $time)
);

/* Highlight what needs highlighting. */
if (strpos($activityNote, 'Status change: ') === 0)
{
    $pipelines = new Pipelines($siteID);

    $statusRS = $pipelines->getStatusesForPicking();
    foreach ($statusRS as $data)
    {
        $activityNote = StringUtility::replaceOnce(
            $data['status'],
            '<span style="color: #ff6c00;">' . $data['status'] . '</span>',
            $activityNote
        );
    }
}

/* Save the new activity entry. */
$activityEntries = new ActivityEntries($siteID);
$activityEntries->update($activityID, $type, $activityNote, $jobOrderID, $date, $_SESSION['CATS']->getTimeZoneOffset());

/* Grab the current activity entry. */
$activityEntry = $activityEntries->get($activityID);

/* Send back "(No Notes)" to be displayed if we don't have any. */
if (empty($activityEntry['notes']))
{
    $activityEntry['notes'] = '(No Notes)';
}

$notificationHTML = '';
/* Notify activity entered owner*/
if ($type == ACTIVITY_DRIFTING)
{           
    if(!empty($activityEntry['enteredByEmail']) &&
       $activityEntry['enteredByEmail'] != '' &&
       $activityEntry['dataItemType'] == DATA_ITEM_CANDIDATE)
    {
        $email = $activityNote . '<BR \><BR \>' . 'Candidate: <BR \>' . '<a href="http://' . $_SERVER['HTTP_HOST'] . $locationPathname . '?m=candidates&amp;a=show&amp;candidateID=' . $activityEntry['dataItemID'] . '">' . 'http://' . $_SERVER['HTTP_HOST'] . $locationPathname . '?m=candidates&amp;a=show&amp;candidateID=' . $activityEntry['dataItemID']. '</a>';
        //$email = $activityNote . '<BR \><BR \>' . 'Candidate: <BR \>' . $_SERVER['HTTP_HOST'] . 'abc' . $_SERVER['REQUEST_URI'];

        /* Send e-mail notification. */
        //FIXME: Make subject configurable.
        $mailer = new Mailer($siteID);
        $mailerStatus = $mailer->sendToOne(array($activityEntry['enteredByEmail'], ''), 'CATS Notification: Drift Activity Updated', $email, true);

        $notificationHTML .= 'Send an e-mail notification to activity note owner';
    }
    else
    {
        $notificationHTML .= 'Error: An e-mail notification could not be sent to the activity entered owner, because the owner does not have a valid e-mail address.';
    }
}
if(($type == ACTIVITY_ARRANGE) || ($type == ACTIVITY_CONFIRM))
{
    if(!empty($activityEntry['enteredByEmail']) &&
       $activityEntry['enteredByEmail'] != '' &&
       $activityEntry['dataItemType'] == DATA_ITEM_CANDIDATE)
    {
        $candidateID = $activityEntry['dataItemID'];
        $regardingID = $jobOrderID;
        $pipelines = new Pipelines($siteID);
                $pipelineUsers = $pipelines->getUser($candidateID, $regardingID);

        if (!empty($pipelineUsers))
        {
            $pipelineUsers = $pipelineUsers[0];
        }
                
        if(!empty($pipelineUsers) &&
           !empty($pipelineUsers['jobOrderRecruiterEmail']) &&
           !empty($pipelineUsers['candidateOwnerEmail']) &&
           $pipelineUsers['jobOrderRecruiterEmail'] != '' &&
           $pipelineUsers['candidateOwnerEmail'] != '')
        {                
            /* Get the change status email template. */
            $emailTemplates = new EmailTemplates($siteID);
            $statusChangeTemplateRS = $emailTemplates->getByTag('EMAIL_TEMPLATE_ARRANGEMENT');
            
            if (empty($statusChangeTemplateRS) ||
                empty($statusChangeTemplateRS['textReplaced']))
            {
                $statusChangeTemplate = '';
            }
            else
            {
                $statusChangeTemplate = $statusChangeTemplateRS['textReplaced'];
            }
            
            /* Replace e-mail template variables. */
            $stringsToFind = array(
                '%CANDOWNER%',
                '%CANDFIRSTNAME%',
                '%CANDFULLNAME%',
                '%CANDCATSURL%',
                '%JBODCATSURL%',
                '%MESSAGE%'
            );
            $replacementStrings = array(
                $pipelineUsers['candidateOwnerFirstName'] . ' ' . $pipelineUsers['candidateOwnerLastName'],
                $pipelineUsers['candidateFirstName'],
                $pipelineUsers['candidateFirstName'] . ' ' . $pipelineUsers['candidateLastName'],
                '<a href="http://' . $_SERVER['HTTP_HOST'] . $locationPathname . '?m=candidates&amp;a=show&amp;candidateID=' . $candidateID . '">'.
                    'http://' . $_SERVER['HTTP_HOST'] . $locationPathname . '?m=candidates&amp;a=show&amp;candidateID=' . $candidateID . '</a>',
                '<a href="http://' . $_SERVER['HTTP_HOST'] . $locationPathname . '?m=joborders&amp;a=show&amp;jobOrderID=' . $regardingID . '">'.
                    'http://' . $_SERVER['HTTP_HOST'] . $locationPathname . '?m=joborders&amp;a=show&amp;jobOrderID=' . $regardingID . '</a>',
                $activityNote
            );
            
            $statusChangeTemplate = str_replace($stringsToFind, $replacementStrings, $statusChangeTemplate);

            $email = $statusChangeTemplate;// . '<br><br><p>' . $activityNote . '</p>';
            
            /* Send e-mail notification. */
            //FIXME: Make subject configurable.
            $mailer = new Mailer($siteID);
            $mailerStatus = $mailer->sendToMany(
                array(array($pipelineUsers['candidateOwnerEmail'], ''), array($pipelineUsers['jobOrderRecruiterEmail'], '')),
                'CATS Notification: Arrangement / Confirmation - ' . $pipelineUsers['candidateFirstName'] . ' ' . $pipelineUsers['candidateLastName'] .
                ' (' . $pipelineUsers['candidateOwnerFirstName'] . ' ' . $pipelineUsers['candidateOwnerLastName'] . ')', $email, true);
                
            $notificationHTML .= 'Send an e-mail notification to candidate owner and job order recruiter';
        }
        else
        {
            $notificationHTML .= 'Error: An e-mail notification' .
                ' could not be sent to the candidate owner and job order recruiter' .
                ' because the owner/recruiter does not have a valid e-mail address.';
        }
    }
    else
    {
        $notificationHTML .= 'Error: An e-mail notification could not be sent to the activity entered owner, because the owner does not have a valid e-mail address.';
    }
    
}


/* Send back the XML data. */
$interface->outputXMLPage(
    "<data>\n" .
    "    <errorcode>1</errorcode>\n" .
    "    <errormessage>" . $notificationHTML . "</errormessage>\n" .
    "    <type>"            . $activityEntry['type'] . "</type>\n" .
    "    <typedescription>" . $activityEntry['typeDescription'] . "</typedescription>\n" .
    "    <notes>"           . htmlspecialchars($activityEntry['notes']) . "</notes>\n" .
    "    <regarding>"       . htmlspecialchars($activityEntry['regarding']) . "</regarding>\n" .
    "    <date>"            . htmlspecialchars($activityEntry['dateCreated']) . "</date>\n" .
    "</data>\n"
);

?>
