<?php

# Autoload classes and traits
include( 'Traits/Chainable.php' );
include( 'Classes/AbstractTest.php' );
include( 'Classes/SplitTestHandler.php' );


####################################################


// Sending directly a payload (highest priority)
SplitTestHandler::payload([
                        'username'  => 'ctest',
                        'email'     => 'ctest_rb_3@f4f1click.com',
                        'sitekey'   => 'flirt4free',
                    ])
                    ->handle([
                        'SplitTests/UATest1',
                        'SplitTests/BillingTests/BillingTest1',
                        'SplitTests/FETests/FETest1',
                    ]);

exit;