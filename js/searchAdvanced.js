/*
 * CATS
 * Search Advanced JavaScript Library
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
 * $Id: searchAdvanced.js 2372 2007-04-24 21:57:11Z will $
 */

var modes;
var data;

function getAdvancedSearchOpenCount()
{
    var openCount = 0;

    for (var i = 0; i < nodes.length; i++)
    {
        if (nodes[i] == " AND (" || nodes[i] == " OR (" ||
            nodes[i] == " AND NOT (")
        {
            openCount++;
        }
        else if (nodes[i] == ")" && openCount > 0)
        {
            openCount--;
        }
    }

    return openCount;
}

function synchronizeAdvancedSearchParentheses()
{
    var normalizedNodes = [];
    var openCount = 0;

    for (var i = 0; i < nodes.length; i++)
    {
        if (typeof nodes[i] == 'undefined')
        {
            continue;
        }

        if (nodes[i] === "")
        {
            continue;
        }

        if (nodes[i] == " AND (" || nodes[i] == " OR (" ||
            nodes[i] == " AND NOT (")
        {
            normalizedNodes.push(nodes[i]);
            openCount++;
        }
        else if (nodes[i] == ")")
        {
            if (openCount > 0)
            {
                normalizedNodes.push(nodes[i]);
                openCount--;
            }
        }
        else
        {
            normalizedNodes.push(nodes[i]);
        }
    }

    if (normalizedNodes.length === 0 ||
        normalizedNodes[normalizedNodes.length - 1] !== "")
    {
        normalizedNodes.push("");
    }

    nodes = normalizedNodes;
}

function getAdvancedSearchOpenCountBefore(nodeNum)
{
    var openCount = 0;

    for (var i = 0; i < nodeNum; i++)
    {
        if (nodes[i] == " AND (" || nodes[i] == " OR (" ||
            nodes[i] == " AND NOT (")
        {
            openCount++;
        }
        else if (nodes[i] == ")" && openCount > 0)
        {
            openCount--;
        }
    }

    return openCount;
}

function getAdvancedSearchDisplayDepth(nodeNum)
{
    var depth = getAdvancedSearchOpenCountBefore(nodeNum);

    if (typeof nodes[nodeNum] != 'undefined' && nodes[nodeNum] == ")" &&
        depth > 0)
    {
        depth--;
    }

    return depth;
}

function getAdvancedSearchEndGroupLabel(nodeNum)
{
    var stack = [];
    var label = 'End Group';
    var level = getAdvancedSearchOpenCountBefore(nodeNum);

    for (var i = 0; i < nodeNum; i++)
    {
        if (nodes[i] == " AND (")
        {
            stack.push('AND');
        }
        else if (nodes[i] == " OR (")
        {
            stack.push('OR');
        }
        else if (nodes[i] == " AND NOT (")
        {
            stack.push('NOT');
        }
        else if (nodes[i] == ")" && stack.length > 0)
        {
            stack.pop();
        }
    }

    if (stack.length > 0)
    {
        label = 'End ' + stack[stack.length - 1] + ' Group';
    }

    if (level > 0)
    {
        label += ' (Level ' + level + ')';
    }

    return label;
}

function getAdvancedSearchStartGroupLabel(nodeNum, groupName)
{
    var level = getAdvancedSearchOpenCountBefore(nodeNum) + 1;

    return 'Start ' + groupName + ' Group (Level ' + level + ')';
}

function advancedSearchReset()
{
    nodes = [];
    data = [];
    data[0] = document.getElementById('searchText').value;
    nodes[0] = '';
    if (document.getElementById('advancedSearchParser'))
    {
        document.getElementById('advancedSearchParser').value = '';
    }
    if (document.getElementById('advancedSearchOn'))
    {
        document.getElementById('advancedSearchOn').value = 0;
    }
    advancedSearchDraw();
}

function advancedSearchDisable()
{
    if (document.getElementById('advancedSearchField'))
    {
        document.getElementById('advancedSearchField').style.display = 'none';
    }
    if (document.getElementById('advancedSearchParser'))
    {
        document.getElementById('advancedSearchParser').value = '';
    }
    if (document.getElementById('advancedSearchOn'))
    {
        document.getElementById('advancedSearchOn').value = 0;
    }
}

function advancedSearchSet()
{
    var text = '';
    var textp = '';
    var autoCloseCount = 0;

    synchronizeAdvancedSearchParentheses();

    for (var i = 0; i < nodes.length; i++)
    {
        if (typeof data[i] != 'undefined')
        {
            text += data[i] + nodes[i];
            if (i != 0)
            {
                textp += '{[+';
            }
            textp += data[i] + '[|]' + nodes[i];
        }
        else if (typeof nodes[i] != 'undefined')
        {
            text += nodes[i];
            textp += '[|]' + nodes[i];
        }
    }

    autoCloseCount = getAdvancedSearchOpenCount();
    while (autoCloseCount > 0)
    {
        text += ')';
        textp += '{[+[|])';
        autoCloseCount--;
    }

    document.getElementById('searchText').value = text;
    document.getElementById('advancedSearchParser').value = textp;
}

