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