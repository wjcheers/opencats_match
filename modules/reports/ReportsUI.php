<?php
/*
 * CATS
 * Reports Module
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
 * $Id: ReportsUI.php 3810 2007-12-05 19:13:25Z brian $
 */

include_once('./lib/Statistics.php');
include_once('./lib/DateUtility.php');
include_once('./lib/Candidates.php');
include_once('./lib/ActivityEntries.php');
include_once('./lib/CommonErrors.php');

class ReportsUI extends UserInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'reports';
        $this->_moduleName = 'reports';
        $this->_moduleTabText = 'Reports';
        $this->_subTabs = array(
                'EEO Reports' => CATSUtility::getIndexName() . '?m=reports&amp;a=customizeEEOReport'
            );
    }


    public function handleRequest()
    {
        if (!eval(Hooks::get('REPORTS_HANDLE_REQUEST'))) return;

        $action = $this->getAction();
        switch ($action)
        {
            case 'graphView':
                $this->graphView();
                break;

            case 'generateJobOrderReportPDF':
                $this->generateJobOrderReportPDF();
                break;

            case 'showSubmissionReport':
                $this->showSubmissionReport();
                break;

            case 'showFunctionReport':
                $this->showFunctionReport();
                break;

            case 'showCompaniesReport':
                $this->showCompaniesReport();
                break;

            case 'showPlacementReport':
                $this->showPlacementReport();
                break;

            case 'showOfferReport':
                $this->showOfferReport();
                break;

            case 'showUserReport':
                $this->showUserReport();
                break;

            case 'showUserReportByUser':
                $this->showUserReportByUser();
                break;

            case 'showSubmitReport':
                $this->showSubmitReport();
                break;

            case 'customizeJobOrderReport':
                $this->customizeJobOrderReport();
                break;

            case 'customizeEEOReport':
                $this->customizeEEOReport();
                break;

            case 'generateEEOReportPreview':
                $this->generateEEOReportPreview();
                break;

            case 'reports':
            default:
                $this->reports();
                break;
        }
    }

    private function reports()
    {
        /* Grab an instance of Statistics. */
        $statistics = new Statistics($this->_siteID);

        /* Get company statistics. */
        $statisticsData['totalCompanies']     = $statistics->getCompanyCount(TIME_PERIOD_TODATE);
        $statisticsData['companiesToday']     = $statistics->getCompanyCount(TIME_PERIOD_TODAY);
        $statisticsData['companiesYesterday'] = $statistics->getCompanyCount(TIME_PERIOD_YESTERDAY);
        $statisticsData['companiesThisWeek']  = $statistics->getCompanyCount(TIME_PERIOD_THISWEEK);
        $statisticsData['companiesLastWeek']  = $statistics->getCompanyCount(TIME_PERIOD_LASTWEEK);
        $statisticsData['companiesThisMonth'] = $statistics->getCompanyCount(TIME_PERIOD_THISMONTH);
        $statisticsData['companiesLastMonth'] = $statistics->getCompanyCount(TIME_PERIOD_LASTMONTH);
        $statisticsData['companiesThisQuarter'] = $statistics->getCompanyCount(TIME_PERIOD_THISQUARTER);
        $statisticsData['companiesLastQuarter'] = $statistics->getCompanyCount(TIME_PERIOD_LASTQUARTER);
        $statisticsData['companiesThisYear']  = $statistics->getCompanyCount(TIME_PERIOD_THISYEAR);
        $statisticsData['companiesLastYear']  = $statistics->getCompanyCount(TIME_PERIOD_LASTYEAR);

        /* Get candidate statistics. */
        $statisticsData['totalCandidates']     = $statistics->getCandidateCount(TIME_PERIOD_TODATE);
        $statisticsData['candidatesToday']     = $statistics->getCandidateCount(TIME_PERIOD_TODAY);
        $statisticsData['candidatesYesterday'] = $statistics->getCandidateCount(TIME_PERIOD_YESTERDAY);
        $statisticsData['candidatesThisWeek']  = $statistics->getCandidateCount(TIME_PERIOD_THISWEEK);
        $statisticsData['candidatesLastWeek']  = $statistics->getCandidateCount(TIME_PERIOD_LASTWEEK);
        $statisticsData['candidatesThisMonth'] = $statistics->getCandidateCount(TIME_PERIOD_THISMONTH);
        $statisticsData['candidatesLastMonth'] = $statistics->getCandidateCount(TIME_PERIOD_LASTMONTH);
        $statisticsData['candidatesThisQuarter'] = $statistics->getCandidateCount(TIME_PERIOD_THISQUARTER);
        $statisticsData['candidatesLastQuarter'] = $statistics->getCandidateCount(TIME_PERIOD_LASTQUARTER);
        $statisticsData['candidatesThisYear']  = $statistics->getCandidateCount(TIME_PERIOD_THISYEAR);
        $statisticsData['candidatesLastYear']  = $statistics->getCandidateCount(TIME_PERIOD_LASTYEAR);

        /* Get submission statistics. */
        $statisticsData['totalSubmissions']     = $statistics->getSubmissionCount(TIME_PERIOD_TODATE);
        $statisticsData['submissionsToday']     = $statistics->getSubmissionCount(TIME_PERIOD_TODAY);
        $statisticsData['submissionsYesterday'] = $statistics->getSubmissionCount(TIME_PERIOD_YESTERDAY);
        $statisticsData['submissionsThisWeek']  = $statistics->getSubmissionCount(TIME_PERIOD_THISWEEK);
        $statisticsData['submissionsLastWeek']  = $statistics->getSubmissionCount(TIME_PERIOD_LASTWEEK);
        $statisticsData['submissionsThisMonth'] = $statistics->getSubmissionCount(TIME_PERIOD_THISMONTH);
        $statisticsData['submissionsLastMonth'] = $statistics->getSubmissionCount(TIME_PERIOD_LASTMONTH);
        $statisticsData['submissionsThisQuarter'] = $statistics->getSubmissionCount(TIME_PERIOD_THISQUARTER);
        $statisticsData['submissionsLastQuarter'] = $statistics->getSubmissionCount(TIME_PERIOD_LASTQUARTER);
        $statisticsData['submissionsThisYear']  = $statistics->getSubmissionCount(TIME_PERIOD_THISYEAR);
        $statisticsData['submissionsLastYear']  = $statistics->getSubmissionCount(TIME_PERIOD_LASTYEAR);

		/* Get placement statistics. */
        $statisticsData['totalPlacements']     = $statistics->getPlacementCount(TIME_PERIOD_TODATE);
        $statisticsData['placementsToday']     = $statistics->getPlacementCount(TIME_PERIOD_TODAY);
        $statisticsData['placementsYesterday'] = $statistics->getPlacementCount(TIME_PERIOD_YESTERDAY);
        $statisticsData['placementsThisWeek']  = $statistics->getPlacementCount(TIME_PERIOD_THISWEEK);
        $statisticsData['placementsLastWeek']  = $statistics->getPlacementCount(TIME_PERIOD_LASTWEEK);
        $statisticsData['placementsThisMonth'] = $statistics->getPlacementCount(TIME_PERIOD_THISMONTH);
        $statisticsData['placementsLastMonth'] = $statistics->getPlacementCount(TIME_PERIOD_LASTMONTH);
        $statisticsData['placementsThisQuarter'] = $statistics->getPlacementCount(TIME_PERIOD_THISQUARTER);
        $statisticsData['placementsLastQuarter'] = $statistics->getPlacementCount(TIME_PERIOD_LASTQUARTER);
        $statisticsData['placementsThisYear']  = $statistics->getPlacementCount(TIME_PERIOD_THISYEAR);
        $statisticsData['placementsLastYear']  = $statistics->getPlacementCount(TIME_PERIOD_LASTYEAR);

        /* Get contact statistics. */
        $statisticsData['totalContacts']     = $statistics->getContactCount(TIME_PERIOD_TODATE);
        $statisticsData['contactsToday']     = $statistics->getContactCount(TIME_PERIOD_TODAY);
        $statisticsData['contactsYesterday'] = $statistics->getContactCount(TIME_PERIOD_YESTERDAY);
        $statisticsData['contactsThisWeek']  = $statistics->getContactCount(TIME_PERIOD_THISWEEK);
        $statisticsData['contactsLastWeek']  = $statistics->getContactCount(TIME_PERIOD_LASTWEEK);
        $statisticsData['contactsThisMonth'] = $statistics->getContactCount(TIME_PERIOD_THISMONTH);
        $statisticsData['contactsLastMonth'] = $statistics->getContactCount(TIME_PERIOD_LASTMONTH);
        $statisticsData['contactsThisQuarter'] = $statistics->getContactCount(TIME_PERIOD_THISQUARTER);
        $statisticsData['contactsLastQuarter'] = $statistics->getContactCount(TIME_PERIOD_LASTQUARTER);
        $statisticsData['contactsThisYear']  = $statistics->getContactCount(TIME_PERIOD_THISYEAR);
        $statisticsData['contactsLastYear']  = $statistics->getContactCount(TIME_PERIOD_LASTYEAR);

		/* Get offer statistics. */
        $statisticsData['totalOffers']     = $statistics->getOfferCount(TIME_PERIOD_TODATE);
        
        /* Get job order statistics. */
        $statisticsData['totalJobOrders']     = $statistics->getJobOrderCount(TIME_PERIOD_TODATE);
        $statisticsData['jobOrdersToday']     = $statistics->getJobOrderCount(TIME_PERIOD_TODAY);
        $statisticsData['jobOrdersYesterday'] = $statistics->getJobOrderCount(TIME_PERIOD_YESTERDAY);
        $statisticsData['jobOrdersThisWeek']  = $statistics->getJobOrderCount(TIME_PERIOD_THISWEEK);
        $statisticsData['jobOrdersLastWeek']  = $statistics->getJobOrderCount(TIME_PERIOD_LASTWEEK);
        $statisticsData['jobOrdersThisMonth'] = $statistics->getJobOrderCount(TIME_PERIOD_THISMONTH);
        $statisticsData['jobOrdersLastMonth'] = $statistics->getJobOrderCount(TIME_PERIOD_LASTMONTH);
        $statisticsData['jobOrdersThisQuarter'] = $statistics->getJobOrderCount(TIME_PERIOD_THISQUARTER);
        $statisticsData['jobOrdersLastQuarter'] = $statistics->getJobOrderCount(TIME_PERIOD_LASTQUARTER);
        $statisticsData['jobOrdersThisYear']  = $statistics->getJobOrderCount(TIME_PERIOD_THISYEAR);
        $statisticsData['jobOrdersLastYear']  = $statistics->getJobOrderCount(TIME_PERIOD_LASTYEAR);

        if (!eval(Hooks::get('REPORTS_SHOW'))) return;

        $this->_template->assign('active', $this);
        $this->_template->assign('statisticsData', $statisticsData);
        $this->_template->display('./modules/reports/Reports.tpl');
    }

    private function graphView()
    {
        if (isset($_GET['theImage']))
        {
            $this->_template->assign('theImage', $_GET['theImage']);
        }
        else
        {
            $this->_template->assign('theImage', '');
        }

        if (!eval(Hooks::get('REPORTS_GRAPH'))) return;

        $this->_template->assign('active', $this);
        $this->_template->display('./modules/reports/GraphView.tpl');
    }

    private function showSubmissionReport()
    {
        //FIXME: getTrimmedInput
        if (isset($_GET['period']) && !empty($_GET['period']))
        {
            $period = $_GET['period'];
        }
        else
        {
            $period = '';
        }


        switch ($period)
        {
            case 'yesterday':
                $period = TIME_PERIOD_YESTERDAY;
                $reportTitle = 'Yesterday\'s Report';
                break;

            case 'thisWeek':
                $period = TIME_PERIOD_THISWEEK;
                $reportTitle = 'This Week\'s Report';
                break;

            case 'lastWeek':
                $period = TIME_PERIOD_LASTWEEK;
                $reportTitle = 'Last Week\'s Report';
                break;

            case 'thisMonth':
                $period = TIME_PERIOD_THISMONTH;
                $reportTitle = 'This Month\'s Report';
                break;

            case 'lastMonth':
                $period = TIME_PERIOD_LASTMONTH;
                $reportTitle = 'Last Month\'s Report';
                break;

            case 'thisQuarter':
                $period = TIME_PERIOD_THISQUARTER;
                $reportTitle = 'This Quarter\'s Report';
                break;

            case 'lastQuarter':
                $period = TIME_PERIOD_LASTQUARTER;
                $reportTitle = 'Last Quarter\'s Report';
                break;
                
            case 'thisYear':
                $period = TIME_PERIOD_THISYEAR;
                $reportTitle = 'This Year\'s Report';
                break;

            case 'lastYear':
                $period = TIME_PERIOD_LASTYEAR;
                $reportTitle = 'Last Year\'s Report';
                break;

            case 'toDate':
                $period = TIME_PERIOD_TODATE;
                $reportTitle = 'To Date Report';
                break;

            case 'today':
            default:
                $period = TIME_PERIOD_TODAY;
                $reportTitle = 'Today\'s Report';
                break;
        }

        $statistics = new Statistics($this->_siteID);
        $submissionJobOrdersRS = $statistics->getSubmissionJobOrders($period);

        foreach ($submissionJobOrdersRS as $rowIndex => $submissionJobOrdersData)
        {
            /* Querys inside loops are bad, but I don't think there is any avoiding this. */
            $submissionJobOrdersRS[$rowIndex]['submissionsRS'] = $statistics->getSubmissionsByJobOrder(
                $period, $submissionJobOrdersData['jobOrderID'], $this->_siteID
            );
        }

        if (!eval(Hooks::get('REPORTS_SHOW_SUBMISSION'))) return;

        $this->_template->assign('reportTitle', $reportTitle);
        $this->_template->assign('submissionJobOrdersRS', $submissionJobOrdersRS);
        $this->_template->display('./modules/reports/SubmissionReport.tpl');
    }


    private function showSubmitReport()
    {
        //FIXME: getTrimmedInput
        if (isset($_GET['period']) && !empty($_GET['period']))
        {
            $period = $_GET['period'];
        }
        else
        {
            $period = '';
        }


        switch ($period)
        {
            case 'yesterday':
                $period = TIME_PERIOD_YESTERDAY;
                $reportTitle = 'Yesterday\'s Report';
                break;

            case 'thisWeek':
                $period = TIME_PERIOD_THISWEEK;
                $reportTitle = 'This Week\'s Report';
                break;

            case 'lastWeek':
                $period = TIME_PERIOD_LASTWEEK;
                $reportTitle = 'Last Week\'s Report';
                break;

            case 'thisMonth':
                $period = TIME_PERIOD_THISMONTH;
                $reportTitle = 'This Month\'s Report';
                break;

            case 'lastMonth':
                $period = TIME_PERIOD_LASTMONTH;
                $reportTitle = 'Last Month\'s Report';
                break;

            case 'thisQuarter':
                $period = TIME_PERIOD_THISQUARTER;
                $reportTitle = 'This Quarter\'s Report';
                break;

            case 'lastQuarter':
                $period = TIME_PERIOD_LASTQUARTER;
                $reportTitle = 'Last Quarter\'s Report';
                break;
                
            case 'thisYear':
                $period = TIME_PERIOD_THISYEAR;
                $reportTitle = 'This Year\'s Report';
                break;

            case 'lastYear':
                $period = TIME_PERIOD_LASTYEAR;
                $reportTitle = 'Last Year\'s Report';
                break;

            case 'toDate':
                $period = TIME_PERIOD_TODATE;
                $reportTitle = 'To Date Report';
                break;

            case 'today':
            default:
                $period = TIME_PERIOD_TODAY;
                $reportTitle = 'Today\'s Report';
                break;
        }

        $statistics = new Statistics($this->_siteID);
        $submitRS = $statistics->getSubmitReport($period);

        if (!eval(Hooks::get('REPORTS_SHOW_SUBMISSION'))) return;

        $this->_template->assign('reportTitle', $reportTitle);
        $this->_template->assign('submitRS', $submitRS);
        $this->_template->display('./modules/reports/SubmitReport.tpl');
    }

    private function showPlacementReport()
    {
        //FIXME: getTrimmedInput
        if (isset($_GET['period']) && !empty($_GET['period']))
        {
            $period = $_GET['period'];
        }
        else
        {
            $period = '';
        }


        switch ($period)
        {
            case 'yesterday':
                $period = TIME_PERIOD_YESTERDAY;
                $reportTitle = 'Yesterday\'s Report';
                break;

            case 'thisWeek':
                $period = TIME_PERIOD_THISWEEK;
                $reportTitle = 'This Week\'s Report';
                break;

            case 'lastWeek':
                $period = TIME_PERIOD_LASTWEEK;
                $reportTitle = 'Last Week\'s Report';
                break;

            case 'thisMonth':
                $period = TIME_PERIOD_THISMONTH;
                $reportTitle = 'This Month\'s Report';
                break;

            case 'lastMonth':
                $period = TIME_PERIOD_LASTMONTH;
                $reportTitle = 'Last Month\'s Report';
                break;

            case 'thisQuarter':
                $period = TIME_PERIOD_THISQUARTER;
                $reportTitle = 'This Quarter\'s Report';
                break;

            case 'lastQuarter':
                $period = TIME_PERIOD_LASTQUARTER;
                $reportTitle = 'Last Quarter\'s Report';
                break;
                
            case 'thisYear':
                $period = TIME_PERIOD_THISYEAR;
                $reportTitle = 'This Year\'s Report';
                break;

            case 'lastYear':
                $period = TIME_PERIOD_LASTYEAR;
                $reportTitle = 'Last Year\'s Report';
                break;

            case 'toDate':
                $period = TIME_PERIOD_TODATE;
                $reportTitle = 'To Date Report';
                break;

            case 'today':
            default:
                $period = TIME_PERIOD_TODAY;
                $reportTitle = 'Today\'s Report';
                break;
        }

        $statistics = new Statistics($this->_siteID);
        $placementsJobOrdersRS = $statistics->getPlacementsJobOrders($period);

        foreach ($placementsJobOrdersRS as $rowIndex => $placementsJobOrdersData)
        {
            /* Querys inside loops are bad, but I don't think there is any avoiding this. */
            $placementsJobOrdersRS[$rowIndex]['placementsRS'] = $statistics->getPlacementsByJobOrder(
                $period, $placementsJobOrdersData['jobOrderID'], $this->_siteID
            );
        }

        if (!eval(Hooks::get('REPORTS_SHOW_SUBMISSION'))) return;

        $this->_template->assign('reportTitle', $reportTitle);
        $this->_template->assign('placementsJobOrdersRS', $placementsJobOrdersRS);
        $this->_template->display('./modules/reports/PlacedReport.tpl');
    }

    private function showFunctionReport()
    {
        //FIXME: getTrimmedInput
        if (isset($_GET['period']) && !empty($_GET['period']))
        {
            $period = $_GET['period'];
        }
        else
        {
            $period = '';
        }

        switch ($period)
        {
            case 'today':
            default:
                $period = TIME_PERIOD_TODAY;
                $reportTitle = 'Today\'s Report';
                break;
        }

        $statistics = new Statistics($this->_siteID);
        $jobOrderFunctionsRS = $statistics->getJobOrderFunctions($period);

        foreach ($jobOrderFunctionsRS as $rowIndex => $jobOrderFunctionsData)
        {
            /* Querys inside loops are bad, but I don't think there is any avoiding this. */
            $jobOrderFunctionsRS[$rowIndex]['jobOrdersRS'] = $statistics->getJobOrdersByFunction(
                $jobOrderFunctionsData['jobOrderFunctions'], $this->_siteID
            );
        }

        if (!eval(Hooks::get('REPORTS_SHOW_FUNCTIONS'))) return;

        $this->_template->assign('reportTitle', $reportTitle);
        $this->_template->assign('jobOrderFunctionsRS', $jobOrderFunctionsRS);
        $this->_template->display('./modules/reports/FunctionReport.tpl');
    }

    private function showCompaniesReport()
    {
        //FIXME: getTrimmedInput
        if (isset($_GET['period']) && !empty($_GET['period']))
        {
            $period = $_GET['period'];
        }
        else
        {
            $period = '';
        }

        switch ($period)
        {
            case 'today':
            default:
                $period = TIME_PERIOD_TODAY;
                $reportTitle = 'Today\'s Report';
                break;
        }

        $statistics = new Statistics($this->_siteID);
        $companiesRS = $statistics->getCompanies($period);

        if (!eval(Hooks::get('REPORTS_SHOW_COMPANY'))) return;

        $this->_template->assign('reportTitle', $reportTitle);
        $this->_template->assign('companiesRS', $companiesRS);
        $this->_template->display('./modules/reports/CompaniesReport.tpl');
    }

    private function showOfferReport()
    {
        //FIXME: getTrimmedInput
        if (isset($_GET['period']) && !empty($_GET['period']))
        {
            $period = $_GET['period'];
        }
        else
        {
            $period = '';
        }


        switch ($period)
        {
            case 'yesterday':
                $period = TIME_PERIOD_YESTERDAY;
                $reportTitle = 'Yesterday\'s Report';
                break;

            case 'thisWeek':
                $period = TIME_PERIOD_THISWEEK;
                $reportTitle = 'This Week\'s Report';
                break;

            case 'lastWeek':
                $period = TIME_PERIOD_LASTWEEK;
                $reportTitle = 'Last Week\'s Report';
                break;

            case 'thisMonth':
                $period = TIME_PERIOD_THISMONTH;
                $reportTitle = 'This Month\'s Report';
                break;

            case 'lastMonth':
                $period = TIME_PERIOD_LASTMONTH;
                $reportTitle = 'Last Month\'s Report';
                break;

            case 'thisQuarter':
                $period = TIME_PERIOD_THISQUARTER;
                $reportTitle = 'This Quarter\'s Report';
                break;

            case 'lastQuarter':
                $period = TIME_PERIOD_LASTQUARTER;
                $reportTitle = 'Last Quarter\'s Report';
                break;
                
            case 'thisYear':
                $period = TIME_PERIOD_THISYEAR;
                $reportTitle = 'This Year\'s Report';
                break;

            case 'lastYear':
                $period = TIME_PERIOD_LASTYEAR;
                $reportTitle = 'Last Year\'s Report';
                break;

            case 'toDate':
                $period = TIME_PERIOD_TODATE;
                $reportTitle = 'To Date Report';
                break;

            case 'today':
            default:
                $period = TIME_PERIOD_TODAY;
                $reportTitle = 'Today\'s Report';
                break;
        }

        $statistics = new Statistics($this->_siteID);
        $offersJobOrdersRS = $statistics->getOffersJobOrders($period);

        foreach ($offersJobOrdersRS as $rowIndex => $offersJobOrdersData)
        {
            /* Querys inside loops are bad, but I don't think there is any avoiding this. */
            $offersJobOrdersRS[$rowIndex]['offersRS'] = $statistics->getOffersByJobOrder(
                $period, $offersJobOrdersData['jobOrderID'], $this->_siteID
            );
        }

        if (!eval(Hooks::get('REPORTS_SHOW_SUBMISSION'))) return;

        $this->_template->assign('reportTitle', $reportTitle);
        $this->_template->assign('offersJobOrdersRS', $offersJobOrdersRS);
        $this->_template->display('./modules/reports/OfferedReport.tpl');
    }
    
    private function showUserReport()
    {
        //FIXME: getTrimmedInput
        if (isset($_GET['period']) && !empty($_GET['period']))
        {
            $periodString = $_GET['period'];
        }
        else
        {
            $periodString = '';
        }
        $period = '';

        switch ($periodString)
        {
            case 'yesterday':
                $period = TIME_PERIOD_YESTERDAY;
                $reportTitle = 'Yesterday\'s Report';
                break;

            case 'thisWeek':
                $period = TIME_PERIOD_THISWEEK;
                $reportTitle = 'This Week\'s Report';
                break;

            case 'lastWeek':
                $period = TIME_PERIOD_LASTWEEK;
                $reportTitle = 'Last Week\'s Report';
                break;

            case 'thisMonth':
                $period = TIME_PERIOD_THISMONTH;
                $reportTitle = 'This Month\'s Report';
                break;

            case 'lastMonth':
                $period = TIME_PERIOD_LASTMONTH;
                $reportTitle = 'Last Month\'s Report';
                break;

            case 'thisQuarter':
                $period = TIME_PERIOD_THISQUARTER;
                $reportTitle = 'This Quarter\'s Report';
                break;

            case 'lastQuarter':
                $period = TIME_PERIOD_LASTQUARTER;
                $reportTitle = 'Last Quarter\'s Report';
                break;
                
            case 'thisYear':
                $period = TIME_PERIOD_THISYEAR;
                $reportTitle = 'This Year\'s Report';
                break;

            case 'lastYear':
                $period = TIME_PERIOD_LASTYEAR;
                $reportTitle = 'Last Year\'s Report';
                break;

            case 'toDate':
                $period = TIME_PERIOD_TODATE;
                $reportTitle = 'To Date Report';
                break;

            case 'today':
            default:
                $period = TIME_PERIOD_TODAY;
                $reportTitle = 'Today\'s Report';
                break;
        }

        $statistics = new Statistics($this->_siteID);
        $UsersRS = $statistics->getReportUsers($period);

        foreach ($UsersRS as $rowIndex => $UsersData)
        {
            /* Querys inside loops are bad, but I don't think there is any avoiding this. */
            $UsersRS[$rowIndex]['reportRS'] = $statistics->getReportByUser(
                $period, $UsersData['userID']
            );
            if($period == TIME_PERIOD_TODAY)
            {
                $UsersRS[$rowIndex]['currentReportRS'] = $statistics->getCurrentReportByUser(
                    $UsersData['userID']
                );
            }
        }
        
        if (!eval(Hooks::get('REPORTS_SHOW_USERS_REPORT'))) return;

        $this->_template->assign('reportTitle', $reportTitle);
        $this->_template->assign('period', $periodString);
        $this->_template->assign('UsersRS', $UsersRS);
        $this->_template->display('./modules/reports/UsersReport.tpl');
    }

    private function showUserReportByUser()
    {
        //FIXME: getTrimmedInput
        if (isset($_GET['period']) && !empty($_GET['period']))
        {
            $periodString = $_GET['period'];
        }
        else
        {
            $periodString = '';
        }
        $period = '';
        
        //FIXME: getTrimmedInput
        if (isset($_GET['userID']) && !empty($_GET['userID']))
        {
            $userID = $_GET['userID'];
        }
        else
        {
            $userID = '';
        }
        
        $subdaystart = 0;
        $subdayend = 0;
        $byday = 0;
        $reportTitle = '';
        switch ($periodString)
        {
            case 'yesterday':
                $byday = 1;
                $subdaystart = -1; // php weekday 0-6, sql week 1-7
                $subdayend = $subdaystart;
                $period = TIME_PERIOD_YESTERDAY;
                $reportTitle = 'Yesterday\'s Report';
                break;

            case 'thisWeek':
                $byday = 1;
                $subdaystart = -date('w');
                $subdayend = 0; // php weekday 0-6, sql week 1-7
                $period = TIME_PERIOD_THISWEEK;
                $reportTitle = 'This Week\'s Report';
                break;

            case 'lastWeek':
                $byday = 1;
                $subdaystart = -6-1-date('w'); // php weekday 0-6, sql week 1-7
                $subdayend = -date('w')-1;
                $period = TIME_PERIOD_LASTWEEK;
                $reportTitle = 'Last Week\'s Report';
                break;

            case 'thisMonth':
                $period = TIME_PERIOD_THISMONTH;
                $reportTitle = 'This Month\'s Report';
                break;

            case 'lastMonth':
                $period = TIME_PERIOD_LASTMONTH;
                $reportTitle = 'Last Month\'s Report';
                break;

            case 'thisQuarter':
                $period = TIME_PERIOD_THISQUARTER;
                $reportTitle = 'This Quarter\'s Report';
                break;

            case 'lastQuarter':
                $period = TIME_PERIOD_LASTQUARTER;
                $reportTitle = 'Last Quarter\'s Report';
                break;
                
            case 'thisYear':
                $period = TIME_PERIOD_THISYEAR;
                $reportTitle = 'This Year\'s Report';
                break;

            case 'lastYear':
                $period = TIME_PERIOD_LASTYEAR;
                $reportTitle = 'Last Year\'s Report';
                break;

            case 'toDate':
                $period = TIME_PERIOD_TODATE;
                $reportTitle = 'To Date Report';
                break;
            case 'today':
            default:
                $byday = 1;
                $subdaystart = 0; // php weekday 0-6, sql week 1-7
                $subdayend = 0;
                $period = TIME_PERIOD_TODAY;
                $reportTitle = 'Today\'s Report';
                break;
        }

        $UserDateRS = '';
        $statistics = new Statistics($this->_siteID);
        
        if($byday)
        {
            $daycount = 0;
            $dayofweekstring = ['SUN', 'MON', 'TUE', 'WED', 'THUR', 'FRI', 'SAT'];
            for(;($daycount + $subdaystart) <= $subdayend;$daycount++)
            {
                $date = date('m-d-y', strtotime(($subdaystart+$daycount).' days'));
                $weekday = date('w', strtotime(($subdaystart+$daycount).' days'));
                $UserDateRS[$daycount]['date'] = $date . ' (' . $dayofweekstring[$weekday] . ')';

                /* Querys inside loops are bad, but I don't think there is any avoiding this. */
                $UserDateRS[$daycount]['reportRS'] = $statistics->getReportByUser(
                    $period, $userID, (string)($subdaystart+$daycount)
                );
            }
        }
        else
        {
            $UserDateRS[0]['date'] = $reportTitle;

            /* Querys inside loops are bad, but I don't think there is any avoiding this. */
            $UserDateRS[0]['reportRS'] = $statistics->getReportByUser(
                $period, $userID
            );
        }
        
        $submitRS = $statistics->getSubmitReport($period, $userID);


        $activityEntries = new ActivityEntries($this->_siteID);
        $activityRS = $activityEntries->getAllForReportByUser($period, $userID);
        if (!empty($activityRS))
        {
            foreach ($activityRS as $rowIndex => $row)
            {
                if (empty($activityRS[$rowIndex]['notes']))
                {
                    $activityRS[$rowIndex]['notes'] = '(No Notes)';
                }

                if (empty($activityRS[$rowIndex]['regarding']))
                {
                    $activityRS[$rowIndex]['regarding'] = 'General';
                }

                $activityRS[$rowIndex]['enteredByAbbrName'] = StringUtility::makeInitialName(
                    $activityRS[$rowIndex]['enteredByFirstName'],
                    $activityRS[$rowIndex]['enteredByLastName'],
                    false,
                    LAST_NAME_MAXLEN
                );
            }
        }
        
        
        if (!eval(Hooks::get('REPORTS_SHOW_USERS_REPORT_BY_USER'))) return;

        $this->_template->assign('active', $this);
        $this->_template->assign('reportTitle', $reportTitle);
        $this->_template->assign('period', $periodString);
        $this->_template->assign('activityRS', $activityRS);
        $this->_template->assign('UserDateRS', $UserDateRS);
        $this->_template->assign('submitRS', $submitRS);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());
        $this->_template->display('./modules/reports/UsersReportByUser.tpl');
    }

    private function customizeJobOrderReport()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $jobOrderID = $_GET['jobOrderID'];

        $siteName = $_SESSION['CATS']->getSiteName();


        $statistics = new Statistics($this->_siteID);
        $data = $statistics->getJobOrderReport($jobOrderID);
        $dataThisMonth = $statistics->getPeriodJobOrderReport($jobOrderID, TIME_PERIOD_THISMONTH);
        $dataLastMonth = $statistics->getPeriodJobOrderReport($jobOrderID, TIME_PERIOD_LASTMONTH);

        /* Bail out if we got an empty result set. */
        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'The specified job order ID could not be found.');
        }

        $reportParameters['siteName'] = $siteName;
        $reportParameters['companyName'] = $data['companyName'];
        $reportParameters['jobOrderName'] = $data['title'];
        $reportParameters['accountManager'] = $data['ownerFullName'];
        $reportParameters['recruiter'] = $data['recruiterFullName'];

        $reportParameters['periodLine'] = sprintf(
            '%s - %s',
            strtok($data['dateCreated'], ' '),
            DateUtility::getAdjustedDate('m-d-y')
        );

        $reportParameters['dataSet1'] = $data['pipeline'];
        $reportParameters['dataSet2'] = $data['submitted'];
        $reportParameters['dataSet3'] = $data['pipelineInterving'];
        $reportParameters['dataSet4'] = $data['pipelinePlaced'];

        $dataSet = array(
            $reportParameters['dataSet4'],
            $reportParameters['dataSet3'],
            $reportParameters['dataSet2'],
            $reportParameters['dataSet1']
        );

        $reportParameters['dataSetLastMonth1'] = $dataLastMonth['pipeline'];
        $reportParameters['dataSetLastMonth2'] = $dataLastMonth['submitted'];
        $reportParameters['dataSetLastMonth3'] = $dataLastMonth['pipelineInterving'];
        $reportParameters['dataSetLastMonth4'] = $dataLastMonth['pipelinePlaced'];

        $dataSetLastMonth = array(
            $reportParameters['dataSetLastMonth4'],
            $reportParameters['dataSetLastMonth3'],
            $reportParameters['dataSetLastMonth2'],
            $reportParameters['dataSetLastMonth1']
        );

        $reportParameters['dataSetThisMonth1'] = $dataThisMonth['pipeline'];
        $reportParameters['dataSetThisMonth2'] = $dataThisMonth['submitted'];
        $reportParameters['dataSetThisMonth3'] = $dataThisMonth['pipelineInterving'];
        $reportParameters['dataSetThisMonth4'] = $dataThisMonth['pipelinePlaced'];

        $dataSetThisMonth = array(
            $reportParameters['dataSetThisMonth4'],
            $reportParameters['dataSetThisMonth3'],
            $reportParameters['dataSetThisMonth2'],
            $reportParameters['dataSetThisMonth1']
        );

        $this->_template->assign('reportParameters', $reportParameters);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->display('./modules/reports/JobOrderReport.tpl');
    }

    private function customizeEEOReport()
    {
        $this->_template->assign('modePeriod', 'all');
        $this->_template->assign('modeStatus', 'all');
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->display('./modules/reports/EEOReport.tpl');
    }

    private function generateJobOrderReportPDF()
    {
        /* E_STRICT doesn't like FPDF. */
        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~ E_STRICT);
        include_once('./lib/fpdf/fpdf.php');
        error_reporting($errorReporting);

        // FIXME: Hook?
        $isASP = $_SESSION['CATS']->isASP();

        $unixName = $_SESSION['CATS']->getUnixName();

        $siteName       = $this->getTrimmedInput('siteName', $_GET);
        $companyName    = $this->getTrimmedInput('companyName', $_GET);
        $jobOrderName   = $this->getTrimmedInput('jobOrderName', $_GET);
        $periodLine     = $this->getTrimmedInput('periodLine', $_GET);
        $accountManager = $this->getTrimmedInput('accountManager', $_GET);
        $recruiter      = $this->getTrimmedInput('recruiter', $_GET);
        $notes          = $this->getTrimmedInput('notes', $_GET);

        if (isset($_GET['dataSet']))
        {
            $dataSet = $_GET['dataSet'];
            $dataSet = explode(',', $dataSet);
        }
        else
        {
            $dataSet = array(4, 3, 2, 1);
        }


        /* PDF Font Face. */
        // FIXME: Customizable.
        $fontFace = 'Arial';

        $pdf = new FPDF();
        $pdf->AddPage();

        if (!eval(Hooks::get('REPORTS_CUSTOMIZE_JO_REPORT_PRE'))) return;

        if ($isASP && $unixName == 'cognizo')
        {
            /* TODO: MAKE THIS CUSTOMIZABLE FOR EVERYONE. */
            $pdf->SetFont($fontFace, 'B', 10);
            $pdf->Image('images/cognizo-logo.jpg', 130, 10, 59, 20);
            $pdf->SetXY(129,27);
            $pdf->Write(5, 'Information Technology Consulting');
        }

        $pdf->SetXY(25, 35);
        $pdf->SetFont($fontFace, 'BU', 14);
        $pdf->Write(5, "Recruiting Summary Report\n");

        $pdf->SetFont($fontFace, '', 10);
        $pdf->SetX(25);
        $pdf->Write(5, DateUtility::getAdjustedDate('l, F d, Y') . "\n\n\n");

        $pdf->SetFont($fontFace, 'B', 10);
        $pdf->SetX(25);
        $pdf->Write(5, 'Company: '. $companyName . "\n");

        $pdf->SetFont($fontFace, '', 10);
        $pdf->SetX(25);
        $pdf->Write(5, 'Position: ' . $jobOrderName . "\n\n");

        $pdf->SetFont($fontFace, '', 10);
        $pdf->SetX(25);
        $pdf->Write(5, 'Period: ' . $periodLine . "\n\n");

        $pdf->SetFont($fontFace, '', 10);
        $pdf->SetX(25);
        $pdf->Write(5, 'Account Manager: ' . $accountManager . "\n");

        $pdf->SetFont($fontFace, '', 10);
        $pdf->SetX(25);
        $pdf->Write(5, 'Recruiter: ' . $recruiter . "\n");

        /* Note that the server is not logged in when getting this file from
         * itself.
         */
        // FIXME: Pass session cookie in URL? Use cURL and send a cookie? I
        //        really don't like this... There has to be a way.
        // FIXME: "could not make seekable" - http://demo.catsone.net/index.php?m=graphs&a=jobOrderReportGraph&data=%2C%2C%2C
        //        in /usr/local/www/catsone.net/data/lib/fpdf/fpdf.php on line 1500
        $URI = CATSUtility::getAbsoluteURI(
            CATSUtility::getIndexName()
            . '?m=graphs&a=jobOrderReportGraph&data='
            . urlencode(implode(',', $dataSet))
        );

        $pdf->Image($URI, 70, 95, 80, 80, 'jpg');

        $pdf->SetXY(25,180);
        $pdf->SetFont($fontFace, '', 10);
        $pdf->Write(5, 'Total Candidates ');
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Write(5, 'Screened');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Write(5, ' by ' . $siteName . ": \n\n");

        $pdf->SetX(25);
        $pdf->SetFont($fontFace, '', 10);
        $pdf->Write(5, 'Total Candidates ');
        $pdf->SetTextColor(0, 125, 0);
        $pdf->Write(5, 'Submitted');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Write(5, ' to ' . $companyName . ": \n\n");

        $pdf->SetX(25);
        $pdf->SetFont($fontFace, '', 10);
        $pdf->Write(5, 'Total Candidates ');
        $pdf->SetTextColor(0, 0, 255);
        $pdf->Write(5, 'Interviewed');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Write(5, ' by ' . $companyName . ": \n\n");

        $pdf->SetX(25);
        $pdf->SetFont($fontFace, '', 10);
        $pdf->Write(5, 'Total Candidates ');
        $pdf->SetTextColor(255, 75, 0);
        $pdf->Write(5, 'Placed');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Write(5, ' at ' . $companyName . ": \n\n\n");

        if ($notes != '')
        {
            $pdf->SetX(25);
            $pdf->SetFont($fontFace, '', 10);
            $pdf->Write(5, "Notes:\n");

            $len = strlen($notes);
            $maxChars = 70;

            $pdf->SetLeftMargin(25);
            $pdf->SetRightMargin(25);
            $pdf->SetX(25);
            $pdf->Write(5, $notes . "\n");
        }

        $pdf->SetXY(165, 180);
        $pdf->SetFont($fontFace, 'B', 10);
        $pdf->Write(5, $dataSet[0] . "\n\n");
        $pdf->SetX(165);
        $pdf->Write(5, $dataSet[1] . "\n\n");
        $pdf->SetX(165);
        $pdf->Write(5, $dataSet[2] . "\n\n");
        $pdf->SetX(165);
        $pdf->Write(5, $dataSet[3] . "\n\n");

        $pdf->Rect(3, 6, 204, 285);

        if (!eval(Hooks::get('REPORTS_CUSTOMIZE_JO_REPORT_POST'))) return;

        $pdf->Output();
        die();
    }

    function generateEEOReportPreview()
    {
        $modePeriod = $this->getTrimmedInput('period', $_GET);
        $modeStatus = $this->getTrimmedInput('status', $_GET);

        $statistics = new Statistics($this->_siteID);
        $EEOReportStatistics = $statistics->getEEOReport($modePeriod, $modeStatus);

        //print_r($EEOReportStatistics);

        switch ($modePeriod)
        {
            case 'week':
                $labelPeriod = ' Last Week';
                break;

            case 'month':
                $labelPeriod = ' Last Month';
                break;

            default:
                $labelPeriod = '';
                break;
        }

        switch ($modeStatus)
        {
            case 'rejected':
                $labelStatus = ' Rejected';
                break;

            case 'placed':
                $labelStatus = ' Placed';
                break;

            default:
                $labelStatus = '';
                break;
        }

        /* Produce the URL to the ethic statistics graph. */
        $labels = array();
        $data = array();

        $rsEthnicStatistics = $EEOReportStatistics['rsEthnicStatistics'];

        foreach ($rsEthnicStatistics as $index => $line)
        {
            $labels[] = $line['EEOEthnicType'];
            $data[] = $line['numberOfCandidates'];
        }

        $urlEthnicGraph = CATSUtility::getAbsoluteURI(
            sprintf("%s?m=graphs&a=generic&title=%s&labels=%s&data=%s&width=%s&height=%s",
                CATSUtility::getIndexName(),
                urlencode('Number of Candidates'.$labelStatus.' by Ethnic Type'.$labelPeriod),
                urlencode(implode(',', $labels)),
                urlencode(implode(',', $data)),
                400,
                240
            ));


        /* Produce the URL to the veteran status statistics graph. */
        $labels = array();
        $data = array();

        $rsVeteranStatistics = $EEOReportStatistics['rsVeteranStatistics'];

        foreach ($rsVeteranStatistics as $index => $line)
        {
            $labels[] = $line['EEOVeteranType'];
            $data[] = $line['numberOfCandidates'];
        }

        $urlVeteranGraph = CATSUtility::getAbsoluteURI(
            sprintf("%s?m=graphs&a=generic&title=%s&labels=%s&data=%s&width=%s&height=%s",
                CATSUtility::getIndexName(),
                urlencode('Number of Candidates'.$labelStatus.' by Veteran Status'.$labelPeriod),
                urlencode(implode(',', $labels)),
                urlencode(implode(',', $data)),
                400,
                240
            ));

        /* Produce the URL to the gender statistics graph. */
        $labels = array();
        $data = array();

        $rsGenderStatistics = $EEOReportStatistics['rsGenderStatistics'];

        $labels[] = 'Male ('.$rsGenderStatistics['numberOfCandidatesMale'].')';
        $data[] = $rsGenderStatistics['numberOfCandidatesMale'];

        $labels[] = 'Female ('.$rsGenderStatistics['numberOfCandidatesFemale'].')';
        $data[] = $rsGenderStatistics['numberOfCandidatesFemale'];

        $urlGenderGraph = CATSUtility::getAbsoluteURI(
            sprintf("%s?m=graphs&a=genericPie&title=%s&labels=%s&data=%s&width=%s&height=%s&legendOffset=%s",
                CATSUtility::getIndexName(),
                urlencode('Number of Candidates by Gender'),
                urlencode(implode(',', $labels)),
                urlencode(implode(',', $data)),
                320,
                300,
                1.575
            ));

        if ($rsGenderStatistics['numberOfCandidatesMale'] == 0 && $rsGenderStatistics['numberOfCandidatesFemale'] == 0)
        {
            $urlGenderGraph = "images/noDataByGender.png";
        }

        /* Produce the URL to the disability statistics graph. */
        $labels = array();
        $data = array();

        $rsDisabledStatistics = $EEOReportStatistics['rsDisabledStatistics'];

        $labels[] = 'Disabled ('.$rsDisabledStatistics['numberOfCandidatesDisabled'].')';
        $data[] = $rsDisabledStatistics['numberOfCandidatesDisabled'];

        $labels[] = 'Non Disabled ('.$rsDisabledStatistics['numberOfCandidatesNonDisabled'].')';
        $data[] = $rsDisabledStatistics['numberOfCandidatesNonDisabled'];

        $urlDisabilityGraph = CATSUtility::getAbsoluteURI(
            sprintf("%s?m=graphs&a=genericPie&title=%s&labels=%s&data=%s&width=%s&height=%s&legendOffset=%s",
                CATSUtility::getIndexName(),
                urlencode('Number of Candidates by Disability Status'),
                urlencode(implode(',', $labels)),
                urlencode(implode(',', $data)),
                320,
                300,
                1.575
            ));

        if ($rsDisabledStatistics['numberOfCandidatesNonDisabled'] == 0 && $rsDisabledStatistics['numberOfCandidatesDisabled'] == 0)
        {
            $urlDisabilityGraph = "images/noDataByDisability.png";
        }

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('EEOReportStatistics', $EEOReportStatistics);
        $this->_template->assign('urlEthnicGraph', $urlEthnicGraph);
        $this->_template->assign('urlVeteranGraph', $urlVeteranGraph);
        $this->_template->assign('urlGenderGraph', $urlGenderGraph);
        $this->_template->assign('urlDisabilityGraph', $urlDisabilityGraph);
        $this->_template->assign('modePeriod', $modePeriod);
        $this->_template->assign('modeStatus', $modeStatus);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->display('./modules/reports/EEOReport.tpl');
    }
}

?>
