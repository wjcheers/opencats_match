/*
 * CATS
 * Candidates Form Validation
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * $Id: validator.js 2646 2007-07-09 16:40:31Z Andrew $
 */

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

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkCreateAttachmentForm(form)
{
    var errorMessage = '';

    errorMessage += checkAttachmentFile();

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
