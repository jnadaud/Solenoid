<?php
namespace Solenoid\I18n;

use \Solenoid\I18n\Locale;

class I18n
{
    private $_resourcesDirectory;

    public function __construct($resourcesDirectory = null)
    {
        if (isset($resourcesDirectory)) {
            $this->setResourcesDirectory($resourcesDirectory);
        }
    }

    public function getAddressFormat($locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        return $this->getAddressFormatByCountry(Locale::getRegion($locale));
    }

    public function getAddressFormatByCountry($country)
    {
        $resource = $this->getResource('supplemental_data');

        if (isset($resource['addresses'][strtoupper($country)]['addressFormat'])) {
            return $resource['addresses'][strtoupper($country)]['addressFormat'];
        }

        return null;
    }

    public function getCountryByCode($code, $locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $resource = $this->getResource($locale);

        if (isset($resource['countries'][strtoupper($code)])) {
            return $resource['countries'][strtoupper($code)];
        }

        return null;
    }

    public function getCountriesPairs($locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $resource = $this->getResource($locale);

        if (isset($resource['countries'])) {
            return $resource['countries'];
        }

        return array();
    }

    public function getCurrenciesPairs($locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $resource = $this->getResource($locale);
        $supplemental = $this->getResource('supplemental_data');
        $data = array();

        if (!isset($supplemental['currencies'])) {
            return array();
        }

        $currencies = array_unique($supplemental['currencies']);

        foreach ($currencies as $country => $currency) {
            if (isset($resource['currency'][$currency]['name'])) {
                $data[$currency] =
                    ucwords($resource['currency'][$currency]['name']);
            }
        }

        asort($data);
        return $data;
    }

    public function getCurrencyByCountry($country)
    {
        $resource = $this->getResource('supplemental_data');

        if (isset($resource['currencies'][strtoupper($country)])) {
            return $resource['currencies'][strtoupper($country)];
        }

        return null;
    }

    public function getCurrencyFormatByLocale($locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $resource = $this->getResource($locale);

        if (isset($resource['currencyFormat'])) {
            return $resource['currencyFormat'];
        }

        return null;
    }

    public function getCurrencyIso($locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        return $this->getCurrencyByCountry(Locale::getRegion($locale));
    }

    public function getCurrencySymbol($currency = null, $locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        if (!isset($currency)) {
            $currency = $this->getCurrencyByCountry(
                Locale::getRegion($locale));
        }

        $resource = $this->getResource($locale);

        if (isset($resource['currency'][$currency]['symbol'])) {
            return $resource['currency'][$currency]['symbol'];
        }

        return null;
    }

    public function getDateFormat($type = 'medium', $locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $resource = $this->getResource($locale);

        if (isset($resource['dateFormats'][strtolower($type)])) {
            return $resource['dateFormats'][strtolower($type)];
        }

        return null;
    }

    public function getDateTimeFormat($locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $resource = $this->getResource($locale);

        if (isset($resource['dateTimeFormat'])) {
            return $resource['dateTimeFormat'];
        }

        return null;
    }

    public function getDecimalFormat($locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $resource = $this->getResource($locale);

        if (isset($resource['decimalFormat'])) {
            return $resource['decimalFormat'];
        }

        return null;
    }

    public function getMonthName($month, $type = 'wide', $locale = null,
        $standAlone = false)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $resource = $this->getResource($locale);

