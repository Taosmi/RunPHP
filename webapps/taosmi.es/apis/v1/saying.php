<?php

namespace apis\v1;
use runPHP\ApiController, runPHP\Response;
use domain\Saying as oSaying;

/**
 * The saying API.
 */
class saying extends ApiController {

    /**
     * Get a random saying from the repository.
     *
     * @param  array  $params  Request parameters.
     * @return Response        A response.
     */
    public function get ($params) {
        // Get a saying repository.
        $sayingRepo = $this->repository('domain\Saying');
        // Get the total number of sayings.
        $says = $sayingRepo->select('COUNT(*) as total')->to(null)->findOne();
        // Get a random saying.
        $randomId = mt_rand(1, $says['total']);
        $saying = $sayingRepo->findOne(array(
            'id' => eq($randomId)
        ));
        // Return the random saying.
        return new Response(array(
            'saying' => $saying
        ));
    }

}