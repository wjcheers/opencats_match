<?php
/*
 * CATS
 * Asynchroneous Queue Processor
 *
 * Cleans up AI-generated Jecho draft reports after their retention window.
 */

include_once('./modules/queue/lib/Task.php');
include_once('./lib/Attachments.php');

define('JECHO_AI_REPORT_TTL_DAYS', 60);

class CleanJechoAIReports extends Task
{
    public function getSchedule()
    {
        return '0 * * * *';
    }

    public function run($siteID, $args)
    {
        Task::setName('Clean Jecho AI Reports');
        Task::setDescription('Delete Jecho AI draft reports older than 60 days.');

        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                attachment_id AS attachmentID,
                site_id AS siteID
             FROM
                attachment
             WHERE
                (
                    original_filename LIKE %s ESCAPE '\\\\'
                    OR original_filename LIKE %s ESCAPE '\\\\'
                    OR title LIKE %s ESCAPE '\\\\'
                    OR title LIKE %s ESCAPE '\\\\'
                )
             AND
                site_id = %s
             AND
                DATEDIFF(NOW(), date_created) > %s",
            $db->makeQueryString('Jecho\_AI\_Report\_%'),
            $db->makeQueryString('Jecho AI Report\_%'),
            $db->makeQueryString('Jecho\_AI\_Report%'),
            $db->makeQueryString('Jecho AI Report%'),
            $db->makeQueryInteger($siteID),
            JECHO_AI_REPORT_TTL_DAYS
        );

        $attachmentsRS = $db->getAllAssoc($sql);
        if (empty($attachmentsRS))
        {
            $this->setResponse('No Jecho AI draft reports needed cleanup.');
            return TASKRET_SUCCESS_NOLOG;
        }

        $deletedCount = 0;

        foreach ($attachmentsRS as $attachmentData)
        {
            $attachmentID = (int) $attachmentData['attachmentID'];
            $attachmentSiteID = (int) $attachmentData['siteID'];
            if ($attachmentID <= 0 || $attachmentSiteID <= 0)
            {
                continue;
            }

            $attachments = new Attachments($attachmentSiteID);
            if ($attachments->delete($attachmentID))
            {
                $deletedCount++;
            }
        }

        if ($deletedCount > 0)
        {
            $this->setResponse(
                'Deleted ' . number_format($deletedCount, 0) . ' expired Jecho AI draft reports.'
            );
            return TASKRET_SUCCESS;
        }

        $this->setResponse('Jecho AI draft cleanup found candidates but deleted nothing.');
        return TASKRET_SUCCESS_NOLOG;
    }
}
