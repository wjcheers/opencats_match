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

    <p class="note">Companies Report</p>

    <table class="sortable" width="1225">
        <tr>
            <th align="left" nowrap="nowrap">Company Name</th>
            <th align="left" nowrap="nowrap">User</th>
            <th align="left" nowrap="nowrap">Modified</th>
            <th align="left" nowrap="nowrap">NewPipe</th>
            <th align="left" nowrap="nowrap">Submit</th>
            <th align="left" nowrap="nowrap">Interview</th>
            <th align="left" nowrap="nowrap">Updated Notes</th>
            <!--th align="left" nowrap="nowrap">Status</th-->
        </tr>
        <?php foreach ($this->companiesRS as $rowNumber => $companyData): ?>
            <?php foreach ($companyData['reportRS'] as $rowNumber => $reportData): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td valign="top" align="left" width="255px">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php echo($companyData['companyID']) ?>" target="_blank">
                            <?php
                                if (($companyData['isHot']) == '1') {
                                    echo('<span style="font-weight:bold;color:#ff0000;">');
                                }
                            ?>
                            <?php $this->_($companyData['companyName']) ?>
                            <?php
                                if (($companyData['isHot']) == '1') {    
                                    echo('</span>');
                                }
                            ?>
                        </a>
                        &nbsp;
                    </td>
                    <td valign="top" align="left" nowrap="nowrap"><?php $this->_($companyData['ownerFullName']) ?>&nbsp;</td>
                    <td valign="top" align="left" nowrap="nowrap"><?php $this->_($companyData['dateModifieded']) ?>&nbsp;</td>
                    <td valign="top" align="left" nowrap="nowrap"><?php $this->_($reportData['pipelineCount']) ?>&nbsp;</td>
                    <td valign="top" align="left" nowrap="nowrap"><?php $this->_($reportData['submissionCount']) ?>&nbsp;</td>
                    <td valign="top" align="left" nowrap="nowrap"><?php $this->_($reportData['interviewingCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($companyData['companyUpdatedNotes']) ?>&nbsp;</td>
                    <!--td valign="top" align="left" nowrap="nowrap"><?php $this->_($companyData['companyStatus']) ?>&nbsp;</td-->
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </table>

<?php TemplateUtility::printReportFooter(); ?>
