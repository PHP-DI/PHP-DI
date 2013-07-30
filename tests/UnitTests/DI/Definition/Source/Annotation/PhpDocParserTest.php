<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Source\Annotation;

use DI\Definition\Source\Annotation\PhpDocParser;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Test class for PhpDocParserTest
 */
class PhpDocParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/87
     */
    public function testGetParameterTypeUseStatementBeforeLocalNamespace()
    {
        $parser = new PhpDocParser();

        $target1 = new Fixtures\TargetFixture1();

        $target1ReflectionClass = new \ReflectionClass($target1);
        $target1ReflectionMethod = $target1ReflectionClass->getMethod("SomeMethod");
        $target1ReflectionParams = $target1ReflectionMethod->getParameters();

        $result = $parser->getParameterType($target1ReflectionClass, $target1ReflectionMethod, $target1ReflectionParams[0]);

        //Since TargetFixture1 file has a use statement to the Subspace namespace, that's the one that should be returned
        $this->assertEquals('UnitTests\DI\Definition\Source\Annotation\Fixtures\Subspace\SomeDependencyFixture', $result);


        $result = $parser->getParameterType($target1ReflectionClass, $target1ReflectionMethod, $target1ReflectionParams[1]);

        //this parameter should be unaffected by use namespace since it has a relative type path
        $this->assertEquals('UnitTests\DI\Definition\Source\Annotation\Fixtures\Subspace\SomeDependencyFixture2', $result);



        $target2 = new Fixtures\TargetFixture2();

        $target2ReflectionClass = new \ReflectionClass($target2);
        $target2ReflectionMethod = $target2ReflectionClass->getMethod("SomeMethod");
        $target2ReflectionParams = $target2ReflectionMethod->getParameters();

        $result = $parser->getParameterType($target2ReflectionClass, $target2ReflectionMethod, $target2ReflectionParams[0]);

        //Since TargetFixture2 file has a use statement with an alias to the Subspace namespace, that's the one that should be returned
        $this->assertEquals('UnitTests\DI\Definition\Source\Annotation\Fixtures\Subspace\SomeDependencyFixture2', $result);


        $result = $parser->getParameterType($target2ReflectionClass, $target2ReflectionMethod, $target2ReflectionParams[1]);

        //this parameter should be unaffected by use namespace since it has a relative type path
        $this->assertEquals('UnitTests\DI\Definition\Source\Annotation\Fixtures\Subspace\SomeDependencyFixture2', $result);



        $target3 = new Fixtures\TargetFixture3();

        $target3ReflectionClass = new \ReflectionClass($target3);
        $target3ReflectionMethod = $target3ReflectionClass->getMethod("SomeMethod");
        $target3ReflectionParams = $target3ReflectionMethod->getParameters();

        $result = $parser->getParameterType($target3ReflectionClass, $target3ReflectionMethod, $target3ReflectionParams[0]);

        //Since TargetFixture3 file has NO use statement, the one local to the target's namespace should be used
        $this->assertEquals('UnitTests\DI\Definition\Source\Annotation\Fixtures\SomeDependencyFixture', $result);


        $result = $parser->getParameterType($target3ReflectionClass, $target3ReflectionMethod, $target3ReflectionParams[1]);

        //this parameter should be unaffected by use namespace since it has a relative type path
        $this->assertEquals('UnitTests\DI\Definition\Source\Annotation\Fixtures\Subspace\SomeDependencyFixture2', $result);
    }


    /**
     * This test ensures that use statements in class files take precedence in resolving type annotations
     * @see https://github.com/mnapoli/PHP-DI/issues/87
     */
    public function testGetPropertyTypeUseStatementBeforeLocalNamespace()
    {
        $parser = new PhpDocParser();

        $target1 = new Fixtures\TargetFixture1();

        $target1ReflectionClass = new \ReflectionClass($target1);
        $target1ReflectionProperty1 = $target1ReflectionClass->getProperty("dependency1");

        $result = $parser->getPropertyType($target1ReflectionClass, $target1ReflectionProperty1);

        //Since TargetFixture1 file has a use statement to the Subspace namespace, that's the one that should be returned
        $this->assertEquals('UnitTests\DI\Definition\Source\Annotation\Fixtures\Subspace\SomeDependencyFixture', $result);


        $target1ReflectionProperty2 = $target1ReflectionClass->getProperty("dependency2");

        $result = $parser->getPropertyType($target1ReflectionClass, $target1ReflectionProperty2);

        //this property should be unaffected by use namespace since it has a relative type path
        $this->assertEquals('UnitTests\DI\Definition\Source\Annotation\Fixtures\Subspace\SomeDependencyFixture2', $result);



        $target2 = new Fixtures\TargetFixture2();

        $target2ReflectionClass = new \ReflectionClass($target2);
        $target2ReflectionProperty1 = $target2ReflectionClass->getProperty("dependency1");

        $result = $parser->getPropertyType($target2ReflectionClass, $target2ReflectionProperty1);

        //Since TargetFixture2 file has a use statement with an alias to the Subspace namespace, that's the one that should be returned
        $this->assertEquals('UnitTests\DI\Definition\Source\Annotation\Fixtures\Subspace\SomeDependencyFixture2', $result);


        $target2ReflectionProperty2 = $target2ReflectionClass->getProperty("dependency2");

        $result = $parser->getPropertyType($target2ReflectionClass, $target2ReflectionProperty2);

        //this property should be unaffected by use namespace since it has a relative type path
        $this->assertEquals('UnitTests\DI\Definition\Source\Annotation\Fixtures\Subspace\SomeDependencyFixture2', $result);



        $target3 = new Fixtures\TargetFixture3();

        $target3ReflectionClass = new \ReflectionClass($target3);
        $target3ReflectionProperty1 = $target3ReflectionClass->getProperty("dependency1");

        $result = $parser->getPropertyType($target3ReflectionClass, $target3ReflectionProperty1);

        //Since TargetFixture3 file has NO use statement, the one local to the target's namespace should be used
        $this->assertEquals('UnitTests\DI\Definition\Source\Annotation\Fixtures\SomeDependencyFixture', $result);


        $target3ReflectionProperty2 = $target3ReflectionClass->getProperty("dependency2");

        $result = $parser->getPropertyType($target3ReflectionClass, $target3ReflectionProperty2);

        //this property should be unaffected by use namespace since it has a relative type path
        $this->assertEquals('UnitTests\DI\Definition\Source\Annotation\Fixtures\Subspace\SomeDependencyFixture2', $result);
    }

}
