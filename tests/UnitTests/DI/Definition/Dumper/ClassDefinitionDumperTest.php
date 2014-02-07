<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Dumper;

use DI\Definition\ClassDefinition;
use DI\Definition\Dumper\ClassDefinitionDumper;
use DI\Definition\ValueDefinition;
use DI\Scope;

/**
 * @covers \DI\Definition\Dumper\ClassDefinitionDumper
 */
class ClassDefinitionDumperTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $definition = \DI\object('UnitTests\DI\Definition\Dumper\FixtureClass')
            ->lazy()
            ->constructor(\DI\link('Mailer'), 'email@example.com')
            ->method('setFoo', \DI\link('SomeDependency'))
            ->property('prop', 'Some value')
            ->getDefinition('foo');
        $resolver = new ClassDefinitionDumper();

        $str = 'Object (
    class = UnitTests\DI\Definition\Dumper\FixtureClass
    scope = singleton
    lazy = true
    __construct(
        $mailer = link(Mailer)
        $contactEmail = \'email@example.com\'
    )
    $prop = \'Some value\'
    setFoo(
        $foo = link(SomeDependency)
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testClass()
    {
        $definition = \DI\object('UnitTests\DI\Definition\Dumper\FixtureClass')
            ->getDefinition('foo');
        $resolver = new ClassDefinitionDumper();

        $str = 'Object (
    class = UnitTests\DI\Definition\Dumper\FixtureClass
    scope = singleton
    lazy = false
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testNonExistentClass()
    {
        $definition = \DI\object('foobar')
            ->constructor('foo', 'bar')
            ->getDefinition('foo');
        $resolver = new ClassDefinitionDumper();

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
        $resolver = new ClassDefinitionDumper();

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
            ->scope(Scope::PROTOTYPE())
            ->getDefinition('foo');
        $resolver = new ClassDefinitionDumper();

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
        $resolver = new ClassDefinitionDumper();

        $str = 'Object (
    class = stdClass
    scope = singleton
    lazy = true
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testConstructorParameters()
    {
        $definition = \DI\object('UnitTests\DI\Definition\Dumper\FixtureClass')
            ->constructor(\DI\link('Mailer'), 'email@example.com')
            ->getDefinition('foo');
        $resolver = new ClassDefinitionDumper();

        $str = 'Object (
    class = UnitTests\DI\Definition\Dumper\FixtureClass
    scope = singleton
    lazy = false
    __construct(
        $mailer = link(Mailer)
        $contactEmail = \'email@example.com\'
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testUndefinedConstructorParameter()
    {
        $definition = \DI\object('UnitTests\DI\Definition\Dumper\FixtureClass')
            ->constructor(\DI\link('Mailer'))
            ->getDefinition('foo');
        $resolver = new ClassDefinitionDumper();

        $str = 'Object (
    class = UnitTests\DI\Definition\Dumper\FixtureClass
    scope = singleton
    lazy = false
    __construct(
        $mailer = link(Mailer)
        $contactEmail = #UNDEFINED#
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testPropertyValue()
    {
        $definition = \DI\object('UnitTests\DI\Definition\Dumper\FixtureClass')
            ->property('prop', 'Some value')
            ->getDefinition('foo');
        $resolver = new ClassDefinitionDumper();

        $str = 'Object (
    class = UnitTests\DI\Definition\Dumper\FixtureClass
    scope = singleton
    lazy = false
    $prop = \'Some value\'
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testPropertyLink()
    {
        $definition = \DI\object('UnitTests\DI\Definition\Dumper\FixtureClass')
            ->property('prop', \DI\link('foo'))
            ->getDefinition('foo');
        $resolver = new ClassDefinitionDumper();

        $str = 'Object (
    class = UnitTests\DI\Definition\Dumper\FixtureClass
    scope = singleton
    lazy = false
    $prop = link(foo)
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testMethodLinkParameter()
    {
        $definition = \DI\object('UnitTests\DI\Definition\Dumper\FixtureClass')
            ->method('setFoo', \DI\link('Mailer'))
            ->getDefinition('foo');
        $resolver = new ClassDefinitionDumper();

        $str = 'Object (
    class = UnitTests\DI\Definition\Dumper\FixtureClass
    scope = singleton
    lazy = false
    setFoo(
        $foo = link(Mailer)
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
    }

    public function testMethodValueParameter()
    {
        $definition = \DI\object('UnitTests\DI\Definition\Dumper\FixtureClass')
            ->method('setFoo', 'foo')
            ->getDefinition('foo');
        $resolver = new ClassDefinitionDumper();

        $str = 'Object (
    class = UnitTests\DI\Definition\Dumper\FixtureClass
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
        $definition = \DI\object('UnitTests\DI\Definition\Dumper\FixtureClass')
            ->method('defaultValue')
            ->getDefinition('foo');
        $resolver = new ClassDefinitionDumper();

        $str = 'Object (
    class = UnitTests\DI\Definition\Dumper\FixtureClass
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
     * @expectedExceptionMessage This definition dumper is only compatible with ClassDefinition objects, DI\Definition\ValueDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $definition = new ValueDefinition('foo', 'bar');
        $resolver = new ClassDefinitionDumper();

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
