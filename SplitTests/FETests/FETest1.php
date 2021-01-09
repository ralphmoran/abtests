<?php

use Classes\AbstractTest;

class FETest1 extends AbstractTest
{

    protected $active = false;

    /**
     * Dispatches/performs this test.
     *
     * @return void
     */
    public function run()
    {
        $this->sample(70)
            ->control('UDF02')
            ->groups([
                'feA'  => 100,
                'feB'  => 66,
                'feC'  => 33,
            ])
            ->rules(
                'feTest',
                [
                    $this->dataFrom( 'username' )
                        ->equalTo( 'ctest' ),

                    $this->dataFrom( 'sitekey' )
                        ->equalTo( 'xvc' ),
                ])
            ->dispatch();
    }

}