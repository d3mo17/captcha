<?php

namespace DMo\Captcha\Tests;

use DMo\Captcha\Code;
use DMo\Captcha\HTMLGenerator;
use PHPUnit\Framework\TestCase;

class HTMLGeneratorTest extends TestCase
{
    private $gen;

    public function __construct() {
        parent::__construct();

        // three hashes in code array
        $time = mktime(14, 34, 15, 3, 18, 78);

        $code = new Code('secret', $time);
        $this->gen = new HTMLGenerator($code);
    }

    public function testExceptionTooFewInputNames() {
        $this->expectException(\LengthException::class);
        $this->expectExceptionCode(1646092907);
        $this->gen->setInputNames(['bla']);
    }

    public function testExceptionTooFewInputNamesThreshold() {
        $this->expectException(\LengthException::class);
        $this->expectExceptionCode(1646092907);
        $this->gen->setInputNames(['f1', 'f2', 'f3', 'f4', 'f5', 'f6']);
    }

    public function testNoExceptionWithInputNames() {
        $this->gen->setInputNames(['f1', 'f2', 'f3', 'f4', 'f5', 'f6', 'f7']);
        // no exception expected:
        $this->assertTrue(true);
        $this->assertCount(7, $this->gen->getInputNames());
    }

    public function testExceptionTooManyInputNamesThreshold() {
        $this->expectException(\LengthException::class);
        $this->expectExceptionCode(1646092907);
        $this->gen->setInputNames(['f1', 'f2', 'f3', 'f4', 'f5', 'f6', 'f7', 'f8']);
    }

    public function testExceptionWrongInputType() {
        $this->expectException(\DomainException::class);
        $this->expectExceptionCode(1646092504);
        $this->gen->setInputType('bla');
    }

    public function testNoExceptionWithInputTypes() {
        $this->gen->setInputType('text');
        $this->gen->setInputType('hidden');
        $this->gen->setInputType();
        // no exception expected:
        $this->assertTrue(true);
    }
}
