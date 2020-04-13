# Payload Magento2 Module

A module for integrating [Payload](https://payload.co) into a Magento2 store.

## Install

1. Navigate to the root of your Magento installation

2. Install `payload-magento2` with composer

```bash
composer require payload/payload-magento2
```

## Enable

```bash
bin/magento module:enable Payload_PayloadMagento
bin/magento setup:upgrade
```

## Configure

1. Login to Magento admin

2. Open the **Stores > Configuration** page

3. Navigate to the **Sales > Payment Methods** section

4. Scroll to the Payload section

5. Enter your Payload client and secret keys

6. *Optional* Specifiy a specific `processing_id`

7. Set the Payload payment method to active

8. Save the changes


## Documentation

To get further information on integrating with Payload,
visit the unabridged [Payload Documentation](https://docs.payload.co/?php).
