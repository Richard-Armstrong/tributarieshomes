<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Ion Auth Lang - English
*
* Author: Ben Edmunds
*         ben.edmunds@gmail.com
*         @benedmunds
*
* Location: http://github.com/benedmunds/ion_auth/
*
* Created:  03.14.2010
*
* Description:  English language file for Ion Auth messages and errors
*
*/

// Account Creation
$lang['account_creation_successful']            = 'Account Successfully Created';
$lang['account_creation_unsuccessful']          = 'Unable to Create Account';
$lang['account_creation_duplicate_email']       = 'Email Already Used or Invalid';
$lang['account_creation_duplicate_identity']    = 'Identity Already Used or Invalid';
$lang['account_creation_missing_default_group'] = 'Default department is not set';
$lang['account_creation_invalid_default_group'] = 'Invalid default department name set';

// Password
$lang['password_change_successful']          = 'Password Successfully Changed';
$lang['password_change_unsuccessful']        = 'Unable to Change Password';
$lang['forgot_password_successful']          = 'Password Reset Email Sent';
$lang['forgot_password_unsuccessful']        = 'Unable to Reset Password';

// Activation
$lang['activate_successful']                 = 'Account Activated';
$lang['activate_unsuccessful']               = 'Unable to Activate Account';
$lang['deactivate_successful']               = 'Account De-Activated';
$lang['deactivate_unsuccessful']             = 'Unable to De-Activate Account';
$lang['activation_email_successful']         = 'Activation Email Sent';
$lang['activation_email_unsuccessful']       = 'Unable to Send Activation Email';

// Login / Logout
$lang['login_successful']                    = 'Logged In Successfully';
$lang['login_unsuccessful']                  = 'Incorrect Login';
$lang['login_unsuccessful_not_active']       = 'Account is inactive';
$lang['login_timeout']                       = 'Temporarily Locked Out.  Try again later.';
$lang['logout_successful']                   = 'Logged Out Successfully';

// Account Changes
$lang['update_successful']                   = 'Account Information Successfully Updated';
$lang['update_unsuccessful']                 = 'Unable to Update Account Information';
$lang['invalid_user_level']                  = 'Invalid User level';
$lang['unavailable_user_level']              = 'That User level is above your own';
$lang['delete_successful']                   = 'User Deleted';
$lang['delete_unsuccessful']                 = 'Unable to Delete User';

// Departments
$lang['group_creation_successful']           = 'Department created Successfully';
$lang['group_already_exists']                = 'Department name already taken';
$lang['group_update_successful']             = 'Department details updated';
$lang['group_delete_successful']             = 'Department deleted';
$lang['group_delete_unsuccessful']           = 'Unable to delete department';
$lang['group_delete_notallowed']             = 'Can\'t delete the administrators\' department';
$lang['group_name_required']                 = 'Department name is a required field';
$lang['group_name_admin_not_alter']          = 'Administration department name cannot be changed';
$lang['group_name_unassigned_not_alter']     = 'Unassigned department name cannot be changed';
$lang['group_company_incorrect']             = 'You are not able to add a department to another company';

// Activation Email
$lang['email_activation_subject']            = 'Account Activation';
$lang['email_activate_heading']              = 'Activate account for %s';
$lang['email_activate_subheading']           = 'Please click this link to %s.';
$lang['email_activate_link']                 = 'Activate Your Account';

// Forgot Password Email
$lang['email_forgotten_password_subject']    = 'Forgotten Password Verification';
$lang['email_forgot_password_heading']       = 'Reset Password for %s';
$lang['email_forgot_password_subheading']    = 'Please click this link to %s.';
$lang['email_forgot_password_link']          = 'Reset Your Password';

// New Password Email
$lang['email_new_password_subject']          = 'New Password';
$lang['email_new_password_heading']          = 'New Password for %s';
$lang['email_new_password_subheading']       = 'Your password has been reset to: %s';
