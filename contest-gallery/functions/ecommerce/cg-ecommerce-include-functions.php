<?php

    include(__DIR__.'/../ecommerce/backend/gallery/cg-ecommerce-get-orders.php');
    include(__DIR__.'/../ecommerce/backend/gallery/cg-ecommerce-sell-conf.php');
    include(__DIR__.'/../ecommerce/backend/gallery/cg-ecommerce-sell-activate.php');
    include(__DIR__.'/../ecommerce/backend/gallery/cg-ecommerce-sell-deactivate.php');
    include(__DIR__.'/../ecommerce/backend/gallery/cg-move-file-ecommerce-sell-folder.php');
    include(__DIR__.'/../ecommerce/backend/gallery/cg-move-file-from-ecommerce-sell-folder.php');

    include(__DIR__.'/../ecommerce/backend/render/cg-deactivate-ecommerce-question.php');
    include(__DIR__.'/../ecommerce/backend/render/cg-sell-ecommerce-container.php');
    include(__DIR__.'/../ecommerce/backend/render/cg-download-ecommerce-form.php');
    include(__DIR__.'/../ecommerce/backend/render/cg-deactivate-ecommerce-form.php');

    include(__DIR__.'/../ecommerce/backend/options/cg-create-get-ecommerce-options.php');
    include(__DIR__.'/../ecommerce/backend/options/cg-create-ecommerce-tables.php');
    include(__DIR__.'/../ecommerce/backend/options/cg-ecommerce-change-options-and-sizes.php');

    include(__DIR__.'/../ecommerce/backend/requests/requests-paypal-api/cg-get-paypal-data.php');
    include(__DIR__.'/cg-ecommerce-include-javascript.php');
    include (__DIR__.'/../ecommerce/backend/requests/requests-paypal-api/cg-paypal-get-access-token.php');
    include(__DIR__.'/general/cg-paypal-get-invoice-number-logic-result.php');
    include(__DIR__.'/general/cg-ecommerce-functions.php');



