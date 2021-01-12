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
                        'SplitTests/BillingTests/BillingTest1',
                    ])
                    ->get(true);

exit;