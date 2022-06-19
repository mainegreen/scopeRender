<?php
declare(strict_types=1);

namespace Mgrn\Scoperender;

/**
 * This is not meant as a full set of patterns.
 */
class BasicEscapeHtmlPatterns extends EscapePatternsAbstract
{
    const BASIC_PATTERN_IMAGES = '#<[/]?[ ]*(img)([ ]*(alt|align|border|class|dir|height|hspace|id|ismap|lang|longdesc|name|src|style|title|usemap|vspace|width)[ ]*=[\'"][\s\w ;\:\#\@\.\^\%\!\/\-\(\)]*[\'"])*[ ]*[/]{0,1}>#iSu';
    const BASIC_PATTERN_ISO = '#\&\#[\d]{1,4}\;#';
    const BASIC_PATTERN_SIMPLE_ANCHORS = '#<[/]?[ ]*(a)([ ]*(id|name|href|style|class|title)[ ]*=[\'"][\s\w ;\:\#\@\.\^\%\!\/\-]*[\'"])*[ ]*[/]{0,1}>#iSu';
    const BASIC_PATTERN_COMPLEX_ANCHORS = '#<[/]?[ ]*(a)(([ ]*((id|name|href|style|class|title|target|tabindex|accesskey)|(data-[^\t\n\f \\/>"\'=]+))[ ]*=[\'"][\s\w ;\:\#\@\.\^\%\!\/\-]*[\'"])|([ ]*(onclick|ondblclick|onmousedown|onmouseup|onmouseover|onmousemove|onmouseout|onkeypress|onkeydown|onkeyup)[ ]*=[ ]*"[^"]*"))*[ ]*[/]{0,1}>#iSu';

    public function __construct()
    {
        $reflect = new \ReflectionClass(__CLASS__);
        $constants = $reflect->getConstants();
        foreach ($constants as $name=>$value) {
            if (is_string($value) && @preg_match($value,'')!==false) {
                $this->offsetSet($name,$value);
            }
        }
    }
}