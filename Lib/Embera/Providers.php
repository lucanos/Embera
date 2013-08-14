<?php
/**
 * Providers.php
 *
 * @package Embera
 * @author Michael Pratt <pratt@hablarmierda.net>
 * @link   http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Embera;

/**
 * Finds and loads Services/Providers based on the
 * host of a url.
 */
class Providers
{
    /** @var array Configuration Settings */
    protected $config = array();

    /** @var array The mapping of host -> provider class relation. */
    protected $services = array(
        'youtube.com' => '\Embera\Providers\Youtube',
        'youtu.be' => '\Embera\Providers\Youtube',
        'vimeo.com' => '\Embera\Providers\Vimeo',
        'twitter.com' => '\Embera\Providers\Twitter',
        'qik.com' => '\Embera\Providers\Qik',
        'revision3.com' => '\Embera\Providers\Revision3',
        'dailymotion.com' => '\Embera\Providers\DailyMotion',
        'viddler.com' => '\Embera\Providers\Viddler',
        'flickr.com' => '\Embera\Providers\Flickr',
        'flic.kr' => '\Embera\Providers\Flickr',
        'hulu.com' => '\Embera\Providers\Hulu',
        'jest.com' => '\Embera\Providers\Jest',
        'my.opera.com' => '\Embera\Providers\MyOpera',
        'deviantart.com' => '\Embera\Providers\Deviantart',
        'fav.me' => '\Embera\Providers\Deviantart',
        'sta.sh' => '\Embera\Providers\Deviantart',
        'collegehumor.com' => '\Embera\Providers\CollegeHumor'
    );

    /**
     * Construct
     *
     * @param array $config       Associative array with configuration options
     * @param object $oembed      Instance of \Embera\Oembed
     * @return void
     */
    public function __construct(array $config = array(), \Embera\Oembed $oembed = null)
    {
        $this->oembed = $oembed;
        $this->config = $config;
    }

    /**
     * Finds services for the given $urls.
     *
     * @param array $urls An array with all the available urls
     * @return array An Array with loaded services
     */
    protected function findServices(array $urls = array())
    {
        $return = array();
        if (!empty($urls))
        {
            foreach (array_unique($urls) as $u)
            {
                try {
                    $host = $this->getHost($u);
                    if (isset($this->services[$host]))
                    {
                        $provider = new \ReflectionClass($this->services[$host]);
                        $return[$u] = $provider->newInstance($u, $this->config, $this->oembed);
                    }
                } catch (\Exception $e) {}
            }
        }

        return $return;
    }

    /**
     * Adds a new Provider into the service map
     *
     * @param string $host The host for the map
     * @param string|object $class The class or object that should manage the provider
     * @param array $params Custom parameters that should be sent in the url for this Provider
     * @return void
     */
    public function addProvider($host, $class, array $params = array())
    {
        $host = strtolower($host);
        $this->services[$host] = $class;
    }

    /**
     * Gets a normalized host for the given $url
     *
     * @param string $url
     * @return string
     *
     * @throws InvalidArgumentException when the url seems to be invalid
     */
    protected function getHost($url)
    {
        $data = parse_url($url);
        if (empty($data['host']))
            throw new \InvalidArgumentException('The Url: ' . $url . ' seems to be invalid');

        if (preg_match('~^(?:.*)\.(deviantart)\.(com)$~i', $data['host'], $m))
            return strtolower($m['1'] . '.' . $m['2']);

        return preg_replace('~^(?:www|player)\.~i', '', strtolower($data['host']));
    }

    /**
     * Returns an array with all valid services found.
     *
     * @param array|string $urls  An array with urls or a url string
     * @return array
     */
    public function getAll($urls) { return $this->findServices((array) $urls); }
}

?>
