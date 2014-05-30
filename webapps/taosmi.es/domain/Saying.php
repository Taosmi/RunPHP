<?php

namespace domain;

/**
 * A saying representation.
 */
class Saying {

    /**
     * A unique id.
     * @var string
     */
    public $id;

    /**
     * The saying itself.
     * @var string
     */
    public $quote;

    /**
     * The author.
     * @var string
     */
    public $author;
}