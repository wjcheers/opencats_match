/*
 * CATS
 * Candidates Form Validation
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * $Id: activityvalidator.js 2336 2007-04-14 22:01:51Z will $
 */

function checkActivityForm(form)
{
    var errorMessage = '';

    errorMessage += checkEventTitle();
    errorMessage += checkValid();
    errorMessage += checkPersonalAgreement();
    errorMessage += checkCurrentPay();
    errorMessage += checkDesiredPay();
    errorMessage += checkValidEmail();

    if (errorMessage != '')
    {
        alert("Form Error:\n\n" + errorMessage);
        return false;
    }

    return true;
}

function checkEventTitle()
{
    var errorMessage = '';

    scheduleEvent = document.getElementById('scheduleEvent').checked;
    if (!scheduleEvent)
    {
        return '';
    }

    fieldValue = document.getElementById('title').value;
    fieldLabel = document.getElementById('titleLabel');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter an event title.\n\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkValid()
{
    var errorMessage = '';
    fieldLabel = document.getElementById('triggerValidSpan');

    if((document.getElementById('triggerValidSpan').style.display != 'none') &&
       (document.getElementById('triggerValid').checked == false))
    {
        errorMessage = "    - Did you confirm the Effective Recommendation of this candidate?\n\n";

        fieldLabel.style.color = '#ff0000';        
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkPersonalAgreement()
{
    var errorMessage = '';
    fieldLabel = document.getElementById('triggerPersonalAgreementSpan');
    
    
    var personalAgreement = fieldLabel.getAttribute('data-personal-agreement');
    var personalAgreementCount = fieldLabel.getAttribute('data-personal-agreement-count');
    personalAgreementCount ++;
    fieldLabel.setAttribute('data-personal-agreement-count', personalAgreementCount);

    if(personalAgreement == "0"
        && document.getElementById('changeStatus').checked == true
        && document.changePipelineStatusForm.statusID.options[document.changePipelineStatusForm.statusID.selectedIndex].text == "Qualifying")
    {
        if(personalAgreementCount >= 1)
        {
            document.getElementById('triggerPersonalAgreementSpan').style.display = 'inline';
        }
        if(document.getElementById('triggerPersonalAgreement').checked == false)
        {
            errorMessage  = "    - Did you upload the candidate's Personal Agreement?\n";
            errorMessage += "      File name: PersonalAgreement...\n\n";

            fieldLabel.style.color = '#ff0000';        
        }
        else
        {
            fieldLabel.style.color = '#000';
        }
    }

    return errorMessage;
}

function checkCurrentPay()
{
    var errorMessage = '';
    fieldLabel = document.getElementById('triggerCurrentPaySpan');
    
    
    var currentPay = fieldLabel.getAttribute('data-current-pay');
    var currentPayCount = fieldLabel.getAttribute('data-current-pay-count');
    currentPayCount ++;
    fieldLabel.setAttribute('data-current-pay-count', currentPayCount);

    if((!currentPay || currentPay == '')
        && document.getElementById('changeStatus').checked == true
        && document.changePipelineStatusForm.statusID.options[document.changePipelineStatusForm.statusID.selectedIndex].text == "Qualifying")
    {
        if(currentPayCount >= 3)
        {
            document.getElementById('triggerCurrentPaySpan').style.display = 'inline';
        }
        if(document.getElementById('triggerCurrentPay').checked == false)
        {
            errorMessage  = "    - Did you fill the candidate's Current Pay?\n\n";

            fieldLabel.style.color = '#ff0000';        
        }
        else
        {
            fieldLabel.style.color = '#000';
        }
    }

    return errorMessage;
}

function checkDesiredPay()
{
    var errorMessage = '';
    fieldLabel = document.getElementById('triggerDesiredPaySpan');
    
    
    var desiredPay = fieldLabel.getAttribute('data-desired-pay');
    var desiredPayCount = fieldLabel.getAttribute('data-desired-pay-count');
    desiredPayCount ++;
    fieldLabel.setAttribute('data-desired-pay-count', desiredPayCount);

    if((!desiredPay || desiredPay == '')
        && document.getElementById('changeStatus').checked == true
        && document.changePipelineStatusForm.statusID.options[document.changePipelineStatusForm.statusID.selectedIndex].text == "Qualifying")
    {
        if(desiredPayCount >= 3)
        {
            document.getElementById('triggerDesiredPaySpan').style.display = 'inline';
        }
        if(document.getElementById('triggerDesiredPay').checked == false)
        {
            errorMessage  = "    - Did you fill the candidate's Desired Pay?\n\n";

            fieldLabel.style.color = '#ff0000';        
        }
        else
        {
            fieldLabel.style.color = '#000';
        }
    }

    return errorMessage;
}

function checkValidEmail()
{
    var errorMessage = '';
    fieldLabel = document.getElementById('triggerValidEmailSpan');
    
    
    var email = fieldLabel.getAttribute('data-email');
    var emailCount = fieldLabel.getAttribute('data-email-count');
    emailCount ++;
    fieldLabel.setAttribute('data-email-count', emailCount);

    if((!email || email == '')
        && document.getElementById('changeStatus').checked == true
        && document.changePipelineStatusForm.statusID.options[document.changePipelineStatusForm.statusID.selectedIndex].text == "Qualifying")
    {
        if(emailCount >= 3)
        {
            document.getElementById('triggerValidEmailSpan').style.display = 'inline';
        }
        if(document.getElementById('triggerValidEmail').checked == false)
        {
            errorMessage  = "    - Did you fill the candidate's Email?\n\n";

            fieldLabel.style.color = '#ff0000';        
        }
        else
        {
            fieldLabel.style.color = '#000';
        }
    }

    return errorMessage;
}