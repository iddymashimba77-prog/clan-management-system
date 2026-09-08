CREATE TABLE IF NOT EXISTS installation_activations (

    activation_id INT(11) NOT NULL AUTO_INCREMENT,

    installation_id VARCHAR(64) NOT NULL,

    token_hash VARCHAR(255) NOT NULL,

    token_label VARCHAR(100) DEFAULT NULL,

    domain_name VARCHAR(255) DEFAULT NULL,

    admin_user_id INT(11) NOT NULL,

    status ENUM(
        'generated',
        'used',
        'locked',
        'expired'
    ) NOT NULL DEFAULT 'generated',

    expires_at DATETIME DEFAULT NULL,

    used_at DATETIME DEFAULT NULL,

    used_ip VARCHAR(100) DEFAULT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (activation_id),

    UNIQUE KEY uq_installation_id (installation_id),

    UNIQUE KEY uq_token_hash (token_hash),

    KEY idx_admin_user_id (admin_user_id),

    KEY idx_status (status),

    CONSTRAINT fk_activation_admin
        FOREIGN KEY (admin_user_id)
        REFERENCES users(user_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;