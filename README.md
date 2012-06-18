# Can Of Spam

- Version: 2.0
- Author: Symphony Community (https://github.com/symphonists)
- Build Date: 2012-06-18
- Requirements: Symphony 2.3.x

Protect your forms against spam with a hidden hash-validation-field.

## Installation

** Note: The latest version can always be grabbed with "git clone git://github.com/symphonists/canofspam.git" **

1. Upload the 'canofspam' folder in this archive to your Symphony 'extensions' folder.

2. Enable it by selecting the "Can Of Spam", choose "Enable/Install" from the with-selected menu, then click Apply.

3. Add the "Can Of Spam" event to your page (adds the hash to the param pool).

4. Add the "Can Of Spam" filter to your section event (validates the submitted hash).

5. Add a hidden "Can Of Spam" field to your form (contains the hash from the param pool).

## Example HTML

	<input name="canofspam" value="{$canofspam}" type="hidden" />
