<?php
declare(strict_types=1);

namespace Mgrn\Scoperender;

/**
 * A basic class offering escaping for html or javascript. Extend this class for more features and
 * extend or replace the pattern classes to add or change how escaping is to be handled, to add, remove or change
 * how you wish to change the target escape patterns.
 */
class BasicEscaper implements EscaperInterface
{

    protected EscapePatternsAbstract $htmlPatterns;
    protected EscapePatternsAbstract $jsPatterns;
    protected string $defaultEncoding;

    public function __construct(string $defaultEncoding = 'UTF-8')
    {
        $this->defaultEncoding = $defaultEncoding;
        $this->htmlPatterns = new BasicEscapeHtmlPatterns();
        $this->jsPatterns = new BasicEscapeJsPatterns();
    }

    /**
     * @param string|int|float $value The value to escape
     * @param bool $escapeInsertedEscapes Set to true when doing things like escaping identifiers that are already escaped.
     * @param array|null $additionalFormatterPatterns Additional patterns you wish to use in escaping.
     * @return string
     * @throws Exception
     */
    public function escapeJavascript(string|int|float $value, bool $escapeInsertedEscapes = false, ?array $additionalFormatterPatterns = null): string
    {
        $encoding = $encoding ?? $this->defaultEncoding;
        $result = '';
        if (!in_array(strtolower($encoding), ['utf-8', 'utf8', 'ascii'])) {
            throw new Exception('The javascript escaping library does not yet support mb_string');
        }
        $value = (string)$value; // Ensure stringiness
        $value = $this->noNullByte($value, $encoding); // Remove those null bytes
        $patterns = $this->jsPatterns->toArray();
        if ($additionalFormatterPatterns) {
            foreach ($additionalFormatterPatterns as $additionalPattern) {
                if (is_string($additionalPattern) && @preg_match($additionalPattern, '') !== false) {
                    $patterns[] = $additionalPattern;
                }
            }
        }

        $keysElements = [];
        $elements = [];
        foreach ($patterns as $pattern) {
            while (preg_match($pattern, $value, $match)) {
                $foundAt = strpos($value, $match[0]);
                $elements[] = substr($value, 0, $foundAt);
                end($elements);
                $keysElements[key($elements)] = $match[0];
                $value = substr($value, $foundAt + strlen($match[0]));
            }
            $elements[] = $value;
            $escaper = $escapeInsertedEscapes ? '\\\\' : '\\';
            foreach ($keysElements as $key => $value) {
                $keysElements[$key] = $escaper . $value; // escape
            }
            end($elements);
            $keysElements[key($elements)] = '';

            foreach ($elements as $key => $value) {
                $result .= $value . $keysElements[$key];
            }
        }

        return $result;
    }

    protected function noNullByte(string $passed, ?string $encoding = null): string
    {
        $encoding = $encoding ?? $this->defaultEncoding;
        return $this->mbReplace("\0", '', $passed, $encoding);
    }

    /**
     * Replace all occurrences of the search string with the replacement string. Multibyte safe.
     *
     * Grabbed from https://stackoverflow.com/questions/3489495/mb-str-replace-is-slow-any-alternatives
     *
     * @param string $search The value being searched for, otherwise known as the needle. An array may be used to designate multiple needles.
     * @param string $replace The replacement value that replaces found search values. An array may be used to designate multiple replacements.
     * @param string $subject The string or array being searched and replaced on, otherwise known as the haystack.
     *                              If subject is an array, then the search and replace is performed with every entry of subject, and the return value is an array as well.
     * @param string $encoding The encoding parameter is the character encoding. If it is omitted, the internal character encoding value will be used.
     * @param int $count If passed, this will be set to the number of replacements performed.
     * @return string
     */
    protected function mbReplace(string $search, string $replace, string $subject, string $encoding, int &$count = 0): string
    {
        $search_len = mb_strlen($search, $encoding);
        $sb = [];
        while (($offset = mb_strpos($subject, $search, 0, $encoding)) !== false) {
            $sb[] = mb_substr($subject, 0, $offset, $encoding);
            $subject = mb_substr($subject, $offset + $search_len, null, $encoding);
            ++$count;
        }
        $sb[] = $subject;
        $subject = implode($replace, $sb);
        return $subject;
    }

