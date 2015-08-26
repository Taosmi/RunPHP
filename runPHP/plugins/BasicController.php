<?php

namespace runPHP\plugins;
use runPHP\IController, runPHP\Response;

/**
 * This class implements the controller interface. It provides
 * no functionality, only renders a HTML view as response.
 */
class BasicController implements IController {

    /**
     * @param array $request
     */
    public function __construct ($request) {
        // Does nothing.
    }

    /**
     * Main function.
     */
    public function main () {
        // Render the page.
        return new Response('html');
    }
}