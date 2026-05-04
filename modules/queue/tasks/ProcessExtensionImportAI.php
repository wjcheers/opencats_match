<?php
/*
 * CATS
 * Asynchroneous Queue Processor
 *
 * Parses NBI ATS extension quick imports in the background and updates the
 * candidate record after the user already has a candidate ID.
 */

include_once('./constants.php');
include_once('./modules/queue/lib/Task.php');
include_once('./lib/AIResumeParser.php');
include_once('./lib/Candidates.php');
include_once('./lib/ActivityEntries.php');
include_once('./lib/History.php');
include_once('./lib/DatabaseSearch.php');

class ProcessExtensionImportAI extends Task
{
    public function run($siteID, $args)
    {
        $this->setName('Process Extension Import AI');
        $this->setDescription('Parse a quick NBI ATS extension import and update candidate fields.');

        $args = $this->decodeArgs($args);
        $candidateID = isset($args['candidateID']) ? (int) $args['candidateID'] : 0;
        $userID = isset($args['userID']) ? (int) $args['userID'] : 0;
        $attachmentID = isset($args['attachmentID']) ? (int) $args['attachmentID'] : 0;
        $activityID = isset($args['activityID']) ? (int) $args['activityID'] : 0;
        $fileName = isset($args['fileName']) ? trim((string) $args['fileName']) : '';
        $queuedAt = isset($args['queuedAt']) ? trim((string) $args['queuedAt']) : '';
        $created = !empty($args['created']);

        if ($candidateID <= 0)
        {
            $this->setResponse('Invalid extension import queue arguments.');
            return TASKRET_ERROR;
        }

        $candidates = new Candidates($siteID);
        $candidateData = $candidates->get($candidateID);
        if (empty($candidateData))
        {
            $this->setResponse('Candidate not found for extension import queue task.');
            return TASKRET_ERROR;
        }

        $attachment = $this->getAttachmentPayload($siteID, $candidateID, $attachmentID);
        $documentText = isset($attachment['documentText']) ? trim((string) $attachment['documentText']) : '';
        if ($fileName == '' && !empty($attachment['fileName']))
        {
            $fileName = $attachment['fileName'];
        }

        if ($documentText == '')
        {
            $this->setResponse('No extension import attachment text was available.');
            return TASKRET_ERROR;
        }

        $parser = new AIResumeParser();
        $languageCode = $parser->detectLanguageCode($documentText, $fileName);
        $result = $parser->parseResumeText($documentText, array(
            'sourceType' => 'extension_fast_queue',
            'fileName' => $fileName,
            'languageHint' => $languageCode,
            'parseMode' => 'fast',
            'includeJechoReport' => false,
            'targetLanguage' => ($languageCode == 'en') ? 'en' : 'zh'
        ));

        if ($result === false)
        {
            $message = $parser->getLastError();
            $this->recordCandidateImportActivity(
                $siteID,
                $candidateID,
                $userID,
                $activityID,
                'NBI ATS 快速匯入失敗。' . "\n" . '原因: ' . $message
            );
            $this->setResponse('AI parse failed: ' . $message);
            return TASKRET_FAILURE;
        }

        $parseLogID = $parser->createParseLog(
            $siteID,
            $userID,
            'extension_fast_queue',
            ($fileName != '') ? $fileName : ('Extension_Import_' . date('Ymd_His') . '.txt'),
            '',
            $languageCode,
            $result
        );

        $duplicates = $this->findDuplicateCandidates($siteID, $candidateID, $result, $args);
        if ($parseLogID > 0)
        {
            $parser->markSavedCandidate($parseLogID, $candidateID);
        }

        if (count($duplicates) > 0)
        {
            $this->addDuplicateNoticeActivity($siteID, $candidateID, $userID, $activityID, $duplicates, $parseLogID);
            $this->setResponse(
                'Extension import AI parse found possible duplicate candidate #' .
                $duplicates[0]['candidateID'] . '; no fields were updated automatically.'
            );
            return TASKRET_SUCCESS;
        }

        $applyResult = $this->applyAIResultToCandidate(
            $siteID,
            $candidateID,
            $result,
            $created,
            $queuedAt
        );
        $updatedFields = $applyResult['updated'];
        $skippedFields = $applyResult['skippedChanged'];

        $notes = array();
        $notes[] = 'NBI ATS 快速匯入完成。';
        if (count($updatedFields) > 0)
        {
            $notes[] = '已更新: ' . implode('、', $this->formatActivityFieldLabels($updatedFields));
        }
        else
        {
            $notes[] = '未更新既有欄位。';
        }
        if (count($skippedFields) > 0)
        {
            $notes[] = '已保留顧問手動修改: ' . implode('、', $this->formatActivityFieldLabels($skippedFields));
        }

        $this->recordCandidateImportActivity($siteID, $candidateID, $userID, $activityID, implode("\n", $notes));
        $this->setResponse('Extension import AI parse completed for candidate #' . $candidateID . '.');

        return TASKRET_SUCCESS;
    }

