<?php

/*
 * This file is part of the AnalyticsTrackerBundle.
 * (c) 2011 Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jirafe\Bundle\AnalyticsTrackerBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\UrlValidator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

class JirafeAnalyticsTrackerExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->process($configuration->getConfigTree(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('analytics_tracker.xml');

        foreach ($config['trackers'] as $name => $tracker) {
            $this->loadTracker($name, $tracker, $container);
        }
    }

    public function loadTracker($name, array $tracker, ContainerBuilder $container)
    {
        switch ($tracker['type']) {
            case 'jirafe':
                $this->loadJirafeTracker(
                    $name,
                    $tracker['class'],
                    $tracker['template'],
                    $tracker['params'],
                    $container
                );
                break;
            case 'piwik':
                $this->loadPiwikTracker(
                    $name,
                    $tracker['class'],
                    $tracker['template'],
                    $tracker['params'],
                    $container
                );
                break;
            case 'google_analytics':
                $this->loadGoogleAnalyticsTracker(
                    $name,
                    $tracker['class'],
                    $tracker['template'],
                    $tracker['params'],
                    $container
                );
                break;
            default:
                throw new \InvalidArgumentException(sprintf('The \'%s\' tracker type is not supported.', $tracker['type']));
        }
    }

    public function loadPiwikTracker($name, $class, $template, array $params, ContainerBuilder $container)
    {
        $this->ensureParameters($name, array('url', 'site_id'), $params);

        if (!$this->isUrlValid($params['url'])) {
            throw new \Exception(sprintf('The specified url \'%s\' is not valid for the \'%s\' tracker.', $name));
        }

        $class = $class ? : $container->getParameter('jirafe.analytics_tracker.class');
        $template = $template ? : $container->getParameter('jirafe.analytics_tracker.piwik.template');

        $this->addTrackerDefinition($name, $class, $template, $params, $container);
    }

    public function loadGoogleAnalyticsTracker($name, $class, $template, array $params, ContainerBuilder $container)
    {
        $this->ensureParameters($name, array('account'), $params);

        $class = $class ? : $container->getParameter('jirafe.analytics_tracker.class');
        $template = $template ? : $container->getParameter('jirafe.analytics_tracker.google_analytics.template');

        $this->addTrackerDefinition($name, $class, $template, $params, $container);
    }

    public function loadJirafeTracker($name, $class, $template, array $params, ContainerBuilder $container)
    {
        $this->ensureParameters($name, array('site_id'), $params);

        $class = $class ? : $container->getParameter('jirafe.analytics_tracker.class');
        $template = $template ? : $container->getParameter('jirafe.analytics_tracker.jirafe.template');

        $this->addTrackerDefinition($name, $class, $template, $params, $container);
    }

    /**
     * Adds a tracker definition to the given container builder
     *
     * @param  string           $name      The name of the tracker
     * @param  string           $class     The listener class
     * @param  string           $template  The template of the tracker
     * @param  array            $params    The parameters for the template
     * @param  ContainerBuilder $container A ContainerBuilder instance
     */
    protected function addTrackerDefinition($name, $class, $template, $params, ContainerBuilder $container)
    {
        $params['name'] = $name;

        $templating = new Reference('templating.engine.twig');
        $definition = new Definition($class, array($templating, $template, $params));
        $definition->addTag('kernel.event_listener', array(
            'event' => 'kernel.response',
            'method' => 'onKernelResponse'
        ));

        $container->setDefinition(
            sprintf('jirafe.analytics_tracker.%s_tracker', $name),
            $definition
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
    protected function ensureParameters($name, array $keys, array $params)
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
