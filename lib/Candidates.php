<?php
/**
 * CATS
 * Candidates Library
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
 * @version    $Id: Candidates.php 3813 2007-12-05 23:16:22Z brian $
 */

include_once('./lib/Attachments.php');
include_once('./lib/Pipelines.php');
include_once('./lib/History.php');
include_once('./lib/SavedLists.php');
include_once('./lib/ExtraFields.php');
include_once('lib/DataGrid.php');


// function from: https://stackoverflow.com/questions/9831077/how-to-url-encode-only-non-ascii-symbols-of-url-in-php-but-leave-reserved-symbo
function url_path_encode($url) {
    $path = parse_url($url, PHP_URL_PATH);
    if (strpos($path,'%') !== false) return $url; //avoid double encoding
    else {
        $encoded_path = array_map('urlencode', explode('/', $path));
        return str_replace($path, implode('/', $encoded_path), $url);
    }   
}
    
/**
 *  Candidates Library
 *  @package    CATS
 *  @subpackage Library
 */
class Candidates
{
    private $_db;
    private $_siteID;

    public $extraFields;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        $this->extraFields = new ExtraFields($siteID, DATA_ITEM_CANDIDATE);
    }

    /**
     * Adds a candidate to the database and returns its candidate ID.
     *
     * @param string First name.
     * @param string Middle name / initial.
     * @param string Last name.
     * @param string Primary e-mail address.
     * @param string Secondary e-mail address.
     * @param string Home phone number.
     * @param string Mobile phone number.
     * @param string Work phone number.
     * @param string Address (can be multiple lines).
     * @param string City.
     * @param string State / province.
     * @param string Postal code.
     * @param string Source where this candidate was found.
     * @param string Key skills.
     * @param string Date available.
     * @param string Current employer.
     * @param boolean Is this candidate willing to relocate?
     * @param string Current pay rate / salary.
     * @param string Desired pay rate / salary.
     * @param string Misc. candidate notes.
     * @param string Candidate's personal web site.
     * @param integer Entered-by user ID.
     * @param integer Owner user ID.
     * @param string EEO gender, or '' to not specify.
     * @param string EEO gender, or '' to not specify.
     * @param string EEO veteran status, or '' to not specify.
     * @param string EEO disability status, or '' to not specify.
     * @param boolean Skip creating a history entry?
     * @return integer Candidate ID of new candidate, or -1 on failure.
     */
    public function add($firstName, $middleName, $lastName, $email1, $email2,
        $phoneHome, $phoneCell, $phoneWork, $address, $city, $state, $zip,
        $source, $keySkills, $dateAvailable, $currentEmployer, $canRelocate,
        $currentPay, $desiredPay, $notes, $webSite, $bestTimeToCall, $enteredBy, $owner,
        $chineseName, $jobTitle, $extraGender, $maritalStatus, $birthYear, $highestDegree,
        $major, $nationality, $facebook, $github, $linkedin, $googleplus, $twitter,
        $link1, $link2, $link3, $line, $qq, $skype, $wechat, $functions, $jobLevel,
        $gender = '', $race = '', $veteran = '', $disability = '',
        $skipHistory = false)
    {
        $sql = sprintf(
            "INSERT INTO candidate (
                first_name,
                middle_name,
                last_name,
                email1,
                email2,
                phone_home,
                phone_cell,
                phone_work,
                address,
                city,
                state,
                zip,
                source,
                key_skills,
                date_available,
                current_employer,
                can_relocate,
                current_pay,
                desired_pay,
                notes,
                web_site,
                best_time_to_call,
                entered_by,
                is_hot,
                owner,
                site_id,
                date_created,
                date_modified,
                chinese_name, job_title, extra_gender, marital_status, birth_year, highest_degree,
                major, nationality, facebook, github, linkedin, googleplus, twitter,
                link1, link2, link3, line, qq, skype, wechat, functions, job_level,
                eeo_ethnic_type_id,
                eeo_veteran_type_id,
                eeo_disability_status,
                eeo_gender
            )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                0,
                %s,
                %s,
                NOW(),
                NOW(),
                %s, %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s, %s, %s, %s, %s, 
                %s,
                %s,
                %s,
                %s
            )",
            $this->_db->makeQueryString($firstName),
            $this->_db->makeQueryString($middleName),
            $this->_db->makeQueryString($lastName),
            $this->_db->makeQueryString($email1),
            $this->_db->makeQueryString($email2),
            $this->_db->makeQueryString($phoneHome),
            $this->_db->makeQueryString($phoneCell),
            $this->_db->makeQueryString($phoneWork),
            $this->_db->makeQueryString($address),
            $this->_db->makeQueryString($city),
            $this->_db->makeQueryString($state),
            $this->_db->makeQueryString($zip),
            $this->_db->makeQueryString($source),
            $this->_db->makeQueryString($keySkills),
            $this->_db->makeQueryStringOrNULL($dateAvailable),
            $this->_db->makeQueryString($currentEmployer),
            ($canRelocate ? '1' : '0'),
            $this->_db->makeQueryString($currentPay),
            $this->_db->makeQueryString($desiredPay),
            $this->_db->makeQueryString($notes),
            $this->_db->makeQueryString($webSite),
            $this->_db->makeQueryString($bestTimeToCall),
            $this->_db->makeQueryInteger($enteredBy),
            $this->_db->makeQueryInteger($owner),
            $this->_siteID,
            
            $this->_db->makeQueryString($chineseName),
            $this->_db->makeQueryString($jobTitle),
            $this->_db->makeQueryString($extraGender),
            $this->_db->makeQueryString($maritalStatus),
            $this->_db->makeQueryString($birthYear),
            $this->_db->makeQueryString($highestDegree),
            
            $this->_db->makeQueryString($major),
            $this->_db->makeQueryString($nationality),
            $this->_db->makeQueryString($facebook),
            $this->_db->makeQueryString($github),
            $this->_db->makeQueryString($linkedin),
            $this->_db->makeQueryString($googleplus),
            $this->_db->makeQueryString($twitter),
            
            $this->_db->makeQueryString($link1),
            $this->_db->makeQueryString($link2),
            $this->_db->makeQueryString($link3),
            $this->_db->makeQueryString($line),
            $this->_db->makeQueryString($qq),
            $this->_db->makeQueryString($skype),
            $this->_db->makeQueryString($wechat),
            $this->_db->makeQueryString($functions),
            $this->_db->makeQueryString($jobLevel),
            
            $this->_db->makeQueryInteger($race),
            $this->_db->makeQueryInteger($veteran),
            $this->_db->makeQueryString($disability),
            $this->_db->makeQueryString($gender)
        );
        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        $candidateID = $this->_db->getLastInsertID();

        if (!$skipHistory)
        {
            $history = new History($this->_siteID);
            $history->storeHistoryNew(DATA_ITEM_CANDIDATE, $candidateID);
        }

        return $candidateID;
    }

    /**
     * Updates a candidate.
     *
     * @param integer Candidate ID to update.
     * @param string First name.
     * @param string Middle name / initial.
     * @param string Last name.
     * @param string Primary e-mail address.
     * @param string Secondary e-mail address.
     * @param string Home phone number.
     * @param string Mobile phone number.
     * @param string Work phone number.
     * @param string Address (can be multiple lines).
     * @param string City.
     * @param string State / province.
     * @param string Postal code.
     * @param string Source where this candidate was found.
     * @param string Key skills.
     * @param string Date available.
     * @param string Current employer.
     * @param boolean Is this candidate willing to relocate?
     * @param string Current pay rate / salary.
     * @param string Desired pay rate / salary.
     * @param string Misc. candidate notes.
     * @param string Candidate's personal web site.
     * @param integer Owner user ID.
     * @param string EEO gender, or '' to not specify.
     * @param string EEO gender, or '' to not specify.
     * @param string EEO veteran status, or '' to not specify.
     * @param string EEO disability status, or '' to not specify.
     * @return boolean True if successful; false otherwise.
     */
    public function update($candidateID, $isActive, $firstName, $middleName, $lastName,
        $email1, $email2, $phoneHome, $phoneCell, $phoneWork, $address,
        $city, $state, $zip, $source, $keySkills, $dateAvailable,
        $currentEmployer, $canRelocate, $currentPay, $desiredPay,
        $notes, $webSite, $bestTimeToCall, $owner, $isHot, $email, $emailAddress,
        $chineseName, $jobTitle, $extraGender, $maritalStatus, $birthYear, $highestDegree,
        $major, $nationality, $facebook, $github, $linkedin, $googleplus, $twitter,
        $link1, $link2, $link3, $line, $qq, $skype, $wechat, $functions, $jobLevel,
        $gender = '', $race = '', $veteran = '', $disability = '')
    {
        $sql = sprintf(
            "UPDATE
                candidate
            SET
                is_active             = %s,
                first_name            = %s,
                middle_name           = %s,
                last_name             = %s,
                email1                = %s,
                email2                = %s,
                phone_home            = %s,
                phone_work            = %s,
                phone_cell            = %s,
                address               = %s,
                city                  = %s,
                state                 = %s,
                zip                   = %s,
                source                = %s,
                key_skills            = %s,
                date_available        = %s,
                current_employer      = %s,
                current_pay           = %s,
                desired_pay           = %s,
                can_relocate          = %s,
                is_hot                = %s,
                notes                 = %s,
                web_site              = %s,
                best_time_to_call     = %s,
                owner                 = %s,
                date_modified         = NOW(),
                
                chinese_name          = %s,
                job_title             = %s,
                extra_gender          = %s,
                marital_status        = %s,
                birth_year            = %s,
                highest_degree        = %s,                

                major                 = %s,
                nationality           = %s,
                facebook              = %s,
                github                = %s,
                linkedin              = %s,
                googleplus            = %s,
                twitter               = %s,
                
                link1                 = %s,
                link2                 = %s,
                link3                 = %s,
                line                  = %s,
                qq                    = %s,
                skype                 = %s,
                wechat                = %s,
                functions             = %s,
                job_level             = %s,
                
                eeo_ethnic_type_id    = %s,
                eeo_veteran_type_id   = %s,
                eeo_disability_status = %s,
                eeo_gender            = %s
            WHERE
                candidate_id = %s
            AND
                site_id = %s",
            ($isActive ? '1' : '0'),
            $this->_db->makeQueryString($firstName),
            $this->_db->makeQueryString($middleName),
            $this->_db->makeQueryString($lastName),
            $this->_db->makeQueryString($email1),
            $this->_db->makeQueryString($email2),
            $this->_db->makeQueryString($phoneHome),
            $this->_db->makeQueryString($phoneWork),
            $this->_db->makeQueryString($phoneCell),
            $this->_db->makeQueryString($address),
            $this->_db->makeQueryString($city),
            $this->_db->makeQueryString($state),
            $this->_db->makeQueryString($zip),
            $this->_db->makeQueryString($source),
            $this->_db->makeQueryString($keySkills),
            $this->_db->makeQueryStringOrNULL($dateAvailable),
            $this->_db->makeQueryString($currentEmployer),
            $this->_db->makeQueryString($currentPay),
            $this->_db->makeQueryString($desiredPay),
            ($canRelocate ? '1' : '0'),
            ($isHot ? '1' : '0'),
            $this->_db->makeQueryString($notes),
            $this->_db->makeQueryString($webSite),
            $this->_db->makeQueryString($bestTimeToCall),
            $this->_db->makeQueryInteger($owner),
            
            $this->_db->makeQueryString($chineseName),
            $this->_db->makeQueryString($jobTitle),
            $this->_db->makeQueryString($extraGender),
            $this->_db->makeQueryString($maritalStatus),
            $this->_db->makeQueryString($birthYear),
            $this->_db->makeQueryString($highestDegree),
            
            $this->_db->makeQueryString($major),
            $this->_db->makeQueryString($nationality),
            $this->_db->makeQueryString($facebook),
            $this->_db->makeQueryString($github),
            $this->_db->makeQueryString($linkedin),
            $this->_db->makeQueryString($googleplus),
            $this->_db->makeQueryString($twitter),
            
            $this->_db->makeQueryString($link1),
            $this->_db->makeQueryString($link2),
            $this->_db->makeQueryString($link3),
            $this->_db->makeQueryString($line),
            $this->_db->makeQueryString($qq),
            $this->_db->makeQueryString($skype),
            $this->_db->makeQueryString($wechat),
            $this->_db->makeQueryString($functions),
            $this->_db->makeQueryString($jobLevel),
            
            $this->_db->makeQueryInteger($race),
            $this->_db->makeQueryInteger($veteran),
            $this->_db->makeQueryString($disability),
            $this->_db->makeQueryString($gender),
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );

        $preHistory = $this->get($candidateID);
        $queryResult = $this->_db->query($sql);
        $postHistory = $this->get($candidateID);

        $history = new History($this->_siteID);
        $history->storeHistoryChanges(
            DATA_ITEM_CANDIDATE, $candidateID, $preHistory, $postHistory
        );

        if (!$queryResult)
        {
            return false;
        }

        if (!empty($emailAddress))
        {
            /* Send e-mail notification. */
            //FIXME: Make subject configurable.
            $mailer = new Mailer($this->_siteID);
            $mailerStatus = $mailer->sendToOne(
                array($emailAddress, ''),
                'CATS Notification: Candidate Ownership Change',
                $email,
                true
            );
        }

        return true;
    }

    /**
     * Removes a candidate and all associated records from the system.
     *
     * @param integer Candidate ID to delete.
     * @return void
     */
    public function delete($candidateID)
    {
        /* Delete the candidate from candidate. */
        $sql = sprintf(
            "DELETE FROM
                candidate
            WHERE
                candidate_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );
        $this->_db->query($sql);

        $history = new History($this->_siteID);
        $history->storeHistoryDeleted(DATA_ITEM_CANDIDATE, $candidateID);

        /* Delete pipeline entries from candidate_joborder. */
        $sql = sprintf(
            "DELETE FROM
                candidate_joborder
            WHERE
                candidate_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );
        $this->_db->query($sql);

        /* Delete pipeline history from candidate_joborder_status_history. */
        $sql = sprintf(
            "DELETE FROM
                candidate_joborder_status_history
            WHERE
                candidate_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );
        $this->_db->query($sql);

        /* Delete from saved lists. */
        $sql = sprintf(
            "DELETE FROM
                saved_list_entry
            WHERE
                data_item_id = %s
            AND
                site_id = %s
            AND
                data_item_type = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID,
            DATA_ITEM_CANDIDATE
        );
        $this->_db->query($sql);

        /* Delete attachments. */
        $attachments = new Attachments($this->_siteID);
        $attachmentsRS = $attachments->getAll(
            DATA_ITEM_CANDIDATE, $candidateID
        );

        foreach ($attachmentsRS as $rowNumber => $row)
        {
            $attachments->delete($row['attachmentID']);
        }

        /* Delete extra fields. */
        $this->extraFields->deleteValueByDataItemID($candidateID);
    }

    /**
     * Returns all relevent candidate information for a given candidate ID.
     *
     * @param integer Candidate ID.
     * @return array Associative result set array of candidate data, or array()
     *               if no records were returned.
     */
    public function get($candidateID)
    {
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.is_active AS isActive,
                candidate.first_name AS firstName,
                candidate.middle_name AS middleName,
                candidate.last_name AS lastName,
                candidate.email1 AS email1,
                candidate.email2 AS email2,
                candidate.phone_home AS phoneHome,
                candidate.phone_work AS phoneWork,
                candidate.phone_cell AS phoneCell,
                candidate.address AS address,
                candidate.city AS city,
                candidate.state AS state,
                candidate.zip AS zip,
                candidate.source AS source,
                candidate.key_skills AS keySkills,
                candidate.current_employer AS currentEmployer,
                candidate.current_pay AS currentPay,
                candidate.desired_pay AS desiredPay,
                candidate.notes AS notes,
                candidate.owner AS owner,
                candidate.can_relocate AS canRelocate,
                candidate.web_site AS webSite,
                candidate.best_time_to_call AS bestTimeToCall,
                candidate.is_hot AS isHot,
                candidate.is_admin_hidden AS isAdminHidden,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateModified,
                COUNT(
                    candidate_joborder.joborder_id
                ) AS pipeline,
                (
                    SELECT
                        COUNT(*)
                    FROM
                        candidate_joborder_status_history
                    WHERE
                        candidate_id = %s
                    AND
                        status_to = %s
                    AND
                        site_id = %s
                ) AS submitted,
                CONCAT(
                    candidate.first_name, ' ', candidate.last_name
                ) AS candidateFullName,
                CONCAT(
                    entered_by_user.first_name, ' ', entered_by_user.last_name
                ) AS enteredByFullName,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                owner_user.email AS owner_email,
                DATE_FORMAT(
                    candidate.date_available, '%%m-%%d-%%y'
                ) AS dateAvailable,
                
                candidate.chinese_name AS chineseName,
                candidate.job_title AS jobTitle,
                candidate.extra_gender AS extraGender,
                candidate.marital_status AS maritalStatus, 
                candidate.birth_year AS birthYear, 
                candidate.highest_degree AS highestDegree, 
                candidate.major AS major, 
                candidate.nationality AS nationality, 
                candidate.facebook AS facebook, 
                candidate.github AS github, 
                candidate.linkedin AS linkedin, 
                candidate.googleplus AS googleplus, 
                candidate.twitter AS twitter, 
                candidate.link1 AS link1, 
                candidate.link2 AS link2, 
                candidate.link3 AS link3, 
                candidate.line AS line, 
                candidate.qq AS qq, 
                candidate.skype AS skype, 
                candidate.wechat AS wechat, 
                candidate.functions AS functions, 
                candidate.job_level AS jobLevel,
                
                eeo_ethnic_type.type AS eeoEthnicType,
                eeo_veteran_type.type AS eeoVeteranType,
                candidate.eeo_disability_status AS eeoDisabilityStatus,
                candidate.eeo_gender AS eeoGender,
                IF (candidate.eeo_gender = 'm',
                    'Male',
                    IF (candidate.eeo_gender = 'f',
                        'Female',
                        ''))
                     AS eeoGenderText
            FROM
                candidate
            LEFT JOIN user AS entered_by_user
                ON candidate.entered_by = entered_by_user.user_id
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
            LEFT JOIN candidate_joborder
                ON candidate.candidate_id = candidate_joborder.candidate_id
            LEFT JOIN eeo_ethnic_type
                ON eeo_ethnic_type.eeo_ethnic_type_id = candidate.eeo_ethnic_type_id
            LEFT JOIN eeo_veteran_type
                ON eeo_veteran_type.eeo_veteran_type_id = candidate.eeo_veteran_type_id
            WHERE
                candidate.candidate_id = %s
            AND
                candidate.site_id = %s
            GROUP BY
                candidate.candidate_id",
            $this->_db->makeQueryInteger($candidateID),
            PIPELINE_STATUS_SUBMITTED,
            $this->_siteID,
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Returns all candidate information relevent for the Edit Candidate page
     * for a given candidate ID.
     *
     * @param integer Candidate ID.
     * @return array Associative result set array of candidate data, or array()
     *               if no records were returned.
     */
    public function getForEditing($candidateID)
    {
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.is_active AS isActive,
                candidate.first_name AS firstName,
                candidate.middle_name AS middleName,
                candidate.last_name AS lastName,
                candidate.email1 AS email1,
                candidate.email2 AS email2,
                candidate.phone_home AS phoneHome,
                candidate.phone_work AS phoneWork,
                candidate.phone_cell AS phoneCell,
                candidate.address AS address,
                candidate.city AS city,
                candidate.state AS state,
                candidate.zip AS zip,
                candidate.source AS source,
                candidate.key_skills AS keySkills,
                candidate.current_employer AS currentEmployer,
                candidate.current_pay AS currentPay,
                candidate.desired_pay AS desiredPay,
                candidate.notes AS notes,
                candidate.owner AS owner,
                candidate.can_relocate AS canRelocate,
                candidate.web_site AS webSite,
                candidate.best_time_to_call AS bestTimeToCall,
                candidate.is_hot AS isHot,
                
                candidate.chinese_name AS chineseName,
                candidate.job_title AS jobTitle,
                candidate.extra_gender AS extraGender,
                candidate.marital_status AS maritalStatus, 
                candidate.birth_year AS birthYear, 
                candidate.highest_degree AS highestDegree, 
                candidate.major AS major, 
                candidate.nationality AS nationality, 
                candidate.facebook AS facebook, 
                candidate.github AS github, 
                candidate.linkedin AS linkedin, 
                candidate.googleplus AS googleplus, 
                candidate.twitter AS twitter, 
                candidate.link1 AS link1, 
                candidate.link2 AS link2, 
                candidate.link3 AS link3, 
                candidate.line AS line, 
                candidate.qq AS qq, 
                candidate.skype AS skype, 
                candidate.wechat AS wechat, 
                candidate.functions AS functions, 
                candidate.job_level AS jobLevel,
                
                candidate.eeo_ethnic_type_id AS eeoEthnicTypeID,
                candidate.eeo_veteran_type_id AS eeoVeteranTypeID,
                candidate.eeo_disability_status AS eeoDisabilityStatus,
                candidate.eeo_gender AS eeoGender,
                candidate.is_admin_hidden AS isAdminHidden,
                DATE_FORMAT(
                    candidate.date_available, '%%m-%%d-%%y'
                ) AS dateAvailable
            FROM
                candidate
            WHERE
                candidate.candidate_id = %s
            AND
                candidate.site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }

    // FIXME: Document me.
    public function getExport($IDs)
    {
        if (count($IDs) != 0)
        {
            $IDsValidated = array();
            
            foreach ($IDs as $id)
            {
                $IDsValidated[] = $this->_db->makeQueryInteger($id);
            }
            
            $criterion = 'AND candidate.candidate_id IN ('.implode(',', $IDsValidated).')';
        }
        else
        {
            $criterion = '';
        }

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.last_name AS lastName,
                candidate.first_name AS firstName,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.email1 AS email1,
                candidate.key_skills as keySkills
            FROM
                candidate
            WHERE
                candidate.site_id = %s
                %s
            ORDER BY
                candidate.last_name ASC,
                candidate.first_name ASC",
            $this->_siteID,
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns a candidate ID that matches the specified e-mail address.
     *
     * @param string Candidate e-mail address,
     * @return integer Candidate ID, or -1 if no matching candidates were
     *                 found.
     */
    public function getIDByEmail($email)
    {
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID
            FROM
                candidate
            WHERE
            (
                candidate.email1 = %s
                OR candidate.email2 = %s
            )
            AND
                candidate.site_id = %s",
            $this->_db->makeQueryString($email),
            $this->_db->makeQueryString($email),
            $this->_siteID
        );
        $rs = $this->_db->getAssoc($sql);

        if (empty($rs))
        {
            return -1;
        }

        return $rs['candidateID'];
    }
    /**
     * Returns a candidate ID that matches the specified social media id
     *
     * @param string Candidate social media type: id,
     * @return integer Candidate ID, or -1 if no matching candidates were
     *                 found.
     */
    public function getIDBySocialMedia($social)
    {
        $query = array();
        if (!empty($social['wechat']))
        {
            $query[] = 'candidate.wechat=' . $this->_db->makeQueryString($social['wechat']);
        }
        if (!empty($social['line']))
        {
            $query[] = 'candidate.line=' . $this->_db->makeQueryString($social['line']);
        }
        if (!empty($social['skype']))
        {
            $query[] = 'candidate.skype=' . $this->_db->makeQueryString($social['skype']);
        }
        if (!empty($social['qq']))
        {
            $query[] = 'candidate.qq=' . $this->_db->makeQueryString($social['qq']);
        }
        if (empty($query))
        {
            return -1;
        }
        $query = join(" OR ",$query);
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID
            FROM
                candidate
            WHERE
            (
                %s
            )
            AND
                candidate.site_id = %s",
            $query,
            $this->_siteID
        );
        $rs = $this->_db->getAssoc($sql);

        if (empty($rs))
        {
            return -1;
        }

        return $rs['candidateID'];
    }
    public function getIDByPhone($phone)
    {
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID
            FROM
                candidate
            WHERE
            (
                candidate.phone_home = %s
                OR candidate.phone_cell = %s
                OR candidate.phone_work = %s
            )
            AND
                candidate.site_id = %s",
            $this->_db->makeQueryString($phone),
            $this->_db->makeQueryString($phone),
            $this->_db->makeQueryString($phone),
            $this->_siteID
        );
        $rs = $this->_db->getAssoc($sql);
         
        if (empty($rs))
        {
            return -1;
        }
         
        return $rs['candidateID'];
    }
        
    public function getIDByLink($link)
    {
        // case: https://www.linkedin.com/in/%E5%B3%BB%E5%84%BC-%E6%9C%B1-a32399a3/
        // use rawurlencode
        // implode('/', array_map('rawurlencode', explode('/', $link)))
        // linkedin.com/in/峻儼-??a32399a3 => linkedin.com/in/%E5%B3%BB%E5%84%BC-%E6%9C%B1-a32399a3
        // str_replace('%', '\%', $link)
        // linkedin.com/in/%E5%B3%BB%E5%84%BC-%E6%9C%B1-a32399a3 => linkedin.com/in/\%E5\%B3\%BB\%E5\%84\%BC-\%E6\%9C\%B1-a32399a3
        if (strpos($link,'%') !== false) 
        {
            $encLink4Query = trim($link);
        }
        else
        {
            $encLink4Query = trim(implode('/', array_map('rawurlencode', explode('/', $link))));
        }
        $encLink4Query = "'%" . str_replace('%', '\%', $this->_db->escapeString($encLink4Query)) . "%'";
        
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.web_site AS webSite,
                candidate.notes AS notes,
                
                candidate.facebook AS facebook, 
                candidate.github AS github, 
                candidate.linkedin AS linkedin, 
                candidate.googleplus AS googleplus, 
                candidate.twitter AS twitter, 
                candidate.link1 AS link1, 
                candidate.link2 AS link2, 
                candidate.link3 AS link3
            FROM
                candidate
            WHERE
            (
                candidate.web_site like %s
                OR candidate.web_site like %s
                OR candidate.notes like %s
                OR candidate.notes like %s
                OR candidate.facebook like %s
                OR candidate.facebook like %s
                OR candidate.github like %s
                OR candidate.github like %s
                OR candidate.linkedin like %s
                OR candidate.linkedin like %s
                OR candidate.googleplus like %s
                OR candidate.googleplus like %s
                OR candidate.twitter like %s
                OR candidate.twitter like %s
                OR candidate.link1 like %s
                OR candidate.link1 like %s
                OR candidate.link2 like %s
                OR candidate.link2 like %s
                OR candidate.link3 like %s
                OR candidate.link3 like %s
            )
            AND
                candidate.site_id = %s",
            $encLink4Query,
            $this->_db->makeQueryString('%' . trim(urlDecode($link)) . '%'),
            $encLink4Query,
            $this->_db->makeQueryString('%' . trim(urlDecode($link)) . '%'),
            $encLink4Query,
            $this->_db->makeQueryString('%' . trim(urlDecode($link)) . '%'),
            $encLink4Query,
            $this->_db->makeQueryString('%' . trim(urlDecode($link)) . '%'),
            $encLink4Query,
            $this->_db->makeQueryString('%' . trim(urlDecode($link)) . '%'),
            $encLink4Query,
            $this->_db->makeQueryString('%' . trim(urlDecode($link)) . '%'),
            $encLink4Query,
            $this->_db->makeQueryString('%' . trim(urlDecode($link)) . '%'),
            $encLink4Query,
            $this->_db->makeQueryString('%' . trim(urlDecode($link)) . '%'),
            $encLink4Query,
            $this->_db->makeQueryString('%' . trim(urlDecode($link)) . '%'),
            $encLink4Query,
            $this->_db->makeQueryString('%' . trim(urlDecode($link)) . '%'),
            $this->_siteID
        );
        $rs = $this->_db->getAllAssoc($sql);

        if (!empty($rs))
        {
            foreach ($rs as $field)
            {
                // Remove the partial matches. Use Url's path to do reverse search
                // ex.
                // $link => linkedin.com/in/pohsien
                // webSite => https://www.linkedin.com/in/pohsien-liu-83b38395
                // 
                // rtlim => remove the tail symbol
                // parse_url input is "urlDecode" content
                if (!empty($field['webSite']))
                {
                    $parsedUrl = parse_url(urlDecode(rtrim($field['webSite'], '/')));
                    // return $link . ' ' . $parsedUrl['path'];
                    if (!empty($parsedUrl) && !empty($parsedUrl['path']) && !empty(rtrim($parsedUrl['path'], '/')) && strpos($link, rtrim($parsedUrl['path'], '/')) !== false) {
                        return $field['candidateID'];                
                    }
                }
                if (!empty($field['notes']))
                {
                    $parsedUrl = parse_url(urlDecode(rtrim($field['notes'], '/')));
                    if (!empty($parsedUrl) && !empty($parsedUrl['path']) && !empty(rtrim($parsedUrl['path'], '/')) &&  strpos($link, rtrim($parsedUrl['path'], '/')) !== false) {
                        return $field['candidateID'];                
                    }
                }
                if (!empty($field['facebook']))
                {
                    $parsedUrl = parse_url(urlDecode(rtrim($field['facebook'], '/')));
                    if (!empty($parsedUrl) && !empty($parsedUrl['path']) && !empty(rtrim($parsedUrl['path'], '/')) &&  strpos($link, rtrim($parsedUrl['path'], '/')) !== false) {
                        return $field['candidateID'];                
                    }
                }
                if (!empty($field['github']))
                {
                    $parsedUrl = parse_url(urlDecode(rtrim($field['github'], '/')));
                    if (!empty($parsedUrl) && !empty($parsedUrl['path']) && !empty(rtrim($parsedUrl['path'], '/')) &&  strpos($link, rtrim($parsedUrl['path'], '/')) !== false) {
                        return $field['candidateID'];                
                    }
                }
                if (!empty($field['linkedin']))
                {
                    $parsedUrl = parse_url(urlDecode(rtrim($field['linkedin'], '/')));
                    if (!empty($parsedUrl) && !empty($parsedUrl['path']) && !empty(rtrim($parsedUrl['path'], '/')) &&  strpos($link, rtrim($parsedUrl['path'], '/')) !== false) {
                        return $field['candidateID'];                
                    }
                }
                if (!empty($field['googleplus']))
                {
                    $parsedUrl = parse_url(urlDecode(rtrim($field['googleplus'], '/')));
                    if (!empty($parsedUrl) && !empty($parsedUrl['path']) && !empty(rtrim($parsedUrl['path'], '/')) &&  strpos($link, rtrim($parsedUrl['path'], '/')) !== false) {
                        return $field['candidateID'];                
                    }
                }
                if (!empty($field['twitter']))
                {
                    $parsedUrl = parse_url(urlDecode(rtrim($field['twitter'], '/')));
                    if (!empty($parsedUrl) && !empty($parsedUrl['path']) && !empty(rtrim($parsedUrl['path'], '/')) &&  strpos($link, rtrim($parsedUrl['path'], '/')) !== false) {
                        return $field['candidateID'];                
                    }
                }
                if (!empty($field['link1']))
                {
                    $parsedUrl = parse_url(urlDecode(rtrim($field['link1'], '/')));
                    if (!empty($parsedUrl) && !empty($parsedUrl['path']) && !empty(rtrim($parsedUrl['path'], '/')) &&  strpos($link, rtrim($parsedUrl['path'], '/')) !== false) {
                        return $field['candidateID'];                
                    }
                }
                if (!empty($field['link2']))
                {
                    $parsedUrl = parse_url(urlDecode(rtrim($field['link2'], '/')));
                    if (!empty($parsedUrl) && !empty($parsedUrl['path']) && !empty(rtrim($parsedUrl['path'], '/')) &&  strpos($link, rtrim($parsedUrl['path'], '/')) !== false) {
                        return $field['candidateID'];                
                    }
                }
                if (!empty($field['link3']))
                {
                    $parsedUrl = parse_url(urlDecode(rtrim($field['link3'], '/')));
                    if (!empty($parsedUrl) && !empty($parsedUrl['path']) && !empty(rtrim($parsedUrl['path'], '/')) &&  strpos($link, rtrim($parsedUrl['path'], '/')) !== false) {
                        return $field['candidateID'];                
                    }
                }
            }
        }

        return -1;            
    }
     
                                                                                                                                                                                                                                                                            
    /**
     * Returns the number of candidates in the system.  Useful
     * for determining if the friendly "no candidates in system"
     * should be displayed rather than the datagrid.
     *
     * @param boolean Include administratively hidden candidates?
     * @return integer Number of Candidates in site.
     */
    public function getCount($allowAdministrativeHidden = false)
    {
        if (!$allowAdministrativeHidden)
        {
            $adminHiddenCriterion = 'AND candidate.is_admin_hidden = 0';
        }
        else
        {
            $adminHiddenCriterion = '';
        }

        $sql = sprintf(
            "SELECT
                COUNT(*) AS totalCandidates
            FROM
                candidate
            WHERE
                candidate.site_id = %s
            %s",
            $this->_siteID,
            $adminHiddenCriterion
        );

        return $this->_db->getColumn($sql, 0, 0);
    }

    /**
     * Returns the entire candidates list.
     *
     * @param boolean Include administratively hidden candidates?
     * @return array Multi-dimensional associative result set array of
     *               candidates data, or array() if no records were returned.
     */
    public function getAll($allowAdministrativeHidden = false)
    {
        if (!$allowAdministrativeHidden)
        {
            $adminHiddenCriterion = 'AND candidate.is_admin_hidden = 0';
        }
        else
        {
            $adminHiddenCriterion = '';
        }

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.last_name AS lastName,
                candidate.first_name AS firstName,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.email1 AS email1,
                candidate.key_skills AS keySkills,
                candidate.is_hot AS isHot,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                candidate.date_created AS dateCreatedSort,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                candidate
            LEFT JOIN user AS owner_user
                ON candidate.entered_by = user.user_id
            WHERE
                candidate.site_id = %s
            %s
            ORDER BY
                candidate.last_name ASC,
                candidate.first_name ASC",
            $this->_siteID,
            $adminHiddenCriterion
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all resumes for a candidate.
     *
     * @param integer Candidate ID.
     * @return array Multi-dimensional associative result set array of
     *               candidate attachments data, or array() if no records were
     *               returned.
     */
    public function getResumes($candidateID)
    {
        $sql = sprintf(
            "SELECT
                attachment.attachment_id AS attachmentID,
                attachment.data_item_id AS candidateID,
                attachment.title AS title,
                attachment.text AS text
            FROM
                attachment
            WHERE
                resume = 1
            AND
                attachment.data_item_type = %s
            AND
                attachment.data_item_id = %s
            AND
                attachment.site_id = %s",
            DATA_ITEM_CANDIDATE,
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns a candidate resume attachment by attachment.
     *
     * @param integer Attachment ID.
     * @return array Associative result set array of candidate / attachment
     *               data, or array() if no records were returned.
     */
    public function getResume($attachmentID)
    {
        $sql = sprintf(
            "SELECT
                attachment.attachment_id AS attachmentID,
                attachment.data_item_id AS candidateID,
                attachment.title AS title,
                attachment.text AS text,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName
            FROM
                attachment
            LEFT JOIN candidate
                ON attachment.data_item_id = candidate.candidate_id
                AND attachment.site_id = candidate.site_id
            WHERE
                attachment.resume = 1
            AND
                attachment.attachment_id = %s
            AND
                attachment.site_id = %s",
            $this->_db->makeQueryInteger($attachmentID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Returns personal agreement for a candidate.
     *
     * @param integer Candidate ID.
     * @return array Multi-dimensional associative result set array of
     *               candidate attachments data, or array() if no records were
     *               returned.
     */
    public function getPersonalAgreement($candidateID)
    {
        //                COUNT(*) AS count
        $sql = sprintf(
            "SELECT
                attachment.attachment_id AS attachmentID,
                attachment.data_item_id AS candidateID,
                attachment.title AS title,
                attachment.text AS text
            FROM
                attachment
            WHERE
                attachment.data_item_type = %s
            AND
                attachment.data_item_id = %s
            AND
                attachment.title like %s
            AND
                attachment.site_id = %s",
            DATA_ITEM_CANDIDATE,
            $this->_db->makeQueryInteger($candidateID),
            $this->_db->makeQueryString('%personalagreement%'),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }
    
    /**
     * Returns an array of job orders data (jobOrderID, title, companyName)
     * for the specified candidate ID.
     *
     * @param integer Candidate ID,
     * @return array Multi-dimensional associative result set array of
     *               job orders data, or array() if no records were returned.
     */
    public function getJobOrdersArray($candidateID)
    {
        $sql = sprintf(
            "SELECT
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                company.name AS companyName
            FROM
                joborder
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN candidate_joborder
                ON joborder.joborder_id = candidate_joborder.joborder_id
            WHERE
                candidate_joborder.candidate_id = %s
            AND
                joborder.site_id = %s
            ORDER BY
                title ASC",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
     }

    /**
     * Updates a candidate's modified timestamp.
     *
     * @param integer Candidate ID.
     * @return boolean Boolean was the query executed successfully?
     */
    public function updateModified($candidateID)
    {
        $sql = sprintf(
            "UPDATE
                candidate
            SET
                date_modified = NOW()
            WHERE
                candidate_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Returns all upcoming events for the candidate.
     *
     * @param integer Candidate ID.
     * @return array Multi-dimensional associative result set array of
     *               candidate events data, or array() if no records were
     *               returned.
     */
    public function getUpcomingEvents($candidateID)
    {
        $calendar = new Calendar($this->_siteID);
        return $calendar->getUpcomingEventsByDataItem(
            DATA_ITEM_CANDIDATE, $candidateID
        );
    }

    /**
     * Gets all possible source suggestions for a site.
     *
     * @return array Multi-dimensional associative result set array of
     *               candidate sources data.
     */
    public function getPossibleSources()
    {
        $sql = sprintf(
            "SELECT
                candidate_source.source_id AS sourceID,
                candidate_source.name AS name
            FROM
                candidate_source
            WHERE
                candidate_source.site_id = %s
            ORDER BY
                candidate_source.name ASC",
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Updates a sites possible sources with an array generated
     * by getDifferencesFromList (ListEditor.php).
     *
     * @param array Result of ListEditor::getDifferencesFromList().
     * @return void
     */
    public function updatePossibleSources($updates)
    {
        $history = new History($this->_siteID);

        foreach ($updates as $update)
        {
            switch ($update[2])
            {
                case LIST_EDITOR_ADD:
                    $sql = sprintf(
                        "INSERT INTO candidate_source (
                            name,
                            site_id,
                            date_created
                         )
                         VALUES (
                            %s,
                            %s,
                            NOW()
                         )",
                         $this->_db->makeQueryString($update[0]),
                         $this->_siteID
                    );
                    $this->_db->query($sql);

                    break;

                case LIST_EDITOR_REMOVE:
                    $sql = sprintf(
                        "DELETE FROM
                            candidate_source
                         WHERE
                            source_id = %s
                         AND
                            site_id = %s",
                         $update[1],
                         $this->_siteID
                    );
                    $this->_db->query($sql);

                    break;

                case LIST_EDITOR_MODIFY:
                    $sql = sprintf(
                        "SELECT
                            name
                         FROM
                            candidate_source
                         WHERE
                            source_id = %s
                         AND
                            site_id = %s",
                         $this->_db->makeQueryInteger($update[1]),
                         $this->_siteID
                    );
                    $firstSource = $this->_db->getAssoc($sql);

                    $sql = sprintf(
                        "UPDATE
                            candidate
                         SET
                            source = %s
                         WHERE
                            source = %s
                         AND
                            site_id = %s",
                         $update[1],
                         $this->_db->makeQueryString($firstSource['name']),
                         $this->_siteID
                    );
                    $this->_db->query($sql);

                    $sql = sprintf(
                        "UPDATE
                            candidate_source
                         SET
                            name = %s
                         WHERE
                            source_id = %s
                         AND
                            site_id = %s",
                         $this->_db->makeQueryString($update[0]),
                         $this->_db->makeQueryInteger($update[1]),
                         $this->_siteID
                    );
                    $this->_db->query($sql);

                    break;

                default:
                    break;
            }
        }
    }

    /**
     * Changes the administrative hide / show flag.
     * Only can be accessed by a MSA or higher user.
     *
     * @param integer Candidate ID.
     * @param boolean Administratively hide this candidate?
     * @return boolean Was the query executed successfully?
     */    
    public function administrativeHideShow($candidateID, $state)
    {
        $sql = sprintf(
            "UPDATE
                candidate
            SET
                is_admin_hidden = %s
            WHERE
                candidate_id = %s
            AND
                site_id = %s",
            ($state ? 1 : 0),
            $this->_db->makeQueryInteger($candidateID),
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }
}


class CandidatesDataGrid extends DataGrid
{
    protected $_siteID;

    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($instanceName, $siteID, $parameters, $misc = 0)
    {
        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_assignedCriterion = "";
        $this->_dataItemIDColumn = 'candidate.candidate_id';

        $this->_classColumns = array(
            'Attachments' => array('select' => 'candidate.is_submitted AS submitted,
                                                candidate.is_attachment AS attachmentPresent',

                                     'pagerRender' => 'if ($rsData[\'submitted\'] == 1)
                                                    {
                                                        $return = \'<img src="images/job_orders.gif" alt="" width="16" height="16" title="Submitted for a Job Order" />\';
                                                    }
                                                    else
                                                    {
                                                        $return = \'<img src="images/mru/blank.gif" alt="" width="16" height="16" />\';
                                                    }

                                                    if ($rsData[\'attachmentPresent\'] == 1)
                                                    {
                                                        $return .= \'<img src="images/paperclip.gif" alt="" width="16" height="16" title="Attachment Present" />\';
                                                    }
                                                    else
                                                    {
                                                        $return .= \'<img src="images/mru/blank.gif" alt="" width="16" height="16" />\';
                                                    }

                                                    return $return;
                                                   ',
                                     'pagerWidth'    => 34,
                                     'pagerOptional' => true,
                                     'pagerNoTitle' => true,
                                     'sizable'  => false,
                                     'exportable' => false,
                                     'filter'         => 'candidate.is_submitted',
                                     'filterable' => '===~'),
                                     
            'I' => array('select' => 'candidate.is_interviewed AS haveInterview',

                                     'pagerRender' => 'if ($rsData[\'haveInterview\'] == 1)
                                                    {
                                                        $return = \'<img src="images/job_orders.gif" alt="" width="16" height="16" title="Have Interview" />\';
                                                    }
                                                    else
                                                    {
                                                        $return = \'<img src="images/mru/blank.gif" alt="" width="16" height="16" />\';
                                                    }

                                                    return $return;
                                                   ',
                                     'pagerWidth'    => 17,
                                     'filter'         => 'candidate.is_interviewed',
                                     'sizable'  => false),

            'A' => array('select' => 'candidate.is_agreement AS haveAgreement',

                                     'pagerRender' => 'if ($rsData[\'haveAgreement\'] == 1)
                                                    {
                                                        $return = \'<img src="images/job_orders.gif" alt="" width="16" height="16" title="Have Personal Agreement" />\';
                                                    }
                                                    else
                                                    {
                                                        $return = \'<img src="images/mru/blank.gif" alt="" width="16" height="16" />\';
                                                    }

                                                    return $return;
                                                   ',
                                     'pagerWidth'    => 17,
                                     'filter'         => 'candidate.is_agreement',
                                     'sizable'  => false),

            'R' => array('select' => 'candidate.is_resume AS haveResume',

                                     'pagerRender' => 'if ($rsData[\'haveResume\'] == 1)
                                                    {
                                                        $return = \'<img src="images/job_orders.gif" alt="" width="16" height="16" title="Have Resume" />\';
                                                    }
                                                    else
                                                    {
                                                        $return = \'<img src="images/mru/blank.gif" alt="" width="16" height="16" />\';
                                                    }

                                                    return $return;
                                                   ',
                                     'pagerWidth'    => 17,
                                     'filter'         => 'candidate.is_resume',
                                     'sizable'  => false),

            'E' => array('select' => 'candidate.email1 AS e1, candidate.email2 AS e2',

                                     'pagerRender' => 'if (($rsData[\'e1\'] != \'\') || ($rsData[\'e2\'] != \'\'))
                                                    {
                                                        $return = \'<img src="images/job_orders.gif" alt="" width="16" height="16" title="Have Email" />\';
                                                    }
                                                    else
                                                    {
                                                        $return = \'<img src="images/mru/blank.gif" alt="" width="16" height="16" />\';
                                                    }

                                                    return $return;
                                                   ',

                                     'pagerWidth'    => 17,
                                     'sizable'  => false,
                                     'filter'         => 'candidate.email1',
                                     'filterable' => '===~'),

            'P' => array('select' => 'candidate.phone_home AS p1, candidate.phone_cell AS p2, candidate.phone_work AS p3',

                                     'pagerRender' => 'if (($rsData[\'p1\'] != \'\') || ($rsData[\'p2\'] != \'\') || ($rsData[\'p3\'] != \'\'))
                                                    {
                                                        $return = \'<img src="images/job_orders.gif" alt="" width="16" height="16" title="Have Phone" />\';
                                                    }
                                                    else
                                                    {
                                                        $return = \'<img src="images/mru/blank.gif" alt="" width="16" height="16" />\';
                                                    }

                                                    return $return;
                                                   ',

                                     'pagerWidth'    => 17,
                                     'sizable'  => false,
                                     'filter'         => 'candidate.phone_cell',
                                     'filterable' => '===~'),

            'ID/2' =>         array( 'select'   => 'candidate.candidate_id AS candId',
                                     'pagerRender'    => 'return $rsData[\'candId\']%2;',
                                     'pagerWidth'    => 17,
                                     'filter'         => 'candidate.candidate_id%2'),

            'First Name' =>     array('select'         => 'candidate.first_name AS firstName',
                                      'pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';',
                                      'sortableColumn' => 'firstName',
                                      'pagerWidth'     => 75,
                                      'pagerOptional'  => false,
                                      'alphaNavigation'=> true,
                                      'filter'         => 'candidate.first_name'),

            'Last Name' =>      array('select'         => 'candidate.last_name AS lastName',
                                     'sortableColumn'  => 'lastName',
                                     'pagerRender'     => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';',
                                     'pagerWidth'      => 85,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filter'         => 'candidate.last_name'),

            'E-Mail' =>         array('select'   => 'candidate.email1 AS email1',
                                     'sortableColumn'     => 'email1',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'candidate.email1'),

            '2nd E-Mail' =>     array('select'   => 'candidate.email2 AS email2',
                                     'sortableColumn'     => 'email2',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'candidate.email2'),

            'Home Phone' =>     array('select'   => 'candidate.phone_home AS phoneHome',
                                     'sortableColumn'     => 'phoneHome',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'candidate.phone_home'),

            'Cell Phone' =>     array('select'   => 'candidate.phone_cell AS phoneCell',
                                     'sortableColumn'     => 'phoneCell',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'candidate.phone_cell'),

            'Work Phone' =>     array('select'   => 'candidate.phone_work AS phoneWork',
                                     'sortableColumn'     => 'phoneWork',
                                     'pagerWidth'    => 80,
                                     'filter'         => 'candidate.phone_work'),

            'Address' =>        array('select'   => 'candidate.address AS address',
                                     'sortableColumn'     => 'address',
                                     'pagerWidth'    => 250,
                                     'alphaNavigation' => true,
                                     'filter'         => 'candidate.address'),

            'City' =>           array('select'   => 'candidate.city AS city',
                                     'sortableColumn'     => 'city',
                                     'pagerWidth'    => 80,
                                     'alphaNavigation' => true,
                                     'filter'         => 'candidate.city'),


            'State' =>          array('select'   => 'candidate.state AS state',
                                     'sortableColumn'     => 'state',
                                     'filterType' => 'dropDown',
                                     'pagerWidth'    => 50,
                                     'alphaNavigation' => true,
                                     'filter'         => 'candidate.state'),

            'Zip' =>            array('select'  => 'candidate.zip AS zip',
                                     'sortableColumn'    => 'zip',
                                     'pagerWidth'   => 50,
                                     'filter'         => 'candidate.zip'),

            'Misc Notes' =>     array('select'  => 'candidate.notes AS notes',
                                     'sortableColumn'    => 'notes',
                                     'pagerWidth'   => 300,
                                     'filter'         => 'candidate.notes'),

            'Web Site' =>      array('select'  => 'candidate.web_site AS webSite',
                                     'pagerRender'     => 'return \'<a href="\'.htmlspecialchars($rsData[\'webSite\']).\'">\'.htmlspecialchars($rsData[\'webSite\']).\'</a>\';',
                                     'sortableColumn'    => 'webSite',
                                     'pagerWidth'   => 80,
                                     'filter'         => 'candidate.web_site'),

            'Key Skills' =>    array('select'  => 'candidate.key_skills AS keySkills',
                                     'pagerRender' => 'return substr(trim($rsData[\'keySkills\']), 0, 30) . (strlen(trim($rsData[\'keySkills\'])) > 30 ? \'...\' : \'\');',
                                     'sortableColumn'    => 'keySkills',
                                     'pagerWidth'   => 210,
                                     'filter'         => 'candidate.key_skills'),

            'Source' =>        array('select'  => 'candidate.source AS source',
                                     'sortableColumn'    => 'source',
                                     'pagerWidth'   => 140,
                                     'alphaNavigation' => true,
                                     'filter'         => 'candidate.source'),

            'Available' =>     array('select'   => 'DATE_FORMAT(candidate.date_available, \'%m-%d-%y\') AS dateAvailable',
                                     'sortableColumn'     => 'dateAvailable',
                                     'pagerWidth'    => 60),

            'Current Employer' => array('select'  => 'candidate.current_employer AS currentEmployer',
                                     'sortableColumn'    => 'currentEmployer',
                                     'pagerWidth'   => 125,
                                     'alphaNavigation' => true,
                                     'filter'         => 'candidate.current_employer'),

            'Current Pay' => array('select'  => 'candidate.current_pay AS currentPay',
                                     'sortableColumn'    => 'currentPay',
                                     'pagerWidth'   => 125,
                                     'filter'         => 'candidate.current_pay',
                                     'filterTypes'   => '===>=<'),

            'Desired Pay' => array('select'  => 'candidate.desired_pay AS desiredPay',
                                     'sortableColumn'    => 'desiredPay',
                                     'pagerWidth'   => 125,
                                     'filter'         => 'candidate.desired_pay',
                                     'filterTypes'   => '===>=<'),

            'Can Relocate'  => array('select'  => 'candidate.can_relocate AS canRelocate',
                                     'pagerRender'     => 'return ($rsData[\'canRelocate\'] == 0 ? \'No\' : \'Yes\');',
                                     'exportRender'     => 'return ($rsData[\'canRelocate\'] == 0 ? \'No\' : \'Yes\');',
                                     'sortableColumn'    => 'canRelocate',
                                     'pagerWidth'   => 80,
                                     'filter'         => 'candidate.can_relocate'),

            'Owner' =>         array('select'   => 'owner_user.first_name AS ownerFirstName,' .
                                                   'owner_user.last_name AS ownerLastName,' .
                                                   'CONCAT(owner_user.last_name, owner_user.first_name) AS ownerSort',
                                     'join'     => 'LEFT JOIN user AS owner_user ON candidate.owner = owner_user.user_id',
                                     'pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'ownerFirstName\'], $rsData[\'ownerLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'ownerFirstName\'] . " " .$rsData[\'ownerLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),

            'Created' =>       array('select'   => 'DATE_FORMAT(candidate.date_created, \'%m-%d-%y\') AS dateCreated',
                                     'pagerRender'      => 'return $rsData[\'dateCreated\'];',
                                     'sortableColumn'     => 'dateCreatedSort',
                                     'pagerWidth'    => 60,
                                     'filterHaving' => 'DATE(candidate.date_created)',
                                     'filterTypes'   => '===g=s'),

            'Modified' =>      array('select'   => 'DATE_FORMAT(candidate.date_modified, \'%m-%d-%y\') AS dateModified',
                                     'pagerRender'      => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'     => 'dateModifiedSort',
                                     'pagerWidth'    => 60,
                                     'pagerOptional' => false,
                                     'filterHaving' => 'DATE(candidate.date_modified)',
                                     'filterTypes'   => '===g=s'),

            /* This one only works when called from the saved list view.  Thats why it is not optional, filterable, or exportable.
             * FIXME:  Somehow make this defined in the associated savedListDataGrid class child.
             */
            'Added To List' =>  array('select'   => 'DATE_FORMAT(saved_list_entry.date_created, \'%m-%d-%y\') AS dateAddedToList,
                                                     saved_list_entry.date_created AS dateAddedToListSort',
                                     'pagerRender'      => 'return $rsData[\'dateAddedToList\'];',
                                     'sortableColumn'     => 'dateAddedToListSort',
                                     'pagerWidth'    => 60,
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'exportable' => false),

            'OwnerID' =>       array('select'    => '',
                                     'filter'    => 'candidate.owner',
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'filterDescription' => 'Only My Candidates'),

            'IsHot' =>         array('select'    => '',
                                     'filter'    => 'candidate.is_hot',
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'filterDescription' => 'Only Hot Candidates'),
                                     
                                     
            'Chinese Name' =>     array('select'         => 'candidate.chinese_name AS chineseName',
                                      'pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'chineseName\']).\'</a>\';',
                                      'sortableColumn' => 'chineseName',
                                      'pagerWidth'     => 75,
                                      'pagerOptional'   => false,
                                      'alphaNavigation' => true,
                                      'filter'         => 'candidate.chinese_name'),
                                      
            'Job Title' => array('select'  => 'candidate.job_title AS jobTitle',
                                     'sortableColumn'    => 'jobTitle',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return htmlspecialchars($rsData[\'jobTitle\']);',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.job_title'),
                                     
            'Gender' => array('select'  => 'candidate.extra_gender AS extraGender',
                                     'sortableColumn'    => 'extraGender',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return htmlspecialchars($rsData[\'extraGender\']);',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.extra_gender'),
                                     
            'Marital Status' => array('select'  => 'candidate.marital_status AS maritalStatus',
                                     'sortableColumn'    => 'maritalStatus',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return htmlspecialchars($rsData[\'maritalStatus\']);',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.marital_status'),
                                     
            'Birth Year' => array('select'  => 'candidate.birth_year AS birthYear',
                                     'sortableColumn'    => 'birthYear',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return htmlspecialchars($rsData[\'birthYear\']);',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.birth_year'),
                                     
            'Highest Education Degree' => array('select'  => 'candidate.highest_degree AS highestDegree',
                                     'sortableColumn'    => 'highestDegree',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return htmlspecialchars($rsData[\'highestDegree\']);',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.highest_degree'),
                                     
            'Major' => array('select'  => 'candidate.major AS major',
                                     'sortableColumn'    => 'major',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return htmlspecialchars($rsData[\'major\']);',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.major'),
                                     
            'Nationality' => array('select'  => 'candidate.nationality AS nationality',
                                     'sortableColumn'    => 'nationality',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return htmlspecialchars($rsData[\'nationality\']);',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.nationality'),
                                     
            'Facebook' => array('select'  => 'candidate.facebook AS facebook',
                                     'sortableColumn'    => 'facebook',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return \'<a href="\'.htmlspecialchars($rsData[\'facebook\']).\'">\'.htmlspecialchars($rsData[\'facebook\']).\'</a>\';',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.facebook'),
                                     
            'Github' => array('select'  => 'candidate.github AS github',
                                     'sortableColumn'    => 'github',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return \'<a href="\'.htmlspecialchars($rsData[\'github\']).\'">\'.htmlspecialchars($rsData[\'github\']).\'</a>\';',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.github'),
                                     
            'Linkedin' => array('select'  => 'candidate.linkedin AS linkedin',
                                     'sortableColumn'    => 'linkedin',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return \'<a href="\'.htmlspecialchars($rsData[\'linkedin\']).\'">\'.htmlspecialchars($rsData[\'linkedin\']).\'</a>\';',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.linkedin'),
                                     
            'GooglePlus' => array('select'  => 'candidate.googleplus AS googleplus',
                                     'sortableColumn'    => 'googleplus',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return \'<a href="\'.htmlspecialchars($rsData[\'googleplus\']).\'">\'.htmlspecialchars($rsData[\'googleplus\']).\'</a>\';',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.googleplus'),
                                     
            'Twitter' => array('select'  => 'candidate.twitter AS twitter',
                                     'sortableColumn'    => 'twitter',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return \'<a href="\'.htmlspecialchars($rsData[\'twitter\']).\'">\'.htmlspecialchars($rsData[\'twitter\']).\'</a>\';',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.twitter'),
                                     
            'Link1' => array('select'  => 'candidate.link1 AS link1',
                                     'sortableColumn'    => 'link1',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return \'<a href="\'.htmlspecialchars($rsData[\'link1\']).\'">\'.htmlspecialchars($rsData[\'link1\']).\'</a>\';',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.link1'),
                                     
            'Link2' => array('select'  => 'candidate.link2 AS link2',
                                     'sortableColumn'    => 'link2',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return \'<a href="\'.htmlspecialchars($rsData[\'link2\']).\'">\'.htmlspecialchars($rsData[\'link2\']).\'</a>\';',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.link2'),
                                     
            'Link3' => array('select'  => 'candidate.link3 AS link3',
                                     'sortableColumn'    => 'link3',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return \'<a href="\'.htmlspecialchars($rsData[\'link3\']).\'">\'.htmlspecialchars($rsData[\'link3\']).\'</a>\';',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.link3'),
                                     
            'Line' => array('select'  => 'candidate.line AS line',
                                     'sortableColumn'    => 'line',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return htmlspecialchars($rsData[\'line\']);',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.line'),
                                     
            'QQ' => array('select'  => 'candidate.qq AS qq',
                                     'sortableColumn'    => 'qq',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return htmlspecialchars($rsData[\'qq\']);',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.qq'),
                                     
            'QQ' => array('select'  => 'candidate.qq AS qq',
                                     'sortableColumn'    => 'qq',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return htmlspecialchars($rsData[\'qq\']);',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.qq'),
                                     
            'Skype' => array('select'  => 'candidate.skype AS skype',
                                     'sortableColumn'    => 'skype',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return htmlspecialchars($rsData[\'skype\']);',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.skype'),
                                     
            'Wechat' => array('select'  => 'candidate.wechat AS wechat',
                                     'sortableColumn'    => 'wechat',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return htmlspecialchars($rsData[\'wechat\']);',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.wechat'),
                                                                          
            'Functions' => array('select'  => 'candidate.functions AS functions',
                                     'sortableColumn'    => 'functions',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return htmlspecialchars($rsData[\'functions\']);',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.functions'),
                                     
            'Job Level' => array('select'  => 'candidate.job_level AS jobLevel',
                                     'sortableColumn'    => 'jobLevel',
                                     'pagerWidth'   => 110,
                                     'pagerRender'     => 'return htmlspecialchars($rsData[\'jobLevel\']);',
                                     'filterTypes'   => '=~==',
                                     'filter'         => 'candidate.job_level')                                     
        );

        if (US_ZIPS_ENABLED)
        {
            $this->_classColumns['Near Zipcode'] =
                               array('select'  => 'candidate.zip AS zip',
                                     'filter' => 'candidate.zip',
                                     'pagerOptional' => false,
                                     'filterTypes'   => '=@');
        }

        /* Extra fields get added as columns here. */
        $candidates = new Candidates($this->_siteID);
        $extraFieldsRS = $candidates->extraFields->getSettings();
        foreach ($extraFieldsRS as $index => $data)
        {
            $fieldName = $data['fieldName'];

            if (!isset($this->_classColumns[$fieldName]))
            {
                $columnDefinition = $candidates->extraFields->getDataGridDefinition($index, $data, $this->_db);

                /* Return false for extra fields that should not be columns. */
                if ($columnDefinition !== false)
                {
                    $this->_classColumns[$fieldName] = $columnDefinition;
                }
            }
        }

        parent::__construct($instanceName, $parameters, $misc);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array Candidates data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        // FIXME: Factor out Session dependency.
        if ($_SESSION['CATS']->isLoggedIn() && $_SESSION['CATS']->getAccessLevel() < ACCESS_LEVEL_MULTI_SA)
        {
            $adminHiddenCriterion = 'AND candidate.is_admin_hidden = 0';
        }
        else
        {
            $adminHiddenCriterion = '';
        }

        if ($this->getMiscArgument() != 0)
        {
            $savedListID = (int) $this->getMiscArgument();
            $joinSQL  .= ' INNER JOIN saved_list_entry
                                    ON saved_list_entry.data_item_type = '.DATA_ITEM_CANDIDATE.'
                                    AND saved_list_entry.data_item_id = candidate.candidate_id
                                    AND saved_list_entry.site_id = '.$this->_siteID.'
                                    AND saved_list_entry.saved_list_id = '.$savedListID;
        }
        else
        {
            $joinSQL  .= ' LEFT JOIN saved_list_entry
                                    ON saved_list_entry.data_item_type = '.DATA_ITEM_CANDIDATE.'
                                    AND saved_list_entry.data_item_id = candidate.candidate_id
                                    AND saved_list_entry.site_id = '.$this->_siteID;         
        }

        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                candidate.candidate_id AS candidateID,
                candidate.candidate_id AS exportID,
                candidate.is_hot AS isHot,
                candidate.date_modified AS dateModifiedSort,
                candidate.date_created AS dateCreatedSort,
            %s
            FROM
                candidate
            %s
            WHERE
                candidate.site_id = %s
            %s
            %s
            %s
            GROUP BY candidate.candidate_id
            %s
            %s
            %s",
            $distinct,
            $selectSQL,
            $joinSQL,
            $this->_siteID,
            $adminHiddenCriterion,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            $this->_assignedCriterion,
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }
}

/**
 *  EEO Settings Library
 *  @package    CATS
 *  @subpackage Library
 */
class EEOSettings
{
    private $_db;
    private $_siteID;
    private $_userID;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        // FIXME: Factor out Session dependency.
        $this->_userID = $_SESSION['CATS']->getUserID();
        $this->_db = DatabaseConnection::getInstance();
    }


    /**
     * Returns all EEO settings for a site.
     *
     * @return array (setting => value)
     */
    public function getAll()
    {
        /* Default values. */
        $settings = array(
            'enabled' => '0',
            'genderTracking' => '0',
            'ethnicTracking' => '0',
            'veteranTracking' => '0',
            'veteranTracking' => '0',
            'disabilityTracking' => '0',
            'canSeeEEOInfo' => false
        );

        $sql = sprintf(
            "SELECT
                settings.setting AS setting,
                settings.value AS value,
                settings.site_id AS siteID
            FROM
                settings
            WHERE
                settings.site_id = %s
            AND
                settings.settings_type = %s",
            $this->_siteID,
            SETTINGS_EEO
        );
        $rs = $this->_db->getAllAssoc($sql);

        /* Override default settings with settings from the database. */
        foreach ($rs as $rowIndex => $row)
        {
            foreach ($settings as $setting => $value)
            {
                if ($row['setting'] == $setting)
                {
                    $settings[$setting] = $row['value'];
                }
            }
        }

        $settings['canSeeEEOInfo'] = $_SESSION['CATS']->canSeeEEOInfo();

        return $settings;
    }

    /**
     * Sets an EEO setting for a site.
     *
     * @param string Setting name
     * @param string Setting value
     * @return void
     */
    public function set($setting, $value)
    {
        $sql = sprintf(
            "DELETE FROM
                settings
            WHERE
                settings.setting = %s
            AND
                site_id = %s
            AND
                settings_type = %s",
            $this->_db->makeQueryStringOrNULL($setting),
            $this->_siteID,
            SETTINGS_EEO
        );
        $this->_db->query($sql);

        $sql = sprintf(
            "INSERT INTO settings (
                setting,
                value,
                site_id,
                settings_type
            )
            VALUES (
                %s,
                %s,
                %s,
                %s
            )",
            $this->_db->makeQueryStringOrNULL($setting),
            $this->_db->makeQueryStringOrNULL($value),
            $this->_siteID,
            SETTINGS_EEO
         );
         $this->_db->query($sql);
    }
}

?>
