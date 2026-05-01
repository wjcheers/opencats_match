/*
 * CATS
 * Candidate JavaScript Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * $Id: candidate.js 3078 2007-09-21 20:25:28Z will $
 */

var candidateIsAlreadyInSystem = false;
var candidateIsAlreadyInSystemID = -1;
var candidateIsAlreadyInSystemName = '';
var candidateAlreadyInSystemMatches = {};

function normalizeDuplicateCandidateLookupValue(value)
{
    return (value || '').replace(/^\s+|\s+$/g, '').toLowerCase();
}

function makeDuplicateCandidateSourceKey(type, value, sourceKey)
{
    sourceKey = sourceKey || normalizeDuplicateCandidateLookupValue(value);
    return type + ':' + sourceKey;
}

function updateCandidateAlreadyInSystemDisplay()
{
    var selectedMatch = null;
    for (var key in candidateAlreadyInSystemMatches)
    {
        if (candidateAlreadyInSystemMatches.hasOwnProperty(key))
        {
            selectedMatch = candidateAlreadyInSystemMatches[key];
            break;
        }
    }

    candidateIsAlreadyInSystem = !!selectedMatch;
    candidateIsAlreadyInSystemID = selectedMatch ? selectedMatch.id : -1;
    candidateIsAlreadyInSystemName = selectedMatch ? selectedMatch.name : '';

    var linkOuter = document.getElementsByClassName("candidateAlreadyInSystemTable");
    for (var i = 0; i < linkOuter.length; i++)
    {
        linkOuter[i].style.display = selectedMatch ? '' : 'none';
    }

    if (!selectedMatch)
    {
        return;
    }

    linkOuter = document.getElementsByClassName("candidateAlreadyInSystemName");
    for (i = 0; i < linkOuter.length; i++)
    {
        linkOuter[i].innerHTML = candidateIsAlreadyInSystemName;
    }
}

function setCandidateAlreadyInSystemMatch(sourceKey, id, name, currentID)
{
    if (!sourceKey)
    {
        return;
    }

    if (id != -1 && !((currentID != '') && (currentID == id)))
    {
        candidateAlreadyInSystemMatches[sourceKey] = {
            id: id,
            name: name || ''
        };
    }
    else
    {
        delete candidateAlreadyInSystemMatches[sourceKey];
    }

    updateCandidateAlreadyInSystemDisplay();
}

function getDuplicateCandidateFieldValue(fieldName)
{
    var element = null;
    if (fieldName == 'source')
    {
        element = document.getElementById('sourceSelect');
    }
    if (!element)
    {
        element = document.getElementById(fieldName);
    }
    if (!element)
    {
        var named = document.getElementsByName(fieldName);
        if (named && named.length > 0)
        {
            element = named[0];
        }
    }
    if (!element)
    {
        return '';
    }
    if (element.type == 'checkbox')
    {
        return element.checked ? '1' : '';
    }
    return element.value || '';
}

function addDuplicateCandidateField(fields, fieldName, value)
{
    value = value || '';
    value = value.replace(/^\s+|\s+$/g, '');
    if (value != '')
    {
        fields[fieldName] = value;
    }
}

function hasDuplicateCandidateAIParseResult()
{
    var logID = document.getElementById('aiParseLogID');
    if (logID && logID.value != '')
    {
        return true;
    }
    return !!document.getElementById('aiSuggestionTable');
}

function collectDuplicateCandidateAISuggestionFields()
{
    var fields = {};
    var table = document.getElementById('aiSuggestionTable');
    if (!table)
    {
        return fields;
    }

    var rows = table.querySelectorAll ? table.querySelectorAll('tr.aiSuggestionRow') : [];
    for (var i = 0; i < rows.length; i++)
    {
        var field = rows[i].querySelector('.aiSuggestionFieldName');
        var target = rows[i].querySelector('.aiSuggestionTarget');
        var value = rows[i].querySelector('.aiSuggestionValue');
        var fieldName = field ? field.value : (target ? target.value : '');
        if (fieldName && value)
        {
            addDuplicateCandidateField(fields, fieldName, value.value);
        }
    }

    return fields;
}

