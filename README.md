PiwikBundle
===========

Automatically adds a piwik tracker to your Symfony2 website.

Installation
------------

First, you need to add the bundle in your project:

    $ git add submodule git://github.com/jirafe/PiwikBundle.git vendor/bundles/Jirafe/Bundle/PiwikBundle

Then, add it to the autoloader:

    // app/autoload.php
    $loader->registerNamespaces(array(

        // ... other namespaces

        'Jirafe'                         => __DIR__ . '/../src',
        'Mailchimp'                      => __DIR__ . '/../vendor/mailchimp/src',
    ));

Add the bundle to your kernel:

    // app/AppKernel.php
    
    $bundles = array(
        
        // ... other bundles

        new Jirafe\Bundle\PiwikBundle\JirafePiwikBundle(),
    );

Finally, configure it:

    # app/config/config.yml
    # Piwik Configuration
    jirafe_piwik_tracker:
        url:        'http://demo.piwik.org' # url of the piwik application
        site_id:    123                     # id of the site to track
