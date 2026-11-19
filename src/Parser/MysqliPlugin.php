<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Jonathan Vollebregt (jnvsor@gmail.com), Rokas Šleinius (raveren@gmail.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Kint\Parser;

use Kint\Value\AbstractValue;
use Kint\Value\Context\PropertyContext;
use Kint\Value\InstanceValue;
use Kint\Value\Representation\ContainerRepresentation;
use mysqli;

/**
 * Adds support for mysqli object parsing.
 *
 * Due to the way mysqli is implemented in PHP, this will cause
 * warnings on certain mysqli objects if screaming is enabled.
 */
class MysqliPlugin extends AbstractPlugin implements PluginCompleteInterface
{
    public const ALWAYS_READABLE = [
        // These 'properties' are actually globals
        'client_version' => true,
        'connect_errno' => true,
        'connect_error' => true,

        // These are not (Somehow)
        'client_info' => true,
        'errno' => true,
        'error' => true,
    ];

    // These are only readable on connected mysqli objects
    public const CONNECTED_READABLE = [
        'affected_rows' => true,
        'error_list' => true,
        'field_count' => true,
        'host_info' => true,
        'info' => true,
        'insert_id' => true,
        'server_info' => true,
        'server_version' => true,
        'sqlstate' => true,
        'protocol_version' => true,
        'thread_id' => true,
        'warning_count' => true,
    ];

    public function getTypes(): array
    {
        return ['object'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_COMPLETE;
    }

    /**
     * Before 8.1: Properties were nulls when cast to array
     * After 8.1: Properties are readonly and uninitialized when cast to array (Aka missing).
     */
    public function parseComplete(&$var, AbstractValue $v, int $trigger): AbstractValue
    {
        if (!$var instanceof mysqli || !$v instanceof InstanceValue) {
            return $v;
        }

        $props = $v->getRepresentation('properties');

        if (!$props instanceof ContainerRepresentation) {
            return $v;
        }

        $connected = isset($var->sqlstate);

        $parser = $this->getParser();

        $new_contents = [];

        foreach ($props->getContents() as $key => $obj) {
            $new_contents[$key] = $obj;

            $c = $obj->getContext();

            if (!$c instanceof PropertyContext) {
                continue;
            }

            if (isset(self::CONNECTED_READABLE[$c->getName()])) {
                $c->readonly = true;
                if (!$connected) {
                    // No failed connections after PHP 8.1, this must be an empty mysqli object
                    continue;
                }
            } elseif (!isset(self::ALWAYS_READABLE[$c->getName()])) {
                // If someone subclassed \mysqli
                continue; // @codeCoverageIgnore
            }

            $c->readonly = true;

            // Only handle unparsed properties
            if ('uninitialized' !== $obj->getType()) {
                continue;
            }

            $param = $var->{$c->getName()};

            $new_contents[$key] = $parser->parse($param, $c);
        }

        $new_contents = \array_values($new_contents);

        $v->setChildren($new_contents);

        if ($new_contents) {
            $v->replaceRepresentation(new ContainerRepresentation('Properties', $new_contents));
        }

        return $v;
    }
}
