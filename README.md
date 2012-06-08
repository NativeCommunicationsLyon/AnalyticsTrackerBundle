AnalyticsTrackerBundle
======================

Automatically adds analytics trackers to your Symfony2 website.

Installation
------------

First, you need to add the bundle in your project:

    $ git submodule add git://github.com/jirafe/AnalyticsTrackerBundle.git vendor/bundles/Jirafe/Bundle/AnalyticsTrackerBundle

Then, add it to the autoloader:

    // app/autoload.php
    $loader->registerNamespaces(array(

        // ... other namespaces

        'Jirafe'                         => __DIR__ . '/../vendor/bundles',
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
        environments: [prod, whatever_env_you_have, ...] # optional parameter, set globaly the environments where the trackers are enabled
        trackers:
            tracker_a:
                type:       jirafe
                params:
                    site_id:    123                     # id of the site to track
                environments: [dev, whatever_env_you_have, ...] # optional parameter, set locally (for this tracker only) the environments where the tracker is enabled
            tracker_b:
                type:       piwik
                params:
                    url:        'http://demo.piwik.org' # url of the piwik application
                    site_id:    123                     # id of the site to track
            tracker_c:
                type:       google_analytics
                params:
                    account:    GA-XXXXXXXX             # the GA account