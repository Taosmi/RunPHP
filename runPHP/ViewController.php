<?php

namespace runPHP;
use runPHP\Response;

/**
 * Render an HTML view as a response and make the session data available inside
 * the HTML code.
 */
class ViewController {

    /**
     * @var array  A request info.
     */
    public $request;


    /**
     * The controller get a reference to the request information.
     *
     * @param array  $request  The request information.
     */
    public function __construct ($request) {
        $this->request = $request;
    }


    /**
     * Main function.
     *
     * @param  array  $params  The parameters when the URL is a backward URL.
     * @return object          A response.
     */
    public function main ($params = null) {
        // Include the session data and set the HTML fil.
        $response = new Response(array(
            'session' => Session::getAll(),
            'params' => $params
        ));
        $response->setFile($this->request['ctrl']);
        // Return the response.
        return $response;
    }
}