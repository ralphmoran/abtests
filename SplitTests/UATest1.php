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
        $this->sample(50)
            ->control('UDF05')      // If the control variable does not exist, it's added to the payload.
            ->groups([
                'groupA'  => 20,    //
                'groupB'  => 100,   //  
                'groupC'  => 40,    //   All groups are sorted by values.
                'groupD'  => 80,    //
                'groupE'  => 60,    //
            ])
            ->rules(
                'CustomValue',
                [
                    $this->dataFrom( 'username' )   //
                        ->notEqualTo( 'ctest' )     // ($username != 'ctest') AND (strpos($username, 'tea') === false)
                        ->notHas( 'tea' ),          //
                                                    //
                                                    //                          OR
                                                    //
                    $this->dataFrom( 'username' )   //
                        ->equalTo( 'rtest' )        // ($username == 'rtest') AND (strpos($username, 'tesw') !== false)
                        ->has( 'tesw' ),            //
                ])
            ->dispatch();
    }

}