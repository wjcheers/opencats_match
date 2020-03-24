<?php /* $Id: Home.tpl 3563 2007-11-12 07:41:54Z will $ */ ?>
<?php TemplateUtility::printHeader('Home', array('js/sweetTitles.js', 'js/dataGrid.js', 'js/home.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents" style="padding-top: 10px;">

            <table>
                <tr>
                    <td align="left" valign="top" style="text-align: left; width: 405px; height:50px;">
                        <div class="noteUnsizedSpan" style="width:406px;">My Recent Calls</div>
                        <?php $this->dataGrid2->drawHTML();  ?>
                    </td>

                    <td align="center" valign="top" style="text-align: left; width: 405px; font-size:11px; height:50px;">
                        <?php echo($this->upcomingEventsFupHTML); ?>
                    </td>

                    <td align="center" valign="top" style="text-align: left; width: 405px; font-size:11px; height:50px;">
                        <?php echo($this->upcomingEventsHTML); ?>
                    </td>
                </tr>
            </table>

            <table>
                <tr>
                    <td align="left" valign="top" style="text-align: left; width: 50%; height: 240px;">
                        <div class="noteUnsizedSpan" style="width:560px;">Recent Hires</div>

                        <table class="sortable" width="560" style="margin: 0 0 4px 0;">
                            <tr>
                                <th align="left" style="font-size:11px;">Name</th>
                                <th align="left" style="font-size:11px;">Company</th>
                                <th align="left" style="font-size:11px;">Recruiter</th>
                                <th align="left" style="font-size:11px;">Date</th>
                            </tr>
                            <?php foreach($this->placedRS as $index => $data): ?>
                            <tr class="<?php TemplateUtility::printAlternatingRowClass($index); ?>">
                                <td style="font-size:11px;"><a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php echo($data['candidateID']); ?>"style="font-size:11px;" class="<?php echo($data['candidateClassName']); ?>"><?php $this->_($data['firstName']); ?> <?php $this->_($data['lastName']); ?></a></td>
                                <td style="font-size:11px;"><a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php echo($data['companyID']); ?>"  style="font-size:11px;" class="<?php echo($data['companyClassName']); ?>"><?php $this->_($data['companyName']); ?></td>
                                <td style="font-size:11px;"><?php $this->_(StringUtility::makeInitialName($data['userFirstName'], $data['userLastName'], false, LAST_NAME_MAXLEN)); ?></td>
                                <td style="font-size:11px;"><?php $this->_($data['date']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>

                        <?php if (!count($this->placedRS)): ?>
                            <div style="width: 561px; height: 207px; border: 1px solid #c0c0c0; background: #E7EEFF url(images/nodata/dashboardNoHiresWhite.jpg);">
                                &nbsp;
                            </div>
                        <?php endif; ?>
                    </td>

                    <td align="center" valign="top" style="text-align: left; width: 50%; height: 240px;">
                        <div class="noteUnsizedSpan" style="width:645px;">Hiring Overview</div>
                        <map name="dashboardmap" id="dashboardmap">
                           <area href="#" alt="Weekly" title="Weekly"
                                 shape="rect" coords="398,0,461,24" onclick="swapHomeGraph(<?php echo(DASHBOARD_GRAPH_WEEKLY); ?>);" />
                           <area href="#" alt="Monthly" title="Monthly"
                                 shape="rect" coords="398,25,461,48" onclick="swapHomeGraph(<?php echo(DASHBOARD_GRAPH_MONTHLY); ?>);" />
                            <area href="#" alt="Yearly" title="Yearly"
                                 shape="rect" coords="398,49,461,74" onclick="swapHomeGraph(<?php echo(DASHBOARD_GRAPH_YEARLY); ?>);" />
                        </map>
                        <img src="<?php echo(CATSUtility::getIndexName()); ?>?m=graphs&amp;a=miniPlacementStatistics&amp;width=495&amp;height=230" id="homeGraph" onclick="" alt="Hiring Overview" usemap="#dashboardmap" border="0" />
                    </td>
                </tr>
            </table>

            <table>
                <tr>
                    <td align="left" valign="top" style="text-align: left; width: 50%;">
                        <div class="noteUnsizedSpan" style="width: 1220px;">My Candidates (Submitted, Interviewing, Offered in Job Orders) - Page <?php echo($this->dataGridMy->getCurrentPageHTML()); ?> (<?php echo($this->dataGridMy->getNumberOfRows()); ?> Items)</div>
                        <?php $this->dataGridMy->draw(); ?>
                        <div style="float:right;"><?php $this->dataGridMy->printNavigation(false); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php $this->dataGridMy->printShowAll(); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>

                        <?php if (!$this->dataGridMy->getNumberOfRows()): ?>
                        <div style="width: 1226px; height: 47px; border: 1px solid #c0c0c0; background: #E7EEFF url(images/nodata/dashboardNoCandidatesWhiteSmall.jpg);">
                            &nbsp;
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            
            <table>
                <tr>
                    <td align="left" valign="top" style="text-align: left; width: 50%; height: 260px;">
                        <div class="noteUnsizedSpan" style="width: 1220px;">Important Candidates (Submitted, Interviewing, Offered in Job Orders) - Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?> (<?php echo($this->dataGrid->getNumberOfRows()); ?> Items)</div>
                        <?php $this->dataGrid->draw(); ?>
                        <div style="float:right;"><?php $this->dataGrid->printNavigation(false); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php $this->dataGrid->printShowAll(); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>

                        <?php if (!$this->dataGrid->getNumberOfRows()): ?>
                        <div style="width: 1226px; height: 208px; border: 1px solid #c0c0c0; background: #E7EEFF url(images/nodata/dashboardNoCandidatesWhite.jpg);">
                            &nbsp;
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
                        
            <table>
                <tr>
                    <td align="left" valign="top" style="text-align: left; width: 50%;">
                        <div class="noteUnsizedSpan" style="width: 1220px;">Qualifying Candidates in Active Job Orders - Page <?php echo($this->dataGridQualifying->getCurrentPageHTML()); ?> (<?php echo($this->dataGridQualifying->getNumberOfRows()); ?> Items)</div>
                        <?php $this->dataGridQualifying->draw(); ?>
                        <div style="float:right;"><?php $this->dataGridQualifying->printNavigation(false); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php $this->dataGridQualifying->printShowAll(); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>

                        <?php if (!$this->dataGridQualifying->getNumberOfRows()): ?>
                        <div style="width: 1226px; height: 47px; border: 1px solid #c0c0c0; background: #E7EEFF url(images/nodata/dashboardNoCandidatesWhiteSmall.jpg);">
                            &nbsp;
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
     
            <table>
                <tr>
                    <td align="left" valign="top" style="text-align: left; width: 50%;">
                        <div class="noteUnsizedSpan" style="width: 1220px;">Jechoing Candidates in Active Job Orders - Page <?php echo($this->dataGridJechoing->getCurrentPageHTML()); ?> (<?php echo($this->dataGridJechoing->getNumberOfRows()); ?> Items)</div>
                        <?php $this->dataGridJechoing->draw(); ?>
                        <div style="float:right;"><?php $this->dataGridJechoing->printNavigation(false); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php $this->dataGridJechoing->printShowAll(); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>

                        <?php if (!$this->dataGridJechoing->getNumberOfRows()): ?>
                        <div style="width: 1226px; height: 47px; border: 1px solid #c0c0c0; background: #E7EEFF url(images/nodata/dashboardNoCandidatesWhiteSmall.jpg);">
                            &nbsp;
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            
            <table>
                <tr>
                    <td align="left" valign="top" style="text-align: left; width: 50%;">
                        <div class="noteUnsizedSpan" style="width: 1220px;">Verified Candidates in Active Job Orders - Page <?php echo($this->dataGridVerified->getCurrentPageHTML()); ?> (<?php echo($this->dataGridVerified->getNumberOfRows()); ?> Items)</div>
                        <?php $this->dataGridVerified->draw(); ?>
                        <div style="float:right;"><?php $this->dataGridVerified->printNavigation(false); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php $this->dataGridVerified->printShowAll(); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>

                        <?php if (!$this->dataGridVerified->getNumberOfRows()): ?>
                        <div style="width: 1226px; height: 47px; border: 1px solid #c0c0c0; background: #E7EEFF url(images/nodata/dashboardNoCandidatesWhiteSmall.jpg);">
                            &nbsp;
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <table>
                <tr>
                    <td align="left" valign="top" style="text-align: left; width: 50%;">
                        <div class="noteUnsizedSpan" style="width: 1220px;">Awaiting Candidates in Active Job Orders - Page <?php echo($this->dataGridAwaiting->getCurrentPageHTML()); ?> (<?php echo($this->dataGridAwaiting->getNumberOfRows()); ?> Items)</div>
                        <?php $this->dataGridAwaiting->draw(); ?>
                        <div style="float:right;"><?php $this->dataGridAwaiting->printNavigation(false); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php $this->dataGridAwaiting->printShowAll(); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>

                        <?php if (!$this->dataGridAwaiting->getNumberOfRows()): ?>
                        <div style="width: 1226px; height: 47px; border: 1px solid #c0c0c0; background: #E7EEFF url(images/nodata/dashboardNoCandidatesWhiteSmall.jpg);">
                            &nbsp;
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <table>
                <tr>
                    <td align="left" valign="top" style="text-align: left; width: 50%;">
                        <div class="noteUnsizedSpan" style="width: 1220px;">Uploaded Candidates in Active Job Orders - Page <?php echo($this->dataGridUploaded->getCurrentPageHTML()); ?> (<?php echo($this->dataGridUploaded->getNumberOfRows()); ?> Items)</div>
                        <?php $this->dataGridUploaded->draw(); ?>
                        <div style="float:right;"><?php $this->dataGridUploaded->printNavigation(false); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php $this->dataGridUploaded->printShowAll(); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>

                        <?php if (!$this->dataGridUploaded->getNumberOfRows()): ?>
                        <div style="width: 1226px; height: 47px; border: 1px solid #c0c0c0; background: #E7EEFF url(images/nodata/dashboardNoCandidatesWhiteSmall.jpg);">
                            &nbsp;
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <table>
                <tr>
                    <td align="left" valign="top" style="text-align: left; width: 50%; height: 260px;">
                        <div class="noteUnsizedSpan" style="width: 1220px;">Ever Verified Candidates in Active Job Orders (Last 6 months) - Page <?php echo($this->dataGridEverVerified->getCurrentPageHTML()); ?> (<?php echo($this->dataGridEverVerified->getNumberOfRows()); ?> Items)</div>
                        <?php $this->dataGridEverVerified->draw(); ?>
                        <div style="float:right;"><?php $this->dataGridEverVerified->printNavigation(false); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php $this->dataGridEverVerified->printShowAll(); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>

                        <?php if (!$this->dataGridEverVerified->getNumberOfRows()): ?>
                        <div style="width: 1226px; height: 208px; border: 1px solid #c0c0c0; background: #E7EEFF url(images/nodata/dashboardNoCandidatesWhite.jpg);">
                            &nbsp;
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            
            <table>
                <tr>
                    <td align="left" valign="top" style="text-align: left; width: 50%; height: 260px;">
                        <div class="noteUnsizedSpan" style="width: 1220px;">Submitted Candidates - Page <?php echo($this->dataGridSubmitted->getCurrentPageHTML()); ?> (<?php echo($this->dataGridSubmitted->getNumberOfRows()); ?> Items)</div>
                        <?php $this->dataGridSubmitted->draw(); ?>
                        <div style="float:right;"><?php $this->dataGridSubmitted->printNavigation(false); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php $this->dataGridSubmitted->printShowAll(); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>

                        <?php if (!$this->dataGridSubmitted->getNumberOfRows()): ?>
                        <div style="width: 1226px; height: 208px; border: 1px solid #c0c0c0; background: #E7EEFF url(images/nodata/dashboardNoCandidatesWhite.jpg);">
                            &nbsp;
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
          
            <table>
                <tr>
                    <td align="left" valign="top" style="text-align: left; width: 50%; height: 260px;">
                        <div class="noteUnsizedSpan" style="width: 1220px;">Interviewing Candidates - Page <?php echo($this->dataGridInterviewing->getCurrentPageHTML()); ?> (<?php echo($this->dataGridInterviewing->getNumberOfRows()); ?> Items)</div>
                        <?php $this->dataGridInterviewing->draw(); ?>
                        <div style="float:right;"><?php $this->dataGridInterviewing->printNavigation(false); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php $this->dataGridInterviewing->printShowAll(); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>

                        <?php if (!$this->dataGridInterviewing->getNumberOfRows()): ?>
                        <div style="width: 1226px; height: 208px; border: 1px solid #c0c0c0; background: #E7EEFF url(images/nodata/dashboardNoCandidatesWhite.jpg);">
                            &nbsp;
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
         
            <table>
                <tr>
                    <td align="left" valign="top" style="text-align: left; width: 50%; height: 260px;">
                        <div class="noteUnsizedSpan" style="width: 1220px;">Offered Candidates - Page <?php echo($this->dataGridOffered->getCurrentPageHTML()); ?> (<?php echo($this->dataGridOffered->getNumberOfRows()); ?> Items)</div>
                        <?php $this->dataGridOffered->draw(); ?>
                        <div style="float:right;"><?php $this->dataGridOffered->printNavigation(false); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php $this->dataGridOffered->printShowAll(); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>

                        <?php if (!$this->dataGridOffered->getNumberOfRows()): ?>
                        <div style="width: 1226px; height: 208px; border: 1px solid #c0c0c0; background: #E7EEFF url(images/nodata/dashboardNoCandidatesWhite.jpg);">
                            &nbsp;
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            
            <?php /*
            <table>
                <tr>
                    <td align="left" valign="top" style="text-align: left; width: 50%; height: 260px;">
                        <div class="noteUnsizedSpan" style="width: 1220px;">Drifting Candidates in Active Job Orders - Page <?php echo($this->dataGridDrifting->getCurrentPageHTML()); ?> (<?php echo($this->dataGridDrifting->getNumberOfRows()); ?> Items)</div>
                        <?php $this->dataGridDrifting->draw(); ?>
                        <div style="float:right;"><?php $this->dataGridDrifting->printNavigation(false); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php $this->dataGridDrifting->printShowAll(); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>

                        <?php if (!$this->dataGridDrifting->getNumberOfRows()): ?>
                        <div style="width: 1226px; height: 208px; border: 1px solid #c0c0c0; background: #E7EEFF url(images/nodata/dashboardNoCandidatesWhite.jpg);">
                            &nbsp;
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            */ ?>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
