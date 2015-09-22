<?php
namespace Solenoid\I18n\Data;

class CldrXMLElement extends \SimpleXMLElement
{
    /**
    * Read the content from locale
    *
    * Can be called like:
    * <ldml>
    *     <delimiter>test</delimiter>
    *     <second type='myone'>content</second>
    *     <second type='mysecond'>content2</second>
    *     <third type='mythird' />
    * </ldml>
    *
    * Case 1: _readFile('ar','/ldml/delimiter')             -> returns [] = test
    * Case 1: _readFile('ar','/ldml/second[@type=myone]')   -> returns [] = content
    * Case 2: _readFile('ar','/ldml/second','type')         -> returns [myone] = content; [mysecond] = content2
    * Case 3: _readFile('ar','/ldml/delimiter',,'right')    -> returns [right] = test
    * Case 4: _readFile('ar','/ldml/third','type','myone')  -> returns [myone] = mythird
    *
    * @param  string $locale
    * @param  string $path
    * @param  string $attribute
    * @param  string $value
    * @access private
    * @return array
    */
    public function xpath($path)
    {
        $result = parent::xpath($path);
        $argc = func_num_args();
        $argv = func_get_args();

        if ($argc > 1 && !empty($result)) {
            foreach ($result as &$found) {
                if (!empty($argv[1])) {
                    return (string)$found[$argv[1]];
                }
            }
        }

        return $result;
    }
}
