# AB Tests package
It handles and dispatches AB tests.

```php
<?php

# Autoload classes and traits
include( 'Traits/logger.php' );
include( 'Classes/AbstractTest.php' );
include( 'Classes/SplitTestHandler.php' );

$ABTestHandler = new SplitTestHandler();

$ABTestHandler->payload([
                        'username'  => 'ctest',
                        'email'     => 'ctest_rb_3@f4f1click.com',
                        'sitekey'   => 'flirt4free',
                    ])
                    ->handle([
                        'SplitTests/UATest1',
                        'SplitTests/SEOTests/SEOTest1',
                        'SplitTests/FETests/FETest1',
                        'SplitTests/BillingTests/BillingTest1', # This test will not run because it's not compatible with:
                                                                #
                                                                # 'SplitTests/SEOTests/SEOTest1' and 
                                                                # 'SplitTests/BillingTests/BillingTest1'.
                                                                #
                                                                # Please, take a look at SplitTests/BillingTests/BillingTest1.php class.
                    ])
                    ->get(true);
```

## Each AB test must have the next structure:

### Normal AB test

```php
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
            // ->control('UDF05')      # If the control variable does not exist, it's added to the payload.
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

```

### Incompatibility AB test

```php
<?php

use Classes\AbstractTest;

class BillingTest1 extends AbstractTest
{
    protected $incompatibleWith = [     # This test will not run if these tests have been called/loaded.
        'SplitTests/SEOTests/SEOTest1',
        'SplitTests/FETests/FETest1',
    ];

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
```

### Deactivated AB test

```php
<?php

use Classes\AbstractTest;

class FETest1 extends AbstractTest
{
    protected $active = false; # By default this property is true from AbstractTest class.

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
```
