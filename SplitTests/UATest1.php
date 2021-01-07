<?php

use Classes\AbstractTest;

class UATest1 extends AbstractTest
{

    /**
     * Dispatches/performs this test.
     *
     * @return void
     */
    public function run()
    {
        $this->sample(50)           # In this example, this AB test takes only 50% of the general traffic
            ->control('UDF05')      # If the control variable does not exist, it's added to the payload.
            ->groups([
                'groupA'  => 20,    #
                'groupB'  => 100,   # All groups are sorted by values.
                'groupC'  => 40,    # Indexes: 'groupA', 'groupB', 'groupC', etc., are the possible values for control 'UDF05'.
                'groupD'  => 80,    #
                'groupE'  => 60,    #
            ])
            ->rules(
                'CustomValue',      # 'CustomValue' is going to be the actual value for control 'UDF05' only if the next rules are correct.
                [
                    $this->dataFrom( 'username' )   #
                        ->notEqualTo( 'ctest' )     # ($username != 'ctest') AND (strpos($username, 'tea') === false)
                        ->notHas( 'tea' ),          #
                                                    #                          OR
                    $this->dataFrom( 'username' )   #
                        ->equalTo( 'rtest' )        # ($username == 'rtest') AND (strpos($username, 'tesw') !== false)
                        ->has( 'tesw' ),            #
                ])
            ->dispatch();           # This is the actual method that runs/returns the control value and places it into the global payload.
    }

}