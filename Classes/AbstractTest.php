<?php

namespace Classes;

abstract class AbstractTest
{

    protected $payload              = [];
    protected $sample               = 100;
    protected $group_sample         = 0;
    protected $control              = '';
    protected $control_value        = '';
    protected $current_index        = '';
    protected $current_index_value  = '';
    protected $rule_statements      = [];
    protected $statement_pointer    = 0;
    protected $run_test             = true;


    /**
     * @param array $payload
     */
    public function __construct( $payload = [] )
    {
        $this->payload = $payload;
    }


    /**
     * RUN() is required to exist in any test.
     *
     * @return void
     */
    abstract public function run();


    /**
     * Gets/Sets a global sample (percentage), if the sample does not comply 
     * with the solicited percentage, it quits.
     *
     * @param integer $sample
     * @return AbstractTest
     */
    protected function sample( $sample = 100 )
    {
        $this->sample   = $this->getSample();
        
        if( $this->sample > $sample ){
            $this->run_test = false;
            $this->dd( "No test for " . get_called_class() . ". Global sample was " . $this->sample . "%" );
        }

        return $this;
    }


    /**
     * Sets the control variable name. This is not required.
     * If there is no control assigned, a new default control name will be added like:
     * 
     * ```
     * $this->control = get_called_class() . '_control';
     * ```
     *
     * @param string $control
     * @return AbstractTest
     */
    protected function control( string $control )
    {
        $this->control   = $control;

        return $this;
    }


    /**
     * Determines the AB test result, at least 2 groups are needed, A and B, and
     * adds the group's value to the control variable.
     *
     * @param array $groups
     * @return AbstractTest
     */
    protected function groups( array $groups )
    {
        if( count($groups) < 2  ){
            $this->dd( "At least 2 groups are required to perform this test. " . count($groups) . " group was provided." );
            $this->run_test = false;
            
            return $this;
        }

        $this->group_sample = $this->getSample();

        asort($groups);

        foreach($groups as $control_value => $sample){

            if( $this->group_sample <= $sample ){
                $this->control_value = $control_value;
                break;
            }

        }

        return $this;
    }


    /**
     * It validates all elements from $rules as ORs. Each element is an array of ANDs.
     *
     * @param string $new_control_value
     * @param array $rules
     * @return AbstratTest
     */
    protected function rules( $new_control_value, array $rules )
    {
        $tmp_rule_statements = [];

        # Validate ANDs
        foreach( $this->rule_statements as $index => $rule ){
            
            $tmp_statement = true;

            foreach( $rule as $statement ){

                $tmp_rule_statements[ $index ] = (bool) ($tmp_statement & $statement);
                $tmp_statement = $tmp_rule_statements[ $index ];

            }

        }

        # Validates ORs
        foreach( $tmp_rule_statements as $statement ){
            $tmp_statement  = $tmp_statement | $statement;
        }

        # Set the new control value when all the rules complied correctly
        if( $tmp_statement )
            $this->control_value = $new_control_value;

        return $this;
    }


    /**
     * This is the actual method that runs/returns the control value and places it into
     * the global payload.
     *
     * @return void
     */
    protected function dispatch()
    {
        /* TODO: 
            - It needs to walk all the payload indexes.
        */
        if( $this->run_test ){

            $this->control = ( !empty($this->control) ) ? $this->control : get_called_class() . '_control';

            $this->payload[ $this->control ] = $this->control_value;

            $this->dd( "Correctly dispatched " . get_called_class() . ", group sample: " . $this->group_sample 
                        . ", control: '" . $this->control . "', "
                        . "control value: '" . $this->control_value . "'. "
                        . "Global sample was " . $this->sample );
        }
    }


    /**
     * Assigns a value from payload by the index to $this->current_index_value.
     *
     * @param string $index
     * @return AbstractTest
     */
    protected function dataFrom( $index )
    {
        if( isset($this->payload[ $index ]) ){
            $this->current_index        = $index;
            $this->current_index_value  = $this->payload[ $index ];

            end($this->rule_statements);
            $this->statement_pointer    = key($this->rule_statements);
            $this->statement_pointer    = ($this->statement_pointer === NULL) ? 0 : $this->statement_pointer + 1;

            return $this;
        }

        $this->dd( "Index '" . $index . "' does not exist in the payload." );
    }


    /**
     * Compares (non-strictly) 2 values, then assigns the result as booelan to $this->status_value.
     *
     * @param string $value
     * @return AbstractTest
     */
    protected function equalTo( $value = '' )
    {
        $this->rule_statements[ $this->statement_pointer ][]  = (bool) ($this->current_index_value == $value);

        return $this;
    }


    /**
     * Compares (non-strictly) 2 values, then assigns the result as booelan to $this->status_value.
     *
     * @param string $value
     * @return AbstractTest
     */
    protected function notEqualTo( $value = '' )
    {
        $this->rule_statements[ $this->statement_pointer ][]  = (bool) ($this->current_index_value != $value);

        return $this;
    }


    /**
     * Looks for $value in the current value, then assigns the result as booelan to rule_statements array.
     *
     * @param string $value
     * @return AbstractTest
     */
    protected function has( $value = '' )
    {
        $this->rule_statements[ $this->statement_pointer ][]  = (bool) ( strpos($this->current_index_value, $value) !== false );

        return $this;
    }


    /**
     * Validates that if $value DOES NOT EXIST in current value, then assigns the result as booelan to rule_statements array.
     *
     * @param string $value
     * @return AbstractTest
     */
    protected function notHas( $value = '' )
    {
        $this->rule_statements[ $this->statement_pointer ][]  = (bool) ( strpos($this->current_index_value, $value) === false );

        return $this;
    }


    /**
     * Returns a random integer based on mt_rand.
     *
     * @return integer
     */
    protected function getSample() : int
    {
        return mt_rand(1, 100);
    }


    /**
     * Outputs a message and/or exits the application.
     * Add Log activity or any other action before exiting.
     *
     * @param string $msg
     * @return void
     */
    protected function dd( $msg = '' )
    {
        print_r( "\t" . $msg . "\n" );
    }

}