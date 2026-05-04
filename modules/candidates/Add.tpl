<?php /* $Id: Add.tpl 3746 2007-11-28 20:28:21Z andrew $ */ ?>
<?php if ($this->isModal): ?>
    <?php TemplateUtility::printModalHeader('Candidates', array('modules/candidates/validator.js', 'js/addressParser.js', 'js/listEditor.js',  'js/candidate.js', 'js/candidateParser.js'), 'Add New Candidate to This Job Order Pipeline'); ?>
<?php else: ?>
    <?php TemplateUtility::printHeader('Candidates', array('modules/candidates/validator.js', 'js/addressParser.js', 'js/listEditor.js',  'js/candidate.js', 'js/candidateParser.js')); ?>
    <?php TemplateUtility::printHeaderBlock(); ?>
    <?php TemplateUtility::printTabs($this->active, $this->subActive); ?>

    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">

            <table>
                <tr>
                    <td width="3%">
                        <img src="images/candidate.gif" width="24" height="24" alt="Candidates" style="border: none; margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Candidates: Add Candidate</h2></td>
                </tr>
            </table>

<?php endif; ?>

<?php $extraFieldRS = array();?>

            <p class="note<?php if ($this->isModal): ?>Unsized<?php endif; ?>">Basic Information</p>

            <table style="font-weight:bold; border: 1px solid #000; background-color: #ffed1a; padding:5px; display:none; margin-bottom:7px;" width="<?php if ($this->isModal): ?>100%<?php else: ?>1225<?php endif; ?>" class="candidateAlreadyInSystemTable">
                <tr>
                    <td class="tdVertical">
                        This profile may already be in the system.&nbsp;&nbsp;Possible duplicate candidate profile:&nbsp;&nbsp;
                        <a href="javascript:void(0);" onclick="return openCandidateAlreadyInSystemWithPaste('<?php echo(CATSUtility::getIndexName()); ?>');">
                            <img src="images/new_window.gif" border="0" />
                            <img src="images/candidate_small.gif" border="0" />
                            <span class="candidateAlreadyInSystemName"></span>
                        </a>
                    </td>
                </tr>
            </table>

            <?php if ($this->isModal): ?>
                <?php $URI = CATSUtility::getIndexName() . '?m=joborders&amp;a=addCandidateModal&jobOrderID=' . $this->jobOrderID; ?>
            <?php else: ?>
                <?php $URI = CATSUtility::getIndexName() . '?m=candidates&amp;a=add'; ?>
            <?php endif; ?>

            <form name="addCandidateForm" id="addCandidateForm" enctype="multipart/form-data" action="<?php echo($URI); ?>" method="post" onsubmit="result = (checkAddForm(document.addCandidateForm) && onSubmitEmailInSystem() && onSubmitPhoneInSystem() && onSubmitLinkInSystem()); if(result) {document.getElementById('submit').disabled = true; document.getElementById('submit').value='Sending, please wait...'; return;} return onSubmitFalse();" autocomplete="off" enctype="multipart/form-data">
                <?php if ($this->isModal): ?>
                    <input type="hidden" name="jobOrderID" id="jobOrderID" value="<?php echo($this->jobOrderID); ?>" />
                <?php endif; ?>
                <input type="hidden" name="postback" id="postback" value="postback" />
                <input type="hidden" name="aiParseLogID" id="aiParseLogID" value="<?php echo (isset($this->preassignedFields['aiParseLogID']) ? $this->preassignedFields['aiParseLogID'] : ''); ?>" />
                <input type="hidden" name="aiDocumentLanguage" id="aiDocumentLanguage" value="<?php echo (isset($this->preassignedFields['aiDocumentLanguage']) ? $this->preassignedFields['aiDocumentLanguage'] : ''); ?>" />
                <input type="hidden" name="aiResumeExtension" id="aiResumeExtension" value="<?php echo (isset($this->preassignedFields['aiResumeExtension']) ? $this->preassignedFields['aiResumeExtension'] : ''); ?>" />
                <input type="hidden" name="aiParseMode" id="aiParseMode" value="<?php echo (isset($this->preassignedFields['aiParseMode']) ? htmlspecialchars($this->preassignedFields['aiParseMode'], ENT_QUOTES) : 'full'); ?>" />
                <input type="hidden" name="aiJechoReportRequested" id="aiJechoReportRequested" value="<?php echo (!empty($this->preassignedFields['aiJechoReportRequested']) || (!isset($this->preassignedFields['aiJechoReportRequested']) && !empty($this->preassignedFields['aiSavePasteAsJechoReport']))) ? '1' : '0'; ?>" />
                <input type="hidden" name="extensionSourceURL" id="extensionSourceURL" value="<?php echo (isset($this->preassignedFields['extensionSourceURL']) ? htmlspecialchars($this->preassignedFields['extensionSourceURL'], ENT_QUOTES) : ''); ?>" />
                <input type="hidden" name="extensionSourcePageTitle" id="extensionSourcePageTitle" value="<?php echo (isset($this->preassignedFields['extensionSourcePageTitle']) ? htmlspecialchars($this->preassignedFields['extensionSourcePageTitle'], ENT_QUOTES) : ''); ?>" />
                <textarea name="aiJechoReportMarkdown" id="aiJechoReportMarkdown" style="display:none;"><?php echo (isset($this->preassignedFields['aiJechoReportMarkdown']) ? htmlspecialchars($this->preassignedFields['aiJechoReportMarkdown'], ENT_QUOTES) : ''); ?></textarea>

                <?php if (isset($this->preassignedFields['aiParseError']) && $this->preassignedFields['aiParseError'] != ''): ?>
                    <div style="margin-bottom: 10px; padding: 8px; border: 1px solid #cc9999; background-color: #fff3f3; color: #800000; width: <?php if ($this->isModal): ?>100%<?php else: ?>1200px<?php endif; ?>;">
                        AI resume parsing failed: <?php $this->_($this->preassignedFields['aiParseError']); ?>
                    </div>
                <?php endif; ?>

                <table class="editTable" width="<?php if ($this->isModal): ?>100%<?php else: ?>1225<?php endif; ?>">
                    <?php if ($this->isParsingEnabled): ?>
                    <tr>
                        <td class="tdVertical" colspan="2">
                            <img src="images/parser/manual.gif" border="0" />
                        </td>
                        <td class="tdVertical">
                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td align="left"><img src="images/parser/import.gif" border="0" /></td>
                                    <td align="right">
                                        &nbsp;
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="tdVertical">
                            <label id="firstNameLabel" for="firstName">First Name:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="1" name="firstName" id="firstName" class="inputbox" style="width: 150px" value="<?php if(isset($this->preassignedFields['firstName'])) $this->_($this->preassignedFields['firstName']); ?>" />&nbsp;*
                        </td>

                        <td rowspan="12" align="center" valign="top">
                            <?php
                            /* =========================================================================
                             * Resume widget (Add candidate) — Upload / Paste / Fast / Full
                             * =========================================================================
                             *
                             * IMPORTANT FOR FUTURE EDITS (human or AI): this widget mixes two
                             * orthogonal concerns. Don't conflate them.
                             *
                             *   (1) Upload  -> attaches a file to the candidate at save time.
                             *   (2) Parse   -> calls the AI to pre-fill candidate form fields,
                             *                  with two intensities (Fast / Full).
                             *
                             * --- Upload path -------------------------------------------------------
                             *
                             *   <input type="file" id="documentFile">  +  [Upload] button
                             *
                             *   Upload click submits with loadDocument='true' (no parseDocument).
                             *   Server: moves the file to the per-site temp upload dir, pins the
                             *   stored name into hidden `documentTempFile`. The file just sits
                             *   there until the candidate is saved.
                             *
                             *   The (remove) link clears `documentTempFile` and unlinks the temp
                             *   file -> nothing is attached at save.
                             *
                             *   At save (`_addCandidate`):
                             *     - documentTempFile present -> file is attached.
                             *     - Filename rule:
                             *         * Parse ran (aiParseLogID present)
                             *               -> Resume_<Name>_<YYYYMMDD>_<lang>.<ext>
                             *                  (collisions get _V2 / _V3 / ... via
                             *                   AIResumeParser::makeNextStandardFilename)
                             *         * Parse did NOT run
                             *               -> original filename kept as-is. We can't assume
                             *                  the file is a resume, so we apply the "Other"
                             *                  rule from CreateAttachmentModal (no rename).
                             *
                             *   Upload is independent of Fast/Full — it never calls AI.
                             *
                             * --- Parse path (Fast / Full) -----------------------------------------
                             *
                             *   Both buttons submit with loadDocument='true' AND parseDocument='true'.
                             *   Server reads either:
                             *     (a) the just-uploaded file, if user clicked Fast/Full without
                             *         first clicking Upload, OR
                             *     (b) the textarea contents, if the user pasted text.
                             *   Then runs the AI and pre-fills the candidate fields.
                             *
                             *   Fast vs Full (see AIResumeParser::_buildRequestPayload):
                             *     - Model:   Fast = OPENAI_FAST_MODEL (nano)
                             *                Full = OPENAI_MODEL      (mini)
                             *     - Output:  Fast skips career_summary, skill_summary, AND
                             *                jecho_report_markdown.
                             *                Full produces summaries; Jecho Report depends on
                             *                aiJechoReportRequested.
                             *     - Tokens:  Fast max=1500;  Full max=2500 (no Jecho) or 9000.
                             *
                             *   Canonical mode keys are 'fast' / 'full' (DB column, API options,
                             *   extension importMode). UI button labels may differ — don't rename
                             *   the keys.
                             *
                             * --- Paste-only flow (no Upload) --------------------------------------
                             *
                             *   When user pastes text and clicks Fast/Full, no documentTempFile
                             *   exists. At save:
                             *     - Fast + paste:  fields are filled, NO attachment is persisted.
                             *                      The pasted text is throwaway raw input.
                             *     - Full + paste:  Jecho_Report_<Name>_<Date>_<Lang>.md is created
                             *                      from the AI Jecho markdown. The pasted text
                             *                      itself is still NOT persisted.
                             *
                             *   The skip-attachment rule lives in
                             *   CandidatesUI::getParsedResumeTextAttachmentContentFromPost().
                             *
                             * --- Extension import (Chrome / "NBI-ATS") ----------------------------
                             *
                             *   The extension's "fast" / "full" importMode maps directly to
                             *   aiParseMode='fast' / 'full' (CandidatesUI::buildExtensionImportFields).
                             *   Downstream behavior is identical to the in-app Fast / Full buttons.
                             *
                             * --- Race-condition note ---------------------------------------------
                             *
                             *   The beforeunload handler at the bottom of this template fires
                             *   sendBeacon(removeDocumentTempFile) on navigation, gated by
                             *   window._catsFormSubmitting. js/candidateParser.js sets that flag
                             *   in submitCandidateParserForm() because form.submit() does not
                             *   trigger the 'submit' event. If you add a new code path that
                             *   navigates away programmatically while a temp file is pinned,
                             *   set the flag yourself or you'll race-delete the upload.
                             *
                             * =========================================================================
                             */
                            ?>
                            <?php if ($this->isParsingEnabled): ?>
                                <input type="hidden" name="loadDocument" id="loadDocument" value="" />
                                <input type="hidden" name="parseDocument" id="parseDocument" value="" />
                                <input type="hidden" name="documentTempFile" id="documentTempFile" value="<?php echo (isset($this->preassignedFields['documentTempFile']) ? $this->preassignedFields['documentTempFile'] : ''); ?>" />
                                <table cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td valign="middle" align="right" colspan="2">
                                            <img src="images/parser/arrow.gif" border="0" />
                                            <input type="hidden" name="MAX_FILE_SIZE" VALUE="10000000" />
                                            <input type="file" id="documentFile" name="documentFile" onchange="documentFileChange();" size="<?php if ($this->isModal): ?>20<?php else: ?>40<?php endif; ?>" />
                                            <input type="button" id="documentLoad" value="Upload" onclick="loadDocumentFileContents();" disabled />
                                            &nbsp;
                                        </td>
                                    </tr>
                                    <tr>
                                        <td valign="top" align="left" colspan="2">
                                            <?php if (isset($this->preassignedFields['documentTempFile']) && ($tempFile = $this->preassignedFields['documentTempFile']) != ''): ?>
                                            <div id="showAttachmentDetails" style="height: 20px; background-color: #e0e0e0; width: 500px; margin: 1px 0 5px 0; padding: 0 3px 0 5px;">
                                                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                    <tr>
                                                        <td align="left" valign="top" nowrap="nowrap" style="font-size: 11px;">
                                                            <img src="images/parser/attachment.gif" border="0" style="padding-top: 3px;" />
                                                            Attachment: <span style="font-weight: bold;"><?php echo $tempFile; ?></span>
                                                        </td>
                                                        <td align="right" valign="top" nowrap="nowrap" style="font-size: 11px;">
                                                            <a href="javascript:void(0);" onclick="removeDocumentFile();">(remove)</a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <?php endif; ?>
                                            <textarea class="inputbox" tabindex="90" name="documentText" id="documentText" rows="5" cols="40" onmousemove="documentCheck();" onchange="documentCheck();" onmousedown="documentCheck();" onkeypress="documentCheck();" style="width: <?php if ($this->isModal): ?>320<?php else: ?>500<?php endif; ?>px; height: 210px; padding: 3px;"><?php echo $this->contents; ?></textarea>
                                            <br/>
                                            <input type="checkbox" id="aiSavePasteAsJechoReport" name="aiSavePasteAsJechoReport" value="1" style="display:none;"<?php if (!empty($this->preassignedFields['aiSavePasteAsJechoReport'])): ?> checked<?php endif; ?> />
                                            <div style="color: #666666; text-align: center;">
                                            (<b>hint:</b> you may also paste the resume contents)
                                            <br /><br />
                                            <?php if (!($this->parsingStatus['parseLimit'] >= 0 && $this->parsingStatus['parseUsed'] >= $this->parsingStatus['parseLimit'])): ?>
                                            <span style="display:inline-block; text-align:center; vertical-align:middle;">
                                                <button
                                                    type="button"
                                                    id="transfer"
                                                    data-label="完整解析"
                                                    onclick="parseDocumentFileContents('full');"
                                                    <?php if ($this->contents == ''): ?>disabled="disabled"<?php endif; ?>
                                                    style="padding:6px 12px; border:1px solid #2f6fad; border-radius:4px; background:<?php echo ($this->contents != '' ? '#3f84c5' : '#d7dfe8'); ?>; color:<?php echo ($this->contents != '' ? '#ffffff' : '#6b7785'); ?>; font-size:12px; font-weight:bold; cursor:<?php echo ($this->contents != '' ? 'pointer' : 'not-allowed'); ?>;"
                                                >完整解析</button>
                                                <button
                                                    type="button"
                                                    id="transferFast"
                                                    data-label="快速解析"
                                                    onclick="parseDocumentFileContents('fast');"
                                                    <?php if ($this->contents == ''): ?>disabled="disabled"<?php endif; ?>
                                                    style="padding:6px 12px; margin-left:6px; border:1px solid #2f6fad; border-radius:4px; background:<?php echo ($this->contents != '' ? '#3f84c5' : '#d7dfe8'); ?>; color:<?php echo ($this->contents != '' ? '#ffffff' : '#6b7785'); ?>; font-size:12px; font-weight:bold; cursor:<?php echo ($this->contents != '' ? 'pointer' : 'not-allowed'); ?>;"
                                                >快速解析</button>
                                                <span style="display:block; margin-top:5px; font-size:11px; color:#666666;">快速解析只填候選人欄位；完整解析會另存 Jecho_AI_Report_*.md。</span>
                                            </span>
                                            <span id="aiParsingLoading" style="display:none; margin-left:8px; font-size:11px; color:#7a6000; background:#fffbe6; border:1px solid #e6c700; padding:3px 8px; vertical-align:middle;">&#x23F3; AI 解析中，請稍候...</span>
                                            <br /><br />
                                            <details style="display:inline-block; position:relative; text-align:left; font-size:11px; line-height:1.65; color:#4f5b66; vertical-align:top;">
                                                <summary style="cursor:pointer; font-weight:bold; color:#2f6fad; list-style:none; padding:3px 10px; border:1px solid #d8dde2; border-radius:4px; background:#f7f9fb; user-select:none; display:inline-block;">📋 操作說明（Upload / 解析 / 貼上）</summary>
                                                <div style="position:absolute; top:100%; left:50%; transform:translateX(-50%); margin-top:4px; width:<?php if ($this->isModal): ?>320<?php else: ?>500<?php endif; ?>px; padding:10px 14px; background:#ffffff; border:1px solid #d8dde2; border-radius:4px; box-shadow:0 4px 12px rgba(0,0,0,0.12); z-index:10;">
                                                    <div style="margin-bottom:6px;"><b>📎 Upload 上傳檔案</b></div>
                                                    <ul style="margin:0 0 8px 18px; padding:0;">
                                                        <li>選檔 → 按 <b>Upload</b>，檔案暫存；按 <b>Save</b> / <b>Add Candidate</b> 才會正式存成附件。</li>
                                                        <li>點 <b>(remove)</b> 可取消這次上傳。</li>
                                                        <li>Upload 跟下方的「快速 / 完整解析」是獨立動作，互不影響。</li>
                                                    </ul>
                                                    <div style="margin-bottom:6px;"><b>🏷️ 附件檔名規則</b></div>
                                                    <ul style="margin:0 0 8px 18px; padding:0;">
                                                        <li>有跑過解析 → <code>Resume_&lt;姓名&gt;_&lt;日期&gt;_&lt;語言&gt;.&lt;副檔名&gt;</code>。</li>
                                                        <li>同名衝突 → 自動加 <code>_V2</code>、<code>_V3</code>…</li>
                                                        <li>沒跑解析 → 保留原檔名（無法判定是不是履歷）。</li>
                                                    </ul>
                                                    <div style="margin-bottom:6px;"><b>🤖 AI 解析（快速 vs 完整）</b></div>
                                                    <ul style="margin:0 0 8px 18px; padding:0;">
                                                        <li><b>快速</b>：只填候選人欄位；不產 Career / Skill Summary、不產 Jecho Report。</li>
                                                        <li><b>完整</b>：填欄位 + Career / Skill Summary + Jecho Report。</li>
                                                        <li>NBI-ATS 擴充功能傳入的快速 / 完整 ＝ 這兩顆按鈕，行為一致。</li>
                                                    </ul>
                                                    <div style="margin-bottom:6px;"><b>📋 直接貼上文字</b></div>
                                                    <ul style="margin:0 0 0 18px; padding:0;">
                                                        <li>沒上傳檔案、直接貼上履歷文字也可以解析，欄位照樣會填好。</li>
                                                        <li>貼上的原文 <b>不會</b>另存為附件（來源不明確，視為流水帳）。</li>
                                                        <li>完整解析時：另存 <code>Jecho_Report_&lt;姓名&gt;_&lt;日期&gt;_&lt;語言&gt;.md</code>。</li>
                                                    </ul>
                                                </div>
                                            </details>
                                            <?php endif; ?>
                                            <?php if (LicenseUtility::isProfessional() || file_exists('modules/asp')): ?>
                                            &nbsp;
                                            <?php else: ?>
                                                <?php if ($this->parsingStatus['parseLimit'] >= 0 && (($used = $this->parsingStatus['parseUsed']) / ($limit = $this->parsingStatus['parseLimit']) * 100) > 50): ?>
                                                    <?php if ($used == $limit): ?><span style="color: #800000;"><?php endif; ?>
                                                    Used <b><?php echo number_format($this->parsingStatus['parseUsed'],0); ?> / <?php echo number_format($this->parsingStatus['parseLimit'],0); ?></b> daily <a href="http://www.resfly.com" target="_blank">Resfly&trade;</a> automatic resume imports
                                                    <?php if ($used == $limit): ?>
                                                        </span>
                                                        <br />
                                                        Enter resume information manually or
                                                        <a href="http://www.catsone.com/?a=getcats">upgrade to CATS Professional</a>.
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            <?php else: ?>
                                <?php if (PARSING_ENABLED &&
                                    count($this->parsingStatus) &&
                                    $this->parsingStatus['parseUsed'] >= $this->parsingStatus['parseLimit'] &&
                                    $this->parsingStatus['parseLimit'] >= 0): ?>
                                <a href="http://www.catsone.com/professional" target="_blank">All daily resume imports used. For more, upgrade to CATS professional</a>.
                                <?php endif; ?>
                                <?php $freeformTop = '<p class="freeformtop">Cut and paste freeform here.</p>'; ?>
                                <?php eval(Hooks::get('CANDIDATE_TEMPLATE_ABOVE_FREEFORM')); ?>
                                <?php echo($freeformTop); ?>

                                <textarea class="inputbox" tabindex="90" name="addressBlock" id="addressBlock" rows="5" cols="40" style="width: 500px; height: 250px;"></textarea>

                                <?php $freeformBottom = '<p class="freeformbottom">Cut and paste freeform address here.</p>'; ?>
                                <?php eval(Hooks::get('CANDIDATE_TEMPLATE_BELOW_FREEFORM')); ?>
                                <?php echo($freeformBottom); ?>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="middleNameLabel" for="middleName">Middle Name:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="2" name="middleName" id="middleName" class="inputbox" style="width: 150px" value="<?php if(isset($this->preassignedFields['middleName'])) $this->_($this->preassignedFields['middleName']); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="lastNameLabel" for="lastName">Last Name:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="2" name="lastName" id="lastName" class="inputbox" style="width: 150px" value="<?php if(isset($this->preassignedFields['lastName'])) $this->_($this->preassignedFields['lastName']); ?>" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="chineseNameLabel" for="chineseName">Chinese Name:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="2" name="chineseName" id="chineseName" class="inputbox" style="width: 150px" value="<?php if(isset($this->preassignedFields['chineseName'])) $this->_($this->preassignedFields['chineseName']); ?>" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="nationalityLabel" for="nationality">Nationality:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="2" name="nationality" id="nationality" class="inputbox" style="width: 150px" value="<?php if(isset($this->preassignedFields['nationality'])) $this->_($this->preassignedFields['nationality']); ?>" />
                        </td>
                    </tr>
                                        
                    <tr>
                        <td class="tdVertical">
                            <label id="emailLabel" for="email1">E-Mail:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="3" name="email1" id="email1" class="inputbox" style="width: 150px" value="<?php if(isset($this->preassignedFields['email'])) $this->_($this->preassignedFields['email']); elseif (isset($this->preassignedFields['email1'])) $this->_($this->preassignedFields['email1']); ?>" onchange="checkEmailAlreadyInSystem(this.value, '', '', 'email1');" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="email2Label" for="email2">2nd E-Mail:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="4" name="email2" id="email2" class="inputbox" style="width: 150px" value="<?php if (isset($this->preassignedFields['email2'])) $this->_($this->preassignedFields['email2']); ?>" onchange="checkEmailAlreadyInSystem(this.value, '', '', 'email2');" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="webSiteLabel" for="webSite">Web Site:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="5" name="webSite" id="webSite" class="inputbox" style="width: 450px" value="<?php if (isset($this->preassignedFields['webSite'])) $this->_($this->preassignedFields['webSite']); ?>" onchange="checkLinkAlreadyInSystem(this.value);" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="facebookLabel" for="facebook">Facebook:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="5" name="facebook" id="facebook" class="inputbox" style="width: 450px" value="<?php if (isset($this->preassignedFields['facebook'])) $this->_($this->preassignedFields['facebook']); ?>" onchange="checkLinkAlreadyInSystem(this.value);" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="linkedinLabel" for="linkedin">Linkedin:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="5" name="linkedin" id="linkedin" class="inputbox" style="width: 450px" value="<?php if (isset($this->preassignedFields['linkedin'])) $this->_($this->preassignedFields['linkedin']); ?>" onchange="checkLinkAlreadyInSystem(this.value);" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="githubLabel" for="github">Github:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="5" name="github" id="github" class="inputbox" style="width: 450px" value="<?php if (isset($this->preassignedFields['github'])) $this->_($this->preassignedFields['github']); ?>" onchange="checkLinkAlreadyInSystem(this.value);" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="googleplusLabel" for="googleplus">Google Plus:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="5" name="googleplus" id="googleplus" class="inputbox" style="width: 450px" value="<?php if (isset($this->preassignedFields['googleplus'])) $this->_($this->preassignedFields['googleplus']); ?>" onchange="checkLinkAlreadyInSystem(this.value);" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="twitterLabel" for="twitter">Twitter:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="5" name="twitter" id="twitter" class="inputbox" style="width: 450px" value="<?php if (isset($this->preassignedFields['twitter'])) $this->_($this->preassignedFields['twitter']); ?>" onchange="checkLinkAlreadyInSystem(this.value);" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="cakeresumeLabel" for="cakeresume">Cakeresume:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="5" name="cakeresume" id="cakeresume" class="inputbox" style="width: 450px" value="<?php if (isset($this->preassignedFields['cakeresume'])) $this->_($this->preassignedFields['cakeresume']); ?>" onchange="checkLinkAlreadyInSystem(this.value);" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="link1Label" for="link1">Link1:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="5" name="link1" id="link1" class="inputbox" style="width: 450px" value="<?php if (isset($this->preassignedFields['link1'])) $this->_($this->preassignedFields['link1']); ?>" onchange="checkLinkAlreadyInSystem(this.value);" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="link2Label" for="link2">Link2:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="5" name="link2" id="link2" class="inputbox" style="width: 450px" value="<?php if (isset($this->preassignedFields['link2'])) $this->_($this->preassignedFields['link2']); ?>" onchange="checkLinkAlreadyInSystem(this.value);" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="link3Label" for="link3">Link3:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="5" name="link3" id="link3" class="inputbox" style="width: 450px" value="<?php if (isset($this->preassignedFields['link3'])) $this->_($this->preassignedFields['link3']); ?>" onchange="checkLinkAlreadyInSystem(this.value);" />
                        </td>
                    </tr>

                    <?php $tabIndex = 6; ?>
                    <tr>
                        <td class="tdVertical">
                            <label id="sourceLabel" for="sourceSelect">Source:</label>
                        </td>
                        <td class="tdData">
