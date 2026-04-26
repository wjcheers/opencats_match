<?php TemplateUtility::printHeader('AI Resume Usage Report'); ?>
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
                    <td><h2>AI Resume Usage Report</h2></td>
                </tr>
            </table>

            <p class="note">AI resume parsing and Jecho report generation usage.</p>

            <?php if (empty($this->aiParseRecords) && empty($this->aiParseSummary['totalCount'])): ?>
                <p class="warning">No AI usage records were found for this period.</p>
            <?php endif; ?>

            <table border="0" width="1225">
                <tr>
                    <td width="420" valign="top">
                        <table class="statisticsTable" width="400">
                            <tr>
                                <th align="left"><?php $this->_($this->periodData['label']); ?></th>
                                <th align="right">&nbsp;&nbsp;</th>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">AI Requests</td>
                                <td align="right"><?php $this->_($this->aiParseSummary['totalCount']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">Resume Parses</td>
                                <td align="right"><?php $this->_($this->aiParseSummary['parseCount']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">Jecho Reports Generated</td>
                                <td align="right"><?php $this->_($this->aiParseSummary['reportCount']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">Saved Candidates</td>
                                <td align="right"><?php $this->_($this->aiParseSummary['savedCount']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">Successful Parses</td>
                                <td align="right"><?php $this->_($this->aiParseSummary['successCount']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">Input Tokens</td>
                                <td align="right"><?php $this->_(number_format($this->aiParseSummary['inputTokens'], 0)); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">Output Tokens</td>
                                <td align="right"><?php $this->_(number_format($this->aiParseSummary['outputTokens'], 0)); ?>&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                    <td width="805" valign="top">
                        <table class="statisticsTable" width="785">
                            <tr>
                                <th align="left">Filter</th>
                            </tr>
                            <tr>
                                <td>
                                    <form method="get" action="<?php echo(CATSUtility::getIndexName()); ?>">
                                        <input type="hidden" name="m" value="reports" />
                                        <input type="hidden" name="a" value="showAIUsageReport" />
                                        <table border="0" cellpadding="5" cellspacing="0" width="100%">
                                            <tr>
                                                <td align="right" width="120">Period:</td>
                                                <td>
                                                    <select name="period" style="width: 180px;">
                                                        <?php
                                                        $periodOptions = array(
                                                            'today' => 'Today',
                                                            'yesterday' => 'Yesterday',
                                                            'thisWeek' => 'This Week',
                                                            'lastWeek' => 'Last Week',
                                                            'thisMonth' => 'This Month',
                                                            'lastMonth' => 'Last Month',
                                                            'toDate' => 'To Date'
                                                        );
                                                        foreach ($periodOptions as $value => $label)
                                                        {
                                                            $selected = ($this->periodToken == $value) ? 'selected' : '';
                                                            echo '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="right">User:</td>
                                                <td>
                                                    <?php if (!empty($this->canViewAllAIUsage)): ?>
                                                        <select name="userID" style="width: 260px;">
                                                            <option value="0">All Users</option>
                                                            <?php foreach ($this->usersRS as $user): ?>
                                                                <option value="<?php $this->_($user['userID']); ?>" <?php if ((int) $this->userID == (int) $user['userID']) echo 'selected'; ?>>
                                                                    <?php $this->_($user['ownerFullName']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    <?php else: ?>
                                                        <span>This report is limited to your own AI usage.</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td>
                                                    <input type="submit" value="Run Report" class="button" />
                                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=reports" style="margin-left: 10px;">Back to Reports</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top: 20px;">
                        <table class="sortable" width="1225">
                            <tr>
                                <th align="left">Date</th>
                                <th align="left">User</th>
                                <th align="left">Source</th>
                                <th align="left">File</th>
                                <th align="left">Language</th>
                                <th align="left">Model</th>
                                <th align="right">Input</th>
                                <th align="right">Output</th>
                                <th align="left">Status</th>
                                <th align="left">Candidate</th>
                            </tr>
                            <?php if (!empty($this->aiParseRecords)): ?>
                                <?php foreach ($this->aiParseRecords as $index => $row): ?>
                                    <tr class="<?php echo (($index % 2) == 0) ? 'evenTableRow' : 'oddTableRow'; ?>">
                                        <td><?php $this->_($row['createdAt']); ?></td>
                                        <td><?php $this->_($row['userFullName']); ?></td>
                                        <td><?php $this->_($row['sourceLabel']); ?></td>
                                        <td><?php $this->_($row['originalFilename']); ?></td>
                                        <td><?php $this->_($row['documentLanguage']); ?></td>
                                        <td><?php $this->_($row['provider'] . ' / ' . $row['model']); ?></td>
                                        <td align="right"><?php $this->_(number_format($row['inputTokens'], 0)); ?></td>
                                        <td align="right"><?php $this->_(number_format($row['outputTokens'], 0)); ?></td>
                                        <td><?php $this->_($row['statusLabel']); ?></td>
                                        <td>
                                            <?php if (!empty($row['savedCandidateID'])): ?>
                                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($row['savedCandidateID']); ?>" target="_blank">
                                                    #<?php $this->_($row['savedCandidateID']); ?>
                                                </a>
                                            <?php else: ?>
                                                &nbsp;
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="evenTableRow">
                                    <td colspan="10">No records found.</td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