    private function decodeArgs($args)
    {
        if (is_array($args))
        {
            return $this->normalizeDecodedArgs($args);
        }

        if (is_string($args) && $args != '')
        {
            $decoded = @unserialize($args);
            if (is_array($decoded))
            {
                return $this->normalizeDecodedArgs($decoded);
            }
        }

        return array();
    }

    private function normalizeDecodedArgs($args)
    {
        if (!isset($args['sourceURL']) && isset($args['sourceURLBase64']))
        {
            $sourceURL = base64_decode((string) $args['sourceURLBase64'], true);
            $args['sourceURL'] = ($sourceURL === false) ? '' : $sourceURL;
        }

        return $args;
    }

    private function applyAIResultToCandidate($siteID, $candidateID, $result, $created, $queuedAt)
    {
        $candidateData = $this->getCandidateData($siteID, $candidateID);
        if (empty($candidateData))
        {
            return array('updated' => array(), 'skippedChanged' => array());
        }

        $candidate = isset($result['candidate']) && is_array($result['candidate'])
            ? $result['candidate']
            : array();
        $mapped = $this->mapAIResultToCandidateColumns($candidate);
        $overwrite = $created || $this->isQuickImportPlaceholder($candidateData);
        $modifiedAfterQueued = $this->isCandidateModifiedAfterQueued($siteID, $candidateID, $queuedAt);
        $updates = array();
        $updatedLabels = array();
        $skippedChangedLabels = array();

        foreach ($mapped as $columnName => $fieldData)
        {
            $value = isset($fieldData['value']) ? trim((string) $fieldData['value']) : '';
            if ($value == '')
            {
                continue;
            }

            $currentKey = isset($fieldData['currentKey']) ? $fieldData['currentKey'] : '';
            $currentValue = ($currentKey != '' && isset($candidateData[$currentKey]))
                ? $candidateData[$currentKey]
                : '';

            if ($this->shouldSkipFieldChangedAfterQueued(
                $candidateData,
                $modifiedAfterQueued,
                $currentKey,
                $currentValue
            ))
            {
                $skippedChangedLabels[] = isset($fieldData['label']) ? $fieldData['label'] : $columnName;
                continue;
            }

            if (!$overwrite && !$this->isEmptyValue($currentValue))
            {
                continue;
            }
            if (trim((string) $currentValue) === $value)
            {
                continue;
            }

            $updates[$columnName] = $value;
            $updatedLabels[] = isset($fieldData['label']) ? $fieldData['label'] : $columnName;
        }

        if (count($updates) == 0)
        {
            return array(
                'updated' => array(),
                'skippedChanged' => array_values(array_unique($skippedChangedLabels))
            );
        }

        $db = DatabaseConnection::getInstance();
        $assignments = array();
        foreach ($updates as $columnName => $value)
        {
            $assignments[] = $columnName . ' = ' . $db->makeQueryString($value);
        }
        $assignments[] = 'date_modified = NOW()';

        $candidates = new Candidates($siteID);
        $preHistory = $candidates->get($candidateID);

        $sql = sprintf(
            "UPDATE candidate
             SET %s
             WHERE candidate_id = %s
             AND site_id = %s",
            implode(",\n                 ", $assignments),
            $db->makeQueryInteger($candidateID),
            $db->makeQueryInteger($siteID)
        );
        $db->query($sql);

        $postHistory = $candidates->get($candidateID);
        $history = new History($siteID);
        $history->storeHistoryChanges(DATA_ITEM_CANDIDATE, $candidateID, $preHistory, $postHistory);

        return array(
            'updated' => $updatedLabels,
            'skippedChanged' => array_values(array_unique($skippedChangedLabels))
        );
    }

