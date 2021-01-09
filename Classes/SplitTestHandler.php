<?php

/**
 * This class is the general handler, it takes care of each test and runs them.
 * 
 * Each test (AbstractTest) must has the run() method.
 */
final class SplitTestHandler
{
    private $payload        = [];
    private $error_log      = [];
    private $LOADED_TESTS   = [];
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
     * @param   array   $TESTS
     * @return  SplitTestHandler
     */
    public function handle( $TESTS = [] )
    {
        if( !empty($this->payload) && is_array( $TESTS ) && !empty($TESTS) ){
            foreach( $TESTS as $test ){
                try{
                    $this->loadTest( $test )
                            ->perform();
                } catch (Exception $e){
                    $this->error_log[]  = [
                        'error'     => 'TEST{' . $test . '}',
                        'method'    => __METHOD__,
                        'message'   => $e->getMessage(),
                    ];
                }
            }
        }

        return $this;
    }


    /**
     * Retrieves a specific property|log.
     *
     * @param   string  $meta
     * @return  void
     */
    public function get( $meta = '', $verbose = false )
    {
        switch( $meta ){
            case 'log':
            case 'l':
            case 'error':
            case 'e':
                if ($verbose)
                    print_r($this->error_log);
                else 
                    return $this->error_log;
            break;
            case 'loaded':
                if ($verbose)
                    print_r($this->LOADED_TESTS);
                else
                    return $this->LOADED_TESTS;
            break;

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
        $this->payload = $payload;
        $this->checkPayload();
    }


    /**
     * Checks if payload is empty.
     *
     * @return void
     */
    private function checkPayload() {
        if( empty($this->payload) ){
            try{
                throw new Exception('There are no values to work on: payload is empty.');
            } catch (Exception $e){
                $this->error_log[]  = [
                    'error'     => 'PAYLOAD',
                    'method'     => __METHOD__,
                    'message'   => $e->getMessage(),
                ];
            }
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
     * @param   string  $test
     * @return  mixed
     */
    private function loadTest( $test )
    {
        $test_name              = $this->loadFile( $test . '.php' );
        $this->LOADED_TESTS[]   = $test_name;

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
     * @param   string  $file
     * @return  mixed
     */
    private function loadFile( $file )
    {
        $test_name  = explode( '/', $file );
        $test_name  = end( $test_name );
        $test_name  = rtrim( $test_name, '.php' );

        if( !isset( $_ENV['ABTests'][$test_name] ) ){

            # In order to avoid object injection exploits
            call_user_func(function () use ( $file ) {
                ob_start();

                if( !file_exists( $file ) )
                    throw new Exception('File: ' . $file . '.php does not exist.');
                    
                @require $file;
                
                ob_end_clean();
            });

            # Confirm if the class has been loaded from the $file
            if( !class_exists( $test_name ) ){
                $this->error_log[]  = [
                    'test'      => $test_name,
                    'message'   => 'Test: ' . $test_name . ' does not exist.',
                ];

                throw new Exception('Test: ' . $test_name . ' does not exist.');
            }

            $_ENV['ABTests'][$test_name] = 1;
        }

        return $test_name;
    }

}
