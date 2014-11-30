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
        $sayingRepo = $this->repository('domain\Saying');
        $saying = $sayingRepo->findRandom();
        // Render the page.
        return new Response('data', array(
            'saying' => $saying
        ));
    }
}