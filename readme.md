[![duitku](https://www.duitku.com/wp-content/themes/duitku/img/logoblue.svg)](https://www.duitku.com/)

Duitku integration for your Magento 2! 💸💸💸</br>
Start receiving payment through your e-commerce magento with Duitku extension plugin payment integration.

# How to install the plugins
## Install Duitku VA Bank Syariah Indonesia plugins through Composer
Before you begin to install through the composer, you need Magento marketplace account and make sure that you have installed Composer. In your terminal, go to the Magento folder and run the following commands:
1. Install the plugins: `composer require duitku_payment_gateway/duitku_va_bsi`
2. Enable the plugin:  `bin/magento module:enable Duitku_Vabsi`
3. Execute upgrade script : `bin/magento setup:upgrade`
4. Deploy magento :  `bin/magento setup:static-content:deploy -f`
5. Clean cache storage :  `bin/magento cache:clean`
6. Check the module status:  `bin/magento module:status Duitku_Vabsi`

>Note: If you do have a previous version installed and upgrade the plugins to the latest version. After upgrade our plugins, You need to run `bin/magento setup:upgrade --keep-generated`, `bin/magento setup:static-content:deploy` and clean cache `bin/magento cachce:clean`.

## Install Duitku VA Bank Syariah Indonesia plugins from Download file

With these steps, you can custom/modify our Magento plugins to handle the business model that you want

1. Download and extract the plugin you have previously downloaded from GitHub and rename the folder as Vabsi.
2. Upload to the root of the Magento folder (Magento installation folder) you use the FTP client of your choice.
3. Locate the root Magento directory of your shop via FTP connection. </br>(By default the root folder is on SERVER_HOST/Magento)
4. Move the Vabsi folder into the Magento `root/app/code/Duitku` folder. </br>(You might need to create the folder `code/Duitku` if it is not exist).
5. Run this command on terminal

    `bin/magento module:enable Duitku_Vabsi`
    
    `bin/magento setup:upgrade`
    
    `bin/magento cache:clean`
    
    `bin/magento module:status Duitku_Vabsi`


# Plugin Usage Instruction
## Basic Plugins Configuration

Before you begin, make sure that you have successfully installed and enabled Duitku Vabsi plugins.
Configure the Duitku Vabsi plugin in your Magento admin panel: 

1. Log in to your Magento admin panel. 
2. In the left navigation bar, go to **Stores(1)** -> **Configuration(2)**. 
![](https://docs.duitku.com/static/c56ecf2d085a08a62ebd23a7cf2338df/2031d/guide-3b.png "image_mag_config")
3. In the menu, go to **Sales(3)** -> **Payment Methods(4)**
![](https://docs.duitku.com//static/2908a48ebc8a434d9b48eed003f669bb/0955f/guide-4b.png "image_mag_payment")


4. In the Other Payment Method section, click Duitku Vabsi to extend Basic Settings and fill out the following fields:

| Field                   | Description									                               |
|-------------------------| ---------------------------------------------------------------------------|
| Enabled           | To enable and disable this payment method.|
| Title             | The title of the payment method displayed to the customers.|
| Merchant number    | The unique merchantnumber received from the payment system. If you don't know your merchantnumber please Contact [Duitku Payment Provider](mailto:support@duitku.com)\.    |
| API Secret Key | Used as an API key to be used for authorization sandbox or passport environment on API request.    |
| Live Mode    | If Payment Mode Enable this redirect To live otherwise Setup Sandbox Mode.


>Note: Access Keys are unique for every merchant. Server Key is secret, please always keep Server Key confidential.

## Log options

The plugins will store log file in directory `/var/log/duitku.log`. 


see our [documentation](https://docs.duitku.com/en/payment-gateway/plugin/#tab_duitkuv2) for complete instruction.