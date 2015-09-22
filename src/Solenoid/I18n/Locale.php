<?php
namespace Solenoid\I18n;

class Locale
{
    //Locale = language_REGION
    const DEFAULT_LOCALE = 'en';

    protected static $_filters = array(
        'language' => array('filter' => 'strtolower'),
        'script' => array('filter' => array('strtolower', 'ucfirst')),
        'territory' => array('filter' => 'strtoupper'),
        'variant' => array('filter' => 'strtoupper')
    );

    private static $_browserLocales;
    private static $_environmentLocale;
    private static $_locale;

    public function __toString()
    {
        return self::toString();
    }

    public static function getBrowserLocales()
    {
        if (self::$_browserLocales !== null) {
            return self::$_browserLocales;
        }

        $regex  = '(?P<locale>[\w\-]+)+(?:;q=(?P<quality>[0-9]+\.[0-9]+))?';
        $result = array();

        $httpLanguages = getenv('HTTP_ACCEPT_LANGUAGE');

        if (empty($httpLanguages)) {
            if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
                $httpLanguages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            } else {
                return $result;
            }
        }

        foreach (explode(',', $httpLanguages) as $language) {
            if (preg_match("/{$regex}/", $language, $matches)) {
                $quality = isset($matches['quality']) ? $matches['quality'] : 1;
                $result[self::_canonicalize($matches['locale'])] = $quality;
            }
        }

        arsort($result);
        $result = array_keys($result);
        self::$_browserLocales = $result;
        return $result;
    }

    public static function getEnvironmentLocale()
    {
        if (self::$_environmentLocale !== null) {
            return self::$_environmentLocale;
        }

        $regex = '(?P<locale>[\w\_]+)(\.|@|$)+';
        $result = array();

        $value = setlocale(LC_ALL, 0);

        if ($value != 'C' && $value != 'POSIX' && preg_match("/{$regex}/", $value, $matches)) {
            $result = (array) $matches['locale'];

            // TODO: Add region handle
        }

        self::$_environmentLocale = $result;
        return $result;
    }

    public static function getLanguage($locale = null)
    {
        if (!isset($locale)) {
            $locale = self::getLocale();
        }

        $locale = explode('_', $locale);
        return $locale[0];
    }

    public static function setLocale($value)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException(
                'Invalid type for setLocale function');
        }

        self::$_locale = self::_canonicalize($value);
    }

    public static function getLocale()
    {
        if (!isset(self::$_locale)) {
            self::$_locale = self::getPreferedLocale();
        }

        return self::$_locale;
    }

    public static function getPreferedLocale($locale = null)
    {
        if ($locale instanceof self) {
            $locale = $locale->toString();
        }

        if ($locale === 'browser') {
            $locale = self::getBrowserLocales();
        }

        if ($locale === 'environment') {
            $locale = self::getEnvironmentLocale();
        }

        if (($locale === 'auto') or ($locale === null)) {
            $locale = self::getBrowserLocales();
            $locale += self::getEnvironmentLocale();
        }

        if (is_array($locale) === true) {
            reset($locale);
            $locale = current($locale);
        }

        if ($locale === null || trim($locale) == '') {
            $locale = self::DEFAULT_LOCALE;
        }

        $locale = self::_canonicalize($locale);
        return (string)$locale;
    }

    public static function getRegion($locale = null)
    {
        if (!isset($locale)) {
            $locale = self::getLocale();
        }

        $locale = explode('_', strtoupper($locale));

        if (isset($locale[1]) === true) {
            return $locale[1];
        }

        return $locale[0];
    }

    public static function toString()
    {
        return (string)self::getLocale();
    }

    private static function _canonicalize($locale)
    {
        if (empty($locale) || $locale == '') {
            return null;
        }

        $regex  = '(?P<language>[a-z]{2,3})(?:[_-](?P<script>[a-z]{4}))?(?:[_-](?P<territory>[a-z]{2}))?(?:[_-](?P<variant>[a-z]{5,}))?';

        if (!preg_match("/^{$regex}$/i", $locale, $matches)) {
            throw new \InvalidArgumentException('Locale "'.$locale.'" could not be parsed');
        }

        $tags = array_filter(array_intersect_key($matches, static::$_filters));

        foreach ($tags as $name => &$tag) {
            foreach ((array)static::$_filters[$name]['filter'] as $filter) {
                $tag = $filter($tag);
            }
        }

        $result = array();

        foreach (static::$_filters as $name => $value) {
            if (isset($tags[$name])) {
                $result[] = $tags[$name];
            }
        }

        if ($result) {
            return implode('_', $result);
        }

        return $result;
    }
}
