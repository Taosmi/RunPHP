<?php

namespace runPHP;
use runPHP\Response;

/**
 * This class provides no functionality, only renders a HTML view as response.
 */
class ViewController {

    /**
     * Main function.
     */
    public function main () {
        // Render the page.
        return new Response();
    }
}