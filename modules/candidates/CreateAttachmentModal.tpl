<?php /* $Id: CreateAttachmentModal.tpl 3093 2007-09-24 21:09:45Z brian $ */ ?>
<?php TemplateUtility::printModalHeader('Candidates', array('modules/candidates/validator.js'), 'Create Candidate Attachment'); ?>

    <?php if (!$this->isFinishedMode): ?>
        <?php
            $firstName = isset($this->candidateData['firstName']) ? $this->candidateData['firstName'] : '';
            $lastName = isset($this->candidateData['lastName']) ? $this->candidateData['lastName'] : '';
            $attachmentFileNames = isset($this->attachmentFileNames) ? $this->attachmentFileNames : array();
        ?>
        <script type="text/javascript">
            var existingAttachmentFileNames = <?php echo json_encode(array_values($attachmentFileNames)); ?>;
            var attachmentNamingRules = {
                resume: {
                    showCompany: false,
                    companyRequired: false,
                    showLanguage: true,
                    showDegree: false,
                    suggestedResume: true,
                    build: function(parts) {
                        return ['Resume', parts.name, parts.date, parts.language];
                    }
                },
                jecho_report: {
                    showCompany: true,
                    companyRequired: false,
                    showLanguage: true,
                    showDegree: false,
                    suggestedResume: true,
                    build: function(parts) {
                        return ['Jecho_Report', parts.name, parts.date, parts.company, parts.language];
                    }
                },
                personal_agreement: {
                    showCompany: true,
                    companyRequired: false,
                    showLanguage: false,
                    showDegree: false,
                    suggestedResume: false,
                    build: function(parts) {
                        return ['PersonalAgreement', parts.name, parts.date, parts.company];
                    }
                },
                portfolio: {
                    showCompany: true,
                    companyRequired: false,
                    showLanguage: false,
                    showDegree: false,
                    suggestedResume: false,
                    build: function(parts) {
                        return ['Portfolio', parts.name, parts.date, parts.company];
                    }
                },
                transcript: {
                    showCompany: false,
                    companyRequired: false,
                    showLanguage: false,
                    showDegree: true,
                    suggestedResume: false,
                    build: function(parts) {
                        return ['Transcript', parts.name, '(' + parts.degree + ')'];
                    }
                },
                offer_letter: {
                    showCompany: true,
                    companyRequired: true,
                    showLanguage: false,
                    showDegree: false,
                    suggestedResume: false,
                    build: function(parts) {
                        return ['OfferLetter', parts.name, parts.date, parts.company];
                    }
                },
                offer_negotiation: {
                    showCompany: true,
                    companyRequired: true,
                    showLanguage: false,
                    showDegree: false,
                    suggestedResume: false,
                    build: function(parts) {
                        return ['OfferNegotiation', parts.name, parts.date, parts.company];
                    }
                },
                company_personal_information: {
                    showCompany: true,
                    companyRequired: true,
                    showLanguage: false,
                    showDegree: false,
                    suggestedResume: false,
                    build: function(parts) {
                        return [parts.company, 'PersonalInformation', parts.name, parts.date];
                    }
                },
                upload: {
                    showCompany: true,
                    companyRequired: true,
                    showLanguage: false,
                    showDegree: false,
                    suggestedResume: false,
                    build: function(parts) {
                        return ['Upload', parts.name, parts.company, parts.date];
                    }
                },
                upload_indb: {
                    showCompany: true,
                    companyRequired: true,
                    showLanguage: false,
                    showDegree: false,
                    suggestedResume: false,
                    build: function(parts) {
                        return ['Upload', parts.name, 'InDB', parts.company, parts.date];
                    }
                },
                chat: {
                    showCompany: false,
                    companyRequired: false,
                    showLanguage: false,
                    showDegree: false,
                    suggestedResume: false,
                    build: function(parts) {
                        return ['Chat', parts.name, parts.date];
                    }
                },
                interview_feedback: {
                    showCompany: true,
                    companyRequired: true,
                    showLanguage: false,
                    showDegree: false,
                    suggestedResume: false,
                    build: function(parts) {
                        return ['InterviewFeedback', parts.name, parts.company, parts.date];
                    }
                },
                rejection: {
                    showCompany: true,
                    companyRequired: true,
                    showLanguage: false,
                    showDegree: false,
                    suggestedResume: false,
                    build: function(parts) {
                        return ['Rejection', parts.name, parts.company, parts.date];
                    }
                },
                other: {
                    showCompany: false,
                    companyRequired: false,
                    showLanguage: false,
                    showDegree: false,
                    suggestedResume: false,
                    build: function(parts) {
                        return [parts.originalBaseName];
                    }
                }
            };

            function sanitizeFilenameToken(value)
            {
                value = String(value || '');
                value = value.replace(/[\/\\:\*\?"<>\|]+/g, ' ');
                value = value.replace(/\s+/g, '_');
                value = value.replace(/_+/g, '_');
                value = value.replace(/^_+|_+$/g, '');
                return value;
            }

            function stripExtension(fileName)
            {
                var lastDot = fileName.lastIndexOf('.');
                if (lastDot <= 0)
                {
                    return fileName;
                }

                return fileName.substring(0, lastDot);
            }

            function getFileExtension(fileName)
            {
                var lastDot = fileName.lastIndexOf('.');
                if (lastDot <= 0)
                {
                    return '';
                }

                return fileName.substring(lastDot + 1).toLowerCase();
            }

            function getCurrentAttachmentFilename()
            {
                var fileField = document.getElementById('file');
                if (!fileField || !fileField.value)
                {
                    return '';
                }

                return fileField.value.replace(/^.*[\\\/]/, '');
            }

            function escapeRegExp(value)
            {
                return String(value || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function buildVersionedFilename(baseName, extension)
            {
                var normalizedBaseName = String(baseName || '');
                var normalizedExtension = String(extension || '').toLowerCase();
                var index;
                var existingName;
                var regex;
                var nextVersion = 1;
                var hasBaseFile = false;

                if (normalizedBaseName === '')
                {
                    return '';
                }

                if (normalizedExtension === '')
                {
                    return normalizedBaseName;
                }

                for (index = 0; index < existingAttachmentFileNames.length; index++)
                {
                    existingName = String(existingAttachmentFileNames[index] || '');
                    if (existingName.toLowerCase() == (normalizedBaseName + '.' + normalizedExtension).toLowerCase())
                    {
                        hasBaseFile = true;
                        continue;
                    }

                    regex = new RegExp('^' + escapeRegExp(normalizedBaseName) + '_V(\\d+)\\.' + escapeRegExp(normalizedExtension) + '$', 'i');
                    if (regex.test(existingName))
                    {
                        var match = existingName.match(regex);
                        var version = parseInt(match[1], 10);
                        if (!isNaN(version) && version >= nextVersion)
                        {
                            nextVersion = version + 1;
                        }
                    }
                }

                if (!hasBaseFile)
                {
                    return normalizedBaseName;
                }

                return normalizedBaseName + '_V' + nextVersion;
            }

            function getCandidateNameToken()
            {
                return sanitizeFilenameToken(
                    [document.getElementById('candidateFirstName').value, document.getElementById('candidateLastName').value].join(' ')
                );
            }

            function toggleAttachmentNamingFields()
            {
                var fileType = document.getElementById('fileType').value;
                var rule = attachmentNamingRules[fileType] || attachmentNamingRules.other;
                var companyRow = document.getElementById('attachmentCompanyRow');
                var languageRow = document.getElementById('attachmentLanguageRow');
                var degreeRow = document.getElementById('attachmentDegreeRow');
                var companyLabel = document.getElementById('attachmentCompanyLabel');
                var companyNote = document.getElementById('attachmentCompanyNote');
                var resumeValue = document.getElementById('resume');

                companyRow.style.display = rule.showCompany ? '' : 'none';
                languageRow.style.display = rule.showLanguage ? '' : 'none';
                degreeRow.style.display = rule.showDegree ? '' : 'none';

                if (companyLabel)
                {
                    companyLabel.innerHTML = rule.companyRequired ? 'Company:*' : 'Company:';
                }

                if (companyNote)
                {
                    companyNote.style.display = rule.showCompany && !rule.companyRequired ? 'inline' : 'none';
                }

                if (resumeValue)
                {
                    resumeValue.value = rule.suggestedResume ? '1' : '0';
                }
            }

            function updateSuggestedAttachmentFilename()
            {
                var currentFileName = getCurrentAttachmentFilename();
                var currentFileNameField = document.getElementById('currentFilename');
                var suggestedField = document.getElementById('suggestedFilename');
                var extensionHint = document.getElementById('attachmentExtensionHint');
                var fileType = document.getElementById('fileType').value;
                var rule = attachmentNamingRules[fileType] || attachmentNamingRules.other;
                var extension = getFileExtension(currentFileName);
                var company = sanitizeFilenameToken(document.getElementById('attachmentCompany').value);
                var language = sanitizeFilenameToken(document.getElementById('attachmentLanguage').value);
                var degree = sanitizeFilenameToken(document.getElementById('attachmentDegree').value);
                var originalBaseName = sanitizeFilenameToken(stripExtension(currentFileName));
                var nameToken = getCandidateNameToken();
                var dateValue = document.getElementById('attachmentDate').value;
                var tokens;
                var index;
                var filteredTokens = [];
                var suggestedBaseName = '';

                if (currentFileNameField)
                {
                    currentFileNameField.value = currentFileName;
                }

                if (extensionHint)
                {
                    extensionHint.innerHTML = extension ? ('.' + extension) : '(No extension detected)';
                }

                if (!suggestedField)
                {
                    return;
                }

                tokens = rule.build({
                    company: company,
                    date: dateValue,
                    degree: degree,
                    language: language,
                    name: nameToken,
                    originalBaseName: originalBaseName
                });

                for (index = 0; index < tokens.length; index++)
                {
                    if (tokens[index])
                    {
                        filteredTokens.push(tokens[index]);
                    }
                }

                suggestedBaseName = filteredTokens.join('_');
                suggestedField.value = buildVersionedFilename(suggestedBaseName, extension);
                updateFinalAttachmentFilename();
            }

            function getFilenameMode()
            {
                var suggestedOption = document.getElementById('filenameModeSuggested');
                var manualOption = document.getElementById('filenameModeManual');

                if (suggestedOption && suggestedOption.checked)
                {
                    return 'suggested';
                }

                if (manualOption && manualOption.checked)
                {
                    return 'manual';
                }

                return 'original';
            }

            function toggleFilenameModeFields()
            {
                var manualRow = document.getElementById('manualFilenameRow');

                if (!manualRow)
                {
                    return;
                }

                manualRow.style.display = (getFilenameMode() == 'manual') ? '' : 'none';
                updateFinalAttachmentFilename();
            }

            function updateFinalAttachmentFilename()
            {
                var currentFileName = getCurrentAttachmentFilename();
                var suggestedField = document.getElementById('suggestedFilename');
                var manualField = document.getElementById('manualFilename');
                var finalFilenameField = document.getElementById('finalFilename');
                var extension = getFileExtension(currentFileName);
                var suggestedValue = suggestedField ? suggestedField.value : '';
                var manualValue = manualField ? manualField.value : '';

                if (!finalFilenameField)
                {
                    return;
                }

                if (getFilenameMode() == 'original')
                {
                    finalFilenameField.value = currentFileName;
                    updateAttachmentFilenameLengthWarning(finalFilenameField.value);
                    return;
                }

                if (getFilenameMode() == 'manual')
                {
                    if (manualValue == '')
                    {
                        finalFilenameField.value = '';
                        updateAttachmentFilenameLengthWarning('');
                        return;
                    }

                    finalFilenameField.value = extension ? (manualValue + '.' + extension) : manualValue;
                    updateAttachmentFilenameLengthWarning(finalFilenameField.value);
                    return;
                }

                if (suggestedValue == '')
                {
                    finalFilenameField.value = '';
                    updateAttachmentFilenameLengthWarning('');
                    return;
                }

                finalFilenameField.value = extension ? (suggestedValue + '.' + extension) : suggestedValue;
                updateAttachmentFilenameLengthWarning(finalFilenameField.value);
            }

            function updateAttachmentFilenameLengthWarning(fileName)
            {
                var countNode = document.getElementById('filenameLengthCount');
                var warningNode = document.getElementById('filenameLengthWarning');
                var length = String(fileName || '').length;

                if (countNode)
                {
                    countNode.innerHTML = length + ' / 255';
                    countNode.style.color = (length > 255) ? '#cc0000' : '#666666';
                }

                if (warningNode)
                {
                    warningNode.style.display = (length > 255) ? 'block' : 'none';
                }
            }

            function initializeAttachmentNaming()
            {
                toggleAttachmentNamingFields();
                updateSuggestedAttachmentFilename();
                toggleFilenameModeFields();
            }
        </script>
        <form name="createAttachmentForm" id="createAttachmentForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=createAttachment" enctype="multipart/form-data" method="post" onsubmit="return checkCreateAttachmentForm(document.createAttachmentForm);">
            <input type="hidden" name="postback" id="postback" value="postback" />
            <input type="hidden" id="candidateID" name="candidateID" value="<?php echo($this->candidateID); ?>" />
            <input type="hidden" id="candidateFirstName" value="<?php echo htmlspecialchars($firstName); ?>" />
            <input type="hidden" id="candidateLastName" value="<?php echo htmlspecialchars($lastName); ?>" />
            <input type="hidden" id="resume" name="resume" value="1" />

            <table class="editTable" style="width: 100%;">
                <tr>
                    <td class="tdVertical">Attachment:</td>
                    <td class="tdData">
                        <input type="file" id="file" name="file" onchange="updateSuggestedAttachmentFilename();" />
                        <div style="margin-top: 4px; color: #666666; font-size: 11px;">Supported formats: doc, pdf, html, txt, md, jpg, png</div>
                    </td>
                </tr>
                <tr>
                    <td class="tdVertical">File Type:</td>
                    <td class="tdData">
                        <select id="fileType" name="fileType" onchange="toggleAttachmentNamingFields(); updateSuggestedAttachmentFilename();" style="width: 360px;">
                            <option value="resume">Resume / 原稿履歷</option>
                            <option value="jecho_report">Jecho Report / 修改後履歷</option>
                            <option value="personal_agreement">Personal Agreement / 個資同意書</option>
                            <option value="portfolio">Portfolio / 作品集</option>
                            <option value="transcript">Transcript / 成績單</option>
                            <option value="offer_letter">Offer Letter / 聘書</option>
                            <option value="offer_negotiation">Offer Negotiation / Offer協商資訊</option>
                            <option value="company_personal_information">Company Personal Information / 客戶人事資料表</option>
                            <option value="upload">Upload / 上傳客戶系統截圖</option>
                            <option value="upload_indb">Upload InDB / 人選已在客戶資料庫</option>
                            <option value="chat">Chat / 與人選對話截圖</option>
                            <option value="interview_feedback">Interview Feedback / 面試回饋記錄</option>
                            <option value="rejection">Rejection / 客戶系統拒絕訊息</option>
                            <option value="other">Other / 其他</option>
                        </select>
                    </td>
                </tr>
                <tr id="attachmentCompanyRow" style="display: none;">
                    <td class="tdVertical"><label id="attachmentCompanyLabel" for="attachmentCompany">Company:</label></td>
                    <td class="tdData">
                        <input type="text" id="attachmentCompany" name="attachmentCompany" style="width: 240px;" onkeyup="updateSuggestedAttachmentFilename();" onchange="updateSuggestedAttachmentFilename();" />
                        <span id="attachmentCompanyNote" style="display: none; color: #666666; font-size: 11px; margin-left: 6px;">Optional for this file type.</span>
                    </td>
                </tr>
                <tr id="attachmentLanguageRow" style="display: none;">
                    <td class="tdVertical">Language:</td>
                    <td class="tdData">
                        <select id="attachmentLanguage" name="attachmentLanguage" onchange="updateSuggestedAttachmentFilename();" style="width: 120px;">
                            <option value="en">en</option>
                            <option value="zh">zh</option>
                            <option value="cn">cn</option>
                        </select>
                    </td>
                </tr>
                <tr id="attachmentDegreeRow" style="display: none;">
                    <td class="tdVertical">Degree:</td>
                    <td class="tdData">
                        <select id="attachmentDegree" name="attachmentDegree" onchange="updateSuggestedAttachmentFilename();" style="width: 120px;">
                            <option value="B">B</option>
                            <option value="M">M</option>
                            <option value="PhD">PhD</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="tdVertical">Date:</td>
                    <td class="tdData">
                        <input type="text" id="attachmentDate" name="attachmentDate" value="<?php echo date('Ymd'); ?>" style="width: 120px;" onkeyup="updateSuggestedAttachmentFilename();" onchange="updateSuggestedAttachmentFilename();" />
                    </td>
                </tr>
                <tr>
                    <td class="tdVertical">Current Filename:</td>
                    <td class="tdData"><input type="text" id="currentFilename" value="" readonly="readonly" style="width: 360px; background-color: #f5f5f5;" /></td>
                </tr>
                <tr>
                    <td class="tdVertical">Suggested Filename:</td>
                    <td class="tdData">
                        <input type="text" id="suggestedFilename" name="suggestedFilename" value="" readonly="readonly" style="width: 360px; background-color: #f5f5f5;" />
                        <div style="margin-top: 4px; color: #666666; font-size: 11px;">Extension will stay as the uploaded file: <span id="attachmentExtensionHint">(Select a file first)</span></div>
                        <div style="margin-top: 4px; color: #666666; font-size: 11px;">If the same filename already exists, the system will automatically suggest the next version such as <b>_V1</b>, <b>_V2</b>.</div>
                    </td>
                </tr>
                <tr>
                    <td class="tdVertical">Filename Mode:</td>
                    <td class="tdData">
                        <label><input type="radio" id="filenameModeSuggested" name="filenameMode" value="suggested" checked="checked" onclick="toggleFilenameModeFields();" /> Suggested Filename (Recommended)</label><br />
                        <label><input type="radio" id="filenameModeManual" name="filenameMode" value="manual" onclick="toggleFilenameModeFields();" /> Enter Filename Manually</label><br />
                        <label><input type="radio" id="filenameModeOriginal" name="filenameMode" value="original" onclick="toggleFilenameModeFields();" /> Keep Original Uploaded Filename</label>
                    </td>
                </tr>
                <tr id="manualFilenameRow" style="display: none;">
                    <td class="tdVertical">Manual Filename:</td>
                    <td class="tdData">
                        <input type="text" id="manualFilename" name="manualFilename" value="" style="width: 360px;" onkeyup="updateFinalAttachmentFilename();" onchange="updateFinalAttachmentFilename();" />
                        <div style="margin-top: 4px; color: #666666; font-size: 11px;">Enter the filename without the extension. The uploaded file extension will be kept automatically.</div>
                    </td>
                </tr>
                <tr>
                    <td class="tdVertical">Final Filename:</td>
                    <td class="tdData">
                        <input type="text" id="finalFilename" value="" readonly="readonly" style="width: 360px; background-color: #f5f5f5; font-weight: bold;" />
                        <div style="margin-top: 4px; color: #666666; font-size: 11px;">This is the filename that will actually be uploaded.</div>
                        <div id="filenameLengthCount" style="margin-top: 4px; color: #666666; font-size: 11px;">0 / 255</div>
                        <div id="filenameLengthWarning" style="display: none; margin-top: 4px; color: #cc0000; font-size: 11px;">Filename is too long. Please shorten it to 255 characters or fewer.</div>
                    </td>
                </tr>
            </table>
            <input type="submit" class="button" name="submit" id="submit" value="Create Attachment" />&nbsp;
            <input type="button" class="button" name="cancel" value="Cancel" onclick="parentHidePopWin();" />
        </form>
        <script type="text/javascript">
            initializeAttachmentNaming();
        </script>
    <?php else: ?>
        <?php if(isset($this->resumeText) && $this->resumeText == ''): ?>
            <p>The file has been successfully attached, but CATS was unable to index the resume keywords to make the document searchable.  The file format may be unsupported by CATS.</p>
        <?php else: ?>
            <p>The file has been successfully attached.</p>
        <?php endif; ?>
        <form>
            <input type="button" name="close" value="Close" onclick="parentHidePopWinRefresh();" />
        </form>
    <?php endif; ?>
    </body>
</html>
