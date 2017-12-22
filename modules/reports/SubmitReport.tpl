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

    <p class="note">Submit</p>
        <table class="sortable" width="1225">
            <tr>
                <th align="left" nowrap="nowrap">First Name</th>
                <th align="left" nowrap="nowrap">Last Name</th>
                <th align="left" nowrap="nowrap">Candidate Owner</th>
                <th align="left" nowrap="nowrap">JobOrder Date Submitted</th>
                <th align="left" nowrap="nowrap">Candiate Date Modified</th>
            </tr>

            <?php foreach ($this->submitRS as $rowNumber => $submitData): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td valign="top" align="left">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($submitData['candidateID']) ?>" target="_blank"><?php $this->_($submitData['firstName']) ?></a>
                    &nbsp;</td>
                    <td valign="top" align="left">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($submitData['candidateID']) ?>" target="_blank"><?php $this->_($submitData['lastName']) ?></a>
                    &nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($submitData['ownerFullName']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($submitData['dateSubmitted']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($submitData['dateModified']) ?>&nbsp;</td>
                </tr>
            <?php endforeach; ?>
        </table>
<?php TemplateUtility::printReportFooter(); ?>
