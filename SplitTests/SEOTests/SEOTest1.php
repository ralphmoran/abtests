<?php

use Classes\AbstractTest;

class SEOTest1 extends AbstractTest
{

    /**
     * Dispatches/performs this test.
     *
     * @return void
     */
    public function run()
    {
        $this->sample(30)
            ->control('UDF10')
            ->groups([
                'seoA'  => 10,
                'seoB'  => 20,
                'seoC'  => 30,
            ])
            ->rules(
                'seoTest',
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