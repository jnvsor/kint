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

use Dom\HTMLDocument;
use DOMException;
use Kint\Zval\Context\BaseContext;
use Kint\Zval\Representation\Representation;
use Kint\Zval\Value;

class HtmlPlugin extends AbstractPlugin implements PluginCompleteInterface
{
    public function getTypes(): array
    {
        return ['string'];
    }

    public function getTriggers(): int
    {
        if (!KINT_PHP84) {
            return Parser::TRIGGER_NONE; // @codeCoverageIgnore
        }

        return Parser::TRIGGER_SUCCESS;
    }

    public function parseComplete(&$var, Value $v, int $trigger): Value
    {
        if ('<!doctype html>' !== \strtolower(\substr($var, 0, 15))) {
            return $v;
        }

        try {
            $html = HTMLDocument::createFromString($var, LIBXML_NOERROR);
        } catch (DOMException $e) {
            return $v;
        }

        $c = $v->getContext();

        $base = new BaseContext('childNodes');
        $base->depth = $c->getDepth();

        if (null !== ($ap = $c->getAccessPath())) {
            $base->access_path = '\\Dom\\HTMLDocument::createFromString('.$ap.')->childNodes';
        }

        $out = $this->getParser()->parse($html->childNodes, $base);
        $iter = $out->getRepresentation('iterator');

        $r = new Representation('HTML');
        if (isset($out->hints['depth_limit'])) {
            $out->hints['omit_spl_id'] = true;
            $r->contents = $out;
            $v->addRepresentation($r, 0);
        } elseif (\is_array($iter->contents ?? null)) {
            $r->contents = [];

            /**
             * @psalm-var Value[] $iter->contents
             * Psalm bug #11055
             */
            foreach ($iter->contents as $val) {
                $val->hints['omit_spl_id'] = true;
                $r->contents[] = $val;
            }

            if ($r->contents) {
                $v->addRepresentation($r, 0);
            }
        }

        return $v;
    }
}
