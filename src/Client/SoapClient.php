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

namespace NavBundle\Client;

use jamesiarmes\PhpNtlm\SoapClient as NtlmSoapClient;

/**
 * @author Peter Philipp <info@das-peter.ch>
 *
 * todo Waiting for https://github.com/jamesiarmes/php-ntlm/pull/11
 */
class SoapClient extends NtlmSoapClient
{
    /**
     * Cache for fetched WSDLs.
     *
     * @var array
     */
    protected static $wsdlCache = [];

    public function __construct($wsdl, array $options = null)
    {
        $this->options = $options + [
            'curlopts' => [],
            'strip_bad_chars' => true,
            'warn_on_bad_chars' => true,
            'cache_dir' => sys_get_temp_dir(),
        ];
        $wsdl = $this->__fetchWSDL($wsdl);

        parent::__construct($wsdl, $options);
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
    protected function __fetchWSDL(string $wsdl)
    {
        if (empty($wsdl) || file_exists($wsdl)) {
            return $wsdl;
        }
        $wsdlHash = md5($wsdl);

        if (empty(self::$wsdlCache[$wsdlHash])) {
            $tempFile = $this->options['cache_dir']."/$wsdlHash.wsdl";

            if (!file_exists($tempFile) || (isset($this->options['cache_wsdl']) && WSDL_CACHE_NONE === $this->options['cache_wsdl'])) {
                $wsdlContents = $this->__doRequest(null, $wsdl, null, $this->options['soap_version']);
                // Ensure the WSDL is only stored after validating it roughly.
                if (curl_errno($this->ch) || false === strpos($wsdlContents, '<definitions ')) {
                    throw new \SoapFault('Fetching WSDL', sprintf('Unable to fetch a valid WSDL definition from: %s', $wsdl));
                }

                file_put_contents($tempFile, $wsdlContents);
            }

            self::$wsdlCache[$wsdlHash] = $tempFile;
        }

        return self::$wsdlCache[$wsdlHash];
    }

    /**
     * {@inheritdoc}
     */
    protected function buildHeaders($action)
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
