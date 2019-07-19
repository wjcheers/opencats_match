<?php /* $Id: AddActivityScheduleEventModal.tpl 3093 2007-09-24 21:09:45Z brian $ */ ?>

<?php TemplateUtility::printModalHeader('Companies', array('modules/companies/activityvalidator.js', 'js/activity.js'), 'Companies: Log Activity'); ?>

<?php if (!$this->isFinishedMode): ?>

<script type="text/javascript">
</script>

    <form name="logActivityForm" id="logActivityForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=addActivityScheduleEvent" method="post" autocomplete="off">
        <input type="hidden" name="postback" id="postback" value="postback" />
        <input type="hidden" id="companyID" name="companyID" value="<?php echo($this->companyID); ?>" />

        <table class="editTable" width="560">

           <tr id="addActivityTR">
                <td class="tdVertical">
                    <label id="addActivityLabel" for="addActivity">Activity:</label>
                </td>
                <td class="tdData">
                    <input type="checkbox" name="addActivity" id="addActivity" style="margin-left: 0px;" checked onclick="AS_onAddActivityChange('addActivity', 'activityTypeID', 'activityNote', 'addActivitySpanA', 'addActivitySpanB');" />Log an Activity<br />
                    <div id="activityNoteDiv" style="margin-top: 4px;">
                        <span id="addActivitySpanA">Activity Type</span><br />
                        <select id="activityTypeID" name="activityTypeID" class="inputbox" style="width: 150px; margin-bottom: 4px;">
                            <option value="<?php echo(ACTIVITY_CALL); ?>">Call</option>
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
                            <option <?php if($this->activityType == 'Interview') echo 'selected="selected"'; ?> value="<?php echo(ACTIVITY_INTERVIEW); ?>">Interview</option>
                        </select><br />
                        <span id="addActivitySpanB">Activity Notes</span><br />
                        <textarea name="activityNote" id="activityNote" cols="50" rows="10" style="margin-bottom: 4px;" class="inputbox"></textarea>
                    </div>
                </td>
            </tr>


        </table>
        <input type="submit" class="button" name="submit" id="submit" value="Save" />&nbsp;
        <input type="button" class="button" name="close" value="Cancel" onclick="parentGoToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php echo($this->companyID); ?>');" />
    </form>

    <script type="text/javascript">
        document.logActivityForm.activityNote.focus();
    </script>

<?php else: ?>
    <?php if (!$this->changesMade): ?>
        <p>No changes have been made.</p>
    <?php else: ?>
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
    
    <form>
        <input type="button" name="close" class="button" value="Close" onclick="parentGoToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php echo($this->companyID); ?>');" />
    </form>
<?php endif; ?>

    </body>
</html>
