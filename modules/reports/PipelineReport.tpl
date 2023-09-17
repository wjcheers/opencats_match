<?php /* $Id: PipelineReport.tpl 1948 2007-02-23 09:49:27Z will $ */ ?>
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

    <p class="note">Pipelines</p>

    <?php foreach ($this->pipelineJobOrdersRS as $rowNumber => $pipelineJobOrdersData): ?>
        <span style="font: normal normal bold 13px/130% Arial, Tahoma, sans-serif;"><a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($pipelineJobOrdersData['jobOrderID']) ?>" target="_blank"><?php $this->_($pipelineJobOrdersData['title']) ?></a> at <?php $this->_($pipelineJobOrdersData['companyName']) ?> (<?php $this->_($pipelineJobOrdersData['ownerFullName']) ?>)</span>
        <br />
        <table class="sortable" width="1225">
            <tr>
                <th align="left" nowrap="nowrap" width="25%">First Name</th>
                <th align="left" nowrap="nowrap" width="25%">Last Name</th>
                <th align="left" nowrap="nowrap" width="25%">Candidate Owner</th>
                <th align="left" nowrap="nowrap" width="25%">Date Created</th>
            </tr>

            <?php foreach ($pipelineJobOrdersData['pipelinesRS'] as $rowNumber => $pipelinesData): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td valign="top" align="left">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($pipelinesData['candidateID']) ?>" target="_blank"><?php $this->_($pipelinesData['firstName']) ?></a>
                    &nbsp;</td>
                    <td valign="top" align="left">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($pipelinesData['candidateID']) ?>" target="_blank"><?php $this->_($pipelinesData['lastName']) ?></a>
                    &nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($pipelinesData['ownerFullName']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($pipelinesData['dateCreated']) ?>&nbsp;</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endforeach; ?>
<?php TemplateUtility::printReportFooter(); ?>
