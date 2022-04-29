<?php /* $Id: AddActivityChangeStatusModal.tpl 3799 2007-12-04 17:54:36Z brian $ */ ?>
<?php if ($this->isJobOrdersMode): ?>
    <?php TemplateUtility::printModalHeader('Job Orders', array('modules/candidates/activityvalidator.js', 'js/activity.js'), 'Job Orders: Log Activity'); ?>
<?php elseif ($this->onlyScheduleEvent): ?>
    <?php TemplateUtility::printModalHeader('Candidates', array('modules/candidates/activityvalidator.js', 'js/activity.js'), 'Candidates: Schedule Event'); ?>
<?php else: ?>
    <?php TemplateUtility::printModalHeader('Candidates', array('modules/candidates/activityvalidator.js', 'js/activity.js'), 'Candidates: Log Activity'); ?>
<?php endif; ?>

<?php if (!$this->isFinishedMode): ?>

<script type="text/javascript">
    <?php if ($this->isJobOrdersMode): ?>
        statusesArray = new Array(1);
        jobOrdersArray = new Array(1);
        statusesArrayString = new Array(1);
        jobOrdersArrayStringTitle = new Array(1);
        jobOrdersArrayStringCompany = new Array(1);
        statusesArray[0] = <?php echo($this->pipelineData['statusID']); ?>;
        statusesArrayString[0] = '<?php echo($this->pipelineData['status']); ?>';
        jobOrdersArray[0] = <?php echo($this->pipelineData['jobOrderID']); ?>;
        jobOrdersArrayStringTitle[0] = '<?php echo(str_replace("'", "\\'", $this->pipelineData['title'])); ?>';
        jobOrdersArrayStringCompany[0] = '<?php echo(str_replace("'", "\\'", $this->pipelineData['companyName'])); ?>';
    <?php else: ?>
        <?php $count = count($this->pipelineRS); ?>
        statusesArray = new Array(<?php echo($count); ?>);
        jobOrdersArray = new Array(<?php echo($count); ?>);
        statusesArrayString = new Array(<?php echo($count); ?>);
        jobOrdersArrayStringTitle = new Array(<?php echo($count); ?>);
        jobOrdersArrayStringCompany = new Array(<?php echo($count); ?>);
        <?php for ($i = 0; $i < $count; ++$i): ?>
            statusesArray[<?php echo($i); ?>] = <?php echo($this->pipelineRS[$i]['statusID']); ?>;
            statusesArrayString[<?php echo($i); ?>] = '<?php echo($this->pipelineRS[$i]['status']); ?>';
            jobOrdersArray[<?php echo($i); ?>] = <?php echo($this->pipelineRS[$i]['jobOrderID']); ?>;
            jobOrdersArrayStringTitle[<?php echo($i); ?>] = '<?php echo(str_replace("'", "\\'", $this->pipelineRS[$i]['title'])); ?>';
            jobOrdersArrayStringCompany[<?php echo($i); ?>] = '<?php echo(str_replace("'", "\\'", $this->pipelineRS[$i]['companyName'])); ?>';
        <?php endfor; ?>
    <?php endif; ?>
    statusTriggersEmailArray = new Array(<?php echo(count($this->statusRS)); ?>);
    <?php foreach ($this->statusRS as $rowNumber => $statusData): ?>
       statusTriggersEmailArray[<?php echo($rowNumber); ?>] = <?php echo($statusData['triggersEmail']); ?>;
    <?php endforeach; ?>
</script>

    <form name="changePipelineStatusForm" id="changePipelineStatusForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=<?php if ($this->isJobOrdersMode): ?>joborders<?php else: ?>candidates<?php endif; ?>&amp;a=addActivityChangeStatus<?php if ($this->onlyScheduleEvent): ?>&amp;onlyScheduleEvent=true<?php endif; ?>" method="post" onsubmit="result = checkActivityForm(document.changePipelineStatusForm); if(result) {document.getElementById('submit').disabled = true; document.getElementById('submit').value='Sending, please wait...';} return result;" autocomplete="off">
        <input type="hidden" name="postback" id="postback" value="postback" />
        <input type="hidden" id="candidateID" name="candidateID" value="<?php echo($this->candidateID); ?>" />
<?php if ($this->isJobOrdersMode): ?>
        <input type="hidden" id="regardingID" name="regardingID" value="<?php echo($this->selectedJobOrderID); ?>" />