    private function getCandidateData($siteID, $candidateID)
    {
        $candidates = new Candidates($siteID);
        return $candidates->get((int) $candidateID);
    }

    private function getAttachmentPayload($siteID, $candidateID, $attachmentID)
    {
        if ((int) $attachmentID <= 0)
        {
            return array();
        }

        $db = DatabaseConnection::getInstance();
        $sql = sprintf(
            "SELECT
                original_filename AS originalFilename,
                text AS text
             FROM attachment
             WHERE attachment_id = %s
             AND data_item_id = %s
             AND data_item_type = %s
             AND site_id = %s",
            $db->makeQueryInteger($attachmentID),
            $db->makeQueryInteger($candidateID),
            DATA_ITEM_CANDIDATE,
            $db->makeQueryInteger($siteID)
        );
        $attachment = $db->getAssoc($sql);
        if (empty($attachment))
        {
            return array();
        }

        $databaseSearch = new DatabaseSearch();

        return array(
            'fileName' => isset($attachment['originalFilename']) ? $attachment['originalFilename'] : '',
            'documentText' => $databaseSearch->fulltextDecode(
                isset($attachment['text']) ? $attachment['text'] : ''
            )
        );
    }

    private function isCandidateModifiedAfterQueued($siteID, $candidateID, $queuedAt)
    {
        $queuedAt = trim((string) $queuedAt);
        if ($queuedAt == '')
        {
            return false;
        }

        $db = DatabaseConnection::getInstance();
        $sql = sprintf(
            "SELECT COUNT(*) AS count
             FROM candidate
             WHERE candidate_id = %s
             AND site_id = %s
             AND date_modified > %s",
            $db->makeQueryInteger($candidateID),
            $db->makeQueryInteger($siteID),
            $db->makeQueryString($queuedAt)
        );

        return ((int) $db->getColumn($sql, 0, 0)) > 0;
    }

    private function shouldSkipFieldChangedAfterQueued($candidateData, $modifiedAfterQueued, $fieldName, $currentValue)
    {
        if (!$modifiedAfterQueued || $this->isEmptyValue($currentValue))
        {
            return false;
        }
        if (($fieldName == 'firstName' || $fieldName == 'lastName') &&
            $this->isQuickImportPlaceholder($candidateData))
        {
            return false;
        }

        return true;
    }

    private function findDuplicateCandidates($siteID, $currentCandidateID, $result, $args)
    {
        $candidate = isset($result['candidate']) && is_array($result['candidate'])
            ? $result['candidate']
            : array();
        $checks = array();

        $this->addDuplicateCheck($checks, 'email', 'email', $this->getCandidateValue($candidate, 'email'));
        $this->addDuplicateCheck($checks, 'phone', 'phone', $this->getCandidateValue($candidate, 'phone'));
        if (!empty($args['sourceURL']))
        {
            $this->addDuplicateCheck($checks, 'link', 'sourceURL', $args['sourceURL']);
        }
        foreach (array('website', 'linkedin', 'github', 'facebook', 'googleplus', 'twitter', 'cakeresume', 'link1', 'link2', 'link3') as $fieldName)
        {
            $this->addDuplicateCheck($checks, 'link', $fieldName, $this->getCandidateValue($candidate, $fieldName));
        }

        $duplicates = array();
        foreach ($checks as $check)
        {
            if ($check['type'] == 'email')
            {
                $duplicateID = $this->findCandidateIDByEmail($siteID, $currentCandidateID, $check['value']);
            }
            else if ($check['type'] == 'phone')
            {
                $duplicateID = $this->findCandidateIDByPhone($siteID, $currentCandidateID, $check['value']);
            }
            else
            {
                $duplicateID = $this->findCandidateIDByLink($siteID, $currentCandidateID, $check['value']);
            }

            if ($duplicateID > 0 &&
                !isset($duplicates[$duplicateID]) &&
                !$this->shouldCurrentCandidateWinDuplicateRace($siteID, $currentCandidateID, $duplicateID))
            {
                $duplicates[$duplicateID] = array(
                    'candidateID' => $duplicateID,
                    'reason' => $check['label'],
                    'value' => $check['value']
                );
            }
        }

        return array_values($duplicates);
    }

