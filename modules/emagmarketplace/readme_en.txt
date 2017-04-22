1. GENERAL DESCRIPTION

The eMAG Marketplace module helps you integrate your store with the eMAG Marketplace vendor platform. This will allow your store to:

a) Automatically send product documentation (titles, descriptions, images, etc..), product price info and product stock info, to the eMAG Marketplace platform.
b) Automatically send new products and any updates for previously sent products.
c) Automatically receive orders from the eMAG Marketplace platform and create them in your store as soon as they are received.
d) Automatically send order updates to the eMAG Marketplace platform.

2. PREREQUISITES

a) You will have to install a few cron jobs on your server, so you will need to have access to your cPanel (or otherwise manually login to your server by SSH) in order to install the cron jobs.
b) The cron jobs are using the wget command, so you will need to have it installed and allowed by your hosting company.
c) Some of these cron jobs (like the one sending the products to eMAG) and some of the configuration steps (like the one downloading the eMAG categories) will sometimes run for a long time (as much as 5 or 10 or even 20 minutes) so your server must be set up to allow long running PHP scripts (we recommend 3600 seconds just in case)

3. INSTALLATION

a) Install the module from the official addon website for your store.
b) If you are not redirected to the module configuration page automatically, use the 'Configure' link to open it. This is the 'welcome' page where you will be directed to the configuration wizard. You can also use the main menu (eMAG Marketplace -> Main Configuration) to get to the first section of the configuration wizard.

4. MAIN CONFIGURATION

a) At the top of the form there is a list of instructions (blue background with an 'info' icon next to them) that you need to follow to get through the first section of the configuration wizard. The instructions may be initially collapsed to a single message saying 'Click here to see more info' and a click on it  would be required to display them all.
b) Fill in the first form ('Identity') with the correct Marketplace URL and API URL (provided to you by your eMAG Marketplace representative, for your specific country), your API vendor code, username and password (provided to you by your eMAG Marketplace representative) and then test your connection settings.
c) Save your settings and then donwload the eMAG Localities (these are needed if you want to use the eMAG AWB system to create AWB's for eMAG Marketplace orders); Please wait until the system alerts you that the localities have been downloaded, as this is a rather long operation (it could take up to 5 or 10 minutes).
d) Fill in the second form ('Orders') and if you want to use the eMAG AWB system, make sure you select your Sender Locality from the autocomplete dropdown provided by eMAG.
e) Save your settings and then install the cron jobs listed in the instructions, on your server.
f) Proceed to the next step of the wizard.

5. CATEGORY MAPPING

a) If this is the first time you are installing the module on the current store, donwload the eMAG categories using the appropriate button in the toobar (top right).
b) For each category in your store, that you want to list on the eMAG Marketplace, search for the eMAG equivalent and select it from the autocomplete dropdown and then click the 'Update' button next to it.
c) After each category mapping has been saved, the corresponding eMAG Family type will be available for selection, from the dropdown in the second column. Choose a family type and then click the 'Update' button next to it.
d) Type in a commission (integer between 0 and 100, example: 20 for 20%) in the next column, for each of the categories that you want to list and click the 'Update' button next to it.
e) Finally, change the Sync setting, in the last column, for each of the mapped categories, to enable or disable the listing for each of the categories.
f) When Sync is ON for a certain category, all active products from that category (that have this category as default category) will be listed on the eMAG Marketplace, in the eMAG category that you have previously mapped, and specifying the type of family that you selected.

6. CHARACTERISTIC MAPPING

a) This is where you map your product features and attributes to the eMAG characteristics of the categories you have mapped. A list of all the eMAG characteristics is shown, along with the eMAG category that they belong to.
b) For each of the eMAG characteristics, select the appropriate feature and attribute from your store and click the 'Update' button next to each other.
c) The characteristics you have mapped will only be sent to eMAG if either the feature or attribute that you have assigned to them is non-empty. If both the feature and the attribute have a non-empty value, only the attribute value will be sent as the corresponding characteristic value.
d) When you are finished with mapping all the characteristics needed, click on the 'Upload products' button to start sending your existing products to the eMAG Marketplace. Only active products whose default categories you have mapped (and enabled for sync) will be sent to the eMAG Marketplace.

7. API CALL LOGS

a) All comunication with the eMAG Marketplace (each API call) will be stored in the database and will be available for reviewing or debugging, by the vendor. API calls older than 30 days will be deleted, to save space.
b) This section will allow the vendor to easily check the progress of the product upload queue, find API call errors and re-upload all products to the eMAG Marketplace, if necessary.
c) One of the cron jobs that you have installed in the initial setup process, checks for API call errors every 5 minutes, and sends a notification by email, if errors are found. The email address the notification is sent to, is the main shop email address (Preferences/Store contacts/Contact details/Shop email).

8. HOW PRODUCTS ARE PROCESSED AND SENT TO THE EMAG MARKETPLACE

a) The initial product upload and all subsequent updates are added to an upload queue and the queue is processed in the background by one of the cron jobs that you have installed in the initial setup process.
b) New products that you create in the back office, will be added to the upload queue, if their default categories have been mapped and enabled for sync, and if you create them with an active initial status, or if you activate them later on. If you create a new product with its initial status = inactive, it will not be added to the upload queue.
c) Existing product updates are added to the upload queue if their default categories have been mapped and enabled for sync, and if one of the 2 rules below apply:
	- they have already been uploaded to the eMAG Marketplace;
	- they haven't been uploaded to the eMAG Marketplace yet, and you are now activating them for the first time;
d) Product updates are added to the upload queue in the following cases (but only if the rules above apply):
	- after editing and saving them in the back office;
	- after activating or deactivating them from the back office;
	- after their stock has changed due to an order being raised on them;
	- after they are deleted from the back office;

9. HOW ORDERS ARE PROCESSED AND UPDATED ON THE EMAG MARKETPLACE

a) One of the cron jobs that you have installed in the initial setup process, runs every minute in the background and checks for new orders. If new orders are found they are automatically created in your back office. If any errors are found and the orders cannot be processed, email nofitications will be sent to the main shop email address.
b) Customers for each eMAG Marketplace order, are created as guests, so it is mandatory that the Guest Checkout option is enabled in your back office. Also, their email address is not provided by the eMAG Marketplace, along with the rest of the customer info, so they are all created with your main shop email address.
c) Order updates are sent to the eMAG Marketplace in the following cases:
	- after modifying their status from the back office;
	- after adding a new product to them from the back office;
	- after modifying the price or the quantity for one of their products from the back office;
	- after deleting one of their products from the back office;
	- after generating their eMAG Marketplace AWB from the back office;

10. TROUBLESHOOTING

a) Each of the cron jobs will keep a log file where errors will be saved when something doesn't work properly.
b) The log files are located in your module's 'logs' directory (modules/emagmarketplace/logs). You will easily recognize them by their extension ('.log').