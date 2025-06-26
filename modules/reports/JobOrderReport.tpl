<?php /* $Id: JobOrderReport.tpl 2441 2007-05-04 20:42:02Z brian $ */ ?>
<?php TemplateUtility::printHeader('Job Orders', array('modules/joborders/validator.js', 'js/company.js', 'js/sweetTitles.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/job_orders.gif" width="24" height="24" border="0" alt="Job Orders" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Reports: Job Order Report</h2></td>
                </tr>
            </table>

            <p class="note">Generate a job order report.</p>

            <form name="jobOrderReportForm" id="jobOrderReportForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=jobOrderReport" method="post" onsubmit="result = checkJobOrderReportForm(document.jobOrderReportForm); if(result) {document.getElementById('submit').disabled = true; document.getElementById('submit').value='Generating, please wait...';} return result;" autocomplete="off" enctype="multipart/form-data">
                <input type="hidden" name="m" value="reports">
                <input type="hidden" name="a" value="generateJobOrderReportPDF">

                <table class="editTable" width="700">
                    <tr>
                        <td class="tdVertical" style="width: 140px;">
                            <label id="siteNameLabel" for="siteName">Company Name:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="siteName" id="siteName" value="<?php $this->_($this->reportParameters['siteName']); ?>" style="width: 250px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="companyNameLabel" for="companyName">Company:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="companyName" id="companyName" value="<?php $this->_($this->reportParameters['companyName']); ?>" style="width: 250px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="jobOrderNameLabel" for="jobOrderName">Position (Title):</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="jobOrderName" id="jobOrderName" value="<?php $this->_($this->reportParameters['jobOrderName']); ?>" style="width: 250px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="periodLineLabel" for="periodLine">Job Order Period:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="periodLine" id="periodLine" value="<?php $this->_($this->reportParameters['periodLine']); ?>" style="width: 250px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="accountManagerLabel" for="accountManager">Account Manager:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="accountManager" id="accountManager" value="<?php $this->_($this->reportParameters['accountManager']); ?>" style="width: 250px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="recruiterLabel" for="recruiter">Recruiter:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="recruiter" id="recruiter" value="<?php $this->_($this->reportParameters['recruiter']); ?>" style="width: 250px;" />&nbsp;*
                        </td>
                    </tr>
                </table>

                <table class="editTable" width="700">
                    <input type="hidden" name="dataSet" id="dataSet" value="0,0,0,0">
                    <script type="text/javascript">
                        function setDataSet()
                        {
                            document.getElementById('dataSet').value =
                                document.getElementById('dataSet1').value + ',' +
                                document.getElementById('dataSet2').value + ',' +
                                document.getElementById('dataSet3').value + ',' +
                                document.getElementById('dataSet4').value;
                        }
                    </script>

                    <tr>
                        <td class="tdVertical" style="width: 140px;">
                            <label id="dataSet1Label"for="dataSet1">Candidates Screened:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSet1" id="dataSet1" value="<?php $this->_($this->reportParameters['dataSet1']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="dataSet2Label"for="dataSet2">Candidates Submitted:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSet2" id="dataSet2" value="<?php $this->_($this->reportParameters['dataSet2']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="dataSet3Label"for="dataSet3">Candidates Interviewed:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSet3" id="dataSet3" value="<?php $this->_($this->reportParameters['dataSet3']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="dataSet4Label"for="dataSet4">Candidates Placed:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSet4" id="dataSet4" value="<?php $this->_($this->reportParameters['dataSet4']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>
                </table>

                <table class="editTable" width="700">
                    <input type="hidden" name="dataSetThisMonth" id="dataSetThisMonth" value="0,0,0,0">
                    <script type="text/javascript">
                        function setDataSet()
                        {
                            document.getElementById('dataSetThisMonth').value =
                                document.getElementById('dataSetThisMonth1').value + ',' +
                                document.getElementById('dataSetThisMonth2').value + ',' +
                                document.getElementById('dataSetThisMonth3').value + ',' +
                                document.getElementById('dataSetThisMonth4').value;
                        }
                    </script>

                    <tr>
                        <td class="tdVertical" style="width: 140px;">
                            <label id="dataSetThisMonthLabel"for="dataSetThisMonth">This Month:</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical" style="width: 140px;">
                            <label id="dataSetThisMonth1Label"for="dataSetThisMonth1">Candidates Screened:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSetThisMonth1" id="dataSetThisMonth1" value="<?php $this->_($this->reportParameters['dataSetThisMonth1']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="dataSetThisMonth2Label"for="dataSetThisMonth2">Candidates Submitted:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSetThisMonth2" id="dataSetThisMonth2" value="<?php $this->_($this->reportParameters['dataSetThisMonth2']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="dataSetThisMonth3Label"for="dataSetThisMonth3">Candidates Interviewed:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSetThisMonth3" id="dataSetThisMonth3" value="<?php $this->_($this->reportParameters['dataSetThisMonth3']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="dataSetThisMonth4Label"for="dataSetThisMonth4">Candidates Placed:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSetThisMonth4" id="dataSetThisMonth4" value="<?php $this->_($this->reportParameters['dataSetThisMonth4']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>
                </table>

                <table class="editTable" width="700">
                    <input type="hidden" name="dataSetLastMonth" id="dataSetLastMonth" value="0,0,0,0">
                    <script type="text/javascript">
                        function setDataSet()
                        {
                            document.getElementById('dataSetLastMonth').value =
                                document.getElementById('dataSetLastMonth1').value + ',' +
                                document.getElementById('dataSetLastMonth2').value + ',' +
                                document.getElementById('dataSetLastMonth3').value + ',' +
                                document.getElementById('dataSetLastMonth4').value;
                        }
                    </script>

                    <tr>
                        <td class="tdVertical" style="width: 140px;">
                            <label id="dataSetLastMonthLabel"for="dataSetLastMonth">Last Month:</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical" style="width: 140px;">
                            <label id="dataSetLastMonth1Label"for="dataSetLastMonth1">Candidates Screened:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSetLastMonth1" id="dataSetLastMonth1" value="<?php $this->_($this->reportParameters['dataSetLastMonth1']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="dataSetLastMonth2Label"for="dataSetLastMonth2">Candidates Submitted:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSetLastMonth2" id="dataSetLastMonth2" value="<?php $this->_($this->reportParameters['dataSetLastMonth2']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="dataSetLastMonth3Label"for="dataSetLastMonth3">Candidates Interviewed:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSetLastMonth3" id="dataSetLastMonth3" value="<?php $this->_($this->reportParameters['dataSetLastMonth3']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="dataSetLastMonth4Label"for="dataSetLastMonth4">Candidates Placed:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSetLastMonth4" id="dataSetLastMonth4" value="<?php $this->_($this->reportParameters['dataSetLastMonth4']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>
                </table>

                <script type="text/javascript">setDataSet();</script>

                <table class="editTable" width="700">
                    <tr>
                        <td class="tdVertical" style="width: 140px;">
                            <label id="notesLabel" for="notes">Misc. Notes:</label>
                        </td>
                        <td class="tdData">
                            <textarea class="inputbox" name="notes" id="notes" rows="5" style="width: 400px;" /></textarea>
                        </td>
                    </tr>
                </table>

                <input type="submit" class="button" name="submit" id="submit" value="Generate Report" />&nbsp;
                <input type="reset"  class="button" name="reset"  value="Reset" />&nbsp;
                
                <!-- IE PDF Hack -->
                <input type="hidden" name="ext" value=".pdf" />
            </form>

            <script type="text/javascript">
                document.jobOrderReportForm.siteName.focus();
            </script>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