function collectDuplicateCandidateSummaryFields(fields)
{
    var labels = document.querySelectorAll ? document.querySelectorAll('[id^="extraFieldLbl"]') : [];
    for (var i = 0; i < labels.length; i++)
    {
        var label = (labels[i].innerText || labels[i].textContent || '').replace(/:\s*$/, '').replace(/^\s+|\s+$/g, '');
        var fieldName = '';
        if (label == 'Career Summary')
        {
            fieldName = 'aiCareerSummary';
        }
        else if (label == 'Skill Summary')
        {
            fieldName = 'aiSkillSummary';
        }
        if (fieldName == '')
        {
            continue;
        }

        var index = labels[i].id.replace('extraFieldLbl', '');
        var element = document.getElementById('extraField' + index);
        if (!element)
        {
            var cell = document.getElementById('extraFieldData' + index);
            element = cell && cell.querySelector ? cell.querySelector('textarea,input,select') : null;
        }
        if (element)
        {
            addDuplicateCandidateField(fields, fieldName, element.value || '');
        }
    }
}

function collectDuplicateCandidateParsedFields()
{
    var fields = collectDuplicateCandidateAISuggestionFields();
    var hasFields = false;
    for (var existingField in fields)
    {
        if (fields.hasOwnProperty(existingField))
        {
            hasFields = true;
            break;
        }
    }
    if (hasFields)
    {
        return fields;
    }
    if (!hasDuplicateCandidateAIParseResult())
    {
        return {};
    }

    var fieldNames = [
        'firstName', 'middleName', 'lastName', 'chineseName', 'nationality',
        'email1', 'email2', 'phoneHome', 'phoneCell', 'phoneWork',
        'webSite', 'facebook', 'linkedin', 'github', 'googleplus',
        'twitter', 'cakeresume', 'link1', 'link2', 'link3',
        'address', 'city', 'state', 'zip', 'source', 'currentEmployer',
        'jobTitle', 'currentPay', 'desiredPay', 'keySkills', 'extraGender',
        'maritalStatus', 'birthYear', 'highestDegree', 'major', 'line',
        'qq', 'skype', 'wechat', 'functions', 'jobLevel', 'notes'
    ];
    for (var i = 0; i < fieldNames.length; i++)
    {
        addDuplicateCandidateField(fields, fieldNames[i], getDuplicateCandidateFieldValue(fieldNames[i]));
    }
    collectDuplicateCandidateSummaryFields(fields);

    return fields;
}

function addDuplicateCandidatePostParam(params, name, value)
{
    if (value === undefined || value === null || value === '')
    {
        return;
    }
    params.push(encodeURIComponent(name) + '=' + encodeURIComponent(value));
}

function getDuplicateCandidateOriginalSourceDetails()
{
    var sourceURL = getDuplicateCandidateFieldValue('extensionSourceURL');
    var pageTitle = getDuplicateCandidateFieldValue('extensionSourcePageTitle');

    if (sourceURL == '' || pageTitle == '')
    {
        var parsedNotesSource = parseDuplicateCandidateSourceFromNotes(
            getDuplicateCandidateFieldValue('notes')
        );
        sourceURL = sourceURL || parsedNotesSource.sourceURL;
        pageTitle = pageTitle || parsedNotesSource.pageTitle;
    }

    if (sourceURL != '' && isDuplicateCandidateInternalCATSPage(sourceURL))
    {
        sourceURL = '';
        pageTitle = '';
    }

    if (sourceURL == '' && !isDuplicateCandidateInternalCATSPage(window.location.href))
    {
        sourceURL = window.location.href;
        pageTitle = document.title || '';
    }

    return {
        sourceURL: sourceURL || '',
        pageTitle: pageTitle || ''
    };
}

function parseDuplicateCandidateSourceFromNotes(notes)
{
    var sourceDetails = {
        sourceURL: '',
        pageTitle: ''
    };
    notes = notes || '';

    var sourceMatch = notes.match(/(?:^|\n)Imported from:\s*(.+?)(?:\r?\n|$)/i);
    if (sourceMatch && sourceMatch[1])
    {
        sourceDetails.sourceURL = sourceMatch[1].replace(/^\s+|\s+$/g, '');
    }

    var titleMatch = notes.match(/(?:^|\n)Source page title:\s*(.+?)(?:\r?\n|$)/i);
    if (titleMatch && titleMatch[1])
    {
        sourceDetails.pageTitle = titleMatch[1].replace(/^\s+|\s+$/g, '');
    }

    return sourceDetails;
}

