<?php
use Pelago\Emogrifier;

/**
 * Inline CSS
 */
class InlineCSS
{
    /**
     * Inline both the embedded css, and css from an external file, into html
     *
     * @param  HTML $htmlContent
     * @param string $cssFile path and filename
     * @return HTML with inlined CSS
     */
    public static function convert($htmlContent, $cssfile)
    {
        $emog = new Emogrifier($htmlContent);

        // Apply the css file to Emogrifier
        if ($cssfile) {
            $cssFileLocation = join(DIRECTORY_SEPARATOR, array(Director::baseFolder(), $cssfile));
            $cssFileHandler = fopen($cssFileLocation, 'r');
            $css = fread($cssFileHandler, filesize($cssFileLocation));
            fclose($cssFileHandler);

            $emog->setCss($css);
        }

        return $emog->emogrify();
    }
}
