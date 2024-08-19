<?php

namespace Omnipay\Mpgs\Message;

/**
 * Mpgs Purchase Request
 *
 * @link https://na-gateway.mastercard.com/api/documentation/apiDocumentation/rest-json/version/82/operation/Transaction%3a%20%20Authorize.html?locale=en_US
 */
class AuthorizeRequest extends AbstractRequest
{
    protected $operation = 'AUTHORIZE';

    protected $action = 'session';

    public function getData()
    {
        $this->validate('currency', 'amount', 'returnUrl', 'transactionId');

        return array_merge($this->getBaseData(), [
            'apiOperation' => 'INITIATE_CHECKOUT',
            'order' => [
                'id' => $this->getTransactionId(),
                'amount' => $this->getAmount(),
                'currency' => $this->getCurrency(),
                'description' => "Paying for the order - {$this->getTransactionReference()}",
                'reference' => $this->getTransactionReference(),
                'invoiceNumber' => $this->getTransactionReference(),
                'notificationUrl' => $this->getNotifyUrl(),
            ],
            'customer' => [
                'email' => $this->getCard()->getEmail(),
                'mobilePhone' => $this->getCard()->getBillingPhone(),
                'firstName' => $this->getCard()->getFirstName(),
                'lastName' => $this->getCard()->getLastName(),
            ],
            'billing' => [
                'address' => [
                    'city' => $this->getCard()->getBillingCity(),
                    'country' => $this->getCard()->getBillingCountry(),
                    'postcodeZip' => $this->getCard()->getBillingPostcode(),
                    'stateProvince' => $this->getCard()->getBillingState(),
                    'street' => $this->getCard()->getBillingAddress1(),
                    'street2' => $this->getCard()->getBillingAddress2(),
                ],
            ],
        ]);
    }

    public function filterDataRecursively($array = [])
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = $this->filterDataRecursively($value);
            }
        }

        return array_filter($array);
    }

    public function sendData($data)
    {
        $data = $this->filterDataRecursively($data);

        $httpResponse = $this->httpClient->request(
            'POST',
            $this->getEndpoint(),
            $this->getHeaders(),
            json_encode($data)
        );

        $body = json_decode($httpResponse->getBody()->getContents(), true);

        return $this->createResponse($body, $httpResponse->getHeaders(), $httpResponse->getStatusCode());
    }

    public function createResponse($data, $headers = [], $status = 404)
    {
        return $this->response = new AuthorizeResponse($this, $data, $headers, $status);
    }
}