    private function shouldCurrentCandidateWinDuplicateRace($siteID, $currentCandidateID, $duplicateCandidateID)
    {
        if ((int) $currentCandidateID >= (int) $duplicateCandidateID)
        {
            return false;
        }

        $currentCandidate = $this->getCandidateData($siteID, $currentCandidateID);
        $duplicateCandidate = $this->getCandidateData($siteID, $duplicateCandidateID);

        return $this->isQuickImportPlaceholder($currentCandidate) &&
            $this->isQuickImportPlaceholder($duplicateCandidate);
    }

    private function addDuplicateCheck(&$checks, $type, $label, $value)
    {
        $value = trim((string) $value);
        if ($value == '')
        {
            return;
        }

        $checks[] = array(
            'type' => $type,
            'label' => $label,
            'value' => $value
        );
    }

    private function findCandidateIDByEmail($siteID, $excludeCandidateID, $email)
    {
        $email = trim((string) $email);
        if ($email == '')
        {
            return 0;
        }

        $db = DatabaseConnection::getInstance();
        $sql = sprintf(
            "SELECT candidate_id AS candidateID
             FROM candidate
             WHERE candidate_id <> %s
             AND site_id = %s
             AND (email1 = %s OR email2 = %s)
             LIMIT 1",
            $db->makeQueryInteger($excludeCandidateID),
            $db->makeQueryInteger($siteID),
            $db->makeQueryString($email),
            $db->makeQueryString($email)
        );
        $rs = $db->getAssoc($sql);

        return empty($rs) ? 0 : (int) $rs['candidateID'];
    }

    private function findCandidateIDByPhone($siteID, $excludeCandidateID, $phone)
    {
        $phone = trim((string) $phone);
        if ($phone == '')
        {
            return 0;
        }

        $db = DatabaseConnection::getInstance();
        $sql = sprintf(
            "SELECT candidate_id AS candidateID
             FROM candidate
             WHERE candidate_id <> %s
             AND site_id = %s
             AND (phone_home = %s OR phone_cell = %s OR phone_work = %s)
             LIMIT 1",
            $db->makeQueryInteger($excludeCandidateID),
            $db->makeQueryInteger($siteID),
            $db->makeQueryString($phone),
            $db->makeQueryString($phone),
            $db->makeQueryString($phone)
        );
        $rs = $db->getAssoc($sql);

        return empty($rs) ? 0 : (int) $rs['candidateID'];
    }

    private function findCandidateIDByLink($siteID, $excludeCandidateID, $link)
    {
        $link = $this->normalizeDuplicateLookupURL($link);
        if ($link == '')
        {
            return 0;
        }

        $db = DatabaseConnection::getInstance();
        $like = $db->makeQueryString('%' . $link . '%');
        $sql = sprintf(
            "SELECT candidate_id AS candidateID
             FROM candidate
             WHERE candidate_id <> %s
             AND site_id = %s
             AND (
                web_site LIKE %s OR notes LIKE %s OR facebook LIKE %s
                OR github LIKE %s OR linkedin LIKE %s OR googleplus LIKE %s
                OR twitter LIKE %s OR cakeresume LIKE %s OR link1 LIKE %s
                OR link2 LIKE %s OR link3 LIKE %s
             )
             LIMIT 1",
            $db->makeQueryInteger($excludeCandidateID),
            $db->makeQueryInteger($siteID),
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like
        );
        $rs = $db->getAssoc($sql);

        return empty($rs) ? 0 : (int) $rs['candidateID'];
    }

    private function normalizeDuplicateLookupURL($url)
    {
        $url = trim(html_entity_decode((string) $url, ENT_QUOTES, 'UTF-8'));
        if ($url == '')
        {
            return '';
        }

        $parseTarget = preg_match('/^[a-z][a-z0-9+.-]*:\/\//i', $url)
            ? $url
            : ('https://' . preg_replace('/^\/+/', '', $url));
        $parts = @parse_url($parseTarget);
        if (!is_array($parts) || empty($parts['host']))
        {
            return '';
        }

        $host = strtolower(preg_replace('/^www\./i', '', $parts['host']));
        $path = isset($parts['path']) ? rtrim(rawurldecode($parts['path']), '/') : '';
        if ($path == '' || $path == '/')
        {
            return '';
        }

        return $host . $path;
    }

