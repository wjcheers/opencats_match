<?php /* $Id: SetGreetingMessage.tpl 1927 2007-02-22 06:03:24Z will $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'js/sorttable.js', 'tinymce')); ?>
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

            <p class="note">Set Mail Template</p>

            <p>%CANDFIRSTNAME% => First Name</BR>
            %CANDLASTNAME%  => Last Name</BR>
            %CANDFULLNAME%  => Full Name</BR>
            %CANDCHNAME%    => Chinese Name</p>
            
            <form name="setGreetingMessageForm" id="setGreetingMessageForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=setGreetingMessage" method="post">
                <input type="hidden" name="postback" id="postback" value="postback" />

                <table class="searchTable">
                    <tr>
                        <td colspan="2">
                            <span class="bold">Set Mail Template</span>
                            <br />
                            <span id='greetingErrorMessage' style="font:smaller; color: red">
                                <?php if (isset($this->errorMessage)): ?>
                                        <?php $this->_($this->errorMessage); ?>
                                <?php endif; ?>
                            </span>
                        </td>
                    </tr>

                    <?php for ($i = 0; $i < 10; $i++): ?>
                        <tr>
                            <td>
                                <br />Template <?php echo $i+1;?>:
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label id="greetingMessageTitleLabel<?php echo $i;?>" for="greetingMessageName<?php echo $i;?>">Name:&nbsp;</label>
                            </td>
                            <td>
                                <input type="text" class="inputbox" id="greetingMessageName<?php echo $i;?>" name="greetingMessageName<?php echo $i;?>" value="<?php 
                                if( !empty($this->greetingMessageName[$i]))
                                {
                                    echo $this->greetingMessageName[$i];
                                }
                                ?>" style="width: 750px;">
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label id="greetingMessageTitleLabel<?php echo $i;?>" for="greetingMessageTitle<?php echo $i;?>">Title:&nbsp;</label>
                            </td>
                            <td>
                                <textarea class="inputbox" id="greetingMessageTitle<?php echo $i;?>" name="greetingMessageTitle<?php echo $i;?>" rows="2" cols="80"  style="width: 750px;"><?php 
                                if( !empty($this->greetingMessageTitle[$i]))
                                {
                                    echo $this->greetingMessageTitle[$i];
                                }
                                ?></textarea>    
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label id="greetingMessageBodyLabel<?php echo $i;?>" for="greetingMessageBody<?php echo $i;?>">Message:&nbsp;</label>
                            </td>
                            <td>
                                <textarea class="mceEditor" id="greetingMessageBody<?php echo $i;?>" name="greetingMessageBody<?php echo $i;?>" rows="10" cols="80" >
                                <?php
                                if( !empty($this->greetingMessageBody[$i]))
                                {
                                    echo $this->greetingMessageBody[$i];
                                }
                                ?>
                                </textarea>                            
                            </td>
                        </tr>
                    <?php endfor; ?>
                   
                    <tr>
                        <td colspan="2">
                            <br />
                            <input type="submit" class="button" id="setGreetingMessage" name="setGreetingMessage" value="Set Mail Template" />
                            <input type="reset"  class="button" id="reset"          name="reset"          value="Reset" />
                            <input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings';" />
                       </td>
                    </tr>
                </table>
            </form>

            <script type="text/javascript">
                //document.setGreetingMessageForm.greetingMessageTitle.focus();
            </script>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
