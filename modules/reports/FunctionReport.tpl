<?php /* $Id: FunctionReport.tpl $ */ ?>
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

    <p class="note">Functions</p>
    <?php foreach ($this->jobOrderFunctionsRS as $rowNumber => $jobOrderFunctionsData): ?>
        <span style="font: normal normal bold 13px/130% Arial, Tahoma, sans-serif;"><?php $this->_($jobOrderFunctionsData['jobOrderFunctions']) ?></span>
            <?php
                $companyID = 0;
                foreach ($jobOrderFunctionsData['jobOrdersRS'] as $rowNumber => $jobOrdersData):
                if($companyID == $jobOrdersData['companyID'])
                    continue;
                $companyID = $jobOrdersData['companyID'];?>
                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($jobOrdersData['companyID']) ?>" target="_blank"><?php $jobOrdersData['companyShortName'] ? $this->_($jobOrdersData['companyShortName']) : $this->_($jobOrdersData['companyName']) ?></a>
            <?php endforeach; ?>
        </BR>
    <?php endforeach; ?>
    
    </BR>
    </BR>

    <?php foreach ($this->jobOrderFunctionsRS as $rowNumber => $jobOrderFunctionsData): ?>
        <span style="font: normal normal bold 13px/130% Arial, Tahoma, sans-serif;"><?php $this->_($jobOrderFunctionsData['jobOrderFunctions']) ?></span>
        <br />
        <table class="sortable" width="1225">
            <tr>
                <th width="2%" align="left" nowrap="nowrap">HC</th>
                <th width="35%" align="left" nowrap="nowrap">Job Order</th>
                <th width="33%" align="left" nowrap="nowrap">Company Name</th>
                <th width="10%" align="left" nowrap="nowrap">Owner</th>
                <th width="10%" align="left" nowrap="nowrap">Recruiter</th>
                <th width="10%" align="left" nowrap="nowrap">Date Modified</th>
            </tr>

            <?php foreach ($jobOrderFunctionsData['jobOrdersRS'] as $rowNumber => $jobOrdersData): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td valign="top" align="left"><?php $this->_($jobOrdersData['openings']) ?>&nbsp;</td>
                    <td valign="top" align="left">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($jobOrdersData['jobOrderID']) ?>" target="_blank"><?php $this->_($jobOrdersData['title']) ?></a>
                    &nbsp;</td>
                    <td valign="top" align="left">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($jobOrdersData['companyID']) ?>" target="_blank"><?php $this->_($jobOrdersData['companyName']) ?></a>
                    &nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($jobOrdersData['ownerFullName']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($jobOrdersData['recruiterFullName']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($jobOrdersData['dateModifieded']) ?>&nbsp;</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endforeach; ?>
<?php TemplateUtility::printReportFooter(); ?>