    private function addDuplicateNoticeActivity($siteID, $candidateID, $userID, $activityID, $duplicates, $parseLogID)
    {
        $notes = array();
        $notes[] = 'NBI ATS 快速匯入未自動更新。';
        $notes[] = '原因: 偵測到疑似重複人選。';
        foreach ($duplicates as $duplicate)
        {
            $line = '疑似既有人選 #' . $duplicate['candidateID'];
            if (!empty($duplicate['reason']))
            {
                $line .= '，依據: ' . $duplicate['reason'];
            }
            if (!empty($duplicate['value']))
            {
                $line .= ' = ' . $duplicate['value'];
            }
            $notes[] = $line;
        }
        $notes[] = '請人工確認是否合併、刪除暫存人選，或保留兩筆資料。';

        $this->recordCandidateImportActivity($siteID, $candidateID, $userID, $activityID, implode("\n", $notes));
    }

    private function mapAIResultToCandidateColumns($candidate)
    {
        $firstName = isset($candidate['first_name']) ? $candidate['first_name'] : '';
        $lastName = isset($candidate['last_name']) ? $candidate['last_name'] : '';
        $chineseName = isset($candidate['chinese_name']) ? $candidate['chinese_name'] : '';
        if (trim($firstName) == '' && trim($lastName) == '' && trim($chineseName) != '')
        {
            $firstName = $chineseName;
        }

        return array(
            'first_name' => array('currentKey' => 'firstName', 'label' => 'firstName', 'value' => $firstName),
            'last_name' => array('currentKey' => 'lastName', 'label' => 'lastName', 'value' => $lastName),
            'chinese_name' => array('currentKey' => 'chineseName', 'label' => 'chineseName', 'value' => $chineseName),
            'email1' => array('currentKey' => 'email1', 'label' => 'email1', 'value' => $this->getCandidateValue($candidate, 'email')),
            'phone_home' => array('currentKey' => 'phoneHome', 'label' => 'phoneHome', 'value' => $this->getCandidateValue($candidate, 'phone')),
            'address' => array('currentKey' => 'address', 'label' => 'address', 'value' => $this->getCandidateValue($candidate, 'address')),
            'city' => array('currentKey' => 'city', 'label' => 'city', 'value' => $this->getCandidateValue($candidate, 'city')),
            'state' => array('currentKey' => 'state', 'label' => 'state', 'value' => $this->getCandidateValue($candidate, 'state')),
            'zip' => array('currentKey' => 'zip', 'label' => 'zip', 'value' => $this->getCandidateValue($candidate, 'zip_code')),
            'current_employer' => array('currentKey' => 'currentEmployer', 'label' => 'currentEmployer', 'value' => $this->getCandidateValue($candidate, 'current_employer')),
            'job_title' => array('currentKey' => 'jobTitle', 'label' => 'jobTitle', 'value' => $this->firstNonEmptyValue($candidate, array('job_title_en', 'job_title_raw', 'job_title_zh'))),
            'functions' => array('currentKey' => 'functions', 'label' => 'functions', 'value' => $this->firstNonEmptyValue($candidate, array('function_en', 'function_raw', 'function_zh'))),
            'job_level' => array('currentKey' => 'jobLevel', 'label' => 'jobLevel', 'value' => $this->formatJobLevelForDisplay($this->getCandidateValue($candidate, 'job_level'))),
            'web_site' => array('currentKey' => 'webSite', 'label' => 'webSite', 'value' => $this->normalizeUrl($this->getCandidateValue($candidate, 'website'))),
            'linkedin' => array('currentKey' => 'linkedin', 'label' => 'linkedin', 'value' => $this->normalizeUrl($this->getCandidateValue($candidate, 'linkedin'))),
            'github' => array('currentKey' => 'github', 'label' => 'github', 'value' => $this->normalizeUrl($this->getCandidateValue($candidate, 'github'))),
            'facebook' => array('currentKey' => 'facebook', 'label' => 'facebook', 'value' => $this->normalizeUrl($this->getCandidateValue($candidate, 'facebook'))),
            'googleplus' => array('currentKey' => 'googleplus', 'label' => 'googleplus', 'value' => $this->normalizeUrl($this->getCandidateValue($candidate, 'googleplus'))),
            'twitter' => array('currentKey' => 'twitter', 'label' => 'twitter', 'value' => $this->normalizeUrl($this->getCandidateValue($candidate, 'twitter'))),
            'cakeresume' => array('currentKey' => 'cakeresume', 'label' => 'cakeresume', 'value' => $this->normalizeUrl($this->getCandidateValue($candidate, 'cakeresume'))),
            'link1' => array('currentKey' => 'link1', 'label' => 'link1', 'value' => $this->normalizeUrl($this->getCandidateValue($candidate, 'link1'))),
            'link2' => array('currentKey' => 'link2', 'label' => 'link2', 'value' => $this->normalizeUrl($this->getCandidateValue($candidate, 'link2'))),
            'link3' => array('currentKey' => 'link3', 'label' => 'link3', 'value' => $this->normalizeUrl($this->getCandidateValue($candidate, 'link3'))),
            'highest_degree' => array('currentKey' => 'highestDegree', 'label' => 'highestDegree', 'value' => $this->getCandidateValue($candidate, 'highest_degree')),
            'major' => array('currentKey' => 'major', 'label' => 'major', 'value' => $this->getCandidateValue($candidate, 'major')),
            'key_skills' => array('currentKey' => 'keySkills', 'label' => 'keySkills', 'value' => $this->firstNonEmptyList($candidate, array('key_skills_en', 'skills_raw', 'skills_en')))
        );
    }

