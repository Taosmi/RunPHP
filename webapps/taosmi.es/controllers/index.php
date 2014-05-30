<?php

namespace controllers;
use runPHP\Controller, runPHP\Response;

/**
 * The controller for /index.
 */
class index extends Controller {

    /**
     * Main function.
     */
    public function main () {
        // Render the page.
        return new Response('/views/index');
    }
}