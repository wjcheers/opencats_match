<?php /* $Id: PlacedReport.tpl 2336 2007-04-14 22:01:51Z will $ */ ?>
<?php TemplateUtility::printHeader($this->reportTitle); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
    <table>
        <tr>
            <td width="3%">
                <img src="images/reports.gif" width="24" height="24" border="0" alt="Reports" style="margin-top: 3px;" />&nbsp;
            </td>
            <td><h2><?php $this->_($this->reportTitle); ?></h2></td>
        </tr>
    </table>

    <p class="note">Users Report</p>

    <table class="sortable" width="1225">
        <tr>
            <th align="left" nowrap="nowrap">User</th>
            <th align="left" nowrap="nowrap">Created(Phone/Email)</th>
            <th align="left" nowrap="nowrap">Modified CANDs</th>
            <th align="left" nowrap="nowrap">Submitted Person</th>
            <th align="left" nowrap="nowrap">Submitted CANDs</th>
            <th align="left" nowrap="nowrap">Interviewing CANDs</th>
            <th align="left" nowrap="nowrap">Offered CANDs</th>
            <th align="left" nowrap="nowrap">Placed CANDs</th>
            <th align="left" nowrap="nowrap">Activities Count</th>
            <!--
            <th align="left" nowrap="nowrap">Created Companies</th>
            <th align="left" nowrap="nowrap">Created Contacts</th>
            -->
        </tr>
        <?php foreach ($this->UsersRS as $rowNumber => $UserData): ?>
            <?php foreach ($UserData['reportRS'] as $rowNumber => $reportData): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td valign="top" align="left">
                        <?php if(($this->period == 'today') ||
                                 ($this->period == 'yesterday') ||
                                 ($this->period == 'thisWeek') ||
                                 ($this->period == 'lastWeek') ||
                                 ($this->period == 'thisMonth') ||
                                 ($this->period == 'lastMonth')): ?>
                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showUserReportByUser&amp;period=<?php $this->_($this->period) ?>&amp;userID=<?php $this->_($UserData['userID']) ?>">
                                <?php $this->_($UserData['ownerFullName']) ?>
                            </a>
                        <?php else: ?>
                            <?php $this->_($UserData['ownerFullName']) ?>
                        <?php endif; ?>
                    </td>
                    <td valign="top" align="left"><?php $this->_($reportData['createdCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['modifiedCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['personSubmittedCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['submittedCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['interviewingCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['offeredCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['placedCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['activityCount']) ?>&nbsp;</td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </table>

    <?php if($this->reportTitle == 'Today\'s Report'): ?>
    <p class="note">Current Pipeline Status</p>

    <table class="sortable" width="1225">
        <tr>
            <th align="left" nowrap="nowrap">User</th>
            <th align="left" nowrap="nowrap">Submitted Candidates</th>
            <th align="left" nowrap="nowrap">Interviewing Candidates</th>
            <th align="left" nowrap="nowrap">Offered Candidates</th>
        </tr>
        <?php foreach ($this->UsersRS as $rowNumber => $UserData): ?>
            <?php foreach ($UserData['currentReportRS'] as $rowNumber => $reportData): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td valign="top" align="left"><?php $this->_($UserData['ownerFullName']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['submittedCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['interviewingCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['offeredCount']) ?>&nbsp;</td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
<?php TemplateUtility::printReportFooter(); ?>
