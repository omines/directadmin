<?php

/*
 * DirectAdmin API Client
 * (c) Omines Internetbureau B.V. - https://omines.nl/
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
     * @param mixed $input Data to convert
     * @param mixed $default Fallback to use if $input is NULL
     * @return string Either ON or OFF
     */
    public static function onOff($input, $default = false)
    {
        return self::toBool($input, $default) ? 'ON' : 'OFF';
    }

    /**
     * Expands a single option to its unlimited counterpart if NULL or literal 'unlimited'.
     *
     * @param array $options Array of options to process
     * @param string $key Key of the item to process
     */
    protected static function processUnlimitedOption(array &$options, $key)
    {
        $ukey = "u{$key}";
        unset($options[$ukey]);
        if (array_key_exists($key, $options) && ($options[$key] === 'unlimited' || !isset($options[$key]))) {
            $options[$ukey] = 'ON';
        }
    }

    /**
     * Detects package/domain options that can be unlimited and sets the accordingly.
     *
     * @param array $options
     * @return array Modified array
     */
    public static function processUnlimitedOptions(array $options)
    {
        foreach (['bandwidth', 'domainptr', 'ftp', 'mysql', 'nemailf', 'nemailml', 'nemailr', 'nemails',
                    'nsubdomains', 'quota', 'vdomains', ] as $key) {
            self::processUnlimitedOption($options, $key);
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
        $unescaped = preg_replace_callback('/&#([0-9]{2})/', function ($val) {
            return chr($val[1]);
        }, $data);
        return \GuzzleHttp\Psr7\parse_query($unescaped);
    }

    /**
     * Ensures a DA-style response element is wrapped properly as an array.
     *
     * @param mixed $result Messy input
     * @return array Sane output
     */
    public static function sanitizeArray($result)
    {
        if (count($result) == 1 && isset($result['list[]'])) {
            $result = $result['list[]'];
        }
        return is_array($result) ? $result : [$result];
    }

    /**
     * Converts values like ON, YES etc. to proper boolean variables.
     *
     * @param mixed $value Value to be converted
     * @param mixed $default Value to use if $value is NULL
     * @return bool
     */
    public static function toBool($value, $default = false)
    {
        return filter_var(isset($value) ? $value : $default, FILTER_VALIDATE_BOOLEAN);
    }
}
