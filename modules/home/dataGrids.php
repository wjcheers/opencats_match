<?php
/*
 * CATS
 * Home Datagrid
 *
 * CATS Version: 0.8.0 (Jhelum)
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/. Software distributed under the License is
 * distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * rights and limitations under the License.
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
 * $Id: dataGrids.php 3583 2007-11-12 23:04:42Z brian $
 */

include_once('./lib/Hooks.php');
include_once('./lib/InfoString.php');
include_once('./lib/Pipelines.php');

class ImportantPipelineDashboard extends DataGrid
{
    protected $_siteID;


    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($siteID, $parameters)
    {
        /* Pager configuration. */
        $this->_tableWidth = 1215;
        $this->_defaultAlphabeticalSortBy = 'lastName';
        $this->ajaxMode = true;
        $this->showExportColumn = false;
        $this->showExportCheckboxes = false;
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;
        $this->dateCriterion = '';
        $this->globalStyle = 'font-size:11px;';
        $this->ignoreSavedColumnLayouts = true;

        $this->defaultSortBy = 'dateModifiedSort';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => 'First Name', 'width' => 85),
            array('name' => 'Last Name', 'width' => 75),
            array('name' => 'Status', 'width' => 75),
            array('name' => 'Position', 'width' => 275),
            array('name' => 'Company', 'width' => 210),
            array('name' => 'Modified', 'width' => 80),
            array('name' => 'Owner', 'width' => 65),
        );


        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_assignedCriterion = "";
        $this->_candidateIDColumn = 'company.company_id';

        $this->_classColumns = array(

            'First Name' =>     array('pagerRender'    => '$ret = \'<img src="images/mru/candidate.gif" height="12" alt="" />\'; if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return $ret.\'&nbsp;<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';',
                                     'sortableColumn'  => 'firstName',
                                     'pagerWidth'      => 85,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'firstName'),

            'Last Name' =>      array('pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'"  style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'"> \'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';',
                                     'sortableColumn'  => 'lastName',
                                     'pagerWidth'      => 75,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'lastName'),

            'Status'    =>      array('pagerRender'    => 'return $rsData[\'status\'];',
                                     'sortableColumn'  => 'statusSort',
                                     'pagerWidth'      => 75,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'status'),

            'Position'    =>    array('pagerRender'    => 'if ($rsData[\'jobOrderIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=joborders&amp;a=show&amp;jobOrderID=\'.$rsData[\'joborderID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'jobOrderTitle\']).\'</a>\';',
                                     'sortableColumn'  => 'jobOrderTitle',
                                     'pagerWidth'      => 220,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'jobOrderTitle'),

            'Company'    =>    array('pagerRender'    => 'if ($rsData[\'companyIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\'.$rsData[\'companyID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'companyName\']).\'</a>\';',
                                     'sortableColumn'  => 'companyName',
                                     'pagerWidth'      => 180,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'companyName'),

            'Modified' =>      array('pagerRender'     => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'  => 'dateModifiedSort',
                                     'pagerWidth'      => 70,
                                     'pagerOptional'   => true,
                                     'filterHaving'    => 'DATE_FORMAT(candidate_joborder.date_modified, \'%m-%d-%y (%%h:%%i %%p)\')'),

            'Owner' =>         array('pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'userFirstName\'], $rsData[\'userLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'userFirstName\'] . " " .$rsData[\'userLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),
         );

        parent::__construct("home:ImportantPipelineDashboard", $parameters);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                candidate_joborder.candidate_joborder_id as candidateJoborderID,
                candidate.first_name as firstName,
                candidate.last_name as lastName,
                candidate.candidate_id as candidateID,
                candidate.site_id as siteID,
                candidate.is_hot as isHot,
                company.name as companyName,
                company.company_id as companyID,
                company.is_hot as companyIsHot,
                joborder.title as jobOrderTitle,
                joborder.is_hot as jobOrderIsHot,
                joborder.joborder_id as joborderID,
                user.first_name as userFirstName,
                user.last_name as userLastName,
                CONCAT(user.last_name, user.first_name) AS ownerSort,
                DATE_FORMAT(candidate_joborder.date_modified, '%%m-%%d-%%y ') as dateModified,
                candidate_joborder.date_modified as dateModifiedSort,
                IF(candidate_joborder.status = %s, 1,
                  IF(candidate_joborder.status = %s, 2, 3)) as statusSort,
                candidate_joborder_status.short_description as status
            FROM
                candidate_joborder
            LEFT JOIN candidate ON
                candidate.candidate_id = candidate_joborder.candidate_id
            LEFT JOIN joborder ON
                joborder.joborder_id = candidate_joborder.joborder_id
            LEFT JOIN company ON
                joborder.company_id = company.company_id
            LEFT JOIN candidate_joborder_status ON
                candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            LEFT JOIN user ON
                candidate.owner = user.user_id
            WHERE
                candidate_joborder.site_id = %s
            AND
                (   candidate_joborder.status = %s
                OR
                    candidate_joborder.status = %s
                OR
                    candidate_joborder.status = %s
                )
            %s
            %s
            %s
            %s",
            $distinct,
            PIPELINE_STATUS_SUBMITTED,
            PIPELINE_STATUS_INTERVIEWING,
            $this->_siteID,
            PIPELINE_STATUS_SUBMITTED,
            PIPELINE_STATUS_INTERVIEWING,
            PIPELINE_STATUS_OFFERED,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }
}

