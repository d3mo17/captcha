<?php

namespace DMo\Captcha\Tests;

use DMo\Captcha\Code;
use PHPUnit\Framework\TestCase;

class CodeTest extends TestCase
{
    private $time;
    private $time2;

    private $cap;
    private $cap1;
    private $cap2;
    private $cap3;
    private $cap4;
    private $cap5;

    public function __construct() {
        parent::__construct();

        // no hashes in code array
        $this->time  = mktime(14, 34, 15, 3, 17, 78);
        // three hashes in code array
        $this->time2 = mktime(14, 34, 15, 3, 18, 78);

        $this->cap  = new Code('secret',    $this->time);
        $this->cap1 = new Code('secret',    $this->time2);
        $this->cap2 = new Code('secret',    $this->time2);
        $this->cap3 = new Code('secret123', $this->time2);
        $this->cap4 = new Code('secret',    $this->time2 + 1);
        $this->cap5 = new Code('secret',    $this->time2 + 6*60);
        $this->cap6 = new Code('secret',    $this->time2 + 6);
        $this->cap7 = new Code('secret',    $this->time2 + 13*60);
        $this->cap8 = new Code('secret',    $this->time2 + 3);
        $this->cap9 = new Code('secret123', $this->time2 + 5);
    }

    public function testFormalStructure() {
        $this->assertIsArray($this->cap->get());
        $this->assertCount(7, $this->cap->get());

        foreach (range(1, 9) as $i) {
            $field = "cap$i";
            $this->assertIsArray($this->{$field}->get());
            $this->assertCount(7, $this->{$field}->get());
        }
    }

    public function testEquality() {
        $this->assertEquals($this->cap1->get(), $this->cap2->get());

        // not equal, cause of first (time containing) entry only
        $this->assertNotEquals($this->cap1->get(), $this->cap4->get());
        // equal in other entries
        $diff = array_diff($this->cap1->get(), $this->cap4->get());
        $this->assertCount(1, $diff);

        // not equal at all, cause of different hash entries
        $this->assertNotEquals($this->cap1->get(), $this->cap3->get());
        $diff = array_diff($this->cap1->get(), $this->cap3->get());
        $this->assertNotCount(1, $diff);
    }

    public function testValid() {
        // valid, cause in same period
        $this->assertTrue($this->cap6->validate($this->cap1->get()));
        // with same hash signature
        $diff = array_diff($this->cap6->get(), $this->cap1->get());
        $this->assertCount(1, $diff);

        // valid, cause in same period
        $this->assertTrue($this->cap7->validate($this->cap5->get()));
        // with same hash signature
        $diff = array_diff($this->cap7->get(), $this->cap5->get());
        $this->assertCount(1, $diff);

        // valid, cause in same period
        $this->assertTrue($this->cap6->validate($this->cap4->get()));
        // with same hash signature
        $diff = array_diff($this->cap6->get(), $this->cap4->get());
        $this->assertCount(1, $diff);

        // different (hash values), cause of different time periods
        $diff = array_diff($this->cap5->get(), $this->cap4->get());
        $this->assertNotCount(1, $diff);
        // but valid, cause of absolute defined period to be valid
        $this->assertTrue($this->cap5->validate($this->cap4->get()));
    }

    public function testInValid() {
        // different secret strings
        $this->assertFalse($this->cap9->validate($this->cap2->get()));
        $this->assertEquals(1645535534, $this->cap9->getLastError()->getCode());

        // to fast for human interaction
        $this->assertFalse($this->cap4->validate($this->cap2->get()));
        $this->assertEquals(1645532843, $this->cap4->getLastError()->getCode());
        $this->assertFalse($this->cap6->validate($this->cap8->get()));
        $this->assertEquals(1645532843, $this->cap6->getLastError()->getCode());

        // last too long
        $this->assertFalse($this->cap7->validate($this->cap2->get()));
        $this->assertEquals(1645535407, $this->cap7->getLastError()->getCode());

        // Fields not empty where they have to be empty (honey pot)
        $code = $this->cap1->get();
        $code[2] = 'I\'m a robot!';
        $diff = array_diff($code, $this->cap6->get());
        $this->assertCount(2, $diff);
        $this->assertFalse($this->cap6->validate($code));
        $this->assertEquals(1645535534, $this->cap6->getLastError()->getCode());

        // Validation of hash with time values fails
        $code = $this->cap1->get();
        $values = explode(':', $code[1]);
        $values[1] .= 'f';
        $code[1] = implode(':', $values);
        $diff = array_diff($code, $this->cap6->get());
        $this->assertCount(1, $diff);
        $this->assertFalse($this->cap6->validate($code));
        $this->assertEquals(1645535755, $this->cap6->getLastError()->getCode());
    }
}