<?php if ($this->isModal): ?>
                            <select id="sourceSelect" tabindex="<?php echo($tabIndex++); ?>" name="source" class="inputbox" style="width: 150px;">
<?php else: ?>
                            <select id="sourceSelect" tabindex="<?php echo($tabIndex++); ?>" name="source" class="inputbox" style="width: 150px;" onchange="if (this.value == 'edit') { listEditor('Sources', 'sourceSelect', 'sourceCSV', false); this.value = '(none)'; } if (this.value == 'nullline') { this.value = '(none)'; }">
                                <option value="edit">(Edit Sources)</option>
                                <option value="nullline">-------------------------------</option>
<?php endif; ?>
                                    <option value="(none)" <?php if (!isset($this->preassignedFields['source'])): ?>selected="selected"<?php endif; ?>>(None)</option>
                                    <?php if (isset($this->preassignedFields['source'])): ?>
                                        <option value="<?php $this->_($this->_($this->preassignedFields['source'])); ?>" selected="selected"><?php $this->_($this->_($this->preassignedFields['source'])); ?></option>
                                    <?php endif; ?>
                                <?php foreach ($this->sourcesRS AS $index => $source): ?>
                                    <option value="<?php $this->_($source['name']); ?>"><?php $this->_($source['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" id="sourceCSV" name="sourceCSV" value="<?php $this->_($this->sourcesString); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="phoneHomeLabel" for="phoneHome">Home Phone:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="6" name="phoneHome" id="phoneHome" class="inputbox" style="width: 150px;" value="<?php if (isset($this->preassignedFields['phoneHome'])) $this->_($this->preassignedFields['phoneHome']); ?>" onchange="checkPhoneAlreadyInSystem(this.value, '', '', 'phoneHome');"  />
                            <?php if ($this->isParsingEnabled): ?>
                                <?php if ($this->parsingStatus['parseLimit'] >= 0 && $this->parsingStatus['parseUsed'] >= $this->parsingStatus['parseLimit']): ?>
                                    &nbsp;
                                <?php endif; ?>
                            <?php else: ?>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="phoneCellLabel" for="phoneCell">Cell Phone:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="7" name="phoneCell" id="phoneCell" class="inputbox" style="width: 150px;" value="<?php if (isset($this->preassignedFields['phoneCell'])) $this->_($this->preassignedFields['phoneCell']); ?>" onchange="checkPhoneAlreadyInSystem(this.value, '', '', 'phoneCell');" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="phoneWorkLabel" for="phoneWork">Work Phone:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="8" name="phoneWork" id="phoneWork" class="inputbox" style="width: 150px" value="<?php if (isset($this->preassignedFields['phoneWork'])) $this->_($this->preassignedFields['phoneWork']); ?>" onchange="checkPhoneAlreadyInSystem(this.value, '', '', 'phoneWork');" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="stateLabel" for="state">Best Time to Call:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="13" name="bestTimeToCall" id="bestTimeToCall" class="inputbox" style="width: 150px" value="<?php if(isset($this->preassignedFields['bestTimeToCall'])) $this->_($this->preassignedFields['bestTimeToCall']); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="addressLabel" for="address">Address:</label>
                        </td>
                        <td class="tdData">
                            <textarea tabindex="9" name="address" id="address" rows="2" cols="40" class="inputbox" style="width: 150px"><?php if(isset($this->preassignedFields['address'])) $this->_($this->preassignedFields['address']); if(isset($this->preassignedFields['address2'])) $this->_("\n" . $this->preassignedFields['address2']); ?></textarea>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="images/indicator2.gif" id="addressParserIndicator" alt="" style="visibility: hidden; margin-left: 10px;" height="16" width="16" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="cityLabel" for="city">City:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="11" name="city" id="city" class="inputbox" style="width: 150px" value="<?php if(isset($this->preassignedFields['city'])) $this->_($this->preassignedFields['city']); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="stateLabel" for="state">State:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="12" name="state" id="state" class="inputbox" style="width: 150px" value="<?php if(isset($this->preassignedFields['state'])) $this->_($this->preassignedFields['state']); ?>" />
                        </td>
                    </tr>

                    <?php /*
                    <tr>
                        <td class="tdVertical">
                            <label id="zipLabel" for="zip">Postal Code:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="13" name="zip" id="zip" class="inputbox" style="width: 150px" value="<?php if(isset($this->preassignedFields['zip'])) $this->_($this->preassignedFields['zip']); ?>" />&nbsp;
                            <input type="button" tabindex="92" onclick="CityState_populate('zip', 'ajaxIndicator');" value="Lookup" />
                            <img src="images/indicator2.gif" alt="AJAX" id="ajaxIndicator" style="vertical-align: middle; visibility: hidden; margin-left: 5px;" />
                        </td>
                    </tr>
                    */ ?>

                    <?php $tabIndex = 15; ?>
                </table>

                <?php if (!$this->isParsingEnabled || $this->associatedAttachment != 0): ?>
                <p class="note<?php if ($this->isModal): ?>Unsized<?php endif; ?>" style="margin-top: 5px;">Resume</p>

                <table class="editTable" width="<?php if ($this->isModal): ?>100%<?php else: ?>1225<?php endif; ?>">
                    <tr>
                        <td class="tdVertical">Resume:</td>
                        <td class="tdData" style="width:320px;">
                            <?php if ($this->associatedAttachment == 0): ?>
                                <nobr> <?php /* FIXME:  remove nobr stuff */ ?>
                                    <?php if (isset($this->overAttachmentQuota)): ?>
                                        <span style="font-size:10px;">(You have already reached your limit of <?php echo(FREE_ACCOUNT_SIZE/1024); ?> MB of attachments, and cannot add additional file attachments without upgrading to CATS Professional Hosted.)<br /></font>Copy and Paste Resume:&nbsp;
                                    <?php else: ?>
                                        <input type="file" id="file" name="file" size="21" tabindex="<?php echo($tabIndex++); ?>" <?php if($this->associatedTextResume !== false): ?>disabled<?php endif; ?> /> &nbsp;
                                    <?php endif; ?>
                                    <a href="javascript:void(0);" onclick="if (document.getElementById('textResumeTD').style.display != '') { document.getElementById('textResumeTD').style.display = ''; document.getElementById('file').disabled=true; } else { document.getElementById('textResumeTD').style.display='none'; document.getElementById('file').disabled = false; }">
                                        <img src="images/package_editors.gif" style="margin:0px; padding:0px;"  class="absmiddle" alt="" border="0" title="Copy / Paste Resume" />
                                    </a>
                                </nobr>
                             <?php else: ?>
                                <a href="<?php echo $this->associatedAttachmentRS['retrievalURL']; ?>">
                                    <img src="<?php $this->_($this->associatedAttachmentRS['attachmentIcon']) ?>" alt="" width="16" height="16" style="border: none;" />
                                </a>
                                <a href="<?php echo $this->associatedAttachmentRS['retrievalURL']; ?>">
                                    <?php $this->_($this->associatedAttachmentRS['originalFilename']) ?>
                                </a>
                                <?php echo($this->associatedAttachmentRS['previewLink']); ?>
                                <input type="hidden" name="associatedAttachment" value="<?php echo($this->associatedAttachment); ?>" />
                            <?php endif; ?>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="3" align="left" valign="top">
                            <input type="hidden" name="textResumeFilename" value="<?php if(isset($this->preassignedFields['textResumeFilename'])) $this->_($this->preassignedFields['textResumeFilename']); else echo('resume.txt'); ?>" />
                            <div id="textResumeTD" <?php if($this->associatedTextResume === false): ?>style="display:none;"<?php endif; ?>>
                                <p class="freeformtop" style="width: 700px;">Cut and paste resume text here.</p>

                                &nbsp;<textarea class="inputbox" tabindex="90" name="textResumeBlock" id="textResumeBlock" rows="5" cols="60" style="width: 700px; height: 300px;"><?php if ($this->associatedTextResume !== false) $this->_($this->associatedTextResume); ?></textarea>

                                <p class="freeformtop" style="width: 700px;">Cut and paste resume text here.</p>
                            </div>
                        </td>
                    </tr>
                </table>
                <?php else: ?>
                <br />
                <?php endif; ?>

                <?php if($this->EEOSettingsRS['enabled'] == 1): ?>
                    <p class="note<?php if ($this->isModal): ?>Unsized<?php endif; ?>" style="margin-top: 5px;">EEO Information</p>
                    <table class="editTable" width="<?php if ($this->isModal): ?>100%<?php else: ?>1225<?php endif; ?>">
                         <?php if ($this->EEOSettingsRS['genderTracking'] == 1): ?>
                             <tr>
                                <td class="tdVertical">
                                    <label id="canRelocateLabel" for="canRelocate">Gender:</label>
                                </td>
                                <td class="tdData">
                                    <select id="gender" name="gender" class="inputbox" style="width:200px;" tabindex="<?php echo($tabIndex++); ?>">
                                        <option selected="selected" value="">----</option>
                                        <option value="M"<?php if (isset($this->preassignedFields['gender']) && $this->preassignedFields['gender'] == 'M') echo ' selected'; ?>>Male</option>
                                        <option value="F"<?php if (isset($this->preassignedFields['gender']) && $this->preassignedFields['gender'] == 'F') echo ' selected'; ?>>Female</option>
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
                                    <select id="race" name="race" class="inputbox" style="width:200px;" tabindex="<?php echo($tabIndex++); ?>">
                                        <option selected="selected" value="">----</option>
                                        <option value="1"<?php if (isset($this->preassignedFields['race']) && $this->preassignedFields['race'] == '1') echo ' selected'; ?>>American Indian</option>
                                        <option value="2"<?php if (isset($this->preassignedFields['race']) && $this->preassignedFields['race'] == '2') echo ' selected'; ?>>Asian or Pacific Islander</option>
                                        <option value="3"<?php if (isset($this->preassignedFields['race']) && $this->preassignedFields['race'] == '3') echo ' selected'; ?>>Hispanic or Latino</option>
                                        <option value="4"<?php if (isset($this->preassignedFields['race']) && $this->preassignedFields['race'] == '4') echo ' selected'; ?>>Non-Hispanic Black</option>
                                        <option value="5"<?php if (isset($this->preassignedFields['race']) && $this->preassignedFields['race'] == '5') echo ' selected'; ?>>Non-Hispanic White</option>
                                    </select>
                                </td>
                             </tr>
                         <?php endif; ?>
                         <?php if ($this->EEOSettingsRS['veteranTracking'] == 1): ?>
                             <tr>
                                <td class="tdVertical">
                                    <label id="canRelocateLabel" for="canRelocate">Veteran Status:</label>
                                </td>
                                <td class="tdData">
                                    <select id="veteran" name="veteran" class="inputbox" style="width:200px;" tabindex="<?php echo($tabIndex++); ?>">
                                        <option selected="selected" value="">----</option>
                                        <option value="1"<?php if (isset($this->preassignedFields['veteran']) && $this->preassignedFields['veteran'] == '1') echo ' selected'; ?>>No</option>
                                        <option value="2"<?php if (isset($this->preassignedFields['veteran']) && $this->preassignedFields['veteran'] == '2') echo ' selected'; ?>>Eligible Veteran</option>
                                        <option valie="3"<?php if (isset($this->preassignedFields['veteran']) && $this->preassignedFields['veteran'] == '3') echo ' selected'; ?>>Disabled Veteran</option>
                                        <option value="4"<?php if (isset($this->preassignedFields['veteran']) && $this->preassignedFields['veteran'] == '4') echo ' selected'; ?>>Eligible and Disabled</option>
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
                                    <select id="disability" name="disability" class="inputbox" style="width:200px;" tabindex="<?php echo($tabIndex++); ?>">
                                        <option selected="selected" value="">----</option>
                                        <option value="No"<?php if (isset($this->preassignedFields['disability']) && $this->preassignedFields['disability'] == 'No') echo ' selected'; ?>>No</option>
                                        <option value="Yes"<?php if (isset($this->preassignedFields['disability']) && $this->preassignedFields['disability'] == 'Yes') echo ' selected'; ?>>Yes</option>
                                    </select>
                                </td>
                             </tr>
                         <?php endif; ?>
                    </table>
                    <br />
                <?php endif; ?>

                <p class="note<?php if ($this->isModal): ?>Unsized<?php endif; ?>" style="margin-top: 5px;">Other</p>
                
                <table style="font-weight:bold; border: 1px solid #000; background-color: #ffed1a; padding:5px; display:none; margin-bottom:7px;" width="<?php if ($this->isModal): ?>100%<?php else: ?>1225<?php endif; ?>" class="candidateAlreadyInSystemTable">
                    <tr>
                        <td class="tdVertical">
                            This profile may already be in the system.&nbsp;&nbsp;Possible duplicate candidate profile:&nbsp;&nbsp;
                            <a href="javascript:void(0);" onclick="return openCandidateAlreadyInSystemWithPaste('<?php echo(CATSUtility::getIndexName()); ?>');">
                                <img src="images/new_window.gif" border="0" />
                                <img src="images/candidate_small.gif" border="0" />
                                <span class="candidateAlreadyInSystemName"></span>
                            </a>
                        </td>
                    </tr>
                </table>
                
                <table class="editTable" width="<?php if ($this->isModal): ?>100%<?php else: ?>1225<?php endif; ?>">

                    <tr>
                        <td class="tdVertical">
                            <label id="canRelocateLabel" for="canRelocate">Can Relocate:</label>
                        </td>
                        <td class="tdData">
                            <input type="checkbox" tabindex="<?php echo($tabIndex++); ?>" id="canRelocate" name="canRelocate" value="1"<?php if (isset($this->preassignedFields['canRelocate']) && $this->preassignedFields['canRelocate'] == '1') echo ' checked'; ?> />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="dateAvailableLabel" for="dateAvailable">Date Available:</label>
                        </td>
                        <td class="tdData">
                            <script type="text/javascript">DateInput('dateAvailable', false, 'MM-DD-YY', '', <?php echo($tabIndex++); ?>);</script>

                            <?php /* DateInput()s take up 3 tabindexes. */ ?>
                            <?php $tabIndex += 2; ?>
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="currentEmployerLabel" for="currentEmployer">Current Employer:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="currentEmployer" id="currentEmployer" class="inputbox" style="width: 150px" value="<?php if (isset($this->preassignedFields['currentEmployer'])) $this->_($this->preassignedFields['currentEmployer']); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="jobTitleLabel" for="jobTitle">Job Title:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="jobTitle" id="jobTitle" class="inputbox" style="width: 150px" value="<?php if (isset($this->preassignedFields['jobTitle'])) $this->_($this->preassignedFields['jobTitle']); ?>" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="currentPayLabel" for="currentEmployer">Current Pay:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="currentPay" id="currentPay" class="inputbox" style="width: 150px" value="<?php if (isset($this->preassignedFields['currentPay'])) $this->_($this->preassignedFields['currentPay']); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="desiredPayLabel" for="currentEmployer">Desired Pay:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="desiredPay" id="desiredPay" class="inputbox" style="width: 150px" value="<?php if (isset($this->preassignedFields['desiredPay'])) $this->_($this->preassignedFields['desiredPay']); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="keySkillsLabel" for="keySkills">Key Skills:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" tabindex="<?php echo($tabIndex++); ?>" name="keySkills" id="keySkills" style="width: 700px;" value="<?php if (isset($this->preassignedFields['keySkills'])) $this->_($this->preassignedFields['keySkills']); ?>" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="extraGenderLabel" for="extraGender">Gender:</label>
                        </td>
                        <td class="tdData">
                            <select id="extraGender" tabindex="<?php echo($tabIndex++); ?>" class="selectBox" name="extraGender" style="width: 150px;">
                                <option value="" selected="">- Select from List -</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="maritalStatusLabel" for="maritalStatus">Marital Status:</label>
                        </td>
                        <td class="tdData">
                            <select id="maritalStatus" tabindex="<?php echo($tabIndex++); ?>" class="selectBox" name="maritalStatus" style="width: 150px;">
                                <option value="" selected="">- Select from List -</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed">Widowed</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="birthYearLabel" for="birthYear">Birth Year:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="birthYear" id="birthYear" class="inputbox" style="width: 150px" value="<?php if (isset($this->preassignedFields['birthYear'])) $this->_($this->preassignedFields['birthYear']); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="highestDegreeLabel" for="highestDegree">Highest Education Degree:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="highestDegree" id="highestDegree" class="inputbox" style="width: 150px" value="<?php if (isset($this->preassignedFields['highestDegree'])) $this->_($this->preassignedFields['highestDegree']); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="majorLabel" for="major">Major:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="major" id="major" class="inputbox" style="width: 150px" value="<?php if (isset($this->preassignedFields['major'])) $this->_($this->preassignedFields['major']); ?>" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="lineLabel" for="line">Line:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="line" id="line" class="inputbox" style="width: 150px" value="<?php if (isset($this->preassignedFields['line'])) $this->_($this->preassignedFields['line']); ?>" onchange="checkSocialMediaAlreadyInSystem('line', this.value);" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="qqLabel" for="qq">Telegram:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="qq" id="qq" class="inputbox" style="width: 150px" value="<?php if (isset($this->preassignedFields['qq'])) $this->_($this->preassignedFields['qq']); ?>" onchange="checkSocialMediaAlreadyInSystem('qq', this.value);" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="skypeLabel" for="skype">Skype:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="skype" id="skype" class="inputbox" style="width: 150px" value="<?php if (isset($this->preassignedFields['skype'])) $this->_($this->preassignedFields['skype']); ?>" onchange="checkSocialMediaAlreadyInSystem('skype', this.value);" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="wechatLabel" for="wechat">Wechat:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="wechat" id="wechat" class="inputbox" style="width: 150px" value="<?php if (isset($this->preassignedFields['wechat'])) $this->_($this->preassignedFields['wechat']); ?>" onchange="checkSocialMediaAlreadyInSystem('wechat', this.value);" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="functionsLabel" for="functions">Functions:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="functions" id="functions" class="inputbox" style="width: 150px" value="<?php if (isset($this->preassignedFields['functions'])) $this->_($this->preassignedFields['functions']); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical">
                            <label id="jobLevelLabel" for="jobLevel">Job Level:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="jobLevel" id="jobLevel" class="inputbox" style="width: 150px" value="<?php if (isset($this->preassignedFields['jobLevel'])) $this->_($this->preassignedFields['jobLevel']); ?>" />
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
                                <?php echo($this->extraFieldRS[$i]['addHTML']); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php endfor; ?>

                    <tr>
                        <td class="tdVertical">
                            <label id="notesLabel" for="notes">Misc. Notes:</label>
                        </td>
                        <td class="tdData">
                            <textarea class="inputbox" tabindex="<?php echo($tabIndex++); ?>" name="notes" id="notes" rows="5" cols="40" style="width: 700px; height: 400px;"><?php if (isset($this->preassignedFields['notes'])) $this->_($this->preassignedFields['notes']); ?></textarea>
                        </td>
                    </tr>
                </table>

                <table style="font-weight:bold; border: 1px solid #000; background-color: #ffed1a; padding:5px; display:none; margin-bottom:7px;" width="<?php if ($this->isModal): ?>100%<?php else: ?>1225<?php endif; ?>" class="candidateAlreadyInSystemTable">
                    <tr>
                        <td class="tdVertical">
                            This profile may already be in the system.&nbsp;&nbsp;Possible duplicate candidate profile:&nbsp;&nbsp;
                            <a href="javascript:void(0);" onclick="return openCandidateAlreadyInSystemWithPaste('<?php echo(CATSUtility::getIndexName()); ?>');">
                                <img src="images/new_window.gif" border="0" />
                                <img src="images/candidate_small.gif" border="0" />
                                <span class="candidateAlreadyInSystemName"></span>
                            </a>
                        </td>
                    </tr>
                </table>
                
                <input type="submit" tabindex="<?php echo($tabIndex++); ?>" class="button" id="submit" value="Add Candidate" />&nbsp;
                <input type="button" tabindex="<?php echo($tabIndex++); ?>" class="button" value="Reset" onclick="return resetAddCandidateForm();" />&nbsp;
                <?php if ($this->isModal): ?>
                    <input type="button" tabindex="<?php echo($tabIndex++); ?>" class="button" value="Back to Search" onclick="javascript:goToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=considerCandidateSearch&amp;jobOrderID=<?php echo($this->jobOrderID); ?>');" />
                <?php else: ?>
                    <input type="button" tabindex="<?php echo($tabIndex++); ?>" class="button" value="Back to Candidates" onclick="javascript:goToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates');" />
                <?php endif; ?>
            </form>

<script type="text/javascript">
    function adjustCandidateSummaryFields(formId)
    {
        var form = document.getElementById(formId);
        if (!form)
        {
            return;
        }

        var labels = form.querySelectorAll('td.tdVertical label');
        for (var i = 0; i < labels.length; i++)
        {
            var labelText = labels[i].textContent.replace(/\s+/g, ' ').replace(/:\s*$/, '').trim();
            if (labelText !== 'Career Summary' && labelText !== 'Skill Summary')
            {
                continue;
            }

            var row = labels[i].closest('tr');
            if (!row)
            {
                continue;
            }

            var textarea = row.querySelector('textarea');
            if (!textarea)
            {
                continue;
            }

            textarea.style.width = '700px';
            textarea.rows = Math.max(parseInt(textarea.rows, 10) || 0, 8);
        }
    }

    // Clean up temp file if user leaves the page without saving
    document.getElementById('addCandidateForm').addEventListener('submit', function() {
        window._catsFormSubmitting = true;
    });
    window.addEventListener('beforeunload', function() {
        // window._catsFormSubmitting is also set by submitCandidateParserForm()
        // (used by the AI Fast/Full parse buttons) since form.submit() does not
        // fire the 'submit' event.
        if (window._catsFormSubmitting) return;
        var tempFile = document.getElementById('documentTempFile');
        var logID    = document.getElementById('aiParseLogID');
        if ((tempFile && tempFile.value !== '') || (logID && logID.value !== '')) {
            var url = 'index.php?m=candidates&a=removeDocumentTempFile&documentTempFile='
                + encodeURIComponent(tempFile ? tempFile.value : '');
            if (logID && logID.value !== '') {
                url += '&aiParseLogID=' + encodeURIComponent(logID.value);
            }
            // Use sendBeacon for reliable delivery on page unload
            if (navigator.sendBeacon) {
                navigator.sendBeacon(url);
            } else {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', url, false);
                try { xhr.send(null); } catch(e) {}
            }
        }
    });

    adjustCandidateSummaryFields('addCandidateForm');
    attachAddCandidateRequiredFieldValidation();
    document.addCandidateForm.firstName.focus();
    <?php if (
        isset($this->preassignedFields['aiParseLogID']) &&
        $this->preassignedFields['aiParseLogID'] != '' &&
        (!isset($this->preassignedFields['aiParseError']) || $this->preassignedFields['aiParseError'] == '')
    ): ?>
        highlightMissingAddCandidateRequiredFields();
    <?php endif; ?>
    <?php if(isset($this->preassignedFields['email']) || isset($this->preassignedFields['email1'])): ?>
        checkEmailAlreadyInSystem(urlDecode("<?php if(isset($this->preassignedFields['email'])) echo(urlencode($this->preassignedFields['email'])); else if(isset($this->preassignedFields['email1'])) echo(urlencode($this->preassignedFields['email1'])); ?>"), '', '', 'email1');
    <?php endif; ?>
    <?php if(isset($this->preassignedFields['email2']) || isset($this->preassignedFields['email2'])): ?>
            checkEmailAlreadyInSystem(urlDecode("<?php if(isset($this->preassignedFields['email2'])) echo(urlencode($this->preassignedFields['email2'])); else if(isset($this->preassignedFields['email2'])) echo(urlencode($this->preassignedFields['email2'])); ?>"), '', '', 'email2');
    <?php endif; ?>
    <?php if(isset($this->preassignedFields['phoneCell']) || isset($this->preassignedFields['phoneCell'])): ?>
            checkPhoneAlreadyInSystem(urlDecode("<?php if(isset($this->preassignedFields['phoneCell'])) echo(urlencode($this->preassignedFields['phoneCell'])); else if(isset($this->preassignedFields['phoneCell'])) echo(urlencode($this->preassignedFields['phoneCell'])); ?>"), '', '', 'phoneCell');
    <?php endif; ?>
    <?php if(isset($this->preassignedFields['phoneWork']) || isset($this->preassignedFields['phoneWork'])): ?>
            checkPhoneAlreadyInSystem(urlDecode("<?php if(isset($this->preassignedFields['phoneWork'])) echo(urlencode($this->preassignedFields['phoneWork'])); else if(isset($this->preassignedFields['phoneWork'])) echo(urlencode($this->preassignedFields['phoneWork'])); ?>"), '', '', 'phoneWork');
    <?php endif; ?>
    <?php if(isset($this->preassignedFields['phoneHome']) || isset($this->preassignedFields['phoneHome'])): ?>
            checkPhoneAlreadyInSystem(urlDecode("<?php if(isset($this->preassignedFields['phoneHome'])) echo(urlencode($this->preassignedFields['phoneHome'])); else if(isset($this->preassignedFields['phoneHome'])) echo(urlencode($this->preassignedFields['phoneHome'])); ?>"), '', '', 'phoneHome');
    <?php endif; ?>
    <?php foreach (array('webSite', 'facebook', 'linkedin', 'github', 'googleplus', 'twitter', 'cakeresume', 'link1', 'link2', 'link3') as $linkFieldName): ?>
        <?php if (isset($this->preassignedFields[$linkFieldName]) && trim((string) $this->preassignedFields[$linkFieldName]) != ''): ?>
            checkLinkAlreadyInSystem(urlDecode("<?php echo(urlencode($this->preassignedFields[$linkFieldName])); ?>"), '', '', '<?php echo($linkFieldName); ?>');
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if (!empty($this->preassignedFields['aiAutoParseFromImport']) && $this->isParsingEnabled): ?>
        if (typeof parseDocumentFileContents === 'function') {
            parseDocumentFileContents(true);
        }
    <?php endif; ?>
</script>

<?php if ($this->isModal): ?>
    </body>
</html>
<?php else: ?>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
<?php endif; ?>
