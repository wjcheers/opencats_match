/*
 * CATS
 * Candidates Form Validation
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * $Id: validator.js 1890 2007-02-20 05:29:38Z will $
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
    errorMessage += checkCompany();
    errorMessage += checkTitle();

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
    errorMessage += checkCompany();
    errorMessage += checkTitle();

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

function checkSearchByCompanyNameForm(form)
{
    var errorMessage = '';

    errorMessage += checkSearchCompanyName();

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

function checkCompany()
{
    var errorMessage = '';

    fieldValue = parseInt(document.getElementById('companyID').value);
    fieldLabel = document.getElementById('companyIDLabel');

    if (isNaN(fieldValue) || fieldValue <= 0)
    {
        errorMessage = "    - You must select a company.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkTitle()
{
    var errorMessage = '';

    fieldValue = document.getElementById('title').value;
    fieldLabel = document.getElementById('titleLabel');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a title.\n";

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

function checkSearchCompanyName()
{
    var errorMessage = '';

    fieldValue = document.getElementById('wildCardString_companyName').value;
    fieldLabel = document.getElementById('wildCardStringLabel_companyName');
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

