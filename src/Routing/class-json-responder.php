<?php

namespace Clockwork_For_Wp\Routing;

use ToyWpRouting\Responder\JsonResponder;

class Json_Responder extends JsonResponder {
    protected $data;

    protected $status;

    protected $options;

    public function __construct($data, int $status = 200, int $options = 0) {
        parent::__construct( $data, $status, $options );

        $this->json()->dontEnvelopeResponse();
    }
}