class QualifyingPipelineDashboard extends DataGrid
{
    protected $_siteID;


    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($siteID, $parameters)
    {
        /* Pager configuration. */
        $this->_tableWidth = 1215;
        $this->_defaultAlphabeticalSortBy = 'lastName';
        $this->ajaxMode = true;
        $this->showExportColumn = false;
        $this->showExportCheckboxes = false;
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;
        $this->dateCriterion = '';
        $this->globalStyle = 'font-size:11px;';
        $this->ignoreSavedColumnLayouts = true;

        $this->defaultSortBy = 'dateModifiedSort';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => 'First Name', 'width' => 85),
            array('name' => 'Last Name', 'width' => 75),
            array('name' => 'Status', 'width' => 75),
            array('name' => 'Position', 'width' => 275),
            array('name' => 'Company', 'width' => 210),
            array('name' => 'Modified', 'width' => 80),
            array('name' => 'Owner', 'width' => 65),
        );


        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_assignedCriterion = "";
        $this->_candidateIDColumn = 'company.company_id';

        $this->_classColumns = array(

            'First Name' =>     array('pagerRender'    => '$ret = \'<img src="images/mru/candidate.gif" height="12" alt="" />\'; if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return $ret.\'&nbsp;<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';',
                                     'sortableColumn'  => 'firstName',
                                     'pagerWidth'      => 85,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'firstName'),

            'Last Name' =>      array('pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'"  style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'"> \'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';',
                                     'sortableColumn'  => 'lastName',
                                     'pagerWidth'      => 75,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'lastName'),

            'Status'    =>      array('pagerRender'    => 'return $rsData[\'status\'];',
                                     'sortableColumn'  => 'statusSort',
                                     'pagerWidth'      => 75,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'status'),

            'Position'    =>    array('pagerRender'    => 'if ($rsData[\'jobOrderIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; if ($rsData[\'joborderStatus\'] != \'Active\') $joborderStatus =  \' (\' . $rsData[\'joborderStatus\'] . \')\'; else $joborderStatus = \'\'; return \'<a href="'.CATSUtility::getIndexName().'?m=joborders&amp;a=show&amp;jobOrderID=\'.$rsData[\'joborderID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'jobOrderTitle\']).$joborderStatus.\'</a>\';',
                                     'sortableColumn'  => 'jobOrderTitle',
                                     'pagerWidth'      => 220,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'jobOrderTitle'),

            'Company'    =>    array('pagerRender'    => 'if ($rsData[\'companyIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\'.$rsData[\'companyID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'companyName\']).\'</a>\';',
                                     'sortableColumn'  => 'companyName',
                                     'pagerWidth'      => 180,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'companyName'),

            'Modified' =>      array('pagerRender'     => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'  => 'dateModifiedSort',
                                     'pagerWidth'      => 70,
                                     'pagerOptional'   => true,
                                     'filterHaving'    => 'DATE_FORMAT(candidate_joborder.date_modified, \'%m-%d-%y (%%h:%%i %%p)\')'),

            'Owner' =>         array('pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'userFirstName\'], $rsData[\'userLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'userFirstName\'] . " " .$rsData[\'userLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),
         );

        parent::__construct("home:QualifyingPipelineDashboard", $parameters);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                candidate_joborder.candidate_joborder_id as candidateJoborderID,
                candidate.first_name as firstName,
                candidate.last_name as lastName,
                candidate.candidate_id as candidateID,
                candidate.site_id as siteID,
                candidate.is_hot as isHot,
                company.name as companyName,
                company.company_id as companyID,
                company.is_hot as companyIsHot,
                joborder.title as jobOrderTitle,
                joborder.is_hot as jobOrderIsHot,
                joborder.joborder_id as joborderID,
                joborder.status as joborderStatus,
                user.first_name as userFirstName,
                user.last_name as userLastName,
                CONCAT(user.last_name, user.first_name) AS ownerSort,
                DATE_FORMAT(candidate_joborder.date_modified, '%%m-%%d-%%y ') as dateModified,
                candidate_joborder.date_modified as dateModifiedSort,
                IF(candidate_joborder.status = %s, 1, 2) as statusSort,
                candidate_joborder_status.short_description as status
            FROM
                candidate_joborder
            LEFT JOIN candidate ON
                candidate.candidate_id = candidate_joborder.candidate_id
            LEFT JOIN joborder ON
                joborder.joborder_id = candidate_joborder.joborder_id
            LEFT JOIN company ON
                joborder.company_id = company.company_id
            LEFT JOIN candidate_joborder_status ON
                candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            LEFT JOIN user ON
                candidate.owner = user.user_id
            WHERE
                candidate_joborder.site_id = %s
            AND
                candidate_joborder.status = %s
            %s
            %s
            %s
            %s",
            $distinct,
            PIPELINE_STATUS_QUALIFYING,
            $this->_siteID,
            PIPELINE_STATUS_QUALIFYING,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }
}


class VerifiedPipelineDashboard extends DataGrid
{
    protected $_siteID;


    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($siteID, $parameters)
    {
        /* Pager configuration. */
        $this->_tableWidth = 1215;
        $this->_defaultAlphabeticalSortBy = 'lastName';
        $this->ajaxMode = true;
        $this->showExportColumn = false;
        $this->showExportCheckboxes = false;
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;
        $this->dateCriterion = '';
        $this->globalStyle = 'font-size:11px;';
        $this->ignoreSavedColumnLayouts = true;

        $this->defaultSortBy = 'dateModifiedSort';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => 'First Name', 'width' => 85),
            array('name' => 'Last Name', 'width' => 75),
            array('name' => 'Status', 'width' => 75),
            array('name' => 'Position', 'width' => 275),
            array('name' => 'Company', 'width' => 210),
            array('name' => 'Modified', 'width' => 80),
            array('name' => 'Owner', 'width' => 65),
        );


        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_assignedCriterion = "";
        $this->_candidateIDColumn = 'company.company_id';

        $this->_classColumns = array(

            'First Name' =>     array('pagerRender'    => '$ret = \'<img src="images/mru/candidate.gif" height="12" alt="" />\'; if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return $ret.\'&nbsp;<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';',
                                     'sortableColumn'  => 'firstName',
                                     'pagerWidth'      => 85,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'firstName'),

            'Last Name' =>      array('pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'"  style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'"> \'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';',
                                     'sortableColumn'  => 'lastName',
                                     'pagerWidth'      => 75,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'lastName'),

            'Status'    =>      array('pagerRender'    => 'return $rsData[\'status\'];',
                                     'sortableColumn'  => 'statusSort',
                                     'pagerWidth'      => 75,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'status'),

            'Position'    =>    array('pagerRender'    => 'if ($rsData[\'jobOrderIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; if ($rsData[\'joborderStatus\'] != \'Active\') $joborderStatus =  \' (\' . $rsData[\'joborderStatus\'] . \')\'; else $joborderStatus = \'\'; return \'<a href="'.CATSUtility::getIndexName().'?m=joborders&amp;a=show&amp;jobOrderID=\'.$rsData[\'joborderID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'jobOrderTitle\']).$joborderStatus.\'</a>\';',
                                     'sortableColumn'  => 'jobOrderTitle',
                                     'pagerWidth'      => 220,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'jobOrderTitle'),

            'Company'    =>    array('pagerRender'    => 'if ($rsData[\'companyIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\'.$rsData[\'companyID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'companyName\']).\'</a>\';',
                                     'sortableColumn'  => 'companyName',
                                     'pagerWidth'      => 180,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'companyName'),

            'Modified' =>      array('pagerRender'     => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'  => 'dateModifiedSort',
                                     'pagerWidth'      => 70,
                                     'pagerOptional'   => true,
                                     'filterHaving'    => 'DATE_FORMAT(candidate_joborder.date_modified, \'%m-%d-%y (%%h:%%i %%p)\')'),

            'Owner' =>         array('pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'userFirstName\'], $rsData[\'userLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'userFirstName\'] . " " .$rsData[\'userLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),
         );

        parent::__construct("home:VerifiedPipelineDashboard", $parameters);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                candidate_joborder.candidate_joborder_id as candidateJoborderID,
                candidate.first_name as firstName,
                candidate.last_name as lastName,
                candidate.candidate_id as candidateID,
                candidate.site_id as siteID,
                candidate.is_hot as isHot,
                company.name as companyName,
                company.company_id as companyID,
                company.is_hot as companyIsHot,
                joborder.title as jobOrderTitle,
                joborder.is_hot as jobOrderIsHot,
                joborder.joborder_id as joborderID,
                joborder.status as joborderStatus,
                user.first_name as userFirstName,
                user.last_name as userLastName,
                CONCAT(user.last_name, user.first_name) AS ownerSort,
                DATE_FORMAT(candidate_joborder.date_modified, '%%m-%%d-%%y ') as dateModified,
                candidate_joborder.date_modified as dateModifiedSort,
                IF(candidate_joborder.status = %s, 1, 2) as statusSort,
                candidate_joborder_status.short_description as status
            FROM
                candidate_joborder
            LEFT JOIN candidate ON
                candidate.candidate_id = candidate_joborder.candidate_id
            LEFT JOIN joborder ON
                joborder.joborder_id = candidate_joborder.joborder_id
            LEFT JOIN company ON
                joborder.company_id = company.company_id
            LEFT JOIN candidate_joborder_status ON
                candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            LEFT JOIN user ON
                candidate.owner = user.user_id
            WHERE
                candidate_joborder.site_id = %s
            AND
                candidate_joborder.status = %s
            %s
            %s
            %s
            %s",
            $distinct,
            PIPELINE_STATUS_VERIFIED,
            $this->_siteID,
            PIPELINE_STATUS_VERIFIED,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }
}