    private function getCandidateValue($candidate, $key)
    {
        return isset($candidate[$key]) ? trim((string) $candidate[$key]) : '';
    }

    private function firstNonEmptyValue($candidate, $keys)
    {
        foreach ($keys as $key)
        {
            $value = $this->getCandidateValue($candidate, $key);
            if ($value != '')
            {
                return $value;
            }
        }

        return '';
    }

    private function firstNonEmptyList($candidate, $keys)
    {
        foreach ($keys as $key)
        {
            if (!isset($candidate[$key]))
            {
                continue;
            }
            $value = $this->stringifyList($candidate[$key]);
            if ($value != '')
            {
                return $value;
            }
        }

        return '';
    }

    private function stringifyList($value)
    {
        if (!is_array($value))
        {
            return trim(str_replace(array("\r", "\n"), ' ', (string) $value));
        }

        $items = array();
        foreach ($value as $item)
        {
            $item = trim((string) $item);
            if ($item != '' && !in_array($item, $items))
            {
                $items[] = $item;
            }
        }

        return implode(', ', $items);
    }

    private function normalizeUrl($value)
    {
        $value = trim((string) $value);
        if ($value == '' || preg_match('/^https?:\/\//i', $value))
        {
            return $value;
        }

        return 'https://' . preg_replace('/^\/+/', '', $value);
    }

    private function formatJobLevelForDisplay($jobLevel)
    {
        $jobLevel = trim((string) $jobLevel);
        if ($jobLevel == '')
        {
            return '';
        }

        $normalized = strtolower($jobLevel);
        $labels = array(
            'intern' => 'Intern',
            'junior' => 'Junior',
            'mid' => 'Mid',
            'senior' => 'Senior',
            'staff' => 'Staff',
            'principal' => 'Principal',
            'lead' => 'Lead',
            'manager' => 'Manager',
            'director' => 'Director',
            'vp' => 'VP',
            'c_level' => 'C-Level'
        );

        if (isset($labels[$normalized]))
        {
            return $labels[$normalized];
        }

        return ucwords(str_replace('_', ' ', $normalized));
    }

    private function isQuickImportPlaceholder($candidateData)
    {
        $firstName = isset($candidateData['firstName']) ? strtolower(trim($candidateData['firstName'])) : '';
        $lastName = isset($candidateData['lastName']) ? strtolower(trim($candidateData['lastName'])) : '';

        return $firstName == 'imported' && $lastName == 'candidate';
    }

    private function isEmptyValue($value)
    {
        return trim((string) $value) == '';
    }

    private function recordCandidateImportActivity($siteID, $candidateID, $userID, $activityID, $statusNotes)
    {
        $activityEntries = new ActivityEntries($siteID);
        $existingNotes = $this->getCandidateImportActivityNotes($siteID, $candidateID, $activityID);
        if ($existingNotes !== false)
        {
            $updatedNotes = $this->mergeCandidateImportActivityNotes($existingNotes, $statusNotes);
            if ($activityEntries->update((int) $activityID, ACTIVITY_NOTE, $updatedNotes, false, false, 0))
            {
                return (int) $activityID;
            }
        }

        return $this->addCandidateActivity($siteID, $candidateID, $userID, $statusNotes);
    }

