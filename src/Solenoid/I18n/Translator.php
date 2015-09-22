<?php
namespace Solenoid\I18n;

class Translator extends \Solenoid\Object\ASingleton implements
    \Solenoid\Object\ISingleton
{
    private $_locale;
    private static $_locales;
    private static $_translations;
    private $_translationsPaths;

    public function __construct()
    {
        $this->addTranslationsPath(__DIR__.DIRECTORY_SEPARATOR
            .'Translations');
    }

    public function setLocale($value)
    {
        $this->_locale = Locale::getPreferedLocale($value);
        return $this;
    }

    public function getLocale()
    {
        if (!isset($this->_locale)) {
            $this->_locale = Locale::getPreferedLocale();
        }

        return $this->_locale;
    }

    public function getLocales()
    {
        if (!isset(self::$_locales)) {
            self::$_locales = array();

            foreach ($this->getTranslationsPaths() as $path) {
                $iterator = new \DirectoryIterator($path);

                foreach ($iterator as $info) {
                    if ($info->isDot()) {
                        continue;
                    }

                    if ($info->isDir() && $info->isReadable()) {
                        if (!isset(self::$_locales[$info->getBasename()])) {
                            self::$_locales[$info->getBasename()] = array();
                        }

                        self::$_locales[$info->getBasename()][] =
                            $info->getPathname();
                    }
                }
            }
        }

        return self::$_locales;
    }

    public function setTranslations($value, $locale = null)
    {
        if (!is_array($value) && !$value instanceof \Traversable) {
            throw new \InvalidArgumentException(
                'Translations argument must be an array and traversable');
        }

        if (isset($locale) && is_string($locale)) {
            if (!isset(self::$_translations)) {
                self::$_translations = array();
            }

            self::$_translations[$locale] = $value;
        } else {
            self::$_translations = $value;
        }

        return $this;
    }

    public function getTranslations($locale = null)
    {
        if (!isset(self::$_translations)) {
            self::$_translations = array();
        }

        if (!isset($locale)) {
            $locale = $this->getLocale();
        }

        $locales = $this->getLocales();

        //If sublocale does not exist, switch for locale, ex: 'es_ES' become 'es'
        if (!in_array($locale, $locales) && strpos($locale, '_') !== false) {
            $locale = substr($locale, 0, strpos($locale, '_'));
        }

        if (!isset(self::$_translations[$locale])) {
            self::$_translations[$locale] = array();

            if (!empty($locales) && isset($locales[$locale])) {
                foreach ($locales[$locale] as $path) {
                }
            }
        }

        return self::$_translations[$locale];
    }

    public function setTranslationsPaths($paths)
    {
        if (!is_array($paths) || !$paths instanceof \Traversable) {
            throw new \InvalidArgumentException(
                'Translations paths argument must be an array and traversable');
        }

        foreach ($paths as $path) {
            $this->addTranslationsPath($path);
        }

        return $this;
    }

    public function getTranslationDirectories()
    {
        if (!isset($this->_translationsPaths)) {
            $this->_translationsPaths = array();
        }

        return $this->_translationsPaths;
    }

    public function _()
    {
        $args = func_get_args();
        $num = func_num_args();
        $args[0] = $this->translate($args[0]);

        if ($num > 1) {
            $value = call_user_func_array('sprintf', $args);
        } else {
            $value = $args[0];
        }

        return $value;
    }

    public function addTranslation($locale, $orginal, $translation)
    {
        if (!isset(self::$_translations)) {
            self::$_translations = array();
        }

        if (!isset(self::$_translations[$locale])) {
            self::$_translations[$locale] = array();
        }

        self::$_translations[$locale][$original] = $translation;
        return $this;
    }

    public function addTranslationsPath($path)
    {
        if (is_string($path)) {
            $path = new \SplFileInfo($path);
        }

        if (!$path instanceof \SplFileInfo) {
            throw new \InvalidArgumentException(
                '"'.$path.'" must be a string or an instance of \SplFileInfo');
        }

        if (!$path->isDir() || !$path->isReadable()) {
            throw new \InvalidArgumentException(
                '"'.$path.'" is not a directory or is not readable');
        }

        if (!isset($this->_translationsPaths)) {
            $this->_translationsPaths = array();
        }

        $this->_translationsPaths[] = $path->getRealPath();
    }

    public function scanDirectoriesForTranslations()
    {
        $iterator = new \DirectoryIterator($this->getTranslationDirectories());

        foreach ($iterator as $info) {
            if ($info->isDot()) {
                continue;
            }

            if ($info->isFile() && $info->isReadable()) {
                $file = new \SplFileObject($info->getRealPath());
                $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);
                $file->setCsvControl(';', '"');

                foreach ($file as $row) {
                    if (!isset($row[0])
                        || !isset($row[1])
                        || strpos($row[0], '//') === 0
                        || strpos($row[0], '/*') === 0
                        || strpos($row[0], '#') === 0
                    ) {
                        continue;
                    }

                    self::$_translations[$locale][$row[0]] = $row[1];
                }
            }
        }
    }

    public function translate($value, $locale = null)
    {
        $translations = $this->getTranslations($locale);

        if (isset($translations[$value])) {
            $value = $translations[$value];
        }

        return $value;
    }
}
