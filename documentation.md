

## Use a Gmail or G-Suit eMail address that isn't yet associated with Adwords
1. Open https://console.developers.google.com/apis/credentials?pli=1
2. Select or create a project
3. Choose "credentials" on the left then create a credential "Oauth Client ID"
4. You may need to first configure "consent screen"
5. Choose "Other" as the application type for the credential being created.
6. Copy out the "client id" and "client secret" then close
7. Run "git clone https://github.com/ztobs/Adword-API-Example.git" in your command prompt/CLI. Enter the folder and
8. Run "php GetRefreshToken.php" and follow the prompts
9. Copy out the refresh token
10. Open https://adwords.google.com/um/StartNewMccAccount?testAccount=true and use same email as above to create Test Manager Account
11. From the Dashboard (please confirm it indicates on the dashboard as test account), create an Adwords account and copy out the account id (not manager id)

## Using another Gmail or G-Suit email address that isn't yet associated with Adwords
12. Open Adwords Account Manager https://adwords.google.com/um/identity and create an account (or you can use an existing Adwords Account Manager Account).
13. Go to settings and copy out the Developer API key. This key can only work on Test accounts until you apply for production key.

## PHP Server Requirements
14. Make sure PHP server is installed and soap is enabled


Now you have:
developerToken (from live/main Adwords manager account)
clientCustomerId (from test Adwords Account)
clientId
clientSecret
refreshToken

14. Use them to update the adsapi_php.ini file






## How to Run
-------------
1. Enter directory dist/ and update the "variation.php" file (be careful not to break the code)
2. From command line interface run: php run.php http://url-to-feed.csv campaign_name

use underscore (_) to indicate space in campaign name
The script will create the campaign if it doesn't exist yet
You can optionally place feed in the dist/ directory and use the file name without any path:
php run.php feed.csv campaign_name





## Important Files
------------------
run.php => For executing the feed sync
variation.php => Contains the Ads template
clean.php => use to clear the local database storage
init.php => Use to recreate the database engine when clean.php fails
log/ => A directory where all logs are kept
temp/ => A directory where the local database is stored
adsapi_php.ini => Contains the adwords API configurations
constants.php => Some parameters like ads bids, campaign bids and keyword bid can be set
GetRefreshToken.php => only needed to get refreshtoken when setting up





## Handling Errors/Exceptions
------------------------------
- Most errors have been caught from stopping flow of script execution.
- All Errors are logged to separate log file per script execution with date and time appended.
- Policy violation errors (when certain words are rejected) are clear stated in the log file, the line in feed and the violating text
- Some other unknown errors may occur that were caught i.e didn't stop the execution of script, just read the log for line number in feed but don't stop the script when this happens
- Any error that makes the script to stop execution should be treated accordingly:
i. 1st find out what line in feed caused it and fix it
ii. Go-to Adwords dashboard and carefully delete the AdGroup connected with the error. This will avoid further "Duplicate AdGroup" errors that will occur when re-running the script
iii. Report the incidence to me@tobilukan.com in a message containing both the copied text from CLI and the last log file




## Dos and Don'ts
-------------------
- Only use the csv format specified (semi-colon separated file)

ID;"Product name";"Product price";"Product retailer price";PZN;"Product description";"Product short-name";"Product category";"Product stock level";"Product discounted price";"Discount in %";"Discount absolute";"Adwords keywords";"Link to Product image";"Link to Product";Tags;"Active/Not Active"

- Always include the header in the csv because the 1st line would be skipped
- The keywords should be comma ( , ) separated not semi-colon ( ; )
- Once a script is used to run a campaign, always use that script for that campaign to avoid error caused by local database not matching with adwords dashboard data
- Hence avoid creating or deleting Campaigns, AdGroups, Ads and Keywords from dashboard manually
- Also Avoid updating campaign names, headline1, headline2, final url, description and status from the adwords dashboard manually.
- **Bids can be updated without issue from Adwords dashboard.**
- If the script created the campaign itself and you wish to delete the campaign in future, remember to go to shared library in adwords dashboard and delete the campaign budget that was created. This will allow the script not to throw an error when that same campaign name is used again.