<?php

namespace domain;
use proWeb\plugins\RepositoryPDO;

/**
 * The saying repository.
 */
class SayingRepository extends RepositoryPDO {

    /**
     * Initiates the repository.
     *
     * @param string $connection  A connection string.
     */
    public function __construct($connection) {
        parent::__construct($connection);
        parent::from('regalos_saying');
        parent::to('domain\Saying');
    }


    /**
     * Adds a new saying to the repository and returns the saying with the
     * auto-generated id.
     *
     * @param object $saying  The new saying to add.
     * @return object         The added saying with the auto-generated id.
     */
    public function add ($saying) {
        $saying->id = parent::add($saying);
        return $saying;
    }

    /**
     * Finds and gets a random saying from the repository.
     *
     * @return object  The random saying.
     */
    public function findRandom () {
        // Gets the total.
        parent::select('COUNT(*) as total');
        parent::to(null);
        $total = parent::find()[0]['total'];
        // Gets the random.
        $randomId = mt_rand(1, $total);
        // Gets a random saying.
        parent::select(null);
        parent::to('domain\Saying');
        $result = parent::find(array(
            'condition' => 'id = "'.$randomId.'"'
        ));
        return reset($result);
    }
}