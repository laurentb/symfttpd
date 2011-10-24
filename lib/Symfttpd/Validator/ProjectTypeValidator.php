<?php
/**
 * ProjectTypeValidator class.
 * 
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 25/10/11
 */

namespace Symfttpd\Validator;

class ProjectTypeValidator
{
    protected $types = array(
        'symfony' => array('1.4', '2.0'), // the 2.0 version should not exist because it's Symfony2
        'Symfony' => array('2.0')
    );

    public static function getInstance()
    {
        return new ProjectTypeValidator();
    }

    /**
     * Checks that the project type and the verison is supported by symfttpd.
     * 
     * @param $type
     * @param null $version
     * @return bool
     */
    public function isValid($type, $version = null)
    {
        if (isset($this->types[$type])) {
            if (!empty($version) && !in_array($version, $this->types[$type])) {
                return false;
            }

            return true;
        }

        return false;
    }
}
