<?php /* $Id: Reports.tpl 3304 2007-10-25 17:31:55Z will $ */ ?>
<?php TemplateUtility::printHeader('Reports'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/reports.gif" width="24" height="24" border="0" alt="Reports" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Reports</h2></td>
                </tr>
            </table>

            <p class="note">Reports</p>

            <table border="0" width="1225">
                <tr>
                    <td width="420">
                        <table class="statisticsTable" width="400">
                            <tr>
                                <th align="left">Today</th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Job Orders</td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersToday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">New Candidates</td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesToday']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Companies</td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesToday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPipelineReport&amp;period=today" target="_blank">New Pipelines</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['pipelinesToday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=today" target="_blank">New Submissions</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsToday']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=today" target="_blank">New Placements</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsToday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">New Contacts</td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsToday']); ?>&nbsp;&nbsp;</td>
                            </tr>
							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmitReport&amp;period=today" target="_blank">Submit Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showUserReport&amp;period=today" target="_blank">User Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showCompanyReport&amp;period=today" target="_blank">Company Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showFunctionReport&amp;period=today" target="_blank">Function Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>

                    <td width="420">
                        <table class="statisticsTable" width="400">
                            <tr>
                                <th align="left">Yesterday</th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Job Orders</td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersYesterday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">New Candidates</td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesYesterday']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Companies</td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesYesterday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPipelineReport&amp;period=yesterday" target="_blank">New Pipelines</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['pipelinesYesterday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=yesterday" target="_blank">New Submissions</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsYesterday']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=yesterday" target="_blank">New Placements</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsYesterday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">New Contacts</td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsYesterday']); ?>&nbsp;&nbsp;</td>
                            </tr>
							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmitReport&amp;period=yesterday" target="_blank">Submit Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showUserReport&amp;period=yesterday" target="_blank">User Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showCompanyReport&amp;period=yesterday" target="_blank">Company Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>

                    <td width="420">
                        <table class="statisticsTable" width="400">
                            <tr>
                                <th align="left">This Week</th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Job Orders</td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersThisWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">New Candidates</td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesThisWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Companies</td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesThisWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPipelineReport&amp;period=thisWeek" target="_blank">New Pipelines</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['pipelinesThisWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=thisWeek" target="_blank">New Submissions</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsThisWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=thisWeek" target="_blank">New Placements</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsThisWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">New Contacts</td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsThisWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmitReport&amp;period=thisWeek" target="_blank">Submit Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showUserReport&amp;period=thisWeek" target="_blank">User Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showCompanyReport&amp;period=thisWeek" target="_blank">Company Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>

                    <td>
                        <table class="statisticsTable" width="400">
                            <tr>
                                <th align="left">Last Week</th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Job Orders</td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersLastWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">New Candidates</td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesLastWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Companies</td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesLastWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPipelineReport&amp;period=lastWeek" target="_blank">New Pipelines</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['pipelinesLastWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=lastWeek" target="_blank">New Submissions</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsLastWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=lastWeek" target="_blank">New Placements</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsLastWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">New Contacts</td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsLastWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmitReport&amp;period=lastWeek" target="_blank">Submit Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showUserReport&amp;period=lastWeek" target="_blank">User Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showCompanyReport&amp;period=lastWeek" target="_blank">Company Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>

                    <td width="420" valign="top">
                        <table class="statisticsTable" width="400">
                            <tr>
                                <th align="left">This Month</th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Job Orders</td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersThisMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">New Candidates</td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesThisMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Companies</td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesThisMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPipelineReport&amp;period=thisMonth" target="_blank">New Pipelines</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['pipelinesThisMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=thisMonth" target="_blank">New Submissions</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsThisMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=thisMonth" target="_blank">New Placements</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsThisMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">New Contacts</td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsThisMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmitReport&amp;period=thisMonth" target="_blank">Submit Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showUserReport&amp;period=thisMonth" target="_blank">User Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showCompanyReport&amp;period=thisMonth" target="_blank">Company Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>

                    <td width="420" valign="top">
                        <table class="statisticsTable" width="400">
                            <tr>
                                <th align="left">Last Month</th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Job Orders</td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersLastMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">New Candidates</td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesLastMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Companies</td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesLastMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPipelineReport&amp;period=lastMonth" target="_blank">New Pipelines</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['pipelinesLastMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=lastMonth" target="_blank">New Submissions</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsLastMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=lastMonth" target="_blank">New Placements</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsLastMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">New Contacts</td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsLastMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmitReport&amp;period=lastMonth" target="_blank">Submit Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showUserReport&amp;period=lastMonth" target="_blank">User Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showCompanyReport&amp;period=lastMonth" target="_blank">Company Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>

                    <td width="420" valign="top">
                        <table class="statisticsTable" width="400">
                            <tr>
                                <th align="left">This Year</th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Job Orders</td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersThisYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">New Candidates</td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesThisYear']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Companies</td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesThisYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPipelineReport&amp;period=thisYear" target="_blank">New Pipelines</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['pipelinesThisYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=thisYear" target="_blank">New Submissions</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsThisYear']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=thisYear" target="_blank">New Placements</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsThisYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">New Contacts</td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsThisYear']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmitReport&amp;period=thisYear" target="_blank">Submit Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showUserReport&amp;period=thisYear" target="_blank">User Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showCompanyReport&amp;period=thisYear" target="_blank">Company Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>

                    <td width="420" valign="top">
                        <table class="statisticsTable" width="400">
                            <tr>
                                <th align="left">Last Year</th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Job Orders</td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersLastYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">New Candidates</td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesLastYear']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Companies</td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesLastYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPipelineReport&amp;period=lastYear" target="_blank">New Pipelines</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['pipelinesLastYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=lastYear" target="_blank">New Submissions</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsLastYear']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=lastYear" target="_blank">New Placements</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsLastYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">New Contacts</td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsLastYear']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmitReport&amp;period=lastYear" target="_blank">Submit Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showUserReport&amp;period=lastYear" target="_blank">User Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showCompanyReport&amp;period=lastYear" target="_blank">Company Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>

                    <td width="420" valign="top">
                        <!--
                        <table class="statisticsTable" width="400">
                            <tr>
                                <th align="left">To Date</th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">Total Companies</td>
                                <td align="right"><?php $this->_($this->statisticsData['totalCompanies']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">Total Candidates</td>
                                <td align="right"><?php $this->_($this->statisticsData['totalCandidates']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">Total Job Orders</td>
                                <td align="right"><?php $this->_($this->statisticsData['totalJobOrders']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=toDate" target="_blank">Total Submissions</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['totalSubmissions']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=toDate" target="_blank">Total Placements</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['totalPlacements']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">Total Contacts</td>
                                <td align="right"><?php $this->_($this->statisticsData['totalContacts']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            
							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showOfferReport&amp;period=toDate" target="_blank">Total Offers</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['totalOffers']); ?>&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                        -->
                    </td>
                </tr>
                <tr>

                    <td width="420" valign="top">
                        <table class="statisticsTable" width="400">
                            <tr>
                                <th align="left">This Quarter</th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Job Orders</td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersThisQuarter']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">New Candidates</td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesThisQuarter']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Companies</td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesThisQuarter']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPipelineReport&amp;period=thisQuarter" target="_blank">New Pipelines</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['pipelinesThisQuarter']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=thisQuarter" target="_blank">New Submissions</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsThisQuarter']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=thisQuarter" target="_blank">New Placements</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsThisQuarter']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">New Contacts</td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsThisQuarter']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmitReport&amp;period=thisQuarter" target="_blank">Submit Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showUserReport&amp;period=thisQuarter" target="_blank">User Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showCompanyReport&amp;period=thisQuarter" target="_blank">Company Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>

                    <td width="420" valign="top">
                        <table class="statisticsTable" width="400">
                            <tr>
                                <th align="left">Last Quarter</th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Job Orders</td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersLastQuarter']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">New Candidates</td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesLastQuarter']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Companies</td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesLastQuarter']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPipelineReport&amp;period=lastQuarter" target="_blank">New Pipelines</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['pipelinesLastQuarter']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=lastQuarter" target="_blank">New Submissions</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsLastQuarter']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=lastQuarter" target="_blank">New Placements</a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsLastQuarter']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">New Contacts</td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsLastQuarter']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmitReport&amp;period=lastQuarter" target="_blank">Submit Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showUserReport&amp;period=lastQuarter" target="_blank">User Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
							<tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showCompanyReport&amp;period=lastQuarter" target="_blank">Company Reports</a>
                                </td>
                                <td align="right">☆&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>

                </tr>
            </table>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
