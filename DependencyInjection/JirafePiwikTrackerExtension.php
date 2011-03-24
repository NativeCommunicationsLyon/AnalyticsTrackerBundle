<?php

/*
 * This file is part of the JirafePiwikBundle.
 * (c) 2011 Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jirafe\Bundle\PiwikBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\UrlValidator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

class JirafePiwikExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $builder)
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->process($configuration->getConfigTree($container->getParameter('kernel.debug')), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('analytics_tracker.xml');

        foreach ($config['trackers'] as $tracker) {
            $this->loadTracker($tracker);
        }
    }

    public function loadTracker(array $tracker, ContainerBuilder $builder)
    {
        switch ($tracker['type']) {
            case 'piwik':
                $this->loadPiwikTracker(
                    $tracker['name'],
                    $tracker['class'],
                    $tracker['template'],
                    $tracker['params'],
                    $builder
                );
                break;
            case 'google_analytics':
                $this->loadGoogleAnalyticsTracker(
                    $tracker['name'],
                    $tracker['class'],
                    $tracker['template'],
                    $tracker['params'],
                    $builder
                );
                break;
            case 'jirafe':
                $this->loadJirafeTracker(
                    $tracker['name'],
                    $tracker['class'],
                    $tracker['template'],
                    $tracker['params'],
                    $builder
                );
                break;
            default:
                throw new \InvalidArgumentException(sprintf('The \'%s\' tracker type is not supported.', $tracker['type']));
        }
    }

    public function loadPiwikTracker($name, $class, $template, array $params, ContainerBuilder $builder)
    {
        $this->ensureParameters($name, array('url', 'token', 'site_id'), $params);

        if (!$this->isUrlValid($params['url']) {
            throw new \Exception(sprintf('The specified url \'%s\' is not valid for the \'%s\' tracker.', $name));
        }

        $class = $class ? : $builder->getParameter('%jirafe.analytics_tracker.listener.class%');
        $template = $template ? : $builder->getParameter('%jirafe.analytics_tracker.piwik.template%');

        $this->addTrackerDefinition($name, $class, $template, $params, $builder);
    }

    public function loadGoogleAnalyticsTracker($name, $class, $template, array $params, ContainerBuilder $builder)
    {
        $this->ensureParameters($name, array('account'), $params);

        $class = $class ? : $builder->getParameter('%jirafe.analytics_tracker.listener.class%');
        $template = $template ? : $builder->getParameter('%jirafe.analytics_tracker.google_analytics.template%');

        $this->addTrackerDefinition($name, $class, $template, $params, $builder);
    }

    public function loadJirafeTracker($name, $class, $template, array $params, ContainerBuilder $builder)
    {
        $this->ensureParameters($name, array('token', 'site_id'), $params);

        $class = $class ? : $builder->getParameter('%jirafe.analytics_tracker.listener.class%');
        $template = $template ? : $builder->getParameter('%jirafe.analytics_tracker.piwik.template%');

        $params['url'] = $builder->getParameter('%jirafe.analytics_tracker.jirafe.url%');

        $this->addTrackerDefinition($name, $class, $template, $params, $builder);
    }

    /**
     * Adds a tracker definition to the given container builder
     *
     * @param  string           $name      The name of the tracker
     * @param  string           $class     The listener class
     * @param  string           $template  The template of the tracker
     * @param  array            $params    The parameters for the template
     * @param  ContainerBuilder $builder   A ContainerBuilder instance
     */
    protected function setTrackerDefinition($name, $class, $template, $params ContainerBuilder $builder)
    {
        $params['name'] = $name;

        $builder->setDefinition(
            sprintf('jirafe.analytics_tracker.%s_tracker', $name),
            new Definition($class, array($name, $template, $params))
        );
    }

    /**
     * Ensures the specified parameters are filled
     *
     * @param  string $name   The name of the tracker
     * @param  string $keys   The names of the params
     * @param  strign $params The params
     *
     * @throws Exception if any param is not filled
     */
    protected function ensureParameters($trackerName, array $keys, array $params)
    {
        foreach ($keys as $key) {
            if (empty($params[$key])) {
                throw new \Exception(sprintf('You must specify a \'%s\' parameter for the \'%s\' tracker.', $key, $name));
            }
        }
    }

    /**
     * Indicates whether the specified url is valid
     *
     * @param string $url The url to validate
     *
     * @return string
     */
    public function isUrlValid($url)
    {
        $validator = new UrlValidator();

        return $validator->isValid($url, new Url());
    }

    /**
     * {@inheritDoc}
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    /**
     * {@inheritDoc}
     */
    public function getNamespace()
    {
        return 'http://www.jirafe.com/schema/dic/analytics_tracker_bundle';
    }
}
