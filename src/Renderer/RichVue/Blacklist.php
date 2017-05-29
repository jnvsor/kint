<?php

class Kint_Renderer_RichVue_Blacklist extends Kint_Renderer_RichVue_Plugin
{
    public function render($o)
    {
        return '<dl>'.$this->renderHeaderLocked($o, '<var>Blacklisted</var>').'</dl>';
    }
}
