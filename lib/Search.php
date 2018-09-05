<?php
/**
 * CATS
 * Search Library
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
 * @version    $Id: Search.php 3587 2007-11-13 03:55:57Z will $
 */

include_once('./lib/Pager.php');
include_once('./lib/DatabaseSearch.php');

if (ENABLE_SPHINX)
{
    include_once(SPHINX_API);
}

/**
 *	Search Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class SearchUtility
{
    /**
     * Returns an excerpt of text based on incidence of keys.
     *
     * @param keys string wildcard terms
     * @param text string result text
     * @return string excerpt
     */
    public static function searchExcerpt($keywords, $text)
    {
        /* CATS fulltext encode the search string. */
        $keywords = DatabaseSearch::fulltextEncode($keywords);

        /* Create an array of keywords to highlight. */
        $keywords = self::makeKeywordsArray($keywords);

        /* Make a copy of the keywords array for manupulating below. */
        $workingKeys = $keywords;

        /* Extract a fragment per keyword, for at most 4 keywords.
         * First we collect ranges of text around each keyword, starting/ending
         * at spaces. If the sum of all fragments is too short, we look for
         * second occurrences.
         */
        $ranges = array();
        $included = array();
        $length = 0;
        while ($length < SEARCH_EXCERPT_LENGTH && count($workingKeys))
        {
            foreach ($workingKeys as $keyOffset => $key)
            {
                if ($length >= SEARCH_EXCERPT_LENGTH)
                {
                    break;
                }

                /* Escape the key for use with preg_*(). */
                $key = preg_quote($key, '/');

                /* Remember occurrence of key so we can skip over it if more occurrnces
                 * are desired.
                 */
                if (!isset($included[$key]))
                {
                    $included[$key] = 0;
                }

                $regExPass = false;

                /* Check for wildcards */
                if (strpos($key, '*') !== false)
                {
                    $newKey = str_replace('\*', '', $key);
                    $regExPass = preg_match(
                        '/' . $newKey . '/i', $text, $matches,
                        PREG_OFFSET_CAPTURE, $included[$key]
                    );
                }
                else
                {
                    $regExPass = preg_match(
                        '/\b' . $key . '\b/i', $text, $matches,
                        PREG_OFFSET_CAPTURE, $included[$key]
                    );
                }

                if ($regExPass)
                {
                    $firstMatchOffset = $matches[0][1];

                    $firstSpaceInRange = strpos($text, ' ', max(0, $firstMatchOffset - 60));
                    if ($firstSpaceInRange !== false)
                    {
                        $end = substr($text, $firstMatchOffset, 80);
                        $lastSpaceInRange = strrpos($end, ' ');

                        if ($lastSpaceInRange !== false)
                        {
                            $ranges[$firstSpaceInRange] = $firstMatchOffset + $lastSpaceInRange;
                            $length += $firstMatchOffset + $lastSpaceInRange - $firstSpaceInRange;
                            $included[$key] = $firstMatchOffset + 1;
                        }
                        else
                        {
                            unset($workingKeys[$keyOffset]);
                        }
                    }
                    else
                    {
                        unset($workingKeys[$keyOffset]);
                    }
                }
                else
                {
                    unset($workingKeys[$keyOffset]);
                }
            }
        }

        /* If we didn't find anything, return the beginning of the text up to
         * SEARCH_EXCERPT_LENGTH.
         */
        if (sizeof($ranges) == 0)
        {

            $text = DatabaseSearch::fulltextDecode($text);
            return substr($text, 0, SEARCH_EXCERPT_LENGTH);
        }

        /* Sort the text ranges by starting position. */
        ksort($ranges);

        /* For each range, in the $ranges array, compare to every other range
         * and test for overlapping ranges. Merge overlapping ranges togeather.
         * The ksort()ing makes this O(n).
         */
        $newRanges = array();
        foreach ($ranges as $rangeFrom => $rangeTo)
        {
            /* On the first loop, set the 'base range' to the first range's
             * limits and continue on to the next loop.
             */
            if (!isset($baseRangeFrom))
            {
                $baseRangeFrom = $rangeFrom;
                $baseRangeTo = $rangeTo;

                continue;
            }

            /* If the start of the current range is before the end of the
             * previous range, make the 'base range' include the new range as
             * well. Otherwise, start the 'base range' over at the limits for
             * the current range.
             */
            if ($rangeFrom <= $baseRangeTo)
            {
                $baseRangeTo = max($baseRangeTo, $rangeTo);
            }
            else
            {
                /* Every time we start the 'base range' over, store the
                 * previous combined range that we just calculated in the
                 * 'new ranges' array.
                 */
                $newRanges[$baseRangeFrom] = $baseRangeTo;

                $baseRangeFrom = $rangeFrom;
                $baseRangeTo = $rangeTo;
            }
        }

        /* Store the last combined range that we just calculated in the 'new
         * ranges' array.
         */
        $newRanges[$baseRangeFrom] = $baseRangeTo;

        /* Fetch text. */
        $out = array();
        foreach ($newRanges as $from => $to)
        {
            $out[] = substr($text, $from, $to - $from);
        }

        $text = implode(' ... ', $out);

        /* Highlight wildcards differently. */
        $keywordsWild = array();
        foreach ($keywords as $keyOffset => $key)
        {
            if (strpos($key, '*') !== false)
            {
                $keywordsWild[] = str_replace('*', '', $key);
                unset($keywords[$keyOffset]);
            }
        }
        $keywords = array_merge($keywords);

        if (!empty($keywordsWild))
        {
            $regex = implode('|', array_map(
                create_function('$string','return preg_quote($string, \'/\');'), $keywordsWild
            ));
            $text = preg_replace(
                '/(' . $regex . ')/i',
                '<span style="background-color: #ffff99">\1</span>',
                $text
           );
        }

        if (!empty($keywords))
        {
            $regex = implode('|', array_map(
                create_function('$string','return preg_quote($string, \'/\');'), $keywords
            ));
            $text = preg_replace(
                '/\b(' . $regex . ')\b/i',
                '<span style="background-color: #ffff99">\1</span>',
                $text
            );
        }

        if (isset($newRanges[0]))
        {
            $text = $text . ' ...';
        }
        else
        {
            $text = '... ' . $text . ' ...';
        }


        /* Remove AntiWord 'table bars' */
        $text = str_replace('|', '', $text);

        return DatabaseSearch::fulltextDecode($text);
    }

    /**
     * Highlights keywords in text for a resume preview and preforms CATS
     * fulltext decoding.
     *
     * @param array keywords to highlight
     * @param string resume text
     * @return string highlighted preview text
     */
    public static function makePreview($keywords, $text)
    {
        if (empty($keywords))
        {
            return DatabaseSearch::fulltextDecode($text);
        }

        /* CATS fulltext encode the search string. */
        $keywords = DatabaseSearch::fulltextEncode($keywords);

        /* Create an array of keywords to highlight. */
        $keywords = self::makeKeywordsArray($keywords);

        /* Highlight wildcards differently. */
        $keywordsWild = array();
        foreach ($keywords as $keyOffset => $key)
        {
            if (strpos($key, '*') !== false)
            {
                $keywordsWild[] = str_replace('*', '', $key);
                unset($keywords[$keyOffset]);
            }
        }
        $keywords = array_merge($keywords);

        if (!empty($keywordsWild))
        {
            $regex = implode('|', array_map(
                create_function('$string','return preg_quote($string, \'/\');'), $keywordsWild
            ));
            $text = preg_replace(
                '/(' . $regex . ')/i',
                '<span style="background-color: #ffff99">\1</span>',
                $text
           );
        }

        if (!empty($keywords))
        {
            $regex = implode('|', array_map(
                create_function('$string','return preg_quote($string, \'/\');'), $keywords
            ));
            $text = preg_replace(
                '/\b(' . $regex . ')\b/i',
                '<span style="background-color: #ffff99">\1</span>',
                $text
            );
        }

        return DatabaseSearch::fulltextDecode($text);
    }

    // FIXME: Document me.
    private static function makeKeywordsArray($string)
    {
        /* Mark up quoted strings with filler characters (no white space). */
        $string = DatabaseSearch::markUpQuotes($string);

        /* Split keywords into an array by "words" and fix quotes. */
        $keywords = explode(' ', $string);
        $keywords = array_map(
            array('DatabaseSearch', 'unMarkUpQuotes'), $keywords
        );

        /* Escape special regex characters in keys, and filter out boolean words. */
        foreach ($keywords as $index => $keyword)
        {
            $keywords[$index] = str_replace(
                array('(', ')'), '', $keywords[$index]
            );

            if (strtoupper($keyword) == 'AND' ||
                strtoupper($keyword) == 'OR' ||
                strtoupper($keyword) == 'NOT')
            {
                unset($keywords[$index]);
                continue;
            }
        }

        return array_merge($keywords);
    }
}


/**
 *	Candidates Search Library
 *	@package    CATS
 *	@subpackage Library
 */
