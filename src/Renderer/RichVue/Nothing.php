<?php

class Kint_Renderer_RichVue_Nothing extends Kint_Renderer_RichVue_Plugin
{
    public function render($o)
    {
        return '<dl><dt><var>No argument</var></dt></dl>';
    }
}
