<?php

namespace controllers;
use proWeb\Controller, proWeb\plugins\HtmlView;

/**
 * The controller for /index.
 */
class index extends Controller {

    /**
     * Main function.
     */
    public function main () {
        // Render the page.
        $template = new HtmlView('/views/index');
        $template->render();
    }
}