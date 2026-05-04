<?php
/*
 * CATS
 *
 * AI resume parsing service wrapper.
 */

include_once('./config.php');
include_once('./lib/DatabaseConnection.php');

class AIResumeParser
{
    const DEFAULT_SOURCE_TYPE = 'paste';
    const MAX_KEY_SKILLS = 10;
    const MAX_RESUME_INPUT_BYTES = 100000;

    private $_lastError;
    private $_lastRequest;
    private $_lastResponse;


    public function __construct()
    {
        $this->_lastError = '';
        $this->_lastRequest = array();
        $this->_lastResponse = array();
    }


    public function isEnabled()
    {
        if (!ENABLE_AI_RESUME_PARSER)
        {
            return false;
        }

        // Test bypass returns canned data, so the API key is irrelevant.
        if (defined('AI_RESUME_PARSER_TEST_BYPASS') && AI_RESUME_PARSER_TEST_BYPASS)
        {
            return true;
        }

        return (OPENAI_API_KEY != '');
    }


    public function getLastError()
    {
        return $this->_lastError;
    }


    public function getLastRequest()
    {
        return $this->_lastRequest;
    }


    public function getLastResponse()
    {
        return $this->_lastResponse;
    }


    /**
     * Calls OpenAI to extract structured candidate fields from a resume.
     *
     * `$options['parseMode']` selects the intensity:
     *   - 'fast': uses OPENAI_FAST_MODEL (nano), instructs the model to skip
     *             career_summary, skill_summary, and jecho_report_markdown,
     *             and caps output tokens at 1500. Used to "fill candidate
     *             fields" cheaply.
     *   - 'full': uses OPENAI_MODEL (mini), produces summaries, and (when
     *             $options['includeJechoReport'] is true) produces the full
     *             Jecho Report markdown. Output tokens up to 9000.
     *
     * 'fast' and 'full' are the canonical mode keys persisted in the
     * ai_resume_parse_log.parse_mode column and used by the chrome extension's
     * importMode. UI button labels may differ (the Add/Edit candidate widget
     * documents the contract — see modules/candidates/Add.tpl).
     *
     * Returns the normalized result array, or false on failure.
     */
    public function parseResumeText($resumeText, $options = array())
    {
        $resumeText = trim($resumeText);
        if ($resumeText == '')
        {
            $this->_setError('Resume text is empty.');
            return false;
        }

        if (!$this->isEnabled())
        {
            $this->_setError('AI resume parser is not enabled.');
            return false;
        }

        $resumeText = $this->_truncateResumeText($resumeText);

        if ($this->_isTestBypassEnabled())
        {
            return $this->_buildBypassParseResult($options);
        }

        $payload = $this->_buildRequestPayload($resumeText, $options);
        $this->_lastRequest = $payload;

        $response = $this->_postJSON(
            rtrim(OPENAI_BASE_URL, '/') . '/responses',
            $payload,
            array(
                'Authorization: Bearer ' . OPENAI_API_KEY,
                'Content-Type: application/json'
            )
        );

        if ($response === false)
        {
            return false;
        }

        $this->_lastResponse = $response;

        $parsedJSON = $this->_extractStructuredOutput($response);
        if ($parsedJSON === false)
        {
            $this->_setError('Failed to extract structured JSON from OpenAI response.');
            return false;
        }

        $result = json_decode($parsedJSON, true);
        if (!is_array($result))
        {
            $this->_setError('OpenAI response JSON could not be decoded.');
            return false;
        }

        return $this->_normalizeResult($result, $options);
    }


    public function generateJechoReportMarkdown($resumeText, $options = array())
    {
        $resumeText = trim($resumeText);
        if ($resumeText == '')
        {
            $this->_setError('Resume text is empty.');
            return false;
        }

        if (!$this->isEnabled())
        {
            $this->_setError('AI resume parser is not enabled.');
            return false;
        }

        $resumeText = $this->_truncateResumeText($resumeText);

        if ($this->_isTestBypassEnabled())
        {
            $this->_lastResponse = $this->_buildBypassFakeResponse();
            return $this->_buildBypassJechoReportMarkdown($options);
        }

        $payload = $this->_buildJechoReportPayload($resumeText, $options);
        $this->_lastRequest = $payload;

        $response = $this->_postJSON(
            rtrim(OPENAI_BASE_URL, '/') . '/responses',
            $payload,
            array(
                'Authorization: Bearer ' . OPENAI_API_KEY,
                'Content-Type: application/json'
            )
        );

        if ($response === false)
        {
            return false;
        }

        $this->_lastResponse = $response;

        $markdown = $this->_extractStructuredOutput($response);
        if ($markdown === false)
        {
            $this->_setError('Failed to extract Markdown output from OpenAI response.');
            return false;
        }

        return $this->_cleanMarkdownOutput($markdown);
    }


    public function makeStandardFilename($candidateName, $languageCode, $extension, $version = 1, $date = false, $prefix = 'Resume')
    {
        $candidateName = $this->_sanitizeFilenameToken($candidateName);
        $languageCode = strtolower(trim($languageCode));
        if ($languageCode == '')
        {
            $languageCode = 'mixed';
        }

        $extension = strtolower(trim($extension));
        if ($extension == '')
        {
            $extension = 'txt';
        }

        if ($date === false)
        {
            $date = date('Ymd');
        }

        $prefix = preg_replace('/[^A-Za-z0-9_]+/', '_', trim((string) $prefix));
        $prefix = trim($prefix, '_');
        if ($prefix == '')
        {
            $prefix = 'Resume';
        }

        $version = (int) $version;
        $fileName = sprintf(
            $prefix . '_%s_%s_%s',
            $candidateName,
            $date,
            $languageCode
        );

        if ($version > 1)
        {
            $fileName .= '_V' . ($version - 1);
        }

        return $fileName . '.' . $extension;
    }


    public function makeJechoReportFilename($originalFilename, $candidateName, $languageCode)
    {
        $languageCode = strtolower(trim($languageCode));
        if ($languageCode != 'zh' && $languageCode != 'en' && $languageCode != 'cn')
        {
            $languageCode = 'zh';
        }

        $baseName = trim((string) $originalFilename);
        if ($baseName != '')
        {
            $baseName = pathinfo($baseName, PATHINFO_FILENAME);
            $baseName = preg_replace('/^Resume(?:[\s_]+AI)?[\s_]+/i', '', $baseName);
            $baseName = preg_replace('/^Jecho(?:[\s_]+)AI(?:[\s_]+)Report(?:[\s_]+)/i', '', $baseName);
            $baseName = preg_replace('/(?:[_\s]+(?:zh|en|cn|mixed))+(?:[_\s]+V\d+)?$/i', '', $baseName);
            $baseName = trim($baseName);
        }

        if ($baseName == '')
        {
            $baseName = $this->_sanitizeFilenameToken($candidateName) . '_' . date('Ymd');
        }
        else
        {
            $baseName = $this->_sanitizeFilenameToken($baseName);
        }

        $fileName = 'Jecho_AI_Report_' . $baseName . '_' . $languageCode;

        return $fileName . '.md';
    }


