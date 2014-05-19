<?php

namespace controllers;
use proWeb\Controller, proWeb\Response;
use domain\SayingRepository;

/**
 * The controller for /randomSaying.
 */
class randomSaying extends Controller {

    /**
     * Main function.
     */
    public function main () {
        // Gets a random saying.
        $sayingRepo = new SayingRepository($this->cfg['REPOS']['taosmi']);
        $saying = $sayingRepo->findRandom();
        // Render the page.
        return new Response($saying);
    }
}