class AwaitingPipelineDashboard extends DataGrid
{
    protected $_siteID;


    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($siteID, $parameters)
    {
        /* Pager configuration. */
        $this->_tableWidth = 1215;
        $this->_defaultAlphabeticalSortBy = 'lastName';
        $this->ajaxMode = true;
        $this->showExportColumn = false;
        $this->showExportCheckboxes = false;
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;
        $this->dateCriterion = '';
        $this->globalStyle = 'font-size:11px;';
        $this->ignoreSavedColumnLayouts = true;

        $this->defaultSortBy = 'dateModifiedSort';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => 'First Name', 'width' => 85),
            array('name' => 'Last Name', 'width' => 75),
            array('name' => 'Status', 'width' => 75),
            array('name' => 'Position', 'width' => 275),
            array('name' => 'Company', 'width' => 210),
            array('name' => 'Modified', 'width' => 80),
            array('name' => 'Owner', 'width' => 65),
        );


        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_assignedCriterion = "";
        $this->_candidateIDColumn = 'company.company_id';

        $this->_classColumns = array(

            'First Name' =>     array('pagerRender'    => '$ret = \'<img src="images/mru/candidate.gif" height="12" alt="" />\'; if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return $ret.\'&nbsp;<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';',
                                     'sortableColumn'  => 'firstName',
                                     'pagerWidth'      => 85,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'firstName'),

            'Last Name' =>      array('pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'"  style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'"> \'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';',
                                     'sortableColumn'  => 'lastName',
                                     'pagerWidth'      => 75,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'lastName'),

            'Status'    =>      array('pagerRender'    => 'return $rsData[\'status\'];',
                                     'sortableColumn'  => 'statusSort',
                                     'pagerWidth'      => 75,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'status'),

            'Position'    =>    array('pagerRender'    => 'if ($rsData[\'jobOrderIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; if ($rsData[\'joborderStatus\'] != \'Active\') $joborderStatus =  \' (\' . $rsData[\'joborderStatus\'] . \')\'; else $joborderStatus = \'\'; return \'<a href="'.CATSUtility::getIndexName().'?m=joborders&amp;a=show&amp;jobOrderID=\'.$rsData[\'joborderID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'jobOrderTitle\']).$joborderStatus.\'</a>\';',
                                     'sortableColumn'  => 'jobOrderTitle',
                                     'pagerWidth'      => 220,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'jobOrderTitle'),

            'Company'    =>    array('pagerRender'    => 'if ($rsData[\'companyIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\'.$rsData[\'companyID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'companyName\']).\'</a>\';',
                                     'sortableColumn'  => 'companyName',
                                     'pagerWidth'      => 180,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'companyName'),

            'Modified' =>      array('pagerRender'     => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'  => 'dateModifiedSort',
                                     'pagerWidth'      => 70,
                                     'pagerOptional'   => true,
                                     'filterHaving'    => 'DATE_FORMAT(candidate_joborder.date_modified, \'%m-%d-%y (%%h:%%i %%p)\')'),

            'Owner' =>         array('pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'userFirstName\'], $rsData[\'userLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'userFirstName\'] . " " .$rsData[\'userLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),
         );

        parent::__construct("home:AwaitingPipelineDashboard", $parameters);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                candidate_joborder.candidate_joborder_id as candidateJoborderID,
                candidate.first_name as firstName,
                candidate.last_name as lastName,
                candidate.candidate_id as candidateID,
                candidate.site_id as siteID,
                candidate.is_hot as isHot,
                company.name as companyName,
                company.company_id as companyID,
                company.is_hot as companyIsHot,
                joborder.title as jobOrderTitle,
                joborder.is_hot as jobOrderIsHot,
                joborder.joborder_id as joborderID,
                joborder.status as joborderStatus,
                user.first_name as userFirstName,
                user.last_name as userLastName,
                CONCAT(user.last_name, user.first_name) AS ownerSort,
                DATE_FORMAT(candidate_joborder.date_modified, '%%m-%%d-%%y ') as dateModified,
                candidate_joborder.date_modified as dateModifiedSort,
                IF(candidate_joborder.status = %s, 1, 2) as statusSort,
                candidate_joborder_status.short_description as status
            FROM
                candidate_joborder
            LEFT JOIN candidate ON
                candidate.candidate_id = candidate_joborder.candidate_id
            LEFT JOIN joborder ON
                joborder.joborder_id = candidate_joborder.joborder_id
            LEFT JOIN company ON
                joborder.company_id = company.company_id
            LEFT JOIN candidate_joborder_status ON
                candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            LEFT JOIN user ON
                candidate.owner = user.user_id
            WHERE
                candidate_joborder.site_id = %s
            AND
                candidate_joborder.status = %s
            %s
            %s
            %s
            %s",
            $distinct,
            PIPELINE_STATUS_AWAITING,
            $this->_siteID,
            PIPELINE_STATUS_AWAITING,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }
}


