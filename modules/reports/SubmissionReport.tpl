<?php /* $Id: SubmissionReport.tpl 1948 2007-02-23 09:49:27Z will $ */ ?>
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

    <p class="note">Submissions</p>

    <?php foreach ($this->submissionJobOrdersRS as $rowNumber => $submissionJobOrdersData): ?>
        <span style="font: normal normal bold 13px/130% Arial, Tahoma, sans-serif;"><a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($submissionJobOrdersData['jobOrderID']) ?>" target="_blank"><?php $this->_($submissionJobOrdersData['title']) ?></a> at <?php $this->_($submissionJobOrdersData['companyName']) ?> (<?php $this->_($submissionJobOrdersData['ownerFullName']) ?>)</span>
        <br />
        <table class="sortable" width="1225">
            <tr>
                <th align="left" nowrap="nowrap" width="25%">First Name</th>
                <th align="left" nowrap="nowrap" width="25%">Last Name</th>
                <th align="left" nowrap="nowrap" width="25%">Candidate Owner</th>
                <th align="left" nowrap="nowrap" width="25%">Date Submitted</th>
            </tr>

            <?php foreach ($submissionJobOrdersData['submissionsRS'] as $rowNumber => $submissionsData): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td valign="top" align="left">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($submissionsData['candidateID']) ?>" target="_blank"><?php $this->_($submissionsData['firstName']) ?></a>
                    &nbsp;</td>
                    <td valign="top" align="left">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($submissionsData['candidateID']) ?>" target="_blank"><?php $this->_($submissionsData['lastName']) ?></a>
                    &nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($submissionsData['ownerFullName']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($submissionsData['dateSubmitted']) ?>&nbsp;</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endforeach; ?>
<?php TemplateUtility::printReportFooter(); ?>