    public function makeNextStandardFilename($candidateName, $languageCode, $extension, $existingFilenames = array(), $prefix = 'Resume')
    {
        $baseFileName = $this->makeStandardFilename($candidateName, $languageCode, $extension, 1, false, $prefix);

        $normalizedExisting = $this->_normalizeFilenameArray($existingFilenames);
        if (!in_array(strtolower($baseFileName), $normalizedExisting))
        {
            return $baseFileName;
        }

        $pathInfo = pathinfo($baseFileName);
        $baseName = isset($pathInfo['filename']) ? $pathInfo['filename'] : $baseFileName;
        $fileExtension = isset($pathInfo['extension']) ? $pathInfo['extension'] : $extension;
        $versionPrefix = $baseName;
        $nextVersion = 2;

        if (preg_match('/^(.*)_V(\d+)$/', $baseName, $matches))
        {
            $versionPrefix = $matches[1];
            $nextVersion = ((int) $matches[2]) + 1;
        }

        foreach ($normalizedExisting as $existingFileName)
        {
            if (preg_match('/^' . preg_quote(strtolower($versionPrefix), '/') . '_v(\d+)\.' . preg_quote(strtolower($fileExtension), '/') . '$/i', $existingFileName, $matches))
            {
                $version = (int) $matches[1];
                if ($version >= $nextVersion)
                {
                    $nextVersion = $version + 1;
                }
            }
        }

        return $versionPrefix . '_V' . $nextVersion . '.' . $fileExtension;
    }


    public function makeNextJechoReportFilename($originalFilename, $candidateName, $languageCode, $existingFilenames = array())
    {
        $baseFileName = $this->makeJechoReportFilename(
            $originalFilename,
            $candidateName,
            $languageCode
        );

        if (!in_array(strtolower($baseFileName), $this->_normalizeFilenameArray($existingFilenames)))
        {
            return $baseFileName;
        }

        $pathInfo = pathinfo($baseFileName);
        $baseName = isset($pathInfo['filename']) ? $pathInfo['filename'] : $baseFileName;
        $extension = isset($pathInfo['extension']) ? $pathInfo['extension'] : 'md';
        $normalizedExisting = $this->_normalizeFilenameArray($existingFilenames);
        $versionPrefix = $baseName;
        $nextVersion = 1;

        if (preg_match('/^(.*)_V(\d+)$/', $baseName, $matches))
        {
            $versionPrefix = $matches[1];
            $nextVersion = ((int) $matches[2]) + 1;
        }

        foreach ($normalizedExisting as $existingFileName)
        {
            if (preg_match('/^' . preg_quote(strtolower($versionPrefix), '/') . '_v(\d+)\.' . preg_quote(strtolower($extension), '/') . '$/i', $existingFileName, $matches))
            {
                $version = (int) $matches[1];
                if ($version >= $nextVersion)
                {
                    $nextVersion = $version + 1;
                }
            }
        }

        return $versionPrefix . '_V' . $nextVersion . '.' . $extension;
    }


    public function detectLanguageCode($resumeText, $fileName = '')
    {
        if ($fileName != '' && preg_match('/_(zh|en|cn|mixed)(?:_V\d+)?\.[a-z0-9]+$/i', $fileName, $matches))
        {
            return strtolower($matches[1]);
        }

        $hasChinese = preg_match('/[\x{4e00}-\x{9fff}]/u', $resumeText);
        $hasASCIIWords = preg_match('/[A-Za-z]{3,}/', $resumeText);

        if ($hasChinese && $hasASCIIWords)
        {
            return 'mixed';
        }
        if ($hasChinese)
        {
            return 'zh';
        }

        return 'en';
    }


    public function createParseLog($siteID, $userID, $sourceType, $originalFilename, $storedFilename,
        $documentLanguage, $result, $status = 'success', $saveParseResult = true)
    {
        if (!$this->_requiredTablesExist())
        {
            return 0;
        }

        $db = DatabaseConnection::getInstance();

        $parseMode = '';
        if (isset($result['meta']['parse_mode']))
        {
            $parseMode = (string) $result['meta']['parse_mode'];
        }
        if ($parseMode == '' && $sourceType == 'jecho_report')
        {
            $parseMode = 'full';
        }

        $sql = sprintf(
            "INSERT INTO ai_resume_parse_log (
                site_id,
                user_id,
                source_type,
                original_filename,
                stored_filename,
                document_language,
                provider,
                model,
                input_tokens,
                output_tokens,
                status,
                parse_mode,
                saved_candidate_id,
                created_at
            ) VALUES (
                %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 0, NOW()
            )",
            $db->makeQueryInteger($siteID),
            $db->makeQueryInteger($userID),
            $db->makeQueryString($sourceType),
            $db->makeQueryString($originalFilename),
            $db->makeQueryString($storedFilename),
            $db->makeQueryString($documentLanguage),
            $db->makeQueryString(isset($result['usage']['provider']) ? $result['usage']['provider'] : 'openai'),
            $db->makeQueryString(isset($result['usage']['model']) ? $result['usage']['model'] : OPENAI_MODEL),
            $db->makeQueryInteger(isset($result['usage']['input_tokens']) ? $result['usage']['input_tokens'] : 0),
            $db->makeQueryInteger(isset($result['usage']['output_tokens']) ? $result['usage']['output_tokens'] : 0),
            $db->makeQueryString($status),
            $db->makeQueryString($parseMode)
        );
        $db->query($sql);
        $parseLogID = $db->getLastInsertID();

        if ($saveParseResult)
        {
            $this->saveParseResult($parseLogID, $result);
        }