<?php endif; ?>

        <table class="editTable" width="560">
            <tr id="visibleTR" <?php if ($this->onlyScheduleEvent): ?>style="display:none;"<?php endif; ?>>
                <td class="tdVertical">
                    <label id="regardingIDLabel" for="regardingID">Regarding:</label>
                </td>
                <td class="tdData">
<?php if ($this->isJobOrdersMode): ?>
                    <span><?php $this->_($this->pipelineData['title']); ?></span>
<?php else: ?>
                    <select id="regardingID" name="regardingID" class="inputbox" style="width: 150px;" onchange="AS_onRegardingChange(statusesArray, jobOrdersArray, 'regardingID', 'statusID', 'statusTR', 'sendEmailCheckTR', 'triggerEmail', 'triggerEmailSpan', 'changeStatus', 'changeStatusSpanA', 'changeStatusSpanB');">
                        <option value="-1">General</option>

                        <?php foreach ($this->pipelineRS as $rowNumber => $pipelinesData): ?>
                            <?php if ($this->selectedJobOrderID == $pipelinesData['jobOrderID']): ?>
                                <option selected="selected" value="<?php $this->_($pipelinesData['jobOrderID']) ?>"><?php $this->_($pipelinesData['title']) ?></option>
                            <?php else: ?>
                                <option value="<?php $this->_($pipelinesData['jobOrderID']) ?>"><?php $this->_($pipelinesData['title']) ?> (<?php $this->_($pipelinesData['companyName']) ?>)</option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
