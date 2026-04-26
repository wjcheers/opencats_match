<?php /* $Id: CreateAttachmentModal.tpl 3093 2007-09-24 21:09:45Z brian $ */ ?>
<?php TemplateUtility::printModalHeader('Job Order', array('modules/joborders/validator.js'), 'Create Job Order Attachment'); ?>

    <?php if (!$this->isFinishedMode): ?>
        <?php
            $companyName = isset($this->jobOrderData['companyName']) ? $this->jobOrderData['companyName'] : '';
            $jobTitle = isset($this->jobOrderData['title']) ? $this->jobOrderData['title'] : '';
            $attachmentFileNames = isset($this->attachmentFileNames) ? $this->attachmentFileNames : array();
        ?>
        <script type="text/javascript">
            var existingJobOrderAttachmentFileNames = <?php echo json_encode(array_values($attachmentFileNames)); ?>;
            var jobOrderAttachmentNamingRules = {
                job_description: {
                    build: function(parts) {
                        return [parts.company, parts.title, parts.date];
                    }
                },
                recruitment_briefing: {
                    build: function(parts) {
                        return [parts.company, 'Recruitment', parts.date];
                    }
                },
                interview_questions: {
                    build: function(parts) {
                        return [parts.company, 'InterviewQuestions', parts.date];
                    }
                },
                print_screen: {
                    build: function(parts) {
                        return [parts.company, 'PrintScreen', parts.date];
                    }
                },
                other: {
                    build: function(parts) {
                        return [parts.originalBaseName];
                    }
                }
            };

            function sanitizeJobOrderAttachmentToken(value)
            {
                value = String(value || '');
                value = value.replace(/[\/\\:\*\?"<>\|]+/g, ' ');
                value = value.replace(/\s+/g, '_');
                value = value.replace(/_+/g, '_');
                value = value.replace(/^_+|_+$/g, '');
                return value;
            }

            function stripJobOrderAttachmentExtension(fileName)
            {
                var lastDot = fileName.lastIndexOf('.');
                if (lastDot <= 0)
                {
                    return fileName;
                }

                return fileName.substring(0, lastDot);
            }

            function getJobOrderAttachmentExtension(fileName)
            {
                var lastDot = fileName.lastIndexOf('.');
                if (lastDot <= 0)
                {
                    return '';
                }

                return fileName.substring(lastDot + 1).toLowerCase();
            }

            function getCurrentJobOrderAttachmentFilename()
            {
                var fileField = document.getElementById('file');
                if (!fileField || !fileField.value)
                {
                    return '';
                }

                return fileField.value.replace(/^.*[\\\/]/, '');
            }

            function escapeJobOrderAttachmentRegExp(value)
            {
                return String(value || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function buildJobOrderAttachmentVersionedFilename(baseName, extension)
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

                for (index = 0; index < existingJobOrderAttachmentFileNames.length; index++)
                {
                    existingName = String(existingJobOrderAttachmentFileNames[index] || '');
                    if (existingName.toLowerCase() == (normalizedBaseName + '.' + normalizedExtension).toLowerCase())
                    {
                        hasBaseFile = true;
                        continue;
                    }

                    regex = new RegExp('^' + escapeJobOrderAttachmentRegExp(normalizedBaseName) + '_V(\\d+)\\.' + escapeJobOrderAttachmentRegExp(normalizedExtension) + '$', 'i');
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

            function getJobOrderFilenameMode()
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

            function toggleJobOrderFilenameModeFields()
            {
                var manualRow = document.getElementById('manualFilenameRow');

                if (manualRow)
                {
                    manualRow.style.display = (getJobOrderFilenameMode() == 'manual') ? '' : 'none';
                }

                updateFinalJobOrderAttachmentFilename();
            }

            function updateSuggestedJobOrderAttachmentFilename()
            {
                var currentFileName = getCurrentJobOrderAttachmentFilename();
                var currentFileNameField = document.getElementById('currentFilename');
                var suggestedField = document.getElementById('suggestedFilename');
                var extensionHint = document.getElementById('attachmentExtensionHint');
                var fileType = document.getElementById('fileType').value;
                var rule = jobOrderAttachmentNamingRules[fileType] || jobOrderAttachmentNamingRules.other;
                var extension = getJobOrderAttachmentExtension(currentFileName);
                var company = sanitizeJobOrderAttachmentToken(document.getElementById('jobOrderCompanyName').value);
                var title = sanitizeJobOrderAttachmentToken(document.getElementById('jobOrderTitle').value);
                var originalBaseName = sanitizeJobOrderAttachmentToken(stripJobOrderAttachmentExtension(currentFileName));
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
                    title: title,
                    date: dateValue,
                    originalBaseName: originalBaseName
                });

                for (index = 0; index < tokens.length; index++)
                {
                    if (tokens[index])
                    {
                        filteredTokens.push(tokens[index]);
                    }
                }

                suggestedField.value = buildJobOrderAttachmentVersionedFilename(filteredTokens.join('_'), extension);
                updateFinalJobOrderAttachmentFilename();
            }

            function updateFinalJobOrderAttachmentFilename()
            {
                var currentFileName = getCurrentJobOrderAttachmentFilename();
                var suggestedField = document.getElementById('suggestedFilename');
                var manualField = document.getElementById('manualFilename');
                var finalFilenameField = document.getElementById('finalFilename');
                var extension = getJobOrderAttachmentExtension(currentFileName);
                var suggestedValue = suggestedField ? suggestedField.value : '';
                var manualValue = manualField ? manualField.value : '';

                if (!finalFilenameField)
                {
                    return;
                }

                if (getJobOrderFilenameMode() == 'original')
                {
                    finalFilenameField.value = currentFileName;
                    updateJobOrderAttachmentFilenameLengthWarning(finalFilenameField.value);
                    return;
                }

                if (getJobOrderFilenameMode() == 'manual')
                {
                    finalFilenameField.value = manualValue ? (manualValue + (extension ? '.' + extension : '')) : '';
                    updateJobOrderAttachmentFilenameLengthWarning(finalFilenameField.value);
                    return;
                }

                finalFilenameField.value = suggestedValue ? (suggestedValue + (extension ? '.' + extension : '')) : '';
                updateJobOrderAttachmentFilenameLengthWarning(finalFilenameField.value);
            }

            function updateJobOrderAttachmentFilenameLengthWarning(fileName)
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

            function initializeJobOrderAttachmentNaming()
            {
                updateSuggestedJobOrderAttachmentFilename();
                toggleJobOrderFilenameModeFields();
            }
        </script>
        <form name="createAttachmentForm" id="createAttachmentForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=createAttachment" enctype="multipart/form-data" method="post" onsubmit="return checkAttachmentForm(document.createAttachmentForm);">
            <input type="hidden" name="postback" id="postback" value="postback" />
            <input type="hidden" id="jobOrderID" name="jobOrderID" value="<?php echo($this->jobOrderID); ?>" />
            <input type="hidden" id="jobOrderCompanyName" value="<?php echo htmlspecialchars($companyName); ?>" />
            <input type="hidden" id="jobOrderTitle" value="<?php echo htmlspecialchars($jobTitle); ?>" />

            <table class="editTable" style="width: 100%;">
                <tr>
                    <td class="tdVertical">Attachment:</td>
                    <td class="tdData">
                        <input type="file" id="file" name="file" onchange="updateSuggestedJobOrderAttachmentFilename();" />
                        <div style="margin-top: 4px; color: #666666; font-size: 11px;">Supported formats: doc, pdf, html, txt, md, jpg, png</div>
                    </td>
                </tr>
                <tr>
                    <td class="tdVertical">File Type:</td>
                    <td class="tdData">
                        <select id="fileType" name="fileType" onchange="updateSuggestedJobOrderAttachmentFilename();" style="width: 360px;">
                            <option value="job_description">Job Description / 職缺說明</option>
                            <option value="recruitment_briefing">Recruitment Briefing / 招募需求</option>
                            <option value="interview_questions">Interview Questions / 面試題</option>
                            <option value="print_screen">Print Screen / 截圖</option>
                            <option value="other">Other / 其他</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="tdVertical">Date:</td>
                    <td class="tdData">
                        <input type="text" id="attachmentDate" name="attachmentDate" value="<?php echo date('Ymd'); ?>" style="width: 120px;" onkeyup="updateSuggestedJobOrderAttachmentFilename();" onchange="updateSuggestedJobOrderAttachmentFilename();" />
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
                        <label><input type="radio" id="filenameModeSuggested" name="filenameMode" value="suggested" checked="checked" onclick="toggleJobOrderFilenameModeFields();" /> Suggested Filename (Recommended)</label><br />
                        <label><input type="radio" id="filenameModeManual" name="filenameMode" value="manual" onclick="toggleJobOrderFilenameModeFields();" /> Enter Filename Manually</label><br />
                        <label><input type="radio" id="filenameModeOriginal" name="filenameMode" value="original" onclick="toggleJobOrderFilenameModeFields();" /> Keep Original Uploaded Filename</label>
                    </td>
                </tr>
                <tr id="manualFilenameRow" style="display: none;">
                    <td class="tdVertical">Manual Filename:</td>
                    <td class="tdData">
                        <input type="text" id="manualFilename" name="manualFilename" value="" style="width: 360px;" onkeyup="updateFinalJobOrderAttachmentFilename();" onchange="updateFinalJobOrderAttachmentFilename();" />
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
            <input type="button" class="button" name="close" value="Cancel" onclick="parentHidePopWin();" />
        </form>
        <script type="text/javascript">
            initializeJobOrderAttachmentNaming();
        </script>
    <?php else: ?>
        <p>The file has been successfully attached.</p>

        <form>
            <input type="button" name="close" value="Close" onclick="parentHidePopWinRefresh();" />
        </form>
    <?php endif; ?>
    </body>
</html>
