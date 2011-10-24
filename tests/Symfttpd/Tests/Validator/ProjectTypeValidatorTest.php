<?php
/*
 * This file is part of the Symfony package.
 *
 * (c) Benjamin Grandfond <benjamin.grandfond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ProjectValidatorTest class.
 * 
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 25/10/11
 */

namespace Symfttpd\Tests\Validator;

use Symfttpd\Validator\ProjectTypeValidator;

class ProjectTypeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProjectTypeValidator $validator
     */
    protected $validator;
    
    public function setUp()
    {
        $this->validator = ProjectTypeValidator::getInstance();
    }

    public function testIsValid()
    {
        $this->assertInstanceof('Symfttpd\Validator\ProjectTypeValidator', $this->validator);
        $this->assertTrue($this->validator->isValid('symfony', '1.4'));
        $this->assertTrue($this->validator->isValid('symfony', '2.0'));
        $this->assertTrue($this->validator->isValid('Symfony', '2.0'));
        $this->assertFalse($this->validator->isValid('zend', '2.0'));
    }
}