class UploadedPipelineDashboard extends DataGrid
{
    protected $_siteID;


    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($siteID, $parameters)
    {
        /* Pager configuration. */
        $this->_tableWidth = 1215;
        $this->_defaultAlphabeticalSortBy = 'lastName';
        $this->ajaxMode = true;
        $this->showExportColumn = false;
        $this->showExportCheckboxes = false;
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;
        $this->dateCriterion = '';
        $this->globalStyle = 'font-size:11px;';
        $this->ignoreSavedColumnLayouts = true;

        $this->defaultSortBy = 'dateModifiedSort';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => 'First Name', 'width' => 85),
            array('name' => 'Last Name', 'width' => 75),
            array('name' => 'Status', 'width' => 75),
            array('name' => 'Position', 'width' => 275),
            array('name' => 'Company', 'width' => 210),
            array('name' => 'Modified', 'width' => 80),
            array('name' => 'Owner', 'width' => 65),
        );


        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_assignedCriterion = "";
        $this->_candidateIDColumn = 'company.company_id';

        $this->_classColumns = array(

            'First Name' =>     array('pagerRender'    => '$ret = \'<img src="images/mru/candidate.gif" height="12" alt="" />\'; if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return $ret.\'&nbsp;<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';',
                                     'sortableColumn'  => 'firstName',
                                     'pagerWidth'      => 85,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'firstName'),

            'Last Name' =>      array('pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'"  style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'"> \'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';',
                                     'sortableColumn'  => 'lastName',
                                     'pagerWidth'      => 75,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'lastName'),

            'Status'    =>      array('pagerRender'    => 'return $rsData[\'status\'];',
                                     'sortableColumn'  => 'statusSort',
                                     'pagerWidth'      => 75,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'status'),

            'Position'    =>    array('pagerRender'    => 'if ($rsData[\'jobOrderIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; if ($rsData[\'joborderStatus\'] != \'Active\') $joborderStatus =  \' (\' . $rsData[\'joborderStatus\'] . \')\'; else $joborderStatus = \'\'; return \'<a href="'.CATSUtility::getIndexName().'?m=joborders&amp;a=show&amp;jobOrderID=\'.$rsData[\'joborderID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'jobOrderTitle\']).$joborderStatus.\'</a>\';',
                                     'sortableColumn'  => 'jobOrderTitle',
                                     'pagerWidth'      => 220,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'jobOrderTitle'),

            'Company'    =>    array('pagerRender'    => 'if ($rsData[\'companyIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\'.$rsData[\'companyID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'companyName\']).\'</a>\';',
                                     'sortableColumn'  => 'companyName',
                                     'pagerWidth'      => 180,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'companyName'),

            'Modified' =>      array('pagerRender'     => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'  => 'dateModifiedSort',
                                     'pagerWidth'      => 70,
                                     'pagerOptional'   => true,
                                     'filterHaving'    => 'DATE_FORMAT(candidate_joborder.date_modified, \'%m-%d-%y (%%h:%%i %%p)\')'),

            'Owner' =>         array('pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'userFirstName\'], $rsData[\'userLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'userFirstName\'] . " " .$rsData[\'userLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),
         );

        parent::__construct("home:UploadedPipelineDashboard", $parameters);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                candidate_joborder.candidate_joborder_id as candidateJoborderID,
                candidate.first_name as firstName,
                candidate.last_name as lastName,
                candidate.candidate_id as candidateID,
                candidate.site_id as siteID,
                candidate.is_hot as isHot,
                company.name as companyName,
                company.company_id as companyID,
                company.is_hot as companyIsHot,
                joborder.title as jobOrderTitle,
                joborder.is_hot as jobOrderIsHot,
                joborder.joborder_id as joborderID,
                joborder.status as joborderStatus,
                user.first_name as userFirstName,
                user.last_name as userLastName,
                CONCAT(user.last_name, user.first_name) AS ownerSort,
                DATE_FORMAT(candidate_joborder.date_modified, '%%m-%%d-%%y ') as dateModified,
                candidate_joborder.date_modified as dateModifiedSort,
                IF(candidate_joborder.status = %s, 1, 2) as statusSort,
                candidate_joborder_status.short_description as status
            FROM
                candidate_joborder
            LEFT JOIN candidate ON
                candidate.candidate_id = candidate_joborder.candidate_id
            LEFT JOIN joborder ON
                joborder.joborder_id = candidate_joborder.joborder_id
            LEFT JOIN company ON
                joborder.company_id = company.company_id
            LEFT JOIN candidate_joborder_status ON
                candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            LEFT JOIN user ON
                candidate.owner = user.user_id
            WHERE
                candidate_joborder.site_id = %s
            AND
                candidate_joborder.status = %s
            %s
            %s
            %s
            %s",
            $distinct,
            PIPELINE_STATUS_UPLOADED,
            $this->_siteID,
            PIPELINE_STATUS_UPLOADED,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }
}

class EverVerifiedPipelineDashboard extends DataGrid
{
    protected $_siteID;


    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($siteID, $parameters)
    {
        /* Pager configuration. */
        $this->_tableWidth = 1215;
        $this->_defaultAlphabeticalSortBy = 'lastName';
        $this->ajaxMode = true;
        $this->showExportColumn = false;
        $this->showExportCheckboxes = false;
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;
        $this->dateCriterion = '';
        $this->globalStyle = 'font-size:11px;';
        $this->ignoreSavedColumnLayouts = true;

        $this->defaultSortBy = 'dateSort';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => 'First Name', 'width' => 85),
            array('name' => 'Last Name', 'width' => 75),
            array('name' => 'Status', 'width' => 75),
            array('name' => 'Position', 'width' => 275),
            array('name' => 'Company', 'width' => 210),
            array('name' => 'Date', 'width' => 80),
            array('name' => 'Owner', 'width' => 65),
        );


        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_assignedCriterion = "";
        $this->_candidateIDColumn = 'company.company_id';

        $this->_classColumns = array(

            'First Name' =>     array('pagerRender'    => '$ret = \'<img src="images/mru/candidate.gif" height="12" alt="" />\'; if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return $ret.\'&nbsp;<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';',
                                     'sortableColumn'  => 'firstName',
                                     'pagerWidth'      => 85,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'firstName'),

            'Last Name' =>      array('pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'"  style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'"> \'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';',
                                     'sortableColumn'  => 'lastName',
                                     'pagerWidth'      => 75,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'lastName'),

            'Status'    =>      array('pagerRender'    => 'return $rsData[\'status\'];',
                                     'sortableColumn'  => 'statusSort',
                                     'pagerWidth'      => 75,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'status'),

            'Position'    =>    array('pagerRender'    => 'if ($rsData[\'jobOrderIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; if ($rsData[\'joborderStatus\'] != \'Active\') $joborderStatus =  \' (\' . $rsData[\'joborderStatus\'] . \')\'; else $joborderStatus = \'\'; return \'<a href="'.CATSUtility::getIndexName().'?m=joborders&amp;a=show&amp;jobOrderID=\'.$rsData[\'joborderID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'jobOrderTitle\']).$joborderStatus.\'</a>\';',
                                     'sortableColumn'  => 'jobOrderTitle',
                                     'pagerWidth'      => 220,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'jobOrderTitle'),

            'Company'    =>    array('pagerRender'    => 'if ($rsData[\'companyIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\'.$rsData[\'companyID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'companyName\']).\'</a>\';',
                                     'sortableColumn'  => 'companyName',
                                     'pagerWidth'      => 180,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'companyName'),

            'Date' =>      array('pagerRender'     => 'return $rsData[\'date\'];',
                                     'sortableColumn'  => 'dateSort',
                                     'pagerWidth'      => 70,
                                     'pagerOptional'   => true,
                                     'filterHaving'    => 'DATE_FORMAT(candidate_joborder_status_history.date, \'%m-%d-%y (%%h:%%i %%p)\')'),

            'Owner' =>         array('pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'userFirstName\'], $rsData[\'userLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'userFirstName\'] . " " .$rsData[\'userLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),
         );

        parent::__construct("home:EverVerifiedPipelineDashboard", $parameters);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                candidate_joborder.candidate_joborder_id as candidateJoborderID,
                candidate.first_name as firstName,
                candidate.last_name as lastName,
                candidate.candidate_id as candidateID,
                candidate.site_id as siteID,
                candidate.is_hot as isHot,
                company.name as companyName,
                company.company_id as companyID,
                company.is_hot as companyIsHot,
                joborder.title as jobOrderTitle,
                joborder.is_hot as jobOrderIsHot,
                joborder.joborder_id as joborderID,
                joborder.status as joborderStatus,
                user.first_name as userFirstName,
                user.last_name as userLastName,
                CONCAT(user.last_name, user.first_name) AS ownerSort,
                DATE_FORMAT(candidate_joborder_status_history.date, '%%m-%%d-%%y ') as date,
                candidate_joborder_status_history.date as dateSort,
                candidate_joborder.status as statusSort,
                candidate_joborder_status.short_description as status,
                candidate_joborder_status_history.status_to as statusEver
            FROM
                candidate_joborder
            LEFT JOIN candidate ON
                candidate.candidate_id = candidate_joborder.candidate_id
            LEFT JOIN joborder ON
                joborder.joborder_id = candidate_joborder.joborder_id
            LEFT JOIN company ON
                joborder.company_id = company.company_id
            LEFT JOIN candidate_joborder_status ON
                candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            LEFT JOIN candidate_joborder_status_history ON
                candidate_joborder_status_history.joborder_id = candidate_joborder.joborder_id
                AND
                candidate_joborder_status_history.candidate_id = candidate_joborder.candidate_id
            LEFT JOIN user ON
                candidate.owner = user.user_id
            WHERE
                candidate_joborder.site_id = %s
            AND
                candidate_joborder_status_history.status_to = %s
            AND
                candidate_joborder_status_history.date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            %s
            %s
            %s
            %s",
            $distinct,
            $this->_siteID,
            PIPELINE_STATUS_VERIFIED,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }
}


