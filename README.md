AnalyticsTrackerBundle
======================

Automatically adds analytics trackers to your Symfony2 website.

Installation
------------

First, you need to add the bundle in your project:

    $ git add submodule git://github.com/jirafe/AnalyticsTrackerBundle.git vendor/bundles/Jirafe/Bundle/AnalyticsTrackerBundle

Then, add it to the autoloader:

    // app/autoload.php
    $loader->registerNamespaces(array(

        // ... other namespaces

        'Jirafe'                         => __DIR__ . '/../src',
    ));

Add the bundle to your kernel:

    // app/AppKernel.php
    
    $bundles = array(
        
        // ... other bundles

        new Jirafe\Bundle\AnalyticsTrackerBundle\JirafeAnalyticsTrackerBundle(),
    );

Finally, configure it:

    # app/config/config.yml
    # Analytics Tracker Configuration
    jirafe_analytics_tracker:
        trackers:
            tracker_a:
                type:       piwik
                url:        'http://demo.piwik.org' # url of the piwik application
                site_id:    123                     # id of the site to track
            tracker_b:
                type:       google_analytics
                account:    GA-XXXXXXXX             # the GA account
            tracker_c:
                type:       jirafe
                site_id:    123                     # id of the site to track
