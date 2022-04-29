<?php /* $Id: PlacedReportByUser.tpl 2336 2007-04-14 22:01:51Z will $ */ ?>
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

    <p class="note">User Report by Date</p>
    <p>
    Cold Call: Activities with type: Cold Call<br/>
    Talked: Activities with type: Talked<br/>
    Contact: Activities with type: Email, Meeting, Other, Call (LVM), Call (Missed), IM<br/>
    Created: Created Candidates with Email/Phone/IM<br/>
    Submitted Person: Submitted Candidates<br/>
    Submitted: Submitted Pipelines<br/>
    Interviewing: Interviewing Pipelines<br/>
    Offered: Offered Pipelines<br/>
    Placed: Placed Pipelines
    </p>

    <table class="sortable" width="1225">
        <tr>
            <th align="left" nowrap="nowrap">Date</th>
            <th align="left" nowrap="nowrap">Owner</th>
            <th align="left" nowrap="nowrap">Cold Call</th>
            <th align="left" nowrap="nowrap">Talked</th>
            <th align="left" nowrap="nowrap">Contact</th>
            <th align="left" nowrap="nowrap">Activities</th>
            <th align="left" nowrap="nowrap">Created</th>
            <th align="left" nowrap="nowrap">Submitted Person</th>
            <th align="left" nowrap="nowrap">Submitted</th>
            <th align="left" nowrap="nowrap">Interviewing</th>
            <th align="left" nowrap="nowrap">Offered</th>
            <th align="left" nowrap="nowrap">Placed</th>
            <!--
            <th align="left" nowrap="nowrap">Created Companies</th>
            <th align="left" nowrap="nowrap">Created Contacts</th>
            -->
        </tr>
        <?php foreach ($this->UserDateRS as $rowNumber => $UserData): ?>
            <?php foreach ($UserData['reportRS'] as $rowNumber => $reportData): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td valign="top" align="left"><?php $this->_($UserData['date']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['ownerFullName']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['activityColdCall']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['activityTalked']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['activityContact']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['activityCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['createdCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['personSubmittedCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['submittedCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['interviewingCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['offeredCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['placedCount']) ?>&nbsp;</td>
                    <!--
                    <td valign="top" align="left"><?php $this->_($reportData['companyCount']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($reportData['contactCount']) ?>&nbsp;</td>
                    -->
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </table>
    
    <p class="note">Pipelines</p>

    <table class="sortable" width="1225">
        <tr>
            <th align="left" nowrap="nowrap">#</th>
            <th align="left" nowrap="nowrap">Name</th>
            <th align="left" nowrap="nowrap">Chinese</th>
            <th align="left" nowrap="nowrap">Linkedin</th>
            <th align="left" nowrap="nowrap">R</th>
            <th align="left" nowrap="nowrap">KeySkills</th>
            <th align="left" nowrap="nowrap">Owner</th>
            <th align="left" nowrap="nowrap">Date</th>
            <th align="left" nowrap="nowrap">Status</th>
            <th align="left" nowrap="nowrap">Functions</th>
            <th align="left" nowrap="nowrap">Last Notes</th>
            <th align="left" nowrap="nowrap">JobOrder</th>
            <th align="left" nowrap="nowrap">Company</th>
        </tr>

        <?php foreach ($this->submitRS as $rowNumber => $submitData): ?>
            <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                <td valign="top" align="left"><?php $this->_($rowNumber) ?>&nbsp;</td>
                <td valign="top" align="left">
                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($submitData['candidateID']) ?>" target="_blank"><?php $this->_($submitData['candidateFullName']) ?></a>
                &nbsp;</td>
                <td valign="top" align="left">
                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($submitData['candidateID']) ?>" target="_blank"><?php $this->_($submitData['chineseName']) ?></a>
                &nbsp;</td>
                <td valign="top" align="left">
                <?php if($submitData['linkedin'] != ''): ?>
                    <a href="<?php $this->_($submitData['linkedin']) ?>" target="_blank">Linkedin</a>
                <?php endif; ?>
                &nbsp;</td>
                <td valign="top" align="left">
                    <?php if($submitData['isResume'] == 1): ?>
                        <img src="images/job_orders.gif" alt="" width="16" height="16" title="Have Resume" />
                    <?php else: ?>
                        <img src="images/mru/blank.gif" alt="" width="16" height="16" />
                    <?php endif; ?>
                &nbsp;</td>                                
                <td valign="top" align="left"><?php $this->_($submitData['keySkills']) ?>&nbsp;</td>
                <td valign="top" align="left"><?php $this->_($submitData['ownerFullName']) ?>&nbsp;</td>
                <td valign="top" align="left"><?php $this->_($submitData['date']) ?>&nbsp;</td>
                <td valign="top" align="left"><?php $this->_($submitData['status']) ?>&nbsp;</td>
                <td valign="top" align="left"><?php $this->_($submitData['jobOrderFunctions']) ?>&nbsp;</td>
                <td valign="top" align="left"><?php echo $submitData['lastNotes']; ?>&nbsp;</td>
                <td valign="top" align="left">
                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($submitData['jobOrderID']) ?>" target="_blank">
                        <?php $this->_($submitData['jobOrderTitle']) ?>
                    </a>
                </td>
                <td valign="top" align="left">
                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($submitData['companyID']) ?>" target="_blank">
                    <?php if($submitData['companyShortName'] != ''): ?>
                        <?php $this->_($submitData['companyShortName']) ?>
                    <?php else: ?>
                        <?php $this->_($submitData['companyName']) ?>
                    <?php endif; ?>
                </a>
                &nbsp;</td>
                
            </tr>
        <?php endforeach; ?>
    </table>
    
<?php TemplateUtility::printReportFooter(); ?>
