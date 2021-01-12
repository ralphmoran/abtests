<?php

use Traits\Logger;

/**
 * This class is the general handler, it takes care of each test and runs them.
 * 
 * Each test (AbstractTest) must has the run() method.
 */
final class SplitTestHandler
{

    use Logger;

    private $payload        = [];
    private $loaded_tests   = [];
    private $current_test;


    /**
     * Sets the payload for all tests.
     *
     * @param array $payload
     * @return SplitTestHandler
     */
    public function payload( $payload = [] )
    {
        $this->setPayload($payload);

        return $this;
    }


    /**
     * Performs all tests from $TESTS array in a sequential order.
     *
     * @param array $TESTS
     * @return SplitTestHandler
     */
    public function handle( $TESTS = [] )
    {
        if( !empty($this->payload) && is_array($TESTS) && !empty($TESTS) ){
            foreach( $TESTS as $test ){
                try{
                    $this->loadTest( $test )
                            ->perform();
                } catch (Exception $e){
                    $this::$error_log[]  = [
                        'type'     => 'TEST',
                        'method'    => __METHOD__,
                        'message'   => $e->getMessage(),
                    ];
                }
            }
        }

        return $this;
    }


    /**
     * Assigns $payload to property $this->payload and checks
     * is this property is not empty.
     *
     * @param array $payload
     * @return void
     */
    private function setPayload( $payload = [] )
    {
        try{
            if( empty($payload) ){
                throw new Exception('There are no values to work on: payload is empty.');
                exit;
            }

            $this->payload = $payload;
        } catch (Exception $e){
            $this::$error_log[]  = [
                'type'     => 'PAYLOAD',
                'method'     => __METHOD__,
                'message'   => $e->getMessage(),
            ];
        }
    }


    /**
     * This is a wrapper for method run() from each Test instance.
     *
     * @return  void
     */
    private function perform()
    {
        $this->current_test->run();
    }


    /**
     * Loads a test class from a file path.
     *
     * @param string $test
     * @param string $ext
     * @return mixed
     */
    private function loadTest( $test, $ext = 'php' )
    {
        $test_name              = $this->loadFile( $test . '.' . $ext );
        $this->loaded_tests[]   = $test_name;

        $this->current_test     = new $test_name( $this->payload );

        return $this;
    }


    /**
     * Autoloader: This autoloader prevents an exploit attack.
     * 
     * PHP include & require exposes the current context to the included file.
     * 
     * Ref: https://github.com/Respect/Loader/issues/6
     *      https://owasp.org/www-community/vulnerabilities/PHP_Object_Injection
     *
     * @param string $file
     * @return mixed
     */
    private function loadFile( $file )
    {
        $test_name      = $this->getTestName( $file );
        $load_test_name = true;

        /* TODO: 
            1. $_ENV['ABTests'] must contains the actual loaded test classes.
            2. Return NULL if test class was not loaded.
         */
        if( !isset( $_ENV['ABTests'][$test_name] ) ){

            # In order to avoid object injection exploits
            call_user_func(function () use ( $file, &$load_test_name ) {
                ob_start();

                if( !file_exists( $file ) ){
                    $load_test_name = false;
                    throw new Exception('File: ' . $file . '.php does not exist.');
                }

                @require $file;
                
                ob_end_clean();
            });

            # Confirm if the class has been loaded from the $file
            if( !class_exists( $test_name ) ){
                $load_test_name = false;
                throw new Exception('Test: ' . $test_name . ' does not exist.');
            }

            if( $load_test_name )
                $_ENV['ABTests'][$test_name] = 1;
        }

        return $test_name;
    }


    /**
     * Parses a path/file test format and returns the test name.
     *
     * @param string $file
     * @param string $ext
     * @return string
     */
    private function getTestName( $file, $ext = 'php' ) : string
    {
        $test_name      = explode( '/', $file );
        $test_name      = end( $test_name );

        return rtrim( $test_name, '.' . $ext );
    }

}
