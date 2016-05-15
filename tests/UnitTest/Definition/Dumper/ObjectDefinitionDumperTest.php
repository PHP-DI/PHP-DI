<?php

namespace DI\Test\UnitTest\Definition\Dumper;

use DI\Definition\Dumper\ObjectDefinitionDumper;
use DI\Definition\ObjectDefinition;
use DI\Definition\ValueDefinition;
use DI\Scope;

/**
 * @covers \DI\Definition\Dumper\ObjectDefinitionDumper
 */
class ObjectDefinitionDumperTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $definition = \DI\object(FixtureClass::class)
            ->lazy()
            ->constructor(\DI\get('Mailer'), 'email@example.com')
            ->method('setFoo', \DI\get('SomeDependency'))
            ->property('prop', 'Some value')
            ->getDefinition('foo');
        $dumper = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    scope = singleton
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
    }

    public function testClass()
    {
        $definition = \DI\object(FixtureClass::class)
            ->getDefinition('foo');
        $dumper = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    scope = singleton
    lazy = false
)';

        $this->assertEquals($str, $dumper->dump($definition));
    }

    public function testNonExistentClass()
    {
        $definition = \DI\object('foobar')
            ->constructor('foo', 'bar')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = #UNKNOWN# foobar
    scope = singleton
    lazy = false
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testNonInstantiableClass()
    {
        $definition = \DI\object('ArrayAccess')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = #NOT INSTANTIABLE# ArrayAccess
    scope = singleton
    lazy = false
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testScopePrototype()
    {
        $definition = \DI\object('stdClass')
            ->scope(Scope::PROTOTYPE)
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = stdClass
    scope = prototype
    lazy = false
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testLazy()
    {
        $definition = \DI\object('stdClass')
            ->lazy()
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = stdClass
    scope = singleton
    lazy = true
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testConstructorParameters()
    {
        $definition = \DI\object(FixtureClass::class)
            ->constructor(\DI\get('Mailer'), 'email@example.com')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    scope = singleton
    lazy = false
    __construct(
        $mailer = get(Mailer)
        $contactEmail = \'email@example.com\'
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testUndefinedConstructorParameter()
    {
        $definition = \DI\object(FixtureClass::class)
            ->constructor(\DI\get('Mailer'))
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    scope = singleton
    lazy = false
    __construct(
        $mailer = get(Mailer)
        $contactEmail = #UNDEFINED#
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testPropertyValue()
    {
        $definition = \DI\object(FixtureClass::class)
            ->property('prop', 'Some value')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    scope = singleton
    lazy = false
    $prop = \'Some value\'
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testPropertyget()
    {
        $definition = \DI\object(FixtureClass::class)
            ->property('prop', \DI\get('foo'))
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    scope = singleton
    lazy = false
    $prop = get(foo)
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testMethodLinkParameter()
    {
        $definition = \DI\object(FixtureClass::class)
            ->method('setFoo', \DI\get('Mailer'))
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    scope = singleton
    lazy = false
    setFoo(
        $foo = get(Mailer)
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testMethodValueParameter()
    {
        $definition = \DI\object(FixtureClass::class)
            ->method('setFoo', 'foo')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    scope = singleton
    lazy = false
    setFoo(
        $foo = \'foo\'
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testMethodDefaultParameterValue()
    {
        $definition = \DI\object(FixtureClass::class)
            ->method('defaultValue')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    scope = singleton
    lazy = false
    defaultValue(
        $foo = (default value) \'bar\'
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition dumper is only compatible with ObjectDefinition objects, DI\Definition\ValueDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $definition = new ValueDefinition('foo', 'bar');
        $resolver = new ObjectDefinitionDumper();

        $resolver->dump($definition);
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