<?php endif; ?>
                </td>
            </tr>

            <tr id="statusTR" <?php if ($this->onlyScheduleEvent): ?>style="display:none;"<?php endif; ?>>
                <td class="tdVertical">
                    <label id="statusIDLabel" for="statusID">Status:</label>
                </td>
                <td class="tdData">
                    <input type="checkbox" name="changeStatus" id="changeStatus" style="margin-left: 0px" onclick="AS_onChangeStatusChange('changeStatus', 'statusID', 'changeStatusSpanB');"<?php if ($this->selectedJobOrderID == -1 || $this->onlyScheduleEvent): ?> disabled<?php endif; ?> />
                    <span id="changeStatusSpanA"<?php if ($this->selectedJobOrderID == -1): ?> style="color: #aaaaaa;"<?php endif;?>>Change Status</span><br />

                    <div id="changeStatusDiv" style="margin-top: 4px;">
                        <select id="statusID" name="statusID" class="inputbox" style="width: 150px;" onchange="AS_onStatusChange(statusesArray, jobOrdersArray, 'regardingID', 'statusID', 'sendEmailCheckTR', 'triggerEmailSpan', 'activityNote', 'activityTypeID', <?php if ($this->isJobOrdersMode): echo $this->selectedJobOrderID; else: ?>null<?php endif; ?>, 'customMessage', 'origionalCustomMessage', 'triggerEmail', statusesArrayString, jobOrdersArrayStringTitle, jobOrdersArrayStringCompany, statusTriggersEmailArray, 'emailIsDisabled');" disabled>
                            <option value="-1">(Select a Status)</option>

                            <?php if ($this->selectedStatusID == -1): ?>
                                <?php foreach ($this->statusRS as $rowNumber => $statusData): ?>
                                    <option value="<?php $this->_($statusData['statusID']) ?>"><?php $this->_($statusData['status']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php foreach ($this->statusRS as $rowNumber => $statusData): ?>
                                    <option <?php if ($this->selectedStatusID == $statusData['statusID']): ?>selected <?php endif; ?>value="<?php $this->_($statusData['statusID']) ?>"><?php $this->_($statusData['status']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <span id="changeStatusSpanB" style="color: #aaaaaa;">&nbsp;*</span>&nbsp;&nbsp;
                        <span id="triggerEmailSpan" style="display: none;"><input type="checkbox" name="triggerEmail" id="triggerEmail" onclick="AS_onSendEmailChange('triggerEmail', 'sendEmailCheckTR', 'visibleTR');" />Send E-Mail Notification to Candidate</span>
                        <span id="triggerValidSpan" style="display: none;"><br><input type="checkbox" name="triggerValid" id="triggerValid" /><b>檢查事項 *<br>1. 一年內沒投遞過<br>2. Salary Structure 是否寫進 Misc Notes 了</b></span>
                        <span id="triggerPersonalAgreementSpan" style="display: none;" data-personal-agreement-count=0 data-personal-agreement=<?php if ($this->personalAgreementPresent): ?>"1" <?php else: ?>"0"<?php endif; ?> ><br><input type="checkbox" name="triggerPersonalAgreement" id="triggerPersonalAgreement" /><b>Prepare Personal Agreement later.</b></span>
                        <span id="triggerCurrentDesiredPayRatioSpan" style="display: none;" data-current-desired-pay-ratio-count=0 data-current-pay=<?php echo($this->currentPay) ?> data-desired-pay=<?php echo($this->desiredPay) ?> ><br><input type="checkbox" name="triggerCurrentDesiredPayRatio" id="triggerCurrentDesiredPayRatio" /><b>Confirm that Desired Pay is 1.2 times larger than Current Pay.</b></span>
                        <span id="triggerCurrentPaySpan" style="display: none;" data-current-pay-count=0 data-current-pay=<?php echo($this->currentPay) ?> ><br><input type="checkbox" name="triggerCurrentPay" id="triggerCurrentPay" /><b>Prepare Current Pay later.</b></span>
                        <span id="triggerDesiredPaySpan" style="display: none;" data-desired-pay-count=0 data-desired-pay=<?php echo($this->desiredPay) ?> ><br><input type="checkbox" name="triggerDesiredPay" id="triggerDesiredPay" /><b>Prepare Desired Pay later.</b></span>
                        <span id="triggerValidEmailSpan" style="display: none;" data-email-count=0 data-email=<?php echo($this->email1) ?> ><br><input type="checkbox" name="triggerValidEmail" id="triggerValidEmail" /><b>Prepare Email later.</b></span>
                        <span id="triggerValidGenderSpan" style="display: none;" data-gender-count=0 data-gender=<?php echo($this->gender) ?> ><br><input type="checkbox" name="triggerValidGender" id="triggerValidGender" /><b>Prepare Gender later.</b></span>
                        <span id="triggerValidNationalitySpan" style="display: none;" data-nationality-count=0 data-nationality=<?php echo($this->nationality) ?> ><br><input type="checkbox" name="triggerValidNationality" id="triggerValidNationality" /><b>Prepare Nationality later.</b></span>
                    </div>
                </td>
            </tr>

            <tr id="sendEmailCheckTR" style="display: none;">
                <td class="tdVertical">
                    <label id="triggerEmailLabel" for="triggerEmail">E-Mail:</label>
                </td>
                <td class="tdData">
                    Custom Message<br />
                    <input type="hidden" id="origionalCustomMessage" value="<?php $this->_($this->statusChangeTemplate); ?>" />
                    <input type="hidden" id="emailIsDisabled" value="<?php echo($this->emailDisabled); ?>" />
                    <textarea style="height:135px; width:375px;" name="customMessage" id="customMessage" cols="50" class="inputbox"></textarea>
                </td>
            </tr>
           <tr id="addActivityTR" <?php if ($this->onlyScheduleEvent): ?>style="display:none;"<?php endif; ?>>
                <td class="tdVertical">
                    <label id="addActivityLabel" for="addActivity">Activity:</label>
                </td>
                <td class="tdData">
                    <input type="checkbox" name="addActivity" id="addActivity" style="margin-left: 0px;"<?php if (!$this->onlyScheduleEvent): ?> checked="checked"<?php endif; ?> onclick="AS_onAddActivityChange('addActivity', 'activityTypeID', 'activityNote', 'addActivitySpanA', 'addActivitySpanB');" />Log an Activity<br />
                    <div id="activityNoteDiv" style="margin-top: 4px;">
                        <span id="addActivitySpanA">Activity Type</span><br />
                        <select id="activityTypeID" name="activityTypeID" class="inputbox" style="width: 150px; margin-bottom: 4px;" onclick="AS_onActivityChange('addActivity', 'activityTypeID', 'activityNote', 'addActivitySpanA', 'addActivitySpanB');">
                            <option selected="selected" value="<?php echo(ACTIVITY_NOTE); ?>">Note</option>
                            <option value="<?php echo(ACTIVITY_CALL_COLD); ?>">Call (Cold)</option>
                            <option value="<?php echo(ACTIVITY_CALL_TALKED); ?>">Call (Talked)</option>
                            <option value="<?php echo(ACTIVITY_CALL_LVM); ?>">Call (LVM)</option>
                            <option value="<?php echo(ACTIVITY_CALL_MISSED); ?>">Call (Missed)</option>
                            <option value="<?php echo(ACTIVITY_EMAIL); ?>">E-Mail</option>
                            <option value="<?php echo(ACTIVITY_MEETING); ?>">Meeting</option>
                            <option value="<?php echo(ACTIVITY_OTHER); ?>">Other</option>
                            <option value="<?php echo(ACTIVITY_ARRANGE); ?>">Arrange</option>
                            <option value="<?php echo(ACTIVITY_CONFIRM); ?>">Confirm</option>
                            <option value="<?php echo(ACTIVITY_DRIFTING); ?>">Drifting</option>
                            <option value="<?php echo(ACTIVITY_IM_LINKEDIN); ?>">IM (Linkedin)</option>
                            <option value="<?php echo(ACTIVITY_IM_LINE); ?>">IM (Line)</option>
                            <option value="<?php echo(ACTIVITY_IM); ?>">IM</option>
                            <option value="<?php echo(ACTIVITY_INTERVIEW); ?>">Interview</option>
                        </select><br />
                                                
                        <span id="triggerInterviewSpan" style="display: none;"><b><a target="_black" href="https://docs.google.com/document/d/1qSIBtph2RZvqscrApepj4VYeEAin8tZuOAkI4N8ryv0">Interview Feedback Examples</a></b><br></span>
                        
                        <span id="addActivitySpanB">Activity Notes</span><br />
                        <textarea name="activityNote" id="activityNote" cols="50" rows="10" style="margin-bottom: 4px;" class="inputbox"></textarea>
                    </div>
                </td>
            </tr>

            <tr id="scheduleEventTR">
                <td class="tdVertical">
                    <label id="scheduleEventLabel" for="scheduleEvent">Schedule Event:</label>
                </td>
                <td class="tdData">
                    <input type="checkbox" name="scheduleEvent" id="scheduleEvent" style="margin-left: 0px; <?php if ($this->onlyScheduleEvent): ?>display:none;<?php endif; ?>" onclick="AS_onScheduleEventChange('scheduleEvent', 'scheduleEventDiv');"<?php if ($this->onlyScheduleEvent): ?> checked="checked"<?php endif; ?> /><?php if (!$this->onlyScheduleEvent): ?>Schedule Event<?php endif; ?>
                    <div id="scheduleEventDiv" <?php if (!$this->onlyScheduleEvent): ?>style="display:none;"<?php endif; ?>>
                        <table style="border: none; margin: 0px; padding: 0px;">
                            <tr>
                                <td valign="top">
                                    <div style="margin-bottom: 4px;">
                                        <select id="eventTypeID" name="eventTypeID" class="inputbox" style="width: 150px;">
                                            <?php foreach ($this->calendarEventTypes as $eventType): ?>
                                                <option <?php if ($eventType['typeID'] == CALENDAR_EVENT_INTERVIEW): ?>selected="selected" <?php endif; ?>value="<?php echo($eventType['typeID']); ?>"><?php $this->_($eventType['description']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div style="margin-bottom: 4px;">
                                        <script type="text/javascript">DateInput('dateAdd', true, 'MM-DD-YY', '', -1);</script>
                                    </div>

                                    <div style="margin-bottom: 4px;">
                                        <input type="radio" name="allDay" id="allDay0" value="0" style="margin-left: 0px" checked="checked" onchange="AS_onEventAllDayChange('allDay1');" />
                                        <select id="hour" name="hour" class="inputbox" style="width: 40px;">
                                            <?php for ($i = 1; $i <= 12; ++$i): ?>
                                                <option value="<?php echo($i); ?>" <?php if ($i == 9) echo("selected=\"selected\""); ?> ><?php echo(sprintf('%02d', $i)); ?></option>
                                            <?php endfor; ?>
                                        </select>&nbsp;
                                        <select id="minute" name="minute" class="inputbox" style="width: 40px;">
                                            <?php for ($i = 0; $i <= 45; $i = $i + 15): ?>
                                                <option value="<?php echo(sprintf('%02d', $i)); ?>">
                                                    <?php echo(sprintf('%02d', $i)); ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>&nbsp;
                                        <select id="meridiem" name="meridiem" class="inputbox" style="width: 45px;">
                                            <option value="AM">AM</option>
                                            <option value="PM">PM</option>
                                        </select>
                                    </div>

                                    <div style="margin-bottom: 4px;">
                                        <input type="radio" name="allDay" id="allDay1" value="1" style="margin-left: 0px" onchange="AS_onEventAllDayChange('allDay1');" />All Day / No Specific Time<br />
                                    </div>

                                    <div style="margin-bottom: 4px;">
                                        <input type="checkBox" name="publicEntry" id="publicEntry" style="margin-left: 0px" />Public Entry
                                    </div>
                                </td>

                                <td valign="top">
                                    <div style="margin-bottom: 4px;">
                                        <label id="titleLabel" for="title">Title&nbsp;*</label><br />
                                        <input type="text" class="inputbox" name="title" id="title" style="width: 180px;" />
                                    </div>

                                    <div style="margin-bottom: 4px;">
                                        <label id="durationLabel" for="duration">Length:</label>
                                        <br />
                                        <select id="duration" name="duration" class="inputbox" style="width: 180px;">
                                            <option value="15">15 minutes</option>
                                            <option value="30">30 minutes</option>
                                            <option value="45">45 minutes</option>
                                            <option value="60" selected="selected">1 hour</option>
                                            <option value="90">1.5 hours</option>
                                            <option value="120">2 hours</option>
                                            <option value="180">3 hours</option>
                                            <option value="240">4 hours</option>
                                            <option value="300">More than 4 hours</option>
                                        </select>
                                    </div>
                                    
                                    <div style="margin-bottom: 4px;">
                                        <label id="descriptionLabel" for="description">Description</label><br />
                                        <textarea name="description" id="description" cols="20" class="inputbox" style="width: 180px; height:60px;"></textarea>
                                    </div>

                                    <div <?php if (!$this->allowEventReminders): ?>style="display:none;"<?php endif; ?>>
                                        <input type="checkbox" name="reminderToggle" onclick="if (this.checked) document.getElementById('reminderArea').style.display = ''; else document.getElementById('reminderArea').style.display = '';">&nbsp;<label>Set Reminder</label><br />
                                    </div>
                                    
                                    <div style="display:none;" id="reminderArea">
                                        <div>
                                            <label>E-Mail To:</label><br />
                                            <input type="text" id="sendEmail" name="sendEmail" class="inputbox" style="width: 150px" value="<?php $this->_($this->userEmail); ?>" />
                                        </div>
                                        <div>
                                            <label>Time:</label><br />
                                            <select id="reminderTime" name="reminderTime" style="width: 150px">
                                                <option value="15">15 min early</option>
                                                <option value="30">30 min early</option>
                                                <option value="45">45 min early</option>
                                                <option value="60">1 hour early</option>
                                                <option value="120">2 hours early</option>
                                                <option value="1440">1 day early</option>
                                                <option value="2880">2 days early</option>
                                                <option value="4320">3 days early</option>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>

        </table>
        <input type="submit" class="button" name="submit" id="submit" value="Save" />&nbsp;
<?php if ($this->isJobOrdersMode): ?>
        <input type="button" class="button" name="close" value="Cancel" onclick="parentGoToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php echo($this->selectedJobOrderID); ?>');" />
<?php else: ?>
        <input type="button" class="button" name="close" value="Cancel" onclick="parentGoToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php echo($this->candidateID); ?>');" />
<?php endif; ?>
    </form>

    <script type="text/javascript">
        document.changePipelineStatusForm.activityNote.focus();
    </script>

<?php else: ?>
    <?php if (!$this->changesMade): ?>
        <p>No changes have been made.</p>
    <?php else: ?>
         <?php if (!$this->onlyScheduleEvent): ?>
            <?php //FIXME: E-mail stuff. ?>
            <?php if ($this->statusChanged): ?>
                <p>The candidate's status has been changed from <span class="bold"><?php $this->_($this->oldStatusDescription); ?></span> to <span class="bold"><?php $this->_($this->newStatusDescription); ?></span>.</p>
            <?php else: ?>
                <p>The candidate's status has not been changed.</p>
            <?php endif; ?>

            <?php if ($this->activityAdded): ?>
                <?php if (!empty($this->activityDescription)): ?>
                    <p>An activity entry of type <span class="bold"><?php $this->_($this->activityType); ?></span> has been added with the following note: &quot;<?php echo($this->activityDescription); ?>&quot;.</p>
                <?php else: ?>
                    <p>An activity entry of type <span class="bold"><?php $this->_($this->activityType); ?></span> has been added with no notes.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>No activity entries have been added.</p>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php echo($this->eventHTML); ?>

    <?php echo($this->notificationHTML); ?>

    <form>
<?php if ($this->isJobOrdersMode): ?>
        <input type="button" name="close" class="button" value="Close" onclick="parentGoToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php echo($this->regardingID); ?>');" />
<?php else: ?>
        <input type="button" name="close" class="button" value="Close" onclick="parentGoToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php echo($this->candidateID); ?>');" />
<?php endif; ?>
    </form>
<?php endif; ?>

    </body>
</html>
