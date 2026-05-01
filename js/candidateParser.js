var transferButtonLocked = false;
var aiParsedDocumentBaseline = null;

function normalizeDocumentForAIParse(value)
{
    return (value || '').replace(/\s+/g, ' ').trim();
}

function hasPriorAIParse()
{
    var logID = document.getElementById('aiParseLogID');
    return !!(
        (logID && logID.value !== '') ||
        document.getElementById('aiSuggestionTable')
    );
}

function initializeAIParsedDocumentBaseline()
{
    var text = document.getElementById('documentText');
    if (!text || !hasPriorAIParse())
    {
        aiParsedDocumentBaseline = null;
        return;
    }

    aiParsedDocumentBaseline = normalizeDocumentForAIParse(text.value);
}

function hasDocumentChangedSinceAIParse()
{
    var text = document.getElementById('documentText');
    if (!text || aiParsedDocumentBaseline === null)
    {
        return true;
    }

    return normalizeDocumentForAIParse(text.value) !== aiParsedDocumentBaseline;
}

function isAIParseAllowedByDocumentState()
{
    var text = document.getElementById('documentText');
    var file = document.getElementById('documentFile');
    var hasText = !!(text && normalizeDocumentForAIParse(text.value) !== '');
    var hasFile = !!(file && file.value !== '');

    if (hasFile)
    {
        return true;
    }

    return hasText && hasDocumentChangedSinceAIParse();
}

function setTransferButtonEnabled(enabled)
{
    var button = document.getElementById('transfer');

    if (!button)
    {
        return;
    }

    if (transferButtonLocked)
    {
        setAIJechoReportCheckboxEnabled(false);
        return;
    }

    enabled = enabled && isAIParseAllowedByDocumentState();

    button.disabled = !enabled;
    button.style.cursor = enabled ? 'pointer' : 'not-allowed';
    button.style.background = enabled ? '#3f84c5' : '#d7dfe8';
    button.style.borderColor = enabled ? '#2f6fad' : '#b8c3cf';
    button.style.color = enabled ? '#ffffff' : '#6b7785';
    button.innerHTML = 'AI 解析履歷';
    button.title = enabled ? '' : getAIParseDisabledReason();
    setAIJechoReportCheckboxEnabled(enabled);
}

function getAIParseDisabledReason()
{
    var text = document.getElementById('documentText');
    var file = document.getElementById('documentFile');
    var hasText = !!(text && normalizeDocumentForAIParse(text.value) !== '');
    var hasFile = !!(file && file.value !== '');

    if (!hasText && !hasFile)
    {
        return '請先上傳或貼上履歷內容。';
    }

    if (aiParsedDocumentBaseline !== null && !hasDocumentChangedSinceAIParse())
    {
        return '履歷內容尚未有實質變更，無需重新 AI 解析。';
    }

    return '';
}

function submitCandidateParserForm()
{
    var form = document.getElementById('addCandidateForm');
    if (!form)
    {
        form = document.getElementById('editCandidateForm');
    }

    if (!form)
    {
        return;
    }

    if (window.HTMLFormElement && HTMLFormElement.prototype.submit)
    {
        HTMLFormElement.prototype.submit.call(form);
    }
    else
    {
        form.submit();
    }
}

function loadDocumentFileContents()
{
    var file = document.getElementById('documentFile');
    var obj = document.getElementById('loadDocument');
    var btn = document.getElementById('documentLoad');

    obj.value = '';
    obj.value = 'true';

    if (btn)
    {
        btn.disabled = true;
        btn.value = '上傳中...';
    }

    window.setTimeout(function() {
        submitCandidateParserForm();
    }, 10);
}