        if ($standAlone) {
            return isset($resource['monthNamesSA'][strtolower($type)][(int)$month])
                ? $resource['monthNamesSA'][strtolower($type)][(int)$month]
                : $resource['monthNames'][strtolower($type)][(int)$month];
        } else {
            return isset($resource['monthNames'][strtolower($type)][(int)$month])
                ? $resource['monthNames'][strtolower($type)][(int)$month]
                : $resource['monthNamesSA'][strtolower($type)][(int)$month];
        }
    }

    public function getMonthNames($type = 'wide', $locale = null,
        $standAlone = false)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $resource = $this->getResource($locale);

        if ($standAlone) {
            return isset($resource['monthNamesSA'][strtolower($type)])
                ? $resource['monthNamesSA'][strtolower($type)]
                : $resource['monthNames'][strtolower($type)];
        } else {
            return isset($resource['monthNames'][strtolower($type)])
                ? $resource['monthNames'][strtolower($type)]
                : $resource['monthNamesSA'][strtolower($type)];
        }
    }

    public function getNumberSymbol($name = 'decimal', $locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $resource = $this->getResource($locale);

        if (isset($resource['symbols'][strtolower($name)])) {
            return $resource['symbols'][strtolower($name)];
        }

        return null;
    }

    public function getPercentFormat($locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $resource = $this->getResource($locale);

        if (isset($resource['percentFormat'])) {
            return $resource['percentFormat'];
        }

        return null;
    }

    public function getPostalCodeRegex($locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        return $this->getPostalCodeRegexByCountry(Locale::getRegion($locale));
    }

    public function getPostalCodeRegexByCountry($country)
    {
        $resource = $this->getResource('supplemental_data');

        if (isset($resource['addresses'][strtoupper($country)]['postCodeRegex'])) {
            return $resource['addresses'][strtoupper($country)]['postCodeRegex'];
        }

        return null;
    }

    public function getResource($locale)
    {
        if (!@filemtime($this->getResourcesDirectory().strtolower($locale)
            .'.php')) {
            throw new \RunTimeException('Required locale "'.$locale
                .'" does not exist in resource directory');
        }

        return require_once($this->getResourcesDirectory().strtolower($locale)
            .'.php');
    }

    public function setResourcesDirectory($value)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException(
                'Ressources directory parameter must be a string');
        }

        $this->_resourcesDirectory = $value;
    }

    public function getResourcesDirectory()
    {
        if (!isset($this->_resourcesDirectory)) {
            $this->_resourcesDirectory = __DIR__.DIRECTORY_SEPARATOR.'Data'
            .DIRECTORY_SEPARATOR.'Cldr'.DIRECTORY_SEPARATOR.'Locales';
        }

        $this->_resourcesDirectory = $this->_normalizeDirectory(
            $this->_resourcesDirectory);

        if (!is_dir($this->_resourcesDirectory)) {
            throw new \RunTimeException(
                'Ressources directory does not exist');
        }

        return $this->_resourcesDirectory;
    }

    public function getScientificFormat($locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $resource = $this->getResource($locale);

        if (isset($resource['scientificFormat'])) {
            return $resource['scientificFormat'];
        }

        return null;
    }

    public function getTimeFormat($type = 'medium', $locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $resource = $this->getResource($locale);

        if (isset($resource['timeFormats'][strtolower($type)])) {
            return $resource['timeFormats'][strtolower($type)];
        }

        return null;
    }

    public function getWeekDayName($day, $type = 'wide', $locale = null,
        $standAlone = false)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $resource = $this->getResource($locale);

        if ($standAlone) {
            return isset($resource['weekDayNamesSA'][strtolower($type)][(int)$day])
                ? $resource['weekDayNamesSA'][strtolower($type)][(int)$day]
                : $resource['weekDayNames'][strtolower($type)][(int)$day];
        } else {
            return isset($resource['weekDayNames'][strtolower($type)][(int)$day])
                ? $resource['weekDayNames'][strtolower($type)][(int)$day]
                : $resource['weekDayNamesSA'][strtolower($type)][(int)$day];
        }
    }

    public function getWeekDayNames($type = 'wide', $locale = null,
        $standAlone = false)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $resource = $this->getResource($locale);

        if ($standAlone) {
            return isset($resource['weekDayNamesSA'][strtolower($type)])
                ? $resource['weekDayNamesSA'][strtolower($type)]
                : $resource['weekDayNames'][strtolower($type)];
        } else {
            return isset($resource['weekDayNames'][strtolower($type)])
                ? $resource['weekDayNames'][strtolower($type)]
                : $resource['weekDayNamesSA'][strtolower($type)];
        }
    }

    private function _normalizeDirectory($directory)
    {
        $last = $directory[strlen($directory) - 1];

        if (in_array($last, array('/', '\\'))) {
            $directory[strlen($directory) - 1] = DIRECTORY_SEPARATOR;
            return $directory;
        }

        $directory .= DIRECTORY_SEPARATOR;
        return $directory;
    }
}
