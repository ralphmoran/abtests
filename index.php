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
                        'SplitTests/BillingTests/BillingTest1', # This test will not run because it's not compatible with .
                                                                # 'SplitTests/SEOTests/SEOTest1' and 'SplitTests/BillingTests/BillingTest1'.
                                                                # Please, take a look at SplitTests/BillingTests/BillingTest1.php class.
                    ])
                    ->get(true);

// $ABTestHandler->payload([
//                         'username'  => 'ctest',
//                         'email'     => 'ctest_rb_3@f4f1click.com',
//                         'sitekey'   => 'flirt4free',
//                     ])
//                     ->handle([
//                         'SplitTests/UATest1',
//                         'SplitTests/BillingTests/BillingTest1', # Sorting this test before calling its incompatible tests will make it run.
//                         'SplitTests/SEOTests/SEOTest1',         # This is happening due to the order that they have been set.
//                         'SplitTests/FETests/FETest1',
//                     ])
//                     ->get(true);

exit;