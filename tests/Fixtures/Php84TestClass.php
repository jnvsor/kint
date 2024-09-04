<?php

namespace Kint\Test\Fixtures;

class Php84TestClass
{
    public int $a {
        get => $this->a;
        set(int $value) {
            $this->a = $value + 1;
        }
    }

    protected int $b {
        get => $this->b * 2;
    }

    private int $c {
        set(int $value) {
            $this->c = $value;
        }
    }

    private int $d {
        get => $this->a / 2;
        set(int $value) {
            $this->a = $value * 2;
        }
    }

    protected int $e {
        get => $this->b / 2;
    }

    public int $f {
        set(int $value) {
            $this->b = $value - 1;
        }
    }

    public array $bt {
        get => debug_backtrace();
    }
}
