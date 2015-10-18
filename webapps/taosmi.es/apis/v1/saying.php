<?php

namespace apis\v1;
use runPHP\plugins\ApiController, runPHP\Response;
use domain\SayingRepository;

/**
 * The saying API.
 */
class saying extends ApiController {

    /**
     * Get a random saying from the repository.
     */
    public function get () {
        // Get a random saying.
        $sayingRepo = $this->repository('domain\Saying');
        $saying = $sayingRepo->findRandom();
        // Return the info.
        return new Response('data', array(
            'saying' => $saying
        ));
    }

}