<?php

namespace Traits;

trait Logger {

    public static $error_log = [];

    /**
     * Retrieves a specific property|log.
     *
     * @param   string  $meta
     * @return  void
     */
    public function get( $verbose = false )
    {
        if ($verbose)
            print_r(static::$error_log);
        else 
            return static::$error_log;
    }

}