<?php

/*
 * This file is part of the NavBundle.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace NavBundle\Connection;

use jamesiarmes\PhpNtlm\SoapClient;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 * @author Peter Philipp <info@das-peter.ch>
 *
 * TODO: Waiting for https://github.com/jamesiarmes/php-ntlm/pull/11.
 *
 * @method object Read(array $criteria)
 * @method object ReadMultiple(array $criteria)
 * @method object Create(array $criteria)
 * @method object Update(array $criteria)
 * @method object Delete(array $criteria)
 */
class Connection extends SoapClient implements ConnectionInterface, WarmableInterface
{
    /**
     * Cache for fetched WSDLs.
     *
     * @var array
     */
    protected static $wsdlCache = [];
    protected $wsdl;

    public function __construct(string $wsdl, array $options = null)
    {
        $this->options = $options + [
            'curlopts' => [],
            'strip_bad_chars' => true,
            'warn_on_bad_chars' => true,
            'cache_dir' => sys_get_temp_dir(),
            'wsdl_cache_enabled' => ini_get('soap.wsdl_cache_enabled'),
            'soap_version' => \SOAP_1_1,
        ];
        $this->wsdl = $wsdl;

        parent::__construct($this->fetchWSDL($wsdl), $options);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \SoapFault
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function __call($functionName, $arguments)
    {
        // Useless try/catch, but prevents a segfault
        try {
            return parent::__call($functionName, $arguments);
        } catch (\SoapFault $fault) {
            throw $fault;
        }
    }

    public function hasFunction(string $function): bool
    {
        return \in_array($function, array_map(static function (string $fct): string {
            return preg_replace('/^[^ ]+ (.*)\(.*\)$/', '$1', $fct);
        }, $this->__getFunctions()), true);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \SoapFault
     */
    public function warmUp($cacheDir): array
    {
        return [$this->fetchWSDL($this->wsdl, true)];
    }

    /**
     * Fetch the WSDL to use.
     *
     * We need to fetch the WSDL on our own and save it into a file so that the parent class can load it from there.
     * This is because the parent class doesn't support overwriting the WSDL fetching code which means we can't add
     * the required NTLM handling.
     *
     * @throws \SoapFault
     */
    protected function fetchWSDL(string $wsdl, bool $force = false): string
    {
        if (empty($wsdl) || file_exists($wsdl)) {
            return $wsdl;
        }

        if (empty(self::$wsdlCache[$wsdl])) {
            $tempFile = $this->options['cache_dir'].'/'.md5($wsdl).'.wsdl';
            if (!is_dir($this->options['cache_dir'])) {
                mkdir($this->options['cache_dir'], 0777, true);
            }

            if (!file_exists($tempFile) || \WSDL_CACHE_NONE === $this->options['wsdl_cache_enabled'] || true === $force) {
                $wsdlContents = parent::__doRequest('', $wsdl, '', $this->options['soap_version']);
                // Ensure the WSDL is only stored after validating it roughly.
                if (curl_errno($this->ch) || false === strpos($wsdlContents, '<definitions ')) {
                    throw new \SoapFault('Server', sprintf('Unable to fetch a valid WSDL definition from: %s', $wsdl));
                }

                file_put_contents($tempFile, $wsdlContents);
            }

            self::$wsdlCache[$wsdl] = $tempFile;
        }

        return self::$wsdlCache[$wsdl];
    }

    /**
     * {@inheritdoc}
     */
    protected function buildHeaders($action): array
    {
        if (empty($action)) {
            return [
                'Method: GET',
                'Connection: Keep-Alive',
                'User-Agent: PHP-SOAP-CURL',
                'Content-Type: text/xml; charset=utf-8',
            ];
        }

        return parent::buildHeaders($action);
    }
}
