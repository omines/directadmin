<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Utility;

/**
 * Static helper class for various conversion operations.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Conversion
{
    /**
     * Reduces any input to an ON/OFF value.
     *
     * @param mixed $input Data to convert.
     * @param mixed $fallback Fallback to use if $input is NULL.
     * @return string Either ON or OFF.
     */
    public static function onOff($input, $fallback = false)
    {
        return filter_var(isset($input) ? $input : $fallback, FILTER_VALIDATE_BOOLEAN) ? 'ON' : 'OFF';
    }

    /**
     * Detects package/domain options that can be unlimited and sets the accordingly.
     *
     * @param array $options
     * @return array Modified array.
     */
    public static function processUnlimitedOptions(array $options)
    {
        foreach(['bandwidth', 'domainptr', 'ftp', 'mysql', 'nemailf', 'nemailml', 'nemailr', 'nemails',
                    'nsubdomains', 'quota', 'vdomains'] as $key)
        {
            $ukey = "u{$key}";
            unset($options[$ukey]);
            if(array_key_exists($key, $options) && ($options[$key] === 'unlimited' || !isset($options[$key])))
                $options[$ukey] = 'ON';
        }
        return $options;
    }

    /**
     * Processes DirectAdmin style encoded responses into a sane array.
     *
     * @param string $data
     * @return array
     */
    public static function responseToArray($data)
    {
        $unescaped = preg_replace_callback('/&#([0-9]{2})/', function($val) {
            return chr($val[1]); }, $data);
        return \GuzzleHttp\Psr7\parse_query($unescaped);
    }

    /**
     * Ensures a DA-style response element is wrapped properly as an array.
     *
     * @param mixed $result Messy input.
     * @return array Sane output.
     */
    public static function sanitizeArray($result)
    {
        if(count($result) == 1 && isset($result['list[]']))
            $result = $result['list[]'];
        return is_array($result) ? $result : [$result];
    }
}
