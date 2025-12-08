<?php /* $Id: CustomRangeReport.tpl */ ?>
<?php TemplateUtility::printHeader('Custom Range Report'); ?>
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
                    <td><h2>自訂月份範圍報告</h2></td>
                </tr>
            </table>

            <p class="note">自訂月份範圍報告</p>

            <table border="0" width="1225">
                <tr>
                    <td width="420" valign="top">
                        <table class="statisticsTable" width="400">
                            <tr>
                                <th align="left"><?php $this->_($this->customStatisticsData['dateRange']); ?></th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Job Orders</td>
                                <td align="right"><?php $this->_($this->customStatisticsData['jobOrders']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">New Candidates</td>
                                <td align="right"><?php $this->_($this->customStatisticsData['candidates']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left">New Companies</td>
                                <td align="right"><?php $this->_($this->customStatisticsData['companies']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">New Pipelines</td>
                                <td align="right"><?php $this->_($this->customStatisticsData['pipelines']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">New Submissions</td>
                                <td align="right"><?php $this->_($this->customStatisticsData['submissions']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="oddTableRow">
                                <td align="left">New Placements</td>
                                <td align="right"><?php $this->_($this->customStatisticsData['placements']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left">New Contacts</td>
                                <td align="right"><?php $this->_($this->customStatisticsData['contacts']); ?>&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="1" width="420" valign="top" style="padding-top: 20px;">
                        <table class="statisticsTable" width="400">
                            <tr>
                                <th align="left">重新查詢</th>
                            </tr>
                            <tr>
                                <td>
                                    <form method="get" action="<?php echo(CATSUtility::getIndexName()); ?>">
                                        <input type="hidden" name="m" value="reports" />
                                        <input type="hidden" name="a" value="showCustomRangeReport" />
                                        <table border="0" cellpadding="5" cellspacing="0" width="100%">
                                            <tr>
                                                <td align="right" style="padding-right: 10px;">開始月份：</td>
                                                <td>
                                                    <select name="customStartYear" style="width: 80px;">
                                                        <?php
                                                        $currentYear = date('Y');
                                                        $currentMonth = date('m');
                                                        for ($year = $currentYear - 5; $year <= $currentYear + 1; $year++)
                                                        {
                                                            $selected = (isset($this->customStartYear) && $this->customStartYear == $year) ? 'selected' : ((!isset($this->customStartYear) && $year == $currentYear) ? 'selected' : '');
                                                            echo '<option value="' . $year . '" ' . $selected . '>' . $year . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                    <select name="customStartMonth" style="width: 80px; margin-left: 5px;">
                                                        <?php
                                                        for ($month = 1; $month <= 12; $month++)
                                                        {
                                                            $monthStr = sprintf('%02d', $month);
                                                            $selected = (isset($this->customStartMonth) && $this->customStartMonth == $monthStr) ? 'selected' : ((!isset($this->customStartMonth) && $monthStr == $currentMonth) ? 'selected' : '');
                                                            echo '<option value="' . $monthStr . '" ' . $selected . '>' . $monthStr . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="right" style="padding-right: 10px;">結束月份：</td>
                                                <td>
                                                    <select name="customEndYear" style="width: 80px;">
                                                        <?php
                                                        for ($year = $currentYear - 5; $year <= $currentYear + 1; $year++)
                                                        {
                                                            $selected = (isset($this->customEndYear) && $this->customEndYear == $year) ? 'selected' : ((!isset($this->customEndYear) && $year == $currentYear) ? 'selected' : '');
                                                            echo '<option value="' . $year . '" ' . $selected . '>' . $year . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                    <select name="customEndMonth" style="width: 80px; margin-left: 5px;">
                                                        <?php
                                                        for ($month = 1; $month <= 12; $month++)
                                                        {
                                                            $monthStr = sprintf('%02d', $month);
                                                            $selected = (isset($this->customEndMonth) && $this->customEndMonth == $monthStr) ? 'selected' : ((!isset($this->customEndMonth) && $monthStr == $currentMonth) ? 'selected' : '');
                                                            echo '<option value="' . $monthStr . '" ' . $selected . '>' . $monthStr . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" align="center" style="padding-top: 10px;">
                                                    <input type="submit" value="查詢" class="button" />
                                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=reports" style="margin-left: 10px;">返回報告首頁</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>

