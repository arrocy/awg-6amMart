# 6amMart-awgCloud-mod
Modification files to 6amMart for adding whatsapp notifications and Whatsapp OTP
# PLEASE BACKUP THESE 5 ORIGINAL FILES
1. /app/CentralLogics/Helpers.php
2. /app/CentralLogics/SMS_module.php
3. /app/Http/Controllers/Admin/BusinessSettingsController.php
4. /app/Http/Controllers/Admin/SMSModuleController.php
5. /resources/views/admin-views/business-settings/fcm-config.blade.php
# Installation
1. Download the zip file, Upload it to root of 6amMart site, Extract.
2. Clear cache (delete all files inside /storage/framework/views/ folder)
3. Go to (SYSTEM MANAGEMENT) menu "3rd party & configurations" >> "Firebase Notification" >> "Firebase Configuration"
4. Fill TOKEN (get it from arrocy.com)
5. Click "Submit" to save the settings
# ATTENTION!!!!
In order to show the MODIFIED "Notification Settings" page, You will need to clear cache.
If 6amMart does not have clear-cache button, then do this:
- open file manager, browse to /storage/framework/views/ folder
- delete all files in there
# How to use
1. Go to (SYSTEM MANAGEMENT) menu "3rd party & configurations" >> "Firebase Notification" >> "Firebase Configuration"
2. Fill TOKEN (get it from arrocy.com)
3. Click "Submit" to save the settings
# How to custom the message
1. Go to (SYSTEM MANAGEMENT) menu "3rd party & configurations" >> "Firebase Notification" >> "Push Notification"
2. Edit the default messages
