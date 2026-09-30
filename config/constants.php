<?php
/**
 * Clan Management System
 * Global Constants
 */

// Prevent direct access
defined('SYSTEM_ACCESS') || define('SYSTEM_ACCESS', true);

// Application Information
define('APP_NAME', 'Clan Management System');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'Iddy Mashimba');

// Time Zone
date_default_timezone_set('Africa/Dar_es_Salaam');

// Base URL
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost/Clan_Management_System/');

// Upload Directories
define('UPLOAD_PATH', 'assets/uploads/');
define('PROFILE_PICTURE_PATH', 'assets/uploads/profile_pictures/');
define('CLAN_LOGO_PATH', 'assets/uploads/clan_logo/');
define('EVENT_IMAGE_PATH', 'assets/uploads/event_images/');
define('CONTRIBUTION_IMAGE_PATH', 'assets/uploads/contribution_images/');
define('DOCUMENT_PATH', 'assets/uploads/documents/');
define('GALLERY_PATH', 'assets/uploads/gallery/');

// Default Images
define('DEFAULT_PROFILE_PICTURE', 'assets/images/default_profile.png');
define('DEFAULT_CLAN_LOGO', 'assets/images/default_logo.png');

// Account Status
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');

// Gender
define('GENDER_MALE', 'Male');
define('GENDER_FEMALE', 'Female');

// Roles
define('ROLE_CHAIRPERSON', 1);
define('ROLE_SECRETARY', 2);
define('ROLE_TREASURER', 3);
define('ROLE_MEMBER', 4);
define('ROLE_ADMINISTRATOR', 5);

// Clan / Tenant Status
define('CLAN_STATUS_PENDING', 'pending');
define('CLAN_STATUS_ACTIVE', 'active');
define('CLAN_STATUS_SUSPENDED', 'suspended');
define('CLAN_STATUS_REVOKED', 'revoked');

// Subscription Status
define('SUB_STATUS_TRIAL', 'trial');
define('SUB_STATUS_ACTIVE', 'active');
define('SUB_STATUS_EXPIRED', 'expired');
define('SUB_STATUS_SUSPENDED', 'suspended');
define('SUB_STATUS_CANCELLED', 'cancelled');