        return $parseLogID;
    }


    public function getUsageResultFromLastResponse($defaultProvider, $defaultModel)
    {
        if ($defaultProvider == '')
        {
            $defaultProvider = 'openai';
        }
        if ($defaultModel == '')
        {
            $defaultModel = OPENAI_MODEL;
        }

        return array(
            'usage' => array(
                'provider' => $defaultProvider,
                'model' => isset($this->_lastResponse['model']) ? $this->_lastResponse['model'] : $defaultModel,
                'input_tokens' => $this->_getUsageValue($this->_lastResponse, 'input_tokens'),
                'output_tokens' => $this->_getUsageValue($this->_lastResponse, 'output_tokens')
            )
        );
    }


    public function saveParseResult($parseLogID, $result)
    {
        if (!$this->_requiredTablesExist() || (int) $parseLogID <= 0)
        {
            return;
        }

        $db = DatabaseConnection::getInstance();
        $candidate = isset($result['candidate']) && is_array($result['candidate']) ? $result['candidate'] : array();
        $normalization = isset($result['normalization']) && is_array($result['normalization']) ? $result['normalization'] : array();

        $sql = sprintf(
            "INSERT INTO ai_resume_parse_result (
                parse_log_id,
                first_name,
                last_name,
                chinese_name,
                email,
                phone,
                address,
                city,
                state,
                zip_code,
                current_employer,
                job_title_raw,
                job_title_zh,
                job_title_en,
                job_title_canonical_key,
                function_raw,
                function_zh,
                function_en,
                function_canonical_key,
                job_level,
                website,
                linkedin,
                github,
                facebook,
                googleplus,
                twitter,
                cakeresume,
                link1,
                link2,
                link3,
                highest_degree,
                major,
                skills_raw,
                skills_zh,
                skills_en,
                key_skills_zh,
                key_skills_en,
                career_summary,
                skill_summary,
                job_title_confidence,
                function_confidence,
                job_level_confidence,
                skills_confidence,
                created_at,
                updated_at
            ) VALUES (
                %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s,
                %s, %s, %s,
                %s, %s, %s, %s,
                NOW(), NOW()
            )",
            $db->makeQueryInteger($parseLogID),
            $db->makeQueryString(isset($candidate['first_name']) ? $candidate['first_name'] : ''),
            $db->makeQueryString(isset($candidate['last_name']) ? $candidate['last_name'] : ''),
            $db->makeQueryString(isset($candidate['chinese_name']) ? $candidate['chinese_name'] : ''),
            $db->makeQueryString(isset($candidate['email']) ? $candidate['email'] : ''),
            $db->makeQueryString(isset($candidate['phone']) ? $candidate['phone'] : ''),
            $db->makeQueryString(isset($candidate['address']) ? $candidate['address'] : ''),
            $db->makeQueryString(isset($candidate['city']) ? $candidate['city'] : ''),
            $db->makeQueryString(isset($candidate['state']) ? $candidate['state'] : ''),
            $db->makeQueryString(isset($candidate['zip_code']) ? $candidate['zip_code'] : ''),
            $db->makeQueryString(isset($candidate['current_employer']) ? $candidate['current_employer'] : ''),
            $db->makeQueryString(isset($candidate['job_title_raw']) ? $candidate['job_title_raw'] : ''),
            $db->makeQueryString(isset($candidate['job_title_zh']) ? $candidate['job_title_zh'] : ''),
            $db->makeQueryString(isset($candidate['job_title_en']) ? $candidate['job_title_en'] : ''),
            $db->makeQueryString(isset($candidate['job_title_canonical_key']) ? $candidate['job_title_canonical_key'] : ''),
            $db->makeQueryString(isset($candidate['function_raw']) ? $candidate['function_raw'] : ''),
            $db->makeQueryString(isset($candidate['function_zh']) ? $candidate['function_zh'] : ''),
            $db->makeQueryString(isset($candidate['function_en']) ? $candidate['function_en'] : ''),
            $db->makeQueryString(isset($candidate['function_canonical_key']) ? $candidate['function_canonical_key'] : ''),
            $db->makeQueryString(isset($candidate['job_level']) ? $candidate['job_level'] : ''),
            $db->makeQueryString(isset($candidate['website']) ? $candidate['website'] : ''),
            $db->makeQueryString(isset($candidate['linkedin']) ? $candidate['linkedin'] : ''),
            $db->makeQueryString(isset($candidate['github']) ? $candidate['github'] : ''),
            $db->makeQueryString(isset($candidate['facebook']) ? $candidate['facebook'] : ''),
            $db->makeQueryString(isset($candidate['googleplus']) ? $candidate['googleplus'] : ''),
            $db->makeQueryString(isset($candidate['twitter']) ? $candidate['twitter'] : ''),
            $db->makeQueryString(isset($candidate['cakeresume']) ? $candidate['cakeresume'] : ''),
            $db->makeQueryString(isset($candidate['link1']) ? $candidate['link1'] : ''),
            $db->makeQueryString(isset($candidate['link2']) ? $candidate['link2'] : ''),
            $db->makeQueryString(isset($candidate['link3']) ? $candidate['link3'] : ''),
            $db->makeQueryString(isset($candidate['highest_degree']) ? $candidate['highest_degree'] : ''),
            $db->makeQueryString(isset($candidate['major']) ? $candidate['major'] : ''),
            $db->makeQueryString($this->_stringifyArray(isset($candidate['skills_raw']) ? $candidate['skills_raw'] : array())),
            $db->makeQueryString($this->_stringifyArray(isset($candidate['skills_zh']) ? $candidate['skills_zh'] : array())),
            $db->makeQueryString($this->_stringifyArray(isset($candidate['skills_en']) ? $candidate['skills_en'] : array())),
            $db->makeQueryString($this->_stringifyArray(isset($candidate['key_skills_zh']) ? $candidate['key_skills_zh'] : array())),
            $db->makeQueryString($this->_stringifyArray(isset($candidate['key_skills_en']) ? $candidate['key_skills_en'] : array())),
            $db->makeQueryString(isset($candidate['career_summary']) ? $candidate['career_summary'] : ''),
            $db->makeQueryString(isset($candidate['skill_summary']) ? $candidate['skill_summary'] : ''),
            (float) (isset($normalization['job_title_confidence']) ? $normalization['job_title_confidence'] : 0),
            (float) (isset($normalization['function_confidence']) ? $normalization['function_confidence'] : 0),
            (float) (isset($normalization['job_level_confidence']) ? $normalization['job_level_confidence'] : 0),
            (float) (isset($normalization['skills_confidence']) ? $normalization['skills_confidence'] : 0)
        );

        $db->query($sql);
    }


    public function markSavedCandidate($parseLogID, $candidateID)
    {
        if ((int) $parseLogID <= 0 || !$this->_requiredTablesExist())
        {
            return;
        }

        $db = DatabaseConnection::getInstance();
        $sql = sprintf(
            "UPDATE ai_resume_parse_log
             SET status = 'saved',
                 saved_candidate_id = %s
             WHERE id = %s",
            $db->makeQueryInteger($candidateID),
            $db->makeQueryInteger($parseLogID)
        );

        $db->query($sql);
    }


    public function linkCandidate($parseLogID, $candidateID)
    {
        if ((int) $parseLogID <= 0 || !$this->_requiredTablesExist())
        {
            return;
        }

        $db = DatabaseConnection::getInstance();
        $sql = sprintf(
            "UPDATE ai_resume_parse_log
             SET saved_candidate_id = %s
             WHERE id = %s",
            $db->makeQueryInteger($candidateID),
            $db->makeQueryInteger($parseLogID)
        );

        $db->query($sql);
    }


    public function markStatus($parseLogID, $status)
    {
        if ((int) $parseLogID <= 0 || !$this->_requiredTablesExist())
        {
            return;
        }

        $db = DatabaseConnection::getInstance();
        $sql = sprintf(
            "UPDATE ai_resume_parse_log
             SET status = %s
             WHERE id = %s",
            $db->makeQueryString($status),
            $db->makeQueryInteger($parseLogID)
        );

        $db->query($sql);
    }


    private function _isTestBypassEnabled()
    {
        return defined('AI_RESUME_PARSER_TEST_BYPASS') && AI_RESUME_PARSER_TEST_BYPASS;
    }


    private function _buildBypassParseResult($options)
    {
        $this->_lastRequest = array('test_bypass' => true);
        $this->_lastResponse = $this->_buildBypassFakeResponse();

        $parseMode = $this->_getParseMode($options);
        $includeJechoReport = ($parseMode != 'fast') && $this->_shouldIncludeJechoReport($options);
        $languageHint = isset($options['languageHint']) ? strtolower(trim($options['languageHint'])) : '';
        $targetLanguage = $this->_getJechoReportTargetLanguage($options, $languageHint);

        $candidate = array(
            'first_name' => 'Test',
            'last_name' => 'Bypass',
            'chinese_name' => '測試 旁路',
            'email' => 'test.bypass@example.com',
            'phone' => '0900-000-000',
            'address' => '101 Demo Road',
            'city' => 'Taipei',
            'state' => 'Taipei',
            'zip_code' => '100',
            'current_employer' => 'Demo Co.',
            'job_title_raw' => 'Senior Software Engineer',
            'job_title_zh' => '資深軟體工程師',
            'job_title_en' => 'Senior Software Engineer',
            'job_title_canonical_key' => 'senior_software_engineer',
            'function_raw' => 'Engineering',
            'function_zh' => '工程',
            'function_en' => 'Engineering',
            'function_canonical_key' => 'engineering',
            'job_level' => 'Senior',
            'website' => 'https://example.com',
            'linkedin' => 'https://www.linkedin.com/in/test-bypass',
            'github' => 'https://github.com/test-bypass',
            'facebook' => '',
            'googleplus' => '',
            'twitter' => '',
            'cakeresume' => '',
            'link1' => '',
            'link2' => '',
            'link3' => '',
            'highest_degree' => 'Master',
            'major' => 'Computer Science',
            'skills_raw' => array('PHP', 'JavaScript', 'MySQL', 'Docker'),
            'skills_zh' => array('PHP', 'JavaScript', 'MySQL', 'Docker'),
            'skills_en' => array('PHP', 'JavaScript', 'MySQL', 'Docker'),
            'key_skills_zh' => array('PHP', 'JavaScript', 'MySQL'),
            'key_skills_en' => array('PHP', 'JavaScript', 'MySQL'),
            'career_summary' => '[TEST BYPASS] 10+ years building web platforms; this summary is dummy data and was not produced by the AI.',
            'skill_summary' => '[TEST BYPASS] Strong full-stack web skills; this summary is dummy data and was not produced by the AI.'
        );

        $result = array(
            'candidate' => $candidate,
            'normalization' => array(
                'job_title_confidence' => 0.99,
                'function_confidence' => 0.99,
                'job_level_confidence' => 0.99,
                'skills_confidence' => 0.99
            ),
            'jecho_report_markdown' => $includeJechoReport ? $this->_buildBypassJechoReportMarkdown($options) : ''
        );

        return $this->_normalizeResult($result, $options);
    }


    private function _buildBypassJechoReportMarkdown($options)
    {
        $targetLanguage = $this->_getJechoReportTargetLanguage($options, isset($options['languageHint']) ? $options['languageHint'] : '');
        if ($targetLanguage == 'en')
        {
            return "# [TEST BYPASS] Jecho Report\n\n_This is dummy markdown returned because AI_RESUME_PARSER_TEST_BYPASS is enabled. No OpenAI call was made._\n\n- Name: Test Bypass\n- Title: Senior Software Engineer\n- Skills: PHP, JavaScript, MySQL\n";
        }

        return "# [TEST BYPASS] Jecho 報告\n\n_此為 dummy 內容，因為 AI_RESUME_PARSER_TEST_BYPASS 為開啟狀態，未呼叫 OpenAI。_\n\n- 姓名：測試 旁路\n- 職稱：資深軟體工程師\n- 技能：PHP、JavaScript、MySQL\n";
    }


    private function _buildBypassFakeResponse()
    {
        return array(
            'model' => defined('OPENAI_MODEL') ? OPENAI_MODEL : 'test-bypass',
            'usage' => array(
                'input_tokens' => 0,
                'output_tokens' => 0
            )
        );
    }


    private function _buildRequestPayload($resumeText, $options)
    {
        $fileName = isset($options['fileName']) ? trim($options['fileName']) : '';
        $sourceType = isset($options['sourceType']) ? trim($options['sourceType']) : self::DEFAULT_SOURCE_TYPE;
        $languageHint = isset($options['languageHint']) ? trim($options['languageHint']) : '';
        $parseMode = $this->_getParseMode($options);
        $isFastParse = ($parseMode == 'fast');
        $includeJechoReport = !$isFastParse && $this->_shouldIncludeJechoReport($options);
        $targetLanguage = $this->_getJechoReportTargetLanguage($options, $languageHint);

        $instructions = array();
        $instructions[] = 'You extract structured candidate data from resumes for an ATS.';
        $instructions[] = 'Return only data that can be supported by the resume text.';
        $instructions[] = 'Normalize titles, functions, and skills into concise canonical English values.';
        $instructions[] = 'Also provide Chinese equivalents when they are clear.';
        $instructions[] = 'Keep key_skills_en and key_skills_zh to the most important 5 to 10 items.';
        $instructions[] = 'Do not invent contact details.';
        $instructions[] = 'If something is unknown, return an empty string or empty array.';
        $instructions[] = 'phone: Output using only digits, parentheses, and # (no +, -, spaces, or dots). Use (country_code) prefix when a country code is present, e.g. +886-912-345-678 → (886)912345678, 02-1234-5678 → (02)12345678, ext 123 → (02)12345678#123.';
        $instructions[] = 'website, linkedin, github, facebook, googleplus, twitter, cakeresume, link1, link2, link3: Always output as a full URL starting with https:// or http://. If only a bare domain or path is found (e.g. linkedin.com/in/foo), prepend https://. Use link1/link2/link3 for any additional profile or portfolio URLs that do not fit the named fields.';
        $instructions[] = 'job_level must be one of: intern, junior, mid, senior, staff, principal, lead, manager, director, vp, c_level, or empty.';
        $instructions[] = 'function_en should describe the job function, not simply repeat the raw title.';
        if ($isFastParse)
        {
            $instructions[] = 'Fast import mode: Extract only structured candidate fields. Set career_summary, skill_summary, and jecho_report_markdown to empty strings. Do not write summaries or JECHO report content.';
        }
        else
        {
            $instructions[] = 'career_summary: Write 3 to 5 bullet points (using "- " prefix) in Traditional Chinese summarizing the candidate\'s professional background, key experience, and strengths. Each bullet should be one concise sentence.';
            $instructions[] = 'skill_summary: Write a bullet list (using "- " prefix) in Traditional Chinese listing the candidate\'s skills grouped or ordered by importance. Include technical skills, tools, and soft skills where applicable.';
        }
        if ($includeJechoReport)
        {
            $instructions[] = 'Also populate jecho_report_markdown with the final JECHO Markdown report in the requested language.';
            $instructions[] = 'Do not wrap jecho_report_markdown in code fences.';
        }
        else
        {
            $instructions[] = 'Set jecho_report_markdown to an empty string.';
        }

        $input = "Resume source type: " . $sourceType . "\n";
        if ($fileName != '')
        {
            $input .= "Original file name: " . $this->_sanitizePromptLine($fileName) . "\n";
        }
        if ($languageHint != '')
        {
            $input .= "Language hint: " . $languageHint . "\n";
        }
        if ($includeJechoReport)
        {
            $input .= "Target JECHO report language: " . $targetLanguage . "\n";
            $input .= "\nFollow these exact JECHO report rules and template when populating jecho_report_markdown:\n\n";
            $input .= $this->_getJechoReportPrompt($targetLanguage);
            $input .= "\n";
        }
        $input .= "\nResume text:\n" . $resumeText;

        return array(
            'model' => $this->_getModelForOptions($options),
            'instructions' => implode("\n", $instructions),
            'input' => $input,
            'max_output_tokens' => $isFastParse ? 1500 : ($includeJechoReport ? 9000 : 2500),
            'text' => array(
                'format' => array(
                    'type' => 'json_schema',
                    'name' => 'resume_parse_result',
                    'strict' => false,
                    'schema' => $this->_getResponseSchema()
                )
            )
        );
    }


    private function _buildJechoReportPayload($resumeText, $options)
    {
        $targetLanguage = $this->_getJechoReportTargetLanguage($options);

        $fileName = isset($options['fileName']) ? trim($options['fileName']) : '';
        $candidateName = isset($options['candidateName']) ? trim($options['candidateName']) : '';

        $instructions = array();
        $instructions[] = 'You transform resume text into a JECHO company resume report in Markdown.';
        $instructions[] = 'Return only the final Markdown document.';
        $instructions[] = 'Do not wrap the output in code fences.';
        $instructions[] = 'Keep all fixed template labels, HTML tags, table separators, and footer text exactly as provided in the template.';
        $instructions[] = 'Replace only the placeholder text with resume-derived content.';
        $instructions[] = 'All timelines must be ordered from newest to oldest.';
        $instructions[] = 'Do not omit resume information. Rewrite only to improve fluency and structure.';
        $instructions[] = 'If a section needs more entries, expand the template while preserving the same format.';

        $input = "Target language: " . $targetLanguage . "\n";
        if ($candidateName != '')
        {
            $input .= "Candidate name: " . $this->_sanitizePromptLine($candidateName) . "\n";
        }
        if ($fileName != '')
        {
            $input .= "Original file name: " . $this->_sanitizePromptLine($fileName) . "\n";
        }
        $input .= "\nFollow these exact JECHO report rules and template:\n\n";
        $input .= $this->_getJechoReportPrompt($targetLanguage);
        $input .= "\n\nResume text:\n" . $resumeText;

        return array(
            'model' => OPENAI_MODEL,
            'instructions' => implode("\n", $instructions),
            'input' => $input,
            'max_output_tokens' => 7000
        );
    }


    private function _shouldIncludeJechoReport($options)
    {
        return !empty($options['includeJechoReport']);
    }


    private function _getParseMode($options)
    {
        $parseMode = isset($options['parseMode']) ? strtolower(trim($options['parseMode'])) : '';
        if ($parseMode == 'fast' || $parseMode == 'quick')
        {
            return 'fast';
        }

        return 'full';
    }


    private function _getModelForOptions($options)
    {
        if ($this->_getParseMode($options) == 'fast' &&
            defined('OPENAI_FAST_MODEL') && trim((string) OPENAI_FAST_MODEL) != '')
        {
            return OPENAI_FAST_MODEL;
        }

        return OPENAI_MODEL;
    }


    private function _getJechoReportTargetLanguage($options, $languageHint = '')
    {
        $targetLanguage = isset($options['targetLanguage']) ? strtolower(trim($options['targetLanguage'])) : '';
        if ($targetLanguage == 'zh' || $targetLanguage == 'en')
        {
            return $targetLanguage;
        }

        $languageHint = strtolower(trim($languageHint));
        if ($languageHint == 'en')
        {
            return 'en';
        }

        return 'zh';
    }


    private function _getJechoReportPrompt($targetLanguage)
    {
        if ($targetLanguage == 'en')
        {
            return <<<'EOT'
Execution order
Read all instructions below first to understand the framework, then generate the Markdown resume output from the provided resume text.
For all seven sections such as career summary and employment history, order entries from most recent to oldest.
Faithfully incorporate all source resume details into the template. Do not delete any job content. You may only improve fluency, integrate related points, or rephrase. If content is long, split it into multiple paragraphs or bullets instead of simplifying it.

Framework
Format the output in Markdown using exactly these seven sections:
Personal Information
Career Summary
Skill Summary
EMPLOYMENT PERIOD
Education
Projects
ADDITIONAL Remarks
If the resume is long, expand the framework as needed and keep output complete. Do not alter fixed template text, styling, HTML, or symbols.

Markdown template

<div title="watermark jecho"><img src="https://jecho.me/wp-content/uploads/jecho-logo.png" style="margin-top: 0px; margin-left: 15px;"></div>

|      |      |   CLIENT: | 一段描述 |
| ---- | ---- | :-------- | :-------------- |
|      |      | POSITION: | 一段描述 |

<div style="width: 100%; border-bottom: 1px solid #ccc; padding-top: 40px;"></div>

### 1. Personal Information

| Name | Nationality | Gender | Location |
| :---------------- | :---------- | :----- | :-------- |
| 一段描述 | 一段描述 | 一段描述 | 一段描述 |

### 2. Career Summary

- **Years in industry**: 一段描述
- **Best skill**: 一段描述
- **Greatest accomplishment**: 一段描述
- **Team attitude**: 一段描述
- **What CV's accomplishment + How he did it?**: 一段描述

### 3. Skill Summary

- 一段描述
- 一段描述
- 一段描述
- 一段描述
- 一段描述

<div style="width: 100%; border-bottom: 1px solid #ccc; margin-top: 40px;"></div>

### 4. EMPLOYMENT PERIOD

#### 一段描述 (Dates)

##### 一段描述 (Company)

###### 一段描述 (Title)

- 一段描述
- 一段描述

- **Reason for leaving**: 一段描述

#### 一段描述 (Dates)

##### 一段描述 (Company)

###### 一段描述 (Title)

- 一段描述
- 一段描述

- **Reason for leaving**: 一段描述

### 5. Education

#### 一段描述 (Years)

##### 一段描述 (School)

###### 一段描述 (Degree or Major)

### 6. Projects

##### 一段描述 (Company)

- 一段描述
- 一段描述

##### 一段描述 (Company)

- 一段描述
- 一段描述

### 7. ADDITIONAL Remarks

- <span style="float: left; color: gray; border: 1px solid #ccc; padding: 0px 8px; border-radius: 6px;">Include explanations for employment gaps exceeding three months. If one paragraph is insufficient, automatically add more to match the number of gaps.</span>
- 一段描述

<div style="width: 100%; border-bottom: 1px solid #ccc; margin-top: 40px;"></div>

<div style="
     background: #f5f5f5;
     margin-top: 30px;
     padding: 10px 20px;
     color: #999;
     text-align: justify;">
JECHO - <a href="https://jecho.me/">https://jecho.me/</a> - Specializing in IT industry recruitment and career consultation. This information is written by Jecho and no part may be reproduced without the prior permission. No. 309, Jilin Rd., Jhongshan Dist., Taipei City, Taiwan. Email: <a href="mailto:jobs@jecho.me">jobs@jecho.me</a> Tel: (02) 6617-2566
</div>
EOT;
        }

        return <<<'EOT'
執行指令的順序
先讀以下所有指令，了解紀錄框架，然後根據提供的履歷文字直接產出符合框架的 Markdown 檔案內容。
不管是職涯摘要、工作經歷等七個點，時間軸都必須由最新到最舊。
請如實將素材履歷文字補充到套版工作內容內，請勿刪減任何資料。只可以讓內容變通順、整合與換句話說，但不能刪掉任何工作內容描述。如果內容過長，請分段產出，但請勿自動簡化內容。

框架說明
請根據以下框架執行 Markdown 檔案格式化，必須符合以下架構：
個人資料
職涯摘要
技能專長
工作經歷
Project
教育經歷
備註事項
ADDITIONAL Remarks
當履歷資料過長時，允許自動擴展框架，以完整呈現所有內容。模板內的既定文字及符號不能刪除或修改。

Markdown 模板

<div title="watermark jecho"><img src="https://jecho.me/wp-content/uploads/jecho-logo.png" style="margin-top: 0px; margin-left: 15px;"></div>

|      |      | 客戶名稱： | 一段說明 |
| ---- | ---- | :-------- | :-------------- |
|      |      | 應徵職位： | 一段說明 |

<div style="width: 100%; border-bottom: 1px solid #ccc; padding-top: 40px;"></div>

### 個人資料

| 姓名 | 國籍 | 性別 | 居住地 |
| :---------------- | :---------- | :----- | :-------- |
| 一段描述 | 一段描述 | 一段描述 | 一段描述 |

### 職涯摘要

- 一段描述
- 一段描述
- 一段描述
- 一段描述
- 一段描述

職涯摘要請直接輸出第三人稱內容，不要顯示 Years in industry、Best skill、Greatest accomplishment、Team attitude、What CV's accomplishment + How he did it? 這些提示文字。

### 技能專長

- 一段描述
- 一段描述
- 一段描述
- 一段描述
- 一段描述

<div style="width: 100%; border-bottom: 1px solid #ccc; margin-top: 40px;"></div>

### 工作經歷

#### 一段描述(日期)

##### 一段描述(公司)

###### 一段描述(Title)

- 一段描述
- 一段描述

- **Reason for leaving**: 一段描述

#### 一段描述(日期)

##### 一段描述(公司)

###### 一段描述(Title)

- 一段描述
- 一段描述

- **Reason for leaving**: 一段描述

### Project

##### 一段描述(公司)

- 一段描述
- 一段描述

##### 一段描述(公司)

- 一段描述
- 一段描述

### 教育經歷

#### 一段描述(年份)

##### 一段描述(學校)

###### 一段描述(學位或科系)

### 備註事項

- 一段描述

### ADDITIONAL Remarks

- <span style="float: left; color: gray; border: 1px solid #ccc; padding: 0px 8px; border-radius: 6px;">這裡的時間請幫我抓他工作間空窗超過三個月的，若一段描述不夠，請自動增加至等於空窗數量的段落</span>
- 一段描述

<div style="width: 100%; border-bottom: 1px solid #ccc; margin-top: 40px;"></div>

<div style="
     background: #f5f5f5;
     margin-top: 30px;
     padding: 10px 20px;
     color: #999;
     text-align: justify;">
JECHO - <a href="https://jecho.me/">https://jecho.me/</a> - Specializing in IT industry recruitment and career consultation. This information is written by Jecho and no part may be reproduced without the prior permission. No. 309, Jilin Rd., Jhongshan Dist., Taipei City, Taiwan. Email: <a href="mailto:jobs@jecho.me">jobs@jecho.me</a> Tel: (02) 6617-2566
</div>
EOT;
    }


    private function _getResponseSchema()
    {
        return array(
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => array(
                'candidate' => array(
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => array(
                        'first_name' => array('type' => 'string'),
                        'last_name' => array('type' => 'string'),
                        'chinese_name' => array('type' => 'string'),
                        'email' => array('type' => 'string'),
                        'phone' => array('type' => 'string'),
                        'address' => array('type' => 'string'),
                        'city' => array('type' => 'string'),
                        'state' => array('type' => 'string'),
                        'zip_code' => array('type' => 'string'),
                        'current_employer' => array('type' => 'string'),
                        'job_title_raw' => array('type' => 'string'),
                        'job_title_zh' => array('type' => 'string'),
                        'job_title_en' => array('type' => 'string'),
                        'job_title_canonical_key' => array('type' => 'string'),
                        'function_raw' => array('type' => 'string'),
                        'function_zh' => array('type' => 'string'),
                        'function_en' => array('type' => 'string'),
                        'function_canonical_key' => array('type' => 'string'),
                        'job_level' => array('type' => 'string'),
                        'website' => array('type' => 'string'),
                        'linkedin' => array('type' => 'string'),
                        'github' => array('type' => 'string'),
                        'facebook' => array('type' => 'string'),
                        'googleplus' => array('type' => 'string'),
                        'twitter' => array('type' => 'string'),
                        'cakeresume' => array('type' => 'string'),
                        'link1' => array('type' => 'string'),
                        'link2' => array('type' => 'string'),
                        'link3' => array('type' => 'string'),
                        'highest_degree' => array('type' => 'string'),
                        'major' => array('type' => 'string'),
                        'skills_raw' => array(
                            'type' => 'array',
                            'items' => array('type' => 'string')
                        ),
                        'skills_zh' => array(
                            'type' => 'array',
                            'items' => array('type' => 'string')
                        ),
                        'skills_en' => array(
                            'type' => 'array',
                            'items' => array('type' => 'string')
                        ),
                        'key_skills_zh' => array(
                            'type' => 'array',
                            'items' => array('type' => 'string')
                        ),
                        'key_skills_en' => array(
                            'type' => 'array',
                            'items' => array('type' => 'string')
                        ),
                        'career_summary' => array('type' => 'string'),
                        'skill_summary' => array('type' => 'string')
                    ),
                    'required' => array(
                        'first_name', 'last_name', 'chinese_name', 'email', 'phone', 'address',
                        'city', 'state', 'zip_code', 'current_employer',
                        'job_title_raw', 'job_title_zh', 'job_title_en',
                        'job_title_canonical_key', 'function_raw', 'function_zh',
                        'function_en', 'function_canonical_key', 'job_level',
                        'website', 'linkedin', 'github', 'facebook', 'googleplus',
                        'twitter', 'cakeresume', 'link1', 'link2', 'link3',
                        'highest_degree', 'major',
                        'skills_raw', 'skills_zh', 'skills_en',
                        'key_skills_zh', 'key_skills_en',
                        'career_summary', 'skill_summary'
                    )
                ),
                'normalization' => array(
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => array(
                        'job_title_confidence' => array('type' => 'number'),
                        'function_confidence' => array('type' => 'number'),
                        'job_level_confidence' => array('type' => 'number'),
                        'skills_confidence' => array('type' => 'number')
                    ),
                    'required' => array(
                        'job_title_confidence', 'function_confidence',
                        'job_level_confidence', 'skills_confidence'
                    )
                ),
                'jecho_report_markdown' => array('type' => 'string')
            ),
            'required' => array('candidate', 'normalization', 'jecho_report_markdown')
        );
    }


    private function _normalizeResult($result, $options)
    {
        if (!isset($result['candidate']) || !is_array($result['candidate']))
        {
            $result['candidate'] = array();
        }
        if (!isset($result['normalization']) || !is_array($result['normalization']))
        {
            $result['normalization'] = array();
        }

        $candidateDefaults = array(
            'first_name' => '',
            'last_name' => '',
            'chinese_name' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'city' => '',
            'state' => '',
            'zip_code' => '',
            'current_employer' => '',
            'job_title_raw' => '',
            'job_title_zh' => '',
            'job_title_en' => '',
            'job_title_canonical_key' => '',
            'function_raw' => '',
            'function_zh' => '',
            'function_en' => '',
            'function_canonical_key' => '',
            'job_level' => '',
            'website' => '',
            'linkedin' => '',
            'github' => '',
            'facebook' => '',
            'googleplus' => '',
            'twitter' => '',
            'cakeresume' => '',
            'link1' => '',
            'link2' => '',
            'link3' => '',
            'highest_degree' => '',
            'major' => '',
            'skills_raw' => array(),
            'skills_zh' => array(),
            'skills_en' => array(),
            'key_skills_zh' => array(),
            'key_skills_en' => array(),
            'career_summary' => '',
            'skill_summary' => ''
        );
        $normalizationDefaults = array(
            'job_title_confidence' => 0,
            'function_confidence' => 0,
            'job_level_confidence' => 0,
            'skills_confidence' => 0
        );

        $result['candidate'] = array_merge($candidateDefaults, $result['candidate']);
        $result['normalization'] = array_merge($normalizationDefaults, $result['normalization']);

        $arrayFields = array(
            'skills_raw',
            'skills_zh',
            'skills_en',
            'key_skills_zh',
            'key_skills_en'
        );

        foreach ($arrayFields as $fieldName)
        {
            $result['candidate'][$fieldName] = $this->_normalizeStringArray(
                $result['candidate'][$fieldName],
                ($fieldName == 'key_skills_zh' || $fieldName == 'key_skills_en') ? self::MAX_KEY_SKILLS : 50
            );
        }

        $result['candidate']['phone'] = $this->_normalizePhone(
            $result['candidate']['phone']
        );

        foreach (array('website', 'linkedin', 'github', 'facebook', 'googleplus', 'twitter', 'cakeresume', 'link1', 'link2', 'link3') as $urlField)
        {
            $result['candidate'][$urlField] = $this->_normalizeUrl(
                $result['candidate'][$urlField]
            );
        }

        $result['usage'] = array(
            'provider' => 'openai',
            'model' => isset($this->_lastResponse['model']) ? $this->_lastResponse['model'] : $this->_getModelForOptions($options),
            'input_tokens' => $this->_getUsageValue($this->_lastResponse, 'input_tokens'),
            'output_tokens' => $this->_getUsageValue($this->_lastResponse, 'output_tokens')
        );

        if (!isset($result['jecho_report_markdown']) || !is_string($result['jecho_report_markdown']))
        {
            $result['jecho_report_markdown'] = '';
        }
        else
        {
            $result['jecho_report_markdown'] = $this->_cleanMarkdownOutput($result['jecho_report_markdown']);
        }

        $result['meta'] = array(
            'source_type' => isset($options['sourceType']) ? $options['sourceType'] : self::DEFAULT_SOURCE_TYPE,
            'file_name' => isset($options['fileName']) ? $options['fileName'] : '',
            'parse_mode' => $this->_getParseMode($options),
            'jecho_report_included' => ($this->_getParseMode($options) != 'fast' && $this->_shouldIncludeJechoReport($options)) ? 1 : 0
        );

        return $result;
    }


    private function _normalizeStringArray($value, $maxItems)
    {
        $result = array();

        if (!is_array($value))
        {
            return $result;
        }

        foreach ($value as $item)
        {
            $item = trim((string) $item);
            if ($item == '')
            {
                continue;
            }
            if (in_array($item, $result))
            {
                continue;
            }
            $result[] = $item;
            if (count($result) >= $maxItems)
            {
                break;
            }
        }

        return $result;
    }


    private function _normalizeUrl($url)
    {
        $url = trim((string) $url);
        if ($url === '')
        {
            return '';
        }
        if (preg_match('/^https?:\/\//i', $url))
        {
            return $url;
        }
        return 'https://' . $url;
    }


    private function _normalizePhone($phone)
    {
        $phone = trim((string) $phone);
        if ($phone === '')
        {
            return '';
        }

        // Extract extension before stripping characters
        $ext = '';
        if (preg_match('/(?:ext|#|x)[\s.]?(\d+)/i', $phone, $m))
        {
            $ext = '#' . $m[1];
            $phone = preg_replace('/[\s.]?(?:ext|#|x)[\s.]?\d+/i', '', $phone);
        }

        // Detect and reformat leading country code (+NNN or 00NNN)
        $prefix = '';
        if (preg_match('/^\+(\d{1,4})(.*)$/', $phone, $m))
        {
            $prefix = '(' . $m[1] . ')';
            $phone  = $m[2];
        }
        elseif (preg_match('/^00(\d{1,4})(.*)$/', $phone, $m))
        {
            $prefix = '(' . $m[1] . ')';
            $phone  = $m[2];
        }

        // Keep only digits from the remaining number
        $digits = preg_replace('/[^0-9]/', '', $phone);

        if ($digits === '')
        {
            return '';
        }

        return $prefix . $digits . $ext;
    }


    private function _getUsageValue($response, $key)
    {
        if (!isset($response['usage']) || !is_array($response['usage']))
        {
            return 0;
        }

        return isset($response['usage'][$key]) ? (int) $response['usage'][$key] : 0;
    }


    private function _extractStructuredOutput($response)
    {
        if (isset($response['output_text']) && is_string($response['output_text']) && trim($response['output_text']) != '')
        {
            return trim($response['output_text']);
        }

        if (!isset($response['output']) || !is_array($response['output']))
        {
            return false;
        }

        foreach ($response['output'] as $outputItem)
        {
            if (!isset($outputItem['content']) || !is_array($outputItem['content']))
            {
                continue;
            }

            foreach ($outputItem['content'] as $contentItem)
            {
                if (isset($contentItem['text']) && is_string($contentItem['text']) && trim($contentItem['text']) != '')
                {
                    return trim($contentItem['text']);
                }
            }
        }

        return false;
    }


    private function _cleanMarkdownOutput($markdown)
    {
        $markdown = trim((string) $markdown);
        $markdown = preg_replace('/^```(?:markdown)?\s*/i', '', $markdown);
        $markdown = preg_replace('/\s*```$/', '', $markdown);

        return trim($markdown);
    }


    private function _normalizeFilenameArray($fileNames)
    {
        $normalized = array();

        if (!is_array($fileNames))
        {
            return $normalized;
        }

        foreach ($fileNames as $fileName)
        {
            $fileName = strtolower(trim((string) $fileName));
            if ($fileName == '')
            {
                continue;
            }
            if (in_array($fileName, $normalized))
            {
                continue;
            }

            $normalized[] = $fileName;
        }

        return $normalized;
    }


    private function _postJSON($url, $payload, $headers)
    {
        $jsonPayload = json_encode($payload);
        if ($jsonPayload === false)
        {
            $this->_setError('Failed to encode request payload.');
            return false;
        }

        $headers[] = 'Content-Length: ' . strlen($jsonPayload);

        if (function_exists('curl_init'))
        {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, OPENAI_TIMEOUT);

            $body = curl_exec($ch);
            $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($body === false)
            {
                $this->_setError('OpenAI request failed: ' . $curlError);
                return false;
            }
        }
        else
        {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => implode("\r\n", $headers),
                    'content' => $jsonPayload,
                    'timeout' => OPENAI_TIMEOUT,
                    'ignore_errors' => true
                )
            ));

            $body = @file_get_contents($url, false, $context);
            if ($body === false)
            {
                $this->_setError('OpenAI request failed.');
                return false;
            }

            $statusCode = 0;
            if (isset($http_response_header[0]) &&
                preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches))
            {
                $statusCode = (int) $matches[1];
            }
        }

        $response = json_decode($body, true);
        if (!is_array($response))
        {
            $this->_setError('OpenAI returned invalid JSON.');
            return false;
        }

        if ($statusCode >= 400)
        {
            $errorMessage = 'OpenAI request failed.';
            if (isset($response['error']['message']))
            {
                $errorMessage = $response['error']['message'];
            }
            $this->_setError($errorMessage);
            return false;
        }

        return $response;
    }


    private function _sanitizeFilenamePart($value)
    {
        $value = trim($value);
        if ($value == '')
        {
            return 'Candidate';
        }

        $value = preg_replace('/[\/\\\\:\*\?"<>\|]+/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }


    private function _sanitizeFilenameToken($value)
    {
        $value = $this->_sanitizeFilenamePart($value);
        $value = preg_replace('/\s+/', '_', $value);
        $value = preg_replace('/_+/', '_', $value);

        return trim($value, '_');
    }


    private function _sanitizePromptLine($value)
    {
        $value = trim((string) $value);
        if ($value == '')
        {
            return '';
        }

        $value = str_replace(array("\r", "\n", "\t"), ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }


    private function _truncateResumeText($resumeText)
    {
        $maxBytes = self::MAX_RESUME_INPUT_BYTES;
        if (strlen($resumeText) <= $maxBytes)
        {
            return $resumeText;
        }

        $resumeText = substr($resumeText, 0, $maxBytes);
        if (function_exists('mb_check_encoding') &&
            function_exists('mb_substr') &&
            !mb_check_encoding($resumeText, 'UTF-8'))
        {
            $resumeText = mb_substr($resumeText, 0, mb_strlen($resumeText, 'UTF-8') - 1, 'UTF-8');
        }

        return rtrim($resumeText) . "\n\n[Truncated due to input size limit.]";
    }


    private function _setError($message)
    {
        $this->_lastError = $message;
    }


    private function _stringifyArray($value)
    {
        if (!is_array($value))
        {
            return '';
        }

        return implode(', ', $value);
    }


    private function _requiredTablesExist()
    {
        static $hasTables = null;

        if ($hasTables !== null)
        {
            return $hasTables;
        }

        $db = DatabaseConnection::getInstance();
        $hasTables = (boolean) $db->getAssoc("SHOW TABLES LIKE 'ai_resume_parse_log'");

        return $hasTables;
    }
}

?>
