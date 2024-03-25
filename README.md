# VeriFace Drupal integration module

This module integrates VeriFace service to Drupal.

## Features

- veriface field on user entity
- custom role assignement based on veriface result

## Howto

1. Install the module using composer: `composer require petiar/veriface` Composer will include required veriface SDK.
2. Go to the Configuration -> Web services -> VeriFace settings (`admin/config/system/veriface`) and enter the API key and change other settings as per your requirements.
3. Veriface field should be added automatically on install. However, **it's needed to set field form display and display**.
