<?php

namespace controllers;
use ProWeb\Controller, ProWeb\Plugins\HtmlView;

/**
 * /index controller.
 */
class index extends Controller {

    /**
     * Main function.
     */
    public function main () {
        // Render the web.
        $template = new HtmlView('/views/index');
        $template->render();
    }
}