class SubmittedPipelineDashboard extends DataGrid
{
    protected $_siteID;


    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($siteID, $parameters)
    {
        /* Pager configuration. */
        $this->_tableWidth = 1215;
        $this->_defaultAlphabeticalSortBy = 'lastName';
        $this->ajaxMode = true;
        $this->showExportColumn = false;
        $this->showExportCheckboxes = false;
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;
        $this->dateCriterion = '';
        $this->globalStyle = 'font-size:11px;';
        $this->ignoreSavedColumnLayouts = true;

        $this->defaultSortBy = 'dateModifiedSort';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => 'First Name', 'width' => 85),
            array('name' => 'Last Name', 'width' => 75),
            array('name' => 'Status', 'width' => 75),
            array('name' => 'Position', 'width' => 275),
            array('name' => 'Company', 'width' => 210),
            array('name' => 'Modified', 'width' => 80),
            array('name' => 'Owner', 'width' => 65),
        );


        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_assignedCriterion = "";
        $this->_candidateIDColumn = 'company.company_id';

        $this->_classColumns = array(

            'First Name' =>     array('pagerRender'    => '$ret = \'<img src="images/mru/candidate.gif" height="12" alt="" />\'; if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return $ret.\'&nbsp;<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';',
                                     'sortableColumn'  => 'firstName',
                                     'pagerWidth'      => 85,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'firstName'),

            'Last Name' =>      array('pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'"  style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'"> \'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';',
                                     'sortableColumn'  => 'lastName',
                                     'pagerWidth'      => 75,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'lastName'),

            'Status'    =>      array('pagerRender'    => 'return $rsData[\'status\'];',
                                     'sortableColumn'  => 'statusSort',
                                     'pagerWidth'      => 75,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'status'),

            'Position'    =>    array('pagerRender'    => 'if ($rsData[\'jobOrderIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; if ($rsData[\'joborderStatus\'] != \'Active\') $joborderStatus =  \' (\' . $rsData[\'joborderStatus\'] . \')\'; else $joborderStatus = \'\'; return \'<a href="'.CATSUtility::getIndexName().'?m=joborders&amp;a=show&amp;jobOrderID=\'.$rsData[\'joborderID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'jobOrderTitle\']).$joborderStatus.\'</a>\';',
                                     'sortableColumn'  => 'jobOrderTitle',
                                     'pagerWidth'      => 220,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'jobOrderTitle'),

            'Company'    =>    array('pagerRender'    => 'if ($rsData[\'companyIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\'.$rsData[\'companyID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'companyName\']).\'</a>\';',
                                     'sortableColumn'  => 'companyName',
                                     'pagerWidth'      => 180,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'companyName'),

            'Modified' =>      array('pagerRender'     => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'  => 'dateModifiedSort',
                                     'pagerWidth'      => 70,
                                     'pagerOptional'   => true,
                                     'filterHaving'    => 'DATE_FORMAT(candidate_joborder.date_modified, \'%m-%d-%y (%%h:%%i %%p)\')'),

            'Owner' =>         array('pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'userFirstName\'], $rsData[\'userLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'userFirstName\'] . " " .$rsData[\'userLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),
         );

        parent::__construct("home:SubmittedPipelineDashboard", $parameters);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                candidate_joborder.candidate_joborder_id as candidateJoborderID,
                candidate.first_name as firstName,
                candidate.last_name as lastName,
                candidate.candidate_id as candidateID,
                candidate.site_id as siteID,
                candidate.is_hot as isHot,
                company.name as companyName,
                company.company_id as companyID,
                company.is_hot as companyIsHot,
                joborder.title as jobOrderTitle,
                joborder.is_hot as jobOrderIsHot,
                joborder.joborder_id as joborderID,
                joborder.status as joborderStatus,
                user.first_name as userFirstName,
                user.last_name as userLastName,
                CONCAT(user.last_name, user.first_name) AS ownerSort,
                DATE_FORMAT(candidate_joborder.date_modified, '%%m-%%d-%%y ') as dateModified,
                candidate_joborder.date_modified as dateModifiedSort,
                IF(candidate_joborder.status = %s, 1, 2) as statusSort,
                candidate_joborder_status.short_description as status
            FROM
                candidate_joborder
            LEFT JOIN candidate ON
                candidate.candidate_id = candidate_joborder.candidate_id
            LEFT JOIN joborder ON
                joborder.joborder_id = candidate_joborder.joborder_id
            LEFT JOIN company ON
                joborder.company_id = company.company_id
            LEFT JOIN candidate_joborder_status ON
                candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            LEFT JOIN user ON
                candidate.owner = user.user_id
            WHERE
                candidate_joborder.site_id = %s
            AND
                candidate_joborder.status = %s
            %s
            %s
            %s
            %s",
            $distinct,
            PIPELINE_STATUS_SUBMITTED,
            $this->_siteID,
            PIPELINE_STATUS_SUBMITTED,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }
}

