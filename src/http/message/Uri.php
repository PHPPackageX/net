<?php
namespace PHPPackageX\net\http\message;

use PHPPackageX\copy\traits\DeepCopyTrait;
use PHPPackageX\net\http\message\traits\UriFilterTrait;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    use DeepCopyTrait;
    use UriFilterTrait;

    const HTTP_DEFAULT_HOST = 'localhost';

    private static $replaceQuery = ['=' => '%3D', '&' => '%26'];

    private static $SchemeDefaultPorts = [
        'http'   => 80,
        'https'  => 443,
        'ftp'    => 21,
        'gopher' => 70,
        'nntp'   => 119,
        'news'   => 119,
        'telnet' => 23,
        'tn3270' => 23,
        'imap'   => 143,
        'pop'    => 110,
        'ldap'   => 389,
    ];

    private $scheme   = '';
    private $userInfo = '';
    private $host     = '';
    private $port;
    private $path     = '';
    private $query    = '';
    private $fragment = '';

    /**
     * @param string $uri
     * @link  https://www.php.net/manual/zh/function.parse-url.php
     */
    public function __construct(string $uri)
    {
        if (!empty($uri) && $components = parse_url($uri)) {

            if (isset($components['scheme'])) {
                $this->scheme = $this->filterScheme($components['scheme']);
            }

            if (isset($components['user'])) {
                $this->userInfo = $this->filterUserInfoComponent($components['user']);
            }

            if (isset($components['pass'])) {
                $this->userInfo .= ':' . $this->filterUserInfoComponent($components['pass']);
            }

            if (isset($components['host'])) {
                $this->host = $this->filterHost($components['host']);
            }

            if (isset($components['port'])) {
                $this->port = $this->filterPort($components['port']);
            }

            if (isset($components['path'])) {
                $this->path = $this->filterPath($components['path']);
            }

            if (isset($components['query'])) {
                $this->query = $this->filterQueryAndFragment($components['query']);
            }

            if (isset($components['fragment'])) {
                $this->fragment = $this->filterQueryAndFragment($components['fragment']);
            }

            $this->removeDefaultPort();
        }
    }

    /**
     * @return string
     */
    public function getAuthority()
    {
        $authority = $this->host;

        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * @param  string $fragment
     * @return $this|Uri|static
     */
    public function withFragment($fragment)
    {
        $fragment = $this->filterQueryAndFragment($fragment);

        if ($this->fragment === $fragment) {
            return $this;
        }

        return $this->deepCopy(['fragment' => $fragment]);
    }

    /**
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @param  string $host
     * @return $this|static
     */
    public function withHost($host)
    {
        $host = $this->filterHost($host);

        if ($this->host === $host) {
            return $this;
        }

        $copy = $this->deepCopy(['host' => $host]);
        $copy->validateState();

        return $copy;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param  string $path
     * @return $this|static
     */
    public function withPath($path)
    {
        $path = $this->filterPath($path);

        if ($this->path === $path) {
            return $this;
        }

        $copy = $this->deepCopy(['path' => $path]);
        $copy->validateState();

        return $copy;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param  int|null $port
     * @return $this|Uri|static
     */
    public function withPort($port)
    {
        $port = $this->filterPort($port);

        if ($this->port === $port) {
            return $this;
        }

        $copy = $this->deepCopy(['port' => $port]);
        $copy->removeDefaultPort();
        $copy->validateState();

        return $copy;
    }

    /**
     * @return int|null
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param  string $query
     * @return $this|static
     */
    public function withQuery($query)
    {
        $query = $this->filterQueryAndFragment($query);

        if ($this->query === $query) {
            return $this;
        }

        return $this->deepCopy(['query' => $query]);
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param  string $scheme
     * @return $this|static
     */
    public function withScheme($scheme)
    {
        $scheme = $this->filterScheme($scheme);

        if ($this->scheme === $scheme) {
            return $this;
        }

        $copy = $this->deepCopy(['scheme' => $scheme]);
        $copy->removeDefaultPort();
        $copy->validateState();

        return $copy;
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @param  string $user
     * @param  null   $password
     * @return $this|static
     */
    public function withUserInfo($user, $password = null)
    {
        $userInfo = $this->filterUserInfoComponent($user);

        if ($password !== null) {
            $userInfo .= ':' . $this->filterUserInfoComponent($password);
        }

        if ($this->userInfo === $userInfo) {
            return $this;
        }

        $copy = $this->deepCopy(['userInfo' => $userInfo]);
        $copy->validateState();

        return $copy;
    }

    /**
     * @return string
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    public function __toString()
    {
        $uri = '';

        if ($this->scheme != '') {
            $uri .= $this->scheme . ':';
        }

        $authority = $this->getAuthority();

        if ($authority != ''|| $this->scheme === 'file') {
            $uri .= '//' . $authority;
        }

        $uri .= $this->path;

        if ($this->query != '') {
            $uri .= '?' . $this->query;
        }

        if ($this->fragment != '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }

    /**
     * 标准方案删除默认端口
     */
    private function removeDefaultPort()
    {
        if ($this->port !== null && self::isDefaultPort($this)) {
            $this->port = null;
        }
    }

    public static function isDefaultPort(UriInterface $uri)
    {
        return $uri->getPort() === null
            || (isset(self::$SchemeDefaultPorts[$uri->getScheme()]) && $uri->getPort() === self::$SchemeDefaultPorts[$uri->getScheme()]);
    }

    private function validateState()
    {
        if ($this->host === '' && ($this->scheme === 'http' || $this->scheme === 'https')) {
            $this->host = self::HTTP_DEFAULT_HOST;
        }

        if ($this->getAuthority() === '') {
            if (0 === strpos($this->path, '//')) {
                throw new \InvalidArgumentException('The path of a URI without an authority must not start with two slashes "//"');
            }
            if ($this->scheme === '' && false !== strpos(explode('/', $this->path, 2)[0], ':')) {
                throw new \InvalidArgumentException('A relative URI must not have a path beginning with a segment containing a colon');
            }
        } elseif (isset($this->path[0]) && $this->path[0] !== '/') {
            @trigger_error(
                'The path of a URI with an authority must start with a slash "/" or be empty. Automagically fixing the URI ' .
                'by adding a leading slash to the path is deprecated since version 1.4 and will throw an exception instead.',
                E_USER_DEPRECATED
            );
            $this->path = '/'. $this->path;
        }
    }
}