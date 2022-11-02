# UsersTask

Code review comments and performed refactoring:

1. Started file with <?php instead of <?
2. Put methods in dedicated Controller (in default namespace for Laravel Controllers)
3. Imported namespaces for other used classes
4. Extracted validation from if ($user['name'] &&...) into dedicated validation method that make use of built-in request()->validate() method.
- avoid duplication of code
- enable easier validation rules maintenance and errors reporting
- solve issue that records that don't pass validation are ignored (as it is right now)
5. Wrap database queries within transaction to allow processing either all or none records
6. Extracted database operation into dedicated method to make code cleaner
7. Added ->wintInputs() method to redirect after SQL issue so form values provided by End User can be preserved on frontent
8. Extracted DB parameters preparation into dedicated method to avoid code duplication and allow safer password encryption (built-in bcrypt instead of md5)
9. Adjusted sendEmail() method
- message and subject extracted to dedicated Mailable class
- corrected attributes given to queue() method - Mailable instead of message content
- no need to validate if $user['email'] exists as this is already part of previous validation parameters


Assumptions:
1. $users array comes from Request $_POST superglobal with format of ['users'][row 1][attribute name]
2. End user performing user tasks enter/edit data on web forms.
3. End user prefers to have all data processed or none (in case there are e.g. duplicates in submitted login or email)
4. Request validation and SQL errors are displayed on End user web forms.
5. Application is based on Laravel 9.X
