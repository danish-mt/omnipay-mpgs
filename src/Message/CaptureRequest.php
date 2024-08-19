<?php

namespace Omnipay\Mpgs\Message;

/**
 * Capture Request
 * @link https://na-gateway.mastercard.com/api/documentation/apiDocumentation/rest-json/version/82/operation/Transaction%3a%20%20Capture.html?locale=en_US
 */
class CaptureRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('orderId');

        return [];
    }

    public function sendData($data)
    {
        $httpResponse = $this->httpClient->request('GET', $this->getEndpoint(), $this->getHeaders());

        $body = json_decode($httpResponse->getBody()->getContents(), true);

        return $this->createResponse($body, $httpResponse->getHeaders(), $httpResponse->getStatusCode());
    }

    public function createResponse($data, $headers = [], $status = 404)
    {
        return $this->response = new CaptureResponse($this, $data, $headers, $status);
    }

    public function getEndpoint()
    {
        return parent::getApiBaseUrl() . "/order/{$this->getOrderId()}/transaction/{$this->getTransactionId()}";
    }
}