    private function getCandidateImportActivityNotes($siteID, $candidateID, $activityID)
    {
        if ((int) $activityID <= 0)
        {
            return false;
        }

        $db = DatabaseConnection::getInstance();
        $sql = sprintf(
            "SELECT notes
             FROM activity
             WHERE activity_id = %s
             AND data_item_id = %s
             AND data_item_type = %s
             AND site_id = %s",
            $db->makeQueryInteger($activityID),
            $db->makeQueryInteger($candidateID),
            DATA_ITEM_CANDIDATE,
            $db->makeQueryInteger($siteID)
        );
        $activity = $db->getAssoc($sql);

        return empty($activity) ? false : (string) $activity['notes'];
    }

    private function mergeCandidateImportActivityNotes($existingNotes, $statusNotes)
    {
        $existingNotes = rtrim((string) $existingNotes);
        $statusNotes = trim((string) $statusNotes);
        if ($statusNotes == '')
        {
            return $existingNotes;
        }

        $sourceLines = $this->extractImportSourceLines($existingNotes);
        if (count($sourceLines) > 0)
        {
            return $this->insertImportSourceLines($statusNotes, $sourceLines);
        }

        return $statusNotes;
    }

    private function extractImportSourceLines($notes)
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $notes);
        $sourceLines = array();
        foreach ($lines as $line)
        {
            $line = trim($line);
            if ($line == '')
            {
                continue;
            }
            if (preg_match('/^(Source URL|來源)\s*:/i', $line))
            {
                $sourceLines['source'] = preg_replace('/^Source URL\s*:/i', '來源:', $line);
            }
            else if (preg_match('/^(Page title|頁面標題)\s*:/i', $line))
            {
                $sourceLines['title'] = preg_replace('/^Page title\s*:/i', '頁面標題:', $line);
            }
        }

        $ordered = array();
        foreach (array('source', 'title') as $key)
        {
            if (isset($sourceLines[$key]))
            {
                $ordered[] = $sourceLines[$key];
            }
        }

        return $ordered;
    }

    private function insertImportSourceLines($statusNotes, $sourceLines)
    {
        $lines = preg_split('/\r\n|\r|\n/', trim((string) $statusNotes));
        if (count($lines) <= 1)
        {
            return trim((string) $statusNotes) . "\n" . implode("\n", $sourceLines);
        }

        $firstLine = array_shift($lines);
        return $firstLine . "\n" . implode("\n", $sourceLines) . "\n" . implode("\n", $lines);
    }

    private function formatActivityFieldLabels($labels)
    {
        $map = array(
            'firstName' => '姓名',
            'lastName' => '姓名',
            'chineseName' => '中文姓名',
            'email1' => 'Email',
            'phoneHome' => '電話',
            'address' => '地址',
            'city' => '地址',
            'state' => '地址',
            'zip' => '地址',
            'currentEmployer' => '現職公司',
            'jobTitle' => '職稱',
            'functions' => '職務類型',
            'jobLevel' => '職級',
            'webSite' => '網站',
            'linkedin' => 'LinkedIn',
            'github' => 'GitHub',
            'facebook' => 'Facebook',
            'googleplus' => 'Google+',
            'twitter' => 'Twitter',
            'cakeresume' => 'CakeResume',
            'link1' => '相關連結',
            'link2' => '相關連結',
            'link3' => '相關連結',
            'highestDegree' => '最高學歷',
            'major' => '科系',
            'keySkills' => '技能'
        );
        $formatted = array();
        foreach ($labels as $label)
        {
            $label = trim((string) $label);
            if ($label == '')
            {
                continue;
            }
            $formatted[] = isset($map[$label]) ? $map[$label] : $label;
        }

        return array_values(array_unique($formatted));
    }

    private function addCandidateActivity($siteID, $candidateID, $userID, $notes)
    {
        $activityEntries = new ActivityEntries($siteID);
        return $activityEntries->add(
            $candidateID,
            DATA_ITEM_CANDIDATE,
            ACTIVITY_NOTE,
            $notes,
            $userID
        );
    }
}
