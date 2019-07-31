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

    <p class="note">Offers</p>

    <?php foreach ($this->offersJobOrdersRS as $rowNumber => $offersJobOrdersData): ?>
        <span style="font: normal normal bold 13px/130% Arial, Tahoma, sans-serif;"><a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($offersJobOrdersData['jobOrderID']) ?>" target="_blank"><?php $this->_($offersJobOrdersData['title']) ?></a> at <?php $this->_($offersJobOrdersData['companyName']) ?> (<?php $this->_($offersJobOrdersData['ownerFullName']) ?>)</span>
        <br />
        <table class="sortable" width="1225">
            <tr>
                <th align="left" nowrap="nowrap">First Name</th>
                <th align="left" nowrap="nowrap">Last Name</th>
                <th align="left" nowrap="nowrap">Candidate Owner</th>
                <th align="left" nowrap="nowrap">Date Placed</th>
            </tr>

            <?php foreach ($offersJobOrdersData['offersRS'] as $rowNumber => $offersData): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td valign="top" align="left">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($offersData['candidateID']) ?>" target="_blank"><?php $this->_($offersData['firstName']) ?></a>
                    &nbsp;</td>
                    <td valign="top" align="left">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($offersData['candidateID']) ?>" target="_blank"><?php $this->_($offersData['lastName']) ?></a>
                    &nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($offersData['ownerFullName']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($offersData['dateSubmitted']) ?>&nbsp;</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endforeach; ?>
<?php TemplateUtility::printReportFooter(); ?>
