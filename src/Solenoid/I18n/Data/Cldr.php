<?php
namespace Solenoid\I18n\Data;

class Cldr
{
    const LATEST_ZIP_CORE_URL =
        'http://www.unicode.org/Public/cldr/latest/core.zip';

    private $_repository;
    private $_resourcesDirectory;
    private $_repositoryIsInitialized;

    public function setRepository($value)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException(
                'Repository parameter must be a string');
        }

        $this->_repository = $value;
    }

    public function getRepository()
    {
        if (!isset($this->_repository)) {
            $this->_repository = __DIR__.DIRECTORY_SEPARATOR.'Cldr'
                .DIRECTORY_SEPARATOR.'Repository';
        }

        $this->_repository = $this->_normalizeDirectory($this->_repository);

        if (!is_dir($this->_repository)) {
            mkdir($this->_repository, 0777, true);
        }

        return $this->_repository;
    }

    public function setResourcesDirectory($value)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException(
                'Resources directory parameter must be a string');
        }

        $this->_resourcesDirectory = $value;
    }

    public function getResourcesDirectory()
    {
        if (!isset($this->_resourcesDirectory)) {
            $this->_resourcesDirectory = __DIR__.DIRECTORY_SEPARATOR.'Cldr'
                .DIRECTORY_SEPARATOR.'Locales';
        }

        $this->_resourcesDirectory = $this->_normalizeDirectory(
            $this->_resourcesDirectory);

        if (!is_dir($this->_resourcesDirectory)) {
            mkdir($this->_resourcesDirectory, 0777, true);
        }

        return $this->_resourcesDirectory;
    }

    public function generateMainData()
    {
        $this->initializeRepository();
        $path = $this->getRepository().'common'
            .DIRECTORY_SEPARATOR.'main';

        if (!is_file($path.DIRECTORY_SEPARATOR.'root.xml')) {
            throw new \RuntimeException(
                "Unable to find the required root.xml under CLDR 'main' "
                ."data directory.");
        }

        $files = @scandir($path);
        ksort($files);
        $root = $files[array_search('root.xml', $files)];
        unset($files[array_search('root.xml', $files)]);
        array_unshift($files, $root);

        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && !is_dir($file)) {
                $data = array();

                if (($pos = strrpos($file, '_')) !== false) {
                    $data = require($this->getResourcesDirectory()
                        .strtolower(substr($file, 0, $pos)).'.php');
                } elseif (basename($file, '.xml') != 'root') {
                    $data = require($this->getResourcesDirectory().'root.php');
                }

                $xml = simplexml_load_file($path.DIRECTORY_SEPARATOR.$file,
                    '\\Solenoid\\I18n\\Data\\CldrXMLElement');

                $this->getData($xml, 'version', $data);
                $this->getData($xml, 'languages', $data);
                $this->getData($xml, 'countries', $data);
                $this->getData($xml, 'countries', $data);
                $this->getData($xml, 'symbols', $data);
                $this->getData($xml, 'decimalFormat', $data);
                $this->getData($xml, 'scientificFormat', $data);
                $this->getData($xml, 'percentFormat', $data);
                $this->getData($xml, 'currencyFormat', $data);
                $this->getData($xml, 'dateFormats', $data);
                $this->getData($xml, 'timeFormats', $data);
                $this->getData($xml, 'dateTimeFormats', $data);
                $this->getData($xml, 'monthsNames', $data);
                $this->getData($xml, 'weekDayNames', $data);
                $this->getData($xml, 'currencySymbols', $data);
                $this->getData($xml, 'currencyNames', $data);
                //$this->getData($xml, 'months', $data);
                $data    = str_replace("\r", '', var_export($data, true));
                $locale  = basename($file, '.xml');
                $content = <<<EOD
/**
* Locale data for '$locale'.
*
* Copyright © 1991-2013 Unicode, Inc. All rights reserved.
* Distributed under the Terms of Use in http://www.unicode.org/copyright.html.
*/
return $data;
EOD;
                file_put_contents($this->getResourcesDirectory()
                    .strtolower($locale).'.php', "<?php\n".$content."\n");
            }
        }
    }

    public function generateSupplementalData()
    {
        $this->initializeRepository();
        $data = array();
        $xml = simplexml_load_file($this->getRepository().'common'
            .DIRECTORY_SEPARATOR.'supplemental'.DIRECTORY_SEPARATOR
            .'supplementalData.xml',
            '\\Solenoid\\I18n\\Data\\CldrXMLElement');
        $this->getData($xml, 'currencyCountries', $data);
        $xml = simplexml_load_file($this->getRepository().'common'
            .DIRECTORY_SEPARATOR.'supplemental'.DIRECTORY_SEPARATOR
            .'postalCodeData.xml',
            '\\Solenoid\\I18n\\Data\\CldrXMLElement');
        $this->getData($xml, 'postalCodes', $data);
        $this->getData($xml, 'addresses', $data);
        $data    = str_replace("\r", '', var_export($data, true));
        $content = <<<EOD
/**
* Supplemental data.
*
* Copyright © 1991-2013 Unicode, Inc. All rights reserved.
* Distributed under the Terms of Use in http://www.unicode.org/copyright.html.
*/
return $data;
EOD;
        file_put_contents($this->getResourcesDirectory().'supplemental_data.php',
            "<?php\n".$content."\n");
    }

    public function getData($xml, $type, &$data = null, $value = null)
    {
        if (!isset($data)) {
            $data = array();
        }

        switch ($type) {
            case 'version':
                if (preg_match('/[\d\.]+/',
                    (string)$xml->identity->version['number'], $matches)) {
                    $data['version'] = $matches[0];
                }
            case 'languages':
                foreach ($xml->xpath('/ldml/localeDisplayNames/languages/*') as $language) {
                    $name = $language->getName();

                    if (!isset($data[(string)$language->attributes()->type])) {
                        $data['languages'][(string)$language->attributes()->type] = ucfirst((string)$language);
                    }
                }

                if (isset($data['languages'])) {
                    asort($data['languages']);
                }
                break;

            case 'countries':
                foreach ($xml->xpath('/ldml/localeDisplayNames/territories/*') as $territory) {
                    $name = $territory->getName();

                    if (!is_numeric((string)$territory->attributes()->type)
                        && !isset($data[(string)$territory->attributes()->type])) {
                        $data['countries'][(string)$territory->attributes()->type] = ucfirst((string)$territory);
                    }
                }

                if (isset($data['countries'])) {
                    asort($data['countries']);
                }
                break;

            case 'symbols':
                foreach ($xml->xpath('/ldml/numbers/symbols/*') as $symbol) {
                    $name = $symbol->getName();

                    if (!isset($data[$name]) || (string)$symbol['draft']==='') {
                        $data['symbols'][$name] = (string)$symbol;
                    }
                }
                break;

            case 'decimalFormat':
                $pattern = $xml->xpath('/ldml/numbers/decimalFormats/decimalFormatLength/decimalFormat/pattern');

                if (isset($pattern[0])) {
                    $data['decimalFormat'] = (string)$pattern[0];
                }

                break;

            case 'scientificFormat':
                $pattern = $xml->xpath('/ldml/numbers/scientificFormats/scientificFormatLength/scientificFormat/pattern');

                if (isset($pattern[0])) {
                    $data['scientificFormat'] = (string)$pattern[0];
                }

                break;

            case 'percentFormat':
                $pattern = $xml->xpath('/ldml/numbers/percentFormats/percentFormatLength/percentFormat/pattern');

                if (isset($pattern[0])) {
                    $data['percentFormat'] = (string)$pattern[0];
                }

                break;

            case 'currencyFormat':
                $pattern = $xml->xpath('/ldml/numbers/currencyFormats/currencyFormatLength/currencyFormat/pattern');

                if (isset($pattern[0])) {
                    $data['currencyFormat'] = (string)$pattern[0];
                }

                break;

            case 'currencySymbols':
                $currencies = $xml->xpath('/ldml/numbers/currencies/currency');

                foreach ($currencies as $currency) {
                    if ((string)$currency->symbol != '') {
                        $data['currency'][(string)$currency['type']]['symbol'] = (string)$currency->symbol;
                    }
                }

                break;

            case 'currencyNames':
                $currencies = $xml->xpath('/ldml/numbers/currencies/currency');

                foreach ($currencies as $currency) {
                    foreach ($currency->displayName as $name) {
                        if ((string)$name != '') {
                            $attributes = $name->attributes();

                            if (count($attributes) == 0) {
                                $data['currency'][(string)$currency['type']]['name'] = (string)$name;
                            } elseif (isset($attributes['count'])) {
                                if ($attributes['count'] == 'one') {
                                    $data['currency'][(string)$currency['type']]['name-single'] =  (string)$name;
                                } elseif ($attributes['count'] == 'other') {
                                    $data['currency'][(string)$currency['type']]['name-multiple'] =  (string)$name;
                                }
                            }
                        }
                    }
                }

                break;

            case 'dateFormats':
                if (empty($value)) {
                    $value = "gregorian";
                }

                $types = $xml->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength');

                if (is_array($types)) {
                    foreach ($types as $type) {
                        $pattern = $type->xpath('dateFormat/pattern');
                        $data['dateFormats'][(string)$type['type']] = (string)$pattern[0];
                    }
                }

                break;

            case 'timeFormats':
                if (empty($value)) {
                    $value = "gregorian";
                }

                $types = $xml->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength');

                if (is_array($types)) {
                    foreach ($types as $type) {
                        $pattern = $type->xpath('timeFormat/pattern');
                        $data['timeFormats'][(string)$type['type']] = (string)$pattern[0];
                    }
                }
                break;

            case 'dateTimeFormats':
                if (empty($value)) {
                    $value = "gregorian";
                }

                $types = $xml->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/dateTimeFormatLength');

                if (is_array($types) && isset($types[0])) {
                    $pattern = $types[0]->xpath('dateTimeFormat/pattern');
                    $data['dateTimeFormat'] = (string)$pattern[0];
                }
                break;

            case 'monthsNames':
                if (empty($value)) {
                    $value = "gregorian";
                }

                $monthTypes = $xml->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'format\']/monthWidth');

                if (is_array($monthTypes)) {
                    foreach ($monthTypes as $monthType) {
                        $names = array();

                        foreach ($monthType->xpath('month') as $month) {
                            $names[(string)$month['type']] = (string)$month;
                        }

                        if ($names !== array()) {
                            $data['monthNames'][(string)$monthType['type']] = $names;
                        }
                    }
                }

                if (isset($data['monthNames']) && !isset($data['monthNames']['abbreviated'])) {
                    $data['monthNames']['abbreviated'] = $data['monthNames']['wide'];
                }

                $monthTypes = $xml->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'stand-alone\']/monthWidth');

                if (is_array($monthTypes)) {
                    foreach ($monthTypes as $monthType) {
                        $names = array();

                        foreach ($monthType->xpath('month') as $month) {
                            $names[(string)$month['type']] = (string)$month;
                        }

                        if ($names !== array()) {
                            $data['monthNamesSA'][(string)$monthType['type']] = $names;
                        }
                    }
                }

                break;

            case 'weekDayNames':
                if (empty($value)) {
                    $value = "gregorian";
                }

                static $mapping = array(
                    'sun'=>0,
                    'mon'=>1,
                    'tue'=>2,
                    'wed'=>3,
                    'thu'=>4,
                    'fri'=>5,
                    'sat'=>6,
                );

                $dayTypes = $xml->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/dayContext[@type=\'format\']/dayWidth');

                if (is_array($dayTypes)) {
                    foreach ($dayTypes as $dayType) {
                        $names = array();

                        foreach ($dayType->xpath('day') as $day) {
                            $names[$mapping[(string)$day['type']]] = (string)$day;
                        }
                        if ($names !== array()) {
                            $data['weekDayNames'][(string)$dayType['type']] = $names;
                        }
                    }
                }

                if (isset($data['weekDayNames']) && !isset($data['weekDayNames']['abbreviated'])) {
                    $data['weekDayNames']['abbreviated'] = $data['weekDayNames']['wide'];
                }

                $dayTypes = $xml->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/dayContext[@type=\'stand-alone\']/dayWidth');

                if (is_array($dayTypes)) {
                    foreach ($dayTypes as $dayType) {
                        $names = array();

                        foreach ($dayType->xpath('day') as $day) {
                            $names[$mapping[(string)$day['type']]] = (string)$day;
                        }
                        if ($names!==array()) {
                            $data['weekDayNamesSA'][(string)$dayType['type']] = $names;
                        }
                    }
                }

                break;

            case 'months':
                if (empty($value)) {
                    $value = "gregorian";
                }

                $data['month']['format']['default']          = $xml->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'format\']/default', 'choice');
                $data['month']['format']['abbreviated']      = $xml->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'format\']/monthWidth[@type=\'abbreviated\']/month', 'type');
                $data['month']['format']['narrow']           = $xml->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'format\']/monthWidth[@type=\'narrow\']/month', 'type');
                $data['month']['format']['wide']             = $xml->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'format\']/monthWidth[@type=\'wide\']/month', 'type');
                $data['month']['stand-alone']['abbreviated'] = $xml->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'stand-alone\']/monthWidth[@type=\'abbreviated\']/month', 'type');
                $data['month']['stand-alone']['narrow']      = $xml->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'stand-alone\']/monthWidth[@type=\'narrow\']/month', 'type');
                $data['month']['stand-alone']['wide']        = $xml->xpath('/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'stand-alone\']/monthWidth[@type=\'wide\']/month', 'type');
                break;

            case 'currencyCountries':
                foreach ($xml->xpath('/supplementalData/currencyData/region') as $region) {
                    if (!is_numeric((string)$region->attributes()->iso3166)) {
                        $data['currencies'][(string)$region->attributes()->iso3166] =
                        (string)$region->currency->attributes()->iso4217;
                    }
                }

                if (isset($data['currencies'])) {
                    ksort($data['currencies'], SORT_STRING);
                }
                break;

            case 'postalCodes':
                foreach ($xml->xpath('/supplementalData/postalCodeData/postCodeRegex') as $regex) {
                    $data['addresses'][(string)$regex->attributes()->territoryId]['postCodeRegex'] = (string)$regex;
                }

                break;

            case 'addresses':
                set_time_limit(0);
                ini_set('max_execution_time', 0);
                $curl = new \Solenoid\Net\Curl();
                $curl->setOptions(array(
                    CURLOPT_RETURNTRANSFER    => true,
                    CURLOPT_HEADER            => false,
                    CURLOPT_FOLLOWLOCATION    => true,
                    CURLOPT_ENCODING        => "",
                    CURLOPT_USERAGENT        => "solenoid_api",
                    CURLOPT_AUTOREFERER        => true,
                    CURLOPT_CONNECTTIMEOUT    => 120,
                    CURLOPT_TIMEOUT            => 120,
                    CURLOPT_MAXREDIRS        => 10,
                    CURLOPT_SSL_VERIFYHOST    => 0,
                    CURLOPT_SSL_VERIFYPEER    => 0
                ));

                foreach ($xml->xpath('/supplementalData/postalCodeData/postCodeRegex') as $regex) {
                    $data['addresses'][(string)$regex->attributes()->territoryId]['postCodeRegex'] = (string)$regex;
                    $result = $curl->exec('http://i18napis.appspot.com/address/data/'.(string)$regex->attributes()->territoryId);

                    if (trim($result) != '') {
                        $result = json_decode($result);

                        if (isset($result->fmt)) {
                            $data['addresses'][(string)$regex->attributes()->territoryId]['addressFormat'] = $result->fmt;
                        }
                    }
                }

                break;
        }

        return $data;
    }

    public function isRepositoryInitialized()
    {
        return (isset($this->_repositoryIsInitialized)
            && $this->_repositoryIsInitialized);
    }

    public function initializeRepository()
    {
        if ($this->isRepositoryInitialized()) {
            return true;
        }

        if (!is_file($file = $this->getRepository().DIRECTORY_SEPARATOR
            .'core.zip')) {
            $fp = fopen($file, "w");
            $curl = new \Solenoid\Net\Curl();
            $curl->setOptions(array(
                CURLOPT_FILE => $fp,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HEADER => false
            ));

            if ($curl->exec(self::LATEST_ZIP_CORE_URL) === false) {
                throw new \Exception("Failed to download from '".
                    self::LATEST_ZIP_CORE_URL."'.");
            };

            $curl->close();
            fclose($fp);
        }

        $archive = new \ZipArchive();

        if ($archive->open($file) === true) {
            $archive->extractTo($this->getRepository());
            $archive->close();
        } else {
            throw new \Exception("Failed to unzip '".$file."'.");
        }

        return ($this->_repositoryIsInitialized = true);
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
