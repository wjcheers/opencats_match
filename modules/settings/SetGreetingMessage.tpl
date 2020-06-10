<?php /* $Id: SetGreetingMessage.tpl 1927 2007-02-22 06:03:24Z will $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'js/sorttable.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: My Profile</h2></td>
                </tr>
            </table>

            <p class="note">Set Greeting Message</p>

            <p>%CANDFIRSTNAME% => First Name</BR>
            %CANDLASTNAME%  => Last Name</BR>
            %CANDFULLNAME%  => Full Name</BR>
            %CANDCHNAME%    => Chinese Name</p>
            
            <form name="setGreetingMessageForm" id="setGreetingMessageForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=setGreetingMessage" method="post">
                <input type="hidden" name="postback" id="postback" value="postback" />

                <table class="searchTable">
                    <tr>
                        <td colspan="2">
                            <span class="bold">Set Greeting Message</span>
                            <br />
                            <br />
                            <span id='greetingErrorMessage' style="font:smaller; color: red">
                                <?php if (isset($this->errorMessage)): ?>
                                        <?php $this->_($this->errorMessage); ?>
                                <?php endif; ?>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <label id="greetingMessageTitleLabel" for="greetingMessageTitle">Greeting Message Title:</label>&nbsp;
                        </td>
                        <td>
                            <textarea class="inputbox" id="greetingMessageTitle" name="greetingMessageTitle" rows="1" cols="80" ><?php $this->_($this->data['greetingMessageTitle']); ?>
                            </textarea>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <label id="greetingMessageBodyLabel" for="greetingMessageBody">Greeting Message Body</label>&nbsp;
                        </td>
                        <td>
                            <textarea class="inputbox" id="greetingMessageBody" name="greetingMessageBody" rows="30" cols="80" ><?php $this->_($this->data['greetingMessageBody']); ?>
                            </textarea>                            
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <br />
                            <input type="submit" class="button" id="setGreetingMessage" name="setGreetingMessage" value="Set Greeting Message" />
                            <input type="reset"  class="button" id="reset"          name="reset"          value="Reset" />
                            <input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings';" />
                       </td>
                    </tr>
                </table>
            </form>

            <script type="text/javascript">
                document.setGreetingMessageForm.greetingMessageTitle.focus();
            </script>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
