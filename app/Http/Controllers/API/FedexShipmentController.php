<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use FedEx\ShipService;
use FedEx\ShipService\ComplexType;
use FedEx\ShipService\SimpleType;

require_once 'credentials.php';

class FedexShipmentController extends Controller
{
    public function getShipment()
    {
        $userCredential = new ComplexType\WebAuthenticationCredential();
        $userCredential
            ->setKey(FEDEX_KEY)
            ->setPassword(FEDEX_PASSWORD);

        $webAuthenticationDetail = new ComplexType\WebAuthenticationDetail();
        $webAuthenticationDetail->setUserCredential($userCredential);

        $clientDetail = new ComplexType\ClientDetail();
        $clientDetail
            ->setAccountNumber(FEDEX_ACCOUNT_NUMBER)
            ->setMeterNumber(FEDEX_METER_NUMBER);

        $version = new ComplexType\VersionId();
        $version
            ->setMajor(23)
            ->setIntermediate(0)
            ->setMinor(0)
            ->setServiceId('ship');

        $shipperAddress = new ComplexType\Address();
        $shipperAddress
            ->setStreetLines(['10 Fed Ex Pkwy'])
            ->setCity('Memphis')
            ->setStateOrProvinceCode('TN')
            ->setPostalCode('38115')
            ->setCountryCode('US');

        $shipperContact = new ComplexType\Contact();
        $shipperContact
            ->setCompanyName('XYZ Pvt Ltd')
            ->setEMailAddress('test@example.com')
            ->setPersonName('Sourabh')
            ->setPhoneNumber(('123-123-1234'));

        $shipper = new ComplexType\Party();
        $shipper
            ->setAccountNumber(FEDEX_ACCOUNT_NUMBER)
            ->setAddress($shipperAddress)
            ->setContact($shipperContact);

        $recipientAddress = new ComplexType\Address();
        $recipientAddress
            ->setStreetLines(['13450 Farmcrest Ct'])
            ->setCity('Herndon')
            ->setStateOrProvinceCode('VA')
            ->setPostalCode('20171')
            ->setCountryCode('US');

        $recipientContact = new ComplexType\Contact();
        $recipientContact
            ->setPersonName('Sazid')
            ->setPhoneNumber('1234567890');

        $recipient = new ComplexType\Party();
        $recipient
            ->setAddress($recipientAddress)
            ->setContact($recipientContact);

        $labelSpecification = new ComplexType\LabelSpecification();
        $labelSpecification
            ->setLabelStockType(new SimpleType\LabelStockType(SimpleType\LabelStockType::_PAPER_7X4POINT75))
            ->setImageType(new SimpleType\ShippingDocumentImageType(SimpleType\ShippingDocumentImageType::_PDF))
            ->setLabelFormatType(new SimpleType\LabelFormatType(SimpleType\LabelFormatType::_COMMON2D));

        $packageLineItem1 = new ComplexType\RequestedPackageLineItem();
        $packageLineItem1
            ->setSequenceNumber(1)
            ->setItemDescription('Product description')
            ->setDimensions(new ComplexType\Dimensions(array(
                'Width' => 10,
                'Height' => 10,
                'Length' => 25,
                'Units' => SimpleType\LinearUnits::_IN
            )))
            ->setWeight(new ComplexType\Weight(array(
                'Value' => 2,
                'Units' => SimpleType\WeightUnits::_LB
            )));

        $shippingChargesPayor = new ComplexType\Payor();
        $shippingChargesPayor->setResponsibleParty($shipper);

        $shippingChargesPayment = new ComplexType\Payment();
        $shippingChargesPayment
            ->setPaymentType(SimpleType\PaymentType::_SENDER)
            ->setPayor($shippingChargesPayor);

        $requestedShipment = new ComplexType\RequestedShipment();
        $requestedShipment->setShipTimestamp(date('c'));
        $requestedShipment->setDropoffType(new SimpleType\DropoffType(SimpleType\DropoffType::_REGULAR_PICKUP));
        $requestedShipment->setServiceType(new SimpleType\ServiceType(SimpleType\ServiceType::_FEDEX_GROUND));
        $requestedShipment->setPackagingType(new SimpleType\PackagingType(SimpleType\PackagingType::_YOUR_PACKAGING));
        $requestedShipment->setShipper($shipper);
        $requestedShipment->setRecipient($recipient);
        $requestedShipment->setLabelSpecification($labelSpecification);
        $requestedShipment->setRateRequestTypes(array(new SimpleType\RateRequestType(SimpleType\RateRequestType::_PREFERRED)));
        $requestedShipment->setPackageCount(1);
        $requestedShipment->setRequestedPackageLineItems([
            $packageLineItem1
        ]);
        $requestedShipment->setShippingChargesPayment($shippingChargesPayment);

        $processShipmentRequest = new ComplexType\ProcessShipmentRequest();
        $processShipmentRequest->setWebAuthenticationDetail($webAuthenticationDetail);
        $processShipmentRequest->setClientDetail($clientDetail);
        $processShipmentRequest->setVersion($version);
        $processShipmentRequest->setRequestedShipment($requestedShipment);

        $shipService = new ShipService\Request();
        //$shipService->getSoapClient()->__setLocation('https://ws.fedex.com:443/web-services/ship');
        $result = $shipService->getProcessShipmentReply($processShipmentRequest);

        // var_dump($result);
        // Save .pdf label

        $labelName = public_path().'/label/'.rand(10,1000).'_label.pdf';
        file_put_contents($labelName, $result->CompletedShipmentDetail->CompletedPackageDetails[0]->Label->Parts[0]->Image);
       // echo $result->CompletedShipmentDetail->CompletedPackageDetails[0]->Label->Parts[0]->Image;
       die($labelName);
    }
}
