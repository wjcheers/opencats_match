/*
 * CATS
 * Candidates Form Validation
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * $Id: validator.js 2646 2007-07-09 16:40:31Z Andrew $
 */

function onSubmitFalse()
{
    return false;
}

function checkAddForm(form)
{
    var errorMessage = '';

    errorMessage += checkFirstName();
    errorMessage += checkLastName();
    errorMessage += checkCurrentPay();
    errorMessage += checkDesiredPay();
    errorMessage += checkBirthYear();
    errorMessage += checkPhoneHome();
    errorMessage += checkPhoneCell();
    errorMessage += checkPhoneWork();
    errorMessage += checkCandidateLinks();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkEditForm(form)
{
    var errorMessage = '';

    errorMessage += checkFirstName();
    errorMessage += checkLastName();
    errorMessage += checkCurrentPay();
    errorMessage += checkDesiredPay();
    errorMessage += checkBirthYear();
    errorMessage += checkPhoneHome();
    errorMessage += checkPhoneCell();
    errorMessage += checkPhoneWork();
    errorMessage += checkCandidateLinks();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function highlightMissingAddCandidateRequiredFields()
{
    checkFirstName();
    checkLastName();
}

function attachAddCandidateRequiredFieldValidation()
{
    var firstName = document.getElementById('firstName');
    var lastName = document.getElementById('lastName');

    if (firstName)
    {
        firstName.onkeyup = checkFirstName;
        firstName.onchange = checkFirstName;
    }

    if (lastName)
    {
        lastName.onkeyup = checkLastName;
        lastName.onchange = checkLastName;
    }
}

function checkCreateAttachmentForm(form)
{
    var errorMessage = '';

    errorMessage += checkAttachmentFile();
    errorMessage += checkAttachmentSuggestedFilename();
    errorMessage += checkAttachmentManualFilename();
    errorMessage += checkAttachmentCompanyRequirement();
    errorMessage += checkAttachmentFilenameLength();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkSearchByFullNameForm(form)
{
    var errorMessage = '';

    errorMessage += checkSearchFullName();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkSearchPhoneNumberForm(form)
{
    var errorMessage = '';

    errorMessage += checkPhoneNumber();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkSearchByKeySkillsForm(form)
{
    var errorMessage = '';

    errorMessage += checkSearchKeySkills();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkSearchResumeForm(form)
{
    var errorMessage = '';

    errorMessage += checkSearchResume();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkEmailForm(form)
{
    var errorMessage = '';

    errorMessage += checkEmailSubject();
    errorMessage += checkEmailBody();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkFirstName()
{
    var errorMessage = '';

    fieldValue = document.getElementById('firstName').value;
    fieldLabel = document.getElementById('firstNameLabel');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a first name.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkCandidateLinks()
{
    var errorMessage = '';
    var linkFields = [
        ['webSite', 'webSiteLabel', 'Web Site'],
        ['facebook', 'facebookLabel', 'Facebook'],
        ['linkedin', 'linkedinLabel', 'Linkedin'],
        ['github', 'githubLabel', 'Github'],
        ['googleplus', 'googleplusLabel', 'GooglePlus'],
        ['twitter', 'twitterLabel', 'Twitter'],
        ['cakeresume', 'cakeresumeLabel', 'Cakeresume'],
        ['link1', 'link1Label', 'Link1'],
        ['link2', 'link2Label', 'Link2'],
        ['link3', 'link3Label', 'Link3']
    ];

    for (var i = 0; i < linkFields.length; i++)
    {
        var field = document.getElementById(linkFields[i][0]);
        var label = document.getElementById(linkFields[i][1]);
        if (!field)
        {
            continue;
        }

        var value = field.value.replace(/^\s+|\s+$/g, '');
        if (value != '' && !/^https?:\/\//i.test(value))
        {
            errorMessage += '    - ' + linkFields[i][2] + ' must start with http:// or https://.\n';
            if (label)
            {
                label.style.color = '#ff0000';
            }
        }
        else if (label)
        {
            label.style.color = '#000';
        }
    }

    return errorMessage;
}

function checkLastName()
{
    var errorMessage = '';

    fieldValue = document.getElementById('lastName').value;
    fieldLabel = document.getElementById('lastNameLabel');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a last name.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkCurrentPay()
{
    var errorMessage = '';

    fieldValue = document.getElementById('currentPay').value;
    fieldLabel = document.getElementById('currentPayLabel');
    if (fieldValue != '' && isNaN(fieldValue))
    {
        errorMessage = "    - Current Pay is not a number. (Number Only)\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkDesiredPay()
{
    var errorMessage = '';

    fieldValue = document.getElementById('desiredPay').value;
    fieldLabel = document.getElementById('desiredPayLabel');
    if (fieldValue != '' && isNaN(fieldValue))
    {
        errorMessage = "    - Desired Pay is not a number. (Number Only)\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkBirthYear()
{
    var errorMessage = '';

    fieldValue = document.getElementById('birthYear').value;
    fieldLabel = document.getElementById('birthYearLabel');
    
    if (fieldValue != '' && (isNaN(fieldValue) || fieldValue.length != 4))
    {
        errorMessage = "    - Birth Year is not a 4 digit number.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkPhoneHome()
{
    var errorMessage = '';

    fieldValue = document.getElementById('phoneHome').value;
    fieldLabel = document.getElementById('phoneHomeLabel');
    if ((fieldValue != '') && new RegExp("[^0-9()#]").test(fieldValue))
    {
        errorMessage = "    - Phone Home Format (02)12345678#123, (886)12345678, 0912345678 \n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}
function checkPhoneCell()
{
    var errorMessage = '';

    fieldValue = document.getElementById('phoneCell').value;
    fieldLabel = document.getElementById('phoneCellLabel');
    if ((fieldValue != '') && new RegExp("[^0-9()#]").test(fieldValue))
    {
        errorMessage = "    - Phone Cell Format (02)12345678#123, (886)12345678, 0912345678 \n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}
function checkPhoneWork()
{
    var errorMessage = '';

    fieldValue = document.getElementById('phoneWork').value;
    fieldLabel = document.getElementById('phoneWorkLabel');
    if ((fieldValue != '') && new RegExp("[^0-9()#]").test(fieldValue))
    {
        errorMessage = "    - Phone Work Format (02)12345678#123, (886)12345678, 0912345678 \n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkSearchFullName()
{
    var errorMessage = '';

    fieldValue = document.getElementById('wildCardString_fullName').value;
    fieldLabel = document.getElementById('wildCardStringLabel_fullName');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter some search text.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkSearchKeySkills()
{
    var errorMessage = '';

    fieldValue = document.getElementById('wildCardString_keySkills').value;
    fieldLabel = document.getElementById('wildCardStringLabel_keySkills');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter some search text.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkSearchResume()
{
    var errorMessage = '';

    fieldValue = document.getElementById('wildCardString_resume').value;
    fieldLabel = document.getElementById('wildCardStringLabel_resume');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter some search text.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkAttachmentFile()
{
    var errorMessage = '';

    fieldValue = document.getElementById('file').value;
    fieldLabel = document.getElementById('file');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a file to upload.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkAttachmentSuggestedFilename()
{
    var errorMessage = '';
    var field = document.getElementById('suggestedFilename');
    var originalMode = document.getElementById('filenameModeOriginal');

    if (!field)
    {
        return errorMessage;
    }

    if ((originalMode && originalMode.checked) ||
        (document.getElementById('filenameModeManual') && document.getElementById('filenameModeManual').checked))
    {
        return errorMessage;
    }

    if (field.value == '')
    {
        errorMessage = "    - You must enter a suggested filename.\n";
    }

    return errorMessage;
}

function checkAttachmentManualFilename()
{
    var errorMessage = '';
    var manualMode = document.getElementById('filenameModeManual');
    var field = document.getElementById('manualFilename');

    if (!manualMode || !manualMode.checked || !field)
    {
        return errorMessage;
    }

    if (field.value == '')
    {
        errorMessage = "    - You must enter a manual filename.\n";
    }

    return errorMessage;
}

function checkAttachmentCompanyRequirement()
{
    var errorMessage = '';
    var fileTypeField = document.getElementById('fileType');
    var companyField = document.getElementById('attachmentCompany');
    var companyLabel = document.getElementById('attachmentCompanyLabel');

    if (!fileTypeField || !companyField || typeof attachmentNamingRules == 'undefined')
    {
        return errorMessage;
    }

    var rule = attachmentNamingRules[fileTypeField.value];
    if (rule && rule.companyRequired && companyField.value.replace(/\s+/g, '') == '')
    {
        errorMessage = "    - You must enter a company for this file type.\n";
        if (companyLabel)
        {
            companyLabel.style.color = '#ff0000';
        }
    }
    else if (companyLabel)
    {
        companyLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkAttachmentFilenameLength()
{
    var errorMessage = '';
    var field = document.getElementById('finalFilename');

    if (!field)
    {
        return errorMessage;
    }

    if (field.value.length > 255)
    {
        errorMessage = "    - Final filename must be 255 characters or fewer.\n";
    }

    return errorMessage;
}

function checkPhoneNumber()
{
    var errorMessage = '';

    fieldValue = document.getElementById('wildCardString_phoneNumber').value;
    fieldLabel = document.getElementById('wildCardStringLabel_phoneNumber');

    if (fieldValue == '')
    {
        errorMessage = "    - You must enter numbers to search.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkEmailSubject()
{
    var errorMessage = '';

    fieldValue = document.getElementById('emailSubject').value;
    fieldLabel = document.getElementById('emailSubjectLabel');

    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a subject for your e-mail.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkEmailBody()
{
    var errorMessage = '';

    fieldValue = '';
    emailBodyParentElement = document.getElementById('emailBody').parentElement;
    mceEditorIframe = getElementsByAttribute(emailBodyParentElement, 'class', "mceEditorIframe");
    if (mceEditorIframe.length > 0) {
        innerDoc = (mceEditorIframe[0].contentDocument) ? mceEditorIframe[0].contentDocument : mceEditorIframe[0].contentWindow.document;
        if(innerDoc) {
            bodyElements = innerDoc.getElementsByClassName('mceContentBody');
            if (bodyElements.length > 0) {
                fieldValue = bodyElements[0].innerHTML;
            }
        }
    }
    fieldLabel = document.getElementById('emailBodyLabel');

    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a body for your e-mail.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

var getElementsByAttribute = function (el, attr, value) {
    var match = [];

    /* Get the droids we're looking for*/
    var elements = el.getElementsByTagName("*");

    /* Loop through all elements */
    for (var ii = 0, ln = elements.length; ii < ln; ii++) {

        if (elements[ii].hasAttribute(attr)) {

            /* If a value was passed, make sure it matches the element's */
            if (value) {

                if (elements[ii].getAttribute(attr) === value) {
                    match.push(elements[ii]);
                }
            } else {
                /* Else, simply push it */
                match.push(elements[ii]);
            }
        }
    }
    return match;
};


function loadMailTemplate(no)
{
    document.getElementById('emailSubject').value = document.getElementById('greetingMessageTitle' + no).innerHTML;
    
    fieldValue = '';
    emailBodyParentElement = document.getElementById('emailBody').parentElement;
    mceEditorIframe = getElementsByAttribute(emailBodyParentElement, 'class', "mceEditorIframe");
    if (mceEditorIframe.length > 0) {
        innerDoc = (mceEditorIframe[0].contentDocument) ? mceEditorIframe[0].contentDocument : mceEditorIframe[0].contentWindow.document;
        if(innerDoc) {
            bodyElements = innerDoc.getElementsByClassName('mceContentBody');
            fieldValue = bodyElements[0].innerHTML = document.getElementById('greetingMessageBody' + no).innerHTML;
        }
    }
    fieldLabel = document.getElementById('emailBodyLabel');
}