class InterviewingPipelineDashboard extends DataGrid
{
    protected $_siteID;


    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($siteID, $parameters)
    {
        /* Pager configuration. */
        $this->_tableWidth = 1215;
        $this->_defaultAlphabeticalSortBy = 'lastName';
        $this->ajaxMode = true;
        $this->showExportColumn = false;
        $this->showExportCheckboxes = false;
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;
        $this->dateCriterion = '';
        $this->globalStyle = 'font-size:11px;';
        $this->ignoreSavedColumnLayouts = true;

        $this->defaultSortBy = 'dateModifiedSort';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => 'First Name', 'width' => 85),
            array('name' => 'Last Name', 'width' => 75),
            array('name' => 'Status', 'width' => 75),
            array('name' => 'Position', 'width' => 275),
            array('name' => 'Company', 'width' => 210),
            array('name' => 'Modified', 'width' => 80),
            array('name' => 'Owner', 'width' => 65),
        );


        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_assignedCriterion = "";
        $this->_candidateIDColumn = 'company.company_id';

        $this->_classColumns = array(

            'First Name' =>     array('pagerRender'    => '$ret = \'<img src="images/mru/candidate.gif" height="12" alt="" />\'; if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return $ret.\'&nbsp;<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';',
                                     'sortableColumn'  => 'firstName',
                                     'pagerWidth'      => 85,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'firstName'),

            'Last Name' =>      array('pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'"  style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'"> \'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';',
                                     'sortableColumn'  => 'lastName',
                                     'pagerWidth'      => 75,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'lastName'),

            'Status'    =>      array('pagerRender'    => 'return $rsData[\'status\'];',
                                     'sortableColumn'  => 'statusSort',
                                     'pagerWidth'      => 75,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'status'),

            'Position'    =>    array('pagerRender'    => 'if ($rsData[\'jobOrderIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; if ($rsData[\'joborderStatus\'] != \'Active\') $joborderStatus =  \' (\' . $rsData[\'joborderStatus\'] . \')\'; else $joborderStatus = \'\'; return \'<a href="'.CATSUtility::getIndexName().'?m=joborders&amp;a=show&amp;jobOrderID=\'.$rsData[\'joborderID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'jobOrderTitle\']).$joborderStatus.\'</a>\';',
                                     'sortableColumn'  => 'jobOrderTitle',
                                     'pagerWidth'      => 220,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'jobOrderTitle'),

            'Company'    =>    array('pagerRender'    => 'if ($rsData[\'companyIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\'.$rsData[\'companyID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'companyName\']).\'</a>\';',
                                     'sortableColumn'  => 'companyName',
                                     'pagerWidth'      => 180,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'companyName'),

            'Modified' =>      array('pagerRender'     => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'  => 'dateModifiedSort',
                                     'pagerWidth'      => 70,
                                     'pagerOptional'   => true,
                                     'filterHaving'    => 'DATE_FORMAT(candidate_joborder.date_modified, \'%m-%d-%y (%%h:%%i %%p)\')'),

            'Owner' =>         array('pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'userFirstName\'], $rsData[\'userLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'userFirstName\'] . " " .$rsData[\'userLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),
         );

        parent::__construct("home:InterviewingPipelineDashboard", $parameters);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                candidate_joborder.candidate_joborder_id as candidateJoborderID,
                candidate.first_name as firstName,
                candidate.last_name as lastName,
                candidate.candidate_id as candidateID,
                candidate.site_id as siteID,
                candidate.is_hot as isHot,
                company.name as companyName,
                company.company_id as companyID,
                company.is_hot as companyIsHot,
                joborder.title as jobOrderTitle,
                joborder.is_hot as jobOrderIsHot,
                joborder.joborder_id as joborderID,
                joborder.status as joborderStatus,
                user.first_name as userFirstName,
                user.last_name as userLastName,
                CONCAT(user.last_name, user.first_name) AS ownerSort,
                DATE_FORMAT(candidate_joborder.date_modified, '%%m-%%d-%%y ') as dateModified,
                candidate_joborder.date_modified as dateModifiedSort,
                IF(candidate_joborder.status = %s, 1, 2) as statusSort,
                candidate_joborder_status.short_description as status
            FROM
                candidate_joborder
            LEFT JOIN candidate ON
                candidate.candidate_id = candidate_joborder.candidate_id
            LEFT JOIN joborder ON
                joborder.joborder_id = candidate_joborder.joborder_id
            LEFT JOIN company ON
                joborder.company_id = company.company_id
            LEFT JOIN candidate_joborder_status ON
                candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            LEFT JOIN user ON
                candidate.owner = user.user_id
            WHERE
                candidate_joborder.site_id = %s
            AND
                candidate_joborder.status = %s
            %s
            %s
            %s
            %s",
            $distinct,
            PIPELINE_STATUS_INTERVIEWING,
            $this->_siteID,
            PIPELINE_STATUS_INTERVIEWING,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }
}

class OfferedPipelineDashboard extends DataGrid
{
    protected $_siteID;


    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($siteID, $parameters)
    {
        /* Pager configuration. */
        $this->_tableWidth = 1215;
        $this->_defaultAlphabeticalSortBy = 'lastName';
        $this->ajaxMode = true;
        $this->showExportColumn = false;
        $this->showExportCheckboxes = false;
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;
        $this->dateCriterion = '';
        $this->globalStyle = 'font-size:11px;';
        $this->ignoreSavedColumnLayouts = true;

        $this->defaultSortBy = 'dateModifiedSort';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => 'First Name', 'width' => 85),
            array('name' => 'Last Name', 'width' => 75),
            array('name' => 'Status', 'width' => 75),
            array('name' => 'Position', 'width' => 275),
            array('name' => 'Company', 'width' => 210),
            array('name' => 'Modified', 'width' => 80),
            array('name' => 'Owner', 'width' => 65),
        );


        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_assignedCriterion = "";
        $this->_candidateIDColumn = 'company.company_id';

        $this->_classColumns = array(

            'First Name' =>     array('pagerRender'    => '$ret = \'<img src="images/mru/candidate.gif" height="12" alt="" />\'; if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return $ret.\'&nbsp;<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';',
                                     'sortableColumn'  => 'firstName',
                                     'pagerWidth'      => 85,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'firstName'),

            'Last Name' =>      array('pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'"  style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'"> \'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';',
                                     'sortableColumn'  => 'lastName',
                                     'pagerWidth'      => 75,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'lastName'),

            'Status'    =>      array('pagerRender'    => 'return $rsData[\'status\'];',
                                     'sortableColumn'  => 'statusSort',
                                     'pagerWidth'      => 75,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'status'),

            'Position'    =>    array('pagerRender'    => 'if ($rsData[\'jobOrderIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; if ($rsData[\'joborderStatus\'] != \'Active\') $joborderStatus =  \' (\' . $rsData[\'joborderStatus\'] . \')\'; else $joborderStatus = \'\'; return \'<a href="'.CATSUtility::getIndexName().'?m=joborders&amp;a=show&amp;jobOrderID=\'.$rsData[\'joborderID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'jobOrderTitle\']).$joborderStatus.\'</a>\';',
                                     'sortableColumn'  => 'jobOrderTitle',
                                     'pagerWidth'      => 220,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'jobOrderTitle'),

            'Company'    =>    array('pagerRender'    => 'if ($rsData[\'companyIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\'.$rsData[\'companyID\'].\'"  style="font-size:11px;" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'companyName\']).\'</a>\';',
                                     'sortableColumn'  => 'companyName',
                                     'pagerWidth'      => 180,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'companyName'),

            'Modified' =>      array('pagerRender'     => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'  => 'dateModifiedSort',
                                     'pagerWidth'      => 70,
                                     'pagerOptional'   => true,
                                     'filterHaving'    => 'DATE_FORMAT(candidate_joborder.date_modified, \'%m-%d-%y (%%h:%%i %%p)\')'),

            'Owner' =>         array('pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'userFirstName\'], $rsData[\'userLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'userFirstName\'] . " " .$rsData[\'userLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),
         );

        parent::__construct("home:OfferedPipelineDashboard", $parameters);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                candidate_joborder.candidate_joborder_id as candidateJoborderID,
                candidate.first_name as firstName,
                candidate.last_name as lastName,
                candidate.candidate_id as candidateID,
                candidate.site_id as siteID,
                candidate.is_hot as isHot,
                company.name as companyName,
                company.company_id as companyID,
                company.is_hot as companyIsHot,
                joborder.title as jobOrderTitle,
                joborder.is_hot as jobOrderIsHot,
                joborder.joborder_id as joborderID,
                joborder.status as joborderStatus,
                user.first_name as userFirstName,
                user.last_name as userLastName,
                CONCAT(user.last_name, user.first_name) AS ownerSort,
                DATE_FORMAT(candidate_joborder.date_modified, '%%m-%%d-%%y ') as dateModified,
                candidate_joborder.date_modified as dateModifiedSort,
                IF(candidate_joborder.status = %s, 1, 2) as statusSort,
                candidate_joborder_status.short_description as status
            FROM
                candidate_joborder
            LEFT JOIN candidate ON
                candidate.candidate_id = candidate_joborder.candidate_id
            LEFT JOIN joborder ON
                joborder.joborder_id = candidate_joborder.joborder_id
            LEFT JOIN company ON
                joborder.company_id = company.company_id
            LEFT JOIN candidate_joborder_status ON
                candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            LEFT JOIN user ON
                candidate.owner = user.user_id
            WHERE
                candidate_joborder.site_id = %s
            AND
                candidate_joborder.status = %s
            %s
            %s
            %s
            %s",
            $distinct,
            PIPELINE_STATUS_OFFERED,
            $this->_siteID,
            PIPELINE_STATUS_OFFERED,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }
}


