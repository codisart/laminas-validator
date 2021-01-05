<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Stdlib\Parameters;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Identical;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class IdenticalTest extends TestCase
{
    /** @var Identical */
    public $validator;

    protected function setUp() : void
    {
        $this->validator = new Identical;
    }

    public function testTokenInitiallyNull()
    {
        $this->assertNull($this->validator->getToken());
    }

    public function testCanSetToken()
    {
        $this->testTokenInitiallyNull();
        $this->validator->setToken('foo');
        $this->assertEquals('foo', $this->validator->getToken());
    }

    public function testCanSetTokenViaConstructor()
    {
        $validator = new Identical('foo');
        $this->assertEquals('foo', $validator->getToken());
    }

    public function testValidatingWhenTokenNullReturnsFalse()
    {
        $this->assertFalse($this->validator->isValid('foo'));
    }

    public function testValidatingWhenTokenNullSetsMissingTokenMessage()
    {
        $this->testValidatingWhenTokenNullReturnsFalse();
        $messages = $this->validator->getMessages();
        $this->assertArrayHasKey('missingToken', $messages);
    }

    public function testValidatingAgainstTokenWithNonMatchingValueReturnsFalse()
    {
        $this->validator->setToken('foo');
        $this->assertFalse($this->validator->isValid('bar'));
    }

    public function testValidatingAgainstTokenWithNonMatchingValueSetsNotSameMessage()
    {
        $this->testValidatingAgainstTokenWithNonMatchingValueReturnsFalse();
        $messages = $this->validator->getMessages();
        $this->assertArrayHasKey('notSame', $messages);
    }

    public function testValidatingAgainstTokenWithMatchingValueReturnsTrue()
    {
        $this->validator->setToken('foo');
        $this->assertTrue($this->validator->isValid('foo'));
    }

    /**
     * @group Laminas-6953
     */
    public function testValidatingAgainstEmptyToken()
    {
        $this->validator->setToken('');
        $this->assertTrue($this->validator->isValid(''));
    }

    /**
     * @group Laminas-7128
     */
    public function testValidatingAgainstNonStrings()
    {
        $this->validator->setToken(true);
        $this->assertTrue($this->validator->isValid(true));
        $this->assertFalse($this->validator->isValid(1));

        $this->validator->setToken(['one' => 'two', 'three']);
        $this->assertTrue($this->validator->isValid(['one' => 'two', 'three']));
        $this->assertFalse($this->validator->isValid([]));
    }

    public function testValidatingTokenArray()
    {
        $validator = new Identical(['token' => 123]);
        $this->assertTrue($validator->isValid(123));
        $this->assertFalse($validator->isValid(['token' => 123]));
    }

    public function testValidatingNonStrictToken()
    {
        $validator = new Identical(['token' => 123, 'strict' => false]);
        $this->assertTrue($validator->isValid('123'));

        $validator->setStrict(true);
        $this->assertFalse($validator->isValid(['token' => '123']));
    }

    public function testEqualsMessageTemplates()
    {
        $validator = $this->validator;
        $this->assertObjectHasAttribute('messageTemplates', $validator);
        $this->assertEquals($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables()
    {
        $validator = $this->validator;
        $this->assertObjectHasAttribute('messageVariables', $validator);
        $this->assertEquals(array_keys($validator->getOption('messageVariables')), $validator->getMessageVariables());
    }

    public function testValidatingStringTokenInContext()
    {
        $this->validator->setToken('email');

        $this->assertTrue($this->validator->isValid(
            'john@doe.com',
            ['email' => 'john@doe.com']
        ));

        $this->assertFalse($this->validator->isValid(
            'john@doe.com',
            ['email' => 'harry@hoe.com']
        ));

        $this->assertFalse($this->validator->isValid(
            'harry@hoe.com',
            ['email' => 'john@doe.com']
        ));

        $this->assertTrue($this->validator->isValid(
            'john@doe.com',
            new Parameters(['email' => 'john@doe.com'])
        ));

        $this->assertFalse($this->validator->isValid(
            'john@doe.com',
            new Parameters(['email' => 'harry@hoe.com'])
        ));

        $this->assertFalse($this->validator->isValid(
            'harry@hoe.com',
            new Parameters(['email' => 'john@doe.com'])
        ));
    }

    public function testValidatingArrayTokenInContext()
    {
        $this->validator->setToken(['user' => 'email']);

        $this->assertTrue($this->validator->isValid(
            'john@doe.com',
            [
                'user' => [
                    'email' => 'john@doe.com',
                ],
            ]
        ));

        $this->assertFalse($this->validator->isValid(
            'john@doe.com',
            [
                'user' => [
                    'email' => 'harry@hoe.com',
                ],
            ]
        ));

        $this->assertFalse($this->validator->isValid(
            'harry@hoe.com',
            [
                'user' => [
                    'email' => 'john@doe.com',
                ],
            ]
        ));

        $this->assertTrue($this->validator->isValid(
            'john@doe.com',
            new Parameters([
                'user' => [
                    'email' => 'john@doe.com',
                ],
            ])
        ));

        $this->assertFalse($this->validator->isValid(
            'john@doe.com',
            new Parameters([
                'user' => [
                    'email' => 'harry@hoe.com',
                ],
            ])
        ));

        $this->assertFalse($this->validator->isValid(
            'harry@hoe.com',
            new Parameters([
                'user' => [
                    'email' => 'john@doe.com',
                ],
            ])
        ));
    }

    public function testCanSetLiteralParameterThroughConstructor()
    {
        $validator = new Identical(['token' => 'foo', 'literal' => true]);
        // Default is false
        $validator->setLiteral(true);
        $this->assertTrue($validator->getLiteral());
    }

    public function testLiteralParameterDoesNotAffectValidationWhenNoContextIsProvided()
    {
        $this->validator->setToken(['foo' => 'bar']);

        $this->validator->setLiteral(false);
        $this->assertTrue($this->validator->isValid(['foo' => 'bar']));

        $this->validator->setLiteral(true);
        $this->assertTrue($this->validator->isValid(['foo' => 'bar']));
    }

    public function testLiteralParameterWorksWhenContextIsProvided()
    {
        $this->validator->setToken(['foo' => 'bar']);
        $this->validator->setLiteral(true);

        $this->assertTrue($this->validator->isValid(
            ['foo' => 'bar'],
            ['foo' => 'baz'] // Provide a context to make sure the literal parameter will work
        ));
    }

    /**
     * @dataProvider invalidContextProvider
     *
     * @param mixed $context
     */
    public function testIsValidThrowsExceptionOnInvalidContext($context)
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->isValid('john@doe.com', $context);
    }

    /**
     * @return mixed[][]
     */
    public function invalidContextProvider()
    {
        return [
            [false],
            [new \stdClass()],
            ['dummy'],
        ];
    }
}
