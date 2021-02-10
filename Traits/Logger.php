<?php

namespace Traits;

trait Logger {

    protected $log = [];

    /**
     * Retrieves a specific property|log.
     *
     * @param   string  $meta
     * @return  array|string
     */
    public function getLog( $verbose = false )
    {
        return ($verbose) ? print_r($this->log) : $this->log;
    }

}