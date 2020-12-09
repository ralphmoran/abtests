<?php

use Traits\Chainable;


/**
 * This class is the general handler, it takes care of each test and runs them.
 * 
 * Each test (AbstractTest) must has the run() method.
 */
final class SplitTestHandler
{
    use Chainable;

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
    public function payload_( array $payload = [] )
    {
        $this->payload = ( empty($paload) && !empty($GLOBALS['DATA']) ) ? $GLOBALS['DATA'] : $payload;

        return $this;
    }


    /**
     * Performs all tests from $TESTS array in a sequential order.
     *
     * @param   array   $TESTS
     * @return  SplitTestHandler
     */
    public function handle_( $TESTS = [] )
    {
        $this->payload = ( empty($this->payload) && !empty($GLOBALS['DATA']) ) ? $GLOBALS['DATA'] : $this->payload;

        if( is_array( $TESTS ) ){
            foreach( $TESTS as $test ){
                try{
                    $this->loadTest( $test )
                            ->perform();
                } catch (Exception $e){
                    $this->error_log[]  = [
                        'test'      => $test,
                        'message'   => $e->getMessage(),
                    ];
                }
            }
        }

        return $this;
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
        if( file_exists( $test . '.php' ) ){

            //
            //  Load class file
            //
            @require_once( $test . '.php' );

            $test_name = explode( '/', $test );
            $test_name = end( $test_name );


            if( !class_exists( $test_name ) ){

                $this->error_log[]  = [
                    'test'      => $test_name,
                    'message'   => 'Test: ' . $test_name . ' does not exist.',
                ];

                throw new Exception('Test: ' . $test_name . ' does not exist.');

            }

            $this->LOADED_TESTS[]   = $test_name;

            //
            //  Instantiate Test class
            //
            $this->current_test = new $test_name( $this->payload );

            return $this;

        }

        throw new Exception('File: ' . $test . '.php does not exist.');
    }


    /**
     * Retrieves a specific property|log.
     *
     * @param   string  $meta
     * @return  void
     */
    public function get_( $meta = '', $verbose = false )
    {
        switch( $meta ){
            case 'log':
            case 'l':
            case 'error':
            case 'e':
            case 'messages':
            case 'm':
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
    
}
