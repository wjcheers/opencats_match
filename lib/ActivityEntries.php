<?php
/**
 * CATS
 * Activity Entries Library
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
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: ActivityEntries.php 3592 2007-11-13 17:30:46Z brian $
 */

define('ACTIVITY_CALL',        100);
define('ACTIVITY_EMAIL',       200);
define('ACTIVITY_MEETING',     300);
define('ACTIVITY_OTHER',       400);
define('ACTIVITY_CALL_TALKED', 500);
define('ACTIVITY_CALL_LVM',    600);
define('ACTIVITY_CALL_MISSED', 700);
define('ACTIVITY_ARRANGE',     800);
define('ACTIVITY_CONFIRM',     900);
define('ACTIVITY_DRIFTING',   1000);
define('ACTIVITY_IM_LINKEDIN',1100);
define('ACTIVITY_INTERVIEW',  1200);
define('ACTIVITY_CALL_FIRST', 1300);
define('ACTIVITY_NOTE',       1400);
define('ACTIVITY_IM_LINE',    1500);
define('ACTIVITY_IM',         1600);
define('ACTIVITY_REPORT',     1700);

/**
 * Candidates library.
 */
include_once('./lib/Candidates.php');

/**
 * Contacts library.
 */
include_once('./lib/Contacts.php');

/**
 * Companies library.
 */
include_once('./lib/Companies.php');

/**
 * Job Orders library.
 */
include_once('./lib/JobOrders.php');

/**
 * Job Orders library.
 */
include_once('./lib/Statistics.php');

/**
 *  Activity Entries Library
 *  @package    CATS
 *  @subpackage Library
 */
