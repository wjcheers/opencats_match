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

    <p class="note">User Reports</p>

    <table class="sortable" width="1225">
        <tr>
            <th align="left" nowrap="nowrap">User</th>
            <th align="left" nowrap="nowrap">Created Candidate</th>
            <th align="left" nowrap="nowrap">Modified Candidate</th>
            <th align="left" nowrap="nowrap">Submitted Candidate</th>
            <th align="left" nowrap="nowrap">Activity Count</th>
            <th align="left" nowrap="nowrap">Created Company</th>
            <th align="left" nowrap="nowrap">Created Contact</th>
        </tr>
        <?php foreach ($this->UsersRS as $rowNumber => $UserData): ?>
            <?php foreach ($UserData['reportRS'] as $rowNumber => $reportData): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td valign="top" align="left"><?php $this->_($UserData['ownerFullName']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['createdCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['modifiedCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['submittedCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['activityCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['companyCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['contactCount']) ?>&nbsp;</td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </table>
<?php TemplateUtility::printReportFooter(); ?>
