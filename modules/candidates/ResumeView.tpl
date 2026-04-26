<!-- NOSPACEFILTER -->
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <?php if (!empty($this->data)): ?>
            <title>
                Candidates - Preview
                <?php $this->_($this->data['firstName'] . ' ' . $this->data['lastName']); ?>
            </title>
        <?php else: ?>
            <title>Candidates - Preview (Error)</title>
        <?php endif; ?>
        <?php if (!empty($this->data['isStyledMarkdownResume'])): ?>
            <link rel="stylesheet" type="text/css" href="modules/candidates/ResumeView.css?v=<?php echo @filemtime(dirname(__FILE__) . '/ResumeView.css'); ?>" />
            <script type="text/javascript">
                function resizeResumePreviewWindow()
                {
                    if (document.body.className.indexOf('resumePreviewBody') === -1 ||
                        typeof window.resizeTo !== 'function' ||
                        typeof screen == 'undefined')
                    {
                        return;
                    }

                    var targetWidth = Math.min(1260, screen.availWidth - 40);
                    var targetHeight = Math.min(980, screen.availHeight - 40);

                    if (targetWidth > 0 && targetHeight > 0)
                    {
                        window.resizeTo(targetWidth, targetHeight);
                    }
                }

                function toggleResumeSource()
                {
                    var body = document.body;
                    var toggleButton = document.getElementById('resumeSourceToggle');
                    if (!body || !toggleButton)
                    {
                        return false;
                    }

                    if (body.className.indexOf('showRawMarkdown') !== -1)
                    {
                        body.className = body.className.replace(/\s*showRawMarkdown/g, '');
                        toggleButton.innerHTML = '原始 Markdown';
                    }
                    else
                    {
                        body.className += ' showRawMarkdown';
                        toggleButton.innerHTML = '預覽模式';
                    }

                    return false;
                }
            </script>
            <script type="text/javascript">
                if (window.addEventListener)
                {
                    window.addEventListener('load', resizeResumePreviewWindow, false);
                }
                else if (window.attachEvent)
                {
                    window.attachEvent('onload', resizeResumePreviewWindow);
                }
            </script>
        <?php endif; ?>
    </head>

    <body<?php if (!empty($this->data['isStyledMarkdownResume'])): ?> class="resumePreviewBody"<?php endif; ?>>
<?php if (!empty($this->data)): ?>

<?php if (!empty($this->data['isPDFResume']) && !empty($this->data['retrievalURL'])): ?>

<div style="padding: 0; margin: 0; background: #eef3f5;">
    <iframe
        src="<?php echo($this->data['retrievalURL']); ?>"
        style="display: block; width: 100%; height: 96vh; border: none; background: #fff;"
        title="PDF Preview"></iframe>
    <div style="padding: 10px 16px 16px; text-align: right; font-size: 12px; color: #666;">
        <a href="<?php echo($this->data['retrievalURL']); ?>" target="_blank" style="color: #00828c; text-decoration: none;">無法內嵌預覽時，改用新分頁開啟附件</a>
    </div>
</div>

<?php elseif (!empty($this->data['isStyledMarkdownResume'])): ?>

<div class="resumeToolbar">
    <div class="resumeToolbarTitle">
        <?php $this->_($this->data['originalFilename']); ?>
    </div>
    <div class="resumeToolbarActions">
        <a href="#" id="resumeSourceToggle" class="resumeToolbarButton" onclick="return toggleResumeSource();">原始 Markdown</a>
    </div>
</div>

<div class="resumePreviewShell">
    <div class="resumeRenderedView">
        <div id="write">
<?php echo($this->data['renderedHtml']); ?>
        </div>
    </div>

    <pre id="resumeRawSource" class="resumeRawSource"><?php echo htmlspecialchars($this->data['rawText'], ENT_QUOTES, 'UTF-8'); ?></pre>
</div>

<?php else: ?>

<pre style="font-size: 12px; padding: 5px;">
<?php echo($this->data['text']); ?>
</pre>

<?php endif; ?>

<?php else: ?>

<pre style="font-size: 12px; padding: 5px;">
Error: No text exists for this attachment.
</pre>

<?php endif; ?>

    </body>
</html>