class ActivityEntries
{
    private $_db;
    private $_siteID;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }


    /**
     * Adds an activity entry to the database.
     *
     * @param integer Data Item ID.
     * @param flag Data Item type flag.
     * @param flag Activity type flag.
     * @param string Activity notes.
     * @param integer Entered-by user ID.
     * @param integer Job Order ID; -1 for general.
     * @return integer New Activity ID; -1 on failure.
     */
    public function add($dataItemID, $dataItemType, $activityType,
        $activityNotes, $enteredBy, $jobOrderID = -1)
    {
        $sql = sprintf(
            "INSERT INTO activity (
                data_item_id,
                data_item_type,
                joborder_id,
                entered_by,
                type,
                notes,
                site_id,
                date_created,
                date_modified
            )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                NOW(),
                NOW()
            )",
            $this->_db->makeQueryInteger($dataItemID),
            $this->_db->makeQueryInteger($dataItemType),
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_db->makeQueryInteger($enteredBy),
            $this->_db->makeQueryInteger($activityType),
            $this->_db->makeQueryString($activityNotes),
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        $activityEntryID = $this->_db->getLastInsertID();

        $history = new History($this->_siteID);
        $history->storeHistoryData(
            $dataItemType,
            $dataItemID,
            'ACTIVITY',
            '(NEW)',
            $activityNotes,
            '(USER) Added activity.'
        );

        /* Update the last-modified timestamp for the "parent" Data Item. */
        $this->_updateDataItemModified($dataItemID, $dataItemType);

        /* If there is a job order being associated, update it's modified
         * timestamp, too.
         */
        if ($jobOrderID != -1)
        {
            $this->_updateDataItemModified($jobOrderID, DATA_ITEM_JOBORDER);
        }

        if(($dataItemType == DATA_ITEM_CANDIDATE) && ($jobOrderID != -1))
        {
            $sql = sprintf(
                "UPDATE
                    candidate_joborder
                SET
                    last_notes  = 
                        CONCAT(
                            '<strong>',
                            DATE_FORMAT(NOW(), '%%m-%%d-%%y'),
                            ' (',
                            %s,
                            '):</strong> ',
                            IF(
                                ISNULL(%s) OR %s = '',
                                '(No Notes)',
                                %s
                            )
                        )
                WHERE
                    candidate_id    = %s
                AND
                    joborder_id     = %s                
                AND
                    site_id = %s",
                $this->_db->makeQueryString($_SESSION['CATS']->getFullName()),
                $this->_db->makeQueryString($activityNotes),
                $this->_db->makeQueryString($activityNotes),
                $this->_db->makeQueryString($activityNotes),
                $this->_db->makeQueryInteger($dataItemID),
                $this->_db->makeQueryInteger($jobOrderID),
                $this->_siteID
            );
            $queryResult = $this->_db->query($sql);
            if (!$queryResult)
            {
                return -1;
            }
        }

        return $activityEntryID;
    }

    /**
     * Updates an activity entry with the information provided.
     *
     * @param integer Activity ID to update.
     * @param flag New activity type flag.
     * @param string New activity notes.
     * @param integer New Job Order ID; -1 for general.
     * @return boolean True if successful; false otherwise.
     */
    public function update($activityID, $activityType, $activityNotes,
        $jobOrderID = false, $date = false, $timezoneOffset)
    {
        /* Get some extra information about the activity entry that we'll
         * need later on.
         */
        $sql = sprintf(
            "SELECT
                activity.data_item_id AS dataItemID,
                activity.data_item_type AS dataItemType,
                activity.joborder_id AS jobOrderID,
                activity.notes AS notes,
                activity.entered_by AS enteredBy,
                CONCAT(
                    user.first_name, ' ', user.last_name
                ) AS enteredByFullName
            FROM
                activity
            LEFT JOIN user AS user
                ON activity.entered_by = user.user_id
            WHERE
                activity.activity_id = %s
            AND
                activity.site_id = %s",
            $this->_db->makeQueryInteger($activityID),
            $this->_siteID
        );
        $activityIDRS = $this->_db->getAssoc($sql);

        if (!$activityIDRS)
        {
            return false;
        }

        /* If a job order ID wasn't specified, use the existing one. */
        if ($jobOrderID === false)
        {
            $newJobOrderID = $activityIDRS['jobOrderID'];
        }
        else
        {
            $newJobOrderID = $jobOrderID;
        }

        $sql = sprintf(
            "UPDATE
                activity
            SET
                type          = %s,
                notes         = %s,
                joborder_id   = %s,
                date_modified = NOW()
            WHERE
                activity_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($activityType),
            $this->_db->makeQueryString($activityNotes),
            $this->_db->makeQueryInteger($newJobOrderID),
            $this->_db->makeQueryInteger($activityID),
            $this->_siteID
        );
        $queryResult = $this->_db->query($sql);

        if ($date !== false)
        {
            $sql = sprintf(
                "UPDATE
                    activity
                SET
                    date_created  = DATE_SUB(%s, INTERVAL %s HOUR),
                    date_modified = NOW()
                WHERE
                    activity_id = %s
                AND
                    site_id = %s",
                $this->_db->makeQueryString($date),
                $this->_db->makeQueryInteger($timezoneOffset),
                $this->_db->makeQueryInteger($activityID),
                $this->_siteID
            );

            $queryResult = $this->_db->query($sql);
        }

        $history = new History($this->_siteID);
        $history->storeHistoryData(
            $activityIDRS['dataItemType'],
            $activityIDRS['dataItemID'],
            'ACTIVITY',
            $activityIDRS['notes'],
            $activityNotes,
            '(USER) Edited ' . $activityIDRS['enteredByFullName'] . '\'s activity.'
        );

        if (!$queryResult)
        {
            return false;
        }

        /* Update the last-modified timestamp for the "parent" Data Item. */
        $this->_updateDataItemModified(
            $activityIDRS['dataItemID'], $activityIDRS['dataItemType']
        );

        /* If there is a job order being associated, update it's modified
         * timestamp, too.
         */
        if (!empty($jobOrderID) && ctype_digit((string) $jobOrderID))
        {
            $this->_updateDataItemModified($jobOrderID, DATA_ITEM_JOBORDER);
        }

        /* The job order ID may have been changed. If it has, and the new one
         * is valid, update its modified timestamp, too.
         */
        if (!empty($newJobOrderID) && ctype_digit((string) $newJobOrderID) &&
            $jobOrderID != $newJobOrderID)
        {
            $this->_updateDataItemModified($newJobOrderID, DATA_ITEM_JOBORDER);
        }

        if($activityIDRS['dataItemType'] == DATA_ITEM_CANDIDATE)
        {            
            if(!empty($newJobOrderID) && ctype_digit((string) $newJobOrderID))
            {
                $sql = sprintf(
                    "UPDATE
                        candidate_joborder
                    SET
                        last_notes  = 
                            CONCAT(
                                '<strong>',
                                DATE_FORMAT(NOW(), '%%m-%%d-%%y'),
                                ' (',
                                %s,
                                '):</strong> ',
                                IF(
                                    ISNULL(%s) OR %s = '',
                                    '(No Notes)',
                                    %s
                                )
                            )
                    WHERE
                        candidate_id    = %s
                    AND
                        joborder_id     = %s                
                    AND
                        site_id = %s",
                    $this->_db->makeQueryString($_SESSION['CATS']->getFullName()),
                    $this->_db->makeQueryString($activityNotes),
                    $this->_db->makeQueryString($activityNotes),
                    $this->_db->makeQueryString($activityNotes),
                    $this->_db->makeQueryInteger($activityIDRS['dataItemID']),
                    $this->_db->makeQueryInteger($newJobOrderID),
                    $this->_siteID
                );
                $queryResult = $this->_db->query($sql);
                if (!$queryResult)
                {
                    return false;
                }
            }
            else if(!empty($activityIDRS['jobOrderID']) && ctype_digit((string) $activityIDRS['jobOrderID']))
            {
                // this is job order ID change case. erase last_notes of original candidate_joborder.last_notes
                $sql = sprintf(
                    "UPDATE
                        candidate_joborder
                    SET
                        last_notes      = ''
                    WHERE
                        candidate_id    = %s
                    AND
                        joborder_id     = %s                
                    AND
                        site_id = %s",
                    $this->_db->makeQueryInteger($activityIDRS['dataItemID']),
                    $this->_db->makeQueryInteger($activityIDRS['jobOrderID']),
                    $this->_siteID
                );
                $queryResult = $this->_db->query($sql);
                if (!$queryResult)
                {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Removes an activity note from the system.
     *
     * @param integer Activity ID.
     * @return boolean True if successful; false otherwise.
     */
    public function delete($activityID)
    {
        $sql = sprintf(
            "SELECT
                activity.data_item_id AS dataItemID,
                activity.data_item_type AS dataItemType,
                activity.joborder_id AS jobOrderID,
                activity.notes AS notes,
                activity.entered_by AS enteredBy,
                CONCAT(
                    user.first_name, ' ', user.last_name
                ) AS enteredByFullName
            FROM
                activity
            LEFT JOIN user AS user
                ON activity.entered_by = user.user_id
            WHERE
                activity.activity_id = %s
            AND
                activity.site_id = %s",
            $this->_db->makeQueryInteger($activityID),
            $this->_siteID
        );
        $activityIDRS = $this->_db->getAssoc($sql);

        if (!$activityIDRS)
        {
            return false;
        }

        $sql = sprintf(
            "DELETE FROM
                activity
            WHERE
                activity_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($activityID),
            $this->_siteID
        );
        $queryResult = $this->_db->query($sql);

        if (!$queryResult)
        {
            return false;
        }

        $history = new History($this->_siteID);
        $history->storeHistoryData(
            $activityIDRS['dataItemType'],
            $activityIDRS['dataItemID'],
            'ACTIVITY',
            $activityIDRS['notes'],
            '(DELETE)',
            '(USER) Deleted ' . $activityIDRS['enteredByFullName'] . '\'s activity.'
        );

        /* Update the last-modified timestamp for the "parent" Data Item. */
        $this->_updateDataItemModified(
            $activityIDRS['dataItemID'], $activityIDRS['dataItemType']
        );

        /* If there is a job order associated, update it's modified timestamp,
         * too.
         */
        if (!empty($activityIDRS['jobOrderID']) &&
            ctype_digit((string) $activityIDRS['jobOrderID']))
        {
            $this->_updateDataItemModified(
                $activityIDRS['jobOrderID'], DATA_ITEM_JOBORDER
            );
        }

        return true;
    }

    /**
     * Returns number of total activities (for activities datagrid).
     *
     * @return integer count
     */
    public function getCount()
    {
        $sql = sprintf(
            "SELECT
                COUNT(*) AS totalActivities
            FROM
                activity
            WHERE
                activity.site_id = %s",
            $this->_siteID
        );

        return $this->_db->getColumn($sql, 0, 0);
    }

    /**
     * Returns an activity entry.
     *
     * @param integer Activity ID.
     * @return array Activity data.
     */
    public function get($activityID)
    {
        $sql = sprintf(
            "SELECT
                activity.activity_id AS activityID,
                activity.data_item_id AS dataItemID,
                activity.data_item_type AS dataItemType,
                activity.joborder_id AS jobOrderID,
                activity.type AS type,
                activity_type.short_description AS typeDescription,
                activity.notes AS notes,
                DATE_FORMAT(
                    activity.date_created, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateCreated,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName,
                entered_by_user.email AS enteredByEmail,
                IF(
                    ISNULL(joborder.title),
                    'General',
                    CONCAT(joborder.title, ' (', company.name, ')')
                ) AS regarding,
                joborder.title AS regardingJobTitle,
                company.name AS regardingCompanyName
            FROM
                activity
            LEFT JOIN activity_type
                ON activity.type = activity_type.activity_type_id
            LEFT JOIN user AS entered_by_user
                ON activity.entered_by = entered_by_user.user_id
            LEFT JOIN joborder
                ON activity.joborder_id = joborder.joborder_id
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            WHERE
                activity.activity_id = %s
            AND
                activity.site_id = %s",
            $this->_db->makeQueryInteger($activityID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Returns all activity entries for a Data Item.
     *
     * @param integer Data Item ID.
     * @param flag Data Item type flag.
     * @return resultset Activity entries data.
     */
    public function getAllByDataItem($dataItemID, $dataItemType, $activityType = NULL)
    {
        $activityCondition = '';
        if($activityType != NULL)
        {
            $activityCondition = 'AND activity_type.short_description ' . $activityType;
        }
        $sql = sprintf(
            "SELECT
                activity.activity_id AS activityID,
                activity.data_item_id AS dataItemID,
                activity.joborder_id AS jobOrderID,
                activity.notes AS notes,
                DATE_FORMAT(
                    activity.date_created, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateCreated,
                activity.date_created AS dateCreatedSort,
                activity.type AS type,
                activity_type.short_description AS typeDescription,
                activity.date_created AS dateCreatedSort,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName,
                IF(
                    ISNULL(joborder.title),
                    'General',
                    CONCAT(joborder.title, ' (', company.name, ')')
                ) AS regarding,
                joborder.title AS regardingJobTitle,
                company.name AS regardingCompanyName
            FROM
                activity
            LEFT JOIN user AS entered_by_user
                ON activity.entered_by = entered_by_user.user_id
            LEFT JOIN activity_type
                ON activity.type = activity_type.activity_type_id
            LEFT JOIN joborder
                ON activity.joborder_id = joborder.joborder_id
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            WHERE
                activity.data_item_id = %s
            AND
                activity.data_item_type = %s
            %s
            AND
                activity.site_id = %s
            ORDER BY
                dateCreatedSort ASC",
            $this->_db->makeQueryInteger($dataItemID),
            $this->_db->makeQueryInteger($dataItemType),
            $activityCondition,
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all activity entries for a Data Item.
     *
     * @param integer Data Item ID.
     * @param flag Data Item type flag.
     * @return resultset Activity entries data.
     */
    public function getAllRegardingByDataItem($dataItemID = NULL, $dataItemType = NULL, $jobOrderID = NULL, $companyID = NULL, $activityType = NULL, $period = NULL, $userID = NULL)
    {
        $criterion = '';
        if($period != NULL)
        {
            $statistics = new Statistics($this->_siteID);
            $criterion = $statistics->makePeriodCriterion(
                'activity.date_modified', $period
            );
        }

        $select = '';
        $join = '';
        
        $dataItemCondition = '';
        if($dataItemID != NULL)
        {
            $dataItemCondition = 'AND activity.data_item_id = ' . $dataItemID;
        }
        $dataItemTypeCondition = '';
        if($dataItemType != NULL)
        {
            $dataItemTypeCondition = 'AND activity.data_item_type = ' . $dataItemType;
            if($dataItemType == DATA_ITEM_CANDIDATE)
            {
                //$select .= ",CONCAT(candidate.first_name, ' ', candidate.last_name) AS candidateFullName";
                //$join .= 'LEFT JOIN candidate ON candidate.candidate_id = activity.data_item_id';
            }
        }
        $jobOrderCondition = '';
        if($jobOrderID != NULL)
        {
            $jobOrderCondition = 'AND activity.joborder_id = ' . $jobOrderID;
        }
        $companyCondition = '';
        if($companyID != NULL)
        {
            $companyCondition = 'AND joborder.company_id = ' . $companyID;
        }
        $activityCondition = '';
        if($activityType != NULL)
        {
            $activityCondition = 'AND activity_type.short_description ' . $activityType;
        }
        $userCondition = '';
        if($userID != NULL)
        {
            $userCondition = 'AND activity.entered_by = ' . $userID;
        }
        
        $sql = sprintf(
            "SELECT
                activity.activity_id AS activityID,
                activity.data_item_id AS dataItemID,
                activity.data_item_type AS dataItemType,
                activity.joborder_id AS jobOrderID,
                activity.notes AS notes,
                DATE_FORMAT(
                    activity.date_created, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateCreated,
                DATE_FORMAT(
                    activity.date_modified, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateModified,
                activity.date_created AS dateCreatedSort,
                activity.type AS type,
                activity_type.short_description AS typeDescription,
                activity.date_created AS dateCreatedSort,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName,
                IF(
                    NOT ISNULL(joborder.title) AND (activity.data_item_type = %s),
                    CONCAT(joborder.title, ' (', company.name, ')'),
                    IF(
                        activity.data_item_type = %s,
                        CONCAT(candidate.first_name, ' ', candidate.last_name),
                        IF(
                            NOT ISNULL(company.name) AND (activity.data_item_type = %s),  
                            company.name,                      
                            IF(
                                NOT ISNULL(CONCAT(contact.first_name, contact.last_name)) AND (activity.data_item_type = %s),
                                CONCAT(contact.first_name, ' ', contact.last_name),
                                'General'
                            )
                        )
                    )
                ) AS regarding,
                joborder.title AS regardingJobTitle,
                CONCAT(candidate.first_name, ' ', candidate.last_name) AS candidateFullName,
                CONCAT(contact.first_name, ' ', contact.last_name) AS regardingContactName,
                company.name AS regardingCompanyName
                %s
            FROM
                activity
            LEFT JOIN user AS entered_by_user
                ON activity.entered_by = entered_by_user.user_id
            LEFT JOIN activity_type
                ON activity.type = activity_type.activity_type_id
            LEFT JOIN joborder
                ON activity.joborder_id = joborder.joborder_id
            LEFT JOIN candidate
                ON candidate.candidate_id = activity.data_item_id AND activity.data_item_type = %s
            LEFT JOIN company
                ON (joborder.company_id = company.company_id AND activity.data_item_type = %s) OR
                   (company.company_id = activity.data_item_id AND activity.data_item_type = %s)
            LEFT JOIN contact
                ON contact.contact_id = activity.data_item_id AND activity.data_item_type = %s
            %s
            WHERE
                activity.site_id = %s
            %s
            %s
            %s
            %s
            %s
            %s
            %s
            ORDER BY
                dateCreatedSort ASC",
            DATA_ITEM_CANDIDATE,
            DATA_ITEM_CANDIDATE,
            DATA_ITEM_COMPANY,
            DATA_ITEM_CONTACT,
            $select,
            DATA_ITEM_CANDIDATE,
            DATA_ITEM_CANDIDATE,
            DATA_ITEM_COMPANY,
            DATA_ITEM_CONTACT,
            $join,
            $this->_siteID,
            $dataItemCondition,
            $dataItemTypeCondition,
            $jobOrderCondition,
            $companyCondition,
            $activityCondition,
            $userCondition,
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }
    
    /**
     * Returns all activity entries for report
     *
     * @param integer Data Item ID.
     * @param flag Data Item type flag.
     * @return resultset Activity entries data.
     */
    public function getAllForReportByUser($period = NULL, $userID = NULL)
    {
        $criterion = '';
        if($period != NULL)
        {
            $statistics = new Statistics($this->_siteID);
            $criterion = $statistics->makePeriodCriterion(
                'activity.date_modified', $period
            );
        }

        $activityCondition = "AND (activity_type.short_description = 'Report' OR 
            activity_type.short_description = 'Call (Talked)' OR 
            activity_type.short_description = 'Call (First)' OR 
            activity_type.short_description = 'Call (Missed)' OR 
            activity_type.short_description = 'Call (LVM)' OR 
            activity_type.short_description = 'Email' OR 
            activity_type.short_description = 'Meeting' OR 
            activity_type.short_description = 'IM (Linkedin)' OR 
            activity_type.short_description = 'IM (Line)' OR 
            activity_type.short_description = 'IM')";
            
        $userCondition = '';
        if($userID != NULL)
        {
            $userCondition = 'AND activity.entered_by = ' . $userID;
        }
        
        $sql = sprintf(
            "SELECT
                activity.activity_id AS activityID,
                activity.data_item_id AS dataItemID,
                activity.data_item_type AS dataItemType,
                activity.joborder_id AS jobOrderID,
                activity.notes AS notes,
                DATE_FORMAT(
                    activity.date_created, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateCreated,
                DATE_FORMAT(
                    activity.date_modified, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateModified,
                activity.date_created AS dateCreatedSort,
                activity.type AS type,
                activity_type.short_description AS typeDescription,
                activity.date_created AS dateCreatedSort,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName,
                IF(
                    NOT ISNULL(joborder.title) AND (activity.data_item_type = %s),
                    CONCAT(candidate.first_name, ' ', candidate.last_name, ' (', joborder.title, ' - ', company.name, ')'),
                    IF(
                        activity.data_item_type = %s,
                        CONCAT(candidate.first_name, ' ', candidate.last_name),
                        IF(
                            NOT ISNULL(company.name) AND (activity.data_item_type = %s),  
                            company.name,
                            IF(
                                NOT ISNULL(CONCAT(contact.first_name, contact.last_name)) AND (activity.data_item_type = %s),
                                CONCAT(contact.first_name, ' ', contact.last_name),
                                'General'
                            )
                        )
                    )
                ) AS regarding,
                joborder.title AS regardingJobTitle,
                CONCAT(candidate.first_name, ' ', candidate.last_name) AS candidateFullName,
                CONCAT(contact.first_name, ' ', contact.last_name) AS regardingContactName,
                company.name AS regardingCompanyName
            FROM
                activity
            LEFT JOIN user AS entered_by_user
                ON activity.entered_by = entered_by_user.user_id
            LEFT JOIN activity_type
                ON activity.type = activity_type.activity_type_id
            LEFT JOIN joborder
                ON activity.joborder_id = joborder.joborder_id
            LEFT JOIN candidate
                ON candidate.candidate_id = activity.data_item_id AND activity.data_item_type = %s
            LEFT JOIN company
                ON (joborder.company_id = company.company_id AND activity.data_item_type = %s) OR
                   (company.company_id = activity.data_item_id AND activity.data_item_type = %s)
            LEFT JOIN contact
                ON contact.contact_id = activity.data_item_id AND activity.data_item_type = %s
            WHERE
                activity.site_id = %s
            %s
            %s
            %s
            ORDER BY
                regarding ASC",
            DATA_ITEM_CANDIDATE,
            DATA_ITEM_CANDIDATE,
            DATA_ITEM_COMPANY,
            DATA_ITEM_CONTACT,
            DATA_ITEM_CANDIDATE,
            DATA_ITEM_CANDIDATE,
            DATA_ITEM_COMPANY,
            DATA_ITEM_CONTACT,
            $this->_siteID,
            $activityCondition,
            $userCondition,
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all activity types and their descriptions.
     *
     * @return resultset Activity type IDs and descriptions.
     */
    public function getTypes()
    {
        $sql = sprintf(
            "SELECT
                activity_type_id AS typeID,
                short_description AS type
            FROM
                activity_type
            ORDER BY
                activity_type_id ASC",
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Updates a Data Item's modified timestamp.
     *
     * @param integer Data Item ID.
     * @param flag Data Item type flag.
     * @return void
     */
    private function _updateDataItemModified($dataItemID, $dataItemType)
    {
        switch ($dataItemType)
        {
            case DATA_ITEM_CANDIDATE:
                $dataItem = new Candidates($this->_siteID);
                break;

            case DATA_ITEM_COMPANY:
                $dataItem = new Companies($this->_siteID);
                break;

            case DATA_ITEM_CONTACT:
                $dataItem = new Contacts($this->_siteID);
                break;

            case DATA_ITEM_JOBORDER:
                $dataItem = new JobOrders($this->_siteID);
                break;

            default:
                return;
                break;
        }

        $dataItem->updateModified($dataItemID);
    }
}

?>
