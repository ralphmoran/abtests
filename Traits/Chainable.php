<?php

namespace Traits;

trait Chainable {

    /**
     * Processes method name.
     *
     * @param string $name
     * @return string
     */
    private static function parseMethodName( string $name ) : string
    {
        return $name .'_';
    }

    /**
     * Calling method "$name" from instance scope.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed|NULL
     */
    public function __call( string $method, array $arguments )
    {
        $method = static::parseMethodName($method);

        return method_exists( $this, $method ) ? call_user_func_array( [ $this, $method ], $arguments ) : NULL ;
    }

    /**
     * Calling method "$name" from static scope.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed|NULL
     */
    public static function __callStatic( string $method, array $arguments )
    {

        $instance = new static();
        $method   = static::parseMethodName( $method );

        return method_exists( $instance, $method ) ? call_user_func_array( [ $instance, $method ], $arguments ) : NULL ;
    }

}
