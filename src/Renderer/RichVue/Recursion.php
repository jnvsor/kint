<?php

class Kint_Renderer_RichVue_Recursion extends Kint_Renderer_RichVue_Plugin
{
    public function render($o)
    {
        return '<dl>'.$this->renderHeaderLocked($o, '<var>Recursion</var>').'</dl>';
    }
}