class DriftingDashboard extends DataGrid
{
    protected $_siteID;


    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($siteID, $parameters)
    {
        /* Pager configuration. */
        $this->_tableWidth = 1215;
        $this->_defaultAlphabeticalSortBy = 'lastName';
        $this->ajaxMode = true;
        $this->showExportColumn = false;
        $this->showExportCheckboxes = false;
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;
        $this->dateCriterion = '';
        $this->globalStyle = 'font-size:11px;';
        $this->ignoreSavedColumnLayouts = true;

        $this->defaultSortBy = 'notes';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => 'First Name', 'width' => 85),
            array('name' => 'Last Name', 'width' => 75),
            array('name' => 'Regarding', 'width' => 205),
            array('name' => 'Entered', 'width' => 60),
            array('name' => 'Created', 'width' => 125),
            array('name' => 'Modified', 'width' => 125),
            array('name' => 'Activity Notes', 'width' => 175),
        );


        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_assignedCriterion = "";
        $this->_candidateIDColumn = 'company.company_id';

        $this->_classColumns = array(

            'First Name' =>     array('pagerRender'    => '$ret = \'<img src="images/mru/candidate.gif" height="12" alt="" />\'; if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return $ret.\'&nbsp;<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'" style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';',
                                     'sortableColumn'  => 'firstName',
                                     'pagerWidth'      => 85,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'firstName'),

            'Last Name' =>      array('pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'candidateID\'].\'"  style="font-size:11px;" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make(DATA_ITEM_CANDIDATE,$rsData[\'candidateID\'],$rsData[\'siteID\'])).\'"> \'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';',
                                     'sortableColumn'  => 'lastName',
                                     'pagerWidth'      => 75,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'lastName'),


            'Entered' =>         array('pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'userFirstName\'], $rsData[\'userLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'userFirstName\'] . " " .$rsData[\'userLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),
                                     
            'Created' =>       array('select'   => 'DATE_FORMAT(activity.date_created, \'%m-%d-%y\') AS dateCreated',
                                     'pagerRender'      => 'return $rsData[\'dateCreated\'];',
                                     'sortableColumn'     => 'dateCreatedSort',
                                     'pagerWidth'    => 125,
                                     'filterHaving' => 'DATE_FORMAT(activity.date_created, \'%m-%d-%y\')'),

            'Modified' =>      array('select'   => 'DATE_FORMAT(activity.date_modified, \'%m-%d-%y\') AS dateModified',
                                     'pagerRender'      => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'     => 'dateModifiedSort',
                                     'pagerWidth'    => 125,
                                     'pagerOptional' => false,
                                     'filterHaving' => 'DATE_FORMAT(activity.date_modified, \'%m-%d-%y\')'),

            'Activity Notes'    =>      array('pagerRender'    => 'return $rsData[\'notes\'];',
                                     'sortableColumn'  => 'notes',
                                     'pagerWidth'      => 175,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'notes'),
    
             'Regarding' =>      array('pagerRender'    => 'if ($rsData[\'jobOrderIsHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; if ($rsData[\'companyIsHot\'] == 1) $companyClassName =  \'jobLinkHot\'; else $companyClassName = \'jobLinkCold\';  if ($rsData[\'jobOrderTitle\'] == \'\') {$ret = \'General\'; } else {$ret = \'<a href="'.CATSUtility::getIndexName().'?m=joborders&amp;a=show&amp;jobOrderID=\'.$rsData[\'jobOrderID\'].\'" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'jobOrderTitle\']).\'</a>\'; if($rsData[\'regardingCompanyName\'] != \'\') {$ret .= \' <a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\'.$rsData[\'companyID\'].\'" class="\'.$companyClassName.\'">(\'.htmlspecialchars($rsData[\'regardingCompanyName\']).\')\';}} return $ret;', 
                                     'sortableColumn'  => 'regarding',
                                     'pagerWidth'      => 205,
                                     'pagerOptional'   => true,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'regarding'),    
         );

        parent::__construct("home:DriftingDashboard", $parameters);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                candidate.first_name as firstName,
                candidate.last_name as lastName,
                candidate.candidate_id as candidateID,
                candidate.site_id as siteID,
                candidate.is_hot as isHot,
                company.name as companyName,
                company.company_id as companyID,
                company.is_hot as companyIsHot,
                joborder.title as jobOrderTitle,
                joborder.is_hot as jobOrderIsHot,
                joborder.joborder_id as jobOrderID,
                user.first_name as userFirstName,
                user.last_name as userLastName,
                CONCAT(user.last_name, user.first_name) AS ownerSort,
                activity.notes as notes,
                DATE_FORMAT(
                    activity.date_created, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateCreated,
                activity.date_created AS dateCreatedSort,
                DATE_FORMAT(
                    activity.date_modified, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateModified,
                activity.date_modified AS dateModifiedSort,
                IF(
                    ISNULL(joborder.title),
                    'General',
                    CONCAT(joborder.title, ' (', company.name, ')')
                ) as regarding,
                joborder.title AS regardingJobTitle,
                company.name AS regardingCompanyName
            FROM
                activity
            LEFT JOIN candidate ON
                candidate.candidate_id = activity.data_item_id
            LEFT JOIN joborder ON
                joborder.joborder_id = activity.joborder_id
            LEFT JOIN company ON
                joborder.company_id = company.company_id
            LEFT JOIN user ON
                activity.entered_by = user.user_id
            WHERE
                activity.site_id = %s
            AND
                activity.data_item_type = %s
            AND
                activity.type = %s
            %s
            %s
            %s
            %s",
            $distinct,
            $this->_siteID,
            $this->_db->makeQueryInteger(DATA_ITEM_CANDIDATE),
            $this->_db->makeQueryInteger(ACTIVITY_DRIFTING),
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }
}

// FIXME: Includes in the middle of the file = bad.
// FIXME: Multiple classes per file probably also bad.
include_once('./lib/ActivityEntries.php');
include_once('./lib/Hooks.php');
include_once('./lib/InfoString.php');

class CallsDataGrid extends DataGrid
{
    protected $_siteID;


    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($siteID, $parameters)
    {
        /* Pager configuration. */
        $this->_tableWidth = 400;
        $this->_defaultAlphabeticalSortBy = 'lastName';
        $this->ajaxMode = true;
        $this->showExportColumn = false;
        $this->showExportCheckboxes = false;
        $this->showActionArea = true;
        $this->allowSorting = false;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;
        $this->dateCriterion = '';
        $this->globalStyle = 'font-size:11px; margin:1px; padding:1px;';
        $this->listStyle = true;
        $this->ignoreSavedColumnLayouts = true;

        if (isset($parameters['period']) && !empty($parameters['period']))
        {
            $this->dateCriterion .= ' AND activity.date_created >= ' . $parameters['period'] . ' ';
        }
        else
        {
            if (isset($parameters['startDate']) && !empty($parameters['startDate']))
            {
                $this->dateCriterion .= ' AND activity.date_created >= \'' .$parameters['startDate'].'\' ';
            }

            if (isset($parameters['endDate']) && !empty($parameters['endDate']))
            {
                $this->dateCriterion .= ' AND activity.date_created <= \''.$parameters['endDate'].'\' ';
            }
        }

        $this->defaultSortBy = 'dateCreatedSort';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => 'Time', 'width' => 90),
            array('name' => 'Name', 'width' => 175)
        );


        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_userID = $_SESSION['CATS']->getUserID();
        $this->_assignedCriterion = "";
        $this->_dataItemIDColumn = 'company.company_id';

        $this->_classColumns = array(
            'Time' =>          array('pagerRender'    => 'return $rsData[\'dateCreated\'].\':\';',
                                      'sortableColumn' => 'dateCreatedSort',
                                      'pagerWidth'     => 90,
                                      'pagerOptional'  => false,
                                      'alphaNavigation'=> true,
                                      'filterHaving'   => 'dateCreated'),

            'Name' =>          array('pagerRender'    => 'if ($rsData[\'dataItemType\']=='.DATA_ITEM_CANDIDATE.') {$ret = \'<img src="images/mru/candidate.gif" height="12" alt="" />\';} else if ($rsData[\'dataItemType\']=='.DATA_ITEM_CONTACT.') {$ret = \'<img src="images/mru/contact.gif" height="12">\';} else {$ret = \'<img src="images/mru/blank.gif">\';} if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; if ($rsData[\'dataItemType\']=='.DATA_ITEM_CANDIDATE.') {$ret = $ret.\'&nbsp;<a style="font-size:11px;" href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'dataItemID\'].\'" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make($rsData[\'dataItemType\'],$rsData[\'dataItemID\'],$rsData[\'siteID\'])).\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';} else {$ret = $ret.\'&nbsp;<a style="font-size:11px;" href="'.CATSUtility::getIndexName().'?m=contacts&amp;a=show&amp;contactID=\'.$rsData[\'dataItemID\'].\'" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make($rsData[\'dataItemType\'],$rsData[\'dataItemID\'],$rsData[\'siteID\'])).\'">\'.htmlspecialchars($rsData[\'firstName\']).\'</a>\';} if ($rsData[\'dataItemType\']=='.DATA_ITEM_CANDIDATE.') {return $ret . \'<a style="font-size:11px;" href="'.CATSUtility::getIndexName().'?m=candidates&amp;a=show&amp;candidateID=\'.$rsData[\'dataItemID\'].\'" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make($rsData[\'dataItemType\'],$rsData[\'dataItemID\'],$rsData[\'siteID\'])).\'"> \'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';} else {return $ret . \'<a style="font-size:11px;" href="'.CATSUtility::getIndexName().'?m=contacts&amp;a=show&amp;contactID=\'.$rsData[\'dataItemID\'].\'" class="\'.$className.\'" title="\'.htmlspecialchars(InfoString::make($rsData[\'dataItemType\'],$rsData[\'dataItemID\'],$rsData[\'siteID\'])).\'"> \'.htmlspecialchars($rsData[\'lastName\']).\'</a>\';}',
                                     'sortableColumn'  => 'firstName',
                                     'pagerWidth'      => 120,
                                     'pagerOptional'   => false,
                                     'alphaNavigation' => true,
                                     'filterHaving'    => 'firstName'),

        );

        parent::__construct("home:CallsDataGrid", $parameters);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                activity.activity_id AS activityID,
                activity.data_item_id AS dataItemID,
                activity.data_item_type AS dataItemType,
                activity.site_id AS siteID,
                data_item_type.short_description AS item,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.phone_work AS workPhone,
                candidate.phone_cell AS cellPhone,
                candidate.phone_home AS otherPhone,
                candidate.is_hot AS isHot,
                joborder.is_hot AS jobIsHot,
                company.is_hot AS companyIsHot,
                company.company_id AS companyID,
                activity.joborder_id AS jobOrderID,
                activity.notes AS notes,
                activity_type.short_description AS typeDescription,
                DATE_FORMAT(
                    activity.date_created, '%%m-%%d-%%y %%h:%%i %%p'
                ) AS dateCreated,
                activity.date_created AS dateCreatedSort,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName,
                CONCAT(entered_by_user.last_name, entered_by_user.first_name) AS enteredBySort,
                IF(ISNULL(joborder.title),
                    'General',
                    CONCAT(joborder.title, ' (', company.name, ')'))
                AS regarding,
                joborder.title AS regardingJobTitle,
                company.name AS regardingCompanyName
            FROM
                activity
            JOIN data_item_type
                ON activity.data_item_type = data_item_type.data_item_type_id
            LEFT JOIN user AS entered_by_user
                ON activity.entered_by = entered_by_user.user_id
            LEFT JOIN activity_type
                ON activity.type = activity_type.activity_type_id
            LEFT JOIN joborder
                ON activity.joborder_id = joborder.joborder_id
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            INNER JOIN candidate
                ON activity.data_item_id = candidate.candidate_id
            WHERE
                activity.data_item_type = %s
            AND
                activity.entered_by = %s
            AND
                activity.site_id = %s
                %s
                %s
            UNION
            SELECT %s
                activity.activity_id AS activityID,
                activity.data_item_id AS dataItemID,
                activity.data_item_type AS dataItemType,
                activity.site_id AS siteID,
                data_item_type.short_description AS item,
                contact.first_name AS firstName,
                contact.last_name AS lastName,
                contact.phone_work AS workPhone,
                contact.phone_cell AS cellPhone,
                contact.phone_other AS otherPhone,
                contact.is_hot AS isHot,
                joborder.is_hot AS jobIsHot,
                company.is_hot AS companyIsHot,
                company.company_id AS companyID,
                activity.joborder_id AS jobOrderID,
                activity.notes AS notes,
                activity_type.short_description AS typeDescription,
                DATE_FORMAT(
                    activity.date_created, '%%m-%%d-%%y %%h:%%i %%p'
                ) AS dateCreated,
                activity.date_created AS dateCreatedSort,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName,
                CONCAT(entered_by_user.last_name, entered_by_user.first_name) AS enteredBySort,
                IF(ISNULL(joborder.title),
                    'General',
                    CONCAT(joborder.title, ' (', company.name, ')'))
                AS regarding,
                joborder.title AS regardingJobTitle,
                company.name AS regardingCompanyName
            FROM
                activity
            JOIN data_item_type
                ON activity.data_item_type = data_item_type.data_item_type_id
            LEFT JOIN user AS entered_by_user
                ON activity.entered_by = entered_by_user.user_id
            LEFT JOIN activity_type
                ON activity.type = activity_type.activity_type_id
            LEFT JOIN joborder
                ON activity.joborder_id = joborder.joborder_id
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            INNER JOIN contact
                ON activity.data_item_id = contact.contact_id
            WHERE
                activity.data_item_type = %s
            AND
                activity.entered_by = %s
            AND
                activity.site_id = %s
                %s
                %s
            %s
            ORDER BY dateCreatedSort DESC
            LIMIT 6",
            $distinct,
            DATA_ITEM_CANDIDATE,
            $this->_userID,
            $this->_siteID,
            $this->dateCriterion,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            $distinct,
            DATA_ITEM_CONTACT,
            $this->_userID,
            $this->_siteID,
            $this->dateCriterion,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : ''
        );

        return $sql;
    }
}

?>
