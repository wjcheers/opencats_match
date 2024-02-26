<?php /* $Id: SendEmail.tpl 3078 2007-09-21 20:25:28Z will $ */ ?>
<?php TemplateUtility::printHeader('Candidates', array('modules/candidates/validator.js', 'js/searchSaved.js', 'js/sweetTitles.js', 'js/searchAdvanced.js', 'js/highlightrows.js', 'js/export.js', 'tinymce')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/candidate.gif" width="24" height="24" border="0" alt="Candidates" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Candidates: Send E-mail</h2></td>
                </tr>
            </table>

            <p class="note">Send Candidates E-mail</p>

            <?php
            if($this->success == true)
            {
                ?>

                <br />
                <span style="font-size: 12pt; font-weight: 900;">
                Your e-mail has been sent to the following recipients:
                <blockquote>
                <?php
                echo $this->success_to;
                ?>
                </blockquote>
                </span>
                You may check your e-mail sent box to confirm the the results.


                <?php
            }
            else
            {
                $emailTo = '';
                $firstTo = '';
                $lastTo = '';
                $fullTo = '';
                $chTo = '';
                foreach($this->recipients as $recipient)
                {
                        if(strlen($recipient['email1']) > 0)
                        {
                            $eml = $recipient['email1'];
                            if($emailTo != '')
                            {
                                $emailTo .= ', ';
                                $firstTo .= ', ';
                                $lastTo .= ', ';
                                $fullTo .= ', ';
                                $chTo .= ', ';
                            }
                            $emailTo .= $eml;
                            $firstTo .= $recipient['firstName'];
                            $lastTo .= $recipient['lastName'];
                            $fullTo .= $recipient['candidateFullName'];
                            $chTo .= $recipient['chineseName'];
                        }
                        if(strlen($recipient['email2']) > 0)
                        {
                            $eml = $recipient['email2'];
                            if($emailTo != '')
                            {
                                $emailTo .= ', ';
                                $firstTo .= ', ';
                                $lastTo .= ', ';
                                $fullTo .= ', ';
                                $chTo .= ', ';
                            }
                            $emailTo .= $eml;
                            $firstTo .= $recipient['firstName'];
                            $lastTo .= $recipient['lastName'];
                            $fullTo .= $recipient['candidateFullName'];
                            $chTo .= $recipient['chineseName'];
                        }
                }
                $tabIndex = 1;
                ?>
                
                <p>%CANDFIRSTNAME% => First Name</BR>
                %CANDLASTNAME%  => Last Name</BR>
                %CANDFULLNAME%  => Full Name</BR>
                %CANDCHNAME%    => Chinese Name</p>
                
                <?php
                /*
                $greetingMessageName = unserialize($this->user[0]['greetingMessageName']);
                $greetingMessageTitle = unserialize($this->user[0]['greetingMessageTitle']);
                $greetingMessageBody = unserialize($this->user[0]['greetingMessageBody']);
                */
                $greetingMessageName = json_decode($this->user[0]['greetingMessageName']);
                $greetingMessageTitle = json_decode($this->user[0]['greetingMessageTitle']);
                $greetingMessageBody = json_decode($this->user[0]['greetingMessageBody']);
                ?>
                
                Template:
                <table>
                <?php for ($i = 0; $i < 10; $i++): ?>
                <tr>
                <td>
                <a href="#" class="greetingTemplate" title="<?php echo $greetingMessageTitle[$i];?>" onclick="loadMailTemplate(<?php echo $i; ?>); return false;"><?php echo $greetingMessageName[$i];?></a>
                <span style="display: none;" id="greetingMessageTitle<?php echo $i;?>"><?php echo $greetingMessageTitle[$i];?></span>
                <span style="display: none;" id="greetingMessageBody<?php echo $i;?>"><?php echo $greetingMessageBody[$i];?></span>
                </td>
                </tr>
                <?php endfor; ?>
                </table>
                
            <table class="editTable" width="100%">
                <tr>
                    <td>
                        <form name="emailForm" id="emailForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=emailCandidates" method="post" onsubmit="result = checkEmailForm(document.emailForm); if(result) {document.getElementById('submit').disabled = true; document.getElementById('submit').value='Sending, please wait...';} return result;" autocomplete="off" enctype="multipart/form-data">
                        <input type="hidden" name="postback" id="postback" value="postback" />
                        <table>
                            <tr>
                                <td class="tdVertical" style="text-align: right;">
                                    To
                                </td>
                                <td class="tdData">
                                    <textarea class="inputbox" name="emailTo" rows="2", cols="90" tabindex="<?php echo($tabIndex++); ?>" style="width: 750px;" readonly><?php echo($emailTo); ?></textarea>
                                    <textarea style="display:none;" class="inputbox" name="firstTo" rows="2", cols="90" tabindex="99" style="width: 600px;" readonly><?php echo($firstTo); ?></textarea>
                                    <textarea style="display:none;" class="inputbox" name="lastTo" rows="2", cols="90" tabindex="99" style="width: 600px;" readonly><?php echo($lastTo); ?></textarea>
                                    <textarea style="display:none;" class="inputbox" name="fullTo" rows="2", cols="90" tabindex="99" style="width: 600px;" readonly><?php echo($fullTo); ?></textarea>
                                    <textarea style="display:none;" class="inputbox" name="chTo" rows="2", cols="90" tabindex="99" style="width: 600px;" readonly><?php echo($chTo); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="tdVertical" style="text-align: right;">
                                    <label id="emailSubjectLabel" for="emailSubject">Subject</label>
                                </td>
                                <td class="tdData">
                                    <textarea id="emailSubject" class="inputbox" name="emailSubject" rows="2", cols="90" tabindex="<?php echo($tabIndex++); ?>" style="width: 750px;"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="tdVertical" style="text-align: right;">
                                    <label id="emailBodyLabel" for="emailBody">Body</label>
                                </td>
                                <td class="tdData">
                                    <textarea id="emailBody" tabindex="<?php echo($tabIndex++); ?>" name="emailBody" rows="25" cols="90" style="width: 750px;" class="mceEditor"></textarea />
                                </td>
                            </tr>
                            <tr>
                                <td align="right" valign="top" colspan="2">
                                    <input type="submit" tabindex="<?php echo($tabIndex++); ?>" class="button" id="submit" value="Send E-Mail" />&nbsp;
                                    <input type="reset"  tabindex="<?php echo($tabIndex++); ?>" class="button" value="Reset" />&nbsp;
                                </td>
                            </tr>
                        </table>

                        </form>

                        <script type="text/javascript">
                        //document.emailForm.emailSubject.focus();
                        </script>
                    </td>
                </tr>
            </table>
            <?php
            }
            ?>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