function advancedSearchDraw()
{
    var html = '<br />Advanced search:<br />';

    if (nodes.length > 2)
    {
        html += '<div><select id="nothing" style="width:200px;"><option>--------</option></select></div>';
    }

    html += '<div style="margin-top:4px; margin-left:0px;">';
    html += '<input type="text" id="searchValue' + 0 + '" value="' + data[0] + '" onkeyup="data[0] = document.getElementById(\'searchValue\'+' + 0 + ').value; advancedSearchSet(); ">';
    html += '</div>';

    for (var i = 0; i < nodes.length; i++)
    {
        html += '<div style="margin-top:4px; margin-left:' +
            (getAdvancedSearchDisplayDepth(i) * 18) + 'px;">';

        html += '<select id="searchSelect' + i + '" onchange="setSearchNode(' + i + ');" style="width:200px;">';
        html += '<option value=""></option>';
        html += '<option value=" AND " '      + ((nodes[i] == " AND "     ) ? 'selected' : '') + '>AND</option>';
        html += '<option value=" OR " '       + ((nodes[i] == " OR "      ) ? 'selected' : '') + '>OR</option>';
        html += '<option value=" AND NOT " '  + ((nodes[i] == " AND NOT " ) ? 'selected' : '') + '>AND NOT</option>';
        html += '<option value="* " '         + ((nodes[i] == "* "        ) ? 'selected' : '') + '>Prefix Match</option>';
        html += '<option value=" AND (" '     + ((nodes[i] == " AND ("    ) ? 'selected' : '') + '>' + getAdvancedSearchStartGroupLabel(i, 'AND') + '</option>';
        html += '<option value=" OR (" '      + ((nodes[i] == " OR ("     ) ? 'selected' : '') + '>' + getAdvancedSearchStartGroupLabel(i, 'OR') + '</option>';
        html += '<option value=" AND NOT (" ' + ((nodes[i] == " AND NOT (") ? 'selected' : '') + '>' + getAdvancedSearchStartGroupLabel(i, 'NOT') + '</option>';

        if (nodes[i] == ")" || getAdvancedSearchOpenCountBefore(i) > 0)
        {
            html += '<option value=")" ' + (((nodes[i] == ")") ? 'selected' : '')) + '>' + getAdvancedSearchEndGroupLabel(i) + '</option>';
        }

        html += '</select>';
        if (nodes[i] == " AND " || nodes[i] == " OR " || nodes[i] == " AND NOT "
           || nodes[i] == " AND (" || nodes[i] == " OR (" || nodes[i] == " AND NOT (" )
        {
            html += '<input type="text" id="searchValue' + (i + 1) + '" value="' + ((typeof data[i + 1] != "undefined") ? data[i + 1] : "") + '" onkeyup="data['+(i+1)+'] = document.getElementById(\'searchValue\'+' + (i + 1) + ').value; advancedSearchSet();">'
        }
        else
        {
            html += '<input type="text" id="searchValue' + (i + 1) + '" style="display:none;" value="' + ((typeof data[i + 1] != "undefined") ? data[i + 1] : "") + '" onkeyup="data[' + (i + 1) + '] = document.getElementById(\'searchValue\'+' + (i + 1) + ').value; advancedSearchSet();">'
        }

        html += '</div>';
    }
    html += '<br /><br />';
    html += '<input type="button" class="button" id="searchAdvanced" name="searchAdvanced" value="Search" onclick="advancedSearchSet(); document.getElementById(\'advancedSearchOn\').value=' + data.length + '; document.getElementById(\'searchForm\').submit();" />&nbsp;';
    html += '<input type="button" class="button" name="simpleSearch" value="Simple" onclick="advancedSearchDisable();" />&nbsp;';
    html += '<input type="button" class="button" name="resetSearch" value="Reset" onclick="document.getElementById(\'searchText\').value = \'\'; advancedSearchReset();" />&nbsp;';
    document.getElementById('advancedSearchField').innerHTML = html;
}

function setSearchNode(nodeNum)
{
    var dropDownList = document.getElementById('searchSelect'+nodeNum);
    nodes[nodeNum] = dropDownList[dropDownList.selectedIndex].value;

    if (nodes[nodeNum] == " AND (" || nodes[nodeNum] == " OR (" ||
        nodes[nodeNum] == " AND NOT (")
    {
        data[nodeNum + 1] = (typeof data[nodeNum + 1] != "undefined")
            ? data[nodeNum + 1] : "";
        data[nodeNum + 2] = (typeof data[nodeNum + 2] != "undefined")
            ? data[nodeNum + 2] : "";
    }

    if (nodes[nodeNum] == ")")
    {
        data[nodeNum + 1] = (typeof data[nodeNum + 1] != "undefined")
            ? data[nodeNum + 1] : "";
        nodes[nodeNum + 1] = (typeof nodes[nodeNum + 1] != "undefined")
            ? nodes[nodeNum + 1] : "";
    }

    synchronizeAdvancedSearchParentheses();
    advancedSearchSet();
    advancedSearchDraw();

    if (nodes[nodeNum] != ")")
    {
        document.getElementById('searchValue'+(nodeNum+1)).focus();
    }
}

function advancedSearchConsider()
{
    if (typeof(advancedValidFields) == 'undefined') return;
    var dropDownList = document.getElementById('searchMode');
    var theField = dropDownList[dropDownList.selectedIndex].value;
    var goodField = false;
    for (var i = 0; i < advancedValidFields.length; i++)
    {
        if (theField == advancedValidFields[i])
        {
            goodField = true;
        }
    }
    if (goodField)
    {
        document.getElementById('advancedSearch').style.display = '';
    }
    else
    {
        document.getElementById('advancedSearch').style.display = 'none';
        document.getElementById('advancedSearchField').style.display = 'none';
    }
}