function isDuplicateCandidateInternalCATSPage(url)
{
    url = (url || '').replace(/&amp;/gi, '&');
    return /[?&]m=candidates(?:[&#]|$)/i.test(url) &&
        /[?&]a=(add|edit|importFromExtension)(?:[&#]|$)/i.test(url);
}

function openCandidateAlreadyInSystemWithPaste(indexName)
{
    if (candidateIsAlreadyInSystemID == -1)
    {
        return false;
    }

    var candidateURL = indexName + '?m=candidates&a=show&candidateID=' + encodeURIComponent(candidateIsAlreadyInSystemID);
    var documentTextField = document.getElementById('documentText');
    var documentText = documentTextField ? documentTextField.value : '';
    if (documentText.replace(/\s+/g, '') == '')
    {
        window.open(candidateURL);
        return false;
    }

    var targetWindow = window.open('', '_blank');
    if (targetWindow)
    {
        targetWindow.document.write('<html><body style="font-family:Arial,sans-serif;font-size:13px;">Opening duplicate candidate with pasted resume...</body></html>');
    }

    var http = AJAX_getXMLHttpObject();
    http.onreadystatechange = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        var redirectURL = candidateURL;
        if (http.status >= 200 && http.status < 300)
        {
            try
            {
                var response = JSON.parse(http.responseText);
                if (response && response.success && response.redirectURL)
                {
                    redirectURL = response.redirectURL;
                }
            }
            catch (e)
            {
            }
        }

        if (targetWindow)
        {
            targetWindow.location = redirectURL;
        }
        else
        {
            window.open(redirectURL);
        }
    };

    var params = [];
    addDuplicateCandidatePostParam(params, 'postback', 'postback');
    addDuplicateCandidatePostParam(params, 'candidateID', candidateIsAlreadyInSystemID);
    addDuplicateCandidatePostParam(params, 'documentText', documentText);
    addDuplicateCandidatePostParam(params, 'sourceType', 'cats');
    var originalSource = getDuplicateCandidateOriginalSourceDetails();
    addDuplicateCandidatePostParam(params, 'sourceURL', originalSource.sourceURL);
    addDuplicateCandidatePostParam(params, 'pageTitle', originalSource.pageTitle);

    var parsedFields = collectDuplicateCandidateParsedFields();
    if (window.JSON && JSON.stringify)
    {
        var parsedFieldCount = 0;
        for (var parsedField in parsedFields)
        {
            if (parsedFields.hasOwnProperty(parsedField))
            {
                parsedFieldCount++;
            }
        }
        if (parsedFieldCount > 0)
        {
            addDuplicateCandidatePostParam(params, 'aiSuggestedFields', JSON.stringify(parsedFields));
        }
    }

    var metaFields = [
        'aiParseLogID',
        'aiDocumentLanguage',
        'aiResumeExtension',
        'aiJechoReportRequested',
        'aiJechoReportMarkdown'
    ];
    for (var i = 0; i < metaFields.length; i++)
    {
        addDuplicateCandidatePostParam(params, metaFields[i], getDuplicateCandidateFieldValue(metaFields[i]));
    }

    http.open('POST', indexName + '?m=candidates&a=importFromExtension', true);
    http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    http.send(params.join('&'));

    return false;
}

function checkEmailAlreadyInSystem(email, currentID, sessionCookie, sourceKey)
{
    if (email == '')
    {
        return;
    }

    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '&email=' + urlEncode(email);

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        if (!http.responseXML)
        {
            var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                             + http.responseText;
            /* alert(errorMessage); */
            return;
        }

        var idNode = http.responseXML.getElementsByTagName('id').item(0);

        var candidateID = idNode.firstChild.nodeValue;
        var nameNode = http.responseXML.getElementsByTagName('name').item(0);
        var candidateName = (nameNode && nameNode.firstChild) ? nameNode.firstChild.nodeValue : '';
        setCandidateAlreadyInSystemMatch(
            makeDuplicateCandidateSourceKey('email', email, sourceKey),
            candidateID,
            candidateName,
            currentID
        );
    }

    AJAX_callCATSFunction(
        http,
        'getCandidateIdByEmail',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}

function onSubmitEmailInSystem()
{
    if (candidateIsAlreadyInSystem)
    {
        var agree=confirm("Warning:  The candidate may already be in the system.\n\nAre you sure you want to add the candidate?");
        if (agree)
        {
            candidateIsAlreadyInSystem = false;
        	return true ;
        }
        else
        	return false ;
    }
    return true;
}


function checkPhoneAlreadyInSystem(phone, currentID, sessionCookie, sourceKey)
{
    if (phone == '')
    {
        return;
    }

    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '&phone=' + urlEncode(phone);

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        if (!http.responseXML)
        {
            var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                             + http.responseText;
            /* alert(errorMessage); */
            return;
        }

        var idNode = http.responseXML.getElementsByTagName('id').item(0);

        var candidateID = idNode.firstChild.nodeValue;
        var nameNode = http.responseXML.getElementsByTagName('name').item(0);
        var candidateName = (nameNode && nameNode.firstChild) ? nameNode.firstChild.nodeValue : '';
        setCandidateAlreadyInSystemMatch(
            makeDuplicateCandidateSourceKey('phone', phone, sourceKey),
            candidateID,
            candidateName,
            currentID
        );
    }

    AJAX_callCATSFunction(
        http,
        'getCandidateIdByPhone',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}

function onSubmitPhoneInSystem()
{
    if (candidateIsAlreadyInSystem)
    {
        var agree=confirm("Warning:  The candidate may already be in the system.\n\nAre you sure you want to add the candidate?");
        if (agree)
        {
            candidateIsAlreadyInSystem = false;
        	return true ;
        }
        else
        	return false ;
    }
    return true;
}


function checkLinkAlreadyInSystem(link, currentID, sessionCookie, sourceKey)
{
    if (link == '')
    {
        return;
    }

    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '&link=' + urlEncode(link);

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        if (!http.responseXML)
        {
            var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                             + http.responseText;
            /* alert(errorMessage); */
            return;
        }

        var idNode = http.responseXML.getElementsByTagName('id').item(0);

        var candidateID = idNode.firstChild.nodeValue;
        var nameNode = http.responseXML.getElementsByTagName('name').item(0);
        var candidateName = (nameNode && nameNode.firstChild) ? nameNode.firstChild.nodeValue : '';
        setCandidateAlreadyInSystemMatch(
            makeDuplicateCandidateSourceKey('link', link, sourceKey),
            candidateID,
            candidateName,
            currentID
        );
    }

    AJAX_callCATSFunction(
        http,
        'getCandidateIdByLink',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}

function onSubmitLinkInSystem()
{
    if (candidateIsAlreadyInSystem)
    {
        var agree=confirm("Warning:  The candidate may already be in the system.\n\nAre you sure you want to add the candidate?");
        if (agree)
        {
            candidateIsAlreadyInSystem = false;
        	return true ;
        }
        else
        	return false ;
    }
    return true;
}


function checkSocialMediaAlreadyInSystem(type, social, sessionCookie, sourceKey)
{
    if (social == '')
    {
        return;
    }

    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '&' + type + '=' + urlEncode(social);
    // format: &wechat=xxxx, &skype=xxxx, &line=xxxx, &qq=xxxx

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        if (!http.responseXML)
        {
            var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                             + http.responseText;
            /* alert(errorMessage); */
            return;
        }

        var idNode = http.responseXML.getElementsByTagName('id').item(0);

        var candidateID = idNode.firstChild.nodeValue;
        var nameNode = http.responseXML.getElementsByTagName('name').item(0);
        var candidateName = (nameNode && nameNode.firstChild) ? nameNode.firstChild.nodeValue : '';
        setCandidateAlreadyInSystemMatch(
            makeDuplicateCandidateSourceKey('social-' + type, social, sourceKey),
            candidateID,
            candidateName,
            ''
        );
    }

    AJAX_callCATSFunction(
        http,
        'getCandidateIdBySocialMedia',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}

function onSubmitSocialMediaInSystem()
{
    if (candidateIsAlreadyInSystem)
    {
        var agree=confirm("Warning:  The candidate may already be in the system.\n\nAre you sure you want to add the candidate?");
        if (agree)
        {
            candidateIsAlreadyInSystem = false;
        	return true ;
        }
        else
        	return false ;
    }
    return true ;
}
