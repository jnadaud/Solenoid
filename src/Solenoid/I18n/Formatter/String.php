<?php
namespace Solenoid\I18n\Formatter;

use \Solenoid\I18n\Locale;

class String
{
    public static function formatAddress($data, $locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $i18n = new I18n();
        return self::_formatAddress($i18n->getAddressFormat($locale), $data);
    }

    public static function formatCompactAddress($data, $locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $i18n = new I18n();
        return self::_formatAddress($i18n->getAddressFormat($locale),
            $data, ' - ');
    }

    private static function _formatAddress($pattern, $data, $separator = "\n")
    {
        /*
        N: Name (The formatting of names for this field is outside of the scope of the address elements.)
        O: Organization
        A: Address Lines (2 or 3 lines address)
        D: District (Sub-locality): smaller than a city, and could be a neighbourhood, suburb or dependent locality in the UK.
        C: City (Locality)
        S: State (Administrative Area)
        Z: ZIP Code / Postal Code
        X: Sorting code, for example, CEDEX as used in France
        n: newline
        */

        if (isset($data['organization'])) {
            $pattern = str_replace('%O', $data['organization'], $pattern);
        }

        if (isset($data['district'])) {
            $pattern = str_replace('%D', $data['district'], $pattern);
        }

        if (isset($data['name'])) {
            $pattern = str_replace('%N', $data['name'], $pattern);
        }

        if (isset($data['city'])) {
            $pattern = str_replace('%C', $data['city'], $pattern);
        }

        if (isset($data['address'])) {
            $pattern = str_replace('%A', $data['address'], $pattern);
        }

        if (isset($data['state'])) {
            $pattern = str_replace('%S', $data['state'], $pattern);
        }

        if (isset($data['zip-code'])) {
            $pattern = str_replace('%Z', $data['zip-code'], $pattern);
        }

        if (isset($data['sorting-code'])) {
            $pattern = str_replace('%X', $data['sorting-code'], $pattern);
        }

        $pattern = preg_replace('/%[A-Z]%n/', '', $pattern);
        $pattern = str_replace('%n', $separator, $pattern);

        return $pattern;
    }
}
