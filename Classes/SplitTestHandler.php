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

    const _PAYLOAD_ERROR_MSG_ = 'There are no values to work on: payload is empty.';
    const _TEST_ERROR_MSG_ = 'There have not been set any test.';

    /**
     * An array where the control is in.
     *
     * @var array
     */
    private $payload = [];

    /**
     * List of loaded tests.
     *
     * @var array
     */
    private $loaded_tests = [];

    /**
     * Current working test.
     *
     * @var [type]
     */
    private $current_test;

    /**
     * Full file path for test.
     *
     * @var string
     */
    private $current_path_test = '';

    /**
     * File extension.
     *
     * @var string
     */
    private $current_file_ext = 'php';


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
     * Performs all tests from $TESTS array in sequential order.
     *
     * @param array $TESTS
     * @return SplitTestHandler
     */
    public function handle( $tests = [] )
    {
        $this->ifEmptyThenExit( $this->payload, SplitTestHandler::_PAYLOAD_ERROR_MSG_ );
        $this->ifEmptyThenExit( $tests, SplitTestHandler::_TEST_ERROR_MSG_ );
        
        foreach( $tests as $test ){
            try{
                $this->loadTest( $test )
                        ->perform();
            } catch (Exception $e){
                $this::$log['errors'][]  = [
                    'type'      => 'TEST',
                    'method'    => __METHOD__,
                    'message'   => $e->getMessage(),
                ];
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
     * @throws Exception If $payload is empty.
     */
    private function setPayload( $payload = [] )
    {
        try{
            $this->ifEmptyThenExit( $payload, SplitTestHandler::_PAYLOAD_ERROR_MSG_ );
            $this->payload = $payload;
        } catch (Exception $e){
            $this::$log['errors'][]  = [
                'type'      => 'PAYLOAD',
                'method'    => __METHOD__,
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
        $this->current_file_ext = $ext;
        $test_name = $this->loadFile( $test . '.' . $ext );

        if( $test_name !== NULL && is_string( $test_name ) ) {
            $this->current_test = new $test_name( $this->payload );
            $this->checkIncompatibility();
        }

        return $this;
    }


    /**
     * Checks the incompatibility of the current test against loaded tests.
     *
     * @return void
     * @throws Exception If there is at least one test loaded incompatible with current test.
     */
    private function checkIncompatibility()
    {
        if( !empty( array_intersect( $this->current_test->getIncompatibleTests(), $this->loaded_tests ) ) ){
            throw new Exception(
                                'Test incompatibility of "' . $this->current_path_test 
                                . '" with ["' 
                                    . implode( '", "', $this->current_test->getIncompatibleTests()) 
                                . '"]'
                            );
        }
    }


    /**
     * Autoloader: This autoloader prevents an exploit attack.
     * 
     * PHP include & require exposes the current context to the included file.
     * 
     * @link https://github.com/Respect/Loader/issues/6
     * @link https://owasp.org/www-community/vulnerabilities/PHP_Object_Injection
     *
     * @param string $file
     * @return mixed
     * @throws Exception If $file does not exist or class couldn't being loaded.
     */
    private function loadFile( $file )
    {
        $test_name = $this->getTestName( $file );
        
        if( !in_array( $test_name, $this->loaded_tests ) ){

            # In order to avoid object injection exploits
            call_user_func(function () use ( $file ) {
                ob_start();

                if( !file_exists( $file ) ){
                    throw new Exception('File: ' . $file . '.php does not exist.');
                    ob_end_clean();

                    return NULL;
                }

                @require $file;
                
                ob_end_clean();
            });

            # Confirm if the class has been loaded from the $file
            if( !class_exists( $test_name ) ){
                throw new Exception('Test: ' . $test_name . ' does not exist.');

                return NULL;
            }

            $this->loaded_tests[] = $this->current_path_test = str_replace( '.' . $this->current_file_ext, '', $file );
        }

        return (string) $test_name;
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


    /**
     * Validates if array $data is empty, if so, it trows an exception and exits.
     *
     * @param array $data
     * @param string $msg
     * @return void
     * @throws Exception If array $data is empty.
     */
    private function ifEmptyThenExit( $data = [], $msg = 'Data is empty.' )
    {
        if( empty($data) ){
            throw new Exception($msg);
            exit;
        }
    }

}
