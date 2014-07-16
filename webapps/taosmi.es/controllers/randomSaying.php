<?php

namespace controllers;
use runPHP\Controller, runPHP\Response;
use domain\SayingRepository;

/**
 * The controller for /randomSaying.
 */
class randomSaying extends Controller {

    /**
     * Main function.
     */
    public function main () {
        // Get a random saying.
        $sayingRepo = new SayingRepository($this->repos['taosmi']);
        $saying = $sayingRepo->findRandom();
        // Render the page.
        return new Response($saying);
    }
}