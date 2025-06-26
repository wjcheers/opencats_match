<?php
/*
 * CATS
 * Asynchroneous Queue Processor
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
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
 * $Id: Reminders.php 3558 2007-11-11 22:44:14Z will $
 */

include_once('./modules/queue/lib/Task.php');
include_once('./lib/Calendar.php');
include_once('./lib/DateUtility.php');
include_once('./lib/SystemUtility.php');

class Reminders extends Task
{
    public function getSchedule()
    {
        /**
         * Crontab-formatted string for how often to run the recurring task
         * Examples:
         *     "* * * * *":             Every minute
         *     "1,3,5 * * * *":         1st, 2nd and 5th minute of every hour
         *     "* 1 * * *":             1:00am every day
         *     "* * 1 * *":             The 1st of every month
         *
         * Values are as follows: minute, hour, day of month, month, day of week (0 sun -> 6 mon)
         */
        return '* * * * *';
    }

    public function run($siteID, $args)
    {
        Task::setName('Calendar Reminders');
        Task::setDescription('Send out reminder e-mails from the CATS calendar.');

        $calendar = new Calendar(0);

        //Check for reminders that need to be sent out.
        $dueEvents = $calendar->getAllDueReminders();

        // Do/log nothing if no events exist
        if (!count($dueEvents))
        {
            return TASKRET_SUCCESS_NOLOG;
        }

        foreach ($dueEvents as $index => $data)
        {
            $emailSubject = 'CATS: ' . $data['title'];

            $emailContents = $GLOBALS['eventReminderEmail'];
            
            $URLpar = 'm=calendar';
            if($data['dataItemType'] == DATA_ITEM_CANDIDATE) {
                $URLpar = 'm=candidates&amp;a=show&amp;candidateID=' . $data['dataItemID'];
            }
            if($data['dataItemType'] == DATA_ITEM_CONTACT) {
                $URLpar = 'm=contacts&amp;a=show&amp;contactID=' . $data['dataItemID'];
            }

            $stringsToFind = array(
                '%FULLNAME%',
                '%NOTES%',
                '%EVENTNAME%',
                '%DUETIME%',
                'm=calendar'
            );
            $replacementStrings = array(
                $data['enteredByFirstName'] . ' ' . $data['enteredByLastName'],
                $data['description'],
                $data['title'],
                self::_getReminderTimeString($data['reminderTime']),
                $URLpar
            );
            $emailContents = str_replace(
                $stringsToFind,
                $replacementStrings,
                $emailContents
            );

            $emailDestination = $data['reminderEmail'];

            // SEND E-Mail here
            $calendar->sendEmail(
                $data['siteID'],
                0,
                $emailDestination,
                $emailSubject,
                $emailContents
            );

            // Remove alert.
            $calendar->updateEventDisableReminder($data['eventID']);
        }

        // Set the response the task wants logged
        $this->setResponse(sprintf(
            'E-mailed %d calendar reminders.',
            count($dueEvents)
        ));

        return TASKRET_SUCCESS;
    }

    private function _getReminderTimeString($reminderTime)
    {
        if ($reminderTime < 1)
        {
            //$string = 'immediately';
            $string = '立即';
        }
        else if ($reminderTime == 1)
        {
            //$string = 'in 1 minute';
            $string = '1分鐘';
        }
        else if ($reminderTime < 60)
        {
            //$string = 'in ' . $reminderTime . ' minutes';
            $string = $reminderTime . '分鐘';
        }
        else if ($reminderTime == 60)
        {
            //$string = 'in 1 hour';
            $string = '1小時';
        }
        else if ($reminderTime < 1440)
        {
            //$string = 'in ' . (($reminderTime * 1.0) / 60) . ' hours';
            $string = (($reminderTime * 1.0) / 60) . '小時';
        }
        else if ($reminderTime == 1440)
        {
            //$string = 'in 1 day';
            $string = '1天';
        }
        else
        {
            //$string = 'in ' . (($reminderTime * 1.0) / 1440) . ' days';
            $string = (($reminderTime * 1.0) / 1440) . '天';
        }

    	return $string;
    }
}