class SearchCandidates
{
    private $_db;
    private $_siteID;
    protected $_userID = -1;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        //FIXME: Library code Session dependencies suck.
        $this->_userID = $_SESSION['CATS']->getUserID();
    }
    
    
    /**
     * Returns all candidates with full names matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array candidates data
     */
    public function byFullName($wildCardString, $sortBy, $sortDirection)
    {
        $wildCardString = '%' . str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.city AS city,
                candidate.state AS state,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.key_skills AS keySkills,
                candidate.email1 AS email1,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
            FROM
                candidate
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
            WHERE
            (
                CONCAT(candidate.first_name, ' ', candidate.last_name) LIKE %s
                OR CONCAT(candidate.last_name, ' ', candidate.first_name) LIKE %s
                OR CONCAT(candidate.last_name, ', ', candidate.first_name) LIKE %s
            )
            AND
                candidate.is_admin_hidden = 0
            AND
                candidate.site_id = %s
            ORDER BY
                %s %s",
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $this->_siteID,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all candidates with key skills matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array candidates data
     */
    public function byKeySkills($wildCardString, $sortBy, $sortDirection)
    {
        $WHERE = DatabaseSearch::makeBooleanSQLWhere(
            $wildCardString, $this->_db, 'candidate.key_skills'
        );

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.city AS city,
                candidate.state AS state,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.key_skills AS keySkills,
                candidate.email1 AS email1,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
            FROM
                candidate
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
            WHERE
                %s
            AND
                candidate.is_admin_hidden = 0
            AND
                candidate.site_id = %s
            AND
                candidate.is_active = 1
            ORDER BY
                %s %s",
            $WHERE,
            $this->_siteID,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all candidates with E-Mail addresses matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array candidates data
     */
    public function byEmail($wildCardString, $sortBy = 'firstName', $sortDirection = 'ASC')
    {
        $wildCardString = '%' . str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.city AS city,
                candidate.state AS state,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.key_skills AS keySkills,
                candidate.email1 AS email1,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
            FROM
                candidate
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
            WHERE
                candidate.email1 LIKE %s
            AND
                candidate.is_admin_hidden = 0
            AND
                candidate.site_id = %s
            ORDER BY
                %s %s",
            $wildCardString,
            $this->_siteID,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all candidates with phone numbers matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array candidates data
     */
    public function byPhone($wildCardString, $sortBy, $sortDirection)
    {
        $wildCardString = str_replace(
            array('.', '-', '(', ')'),
            '',
            $wildCardString
        );
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.city AS city,
                candidate.state AS state,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.key_skills AS keySkills,
                candidate.email1 AS email1,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
            FROM
                candidate
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
            WHERE
            (
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(candidate.phone_home, '-', ''),
                        '.', ''),
                    ')', ''),
                '(', '') LIKE %s
                OR REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(candidate.phone_cell, '-', ''),
                        '.', ''),
                    ')', ''),
                '(', '') LIKE %s
            )
            AND
                candidate.is_admin_hidden = 0
            AND
                candidate.site_id = %s
            ORDER BY
                %s %s",
            $wildCardString,
            $wildCardString,
            $this->_siteID,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }
}


/**
 *	Companies Search Library
 *	@package    CATS
 *	@subpackage Library
 */
class SearchCompanies
{
    private $_db;
    private $_siteID;
    protected $_userID = -1;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        //FIXME: Library code Session dependencies suck.
        $this->_userID = $_SESSION['CATS']->getUserID();
    }
    
    
    /**
     * Returns all companies with names matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array companies data
     */
    public function byName($wildCardString, $sortBy, $sortDirection)
    {
        $wildCardString = '%' . str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS name,
                company.city AS city,
                company.state AS state,
                company.phone1 AS phone1,
                company.url AS url,
                company.key_technologies AS keyTechnologies,
                company.is_hot AS isHot,
                DATE_FORMAT(
                    company.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    company.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                company
            LEFT JOIN user AS owner_user
                ON company.owner = owner_user.user_id
            WHERE
                company.name LIKE %s
            AND
                company.site_id = %s
            ORDER BY
                %s %s",
            $wildCardString,
            $this->_siteID,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all companies with key technologies matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array candidates data
     */
    public function byKeyTechnologies($wildCardString)
    {
        $WHERE = DatabaseSearch::makeBooleanSQLWhere(
            $wildCardString, $this->_db, 'company.key_technologies'
        );

        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS name,
                company.city AS city,
                company.state AS state,
                company.phone1 AS phone1,
                company.url AS url,
                company.key_technologies AS keyTechnologies,
                company.is_hot AS isHot,
                DATE_FORMAT(
                    company.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    company.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                company
            LEFT JOIN user AS owner_user
                ON company.owner = owner_user.user_id
            WHERE
                %s
            AND
                company.site_id = %s
            ORDER BY
                company.name ASC",
            $WHERE,
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }
}

/**
 *	Job Orders Search Library
 *	@package    CATS
 *	@subpackage Library
 */
class SearchJobOrders
{
    private $_db;
    private $_siteID;
    protected $_userID = -1;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        //FIXME: Library code Session dependencies suck.
        $this->_userID = $_SESSION['CATS']->getUserID();
    }
    
    
    /**
     * Returns all job orders with titles matching $wildCardString. If
     * activeOnly is true, only Active/OnHold/Full job orders will be shown.
     *
     * @param string wildcard match string
     * @param boolean return active job orders only
     * @return array job orders data
     */
    public function byTitle($wildCardString, $sortBy, $sortDirection,
        $activeOnly)
    {
        if ($activeOnly)
        {
            //FIXME:  Remove session dependancy.
            if ($_SESSION['CATS']->isFree())
            {
                $activeCriterion = "AND joborder.status = 'Active'";
            }
            else
            {
                $activeCriterion = "AND (joborder.status IN ('Active', 'OnHold', 'Full'))";
            }
        }
        else
        {
            $activeCriterion = "";
        }

        $WHERE = DatabaseSearch::makeBooleanSQLWhere(
            $wildCardString, $this->_db, 'joborder.title'
        );

        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS companyName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.type AS type,
                joborder.is_hot AS isHot,
                joborder.duration AS duration,
                joborder.rate_max AS maxRate,
                joborder.salary AS salary,
                joborder.status AS status,
                joborder.city AS city,
                joborder.state AS state,
                contact.first_name AS contactFirstName,
                contact.last_name AS contactLastName,
                CONCAT(
                    contact.first_name, ' ', contact.last_name
                ) AS contactFullName,
                recruiter_user.first_name AS recruiterFirstName,
                recruiter_user.last_name AS recruiterLastName,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS startDate,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    joborder.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
            FROM
                company
            LEFT JOIN joborder
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS recruiter_user
                ON joborder.recruiter = recruiter_user.user_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            LEFT JOIN contact
                ON joborder.contact_id = contact.contact_id
            WHERE
                %s
            %s
            AND
                joborder.is_admin_hidden = 0
            AND
                joborder.site_id = %s
            ORDER BY
                %s %s",
            $WHERE,
            $activeCriterion,
            $this->_siteID,
            $sortBy,
            $sortDirection
        );

        if (!eval(Hooks::get('JO_SEARCH_SQL'))) return;
        if (!eval(Hooks::get('JO_SEARCH_BY_TITLE'))) return;

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all job orders with company names matching $wildCardString. If
     * activeOnly is true, only Active/OnHold/Full job orders will be shown.
     *
     * @param string wildcard match string
     * @param boolean return active job orders only
     * @return array job orders data
     */
    public function byCompanyName($wildCardString, $sortBy, $sortDirection, $activeOnly)
    {
        $wildCardString = '%' . str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        if ($activeOnly)
        {
            //FIXME:  Remove session dependancy.
            if ($_SESSION['CATS']->isFree())
            {
                $activeCriterion = "AND joborder.status = 'Active'";
            }
            else
            {
                $activeCriterion = "AND (joborder.status IN ('Active', 'OnHold', 'Full'))";
            }
        }
        else
        {
            $activeCriterion = "";
        }

        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS companyName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.type AS type,
                joborder.is_hot AS isHot,
                joborder.duration AS duration,
                joborder.rate_max AS maxRate,
                joborder.salary AS salary,
                joborder.status AS status,
                joborder.city AS city,
                joborder.state AS state,
                contact.first_name AS contactFirstName,
                contact.last_name AS contactLastName,
                CONCAT(
                    contact.first_name, ' ', contact.last_name
                ) AS contactFullName,
                recruiter_user.first_name AS recruiterFirstName,
                recruiter_user.last_name AS recruiterLastName,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS startDate,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    joborder.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
            FROM
                company
            LEFT JOIN joborder
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS recruiter_user
                ON joborder.recruiter = recruiter_user.user_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            LEFT JOIN contact
                ON joborder.contact_id = contact.contact_id
            WHERE
                company.name LIKE %s
            %s
            AND
                joborder.is_admin_hidden = 0
            AND
                joborder.site_id = %s
            AND
                company.site_id = %s
            ORDER BY
                %s %s",
            $wildCardString,
            $activeCriterion,
            $this->_siteID,
            $this->_siteID,
            $sortBy,
            $sortDirection
        );

        if (!eval(Hooks::get('JO_SEARCH_SQL'))) return;
        if (!eval(Hooks::get('JO_SEARCH_BY_CLIENT_NAME'))) return;

        return $this->_db->getAllAssoc($sql);
    }
    
    /**
     * Returns all recently modified job orders. If activeOnly is true, 
     * only Active/OnHold/Full job orders will be shown.
     *
     * @param boolean return active job orders only
     * @return array job orders data
     */
    public function recentlyModified($sortDirection, $activeOnly, $limit)
    {
        if ($activeOnly)
        {
            //FIXME:  Remove session dependancy.
            if ($_SESSION['CATS']->isFree())
            {
                $activeCriterion = "AND joborder.status = 'Active'";
            }
            else
            {
                $activeCriterion = "AND (joborder.status IN ('Active', 'OnHold', 'Full'))";
            }
        }
        else
        {
            $activeCriterion = "";
        }

        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS companyName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.type AS type,
                joborder.is_hot AS isHot,
                joborder.duration AS duration,
                joborder.rate_max AS maxRate,
                joborder.salary AS salary,
                joborder.status AS status,
                joborder.city AS city,
                joborder.state AS state,
                contact.first_name AS contactFirstName,
                contact.last_name AS contactLastName,
                CONCAT(
                    contact.first_name, ' ', contact.last_name
                ) AS contactFullName,
                recruiter_user.first_name AS recruiterFirstName,
                recruiter_user.last_name AS recruiterLastName,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS startDate,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    joborder.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                joborder.date_modified AS dateModifiedSort
            FROM
                company
            LEFT JOIN joborder
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS recruiter_user
                ON joborder.recruiter = recruiter_user.user_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            LEFT JOIN contact
                ON joborder.contact_id = contact.contact_id
            WHERE
                joborder.site_id = %s
                %s
            AND
                company.site_id = %s
            AND
                joborder.is_admin_hidden = 0
            ORDER BY
                dateModifiedSort %s
            LIMIT 0, %s",
            $this->_siteID,
            $activeCriterion,
            $this->_siteID,
            $sortDirection,
            $limit
        );

        if (!eval(Hooks::get('JO_SEARCH_SQL'))) return;

        return $this->_db->getAllAssoc($sql);
    }
}


