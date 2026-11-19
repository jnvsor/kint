<?php

namespace Kint\Test\Fixtures;

use Mysqli;

class MysqliTestClass extends Mysqli
{
    public string|int $affected_rows = 1234;
}
