<?php

namespace domain;
use runPHP\plugins\RepositoryPDO;

/**
 * The sayings repository.
 */
class SayingRepository extends RepositoryPDO {

    /**
     * Find and get a random saying from the repository.
     *
     * @return  object  The random saying.
     */
    public function findRandom () {
        // Get the total number of sayings.
        parent::select('COUNT(*) as total');
        parent::to(null);
        $total = parent::find()[0]['total'];
        // Get a random saying.
        $randomId = mt_rand(1, $total);
        parent::select(null);
        parent::to('domain\Saying');
        $result = parent::find(array(
            'condition' => 'id = "'.$randomId.'"'
        ));
        return reset($result);
    }
}