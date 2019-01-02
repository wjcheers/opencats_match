<?php /* $Id: Edit.tpl 3695 2007-11-26 22:01:04Z brian $ */ ?>
<?php TemplateUtility::printHeader('Candidates', array('modules/candidates/validator.js', 'js/sweetTitles.js', 'js/listEditor.js', 'js/candidate.js', 'js/doubleListEditor.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>

<?php $extraFieldRS = array();?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/candidate.gif" width="24" height="24" border="0" alt="Candidates" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Candidates: Edit</h2></td>
               </tr>
            </table>

            <p class="note">Edit Candidate</p>

            <table style="font-weight:bold; border: 1px solid #000; background-color: #ffed1a; padding:5px; display:none; margin-bottom:7px;" width="1225" class="candidateAlreadyInSystemTable">
                <tr>
                    <td class="tdVertical">
                        This profile may already be in the system.&nbsp;&nbsp;Possible duplicate candidate profile:&nbsp;&nbsp;
                        <a href="javascript:void(0);" onclick="window.open('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID='+candidateIsAlreadyInSystemID);">
                            <img src="images/new_window.gif" border="0" />
                            <img src="images/candidate_small.gif" border="0" />
                            <span class="candidateAlreadyInSystemName"></span>
                        </a>
                    </td>
                </tr>
            </table>

            <form name="editCandidateForm" id="editCandidateForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=edit" method="post" onsubmit="return (checkEditForm(document.editCandidateForm) && onSubmitEmailInSystem() && onSubmitPhoneInSystem() && onSubmitLinkInSystem());" autocomplete="off">
                <input type="hidden" name="postback" id="postback" value="postback" />
                <input type="hidden" id="candidateID" name="candidateID" value="<?php $this->_($this->data['candidateID']); ?>" />

                <table class="editTable" width="1000">
                    <tr>
                        <td class="tdVertical" valign="top" style="height: 28px;">
                            <label id="isHotLabel" for="isHot">Active:</label>
                        </td>
                        <td class="tdData" >
                            <input type="checkbox" id="isActive" name="isActive"<?php if ($this->data['isActive'] == 1): ?> checked<?php endif; ?> />
                            <img title="Unchecking this box indicates the candidate is inactive, and will no longer display on the resume search results." src="images/information.gif" alt="" width="16" height="16" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="firstNameLabel" for="firstName">First Name:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="firstName" name="firstName" value="<?php $this->_($this->data['firstName']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="middleNameLabel" for="middleName">Middle Name:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="middleName" name="middleName" value="<?php $this->_($this->data['middleName']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="lastNameLabel" for="lastName">Last Name:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="lastName" name="lastName" value="<?php $this->_($this->data['lastName']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="chineseNameLabel" for="chineseName">Chinese Name:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="chineseName" name="chineseName" value="<?php $this->_($this->data['chineseName']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="nationalityLabel" for="nationality">Nationality:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="nationality" name="nationality" value="<?php $this->_($this->data['nationality']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="email1Label" for="email1">E-Mail:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="email1" name="email1" value="<?php $this->_($this->data['email1']); ?>" style="width: 150px;" onchange="checkEmailAlreadyInSystem(this.value, <?php echo($this->candidateID); ?>);" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="email2Label" for="email2">2nd E-Mail:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="email2" name="email2" value="<?php $this->_($this->data['email2']); ?>" style="width: 150px;" onchange="checkEmailAlreadyInSystem(this.value, <?php echo($this->candidateID); ?>);" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="phoneHomeLabel" for="phoneHome">Home Phone:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="phoneHome" name="phoneHome" value="<?php $this->_($this->data['phoneHome']); ?>" style="width: 150px;" onchange="checkPhoneAlreadyInSystem(this.value, <?php echo($this->candidateID); ?>);" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="phoneCellLabel" for="phoneCell">Cell Phone:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="phoneCell" name="phoneCell" value="<?php $this->_($this->data['phoneCell']); ?>" style="width: 150px;" onchange="checkPhoneAlreadyInSystem(this.value, <?php echo($this->candidateID); ?>);" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="phoneWorkLabel" for="phoneWork">Work Phone:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="phoneWork" name="phoneWork" value="<?php $this->_($this->data['phoneWork']); ?>" style="width: 150px;" onchange="checkPhoneAlreadyInSystem(this.value, <?php echo($this->candidateID); ?>);" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="canRelocateLabel" for="canRelocate">Best Time To Call:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="bestTimeToCall" name="bestTimeToCall" value="<?php $this->_($this->data['bestTimeToCall']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="webSiteLabel" for="webSite">Web Site:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="webSite" name="webSite" value="<?php $this->_($this->data['webSite']); ?>" style="width: 450px" onchange="checkLinkAlreadyInSystem(this.value, <?php echo($this->candidateID); ?>);" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="facebookLabel" for="facebook">Facebook:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="facebook" name="facebook" value="<?php $this->_($this->data['facebook']); ?>" style="width: 450px" onchange="checkLinkAlreadyInSystem(this.value, <?php echo($this->candidateID); ?>);" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="linkedinLabel" for="linkedin">Linkedin:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="linkedin" name="linkedin" value="<?php $this->_($this->data['linkedin']); ?>" style="width: 450px" onchange="checkLinkAlreadyInSystem(this.value, <?php echo($this->candidateID); ?>);" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="githubLabel" for="github">Github:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="github" name="github" value="<?php $this->_($this->data['github']); ?>" style="width: 450px" onchange="checkLinkAlreadyInSystem(this.value, <?php echo($this->candidateID); ?>);" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="googleplusLabel" for="googleplus">GooglePlus:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="googleplus" name="googleplus" value="<?php $this->_($this->data['googleplus']); ?>" style="width: 450px" onchange="checkLinkAlreadyInSystem(this.value, <?php echo($this->candidateID); ?>);" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="twitterLabel" for="twitter">Twitter:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="twitter" name="twitter" value="<?php $this->_($this->data['twitter']); ?>" style="width: 450px" onchange="checkLinkAlreadyInSystem(this.value, <?php echo($this->candidateID); ?>);" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="link1Label" for="link1">Link1:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="link1" name="link1" value="<?php $this->_($this->data['link1']); ?>" style="width: 450px" onchange="checkLinkAlreadyInSystem(this.value, <?php echo($this->candidateID); ?>);" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="link2Label" for="link2">Link2:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="link2" name="link2" value="<?php $this->_($this->data['link2']); ?>" style="width: 450px" onchange="checkLinkAlreadyInSystem(this.value, <?php echo($this->candidateID); ?>);" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="link3Label" for="link3">Link3:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="link3" name="link3" value="<?php $this->_($this->data['link3']); ?>" style="width: 450px" onchange="checkLinkAlreadyInSystem(this.value, <?php echo($this->candidateID); ?>);" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="sourceLabel" for="source">Source:</label>
                        </td>
                        <td class="tdData">
                            <select id="sourceSelect" name="source" class="inputbox" style="width: 150px;" onchange="if (this.value == 'edit') { listEditor('Sources', 'sourceSelect', 'sourceCSV', false, ''); this.value = '(none)'; } if (this.value == 'nullline') { this.value = '(none)'; }">
                                <option value="edit">(Edit Sources)</option>
                                <option value="nullline">-------------------------------</option>
                                <?php if ($this->sourceInRS == false): ?>
                                    <?php if ($this->data['source'] != '(none)'): ?>
                                        <option value="(none)">(None)</option>
                                    <?php endif; ?>
                                    <option value="<?php $this->_($this->data['source']); ?>" selected="selected"><?php $this->_($this->data['source']); ?></option>
                                <?php else: ?>
                                    <option value="(none)">(None)</option>
                                <?php endif; ?>
                                <?php foreach ($this->sourcesRS AS $index => $source): ?>
                                    <option value="<?php $this->_($source['name']); ?>" <?php if ($source['name'] == $this->data['source']): ?>selected<?php endif; ?>><?php $this->_($source['name']); ?></option>
                                <?php endforeach; ?>
                            </select>

                            <input type="hidden" id="sourceCSV" name="sourceCSV" value="<?php $this->_($this->sourcesString); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="addressLabel" for="address1">Address:</label>
                        </td>
                        <td class="tdData">
                            <textarea class="inputbox" id="address" name="address" style="width: 150px;"><?php $this->_($this->data['address']); ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="cityLabel" for="city">City:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="city" name="city" value="<?php $this->_($this->data['city']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="stateLabel" for="state">State:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="state" name="state" value="<?php $this->_($this->data['state']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <?php /*
                    <tr>
                        <td class="tdVertical">
                            <label id="zipLabel" for="zip">Postal Code:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="zip" name="zip" value="<?php $this->_($this->data['zip']); ?>" style="width: 150px;" />
                            <input type="button" class="button" onclick="CityState_populate('zip', 'ajaxIndicator');" value="Lookup" />
                            <img src="images/indicator2.gif" alt="AJAX" id="ajaxIndicator" style="vertical-align: middle; visibility: hidden; margin-left: 5px;" />
                        </td>
                    </tr>
                    */ ?>

                    <tr>
                        <td class="tdVertical" valign="top" style="height: 28px;">
                            <label id="isHotLabel" for="isHot">Hot Candidate:</label>
                        </td>
                        <td class="tdData" >
                            <input type="checkbox" id="isHot" name="isHot"<?php if ($this->data['isHot'] == 1): ?> checked<?php endif; ?> />

                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="ownerLabel" for="owner">Owner:</label>
                        </td>
                        <td class="tdData">
                            <select id="owner" name="owner" class="inputbox" style="width: 150px;" <?php if (!$this->emailTemplateDisabled): ?>onchange="document.getElementById('divOwnershipChange').style.display=''; <?php if ($this->canEmail): ?>document.getElementById('checkboxOwnershipChange').checked=true;<?php endif; ?>"<?php endif; ?>>
                                <option value="-1">None</option>

                                <?php foreach ($this->usersRS as $rowNumber => $usersData): ?>
                                    <?php if ($this->data['owner'] == $usersData['userID']): ?>
                                        <option selected="selected" value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                                    <?php else: ?>
                                        <option value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>&nbsp;*
                            <div style="display:none;" id="divOwnershipChange">
                                <input type="checkbox" name="ownershipChange" id="checkboxOwnershipChange" onclick="return false;" <?php if (!$this->canEmail): ?>disabled<?php endif; ?>> E-Mail new owner of change
                            </div>
                        </td>
                    </tr>

                     <tr>
                        <td class="tdVertical">
                            <label id="sourceLabel" for="image">Picture:</label>
                        </td>
                        <td class="tdData">
                            <input type="button" class="button" id="addImage" name="addImage" value="Edit Profile Picture" style="width:150px;" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addEditImage&amp;candidateID=<?php echo($this->candidateID); ?>', 400, 370, null); return false;" />&nbsp;
                        </td>
                    </tr>
                </table>
               
                <?php if($this->EEOSettingsRS['enabled'] == 1): ?>
                    <?php if(!$this->EEOSettingsRS['canSeeEEOInfo']): ?>
                        <table class="editTable" width="1000">
                            <tr>
                                <td>
                                    Editing EEO data is disabled.
                                </td>
                            </tr>
                        </tr>
                        <table class="editTable" width="1000" style="display:none;">
                    <?php else: ?>
                        <table class="editTable" width="1000">
                    <?php endif; ?>               

                         <?php if ($this->EEOSettingsRS['genderTracking'] == 1): ?>
                             <tr>
                                <td class="tdVertical">
                                    <label id="canRelocateLabel" for="canRelocate">Gender:</label>
                                </td>
                                <td class="tdData">
                                    <select id="gender" name="gender" class="inputbox" style="width:200px;">
                                        <option value="">----</option>
                                        <option value="m" <?php if (strtolower($this->data['eeoGender']) == 'm') echo('selected'); ?>>Male</option>
                                        <option value="f" <?php if (strtolower($this->data['eeoGender']) == 'f') echo('selected'); ?>>Female</option>
                                    </select>
                                </td>
                             </tr>
                         <?php endif; ?>
                         <?php if ($this->EEOSettingsRS['ethnicTracking'] == 1): ?>
                             <tr>
                                <td class="tdVertical">
                                    <label id="canRelocateLabel" for="canRelocate">Ethnic Background:</label>
                                </td>
                                <td class="tdData">
                                    <select id="race" name="race" class="inputbox" style="width:200px;">
                                        <option value="">----</option>
                                        <option value="1" <?php if ($this->data['eeoEthnicTypeID'] == 1) echo('selected'); ?>>American Indian</option>
                                        <option value="2" <?php if ($this->data['eeoEthnicTypeID'] == 2) echo('selected'); ?>>Asian or Pacific Islander</option>
                                        <option value="3" <?php if ($this->data['eeoEthnicTypeID'] == 3) echo('selected'); ?>>Hispanic or Latino</option>
                                        <option value="4" <?php if ($this->data['eeoEthnicTypeID'] == 4) echo('selected'); ?>>Non-Hispanic Black</option>
                                        <option value="5" <?php if ($this->data['eeoEthnicTypeID'] == 5) echo('selected'); ?>>Non-Hispanic White</option>
                                    </select>
                                </td>
                             </tr>
                         <?php endif; ?>
                         <?php if ($this->EEOSettingsRS['veteranTracking'] == 1): ?>
                             <tr>
                                <td class="tdVertical">
                                    <label id="canRelocateLabel" for="canRelocate">Vetran Status:</label>
                                </td>
                                <td class="tdData">
                                    <select id="veteran" name="veteran" class="inputbox" style="width:200px;">
                                        <option value="">----</option>
                                        <option value="1" <?php if ($this->data['eeoVeteranTypeID'] == 1) echo('selected'); ?>>No</option>
                                        <option value="2" <?php if ($this->data['eeoVeteranTypeID'] == 2) echo('selected'); ?>>Eligible Veteran</option>
                                        <option value="3" <?php if ($this->data['eeoVeteranTypeID'] == 3) echo('selected'); ?>>Disabled Veteran</option>
                                        <option value="4" <?php if ($this->data['eeoVeteranTypeID'] == 4) echo('selected'); ?>>Eligible and Disabled</option>
                                    </select>
                                </td>
                             </tr>
                         <?php endif; ?>
                         <?php if ($this->EEOSettingsRS['disabilityTracking'] == 1): ?>
                             <tr>
                                <td class="tdVertical">
                                    <label id="canRelocateLabel" for="canRelocate">Disability Status:</label>
                                </td>
                                <td class="tdData">
                                    <select id="disability" name="disability" class="inputbox" style="width:200px;">
                                        <option value="">----</option>
                                        <option value="No" <?php if ($this->data['eeoDisabilityStatus'] == 'No') echo('selected'); ?>>No</option>
                                        <option value="Yes" <?php if ($this->data['eeoDisabilityStatus'] == 'Yes') echo('selected'); ?>>Yes</option>
                                    </select>
                                </td>
                             </tr>
                         <?php endif; ?>
                    </table>
                <?php endif; ?>

                <table style="font-weight:bold; border: 1px solid #000; background-color: #ffed1a; padding:5px; display:none; margin-bottom:7px;" width="1225" class="candidateAlreadyInSystemTable">
                    <tr>
                        <td class="tdVertical">
                            This profile may already be in the system.&nbsp;&nbsp;Possible duplicate candidate profile:&nbsp;&nbsp;
                            <a href="javascript:void(0);" onclick="window.open('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID='+candidateIsAlreadyInSystemID);">
                                <img src="images/new_window.gif" border="0" />
                                <img src="images/candidate_small.gif" border="0" />
                                <span class="candidateAlreadyInSystemName"></span>
                            </a>
                        </td>
                    </tr>
                </table>
                
                <table class="editTable" width="1000">
                    <tr>
                        <td class="tdVertical">
                            <label id="canRelocateLabel" for="canRelocate">Can Relocate:</label>
                        </td>
                        <td class="tdData">
                            <input type="checkbox" id="canRelocate" name="canRelocate"<?php if ($this->data['canRelocate'] == 1): ?> checked<?php endif; ?> />
                        </td>
                    </tr>


                    <tr>
                        <td class="tdVertical">
                            <label id="dateAvailableLabel" for="dateAvailable">Date Available:</label>
                        </td>
                        <td class="tdData">
                            <?php if (!empty($this->data['dateAvailable'])): ?>
                                <script type="text/javascript">DateInput('dateAvailable', false, 'MM-DD-YY', '<?php echo($this->data['dateAvailableMDY']); ?>', -1);</script>
                            <?php else: ?>
                                <script type="text/javascript">DateInput('dateAvailable', false, 'MM-DD-YY', '', -1);</script>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="currentEmployerLabel" for="currentEmployer">Current Employer:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="currentEmployer" name="currentEmployer" value="<?php $this->_($this->data['currentEmployer']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="jobTitleLabel" for="jobTitle">Job Title:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="jobTitle" name="jobTitle" value="<?php $this->_($this->data['jobTitle']); ?>" style="width: 150px;" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="currentPayLabel" for="currentEmployer">Current Pay:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" name="currentPay" id="currentPay" value="<?php $this->_($this->data['currentPay']); ?>" class="inputbox" style="width: 150px" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="desiredPayLabel" for="currentEmployer">Desired Pay:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" name="desiredPay" id="desiredPay" value="<?php $this->_($this->data['desiredPay']); ?>" class="inputbox" style="width: 150px" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="keySkillsLabel" for="keySkills">Key Skills:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="keySkills" name="keySkills" value="<?php $this->_($this->data['keySkills']); ?>" style="width: 700px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="extraGenderLabel" for="extraGender">Gender:</label>
                        </td>
                        <td class="tdData">
                            <select id="extraGender" class="selectBox" name="extraGender" style="width: 150px;">
                                <option value=""></option>
                                <option value="Male"<?php if($this->data['extraGender'] == 'Male') echo ' selected=""'; ?>>Male</option>
                                <option value="Female"<?php if($this->data['extraGender'] == 'Female') echo ' selected=""'; ?>>Female</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="maritalStatusLabel" for="maritalStatus">Marital Status:</label>
                        </td>
                        <td class="tdData">                        
                            <select id="maritalStatus" class="selectBox" name="maritalStatus" style="width: 150px;">
                                <option value=""></option>
                                <option value="Single"<?php if($this->data['maritalStatus'] == 'Single') echo ' selected=""'; ?>>Single</option>
                                <option value="Married"<?php if($this->data['maritalStatus'] == 'Married') echo ' selected=""'; ?>>Married</option>
                                <option value="Divorced"<?php if($this->data['maritalStatus'] == 'Divorced') echo ' selected=""'; ?>>Divorced</option>
                                <option value="Widowed"<?php if($this->data['maritalStatus'] == 'Widowed') echo ' selected=""'; ?>>Widowed</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="birthYearLabel" for="birthYear">Birth Year:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="birthYear" name="birthYear" value="<?php $this->_($this->data['birthYear']); ?>" style="width: 150px;" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="highestDegreeLabel" for="highestDegree">Highest Education Degree:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="highestDegree" name="highestDegree" value="<?php $this->_($this->data['highestDegree']); ?>" style="width: 150px;" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="majorLabel" for="major">Major:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="major" name="major" value="<?php $this->_($this->data['major']); ?>" style="width: 150px;" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="lineLabel" for="line">Line:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="line" name="line" value="<?php $this->_($this->data['line']); ?>" style="width: 150px;" onchange="checkSocialMediaAlreadyInSystem('line', this.value);" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="qqLabel" for="qq">QQ:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="qq" name="qq" value="<?php $this->_($this->data['qq']); ?>" style="width: 150px;" onchange="checkSocialMediaAlreadyInSystem('qq', this.value);" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="skypeLabel" for="skype">Skype:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="skype" name="skype" value="<?php $this->_($this->data['skype']); ?>" style="width: 150px;" onchange="checkSocialMediaAlreadyInSystem('skype', this.value);" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="wechatLabel" for="wechat">Wechat:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="wechat" name="wechat" value="<?php $this->_($this->data['wechat']); ?>" style="width: 150px;" onchange="checkSocialMediaAlreadyInSystem('wechat', this.value);" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="functionsLabel" for="functions">Functions:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="functions" name="functions" value="<?php $this->_($this->data['functions']); ?>" style="width: 150px;" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="jobLevelLabel" for="jobLevel">Job Level:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="jobLevel" name="jobLevel" value="<?php $this->_($this->data['jobLevel']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    
                    <?php for ($i = 0; $i < count($this->extraFieldRS); $i++): ?>
                    <?php if (!isset($extraFieldRS[$i])): ?>
                        <tr>
                            <td class="tdVertical" id="extraFieldTd<?php echo($i); ?>">
                                <label id="extraFieldLbl<?php echo($i); ?>">
                                    <?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:
                                </label>
                            </td>
                            <td class="tdData" id="extraFieldData<?php echo($i); ?>">
                                <?php echo($this->extraFieldRS[$i]['editHTML']); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php endfor; ?>

                    <tr>
                        <td class="tdVertical">
                            <label id="notesLabel" for="notes">Misc. Notes:</label>
                        </td>
                        <td class="tdData">
                            <textarea class="inputbox" id="notes" name="notes" rows="5" style="width: 700px; height: 400px"><?php $this->_($this->data['notes']); ?></textarea>
                        </td>
                    </tr>
                </table>
                
                <table style="font-weight:bold; border: 1px solid #000; background-color: #ffed1a; padding:5px; display:none; margin-bottom:7px;" width="1225" class="candidateAlreadyInSystemTable">
                    <tr>
                        <td class="tdVertical">
                            This profile may already be in the system.&nbsp;&nbsp;Possible duplicate candidate profile:&nbsp;&nbsp;
                            <a href="javascript:void(0);" onclick="window.open('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID='+candidateIsAlreadyInSystemID);">
                                <img src="images/new_window.gif" border="0" />
                                <img src="images/candidate_small.gif" border="0" />
                                <span class="candidateAlreadyInSystemName"></span>
                            </a>
                        </td>
                    </tr>
                </table>
                
                <input type="submit" class="button" name="submit" id="submit" value="Save" />&nbsp;
                <input type="reset"  class="button" name="reset"  id="reset"  value="Reset" onclick="resetFormForeign();" />&nbsp;
                <input type="button" class="button" name="back"   id="back"   value="Back to Details" onclick="javascript:goToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php echo($this->candidateID); ?>');" />
            </form>

            <script type="text/javascript">
                document.editCandidateForm.firstName.focus();
            </script>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
