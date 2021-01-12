<?php

namespace Traits;

trait Logger {

    public static $log = [];

    /**
     * Retrieves a specific property|log.
     *
     * @param   string  $meta
     * @return  void
     */
    public function get( $verbose = false )
    {
        if ($verbose)
            print_r(static::$log);
        else 
            return static::$log;
    }

}