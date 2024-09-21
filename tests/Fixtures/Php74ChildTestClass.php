<?php

namespace Kint\Test\Fixtures;

class Php74ChildTestClass extends Php74TestClass
{
    public const VALUE_1 = 'replaced';
    public const VALUE_5 = 5;
    private const VALUE_3 = 'replaced';
    private const VALUE_6 = 6;

    public static $value_1 = 'replaced';
    public static $value_5 = 5;
    private static $value_3 = 'replaced';
    private static $value_6 = 6;

    public string $c = 'subclassed';
    private string $last = 'done';

    public function test1() {}
    public static function test2() {}
    public function test9() {}
    public static function test10() {}
    private function test5() {}
    private static function test6() {}
    private function test11() {}
    private static function test12() {}
}