    // region HTML encoding

    /**
     * @param string|int|float $value value to encode
     * @param bool $encodeAll whole string is htmlencoded
     * @param bool $preserveFormatters valid text formatting elements are preserved unchanged, unless all encoded
     * @param array|null $additionalFormatterPatterns valid text formatting elements are removed entirely, and all remaining text is htmlencoded, unless all encoded
     * @param string|null $encoding
     * @return string
     */
    public function escapeHtml(string|int|float $value, bool $encodeAll = false, bool $preserveFormatters = false, ?array $additionalFormatterPatterns = null, ?string $encoding = null): string
    {
        $encoding = $encoding ?? $this->defaultEncoding;
        if (!in_array(strtolower($encoding), ['utf-8', 'utf8', 'ascii'])) {
            $encodeAll = true; // We're just not there yet. All my patterns are PCRE, and mb_ is ereg
        }

        $result = '';

        $patterns = $this->htmlPatterns->toArray();
        if ($additionalFormatterPatterns) {
            foreach ($additionalFormatterPatterns as $additionalPattern) {
                if (is_string($additionalPattern) && @preg_match($additionalPattern, '') !== false) {
                    $patterns[] = $additionalPattern;
                }
            }
        }
        $value = (string)$value; // Ensure stringiness
        $value = $this->noNullByte($value, $encoding); // Remove those null bytes
        if ($encodeAll) {
            $result = $this->encodeAll($value, $encoding);
        } elseif ($preserveFormatters) { // Preserve the formatters. Format around them
            $keysElements = array();
            $elements = array();

            $matched = true;
            while ($matched) {
                $matchArray = array();
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $value, $match)) {
                        $foundAt = strpos($value, $match[0]);
                        $matchArray[$foundAt] = $match;
                    }
                }
                if (count($matchArray) > 0) {
                    ksort($matchArray);
                    $match = reset($matchArray); // use the first match found in the string
                    $foundAt = strpos($value, $match[0]);
                    $elements[] = substr($value, 0, $foundAt);
                    end($elements);
                    $keysElements[key($elements)] = $match[0];
                    $value = substr($value, $foundAt + strlen($match[0]));
                } else {
                    $matched = false;
                }
            }
            $elements[] = $value;
            end($elements);
            $keysElements[key($elements)] = '';
            foreach ($elements as $key => $value) {
                $elements[$key] = $this->encodeAll($value, $encoding);
            }
            foreach ($elements as $key => $value) {
                $result .= $value . $keysElements[$key];
            }
        } else { // strip out the formatters.
            foreach ($patterns as $pattern) {
                $value = preg_replace($pattern, '', $value);
            }
            $result = $this->encodeAll($value, $encoding);
        }

        return $result;
    }

    /**
     * An attempt at escaping everything while respecting encoding
     *
     * @param string $value
     * @param string|null $encoding
     * @return string
     */
    protected function encodeAll(string $value, ?string $encoding = null): string
    {
        $encoding = $encoding ?? $this->defaultEncoding;
        $result = htmlentities($value, ENT_QUOTES | ENT_SUBSTITUTE, $encoding);
        if ($result == '' && $value != '') {
            $len = mb_strlen($value, $encoding);
            for ($i = 0; $i < $len; $i++) {
                $char = mb_substr($value, $i, 1, $encoding);
                if (mb_check_encoding($char, $encoding)) {
                    $result .= htmlentities($char, ENT_QUOTES, $encoding);
                } else {
                    $attempt = mb_convert_encoding($char, $encoding);
                    $result .= htmlentities($attempt, ENT_QUOTES, $encoding);
                }
            }
        }
        return $result;
    }
    // endregion

}