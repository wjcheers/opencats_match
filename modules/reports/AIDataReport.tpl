<?php TemplateUtility::printHeader('AI Resume Data Report'); ?>
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
                    <td><h2>AI Resume Data Report</h2></td>
                </tr>
            </table>

            <p class="note">AI resume parser dictionary, suggestion, and parse result overview.</p>

            <?php if (!empty($this->message)): ?>
                <p class="note">
                    <?php
                        $messages = array(
                            'accepted' => 'Suggestion accepted as an alias.',
                            'created' => 'Dictionary entry created from suggestion.',
                            'ignored' => 'Suggestion ignored.',
                            'missing_suggestion' => 'Suggestion was not found.',
                            'unsupported' => 'Suggestion type is not supported.'
                        );
                        $this->_(isset($messages[$this->message]) ? $messages[$this->message] : $this->message);
                    ?>
                </p>
            <?php endif; ?>

            <table class="statisticsTable" width="100%">
                <tr>
                    <th align="left">How to Use This Page</th>
                </tr>
                <tr>
                    <td>
                        This page is for checking AI resume parsing quality. First confirm that Parse Logs and Parse Results have records.
                        Then review Job Title, Function, and Job Level to find values that are unmapped or inconsistent.
                        Finally review Suggestions and Recent Parse Results to decide which aliases or dictionary entries should be added later.
                    </td>
                </tr>
            </table>

            <table border="0" width="100%">
                <tr>
                    <td width="35%" valign="top">
                        <table class="statisticsTable" width="100%">
                            <tr>
                                <th align="left">AI Data Tables</th>
                                <th align="right">&nbsp;&nbsp;</th>
                            </tr>
                            <?php foreach ($this->dictionarySummary as $index => $row): ?>
                                <tr class="<?php echo (($index % 2) == 0) ? 'evenTableRow' : 'oddTableRow'; ?>">
                                    <td align="left">
                                        <?php $this->_($row['label']); ?><br />
                                        <span style="color: #777;"><?php $this->_($row['table']); ?></span>
                                    </td>
                                    <td align="right">
                                        <?php if ($row['count'] === null): ?>
                                            Missing
                                        <?php else: ?>
                                            <?php $this->_(number_format($row['count'], 0)); ?>
                                        <?php endif; ?>
                                        &nbsp;&nbsp;
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                    <td width="65%" valign="top">
                        <table class="statisticsTable" width="100%">
                            <tr>
                                <th align="left">Links</th>
                            </tr>
                            <tr>
                                <td>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showAIUsageReport&amp;period=today">AI Resume Usage Report</a>
                                    &nbsp;|&nbsp;
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=reports">Back to Reports</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <br />
            <p class="note">Top parsed values. High unmapped counts usually mean the dictionary or aliases need cleanup.</p>
            <table border="0" width="100%">
                <tr>
                    <td width="33%" valign="top" style="padding-right: 8px;">
                        <table class="sortable" width="100%" style="table-layout: fixed;">
                            <tr>
                                <th align="left" width="60%">Job Title</th>
                                <th align="right" width="18%">Count</th>
                                <th align="right" width="22%">Avg Conf.</th>
                            </tr>
                            <?php if (!empty($this->jobTitleStats)): ?>
                                <?php foreach ($this->jobTitleStats as $index => $row): ?>
                                    <tr class="<?php echo (($index % 2) == 0) ? 'evenTableRow' : 'oddTableRow'; ?>">
                                        <td style="word-wrap: break-word;">
                                            <?php $this->_($row['canonicalValue']); ?><br />
                                            <span style="color: #777;"><?php $this->_($row['sampleRawValue']); ?></span>
                                        </td>
                                        <td align="right"><?php $this->_(number_format($row['recordCount'], 0)); ?></td>
                                        <td align="right"><?php $this->_(number_format($row['averageConfidence'], 2)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="evenTableRow"><td colspan="3">No job title data.</td></tr>
                            <?php endif; ?>
                        </table>
                    </td>
                    <td width="33%" valign="top" style="padding-right: 8px;">
                        <table class="sortable" width="100%" style="table-layout: fixed;">
                            <tr>
                                <th align="left" width="60%">Function</th>
                                <th align="right" width="18%">Count</th>
                                <th align="right" width="22%">Avg Conf.</th>
                            </tr>
                            <?php if (!empty($this->functionStats)): ?>
                                <?php foreach ($this->functionStats as $index => $row): ?>
                                    <tr class="<?php echo (($index % 2) == 0) ? 'evenTableRow' : 'oddTableRow'; ?>">
                                        <td style="word-wrap: break-word;">
                                            <?php $this->_($row['canonicalValue']); ?><br />
                                            <span style="color: #777;"><?php $this->_($row['sampleRawValue']); ?></span>
                                        </td>
                                        <td align="right"><?php $this->_(number_format($row['recordCount'], 0)); ?></td>
                                        <td align="right"><?php $this->_(number_format($row['averageConfidence'], 2)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="evenTableRow"><td colspan="3">No function data.</td></tr>
                            <?php endif; ?>
                        </table>
                    </td>
                    <td width="34%" valign="top">
                        <table class="sortable" width="100%" style="table-layout: fixed;">
                            <tr>
                                <th align="left" width="60%">Job Level</th>
                                <th align="right" width="18%">Count</th>
                                <th align="right" width="22%">Avg Conf.</th>
                            </tr>
                            <?php if (!empty($this->jobLevelStats)): ?>
                                <?php foreach ($this->jobLevelStats as $index => $row): ?>
                                    <tr class="<?php echo (($index % 2) == 0) ? 'evenTableRow' : 'oddTableRow'; ?>">
                                        <td style="word-wrap: break-word;"><?php $this->_($row['canonicalValue']); ?></td>
                                        <td align="right"><?php $this->_(number_format($row['recordCount'], 0)); ?></td>
                                        <td align="right"><?php $this->_(number_format($row['averageConfidence'], 2)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="evenTableRow"><td colspan="3">No job level data.</td></tr>
                            <?php endif; ?>
                        </table>
                    </td>
                </tr>
            </table>

            <br />
            <p class="note">Recent AI suggestions. These are candidates for future dictionary or alias cleanup.</p>
            <script type="text/javascript">
            function submitAISuggestionAction(form, operation, message)
            {
                form.operation.value = operation;
                return confirm(message);
            }
            </script>
            <table class="sortable" width="100%">
                <tr>
                    <th align="left">Created</th>
                    <th align="left">Type</th>
                    <th align="left">Raw Value</th>
                    <th align="left">Suggested Key</th>
                    <th align="left">Suggested Name</th>
                    <th align="right">Confidence</th>
                    <th align="left">Status</th>
                    <th align="left">Actions</th>
                </tr>
                <?php if (!empty($this->recentSuggestions)): ?>
                    <?php foreach ($this->recentSuggestions as $index => $row): ?>
                        <tr class="<?php echo (($index % 2) == 0) ? 'evenTableRow' : 'oddTableRow'; ?>">
                            <td><?php $this->_($row['createdAt']); ?></td>
                            <td><?php $this->_($row['suggestionType']); ?></td>
                            <td><?php $this->_($row['rawValue']); ?></td>
                            <td><?php $this->_($row['suggestedCanonicalKey']); ?></td>
                            <td><?php $this->_($row['suggestedNameEN'] . ' / ' . $row['suggestedNameZH']); ?></td>
                            <td align="right"><?php $this->_(number_format($row['confidenceScore'], 2)); ?></td>
                            <td><?php $this->_($row['status']); ?></td>
                            <td>
                                <?php if ($row['status'] == 'pending'): ?>
                                    <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=handleAISuggestion">
                                        <input type="hidden" name="suggestionID" value="<?php $this->_($row['suggestionID']); ?>" />
                                        <input type="hidden" name="operation" value="" />
                                        <div style="margin-bottom: 3px;">
                                            <label>Key</label><br />
                                            <input type="text" name="canonicalKey" value="<?php $this->_($row['suggestedCanonicalKey']); ?>" style="width: 190px;" />
                                        </div>
                                        <div style="margin-bottom: 3px;">
                                            <label>EN</label><br />
                                            <input type="text" name="nameEN" value="<?php $this->_($row['suggestedNameEN']); ?>" style="width: 190px;" />
                                        </div>
                                        <div style="margin-bottom: 6px;">
                                            <label>ZH</label><br />
                                            <input type="text" name="nameZH" value="<?php $this->_($row['suggestedNameZH']); ?>" style="width: 190px;" />
                                        </div>
                                        <input
                                            type="submit"
                                            class="button"
                                            value="Accept as Alias"
                                            onclick="return submitAISuggestionAction(this.form, 'accept_alias', 'Accept this suggestion as an alias?');"
                                        />
                                        <input
                                            type="submit"
                                            class="button"
                                            value="Create Entry"
                                            onclick="return submitAISuggestionAction(this.form, 'create_dictionary', 'Create a new dictionary entry from this suggestion?');"
                                        />
                                        <input
                                            type="submit"
                                            class="button"
                                            value="Ignore"
                                            onclick="return submitAISuggestionAction(this.form, 'ignore', 'Ignore this suggestion?');"
                                        />
                                    </form>
                                <?php else: ?>
                                    &nbsp;
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="evenTableRow"><td colspan="8">No suggestions found.</td></tr>
                <?php endif; ?>
            </table>

            <br />
            <p class="note">Recent parse results. Use this area to spot-check whether AI extracted names, contact info, job title, function, and level correctly.</p>
            <table class="sortable" width="100%">
                <tr>
                    <th align="left">Created</th>
                    <th align="left">File</th>
                    <th align="left">Name</th>
                    <th align="left">Email / Phone</th>
                    <th align="left">Job Title</th>
                    <th align="left">Function</th>
                    <th align="left">Level</th>
                    <th align="left">Candidate</th>
                </tr>
                <?php if (!empty($this->recentResults)): ?>
                    <?php foreach ($this->recentResults as $index => $row): ?>
                        <tr class="<?php echo (($index % 2) == 0) ? 'evenTableRow' : 'oddTableRow'; ?>">
                            <td><?php $this->_($row['createdAt']); ?></td>
                            <td><?php $this->_($row['originalFilename']); ?></td>
                            <td><?php $this->_(trim($row['chineseName'] . ' ' . $row['firstName'] . ' ' . $row['lastName'])); ?></td>
                            <td>
                                <?php $this->_($row['email']); ?><br />
                                <span style="color: #777;"><?php $this->_($row['phone']); ?></span>
                            </td>
                            <td><?php $this->_($row['jobTitleRaw']); ?></td>
                            <td><?php $this->_($row['functionRaw']); ?></td>
                            <td><?php $this->_($row['jobLevel']); ?></td>
                            <td>
                                <?php if (!empty($row['savedCandidateID'])): ?>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($row['savedCandidateID']); ?>" target="_blank">
                                        #<?php $this->_($row['savedCandidateID']); ?>
                                    </a>
                                <?php else: ?>
                                    <?php $this->_($row['status']); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="evenTableRow"><td colspan="8">No parse results found.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
