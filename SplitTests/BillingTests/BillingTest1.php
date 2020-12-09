<?php

use Classes\AbstractTest;

class BillingTest1 extends AbstractTest
{

    /**
     * Dispatches/performs this test.
     *
     * @return void
     */
    public function run()
    {
        $this->sample(90)
            ->control('UDF07')
            ->groups([
                'billingA'  => 25,
                'billingB'  => 75,
                'billingC'  => 50,
                'billingD'  => 100,
            ])
            ->rules(
                'billingTest',
                [
                    $this->dataFrom( 'email' )
                        ->has( '_hbp_' )
                        ->has( '@f4f1click.com' ),

                    $this->dataFrom( 'sitekey' )
                        ->equalTo( 'flirt4free' ),
                ])
            ->dispatch();
    }

}