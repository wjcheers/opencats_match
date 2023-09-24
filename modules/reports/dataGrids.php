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
include_once('./lib/Statistics.php');


class ReportCompanies extends DataGrid
{
    protected $_siteID;


    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($siteID, $parameters)
    {        
        /* Pager configuration. */
        $this->_tableWidth = 1215;
        $this->_defaultAlphabeticalSortBy = 'name';
        $this->ajaxMode = false;
        $this->showExportColumn = true;
        $this->showExportCheckboxes = true;
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;
        $this->dateCriterion = '';
        $this->globalStyle = 'font-size:11px;';
        $this->ignoreSavedColumnLayouts = true;

        $this->defaultSortBy = 'ownerSort';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => 'Name', 'width' => 255),
            array('name' => 'Owner', 'width' => 65),
            array('name' => 'Modified', 'width' => 60),
            array('name' => 'Pipelines', 'width' => 100),
            array('name' => 'Submissions', 'width' => 100),
            array('name' => 'Interviews', 'width' => 100),
            array('name' => 'Updated Notes', 'width' => 235),
        );


        $this->_db = DatabaseConnection::getInstance();
        $this->_period = $parameters['period'];
        $this->_siteID = $siteID;
        $this->_userID = $_SESSION['CATS']->getUserID();
        $this->_assignedCriterion = "";
        $this->_dataItemIDColumn = 'company.company_id';

        $this->_classColumns = array(
            'Name' =>     array('select'         => 'company.name AS name',
                                      'pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\'.$rsData[\'companyID\'].\'" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'name\']).\'</a>\';',
                                      'sortableColumn' => 'name',
                                      'pagerWidth'     => 60,
                                      'pagerOptional'  => false,
                                      'alphaNavigation'=> true,
                                      'filter'         => 'company.name'),

            'Owner' =>         array('select'   => 'owner_user.first_name AS ownerFirstName,' .
                                                   'owner_user.last_name AS ownerLastName,' .
                                                   'CONCAT(owner_user.last_name, owner_user.first_name) AS ownerSort',
                                     'pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'ownerFirstName\'], $rsData[\'ownerLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'ownerFirstName\'] . " " .$rsData[\'ownerLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 65,
                                     'pagerOptional'  => false,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),

            'Modified' =>      array('select'   => 'DATE_FORMAT(company.date_modified, \'%m-%d-%y\') AS dateModified',
                                     'pagerRender'      => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'     => 'dateModified',
                                     'pagerWidth'    => 60,
                                     'pagerOptional' => false,
                                     'filterHaving' => 'DATE(company.date_modified)',
                                     'filterTypes'   => '=~=g=s'),


            'Pipelines' =>      array('pagerRender'     => 'return $rsData[\'pipelineCount\'];',
                                     'sortableColumn'  => 'pipelineCount',
                                     'pagerWidth'      => 100,
                                     'pagerOptional'   => false,
                                     'filterHaving'    => 'pipelineCount',
                                     'filterTypes'     => '===>=<'),

            'Submissions' =>      array('pagerRender'     => 'return $rsData[\'submissionCount\'];',
                                     'sortableColumn'  => 'submissionCount',
                                     'pagerWidth'      => 100,
                                     'pagerOptional'   => false,
                                     'filterHaving'    => 'submissionCount',
                                     'filterTypes'     => '===>=<'),

            'Interviews' =>      array('pagerRender'     => 'return $rsData[\'interviewingCount\'];',
                                     'sortableColumn'  => 'interviewingCount',
                                     'pagerWidth'      => 100,
                                     'pagerOptional'   => false,
                                     'filterHaving'    => 'interviewingCount',
                                     'filterTypes'     => '===>=<'),

            'Updated Notes' =>      array('pagerRender'     => 'return $rsData[\'companyUpdatedNotes\'];',
                                     'sortableColumn'  => 'companyUpdatedNotes',
                                     'pagerWidth'      => 220,
                                     'pagerOptional'   => false,
                                     'filterHaving'    => 'companyUpdatedNotes'),

            'OwnerID' =>       array('select'    => '',
                                     'filter'    => 'company.owner',
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'filterDescription' => 'Only My Companies'),

            'IsHot' =>         array('select'    => '',
                                     'filter'    => 'company.is_hot',
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'filterDescription' => 'Only Hot Companies')
         );

        parent::__construct("reports:ReportCompanies", $parameters);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        $statistics = new Statistics($this->_siteID);
        $criterionPipeline = $statistics->makePeriodCriterion('candidate_joborder.date_created', $this->_period);
        $criterion = $statistics->makePeriodCriterion('date', $this->_period);

        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                %s,
                company.company_id AS companyID,
                company.company_id AS exportID,
                extraStatus.value AS companyStatus,
                company.is_hot AS isHot,
                company.date_modified AS dateModifiedSort,
                extraShortName.value AS companyShortName,
                extraNotes.value AS companyUpdatedNotes,
                (SELECT
                        COUNT(*)
                    FROM
                        candidate_joborder
                    LEFT JOIN joborder
                        ON joborder.joborder_id = candidate_joborder.joborder_id
                    LEFT JOIN company AS companyPipeline
                        ON companyPipeline.company_id = joborder.company_id
                    WHERE
                        companyPipeline.site_id = %s
                    AND
                        companyPipeline.company_id = company.company_id
                        %s) AS pipelineCount,
                (SELECT
                        COUNT(*) 
                    FROM
                        candidate_joborder_status_history
                    LEFT JOIN joborder
                        ON joborder.joborder_id = candidate_joborder_status_history.joborder_id
                    LEFT JOIN company AS companyPipeline
                        ON companyPipeline.company_id = joborder.company_id
                    WHERE
                        status_to = 400
                    AND
                        joborder.status IN ('Active', 'OnHold', 'Full', 'Closed')
                    AND
                        companyPipeline.site_id = %s
                    AND
                        companyPipeline.company_id = company.company_id
                    %s) AS submissionCount,
                (SELECT
                        COUNT(*)
                    FROM
                        candidate_joborder_status_history
                    LEFT JOIN candidate
                        ON candidate.candidate_id = candidate_joborder_status_history.candidate_id
                    LEFT JOIN joborder
                        ON joborder.joborder_id = candidate_joborder_status_history.joborder_id
                    LEFT JOIN user AS owner_user
                        ON owner_user.user_id = candidate.owner
                    LEFT JOIN company AS companyPipeline
                        ON companyPipeline.company_id = joborder.company_id
                    WHERE
                        candidate_joborder_status_history.status_to = 500
                    AND
                        companyPipeline.site_id = %s
                    AND
                        companyPipeline.company_id = company.company_id
                    %s) AS interviewingCount
            FROM
                company
            LEFT JOIN user AS owner_user
                ON owner_user.user_id = company.owner
            LEFT JOIN extra_field AS extraNotes
                ON extraNotes.data_item_id = company.company_id
                    AND extraNotes.data_item_type = %s
                    AND extraNotes.field_name = 'Updated Notes'
            LEFT JOIN extra_field AS extraStatus
                ON extraStatus.data_item_id = company.company_id
                    AND extraStatus.data_item_type = %s
                    AND extraStatus.field_name = 'Status'
            LEFT JOIN extra_field AS extraShortName
                ON extraShortName.data_item_id = company.company_id
                    AND extraShortName.data_item_type = %s
                    AND extraShortName.field_name = 'Short Name'
            WHERE
                extraStatus.value = 'Active'
            AND
                company.site_id = %s
            %s
            %s
            %s
            %s",
            $distinct,
            $selectSQL,
            $this->_siteID,
            $criterionPipeline,
            $this->_siteID,
            $criterion,
            $this->_siteID,
            $criterion,
            DATA_ITEM_COMPANY,
            DATA_ITEM_COMPANY,
            DATA_ITEM_COMPANY,
            $this->_siteID,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }
}

?>
