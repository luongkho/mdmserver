<?php

/*
 * This file is part of the PHP CS utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\CS\Tests\DocBlock;

use Symfony\CS\DocBlock\Tag;

/**
 * @author Graham Campbell <graham@mineuk.com>
 */
class TagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideNameCases
     */
    public function testName($expected, $input)
    {
        $tag = new Tag($input);

        $this->assertSame($expected, $tag->getName());
    }

    public function provideNameCases()
    {
        return array(
            array('param', '     * @param Foo $foo'),
            array('return', '*   @return            false'),
            array('throws', '*@thRoWs \Exception'),
            array('throwsss', "\t@THROWSSS\t  \t RUNTIMEEEEeXCEPTION\t\t\t\t\t\t\t\n\n\n"),
            array('other', ' *   @\Foo\Bar(baz = 123)'),
            array('expectedexception', '     * @expectedException Exception'),
        );
    }

    /**
     * @dataProvider provideValidCases
     */
    public function testValid($expected, $input)
    {
        $tag = new Tag($input);

        $this->assertSame($expected, $tag->valid());
    }

    public function provideValidCases()
    {
        return array(
            array(true, '     * @param Foo $foo'),
            array(true, '*   @return            false'),
            array(true, '*@thRoWs \Exception'),
            array(false, "\t@THROWSSS\t  \t RUNTIMEEEEeXCEPTION\t\t\t\t\t\t\t\n\n\n"),
            array(false, ' *   @\Foo\Bar(baz = 123)'),
            array(false, '     * @expectedException Exception'),
        );
    }
}