/**
 *	Contacts Search Library
 *	@package    CATS
 *	@subpackage Library
 */
class ContactsSearch
{
    private $_db;
    private $_siteID;
    protected $_userID = -1;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        //FIXME: Library code Session dependencies suck.
        $this->_userID = $_SESSION['CATS']->getUserID();
    }
    
    
    /**
     * Returns all contacts with full names matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array contacts data
     */
    public function byFullName($wildCardString, $sortBy, $sortDirection)
    {
        $wildCardString = '%' . str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        $sql = sprintf(
            "SELECT
                contact.contact_id AS contactID,
                contact.company_id AS companyID,
                contact.last_name AS lastName,
                contact.first_name AS firstName,
                contact.title AS title,
                contact.phone_work AS phoneWork,
                contact.phone_cell AS phoneCell,
                contact.phone_other AS phoneOther,
                contact.email1 AS email1,
                contact.email2 AS email2,
                contact.is_hot AS isHotContact,
                contact.left_company AS leftCompany,
                DATE_FORMAT(
                    contact.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    contact.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                company.name AS companyName,
                company.is_hot AS isHotCompany
            FROM
                contact
            LEFT JOIN company
                ON contact.company_id = company.company_id
            LEFT JOIN user AS owner_user
                ON contact.owner = owner_user.user_id
            WHERE
            (
                CONCAT(contact.first_name, ' ', contact.last_name) LIKE %s
                OR CONCAT(contact.last_name, ' ', contact.first_name) LIKE %s
                OR CONCAT(contact.last_name, ', ', contact.first_name) LIKE %s
            )
            AND
                contact.site_id = %s
            AND
                company.site_id = %s
            ORDER BY
                %s %s",
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $this->_siteID,
            $this->_siteID,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all contacts with company names matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array contacts data
     */
    public function byCompanyName($wildCardString, $sortBy,
        $sortDirection)
    {
        $wildCardString = '%' . str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        $sql = sprintf(
            "SELECT
                contact.contact_id AS contactID,
                contact.company_id AS companyID,
                contact.last_name AS lastName,
                contact.first_name AS firstName,
                contact.title AS title,
                contact.phone_work AS phoneWork,
                contact.phone_cell AS phoneCell,
                contact.phone_other AS phoneOther,
                contact.email1 AS email1,
                contact.email2 AS email2,
                contact.is_hot AS isHotContact,
                contact.left_company AS leftCompany,
                DATE_FORMAT(
                    contact.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    contact.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                company.name AS companyName,
                company.is_hot AS isHotCompany
            FROM
                contact
            LEFT JOIN company
                ON contact.company_id = company.company_id
            LEFT JOIN user AS owner_user
                ON contact.owner = owner_user.user_id
            WHERE
                company.name LIKE %s
            AND
                contact.site_id = %s
            AND
                company.site_id = %s
            ORDER BY
                %s %s",
            $wildCardString,
            $this->_siteID,
            $this->_siteID,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all contacts with titles matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array contacts data
     */
    public function byTitle($wildCardString, $sortBy, $sortDirection)
    {
        $wildCardString = '%' . str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        $sql = sprintf(
            "SELECT
                contact.contact_id AS contactID,
                contact.company_id AS companyID,
                contact.last_name AS lastName,
                contact.first_name AS firstName,
                contact.title AS title,
                contact.phone_work AS phoneWork,
                contact.phone_cell AS phoneCell,
                contact.phone_other AS phoneOther,
                contact.email1 AS email1,
                contact.email2 AS email2,
                contact.is_hot AS isHotContact,
                contact.left_company AS leftCompany,
                DATE_FORMAT(
                    contact.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    contact.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                company.name AS companyName,
                contact.is_hot AS isHotCompany
            FROM
                contact
            LEFT JOIN company
                ON contact.company_id = company.company_id
            LEFT JOIN user AS owner_user
                ON contact.owner = owner_user.user_id
            WHERE
                contact.title LIKE %s
            AND
                contact.site_id = %s
            AND
                company.site_id = %s
            ORDER BY
                %s %s",
            $wildCardString,
            $this->_siteID,
            $this->_siteID,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }
}

function is_simplified($str)
{
    $len = mb_strlen($str, 'utf-8');

    return ($len != mb_strlen(iconv('UTF-8', 'cp950//IGNORE', $str), 'cp950')) ? true : false;
}

function is_traditional($str)
{
    $len = mb_strlen($str, 'utf-8');

    // gbk 包含 big5 內的字元，所以不能用 gbk
    return ($len != mb_strlen(iconv('UTF-8', 'gb2312//IGNORE', $str), 'gb2312')) ? true : false;
}

class Trans  
{  
         
        private $utf8_gb2312;  
        private $utf8_big5;  
         
        public function __construct(){  
                        $this->utf8_gb2312 = "么万与丑专业丛东丝丢两严丧个丬丰临为丽举么义乌乐乔习乡书买乱争于亏云亘亚产亩亲亵亸亿仅从仑仓仪们价众优伙会伛伞伟传伤伥伦伧伪伫体余佣佥侠侣侥侦侧侨侩侪侬俣俦俨俩俪俭债倾偬偻偾偿傥傧储傩儿兑兖党兰关兴兹养兽冁内冈册写军农冢冯冲决况冻净凄凉凌减凑凛几凤凫凭凯击凼凿刍划刘则刚创删别刬刭刽刿剀剂剐剑剥剧劝办务劢动励劲劳势勋勐勚匀匦匮区医华协单卖卢卤卧卫却卺厂厅历厉压厌厍厕厢厣厦厨厩厮县参叆叇双发变叙叠叶号叹叽吁后吓吕吗吣吨听启吴呒呓呕呖呗员呙呛呜咏咔咙咛咝咤咴咸哌响哑哒哓哔哕哗哙哜哝哟唛唝唠唡唢唣唤唿啧啬啭啮啰啴啸喷喽喾嗫呵嗳嘘嘤嘱噜噼嚣嚯团园囱围囵国图圆圣圹场坂坏块坚坛坜坝坞坟坠垄垅垆垒垦垧垩垫垭垯垱垲垴埘埙埚埝埯堑堕塆墙壮声壳壶壸处备复够头夸夹夺奁奂奋奖奥妆妇妈妩妪妫姗姜娄娅娆娇娈娱娲娴婳婴婵婶媪嫒嫔嫱嬷孙学孪宁宝实宠审宪宫宽宾寝对寻导寿将尔尘尧尴尸尽层屃屉届属屡屦屿岁岂岖岗岘岙岚岛岭岳岽岿峃峄峡峣峤峥峦崂崃崄崭嵘嵚嵛嵝嵴巅巩巯币帅师帏帐帘帜带帧帮帱帻帼幂幞干并广庄庆庐庑库应庙庞废庼廪开异弃张弥弪弯弹强归当录彟彦彻径徕御忆忏忧忾怀态怂怃怄怅怆怜总怼怿恋恳恶恸恹恺恻恼恽悦悫悬悭悯惊惧惨惩惫惬惭惮惯愍愠愤愦愿慑慭憷懑懒懔戆戋戏戗战戬户扎扑扦执扩扪扫扬扰抚抛抟抠抡抢护报担拟拢拣拥拦拧拨择挂挚挛挜挝挞挟挠挡挢挣挤挥挦捞损捡换捣据捻掳掴掷掸掺掼揸揽揿搀搁搂搅携摄摅摆摇摈摊撄撑撵撷撸撺擞攒敌敛数斋斓斗斩断无旧时旷旸昙昼昽显晋晒晓晔晕晖暂暧札术朴机杀杂权条来杨杩杰极构枞枢枣枥枧枨枪枫枭柜柠柽栀栅标栈栉栊栋栌栎栏树栖样栾桊桠桡桢档桤桥桦桧桨桩梦梼梾检棂椁椟椠椤椭楼榄榇榈榉槚槛槟槠横樯樱橥橱橹橼檐檩欢欤欧歼殁殇残殒殓殚殡殴毁毂毕毙毡毵氇气氢氩氲汇汉污汤汹沓沟没沣沤沥沦沧沨沩沪沵泞泪泶泷泸泺泻泼泽泾洁洒洼浃浅浆浇浈浉浊测浍济浏浐浑浒浓浔浕涂涌涛涝涞涟涠涡涢涣涤润涧涨涩淀渊渌渍渎渐渑渔渖渗温游湾湿溃溅溆溇滗滚滞滟滠满滢滤滥滦滨滩滪漤潆潇潋潍潜潴澜濑濒灏灭灯灵灾灿炀炉炖炜炝点炼炽烁烂烃烛烟烦烧烨烩烫烬热焕焖焘煅煳熘爱爷牍牦牵牺犊犟状犷犸犹狈狍狝狞独狭狮狯狰狱狲猃猎猕猡猪猫猬献獭玑玙玚玛玮环现玱玺珉珏珐珑珰珲琎琏琐琼瑶瑷璇璎瓒瓮瓯电画畅畲畴疖疗疟疠疡疬疮疯疱疴痈痉痒痖痨痪痫痴瘅瘆瘗瘘瘪瘫瘾瘿癞癣癫癯皑皱皲盏盐监盖盗盘眍眦眬着睁睐睑瞒瞩矫矶矾矿砀码砖砗砚砜砺砻砾础硁硅硕硖硗硙硚确硷碍碛碜碱碹磙礼祎祢祯祷祸禀禄禅离秃秆种积称秽秾稆税稣稳穑穷窃窍窑窜窝窥窦窭竖竞笃笋笔笕笺笼笾筑筚筛筜筝筹签简箓箦箧箨箩箪箫篑篓篮篱簖籁籴类籼粜粝粤粪粮糁糇紧絷纟纠纡红纣纤纥约级纨纩纪纫纬纭纮纯纰纱纲纳纴纵纶纷纸纹纺纻纼纽纾线绀绁绂练组绅细织终绉绊绋绌绍绎经绐绑绒结绔绕绖绗绘给绚绛络绝绞统绠绡绢绣绤绥绦继绨绩绪绫绬续绮绯绰绱绲绳维绵绶绷绸绹绺绻综绽绾绿缀缁缂缃缄缅缆缇缈缉缊缋缌缍缎缏缐缑缒缓缔缕编缗缘缙缚缛缜缝缞缟缠缡缢缣缤缥缦缧缨缩缪缫缬缭缮缯缰缱缲缳缴缵罂网罗罚罢罴羁羟羡翘翙翚耢耧耸耻聂聋职聍联聩聪肃肠肤肷肾肿胀胁胆胜胧胨胪胫胶脉脍脏脐脑脓脔脚脱脶脸腊腌腘腭腻腼腽腾膑臜舆舣舰舱舻艰艳艹艺节芈芗芜芦苁苇苈苋苌苍苎苏苘苹茎茏茑茔茕茧荆荐荙荚荛荜荞荟荠荡荣荤荥荦荧荨荩荪荫荬荭荮药莅莜莱莲莳莴莶获莸莹莺莼萚萝萤营萦萧萨葱蒇蒉蒋蒌蓝蓟蓠蓣蓥蓦蔷蔹蔺蔼蕲蕴薮藁藓虏虑虚虫虬虮虽虾虿蚀蚁蚂蚕蚝蚬蛊蛎蛏蛮蛰蛱蛲蛳蛴蜕蜗蜡蝇蝈蝉蝎蝼蝾螀螨蟏衅衔补衬衮袄袅袆袜袭袯装裆裈裢裣裤裥褛褴襁襕见观觃规觅视觇览觉觊觋觌觍觎觏觐觑觞触觯詟誉誊讠计订讣认讥讦讧讨让讪讫训议讯记讱讲讳讴讵讶讷许讹论讻讼讽设访诀证诂诃评诅识诇诈诉诊诋诌词诎诏诐译诒诓诔试诖诗诘诙诚诛诜话诞诟诠诡询诣诤该详诧诨诩诪诫诬语诮误诰诱诲诳说诵诶请诸诹诺读诼诽课诿谀谁谂调谄谅谆谇谈谊谋谌谍谎谏谐谑谒谓谔谕谖谗谘谙谚谛谜谝谞谟谠谡谢谣谤谥谦谧谨谩谪谫谬谭谮谯谰谱谲谳谴谵谶谷豮贝贞负贠贡财责贤败账货质贩贪贫贬购贮贯贰贱贲贳贴贵贶贷贸费贺贻贼贽贾贿赀赁赂赃资赅赆赇赈赉赊赋赌赍赎赏赐赑赒赓赔赕赖赗赘赙赚赛赜赝赞赟赠赡赢赣赪赵赶趋趱趸跃跄跖跞践跶跷跸跹跻踊踌踪踬踯蹑蹒蹰蹿躏躜躯车轧轨轩轪轫转轭轮软轰轱轲轳轴轵轶轷轸轹轺轻轼载轾轿辀辁辂较辄辅辆辇辈辉辊辋辌辍辎辏辐辑辒输辔辕辖辗辘辙辚辞辩辫边辽达迁过迈运还这进远违连迟迩迳迹适选逊递逦逻遗遥邓邝邬邮邹邺邻郁郄郏郐郑郓郦郧郸酝酦酱酽酾酿释里鉅鉴銮錾钆钇针钉钊钋钌钍钎钏钐钑钒钓钔钕钖钗钘钙钚钛钝钞钟钠钡钢钣钤钥钦钧钨钩钪钫钬钭钮钯钰钱钲钳钴钵钶钷钸钹钺钻钼钽钾钿铀铁铂铃铄铅铆铈铉铊铋铍铎铏铐铑铒铕铗铘铙铚铛铜铝铞铟铠铡铢铣铤铥铦铧铨铪铫铬铭铮铯铰铱铲铳铴铵银铷铸铹铺铻铼铽链铿销锁锂锃锄锅锆锇锈锉锊锋锌锍锎锏锐锑锒锓锔锕锖锗错锚锜锞锟锠锡锢锣锤锥锦锨锩锫锬锭键锯锰锱锲锳锴锵锶锷锸锹锺锻锼锽锾锿镀镁镂镃镆镇镈镉镊镌镍镎镏镐镑镒镕镖镗镙镚镛镜镝镞镟镠镡镢镣镤镥镦镧镨镩镪镫镬镭镮镯镰镱镲镳镴镶长门闩闪闫闬闭问闯闰闱闲闳间闵闶闷闸闹闺闻闼闽闾闿阀阁阂阃阄阅阆阇阈阉阊阋阌阍阎阏阐阑阒阓阔阕阖阗阘阙阚阛队阳阴阵阶际陆陇陈陉陕陧陨险随隐隶隽难雏雠雳雾霁霉霭靓静靥鞑鞒鞯鞴韦韧韨韩韪韫韬韵页顶顷顸项顺须顼顽顾顿颀颁颂颃预颅领颇颈颉颊颋颌颍颎颏颐频颒颓颔颕颖颗题颙颚颛颜额颞颟颠颡颢颣颤颥颦颧风飏飐飑飒飓飔飕飖飗飘飙飚飞飨餍饤饥饦饧饨饩饪饫饬饭饮饯饰饱饲饳饴饵饶饷饸饹饺饻饼饽饾饿馀馁馂馃馄馅馆馇馈馉馊馋馌馍馎馏馐馑馒馓馔馕马驭驮驯驰驱驲驳驴驵驶驷驸驹驺驻驼驽驾驿骀骁骂骃骄骅骆骇骈骉骊骋验骍骎骏骐骑骒骓骔骕骖骗骘骙骚骛骜骝骞骟骠骡骢骣骤骥骦骧髅髋髌鬓魇魉鱼鱽鱾鱿鲀鲁鲂鲄鲅鲆鲇鲈鲉鲊鲋鲌鲍鲎鲏鲐鲑鲒鲓鲔鲕鲖鲗鲘鲙鲚鲛鲜鲝鲞鲟鲠鲡鲢鲣鲤鲥鲦鲧鲨鲩鲪鲫鲬鲭鲮鲯鲰鲱鲲鲳鲴鲵鲶鲷鲸鲹鲺鲻鲼鲽鲾鲿鳀鳁鳂鳃鳄鳅鳆鳇鳈鳉鳊鳋鳌鳍鳎鳏鳐鳑鳒鳓鳔鳕鳖鳗鳘鳙鳛鳜鳝鳞鳟鳠鳡鳢鳣鸟鸠鸡鸢鸣鸤鸥鸦鸧鸨鸩鸪鸫鸬鸭鸮鸯鸰鸱鸲鸳鸴鸵鸶鸷鸸鸹鸺鸻鸼鸽鸾鸿鹀鹁鹂鹃鹄鹅鹆鹇鹈鹉鹊鹋鹌鹍鹎鹏鹐鹑鹒鹓鹔鹕鹖鹗鹘鹚鹛鹜鹝鹞鹟鹠鹡鹢鹣鹤鹥鹦鹧鹨鹩鹪鹫鹬鹭鹯鹰鹱鹲鹳鹴鹾麦麸黄黉黡黩黪黾鼋鼌鼍鼗鼹齄齐齑齿龀龁龂龃龄龅龆龇龈龉龊龋龌龙龚龛龟志制咨只里系范松没尝尝闹面准钟别闲干尽脏拼";  
  
                        $this->utf8_big5 = "麽萬與醜專業叢東絲丟兩嚴喪個爿豐臨為麗舉麼義烏樂喬習鄉書買亂爭於虧雲亙亞產畝親褻嚲億僅從侖倉儀們價眾優夥會傴傘偉傳傷倀倫傖偽佇體餘傭僉俠侶僥偵側僑儈儕儂俁儔儼倆儷儉債傾傯僂僨償儻儐儲儺兒兌兗黨蘭關興茲養獸囅內岡冊寫軍農塚馮衝決況凍淨淒涼淩減湊凜幾鳳鳧憑凱擊氹鑿芻劃劉則剛創刪別剗剄劊劌剴劑剮劍剝劇勸辦務勱動勵勁勞勢勳猛勩勻匭匱區醫華協單賣盧鹵臥衛卻巹廠廳曆厲壓厭厙廁廂厴廈廚廄廝縣參靉靆雙發變敘疊葉號歎嘰籲後嚇呂嗎唚噸聽啟吳嘸囈嘔嚦唄員咼嗆嗚詠哢嚨嚀噝吒噅鹹呱響啞噠嘵嗶噦嘩噲嚌噥喲嘜嗊嘮啢嗩唕喚呼嘖嗇囀齧囉嘽嘯噴嘍嚳囁嗬噯噓嚶囑嚕劈囂謔團園囪圍圇國圖圓聖壙場阪壞塊堅壇壢壩塢墳墜壟壟壚壘墾坰堊墊埡墶壋塏堖塒塤堝墊垵塹墮壪牆壯聲殼壺壼處備複夠頭誇夾奪奩奐奮獎奧妝婦媽嫵嫗媯姍薑婁婭嬈嬌孌娛媧嫻嫿嬰嬋嬸媼嬡嬪嬙嬤孫學孿寧寶實寵審憲宮寬賓寢對尋導壽將爾塵堯尷屍盡層屭屜屆屬屢屨嶼歲豈嶇崗峴嶴嵐島嶺嶽崠巋嶨嶧峽嶢嶠崢巒嶗崍嶮嶄嶸嶔崳嶁脊巔鞏巰幣帥師幃帳簾幟帶幀幫幬幘幗冪襆幹並廣莊慶廬廡庫應廟龐廢廎廩開異棄張彌弳彎彈強歸當錄彠彥徹徑徠禦憶懺憂愾懷態慫憮慪悵愴憐總懟懌戀懇惡慟懨愷惻惱惲悅愨懸慳憫驚懼慘懲憊愜慚憚慣湣慍憤憒願懾憖怵懣懶懍戇戔戲戧戰戩戶紮撲扡執擴捫掃揚擾撫拋摶摳掄搶護報擔擬攏揀擁攔擰撥擇掛摯攣掗撾撻挾撓擋撟掙擠揮撏撈損撿換搗據撚擄摑擲撣摻摜摣攬撳攙擱摟攪攜攝攄擺搖擯攤攖撐攆擷擼攛擻攢敵斂數齋斕鬥斬斷無舊時曠暘曇晝曨顯晉曬曉曄暈暉暫曖劄術樸機殺雜權條來楊榪傑極構樅樞棗櫪梘棖槍楓梟櫃檸檉梔柵標棧櫛櫳棟櫨櫟欄樹棲樣欒棬椏橈楨檔榿橋樺檜槳樁夢檮棶檢欞槨櫝槧欏橢樓欖櫬櫚櫸檟檻檳櫧橫檣櫻櫫櫥櫓櫞簷檁歡歟歐殲歿殤殘殞殮殫殯毆毀轂畢斃氈毿氌氣氫氬氳匯漢汙湯洶遝溝沒灃漚瀝淪滄渢溈滬濔濘淚澩瀧瀘濼瀉潑澤涇潔灑窪浹淺漿澆湞溮濁測澮濟瀏滻渾滸濃潯濜塗湧濤澇淶漣潿渦溳渙滌潤澗漲澀澱淵淥漬瀆漸澠漁瀋滲溫遊灣濕潰濺漵漊潷滾滯灩灄滿瀅濾濫灤濱灘澦濫瀠瀟瀲濰潛瀦瀾瀨瀕灝滅燈靈災燦煬爐燉煒熗點煉熾爍爛烴燭煙煩燒燁燴燙燼熱煥燜燾煆糊溜愛爺牘犛牽犧犢強狀獷獁猶狽麅獮獰獨狹獅獪猙獄猻獫獵獼玀豬貓蝟獻獺璣璵瑒瑪瑋環現瑲璽瑉玨琺瓏璫琿璡璉瑣瓊瑤璦璿瓔瓚甕甌電畫暢佘疇癤療瘧癘瘍鬁瘡瘋皰屙癰痙癢瘂癆瘓癇癡癉瘮瘞瘺癟癱癮癭癩癬癲臒皚皺皸盞鹽監蓋盜盤瞘眥矓著睜睞瞼瞞矚矯磯礬礦碭碼磚硨硯碸礪礱礫礎硜矽碩硤磽磑礄確鹼礙磧磣堿镟滾禮禕禰禎禱禍稟祿禪離禿稈種積稱穢穠穭稅穌穩穡窮竊竅窯竄窩窺竇窶豎競篤筍筆筧箋籠籩築篳篩簹箏籌簽簡籙簀篋籜籮簞簫簣簍籃籬籪籟糴類秈糶糲粵糞糧糝餱緊縶糸糾紆紅紂纖紇約級紈纊紀紉緯紜紘純紕紗綱納紝縱綸紛紙紋紡紵紖紐紓線紺絏紱練組紳細織終縐絆紼絀紹繹經紿綁絨結絝繞絰絎繪給絢絳絡絕絞統綆綃絹繡綌綏絛繼綈績緒綾緓續綺緋綽緔緄繩維綿綬繃綢綯綹綣綜綻綰綠綴緇緙緗緘緬纜緹緲緝縕繢緦綞緞緶線緱縋緩締縷編緡緣縉縛縟縝縫縗縞纏縭縊縑繽縹縵縲纓縮繆繅纈繚繕繒韁繾繰繯繳纘罌網羅罰罷羆羈羥羨翹翽翬耮耬聳恥聶聾職聹聯聵聰肅腸膚膁腎腫脹脅膽勝朧腖臚脛膠脈膾髒臍腦膿臠腳脫腡臉臘醃膕齶膩靦膃騰臏臢輿艤艦艙艫艱豔艸藝節羋薌蕪蘆蓯葦藶莧萇蒼苧蘇檾蘋莖蘢蔦塋煢繭荊薦薘莢蕘蓽蕎薈薺蕩榮葷滎犖熒蕁藎蓀蔭蕒葒葤藥蒞蓧萊蓮蒔萵薟獲蕕瑩鶯蓴蘀蘿螢營縈蕭薩蔥蕆蕢蔣蔞藍薊蘺蕷鎣驀薔蘞藺藹蘄蘊藪槁蘚虜慮虛蟲虯蟣雖蝦蠆蝕蟻螞蠶蠔蜆蠱蠣蟶蠻蟄蛺蟯螄蠐蛻蝸蠟蠅蟈蟬蠍螻蠑螿蟎蠨釁銜補襯袞襖嫋褘襪襲襏裝襠褌褳襝褲襇褸襤繈襴見觀覎規覓視覘覽覺覬覡覿覥覦覯覲覷觴觸觶讋譽謄訁計訂訃認譏訐訌討讓訕訖訓議訊記訒講諱謳詎訝訥許訛論訩訟諷設訪訣證詁訶評詛識詗詐訴診詆謅詞詘詔詖譯詒誆誄試詿詩詰詼誠誅詵話誕詬詮詭詢詣諍該詳詫諢詡譸誡誣語誚誤誥誘誨誑說誦誒請諸諏諾讀諑誹課諉諛誰諗調諂諒諄誶談誼謀諶諜謊諫諧謔謁謂諤諭諼讒諮諳諺諦謎諞諝謨讜謖謝謠謗諡謙謐謹謾謫譾謬譚譖譙讕譜譎讞譴譫讖穀豶貝貞負貟貢財責賢敗賬貨質販貪貧貶購貯貫貳賤賁貰貼貴貺貸貿費賀貽賊贄賈賄貲賃賂贓資賅贐賕賑賚賒賦賭齎贖賞賜贔賙賡賠賧賴賵贅賻賺賽賾贗讚贇贈贍贏贛赬趙趕趨趲躉躍蹌蹠躒踐躂蹺蹕躚躋踴躊蹤躓躑躡蹣躕躥躪躦軀車軋軌軒軑軔轉軛輪軟轟軲軻轤軸軹軼軤軫轢軺輕軾載輊轎輈輇輅較輒輔輛輦輩輝輥輞輬輟輜輳輻輯轀輸轡轅轄輾轆轍轔辭辯辮邊遼達遷過邁運還這進遠違連遲邇逕跡適選遜遞邐邏遺遙鄧鄺鄔郵鄒鄴鄰鬱郤郟鄶鄭鄆酈鄖鄲醞醱醬釅釃釀釋裏钜鑒鑾鏨釓釔針釘釗釙釕釷釺釧釤鈒釩釣鍆釹鍚釵鈃鈣鈈鈦鈍鈔鍾鈉鋇鋼鈑鈐鑰欽鈞鎢鉤鈧鈁鈥鈄鈕鈀鈺錢鉦鉗鈷缽鈳鉕鈽鈸鉞鑽鉬鉭鉀鈿鈾鐵鉑鈴鑠鉛鉚鈰鉉鉈鉍鈹鐸鉶銬銠鉺銪鋏鋣鐃銍鐺銅鋁銱銦鎧鍘銖銑鋌銩銛鏵銓鉿銚鉻銘錚銫鉸銥鏟銃鐋銨銀銣鑄鐒鋪鋙錸鋱鏈鏗銷鎖鋰鋥鋤鍋鋯鋨鏽銼鋝鋒鋅鋶鐦鐧銳銻鋃鋟鋦錒錆鍺錯錨錡錁錕錩錫錮鑼錘錐錦鍁錈錇錟錠鍵鋸錳錙鍥鍈鍇鏘鍶鍔鍤鍬鍾鍛鎪鍠鍰鎄鍍鎂鏤鎡鏌鎮鎛鎘鑷鐫鎳鎿鎦鎬鎊鎰鎔鏢鏜鏍鏰鏞鏡鏑鏃鏇鏐鐔钁鐐鏷鑥鐓鑭鐠鑹鏹鐙鑊鐳鐶鐲鐮鐿鑔鑣鑞鑲長門閂閃閆閈閉問闖閏闈閑閎間閔閌悶閘鬧閨聞闥閩閭闓閥閣閡閫鬮閱閬闍閾閹閶鬩閿閽閻閼闡闌闃闠闊闋闔闐闒闕闞闤隊陽陰陣階際陸隴陳陘陝隉隕險隨隱隸雋難雛讎靂霧霽黴靄靚靜靨韃鞽韉韝韋韌韍韓韙韞韜韻頁頂頃頇項順須頊頑顧頓頎頒頌頏預顱領頗頸頡頰頲頜潁熲頦頤頻頮頹頷頴穎顆題顒顎顓顏額顳顢顛顙顥纇顫顬顰顴風颺颭颮颯颶颸颼颻飀飄飆飆飛饗饜飣饑飥餳飩餼飪飫飭飯飲餞飾飽飼飿飴餌饒餉餄餎餃餏餅餑餖餓餘餒餕餜餛餡館餷饋餶餿饞饁饃餺餾饈饉饅饊饌饢馬馭馱馴馳驅馹駁驢駔駛駟駙駒騶駐駝駑駕驛駘驍罵駰驕驊駱駭駢驫驪騁驗騂駸駿騏騎騍騅騌驌驂騙騭騤騷騖驁騮騫騸驃騾驄驏驟驥驦驤髏髖髕鬢魘魎魚魛魢魷魨魯魴魺鮁鮃鯰鱸鮋鮓鮒鮊鮑鱟鮍鮐鮭鮚鮳鮪鮞鮦鰂鮜鱠鱭鮫鮮鮺鯗鱘鯁鱺鰱鰹鯉鰣鰷鯀鯊鯇鮶鯽鯒鯖鯪鯕鯫鯡鯤鯧鯝鯢鯰鯛鯨鯵鯴鯔鱝鰈鰏鱨鯷鰮鰃鰓鱷鰍鰒鰉鰁鱂鯿鰠鼇鰭鰨鰥鰩鰟鰜鰳鰾鱈鱉鰻鰵鱅鰼鱖鱔鱗鱒鱯鱤鱧鱣鳥鳩雞鳶鳴鳲鷗鴉鶬鴇鴆鴣鶇鸕鴨鴞鴦鴒鴟鴝鴛鴬鴕鷥鷙鴯鴰鵂鴴鵃鴿鸞鴻鵐鵓鸝鵑鵠鵝鵒鷳鵜鵡鵲鶓鵪鶤鵯鵬鵮鶉鶊鵷鷫鶘鶡鶚鶻鶿鶥鶩鷊鷂鶲鶹鶺鷁鶼鶴鷖鸚鷓鷚鷯鷦鷲鷸鷺鸇鷹鸌鸏鸛鸘鹺麥麩黃黌黶黷黲黽黿鼂鼉鞀鼴齇齊齏齒齔齕齗齟齡齙齠齜齦齬齪齲齷龍龔龕龜誌製谘隻裡係範鬆冇嚐嘗鬨麵準鐘彆閒乾儘臟拚";  
        }  
         
        public function c2t($str) {  
                $str_t = '';  
                $len = strlen($str);  
                $a = 0;  
                while ($a < $len){  
                        if (ord($str{$a})>=224 && ord($str{$a})<=239){  
                                if (($temp = strpos( $this->utf8_gb2312, $str{$a} . $str{$a+1} . $str{$a+2})) !== false){  
                                        $str_t .= $this->utf8_big5{$temp} . $this->utf8_big5{$temp+1} . $this->utf8_big5{$temp+2};  
                                        $a += 3;  
                                        continue;  
                                }  
                        }  
                        $str_t .= $str{$a};  
                        $a += 1;  
                }  
                return $str_t;  
        }  
         
  
        public function t2c($str) {  
                $str_t = '';  
                $len = strlen($str);  
                $a = 0;  
                while ($a < $len){  
                        if (ord($str{$a})>=224 && ord($str{$a})<=239){  
                                if (($temp = strpos( $this->utf8_big5, $str{$a} . $str{$a+1} . $str{$a+2})) !== false){  
                                        $str_t .= $this->utf8_gb2312{$temp} . $this->utf8_gb2312{$temp+1} . $this->utf8_gb2312{$temp+2};  
                                        $a += 3;  
                                        continue;  
                                }  
                        }  
                        $str_t .= $str{$a};  
                        $a += 1;  
                }  
                return $str_t;  
        }  
  
}

/**
 *	Quick Search Library
 *	@package    CATS
 *	@subpackage Library
 */
class QuickSearch
{
    private $_db;
    private $_siteID;
    protected $_userID = -1;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        //FIXME: Library code Session dependencies suck.
        $this->_userID = $_SESSION['CATS']->getUserID();
    }
    
    
    /**
     * Support function for Quick Search code. Searches all relevant fields for
     * $wildCardString.
     *
     * @param string wildcard match string
     * @return array candidates data
     */
    public function candidates($wildCardString)
    {
        $tns = new Trans;
        $wildCardStringChineseName = $wildCardString;
        $wildCardString = preg_replace('/\s+/', ' ',$wildCardString);
        $wildCardString = str_replace(' ', '%', $wildCardString);
        $wildCardString = str_replace('-', '%', $wildCardString);
        $wildCardString = str_replace('_', '%', $wildCardString);
        $wildCardString = '%' . str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);
        if( is_simplified( $wildCardStringChineseName ) )
        {
            $wildCardStringChineseNameBig5 = $tns->c2t($wildCardStringChineseName);
        }
        else 
        {
            $wildCardStringChineseNameBig5 = $wildCardStringChineseName;
        }
        $wildCardStringChineseNameBig5 = '%' . str_replace('*', '%', $wildCardStringChineseNameBig5) . '%';
        $wildCardStringChineseNameBig5 = $this->_db->makeQueryString($wildCardStringChineseNameBig5);
        if( is_traditional( $wildCardStringChineseName ) )
        {
            $wildCardStringChineseNameGB2312 = $tns->t2c($wildCardStringChineseName);
        }
        else 
        {
            $wildCardStringChineseNameGB2312 = $wildCardStringChineseName;
        }
        $wildCardStringChineseNameGB2312 = '%' . str_replace('*', '%', $wildCardStringChineseNameGB2312) . '%';
        $wildCardStringChineseNameGB2312 = $this->_db->makeQueryString($wildCardStringChineseNameGB2312);

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.middle_name AS middleName,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.chinese_name AS chineseName,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.phone_cell AS phoneWork,
                candidate.key_skills AS keySkills,
                candidate.email1 AS email1,
                candidate.email2 AS email2,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
            FROM
                candidate
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
            WHERE
            (
                CONCAT(candidate.first_name, ' ', candidate.last_name) LIKE %s
                OR CONCAT(candidate.last_name, ' ', candidate.first_name) LIKE %s
                OR CONCAT(candidate.last_name, ', ', candidate.first_name) LIKE %s
                OR CONCAT(candidate.middle_name, ' ', candidate.last_name) LIKE %s
                OR CONCAT(candidate.last_name, ' ', candidate.middle_name) LIKE %s
                OR CONCAT(candidate.first_name, ' ', candidate.middle_name, ' ', candidate.last_name) LIKE %s
                OR CONCAT(candidate.last_name, ', ', candidate.first_name, ' ', candidate.middle_name) LIKE %s
                OR candidate.email1 LIKE %s
                OR candidate.email2 LIKE %s
                OR REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(candidate.phone_home, '-', ''),
                        '.', ''),
                    ')', ''),
                '(', '') LIKE
					REPLACE(
						REPLACE(
							REPLACE(
								REPLACE(%s, '-', ''),
							'.', ''),
						')', ''),
					'(', '')
                OR REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(candidate.phone_cell, '-', ''),
                        '.', ''),
                    ')', ''),
                '(', '') LIKE
					REPLACE(
						REPLACE(
							REPLACE(
								REPLACE(%s, '-', ''),
							'.', ''),
						')', ''),
					'(', '')
                OR REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(candidate.phone_work, '-', ''),
                        '.', ''),
                    ')', ''),
                '(', '') LIKE
					REPLACE(
						REPLACE(
							REPLACE(
								REPLACE(%s, '-', ''),
							'.', ''),
						')', ''),
					'(', '')
                OR candidate.chinese_name LIKE %s
                OR candidate.chinese_name LIKE %s
            )
            AND
                candidate.site_id = %s
            AND
                candidate.is_admin_hidden = 0
            ORDER BY
                candidate.date_modified DESC,
                candidate.first_name ASC,
                candidate.last_name ASC
            LIMIT 1000",
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardStringChineseNameBig5,
            $wildCardStringChineseNameGB2312,
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }
    
    /**
     * Support function for Quick Search code. Searches all relevant fields for
     * $wildCardString.
     *
     * @param string wildcard match string
     * @return array companies data
     */
    public function companies($wildCardString)
    {
        $wildCardString = '%' . str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS name,
                company.city AS city,
                company.state AS state,
                company.phone1 AS phone1,
                company.url AS url,
                company.key_technologies AS keyTechnologies,
                company.is_hot AS isHot,
                DATE_FORMAT(
                    company.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    company.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                company
            LEFT JOIN user AS owner_user
                ON company.owner = owner_user.user_id
            WHERE
            (
                company.name LIKE %s
                OR company.phone1 LIKE %s
                OR company.phone2 LIKE %s
                OR company.url LIKE %s
            )
            AND
                company.site_id = %s
            ORDER BY
                company.name ASC
            LIMIT 100",
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }
    
    /**
     * Support function for Quick Search code. Searches all relevant fields for
     * $wildCardString.
     *
     * @param string wildcard match string
     * @return array contacts data
     */
    public function contacts($wildCardString)
    {
        // FIXME: Factor out Session dependency.
        $userCriterion = '';
        if ($_SESSION['CATS']->getAccessLevel() < ACCESS_LEVEL_DELETE)
        {
            $userCriterion = sprintf(
                "AND contact.owner = %s", $_SESSION['CATS']->getUserID()
            );
        }
        
        $wildCardString = '%' . str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        $sql = sprintf(
            "SELECT
                contact.contact_id AS contactID,
                contact.company_id AS companyID,
                contact.last_name AS lastName,
                contact.first_name AS firstName,
                contact.title AS title,
                contact.phone_work AS phoneWork,
                contact.phone_cell AS phoneCell,
                contact.phone_other AS phoneOther,
                contact.email1 AS email1,
                contact.email2 AS email2,
                contact.is_hot AS isHotContact,
                contact.left_company AS leftCompany,
                DATE_FORMAT(
                    contact.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    contact.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                company.name AS companyName,
                company.is_hot AS isHotCompany
            FROM
                contact
            LEFT JOIN company
                ON contact.company_id = company.company_id
            LEFT JOIN user AS owner_user
                ON contact.owner = owner_user.user_id
            WHERE
            (
                CONCAT(contact.first_name, ' ', contact.last_name) LIKE %s
                OR CONCAT(contact.last_name, ' ', contact.first_name) LIKE %s
                OR CONCAT(contact.last_name, ', ', contact.first_name) LIKE %s
                OR contact.phone_work LIKE %s
                OR company.name LIKE %s
                OR contact.email1 LIKE %s
                OR contact.email2 LIKE %s
                OR REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(contact.phone_work, '-', ''),
                        '.', ''),
                    ')', ''),
                '(', '') LIKE %s
                OR REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(contact.phone_cell, '-', ''),
                        '.', ''),
                    ')', ''),
                '(', '') LIKE %s
            )
            %s
            AND
                contact.site_id = %s
            AND
                company.site_id = %s
            ORDER BY
                name ASC
            LIMIT 100",
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $userCriterion,
            $this->_siteID,
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }
    
    /**
     * Support function for Quick Search code. Searches all relevant fields for
     * $wildCardString.
     *
     * @param string wildcard match string
     * @return array job orders data
     */
    public function jobOrders($wildCardString)
    {
        $wildCardString = '%' . str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS companyName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.type AS type,
                joborder.is_hot AS isHot,
                joborder.duration AS duration,
                joborder.rate_max AS maxRate,
                joborder.salary AS salary,
                joborder.status AS status,
                joborder.city AS city,
                joborder.state AS state,
                recruiter_user.first_name AS recruiterFirstName,
                recruiter_user.last_name AS recruiterLastName,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS startDate,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    joborder.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
            FROM
                joborder
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN user AS recruiter_user
                ON joborder.recruiter = recruiter_user.user_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            WHERE
            (
                company.name LIKE %s
                OR joborder.title LIKE %s
            )
            AND
                joborder.is_admin_hidden = 0
            AND
                joborder.site_id = %s
            AND
                company.site_id = %s
            ORDER BY
                name ASC
            LIMIT 500",
            $wildCardString,
            $wildCardString,
            $this->_siteID,
            $this->_siteID
        );

        if (!eval(Hooks::get('JO_SEARCH_SQL'))) return;
        if (!eval(Hooks::get('JO_SEARCH_BY_EVERYTHING'))) return;

        return $this->_db->getAllAssoc($sql);
    }

    public function jobOrdersKeySkills($wildCardString)
    {
        $wildCardString = '%' . str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        $sql = sprintf(
            "SELECT
                extra_field.field_name AS fieldName,
                extra_field.data_item_id AS jobOrderID,
                extra_field.data_item_type AS dataItemType,
                extra_field.value AS value
            FROM
                extra_field
            WHERE
            (
                extra_field.field_name LIKE 'Key Skills'
                AND extra_field.data_item_type LIKE '400'
            )
            AND
                extra_field.site_id = %s
            ORDER BY
                value ASC",
            $this->_siteID
        );

        if (!eval(Hooks::get('JO_SEARCH_SQL'))) return;
        if (!eval(Hooks::get('JO_SEARCH_BY_EVERYTHING'))) return;

        $extraRS = $this->_db->getAllAssoc($sql);

        $jobOrderIDs = array();
                
        foreach ($extraRS as $rowIndex => $row)
        {
            if (!empty($extraRS[$rowIndex]['value']))
            {
                $keySkill = $extraRS[$rowIndex]['value'];
                $keySkills = explode(',', $keySkill);
                $matchCnt = 0;
                $totalCnt = count($keySkills);
                
                foreach ($keySkills as $keySkill)
                {
                    $keySkill = trim($keySkill);
                    
                    if (stripos($wildCardString, $keySkill) === false) {
                        continue;
                    }
                    $matchCnt++;
                }
                if($totalCnt == $matchCnt)
                {
                    $jobOrderIDs[] = $extraRS[$rowIndex]['jobOrderID'];
                }
            }
        }

        if (empty($jobOrderIDs))
        {
            return '';
        }
        
        $sql_in = '(' . implode($jobOrderIDs, ', ') . ')';
        
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS companyName,
                extra_field.value AS keySkills,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.type AS type,
                joborder.is_hot AS isHot,
                joborder.duration AS duration,
                joborder.rate_max AS maxRate,
                joborder.salary AS salary,
                joborder.status AS status,
                joborder.city AS city,
                joborder.state AS state,
                recruiter_user.first_name AS recruiterFirstName,
                recruiter_user.last_name AS recruiterLastName,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS startDate,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    joborder.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
            FROM
                joborder
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN extra_field
                ON extra_field.data_item_id = joborder.joborder_id
            LEFT JOIN user AS recruiter_user
                ON joborder.recruiter = recruiter_user.user_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            WHERE
                joborder.joborder_id in %s
            AND
                extra_field.field_name LIKE 'Key Skills'
            AND
                extra_field.data_item_type LIKE '400'
            AND
                joborder.is_admin_hidden = 0
            AND
                joborder.site_id = %s
            AND
                company.site_id = %s
            ORDER BY
                name ASC
            LIMIT 500",
            $sql_in,
            $this->_siteID,
            $this->_siteID
        );

        if (!eval(Hooks::get('JO_SEARCH_SQL'))) return;
        if (!eval(Hooks::get('JO_SEARCH_BY_EVERYTHING'))) return;

        return $this->_db->getAllAssoc($sql);
    }
}


