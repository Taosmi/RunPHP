<?php

namespace runPHP;

/**
 * A default controller that loads a html as the response.
 */
class defaultController extends Controller {

    /**
     * Main function.
     */
    public function main () {
        // Render the page.
        return new Response('html');
    }
}