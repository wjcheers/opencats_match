<?php /* $Id: CreateAttachmentModal.tpl 3093 2007-09-24 21:09:45Z brian $ */ ?>
<?php TemplateUtility::printModalHeader('Job Order', array('modules/joborders/validator.js'), 'Create Job Order Attachment'); ?>

    <?php if (!$this->isFinishedMode): ?>
        <form name="createAttachmentForm" id="createAttachmentForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=createAttachment" method="post" onsubmit="result = checkCreateAttachmentForm(document.createAttachmentForm); if(result) {document.getElementById('submit').disabled = true; document.getElementById('submit').value='Creating, please wait...';} return result;" autocomplete="off" enctype="multipart/form-data">
            <input type="hidden" name="postback" id="postback" value="postback" />
            <input type="hidden" id="jobOrderID" name="jobOrderID" value="<?php echo($this->jobOrderID); ?>" />

            <table class="editTable">
                <tr>
                    <td class="tdVertical">Attachment:</td>
                    <td class="tdData"><input type="file" id="file" name="file" /></td>
                </tr>
            </table>
            <input type="submit" class="button" name="submit" id="submit" value="Create Attachment" />&nbsp;
            <input type="button" class="button" name="close" value="Cancel" onclick="parentHidePopWin();" />
        </form>
    <?php else: ?>
        <p>The file has been successfully attached.</p>

        <form>
            <input type="button" name="close" value="Close" onclick="parentHidePopWinRefresh();" />
        </form>
    <?php endif; ?>
    </body>
</html>