/**
 *	Saved Searches Library
 *	@package    CATS
 *	@subpackage Library
 */
class SavedSearches
{
    private $_db;
    private $_siteID;
    protected $_userID = -1;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        //FIXME: Library code Session dependencies suck.
        $this->_userID = $_SESSION['CATS']->getUserID();
    }
    
    
    /**
     * Removes a saved search entry.
     *
     * @param integer search ID
     * @return void
     */
    public function remove($searchID)
    {
        $sql = sprintf(
            "DELETE FROM
                saved_search
            WHERE
                search_id = %s
            AND
                user_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($searchID),
            $this->_userID,
            $this->_siteID
        );
        $this->_db->query($sql);
    }

    /**
     * Promotes a recent search to a saved search.
     *
     * @param integer search ID
     * @return boolean True if successful; false otherwise.
     */
    public function save($searchID)
    {
        $sql = sprintf(
            "UPDATE
                saved_search
            SET
                is_custom = 1
            WHERE
                search_id = %s
            AND
                user_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($searchID),
            $this->_userID,
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }

    //FIXME: Document me.
    public function removeRecent($dataItemType, $text)
    {
        $sql = sprintf(
            "DELETE FROM
                saved_search
            WHERE
                data_item_text = %s
            AND
                data_item_type = %s
            AND
                user_id = %s
            AND
                is_custom = 0
            AND
                site_id = %s",
            $this->_db->makeQueryString($text),
            $this->_db->makeQueryInteger($dataItemType),
            $this->_userID,
            $this->_siteID
        );
        $this->_db->query($sql);
    }

    //FIXME: Document me.
    public function add($dataItemType, $text, $url, $isCustom)
    {
        /* If this item is already in the saved search list, remove it. */
        $this->removeRecent($dataItemType, $text);

        $sql = sprintf(
            "INSERT INTO saved_search (
                site_id,
                user_id,
                data_item_type,
                data_item_text,
                url,
                is_custom,
                date_created
            )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                NOW()
            )",
            $this->_siteID,
            $this->_userID,
            $this->_db->makeQueryInteger($dataItemType),
            $this->_db->makeQueryString($text),
            $this->_db->makeQueryString($url),
            ($isCustom ? 1 : 0)
        );
        $this->_db->query($sql);

        $this->prune();
    }

    //FIXME: Document me.
    public function get($dataItemType)
    {
        $sql = sprintf(
            "SELECT
                search_id AS searchID,
                data_item_text AS dataItemText,
                url AS URL,
                is_custom AS isCustom
            FROM
                saved_search
            WHERE
                site_id = %s
            AND
                user_id = %s
            AND
                data_item_type = %s
            ORDER BY
                search_id DESC",
            $this->_siteID,
            $this->_userID,
            $this->_db->makeQueryInteger($dataItemType)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Removes old saved search entries for a user.
     *
     * @return void
     */
    private function prune()
    {
        $sql = sprintf(
            "SELECT
                COUNT(*) AS count
            FROM
                saved_search
            WHERE
                site_id = %s
            AND
                user_id = %s
            AND
                is_custom = 0",
            $this->_siteID,
            $this->_userID
        );
        $rs = $this->_db->getAssoc($sql);

        $count = $rs['count'];

        // FIXME: Remove multiple entries at onceif we're more than one over?
        while ($count > RECENT_SEARCH_MAX_ITEMS)
        {
            /* Remove the least recent entry. */
            $sql = sprintf(
                "SELECT
                    search_id AS searchID
                FROM
                    saved_search
                WHERE
                    site_id = %s
                AND
                    user_id = %s
                AND
                    is_custom = 0
                ORDER BY
                    search_id
                ASC LIMIT 1",
                $this->_siteID,
                $this->_userID
            );
            $rs = $this->_db->getAssoc($sql);

            $sql = sprintf(
                "DELETE FROM
                    saved_search
                WHERE
                    search_id = %s",
                $rs['searchID']
            );
            $this->_db->query($sql);

            --$count;
        }
    }
}


/**
 *	Search by Resume Pager
 *	@package    CATS
 *	@subpackage Library
 */
class SearchByResumePager extends Pager
{
    private $_siteID;
    private $_db;
    private $_WHERE;


    public function __construct($rowsPerPage, $currentPage, $siteID,
        $wildCardString, $sortBy, $sortDirection)
    {
        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;

        $this->_sortByFields = array(
            'firstName',
            'lastName',
            'city',
            'state',
            'dateModifiedSort',
            'dateCreatedSort',
            'ownerSort'
        );

        if (ENABLE_SPHINX)
        {
            /* Sphinx API likes to throw PHP errors *AND* use it's own error
             * handling.
             */
            assert_options(ASSERT_WARNING, 0);

            $sphinx = new SphinxClient();
            $sphinx->SetServer(SPHINX_HOST, SPHINX_PORT);
            $sphinx->SetWeights(array(0, 100, 0, 0, 50));
            $sphinx->SetMatchMode(SPH_MATCH_EXTENDED);
            $sphinx->SetLimits(0, 1000);
            $sphinx->SetSortMode(SPH_SORT_TIME_SEGMENTS, 'date_added');

            // FIXME: This can be sped up a bit by actually grouping ranges of
            //        site IDs into their own index's. Maybe every 500 or so at
            //        least on the Hosted system.
            $sphinx->SetFilter('site_id', array($this->_siteID));

            /* Create the Sphinx query string. */
            $wildCardString = DatabaseSearch::humanToSphinxBoolean($wildCardString);
            
            /* Execute the Sphinx query. Sphinx can ask us to retry if its
             * maxed out. Retry up to 5 times.
             */
            $tries = 0;
            do
            {
                /* Wait for one second if this isn't out first attempt. */
                if (++$tries > 1)
                {
                    sleep(1);
                }
                
                $results = $sphinx->Query($wildCardString, SPHINX_INDEX);
                $errorMessage = $sphinx->GetLastError();
            }
            while (
                $results === false &&
                strpos($errorMessage, 'server maxed out, retry') !== false &&
                $tries <= 5
            );

            /* Throw a fatal error if Sphinx errors occurred. */
            if ($results === false)
            {   
                $this->fatal('Sphinx Error: ' . ucfirst($errorMessage) . '.');
            }

            /* Throw a fatal error (for now) if Sphinx warnings occurred. */
            $lastWarning = $sphinx->GetLastWarning();
            if (!empty($lastWarning))
            {
                // FIXME: Just display a warning, and notify dev team.
                $this->fatal('Sphinx Warning: ' . ucfirst($lastWarning) . '.');
            }

            /* Show warnings for assert()s again. */
            assert_options(ASSERT_WARNING, 1);

            if (empty($results['matches']))
            {
                $this->_WHERE = '0';
            }
            else
            {
                $attachmentIDs = implode(',', array_keys($results['matches']));
                $this->_WHERE = 'attachment.attachment_id IN(' . $attachmentIDs . ')';
            }

        }
        else
        {
            $this->_WHERE = DatabaseSearch::makeBooleanSQLWhere(
                DatabaseSearch::fulltextEncode($wildCardString),
                $this->_db,
                'attachment.text'
            );
        }

        /* How many companies do we have? */
        $sql = sprintf(
            "SELECT
                COUNT(*) AS count
            FROM
                attachment
            LEFT JOIN candidate
                ON attachment.data_item_id = candidate.candidate_id
                AND attachment.data_item_type = %s
                AND attachment.site_id = candidate.site_id
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
            WHERE
                resume = 1
            AND
                %s
            AND
                (ISNULL(candidate.is_admin_hidden) OR (candidate.is_admin_hidden = 0))
            AND
                (ISNULL(candidate.is_active) OR (candidate.is_active = 1))
            AND
                attachment.site_id = %s",
            DATA_ITEM_CANDIDATE,
            $this->_WHERE,
            $this->_siteID
        );
        $rs = $this->_db->getAssoc($sql);

        /* Pass "Search By Resume"-specific parameters to Pager constructor. */
        parent::__construct($rs['count'], $rowsPerPage, $currentPage);
    }


    //FIXME: Document me.
    public function getPage()
    {
        $sql = sprintf(
            "SELECT
                attachment.attachment_id AS attachmentID,
                attachment.data_item_id AS candidateID,
                attachment.title AS title,
                attachment.text AS text,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.city AS city,
                candidate.state AS state,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                candidate.date_created AS dateCreatedSort,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                candidate.date_modified AS dateModifiedSort,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                CONCAT(owner_user.last_name, owner_user.first_name) AS ownerSort
            FROM
                attachment
            LEFT JOIN candidate
                ON attachment.data_item_id = candidate.candidate_id
                AND attachment.site_id = candidate.site_id
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
            WHERE
                resume = 1
            AND
                %s
            AND
                (attachment.data_item_type = %s OR attachment.data_item_type = %s)
            AND
                attachment.site_id = %s
            AND
                (ISNULL(candidate.is_admin_hidden) OR (candidate.is_admin_hidden = 0))
            AND
                (ISNULL(candidate.is_active) OR (candidate.is_active = 1))
            ORDER BY
                %s %s
            LIMIT %s, %s",

            $this->_WHERE,
            DATA_ITEM_CANDIDATE,
            DATA_ITEM_BULKRESUME,
            $this->_siteID,
            $this->_sortBy,
            $this->_sortDirection,
            $this->_thisPageStartRow,
            $this->_rowsPerPage
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Print a fatal error and die.
     *
     * @param string error message
     * @return void
     */
    protected function fatal($error)
    {
        $template = new Template();
        $template->assign('errorMessage', $error);
        $template->display('./Error.tpl');
        die();
    }
}


/**
 *	Search Results Pager
 *	@package    CATS
 *	@subpackage Library
 */
class SearchPager extends Pager
{
    private $_siteID;
    private $_db;
    private $_rs;


    public function __construct($rowsPerPage, $currentPage, $siteID)
    {
        $this->_sortByFields = array(
            'firstName',
            'lastName',
            'city',
            'state',
            'dateModified',
            'dateCreated',
            'owner',
            'phone1',
            'companyName',
            'title',
            'owner_user',
            'owner_user.last_name',
            'type',
            'status',
            'startDate',
            'recruiterLastName',
            'dateCreatedSort',
            'dateModifiedSort',
            'ownerSort'
        );

        /* Pass "Search By Resume"-specific parameters to Pager constructor. */
        parent::__construct(count($this->_rs), $rowsPerPage, $currentPage);
    }
}

?>
