<?php

class Kint_Renderer_RichVue_DepthLimit extends Kint_Renderer_RichVue_Plugin
{
    public function render($o)
    {
        return '<dl>'.$this->renderHeaderLocked($o, '<var>Depth Limit</var>').'</dl>';
    }
}