function parseDocumentFileContents(silent)
{
    var text = document.getElementById('documentText');
    var file = document.getElementById('documentFile');
    var obj = document.getElementById('loadDocument');
    var obj2 = document.getElementById('parseDocument');
    var button = document.getElementById('transfer');

    obj.value = '';
    obj2.value = '';

    if (!isAIParseAllowedByDocumentState())
    {
        documentCheck();
        return;
    }

    if (text.value == '' && file.value == '')
    {
        return;
    }

    if (!silent)
    {
        var confirmMessage = document.getElementById('editCandidateForm')
            ? 'AI 解析履歷後會顯示可套用欄位，您可以選擇要補充哪些資料。是否繼續？'
            : 'AI 解析履歷會覆蓋目前表單中的候選人欄位資料，是否繼續？';

        if (!window.confirm(confirmMessage))
        {
            return;
        }
    }

    obj.value = 'true';
    obj2.value = 'true';
    syncAIJechoReportRequestFromCheckbox();
    transferButtonLocked = true;

    if (button)
    {
        button.disabled = true;
        button.style.cursor = 'wait';
        button.style.background = '#d7dfe8';
        button.style.borderColor = '#b8c3cf';
        button.style.color = '#6b7785';
        button.innerHTML = 'AI 解析中...';
        button.onclick = null;
    }
    setAIJechoReportCheckboxEnabled(false);
    var loading = document.getElementById('aiParsingLoading');
    if (loading)
    {
        loading.style.display = 'inline';
    }

    window.setTimeout(function() {
        submitCandidateParserForm();
    }, 50);
}

function syncAIJechoReportRequestFromCheckbox()
{
    var checkbox = document.getElementById('aiSavePasteAsJechoReport');
    var requested = document.getElementById('aiJechoReportRequested');

    if (!checkbox || !requested)
    {
        return;
    }

    requested.value = checkbox.checked ? '1' : '0';
}

function setAIJechoReportCheckboxEnabled(enabled)
{
    var checkbox = document.getElementById('aiSavePasteAsJechoReport');

    if (!checkbox)
    {
        return;
    }

    checkbox.disabled = !enabled;
}

function documentFileChange()
{
    var obj = document.getElementById('documentLoad');
    if (obj && obj.value != '')
    {
        setTransferButtonEnabled(true);
        obj.disabled=false;
    }
    else
    {
        setTransferButtonEnabled(false);
        obj.disabled=true;
    }
}

function documentCheck()
{
    var obj = document.getElementById('documentText');
    var file = document.getElementById('documentFile');
    var tempFile = document.getElementById('documentTempFile');

    if ((obj.value).length > 0 || file.value != '')
    {
        setTransferButtonEnabled(true);
    }
    else
    {
        setTransferButtonEnabled(false);
    }
}

function removeDocumentFile()
{
    var obj1 = document.getElementById('documentText');
    var obj2 = document.getElementById('documentTempFile');
    var obj3 = document.getElementById('loadDocument');
    var obj4 = document.getElementById('parseDocument');
    var obj5 = document.getElementById('showAttachmentDetails');
    var obj6 = document.getElementById('aiParseLogID');
    var obj7 = document.getElementById('aiDocumentLanguage');
    var obj8 = document.getElementById('aiResumeExtension');

    if ((obj2.value != '') || (obj6 && obj6.value != ''))
    {
        var requestURL = 'index.php?m=candidates&a=removeDocumentTempFile&documentTempFile=' + encodeURIComponent(obj2.value);
        if (obj6 && obj6.value != '')
        {
            requestURL += '&aiParseLogID=' + encodeURIComponent(obj6.value);
        }

        if (window.XMLHttpRequest)
        {
            try
            {
                var request = new XMLHttpRequest();
                request.open('GET', requestURL, false);
                request.send(null);
            }
            catch (e)
            {
            }
        }
    }

    obj1.value = '';
    obj2.value = '';
    obj3.value = '';
    obj4.value = '';
    transferButtonLocked = false;
    aiParsedDocumentBaseline = null;
    if (obj6)
    {
        obj6.value = '';
    }
    if (obj7)
    {
        obj7.value = '';
    }
    if (obj8)
    {
        obj8.value = '';
    }
    var obj9 = document.getElementById('aiJechoReportMarkdown');
    if (obj9)
    {
        obj9.value = '';
    }
    var obj10 = document.getElementById('aiJechoReportRequested');
    if (obj10)
    {
        obj10.value = '0';
    }
    if (obj5)
    {
        obj5.style.display = 'none';
    }
}

if (window.addEventListener)
{
    window.addEventListener('load', function() {
        var text = document.getElementById('documentText');
        if (text)
        {
            text.oninput = documentCheck;
            text.onkeyup = documentCheck;
        }
        initializeAIParsedDocumentBaseline();
        documentCheck();
    });
}

function resetAddCandidateForm()
{
    var form = document.getElementById('addCandidateForm');

    if (!form)
    {
        return false;
    }

    removeDocumentFile();
    window.location.href = form.action;

    return false;
}
