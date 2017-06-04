<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Dumper;

use DI\Definition\Dumper\ObjectDefinitionDumper;

/**
 * @covers \DI\Definition\Dumper\ObjectDefinitionDumper
 */
class ObjectDefinitionDumperTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $definition = \DI\create(FixtureClass::class)
            ->lazy()
            ->constructor(\DI\get('Mailer'), 'email@example.com')
            ->method('setFoo', \DI\get('SomeDependency'))
            ->property('prop', 'Some value')
            ->getDefinition('foo');
        $dumper = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = true
    __construct(
        $mailer = get(Mailer)
        $contactEmail = \'email@example.com\'
    )
    $prop = \'Some value\'
    setFoo(
        $foo = get(SomeDependency)
    )
)';

        $this->assertEquals($str, $dumper->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testClass()
    {
        $definition = \DI\create(FixtureClass::class)
            ->getDefinition('foo');
        $dumper = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
)';

        $this->assertEquals($str, $dumper->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testNonExistentClass()
    {
        $definition = \DI\create('foobar')
            ->constructor('foo', 'bar')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = #UNKNOWN# foobar
    lazy = false
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testNonInstantiableClass()
    {
        $definition = \DI\create('ArrayAccess')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = #NOT INSTANTIABLE# ArrayAccess
    lazy = false
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testLazy()
    {
        $definition = \DI\create('stdClass')
            ->lazy()
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = stdClass
    lazy = true
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testConstructorParameters()
    {
        $definition = \DI\create(FixtureClass::class)
            ->constructor(\DI\get('Mailer'), 'email@example.com')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
    __construct(
        $mailer = get(Mailer)
        $contactEmail = \'email@example.com\'
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testUndefinedConstructorParameter()
    {
        $definition = \DI\create(FixtureClass::class)
            ->constructor(\DI\get('Mailer'))
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
    __construct(
        $mailer = get(Mailer)
        $contactEmail = #UNDEFINED#
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testPropertyValue()
    {
        $definition = \DI\create(FixtureClass::class)
            ->property('prop', 'Some value')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
    $prop = \'Some value\'
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testPropertyGet()
    {
        $definition = \DI\create(FixtureClass::class)
            ->property('prop', \DI\get('foo'))
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
    $prop = get(foo)
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testMethodLinkParameter()
    {
        $definition = \DI\create(FixtureClass::class)
            ->method('setFoo', \DI\get('Mailer'))
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
    setFoo(
        $foo = get(Mailer)
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testMethodValueParameter()
    {
        $definition = \DI\create(FixtureClass::class)
            ->method('setFoo', 'foo')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
    setFoo(
        $foo = \'foo\'
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testMethodDefaultParameterValue()
    {
        $definition = \DI\create(FixtureClass::class)
            ->method('defaultValue')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
    defaultValue(
        $foo = (default value) \'bar\'
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }
}

class FixtureClass
{
    public $prop;

    public function __construct($mailer, $contactEmail)
    {
    }

    public function setFoo($foo)
    {
    }

    public function defaultValue($foo = 'bar')
    {
    }
}
