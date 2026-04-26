<?php /* $Id: CreateAttachmentModal.tpl 3093 2007-09-24 21:09:45Z brian $ */ ?>
<?php TemplateUtility::printModalHeader('Companies', array('modules/companies/validator.js'), 'Create Company Attachment'); ?>

    <?php if (!$this->isFinishedMode): ?>
        <?php
            $companyName = isset($this->companyData['name']) ? $this->companyData['name'] : '';
            $attachmentFileNames = isset($this->attachmentFileNames) ? $this->attachmentFileNames : array();
        ?>
        <script type="text/javascript">
            var existingCompanyAttachmentFileNames = <?php echo json_encode(array_values($attachmentFileNames)); ?>;
            var companyAttachmentNamingRules = {
                company_hr_information: {
                    showLanguage: false,
                    build: function(parts) {
                        return [parts.company, 'HR_Information', parts.date];
                    }
                },
                company_introduction: {
                    showLanguage: true,
                    build: function(parts) {
                        return [parts.company, 'Company_Introduction', parts.language];
                    }
                },
                traffic_information: {
                    showLanguage: false,
                    build: function(parts) {
                        return [parts.company, 'Traffic_Information'];
                    }
                },
                client_bd: {
                    showLanguage: false,
                    build: function(parts) {
                        return ['Client_BD', parts.company, parts.date];
                    }
                },
                other: {
                    showLanguage: false,
                    build: function(parts) {
                        return [parts.originalBaseName];
                    }
                }
            };

            function sanitizeCompanyAttachmentToken(value)
            {
                value = String(value || '');
                value = value.replace(/[\/\\:\*\?"<>\|]+/g, ' ');
                value = value.replace(/\s+/g, '_');
                value = value.replace(/_+/g, '_');
                value = value.replace(/^_+|_+$/g, '');
                return value;
            }

            function stripCompanyAttachmentExtension(fileName)
            {
                var lastDot = fileName.lastIndexOf('.');
                if (lastDot <= 0)
                {
                    return fileName;
                }

                return fileName.substring(0, lastDot);
            }

            function getCompanyAttachmentExtension(fileName)
            {
                var lastDot = fileName.lastIndexOf('.');
                if (lastDot <= 0)
                {
                    return '';
                }

                return fileName.substring(lastDot + 1).toLowerCase();
            }

            function getCurrentCompanyAttachmentFilename()
            {
                var fileField = document.getElementById('file');
                if (!fileField || !fileField.value)
                {
                    return '';
                }

                return fileField.value.replace(/^.*[\\\/]/, '');
            }

            function escapeCompanyAttachmentRegExp(value)
            {
                return String(value || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function buildCompanyAttachmentVersionedFilename(baseName, extension)
            {
                var normalizedBaseName = String(baseName || '');
                var normalizedExtension = String(extension || '').toLowerCase();
                var nextVersion = 1;
                var hasBaseFile = false;
                var index;
                var existingName;
                var regex;

                if (normalizedBaseName === '')
                {
                    return '';
                }

                if (normalizedExtension === '')
                {
                    return normalizedBaseName;
                }

                for (index = 0; index < existingCompanyAttachmentFileNames.length; index++)
                {
                    existingName = String(existingCompanyAttachmentFileNames[index] || '');
                    if (existingName.toLowerCase() == (normalizedBaseName + '.' + normalizedExtension).toLowerCase())
                    {
                        hasBaseFile = true;
                        continue;
                    }

                    regex = new RegExp('^' + escapeCompanyAttachmentRegExp(normalizedBaseName) + '_V(\\d+)\\.' + escapeCompanyAttachmentRegExp(normalizedExtension) + '$', 'i');
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

            function getCompanyFilenameMode()
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

            function toggleCompanyAttachmentNamingFields()
            {
                var fileType = document.getElementById('fileType').value;
                var rule = companyAttachmentNamingRules[fileType] || companyAttachmentNamingRules.other;
                var languageRow = document.getElementById('attachmentLanguageRow');

                if (languageRow)
                {
                    languageRow.style.display = rule.showLanguage ? '' : 'none';
                }
            }

            function toggleCompanyFilenameModeFields()
            {
                var manualRow = document.getElementById('manualFilenameRow');

                if (manualRow)
                {
                    manualRow.style.display = (getCompanyFilenameMode() == 'manual') ? '' : 'none';
                }

                updateFinalCompanyAttachmentFilename();
            }

            function updateSuggestedCompanyAttachmentFilename()
            {
                var currentFileName = getCurrentCompanyAttachmentFilename();
                var currentFileNameField = document.getElementById('currentFilename');
                var suggestedField = document.getElementById('suggestedFilename');
                var extensionHint = document.getElementById('attachmentExtensionHint');
                var fileType = document.getElementById('fileType').value;
                var rule = companyAttachmentNamingRules[fileType] || companyAttachmentNamingRules.other;
                var extension = getCompanyAttachmentExtension(currentFileName);
                var company = sanitizeCompanyAttachmentToken(document.getElementById('companyNameToken').value);
                var language = sanitizeCompanyAttachmentToken(document.getElementById('attachmentLanguage').value);
                var originalBaseName = sanitizeCompanyAttachmentToken(stripCompanyAttachmentExtension(currentFileName));
                var dateValue = document.getElementById('attachmentDate').value;
                var tokens;
                var filteredTokens = [];
                var index;

                if (currentFileNameField)
                {
                    currentFileNameField.value = currentFileName;
                }

                if (extensionHint)
                {
                    extensionHint.innerHTML = extension ? ('.' + extension) : '(Select a file first)';
                }

                if (!suggestedField)
                {
                    return;
                }

                tokens = rule.build({
                    company: company,
                    date: dateValue,
                    language: language,
                    originalBaseName: originalBaseName
                });

                for (index = 0; index < tokens.length; index++)
                {
                    if (tokens[index])
                    {
                        filteredTokens.push(tokens[index]);
                    }
                }

                suggestedField.value = buildCompanyAttachmentVersionedFilename(filteredTokens.join('_'), extension);
                updateFinalCompanyAttachmentFilename();
            }

            function updateFinalCompanyAttachmentFilename()
            {
                var currentFileName = getCurrentCompanyAttachmentFilename();
                var suggestedField = document.getElementById('suggestedFilename');
                var manualField = document.getElementById('manualFilename');
                var finalFilenameField = document.getElementById('finalFilename');
                var extension = getCompanyAttachmentExtension(currentFileName);
                var suggestedValue = suggestedField ? suggestedField.value : '';
                var manualValue = manualField ? manualField.value : '';

                if (!finalFilenameField)
                {
                    return;
                }

                if (getCompanyFilenameMode() == 'original')
                {
                    finalFilenameField.value = currentFileName;
                    updateCompanyAttachmentFilenameLengthWarning(finalFilenameField.value);
                    return;
                }

                if (getCompanyFilenameMode() == 'manual')
                {
                    finalFilenameField.value = manualValue ? (manualValue + (extension ? '.' + extension : '')) : '';
                    updateCompanyAttachmentFilenameLengthWarning(finalFilenameField.value);
                    return;
                }

                finalFilenameField.value = suggestedValue ? (suggestedValue + (extension ? '.' + extension : '')) : '';
                updateCompanyAttachmentFilenameLengthWarning(finalFilenameField.value);
            }

            function updateCompanyAttachmentFilenameLengthWarning(fileName)
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

            function initializeCompanyAttachmentNaming()
            {
                toggleCompanyAttachmentNamingFields();
                updateSuggestedCompanyAttachmentFilename();
                toggleCompanyFilenameModeFields();
            }
        </script>
        <form name="createAttachmentForm" id="createAttachmentForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=createAttachment" enctype="multipart/form-data" method="post" onsubmit="return checkAttachmentForm(document.createAttachmentForm);">
            <input type="hidden" name="postback" id="postback" value="postback" />
            <input type="hidden" id="companyID" name="companyID" value="<?php echo($this->companyID); ?>" />
            <input type="hidden" id="companyNameToken" value="<?php echo htmlspecialchars($companyName); ?>" />

            <table class="editTable" style="width: 100%;">
                <tr>
                    <td class="tdVertical">Attachment:</td>
                    <td class="tdData">
                        <input type="file" id="file" name="file" onchange="updateSuggestedCompanyAttachmentFilename();" />
                        <div style="margin-top: 4px; color: #666666; font-size: 11px;">Supported formats: doc, pdf, html, txt, md, jpg, png</div>
                    </td>
                </tr>
                <tr>
                    <td class="tdVertical">File Type:</td>
                    <td class="tdData">
                        <select id="fileType" name="fileType" onchange="toggleCompanyAttachmentNamingFields(); updateSuggestedCompanyAttachmentFilename();" style="width: 360px;">
                            <option value="company_hr_information">Company HR Information / 客戶系統招募HR資訊截圖</option>
                            <option value="company_introduction">Company Introduction / 客戶公司簡介</option>
                            <option value="traffic_information">Traffic Information / 客戶公司交通資訊</option>
                            <option value="client_bd">Client BD / 客戶業務發展資訊</option>
                            <option value="other">Other / 其他</option>
                        </select>
                    </td>
                </tr>
                <tr id="attachmentLanguageRow" style="display: none;">
                    <td class="tdVertical">Language:</td>
                    <td class="tdData">
                        <select id="attachmentLanguage" name="attachmentLanguage" onchange="updateSuggestedCompanyAttachmentFilename();" style="width: 120px;">
                            <option value="en">en</option>
                            <option value="zh">zh</option>
                            <option value="cn">cn</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="tdVertical">Date:</td>
                    <td class="tdData">
                        <input type="text" id="attachmentDate" name="attachmentDate" value="<?php echo date('Ymd'); ?>" style="width: 120px;" onkeyup="updateSuggestedCompanyAttachmentFilename();" onchange="updateSuggestedCompanyAttachmentFilename();" />
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
                        <label><input type="radio" id="filenameModeSuggested" name="filenameMode" value="suggested" checked="checked" onclick="toggleCompanyFilenameModeFields();" /> Suggested Filename (Recommended)</label><br />
                        <label><input type="radio" id="filenameModeManual" name="filenameMode" value="manual" onclick="toggleCompanyFilenameModeFields();" /> Enter Filename Manually</label><br />
                        <label><input type="radio" id="filenameModeOriginal" name="filenameMode" value="original" onclick="toggleCompanyFilenameModeFields();" /> Keep Original Uploaded Filename</label>
                    </td>
                </tr>
                <tr id="manualFilenameRow" style="display: none;">
                    <td class="tdVertical">Manual Filename:</td>
                    <td class="tdData">
                        <input type="text" id="manualFilename" name="manualFilename" value="" style="width: 360px;" onkeyup="updateFinalCompanyAttachmentFilename();" onchange="updateFinalCompanyAttachmentFilename();" />
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
            initializeCompanyAttachmentNaming();
        </script>
    <?php else: ?>
        <p>The file has been successfully attached.</p>

        <form>
            <input type="button" name="close" value="Close" onclick="parentHidePopWinRefresh();" />
        </form>
    <?php endif; ?>
    </body>
</html>
